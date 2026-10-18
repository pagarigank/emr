<?php

namespace App\Services\Philhealth;

use App\Models\Cf5ShadowBilling;
use App\Models\PhilhealthClaim;

class Cf5ShadowBillingService
{
    public function calculate(PhilhealthClaim $claim): Cf5ShadowBilling
    {
        $drgCode = $this->deriveDrgCode($claim);
        $drgWeight = $this->getDrgWeight($drgCode);
        $baseRate = $this->getBaseRate();

        $totalDrg = $baseRate * $drgWeight;
        $hfAmount = $totalDrg * 0.60;
        $pfAmount = $totalDrg * 0.40;
        $actualCaseRate = (float) ($claim->caseRate?->total_rate ?? $claim->total_claimed_amount);
        $difference = $totalDrg - $actualCaseRate;

        return Cf5ShadowBilling::updateOrCreate(
            [
                'tenant_id' => $claim->tenant_id,
                'philhealth_claim_id' => $claim->id,
                'history' => 1,
            ],
            [
                'drg_code' => $drgCode,
                'drg_description' => $this->getDrgDescription($drgCode),
                'drg_weight' => $drgWeight,
                'base_rate' => $baseRate,
                'hf_amount' => $hfAmount,
                'pf_amount' => $pfAmount,
                'total_drg_amount' => $totalDrg,
                'actual_case_rate_amount' => $actualCaseRate,
                'difference_amount' => $difference,
                'status' => 'Calculated',
                'history' => 1,
                'created_by' => request()->attributes->get('jwt_user')?->id ?? 1,
                'created_date' => now(),
            ]
        );
    }

    protected function deriveDrgCode(PhilhealthClaim $claim): string
    {
        $icd10 = $claim->icd10Code?->code ?? '';

        $drgMap = [
            'I10' => 'DRG-001',
            'I11' => 'DRG-001',
            'I20' => 'DRG-002',
            'I21' => 'DRG-002',
            'I25' => 'DRG-002',
            'I48' => 'DRG-003',
            'I50' => 'DRG-004',
            'J15' => 'DRG-005',
            'J18' => 'DRG-005',
            'J44' => 'DRG-006',
            'J45' => 'DRG-007',
            'E10' => 'DRG-008',
            'E11' => 'DRG-008',
            'E14' => 'DRG-008',
            'N17' => 'DRG-009',
            'N18' => 'DRG-009',
            'K25' => 'DRG-010',
            'K26' => 'DRG-010',
            'K27' => 'DRG-010',
            'K35' => 'DRG-011',
            'K80' => 'DRG-012',
            'S72' => 'DRG-013',
            'S82' => 'DRG-014',
            'O80' => 'DRG-015',
        ];

        foreach ($drgMap as $prefix => $drg) {
            if (str_starts_with($icd10, $prefix)) {
                return $drg;
            }
        }

        return 'DRG-999';
    }

    protected function getDrgWeight(string $drgCode): float
    {
        $weights = [
            'DRG-001' => 1.2000,
            'DRG-002' => 1.5000,
            'DRG-003' => 1.1000,
            'DRG-004' => 1.3000,
            'DRG-005' => 1.0000,
            'DRG-006' => 1.1500,
            'DRG-007' => 0.9000,
            'DRG-008' => 1.2500,
            'DRG-009' => 1.4000,
            'DRG-010' => 0.8500,
            'DRG-011' => 1.6000,
            'DRG-012' => 1.0500,
            'DRG-013' => 1.3500,
            'DRG-014' => 1.1000,
            'DRG-015' => 0.7000,
            'DRG-999' => 1.0000,
        ];

        return $weights[$drgCode] ?? 1.0000;
    }

    protected function getDrgDescription(string $drgCode): string
    {
        $descriptions = [
            'DRG-001' => 'Hypertension & Cardiovascular Disorders',
            'DRG-002' => 'Coronary Artery Disease & Acute MI',
            'DRG-003' => 'Cardiac Arrhythmias',
            'DRG-004' => 'Heart Failure',
            'DRG-005' => 'Pneumonia & Respiratory Infections',
            'DRG-006' => 'COPD',
            'DRG-007' => 'Asthma',
            'DRG-008' => 'Diabetes Mellitus',
            'DRG-009' => 'Renal Failure',
            'DRG-010' => 'Peptic Ulcer Disease',
            'DRG-011' => 'Appendicitis',
            'DRG-012' => 'Cholelithiasis',
            'DRG-013' => 'Hip Fracture',
            'DRG-014' => 'Lower Leg Fracture',
            'DRG-015' => 'Normal Vaginal Delivery',
            'DRG-999' => 'Unclassified / Other Conditions',
        ];

        return $descriptions[$drgCode] ?? 'Unknown DRG';
    }

    protected function getBaseRate(): float
    {
        return config('philhealth.drg.base_rate', 35000);
    }
}
