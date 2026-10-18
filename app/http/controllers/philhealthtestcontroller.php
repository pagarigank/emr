<?php

namespace App\Http\Controllers;

use App\Models\PhilhealthClaim;
use App\Services\PhilHealthXmlService;
use App\Services\PhilHealthApiService;
use Illuminate\Http\JsonResponse;

class PhilHealthTestController extends Controller
{
    protected $xmlService;
    protected $apiService;

    public function __construct(PhilHealthXmlService $xmlService, PhilHealthApiService $apiService)
    {
        $this->xmlService = $xmlService;
        $this->apiService = $apiService;
    }

    /**
     * Executes a complete end-to-end sandbox pipeline submission test.
     *
     * Resolves the HCI code from the claim's tenant automatically.
     * Pass ?claim_id=N to test a specific claim, or defaults to the first draft claim.
     */
    public function __invoke(): JsonResponse
    {
        try {
            // 1. Resolve test claim (use query param or first available)
            $claimId = request()->query('claim_id');
            $claim   = $claimId
                ? PhilhealthClaim::withoutGlobalScopes()->with('tenant')->findOrFail($claimId)
                : PhilhealthClaim::withoutGlobalScopes()->with('tenant')->firstOrFail();

            // 2. Resolve HCI code from the claim's tenant
            $hciCode = $claim->tenant->philhealth_hci_code;

            if (!$hciCode) {
                return response()->json([
                    'diagnostic_run' => 'failed',
                    'error'          => "Tenant #{$claim->tenant_id} has no philhealth_hci_code configured.",
                ], 422);
            }

            // 3. Generate eSOA XML payload
            $xmlString = $this->xmlService->generateEsoaXml($claim->id, $hciCode);

            // 4. Transmit to PhilHealth sandbox
            $filename  = "HCI_{$hciCode}_Claim_{$claim->claim_code}.xml";
            $apiResult = $this->apiService->uploadTestClaim($xmlString, $filename);

            return response()->json([
                'diagnostic_run' => 'complete',
                'claim_code'     => $claim->claim_code,
                'hci_code'       => $hciCode,
                'generated_file' => $filename,
                'xml_preview'    => substr($xmlString, 0, 500) . '...',
                'api_response'   => $apiResult,
            ], $apiResult['http_code'] ?? 200);

        } catch (\Exception $e) {
            return response()->json([
                'diagnostic_run' => 'failed',
                'error'          => $e->getMessage(),
            ], 500);
        }
    }
}
