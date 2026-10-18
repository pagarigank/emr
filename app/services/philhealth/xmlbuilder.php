<?php

namespace App\Services\Philhealth;

use App\Models\PhilhealthClaim;
use App\Models\ClaimBatch;
use App\Services\Philhealth\OecbClaimFormatter;
use DOMDocument;
use DOMElement;

class XmlBuilder
{
    protected array $config;
    protected DOMDocument $doc;

    public function __construct()
    {
        $this->config = config('philhealth');
    }

    public function buildClaimXml(PhilhealthClaim $claim): string
    {
        $claim->loadMissing([
            'patient', 'philhealthMember', 'icd10Code', 'caseRate',
            'secondCaseRate', 'items', 'claimBatch', 'claimProcedures',
        ]);

        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;

        $eclaims = $this->doc->createElement('eCLAIMS');
        $this->doc->appendChild($eclaims);

        $batch = $claim->claimBatch;
        $transmittalNo = $batch ? $batch->id : 1;

        $transmittal = $this->doc->createElement('eTRANSMITTAL');
        $transmittal->setAttribute('pHospCode', $this->config['hospital']['code']);
        $transmittal->setAttribute('pTransmittalNo', (string) $transmittalNo);
        $transmittal->setAttribute('pProcessDate', now()->format('m-d-Y'));
        $transmittal->setAttribute('pHCIFaxNo', $this->config['hospital']['fax_no']);
        $transmittal->setAttribute('pHCIEmail', $this->config['hospital']['email']);
        $transmittal->setAttribute('pHCIContactNo', $this->config['hospital']['contact_no']);
        $transmittal->setAttribute('pRemarks', $claim->remarks ?? '');
        $transmittal->setAttribute('pTotalClaimCount', '1');
        $transmittal->setAttribute('pTotalClaimAmount', number_format((float) $claim->total_claimed_amount, 2, '.', ''));
        $transmittal->setAttribute('pHospitalFileName', ($batch ? $batch->batch_code : $claim->claim_code) . '.xml');
        $transmittal->setAttribute('pHospitalFileDate', now()->format('m-d-Y'));
        $transmittal->setAttribute('pSystemName', $this->config['system']['name']);
        $transmittal->setAttribute('pSystemVersion', $this->config['system']['version']);
        $transmittal->setAttribute('pHCPName', $this->config['hospital']['name']);
        $transmittal->setAttribute('pHCPTIN', $this->config['hospital']['tin']);
        $eclaims->appendChild($transmittal);

        $this->appendClaimElement($eclaims, $claim, 1);

        return $this->doc->saveXML();
    }

    public function buildBatchXml(ClaimBatch $batch): string
    {
        $batch->loadMissing([
            'claims.patient', 'claims.philhealthMember', 'claims.icd10Code',
            'claims.caseRate', 'claims.secondCaseRate', 'claims.items',
            'claims.claimProcedures',
        ]);

        $this->doc = new DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = true;

        $eclaims = $this->doc->createElement('eCLAIMS');
        $this->doc->appendChild($eclaims);

        $totalAmount = 0;
        foreach ($batch->claims as $claim) {
            $totalAmount += (float) $claim->total_claimed_amount;
        }

        $transmittal = $this->doc->createElement('eTRANSMITTAL');
        $transmittal->setAttribute('pHospCode', $this->config['hospital']['code']);
        $transmittal->setAttribute('pTransmittalNo', (string) $batch->id);
        $transmittal->setAttribute('pProcessDate', now()->format('m-d-Y'));
        $transmittal->setAttribute('pHCIFaxNo', $this->config['hospital']['fax_no']);
        $transmittal->setAttribute('pHCIEmail', $this->config['hospital']['email']);
        $transmittal->setAttribute('pHCIContactNo', $this->config['hospital']['contact_no']);
        $transmittal->setAttribute('pRemarks', $batch->remarks ?? '');
        $transmittal->setAttribute('pTotalClaimCount', (string) $batch->claims->count());
        $transmittal->setAttribute('pTotalClaimAmount', number_format($totalAmount, 2, '.', ''));
        $transmittal->setAttribute('pHospitalFileName', $batch->batch_code . '.xml');
        $transmittal->setAttribute('pHospitalFileDate', now()->format('m-d-Y'));
        $transmittal->setAttribute('pSystemName', $this->config['system']['name']);
        $transmittal->setAttribute('pSystemVersion', $this->config['system']['version']);
        $transmittal->setAttribute('pHCPName', $this->config['hospital']['name']);
        $transmittal->setAttribute('pHCPTIN', $this->config['hospital']['tin']);
        $eclaims->appendChild($transmittal);

        $claimNo = 1;
        foreach ($batch->claims as $claim) {
            $this->appendClaimElement($eclaims, $claim, $claimNo);
            $claimNo++;
        }

        return $this->doc->saveXML();
    }

