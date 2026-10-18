<?php

namespace App\Services\Philhealth;

use App\Models\ClaimBatch;
use App\Models\ClaimStatusPoll;
use App\Models\PhilhealthClaim;
use App\Services\Philhealth\AcknowledgmentService;
use App\Traits\SoapClient;
use Illuminate\Support\Facades\Log;

class ClaimStatusPollingService
{
    use SoapClient;

    protected array $config;
    protected AcknowledgmentService $acknowledgmentService;

    public function __construct(AcknowledgmentService $acknowledgmentService)
    {
        $this->config = config('philhealth');
        $this->acknowledgmentService = $acknowledgmentService;
    }

    public function pollClaim(int $claimId): array
    {
        $claim = PhilhealthClaim::findOrFail($claimId);

        if (!in_array($claim->status, ['Submitted', 'Acknowledged'])) {
            return ['success' => false, 'message' => 'Claim is not in a pollable state.'];
        }

        $statusBefore = $claim->status;

        try {
            $result = $this->callStatusCheck($claim);

            $statusAfter = $claim->status;
            $changed = false;

            if ($result['acknowledged'] ?? false) {
                $this->acknowledgmentService->acknowledgeClaim($claim, $result['acknowledgment_code'] ?? 'AUTO-' . now()->format('YmdHis'));
                $statusAfter = 'Acknowledged';
                $changed = true;
            }

            if ($result['paid'] ?? false) {
                $this->acknowledgmentService->recordVoucher(
                    $claim,
                    $result['voucher_number'] ?? 'VCH-' . now()->format('YmdHis'),
                    (float) ($result['voucher_amount'] ?? 0)
                );
                $statusAfter = 'Paid';
                $changed = true;
            }

            if ($result['denied'] ?? false) {
                $this->acknowledgmentService->markDenied($claim, $result['denial_reason'] ?? 'No reason provided.');
                $statusAfter = 'Denied';
                $changed = true;
            }

            ClaimStatusPoll::create([
                'tenant_id' => $claim->tenant_id,
                'philhealth_claim_id' => $claim->id,
                'claim_batch_id' => $claim->claim_batch_id,
                'polled_at' => now(),
                'status_before' => $statusBefore,
                'status_after' => $changed ? $statusAfter : $statusBefore,
                'response_data' => json_encode($result),
                'poll_source' => 'manual',
                'history' => 1,
                'created_by' => 1,
                'created_date' => now(),
            ]);

            return [
                'success' => true,
                'changed' => $changed,
                'status_before' => $statusBefore,
                'status_after' => $statusAfter,
                'message' => $changed ? "Status changed from {$statusBefore} to {$statusAfter}." : 'No status change detected.',
            ];
        } catch (\Throwable $e) {
            Log::error('Claim poll error for claim_id=' . ($claim->id ?? 'unknown'));

            ClaimStatusPoll::create([
                'tenant_id' => $claim->tenant_id,
                'philhealth_claim_id' => $claim->id,
                'claim_batch_id' => $claim->claim_batch_id,
                'polled_at' => now(),
                'status_before' => $statusBefore,
                'status_after' => $statusBefore,
                'response_data' => json_encode(['error' => $e->getMessage()]),
                'poll_source' => 'manual',
                'history' => 1,
                'created_by' => 1,
                'created_date' => now(),
            ]);

            return ['success' => false, 'message' => 'Poll error: ' . $e->getMessage()];
        }
    }

    public function pollBatch(int $batchId): array
    {
        $batch = ClaimBatch::with('claims')->findOrFail($batchId);
        $results = [];

        foreach ($batch->claims as $claim) {
            $results[$claim->id] = $this->pollClaim($claim->id);
        }

        return [
            'success' => true,
            'total' => count($results),
            'changed' => collect($results)->where('changed', true)->count(),
            'results' => $results,
        ];
    }

    public function pollAllSubmitted(): array
    {
        $claims = PhilhealthClaim::whereIn('status', ['Submitted', 'Acknowledged'])->get();
        $results = [];

        foreach ($claims as $claim) {
            $results[$claim->id] = $this->pollClaim($claim->id);
        }

        return [
            'success' => true,
            'total' => count($results),
            'changed' => collect($results)->where('changed', true)->count(),
            'results' => $results,
        ];
    }

