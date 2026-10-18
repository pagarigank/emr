<?php

namespace App\Services\Philhealth;

use App\Traits\SoapClient;
use Illuminate\Support\Facades\Log;

class CloudStorageService
{
    use SoapClient;

    protected array $config;
    protected string $endpoint;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        $this->config = config('philhealth');
        $this->endpoint = $this->config['cloud_storage']['endpoint'];
        $this->username = $this->config['cloud_storage']['username'];
        $this->password = $this->config['cloud_storage']['password'];
    }

    public function createFolder(string $folderName): array
    {
        $params = [
            'pUserName' => $this->username,
            'pUserPassword' => $this->password,
            'pHospitalCode' => $this->config['hospital']['code'],
            'pFolderName' => $folderName,
        ];

        return $this->call('createFolder', $params);
    }

    public function uploadFile(string $folderName, string $fileName, string $fileContent): array
    {
        $params = [
            'pUserName' => $this->username,
            'pUserPassword' => $this->password,
            'pHospitalCode' => $this->config['hospital']['code'],
            'pFolderName' => $folderName,
            'pFileName' => $fileName,
            'pFileData' => base64_encode($fileContent),
            'pFileDescription' => "e-Claims XML - {$fileName}",
        ];

        return $this->call('uploadFile', $params);
    }

    protected function call(string $method, array $params): array
    {
        $envelope = $this->buildSoapEnvelope($method, $params);

        try {
            $result = $this->callSoapEndpoint($this->endpoint, $envelope, 120);

            if ($result['curl_error']) {
                Log::error("Cloud storage {$method} curl error: {$result['curl_error']}");
                return $this->error("Connection failed: {$result['curl_error']}");
            }

            if ($result['http_code'] !== 200) {
                Log::warning("Cloud storage {$method} HTTP {$result['http_code']}");
                return $this->error("HTTP {$result['http_code']}");
            }

            return $this->parseResponse($result['response'], $method);
        } catch (\Throwable $e) {
            Log::error("Cloud storage {$method} exception: " . $e->getMessage());
            return $this->error('Exception: ' . $e->getMessage());
        }
    }

    protected function parseResponse(string $response, string $method): array
    {
        try {
            $result = $this->parseSoapBody($response);
            if (!$result) {
                return $this->error('Failed to parse cloud storage response.');
            }

            $return = [];
            foreach ($result->children() as $name => $child) {
                $return[$name] = (string) $child;
            }

            $statusCode = $return['return'] ?? '0';
            $statusMsg = $return['message'] ?? $return['statusMessage'] ?? '';

            if ($statusCode === '1' || strtoupper($statusCode) === 'SUCCESS') {
                return [
                    'success' => true,
                    'message' => $statusMsg ?: "{$method} completed successfully.",
                    'raw_response' => $response,
                ];
            }

            return [
                'success' => false,
                'message' => $statusMsg ?: "{$method} failed with code: {$statusCode}.",
                'raw_response' => $response,
            ];
        } catch (\Throwable $e) {
            Log::error("Cloud storage {$method} parse error: " . $e->getMessage());
            return $this->error('Parse error: ' . $e->getMessage());
        }
    }

    protected function error(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