    protected function appendClaimElement(\DOMElement $eclaims, PhilhealthClaim $claim, int $claimNo): void
    {
        $claimEl = $this->doc->createElement('CLAIM');
        $claimEl->setAttribute('pClaimNo', (string) $claimNo);
        $claimEl->setAttribute('pPhilhealthClaimType', $this->mapClaimType($claim->claim_type ?? 'ACR'));
        $claimEl->setAttribute('pHIClaimNo', $claim->claim_code);
        $claimEl->setAttribute('pHIAdmissionDate', $claim->date_admitted?->format('m-d-Y') ?? '');
        $claimEl->setAttribute('pHIDischargeDate', $claim->date_discharged?->format('m-d-Y') ?? '');
        $claimEl->setAttribute('pHIAdmissionTime', $claim->time_admitted ?? '');
        $claimEl->setAttribute('pHIDischargeTime', $claim->time_discharged ?? '');
        $claimEl->setAttribute('pAdmissionDiagnosis', $claim->admitting_diagnosis ?? '');
        $claimEl->setAttribute('pDischargeDiagnosis', $claim->discharge_diagnosis ?? '');
        $claimEl->setAttribute('pDischargeDisposition', $claim->disposition_code ?? $claim->outcome ?? '');
        $claimEl->setAttribute('pClaimStatusType', 'N');
        $claimEl->setAttribute('pAmtActual', number_format((float) $claim->total_actual_amount, 2, '.', ''));
        $claimEl->setAttribute('pAmtClaimed', number_format((float) $claim->total_claimed_amount, 2, '.', ''));
        $claimEl->setAttribute('pAmtAdmitted', number_format((float) $claim->total_claimed_amount, 2, '.', ''));
        $claimEl->setAttribute('pClaimedDays', $this->calculateClaimedDays($claim));
        $claimEl->setAttribute('pIsFinal', 'N');
        $eclaims->appendChild($claimEl);

        // MEMBER
        if ($claim->philhealthMember) {
            $member = $this->doc->createElement('MEMBER');
            $member->setAttribute('pPIN', $claim->philhealthMember->pin ?? '');
            $member->setAttribute('pMemberShipType', $this->mapMembershipType($claim->philhealthMember->membership_type ?? ''));
            $member->setAttribute('pMemberLastName', $claim->philhealthMember->last_name ?? '');
            $member->setAttribute('pMemberFirstName', $claim->philhealthMember->first_name ?? '');
            $member->setAttribute('pMemberMiddleName', $claim->philhealthMember->middle_name ?? '');
            $member->setAttribute('pMemberSuffix', $claim->philhealthMember->suffix ?? '');
            $member->setAttribute('pMemberBirthDate', $claim->philhealthMember->date_of_birth?->format('m-d-Y') ?? '');
            $claimEl->appendChild($member);
        }

        // PATIENT
        if ($claim->patient) {
            $patient = $this->doc->createElement('PATIENT');
            $patient->setAttribute('pPatientIs', $claim->patient_is ?? 'M');
            $patient->setAttribute('pPatientLastName', $claim->patient->last_name ?? '');
            $patient->setAttribute('pPatientFirstName', $claim->patient->first_name ?? '');
            $patient->setAttribute('pPatientMiddleName', $claim->patient->middle_name ?? '');
            $patient->setAttribute('pPatientSuffix', $claim->patient->suffix ?? '');
            $patient->setAttribute('pPatientBirthDate', $claim->patient->date_of_birth?->format('m-d-Y') ?? '');
            $patient->setAttribute('pPatientGender', $claim->gender ?? $claim->patient->gender ?? '');
            $claimEl->appendChild($patient);
        }

        // DIAGNOSIS
        if ($claim->icd10Code) {
            $icds = $this->doc->createElement('ICDCODES');
            $icds->setAttribute('pICDCode', str_replace('.', '', $claim->icd10Code->code ?? $claim->icd10Code->description ?? ''));
            $icds->setAttribute('pICDType', 'F');
            $claimEl->appendChild($icds);
        }

        // PROCEDURES (from claim_procedures table)
        $procedures = $claim->claimProcedures;
        if ($procedures && $procedures->count() > 0) {
            foreach ($procedures as $proc) {
                $procEl = $this->doc->createElement('PROCEDURE');
                $procEl->setAttribute('pRVSCode', $proc->rvs_code ?? '');
                $procEl->setAttribute('pRVU', '1');
                $procEl->setAttribute('pOperationDate', $proc->procedure_date?->format('m-d-Y') ?? '');
                $claimEl->appendChild($procEl);
            }
        } else {
            // Fallback: emit PROCEDURE elements from claim_items with type=Procedure
            $procedureItems = $claim->items->where('item_type', 'Procedure');
            foreach ($procedureItems as $item) {
                $procEl = $this->doc->createElement('PROCEDURE');
                $procEl->setAttribute('pRVSCode', $item->item_code ?? '');
                $procEl->setAttribute('pRVU', '1');
                $procEl->setAttribute('pOperationDate', $claim->date_admitted?->format('m-d-Y') ?? '');
                $claimEl->appendChild($procEl);
            }
        }

        // PACKAGE (case rate)
        if ($claim->caseRate) {
            $pkg = $this->doc->createElement('PACKAGE');
            $pkg->setAttribute('pCaseRateCode', $claim->caseRate->rvs_code ?? '');
            $pkg->setAttribute('pCaseRateAmount', number_format((float) $claim->caseRate->total_rate, 2, '.', ''));
            $pkg->setAttribute('pCaseRateHF', number_format((float) $claim->caseRate->hf_fee, 2, '.', ''));
            $pkg->setAttribute('pCaseRatePF', number_format((float) $claim->caseRate->pf_fee, 2, '.', ''));
            if ($claim->secondCaseRate) {
                $pkg->setAttribute('pSecondCaseRateCode', $claim->secondCaseRate->rvs_code ?? '');
            }
            $claimEl->appendChild($pkg);
        } elseif (strtoupper($claim->claim_type ?? '') === 'OECB') {
            $oecbEntries = $claim->erAdmission?->oecbEntries?->where('history', 1) ?? collect();
            $totalFixedFee = $oecbEntries->sum('total_amount') ?: $oecbEntries->sum('oecb_fixed_fee');
            $pkg = $this->doc->createElement('PACKAGE');
            $pkg->setAttribute('pPackageRVS', 'OPER1');
            $pkg->setAttribute('pPackageName', 'OECB-FBE');
            $pkg->setAttribute('pPackageAmount', number_format((float) $totalFixedFee, 2, '.', ''));
            $claimEl->appendChild($pkg);
        }

        // OBSERVATION
        $obs = $this->doc->createElement('OBSERVATION');
        $this->addObservationValue($obs, 'Patient Last Name', $claim->patient?->last_name ?? '');
        $this->addObservationValue($obs, 'Patient First Name', $claim->patient?->first_name ?? '');
        $this->addObservationValue($obs, 'Patient Middle Name', $claim->patient?->middle_name ?? '');
        $this->addObservationValue($obs, 'Patient DOB', $claim->patient?->date_of_birth?->format('m-d-Y') ?? '');
        $this->addObservationValue($obs, 'Patient Gender', $claim->gender ?? $claim->patient?->gender ?? '');
        $this->addObservationValue($obs, 'Patient Type', $claim->encounter_type ?? 'Inpatient');
        $this->addObservationValue($obs, 'Chief Complaint', app(OecbClaimFormatter::class)->format($claim));
        if ($claim->philhealthMember) {
            $this->addObservationValue($obs, 'Member PIN', $claim->philhealthMember->pin);
            $this->addObservationValue($obs, 'Member Type', $this->mapMembershipType($claim->philhealthMember->membership_type ?? ''));
        }
        $claimEl->appendChild($obs);

        // SYSTEM
        $sys = $this->doc->createElement('SYSTEM');
        $sys->setAttribute('pSystemName', $this->config['system']['name']);
        $sys->setAttribute('pSystemVersion', $this->config['system']['version']);
        $claimEl->appendChild($sys);
    }

