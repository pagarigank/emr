<?php

namespace Tests\Unit\Philhealth;

use App\Models\ClaimBatch;
use App\Models\Icd10Code;
use App\Models\Patient;
use App\Models\PhilhealthCaseRate;
use App\Models\PhilhealthClaim;
use App\Models\PhilhealthMember;
use App\Services\Philhealth\XmlBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XmlBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected PhilhealthClaim $claim;

    protected function setUp(): void
    {
        parent::setUp();

        $this->claim = PhilhealthClaim::create([
            'tenant_id' => 1,
            'patient_id' => 1,
            'claim_code' => 'CLM-2025-0001',
            'status' => 'For Submission',
            'claim_type' => 'CASE-RATE',
            'date_admitted' => '2025-06-01',
            'date_discharged' => '2025-06-05',
            'time_admitted' => '08:00',
            'time_discharged' => '14:00',
            'admitting_diagnosis' => 'Hypertension',
            'discharge_diagnosis' => 'Essential Hypertension',
            'outcome' => 'Improved',
            'total_actual_amount' => 35000.00,
            'total_claimed_amount' => 40000.00,
            'history' => 1,
        ]);
    }

    public function test_generates_valid_xml(): void
    {
        $builder = new XmlBuilder;
        $xml = $builder->buildClaimXml($this->claim);

        $this->assertStringStartsWith('<?xml', $xml);

        $doc = new \DOMDocument;
        $doc->loadXML($xml);

        $this->assertEquals('eCLAIMS', $doc->documentElement->tagName);
    }

    public function test_contains_transmittal_with_hospital_code(): void
    {
        $builder = new XmlBuilder;
        $xml = $builder->buildClaimXml($this->claim);

        $doc = new \DOMDocument;
        $doc->loadXML($xml);

        $transmittal = $doc->getElementsByTagName('eTRANSMITTAL')->item(0);
        $this->assertNotNull($transmittal);
        $this->assertEquals('P0000000', $transmittal->getAttribute('pHospCode'));
    }

    public function test_contains_claim_element(): void
    {
        $builder = new XmlBuilder;
        $xml = $builder->buildClaimXml($this->claim);

        $doc = new \DOMDocument;
        $doc->loadXML($xml);

        $claims = $doc->getElementsByTagName('CLAIM');
        $this->assertEquals(1, $claims->length);

        $claimEl = $claims->item(0);
        $this->assertEquals('CLM-2025-0001', $claimEl->getAttribute('pHIClaimNo'));
    }

    public function test_contains_icd_code_when_present(): void
    {
        $icd10 = Icd10Code::create(['code' => 'I10', 'name' => 'Essential Hypertension']);
        $this->claim->update(['icd10_code_id' => $icd10->id]);
        $this->claim->load('icd10Code');

        $builder = new XmlBuilder;
        $xml = $builder->buildClaimXml($this->claim);

        $doc = new \DOMDocument;
        $doc->loadXML($xml);

        $icds = $doc->getElementsByTagName('ICDCODES');
        $this->assertEquals(1, $icds->length);
        $this->assertEquals('I10', $icds->item(0)->getAttribute('pICDCode'));
    }

    public function test_contains_package_when_case_rate_present(): void
    {
        $caseRate = PhilhealthCaseRate::create([
            'rvs_code' => 'RVS-001',
            'description' => 'Test Case Rate',
            'total_rate' => 40000,
        ]);
        $this->claim->update(['case_rate_id' => $caseRate->id]);
        $this->claim->load('caseRate');

        $builder = new XmlBuilder;
        $xml = $builder->buildClaimXml($this->claim);

        $doc = new \DOMDocument;
        $doc->loadXML($xml);

        $packages = $doc->getElementsByTagName('PACKAGE');
        $this->assertEquals(1, $packages->length);
        $this->assertEquals('RVS-001', $packages->item(0)->getAttribute('pCaseRateCode'));
    }
}
