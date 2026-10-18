<?php

namespace Tests\Unit\Philhealth;

use App\Models\Cf5ShadowBilling;
use App\Models\Icd10Code;
use App\Models\PhilhealthCaseRate;
use App\Models\PhilhealthClaim;
use App\Services\Philhealth\Cf5ShadowBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cf5ShadowBillingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_drg_for_hypertension(): void
    {
        $icd10 = Icd10Code::create(['code' => 'I10', 'name' => 'Essential Hypertension']);
        $caseRate = PhilhealthCaseRate::create(['rvs_code' => 'CR-001', 'description' => 'Test Case Rate', 'total_rate' => 25000]);

        $claim = PhilhealthClaim::create([
            'tenant_id' => 1,
            'patient_id' => 1,
            'claim_code' => 'CLM-001',
            'icd10_code_id' => $icd10->id,
            'case_rate_id' => $caseRate->id,
            'total_claimed_amount' => 25000,
            'history' => 1,
        ]);

        $service = new Cf5ShadowBillingService;
        $result = $service->calculate($claim);

        $this->assertInstanceOf(Cf5ShadowBilling::class, $result);
        $this->assertEquals('DRG-001', $result->drg_code);
        $this->assertEquals(1.2000, $result->drg_weight);
        $this->assertEquals(35000, $result->base_rate);
        $this->assertEquals(42000, $result->total_drg_amount);
    }

    public function test_falls_back_to_drg_999_for_unmapped_icd10(): void
    {
        $icd10 = Icd10Code::create(['code' => 'Z00', 'name' => 'General Examination']);
        $caseRate = PhilhealthCaseRate::create(['rvs_code' => 'CR-002', 'description' => 'Test Case Rate', 'total_rate' => 10000]);

        $claim = PhilhealthClaim::create([
            'tenant_id' => 1,
            'patient_id' => 1,
            'claim_code' => 'CLM-002',
            'icd10_code_id' => $icd10->id,
            'case_rate_id' => $caseRate->id,
            'total_claimed_amount' => 10000,
            'history' => 1,
        ]);

        $service = new Cf5ShadowBillingService;
        $result = $service->calculate($claim);

        $this->assertEquals('DRG-999', $result->drg_code);
        $this->assertEquals(1.0000, $result->drg_weight);
        $this->assertEquals(35000, $result->total_drg_amount);
    }

    public function test_calculates_hf_pf_split_correctly(): void
    {
        $icd10 = Icd10Code::create(['code' => 'J18', 'name' => 'Pneumonia']);
        $caseRate = PhilhealthCaseRate::create(['rvs_code' => 'CR-003', 'description' => 'Test Case Rate', 'total_rate' => 20000]);

        $claim = PhilhealthClaim::create([
            'tenant_id' => 1,
            'patient_id' => 1,
            'claim_code' => 'CLM-003',
            'icd10_code_id' => $icd10->id,
            'case_rate_id' => $caseRate->id,
            'total_claimed_amount' => 20000,
            'history' => 1,
        ]);

        $service = new Cf5ShadowBillingService;
        $result = $service->calculate($claim);

        $totalDrg = 35000 * 1.0000;
        $this->assertEquals($totalDrg * 0.60, $result->hf_amount);
        $this->assertEquals($totalDrg * 0.40, $result->pf_amount);
        $this->assertEquals($totalDrg - 20000, $result->difference_amount);
    }

    public function test_calculates_drg_for_normal_delivery(): void
    {
        $icd10 = Icd10Code::create(['code' => 'O80', 'name' => 'Normal Delivery']);
        $caseRate = PhilhealthCaseRate::create(['rvs_code' => 'CR-004', 'description' => 'Test Case Rate', 'total_rate' => 15000]);

        $claim = PhilhealthClaim::create([
            'tenant_id' => 1,
            'patient_id' => 1,
            'claim_code' => 'CLM-004',
            'icd10_code_id' => $icd10->id,
            'case_rate_id' => $caseRate->id,
            'total_claimed_amount' => 15000,
            'history' => 1,
        ]);

        $service = new Cf5ShadowBillingService;
        $result = $service->calculate($claim);

        $this->assertEquals('DRG-015', $result->drg_code);
        $this->assertEquals(0.7000, $result->drg_weight);
        $this->assertEquals('Normal Vaginal Delivery', $result->drg_description);
    }

    public function test_uses_claimed_amount_when_no_case_rate(): void
    {
        $icd10 = Icd10Code::create(['code' => 'I50', 'name' => 'Heart Failure']);

        $claim = PhilhealthClaim::create([
            'tenant_id' => 1,
            'patient_id' => 1,
            'claim_code' => 'CLM-005',
            'icd10_code_id' => $icd10->id,
            'total_claimed_amount' => 45000,
            'history' => 1,
        ]);

        $service = new Cf5ShadowBillingService;
        $result = $service->calculate($claim);

        $this->assertEquals(45000, $result->actual_case_rate_amount);
    }
}
