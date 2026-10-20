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
            'cf5ShadowBilling', 'attendingPhysician',
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
            'claims.claimProcedures', 'claims.cf5ShadowBilling', 'claims.attendingPhysician',
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

        // PARTICULARS (eSOA Itemized billing per Circular 2023-0026)
        $this->appendParticulars($claimEl, $claim);

        // MEDICINES (CF4 Pharmacy items or NOMED tag per Circular 2020-0010)
        $this->appendMedicines($claimEl, $claim);

        // DRG_SHADOW_BILLING (CF5 DRG statement per Circular 2024-0006 & 2026-0012)
        $this->appendDrgShadowBilling($claimEl, $claim);

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
        if ($claim->attendingPhysician) {
            $this->addObservationValue($obs, 'Attending Physician', "{$claim->attendingPhysician->last_name}, {$claim->attendingPhysician->first_name}");
            if ($claim->attendingPhysician->license_number) {
                $this->addObservationValue($obs, 'Attending Physician License', $claim->attendingPhysician->license_number);
            }
        }
        if ($claim->blood_pressure) {
            $this->addObservationValue($obs, 'Vital BP', $claim->blood_pressure);
        }
        if ($claim->heart_rate) {
            $this->addObservationValue($obs, 'Vital HR', $claim->heart_rate);
        }
        if ($claim->respiratory_rate) {
            $this->addObservationValue($obs, 'Vital RR', $claim->respiratory_rate);
        }
        if ($claim->temperature) {
            $this->addObservationValue($obs, 'Vital Temp', $claim->temperature);
        }
        $claimEl->appendChild($obs);

        // SYSTEM
        $sys = $this->doc->createElement('SYSTEM');
        $sys->setAttribute('pSystemName', $this->config['system']['name']);
        $sys->setAttribute('pSystemVersion', $this->config['system']['version']);
        $claimEl->appendChild($sys);
    }

    protected function appendParticulars(DOMElement $claimEl, PhilhealthClaim $claim): void
    {
        $items = $claim->items;
        if (!$items || $items->isEmpty()) {
            return;
        }

        $particularsEl = $this->doc->createElement('PARTICULARS');
        foreach ($items as $item) {
            $itemEl = $this->doc->createElement('ITEM');
            $itemEl->setAttribute('pItemCode', $item->item_code ?: 'ITEM');
            $itemEl->setAttribute('pItemDescription', $item->item_name ?: ($item->description ?: 'Hospital Item'));
            $itemEl->setAttribute('pItemCategory', $this->mapItemCategory($item->item_type ?? 'Others'));
            $itemEl->setAttribute('pQuantity', (string) (float) $item->quantity);
            $itemEl->setAttribute('pUnitPrice', number_format((float) $item->unit_price, 2, '.', ''));
            $itemEl->setAttribute('pTotalActualAmount', number_format((float) $item->actual_amount, 2, '.', ''));
            $itemEl->setAttribute('pTotalClaimedAmount', number_format((float) $item->claimed_amount, 2, '.', ''));
            $itemEl->setAttribute('pCoverageType', 'PHILHEALTH');
            $particularsEl->appendChild($itemEl);
        }
        $claimEl->appendChild($particularsEl);
    }

    protected function appendMedicines(DOMElement $claimEl, PhilhealthClaim $claim): void
    {
        $drugItems = $claim->items ? $claim->items->filter(fn($i) => in_array($i->item_type, ['Drug/Medicine', 'Medicine', 'Drugs'])) : collect();
        $medsEl = $this->doc->createElement('MEDICINES');

        if ($drugItems->isNotEmpty()) {
            foreach ($drugItems as $drug) {
                $medEl = $this->doc->createElement('MEDICINE');
                $medEl->setAttribute('pDrugCode', $drug->item_code ?: 'DRUG001');
                $medEl->setAttribute('pGenericName', $drug->item_name ?: 'Prescribed Drug');
                $medEl->setAttribute('pBrandName', $drug->item_name ?: 'Prescribed Drug');
                $medEl->setAttribute('pPreparation', $drug->description ?: 'Standard');
                $medEl->setAttribute('pQuantity', (string) (float) $drug->quantity);
                $medEl->setAttribute('pUnitPrice', number_format((float) $drug->unit_price, 2, '.', ''));
                $medEl->setAttribute('pTotalCost', number_format((float) $drug->actual_amount, 2, '.', ''));
                $medsEl->appendChild($medEl);
            }
        } else {
            // PhilHealth eCF4 Circular 2020-0010 mandatory NOMED sentinel tag
            $medEl = $this->doc->createElement('MEDICINE');
            $medEl->setAttribute('pDrugCode', 'NOMED');
            $medEl->setAttribute('pGenericName', 'NONE');
            $medEl->setAttribute('pBrandName', 'NONE');
            $medEl->setAttribute('pPreparation', 'NONE');
            $medEl->setAttribute('pQuantity', '0');
            $medEl->setAttribute('pUnitPrice', '0.00');
            $medEl->setAttribute('pTotalCost', '0.00');
            $medsEl->appendChild($medEl);
        }

        $claimEl->appendChild($medsEl);
    }

    protected function appendDrgShadowBilling(DOMElement $claimEl, PhilhealthClaim $claim): void
    {
        $shadow = $claim->cf5ShadowBilling?->first();
        if (!$shadow && ($claim->claim_type === 'DRG' || $claim->icd10Code || $claim->icd10_code_id)) {
            try {
                $shadow = app(\App\Services\Philhealth\Cf5ShadowBillingService::class)->calculate($claim);
            } catch (\Throwable $e) {
                $shadow = null;
            }
        }

        if ($shadow) {
            $drgEl = $this->doc->createElement('DRG_SHADOW_BILLING');
            $drgEl->setAttribute('pDrgCode', $shadow->drg_code ?? 'DRG-999');
            $drgEl->setAttribute('pDrgDescription', $shadow->drg_description ?? 'Unclassified DRG');
            $drgEl->setAttribute('pBaseRate', number_format((float) $shadow->base_rate, 2, '.', ''));
            $drgEl->setAttribute('pRelativeWeight', number_format((float) $shadow->drg_weight, 4, '.', ''));
            $drgEl->setAttribute('pTotalDrgAmount', number_format((float) $shadow->total_drg_amount, 2, '.', ''));
            $drgEl->setAttribute('pHospitalFee', number_format((float) $shadow->hf_amount, 2, '.', ''));
            $drgEl->setAttribute('pProfessionalFee', number_format((float) $shadow->pf_amount, 2, '.', ''));
            $drgEl->setAttribute('pActualCaseRate', number_format((float) $shadow->actual_case_rate_amount, 2, '.', ''));
            $drgEl->setAttribute('pVarianceAmount', number_format((float) $shadow->difference_amount, 2, '.', ''));
            $drgEl->setAttribute('pStatus', $shadow->status ?? 'Calculated');
            $claimEl->appendChild($drgEl);
        }
    }

    protected function mapItemCategory(string $type): string
    {
        return match (strtoupper($type)) {
            'DRUG/MEDICINE', 'MEDICINE', 'DRUGS' => 'DRUGS_MEDICINE',
            'SUPPLY', 'CSR' => 'SUPPLIES',
            'LABORATORY', 'LAB' => 'LABORATORY',
            'RADIOLOGY', 'RAD', 'X-RAY', 'ULTRASOUND' => 'RADIOLOGY',
            'PROCEDURE', 'PROCEDURES' => 'PROCEDURES',
            'ROOMBOARD', 'ROOM & BOARD', 'ACCOMMODATION' => 'ROOM_BOARD',
            'PROFESSIONALFEE', 'PROF. FEE', 'PF', 'DOCTORFEE' => 'PROFESSIONAL_FEE',
            default => 'OTHERS',
        };
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
