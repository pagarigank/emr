<?php

namespace App\Services\Philhealth;

use App\Models\PhilhealthMember;
use App\Models\PhilhealthClaim;
use App\Traits\SoapClient;
use Illuminate\Support\Facades\Log;

class EclaimsService
{
    use SoapClient;

    protected array $config;
    protected string $cewsEndpoint;
    protected string $cewsUsername;
    protected string $cewsPassword;

    public function __construct()
    {
        $this->config = config('philhealth');
        $this->cewsEndpoint = $this->config['cews']['endpoint'];
        $this->cewsUsername = $this->config['cews']['username'];
        $this->cewsPassword = $this->config['cews']['password'];
    }

    public function checkEligibility(PhilhealthClaim $claim): array
    {
        $member = $claim->philhealthMember;
        $patient = $claim->patient;

        if (!$member) {
            return ['eligible' => false, 'message' => 'No PhilHealth member linked to this claim.', 'tracking_no' => null];
        }

        $params = [
            'pUserName' => $this->cewsUsername,
            'pUserPassword' => $this->cewsPassword,
            'pHospitalCode' => $this->config['hospital']['code'],
            'pPIN' => $member->pin,
            'pMemberLastName' => $member->last_name,
            'pMemberFirstName' => $member->first_name,
            'pMemberMiddleName' => $member->middle_name ?? '',
            'pMemberSuffix' => $member->suffix ?? '',
            'pMemberBirthDate' => $member->date_of_birth?->format('m-d-Y') ?? '',
            'pMailingAddress' => $member->mailing_address ?? '',
            'pZipCode' => $member->zip_code ?? '',
            'pPatientIs' => $claim->patient_is ?? $this->resolvePatientIs($claim),
            'pAdmissionDate' => $claim->date_admitted?->format('m-d-Y') ?? '',
            'pDischargeDate' => $claim->date_discharged?->format('m-d-Y') ?? '',
            'pPatientLastName' => $patient?->last_name ?? '',
            'pPatientFirstName' => $patient?->first_name ?? '',
            'pPatientMiddleName' => $patient?->middle_name ?? '',
            'pPatientSuffix' => $patient?->suffix ?? '',
            'pPatientBirthDate' => $patient?->date_of_birth?->format('m-d-Y') ?? '',
            'pPatientGender' => $patient?->gender ?? '',
            'pMemberShipType' => $member->membership_type ?? '',
            'pPEN' => '',
            'pEmployerName' => $member->employer_name ?? '',
            'pRVS' => $claim->caseRate?->rvs_code ?? '',
            'pTotalAmountActual' => number_format((float) $claim->total_actual_amount, 2, '.', ''),
            'pTotalAmountClaimed' => number_format((float) $claim->total_claimed_amount, 2, '.', ''),
            'pIsFinal' => 'N',
        ];

        return $this->callCews($params);
    }

    protected function resolvePatientIs(PhilhealthClaim $claim): string
    {
        if (!$claim->patient_id || !$claim->philhealth_member_id) return 'N';
        $patient = $claim->patient;
        $member = $claim->philhealthMember;
        if (!$patient || !$member) return 'N';
        if (strcasecmp($patient->last_name ?? '', $member->last_name ?? '') === 0
            && strcasecmp($patient->first_name ?? '', $member->first_name ?? '') === 0) {
            return 'M';
        }
        return 'D';
    }

    protected function callCews(array $params): array
    {
        $envelope = $this->buildSoapEnvelope('isClaimEligible', $params);

        try {
            $result = $this->callSoapEndpoint($this->cewsEndpoint, $envelope, $this->config['cews']['timeout'] ?? 30);

            if ($result['curl_error'] || $result['http_code'] !== 200) {
                return $this->fallbackEligibility($params);
            }

            return $this->parseCewsResponse($result['response']);
        } catch (\Throwable $e) {
            Log::warning('CEWS call failed: ' . $e->getMessage());
            return $this->fallbackEligibility($params);
        }
    }

    protected function fallbackEligibility(array $params): array
    {
        return [
            'eligible' => true,
            'message' => 'CEWS unavailable — eligibility assumed. Verify member status manually.',
            'tracking_no' => null,
            'fallback' => true,
        ];
    }

    protected function parseCewsResponse(string $response): array
    {
        try {
            $result = $this->parseSoapBody($response);
            if (!$result) {
                return $this->fallbackEligibility([]);
            }

            $return = null;
            foreach ($result->children() as $child) {
                $return = (string) $child;
                break;
            }

            $eligible = $return === 'YES';
            $trackingNo = null;
            if ($eligible) {
                foreach ($result->children() as $name => $child) {
                    if (stripos($name, 'tracking') !== false || stripos($name, 'reference') !== false) {
                        $trackingNo = (string) $child;
                        break;
                    }
                }
            }

            return [
                'eligible' => $eligible,
                'message' => $eligible ? 'Member is eligible.' : 'Member is NOT eligible for this claim.',
                'tracking_no' => $trackingNo,
                'raw_response' => $response,
                'fallback' => false,
            ];
        } catch (\Throwable $e) {
            Log::warning('CEWS response parsing failed: ' . $e->getMessage());
            return $this->fallbackEligibility([]);
        }
    }
}