    protected function addObservationValue(DOMElement $parent, string $particulars, string $value): void
    {
        $valEl = $this->doc->createElement('VALUE');
        $valEl->setAttribute('pParticulars', $particulars);
        $valEl->setAttribute('pValue', $value);
        $parent->appendChild($valEl);
    }

    protected function calculateClaimedDays(PhilhealthClaim $claim): string
    {
        if (!$claim->date_admitted || !$claim->date_discharged) return '1';
        $days = $claim->date_admitted->diffInDays($claim->date_discharged) + 1;
        return (string) max(1, $days);
    }

    protected function mapMembershipType(string $type): string
    {
        return match (strtolower($type)) {
            'employed', 's' => 'S',
            'self-employed', 'g' => 'G',
            'ofw', 'i' => 'I',
            'nhs', 'ns' => 'NS',
            'nhts', 'no' => 'NO',
            'lifetime', 'ps' => 'PS',
            'senior', 'pg' => 'PG',
            default => strtoupper(substr($type, 0, 2)) ?: 'NS',
        };
    }

    protected function mapClaimType(string $type): string
    {
        return match (strtoupper($type)) {
            'ACR' => 'CASE-RATE',
            'DRG' => 'CASE-RATE',
            'Z-BENEFIT', 'SPECIAL' => 'CASE-RATE',
            'OECB' => 'CASE-RATE',
            default => 'CASE-RATE',
        };
    }
}
