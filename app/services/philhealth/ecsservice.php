<?php

namespace App\Services\Philhealth;

use App\Models\ClaimBatch;
use App\Models\PhilhealthClaim;
use App\Traits\SoapClient;
use Illuminate\Support\Facades\Log;

class EcsService
{
    use SoapClient;

    protected array $config;
    protected string $endpoint;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        $this->config = config('philhealth');
        $this->endpoint = $this->config['eclaims']['endpoint'];
        $this->username = $this->config['eclaims']['username'];
        $this->password = $this->config['eclaims']['password'];
    }

    public function uploadClaim(PhilhealthClaim $claim): array
    {
        $builder = app(XmlBuilder::class);
        $xml = $builder->buildClaimXml($claim);

        return $this->callEcsUpload($claim->claim_code, $xml, $claim->total_claimed_amount);
    }

    public function uploadBatch(ClaimBatch $batch): array
    {
        $builder = app(XmlBuilder::class);
        $xml = $builder->buildBatchXml($batch);

        return $this->callEcsUpload($batch->batch_code, $xml, $batch->total_amount);
    }

    protected function callEcsUpload(string $referenceNo, string $xmlContent, $totalAmount): array
    {
        $params = [
            'pUserName' => $this->username,
            'pUserPassword' => $this->password,
            'pHospitalCode' => $this->config['hospital']['code'],
            'pTransmittalNo' => $referenceNo,
            'pProcessDate' => now()->format('m-d-Y'),
            'pTotalClaimAmount' => number_format((float) $totalAmount, 2, '.', ''),
            'pXMLFilePath' => '',
            'pXMLFileName' => $referenceNo . '.xml',
            'pXMLFile' => $xmlContent,
            'pSystemName' => $this->config['system']['name'],
            'pSystemVersion' => $this->config['system']['version'],
        ];

        $envelope = $this->buildSoapEnvelope('eClaimsUpload', $params);

        try {
            $result = $this->callSoapEndpoint($this->endpoint, $envelope, $this->config['eclaims']['timeout'] ?? 60);

            if ($result['curl_error']) {
                Log::error('ECS upload curl error: ' . $result['curl_error']);
                return $this->ecsError('Connection failed: ' . $result['curl_error']);
            }

            if ($result['http_code'] !== 200) {
                Log::warning("ECS upload HTTP {$result['http_code']} for {$referenceNo}");
                return $this->ecsError("HTTP {$result['http_code']} from ECS endpoint.");
            }

            return $this->parseEcsResponse($result['response'], $referenceNo);
        } catch (\Throwable $e) {
            Log::error('ECS upload exception: ' . $e->getMessage());
            return $this->ecsError('Exception: ' . $e->getMessage());
        }
    }

    protected function parseEcsResponse(string $response, string $referenceNo): array
    {
        try {
            $result = $this->parseSoapBody($response);
            if (!$result) {
                return $this->ecsError('Failed to parse ECS response.');
            }

            $return = [];
            foreach ($result->children() as $name => $child) {
                $return[$name] = (string) $child;
            }

            $trackingNo = $return['eClaimsUploadReturn'] ?? $return['return'] ?? null;

            if ($trackingNo && $trackingNo !== '') {
                return [
                    'success' => true,
                    'tracking_no' => $trackingNo,
                    'message' => 'Claim uploaded successfully via ECS.',
                    'raw_response' => $response,
                ];
            }

            return [
                'success' => false,
                'tracking_no' => null,
                'message' => $return['message'] ?? 'ECS upload returned empty tracking number.',
                'raw_response' => $response,
            ];
        } catch (\Throwable $e) {
            Log::error('ECS response parse error: ' . $e->getMessage());
            return $this->ecsError('Parse error: ' . $e->getMessage());
        }
    }

    protected function ecsError(string $message): array
    {
        return [
            'success' => false,
            'tracking_no' => null,
            'message' => $message,
        ];
    }
}