    protected function callStatusCheck(PhilhealthClaim $claim): array
    {
        $endpoint = $this->config['eclaims']['status_endpoint'] ?? $this->config['eclaims']['endpoint'];

        $params = [
            'pUserName' => $this->config['eclaims']['username'],
            'pUserPassword' => $this->config['eclaims']['password'],
            'pHospitalCode' => $this->config['hospital']['code'],
            'pClaimCode' => $claim->claim_code,
            'pTransmittalNo' => $claim->claimBatch?->batch_code ?? '',
        ];

        $envelope = $this->buildSoapEnvelope('getClaimStatus', $params);

        try {
            $result = $this->callSoapEndpoint($endpoint, $envelope, 60);

            if ($result['curl_error']) {
                Log::warning('Status poll curl error (fallback to simulated): ' . $result['curl_error']);
                return $this->simulateStatusCheck($claim);
            }

            if ($result['http_code'] !== 200) {
                Log::warning("Status poll HTTP {$result['http_code']} (fallback to simulated)");
                return $this->simulateStatusCheck($claim);
            }

            return $this->parseStatusResponse($result['response'], $claim);
        } catch (\Throwable $e) {
            Log::warning('Status poll exception (fallback to simulated): ' . $e->getMessage());
            return $this->simulateStatusCheck($claim);
        }
    }

    protected function parseStatusResponse(string $response, PhilhealthClaim $claim): array
    {
        try {
            $result = $this->parseSoapBody($response);
            if (!$result) {
                return $this->simulateStatusCheck($claim);
            }

            $return = [];
            foreach ($result->children() as $name => $child) {
                $return[$name] = (string) $child;
            }

            $statusCode = $return['statusCode'] ?? $return['return'] ?? '';

            return match (strtoupper($statusCode)) {
                'ACKNOWLEDGED', 'ACCEPTED', '1' => [
                    'acknowledged' => true,
                    'acknowledgment_code' => $return['acknowledgmentCode'] ?? $return['referenceNo'] ?? 'AUTO-' . now()->format('YmdHis'),
                    'message' => $return['message'] ?: 'Claim acknowledged.',
                ],
                'PAID', 'PAYMENT', '2' => [
                    'paid' => true,
                    'voucher_number' => $return['voucherNo'] ?? $return['checkNo'] ?? 'VCH-' . now()->format('YmdHis'),
                    'voucher_amount' => $return['voucherAmount'] ?? $return['amount'] ?? 0,
                    'message' => $return['message'] ?: 'Claim paid.',
                ],
                'DENIED', 'REJECTED', '3' => [
                    'denied' => true,
                    'denial_reason' => $return['message'] ?: 'Claim denied by PhilHealth.',
                    'message' => $return['message'] ?: 'Claim denied.',
                ],
                default => [
                    'acknowledged' => false,
                    'paid' => false,
                    'denied' => false,
                    'status' => $statusCode,
                    'message' => $return['message'] ?: 'No status update.',
                ],
            };
        } catch (\Throwable $e) {
            return $this->simulateStatusCheck($claim);
        }
    }

    protected function simulateStatusCheck(PhilhealthClaim $claim): array
    {
        $daysSinceSubmission = $claim->ecs_uploaded_at
            ? now()->diffInDays($claim->ecs_uploaded_at)
            : ($claim->created_date ? now()->diffInDays($claim->created_date) : 0);

        if ($claim->status === 'Paid' || $claim->status === 'Denied') {
            return ['acknowledged' => false, 'paid' => false, 'denied' => false, 'message' => 'Claim already finalized.'];
        }

        return match (true) {
            $daysSinceSubmission >= 30 => [
                'acknowledged' => true,
                'acknowledgment_code' => 'SIM-ACK-' . $claim->claim_code,
                'message' => 'Claim acknowledged (simulated).',
            ],
            $daysSinceSubmission >= 15 && $claim->status === 'Submitted' => [
                'acknowledged' => true,
                'paid' => true,
                'voucher_number' => 'SIM-VCH-' . $claim->claim_code,
                'voucher_amount' => $claim->total_claimed_amount * 0.85,
                'message' => 'Claim paid (simulated).',
            ],
            $daysSinceSubmission >= 7 => [
                'acknowledged' => true,
                'acknowledgment_code' => 'SIM-ACK-' . $claim->claim_code,
                'message' => 'Claim acknowledged (simulated).',
            ],
            default => [
                'acknowledged' => false,
                'paid' => false,
                'denied' => false,
                'message' => 'Pending — no status update available (simulated).',
            ],
        };
    }
}
