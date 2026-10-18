<?php

namespace App\Services;

use App\Models\PhilhealthClaim;
use SimpleXMLElement;
use Exception;

class PhilHealthXmlService
{
    /**
     * Generates a PhilHealth eClaims 3.0 compliant eSOA XML string.
     *
     * Uses the actual philhealth_claims schema with eager-loaded relationships:
     *  - patient, philhealthMember, icd10Code, caseRate,
     *    cf5ShadowBilling, claimBatch, attendingPhysician
     *
     * @param int $claimId
     * @param string $hciCode  Tenant's philhealth_hci_code for multi-tenant guard
     * @return string  Raw XML payload
     * @throws Exception
     */
    public function generateEsoaXml(int $claimId, string $hciCode): string
    {
        // 1. Fetch claim with all relationships needed for eSOA mapping
        $claim = PhilhealthClaim::withoutGlobalScopes()
            ->with([
                'patient',
                'philhealthMember',
                'icd10Code',
                'caseRate',
                'cf5ShadowBilling',
                'claimBatch',
                'attendingPhysician',
            ])
            ->where('id', $claimId)
            ->first();

        if (!$claim) {
            throw new Exception("Claim record not found: {$claimId}");
        }

        // 2. Multi-tenant guard — resolve HCI code from the claim's tenant
        $tenantHciCode = $claim->tenant->philhealth_hci_code ?? null;

        if ($tenantHciCode !== $hciCode) {
            throw new Exception(
                "Access denied: claim tenant HCI ({$tenantHciCode}) does not match requested HCI ({$hciCode})"
            );
        }

        // 3. Initialize XML document with PhilHealth DTD root node
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><CLAIM></CLAIM>');

        // 4. Map Header / Facility Data
        $xml->addChild('HCI_CODE', $this->esc($tenantHciCode));
        $xml->addChild('HCI_NAME', $this->esc($claim->tenant->tenant_name ?? ''));
        $xml->addChild('CLAIM_CODE', $this->esc($claim->claim_code));
        $xml->addChild('CLAIM_TYPE', $this->esc($claim->claim_type));

        // 5. Map Patient Demographic Block
        $patientNode = $xml->addChild('PATIENT');
        $patient     = $claim->patient;
        $patientNode->addChild('PATIENT_CODE', $this->esc($patient->patient_code ?? ''));
        $patientNode->addChild('FIRST_NAME', $this->esc($patient->first_name ?? ''));
        $patientNode->addChild('LAST_NAME', $this->esc($patient->last_name ?? ''));
        $patientNode->addChild('MIDDLE_NAME', $this->esc($patient->middle_name ?? ''));
        $patientNode->addChild('SUFFIX', $this->esc($patient->suffix ?? ''));
        $patientNode->addChild('DATE_OF_BIRTH', $patient->date_of_birth?->format('m/d/Y') ?? '');
        $patientNode->addChild('SEX', $this->esc($claim->gender ?? $patient->gender?->name ?? ''));
        $patientNode->addChild('CIVIL_STATUS', $this->esc($patient->civilStatus?->name ?? ''));
        $patientNode->addChild('ADDRESS', $this->esc($patient->address_street ?? ''));
        $patientNode->addChild('ZIP_CODE', $this->esc($patient->zip_code ?? ''));

        // 6. Map PhilHealth Member / Insurance Block
        $memberNode = $xml->addChild('PHILHEALTH_MEMBER');
        $member     = $claim->philhealthMember;
        $memberNode->addChild('PIN', $this->esc($member->pin ?? $patient->philhealth_pin ?? ''));
        $memberNode->addChild('MEMBERSHIP_TYPE', $this->esc($claim->membership_type ?? $member->membership_type ?? ''));
        $memberNode->addChild('EMPLOYER_NAME', $this->esc($member->employer_name ?? ''));
        $memberNode->addChild('RELATIONSHIP_TO_MEMBER', $this->esc($claim->relationship_to_member ?? ''));
        $memberNode->addChild('PATIENT_IS', $this->esc($claim->patient_is ?? ''));

        // 7. Map Encounter / Confinement Block
        $encounterNode = $xml->addChild('ENCOUNTER');
        $encounterNode->addChild('ENCOUNTER_TYPE', $this->esc($claim->encounter_type ?? ''));
        $encounterNode->addChild('DATE_ADMITTED', $claim->date_admitted?->format('m/d/Y') ?? '');
        $encounterNode->addChild('TIME_ADMITTED', $this->esc($claim->time_admitted ?? ''));
        $encounterNode->addChild('DATE_DISCHARGED', $claim->date_discharged?->format('m/d/Y') ?? '');
        $encounterNode->addChild('TIME_DISCHARGED', $this->esc($claim->time_discharged ?? ''));
        $encounterNode->addChild('OUTCOME', $this->esc($claim->outcome ?? ''));
        $encounterNode->addChild('DISPOSITION_CODE', $this->esc($claim->disposition_code ?? ''));

        // 8. Map Diagnosis Block
        $diagnosisNode = $xml->addChild('DIAGNOSIS');
        $diagnosisNode->addChild('ADMITTING_DIAGNOSIS', $this->esc($claim->admitting_diagnosis ?? ''));
        $diagnosisNode->addChild('DISCHARGE_DIAGNOSIS', $this->esc($claim->discharge_diagnosis ?? ''));
        $diagnosisNode->addChild('ICD10_CODE', $this->esc($claim->icd10Code?->code ?? ''));
        $diagnosisNode->addChild('ICD10_DESCRIPTION', $this->esc($claim->icd10Code?->description ?? ''));
        $diagnosisNode->addChild('SECONDARY_ICD10_CODE', $this->esc($claim->secondaryIcd10Code?->code ?? ''));
        $diagnosisNode->addChild('TERTIARY_ICD10_CODE', $this->esc($claim->tertiaryIcd10Code?->code ?? ''));

        // 9. Map Clinical / CF4 Block
        $clinicalNode = $xml->addChild('CLINICAL');
        $clinicalNode->addChild('CHIEF_COMPLAINT', $this->esc($claim->chief_complaint ?? ''));
        $clinicalNode->addChild('HISTORY_OF_PRESENT_ILLNESS', $this->esc($claim->history_of_present_illness ?? ''));
        $clinicalNode->addChild('PERTINENT_PAST_MEDICAL_HISTORY', $this->esc($claim->pertinent_past_medical_history ?? ''));
        $clinicalNode->addChild('PHYSICAL_EXAM_FINDINGS', $this->esc($claim->physical_exam_findings ?? ''));
        $clinicalNode->addChild('REVIEW_OF_SYSTEMS', $this->esc($claim->review_of_systems ?? ''));
        $clinicalNode->addChild('COURSE_IN_WARD', $this->esc($claim->course_in_ward ?? ''));
        $clinicalNode->addChild('LAB_IMAGING_SUMMARY', $this->esc($claim->lab_imaging_summary ?? ''));

        // 10. Map Vital Signs Block
        $vitalsNode = $xml->addChild('VITAL_SIGNS');
        $vitalsNode->addChild('BLOOD_PRESSURE', $this->esc($claim->blood_pressure ?? ''));
        $vitalsNode->addChild('HEART_RATE', $this->esc($claim->heart_rate ?? ''));
        $vitalsNode->addChild('TEMPERATURE', $this->esc($claim->temperature ?? ''));
        $vitalsNode->addChild('RESPIRATORY_RATE', $this->esc($claim->respiratory_rate ?? ''));

        // 11. Map Case Rate / Amounts Block
        $amountsNode = $xml->addChild('AMOUNTS');
        $amountsNode->addChild('TOTAL_ACTUAL_AMOUNT', number_format($claim->total_actual_amount, 2, '.', ''));
        $amountsNode->addChild('TOTAL_CLAIMED_AMOUNT', number_format($claim->total_claimed_amount, 2, '.', ''));
        $amountsNode->addChild('CASE_RATE_CODE', $this->esc($claim->caseRate?->rvs_code ?? ''));
        $amountsNode->addChild('CASE_RATE_DESCRIPTION', $this->esc($claim->caseRate?->description ?? ''));

        // 12. Map CF5 Shadow Billing / DRG Block (if present)
        $cf5 = $claim->cf5ShadowBilling->first();
        if ($cf5) {
            $cf5Node = $xml->addChild('CF5_BILLING');
            $cf5Node->addChild('DRG_CODE', $this->esc($cf5->drg_code ?? ''));
            $cf5Node->addChild('DRG_DESCRIPTION', $this->esc($cf5->drg_description ?? ''));
            $cf5Node->addChild('DRG_WEIGHT', number_format($cf5->drg_weight, 4, '.', ''));
            $cf5Node->addChild('BASE_RATE', number_format($cf5->base_rate, 2, '.', ''));
            $cf5Node->addChild('HF_AMOUNT', number_format($cf5->hf_amount, 2, '.', ''));
            $cf5Node->addChild('PF_AMOUNT', number_format($cf5->pf_amount, 2, '.', ''));
            $cf5Node->addChild('TOTAL_DRG_AMOUNT', number_format($cf5->total_drg_amount, 2, '.', ''));
            $cf5Node->addChild('ACTUAL_CASE_RATE_AMOUNT', number_format($cf5->actual_case_rate_amount, 2, '.', ''));
            $cf5Node->addChild('DIFFERENCE_AMOUNT', number_format($cf5->difference_amount, 2, '.', ''));
        }

        // 13. Map Physician Block
        $physicianNode = $xml->addChild('PHYSICIAN');
        $physicianNode->addChild('ATTENDING_PHYSICIAN', $this->esc($claim->attendingPhysician?->full_name ?? ''));
        $physicianNode->addChild('REFERRING_PHYSICIAN', $this->esc($claim->referringPhysician?->full_name ?? ''));

        // 14. Map CEWS / ECS Tracking Block
        $trackingNode = $xml->addChild('TRACKING');
        $trackingNode->addChild('TRACKING_NUMBER', $this->esc($claim->tracking_number ?? ''));
        $trackingNode->addChild('ECS_TRACKING_NO', $this->esc($claim->ecs_tracking_no ?? ''));
        $trackingNode->addChild('ACKNOWLEDGMENT_CODE', $this->esc($claim->acknowledgment_code ?? ''));
        $trackingNode->addChild('VOUCHER_NUMBER', $this->esc($claim->voucher_number ?? ''));

        // 15. Map OB Block (if applicable)
        if ($claim->lmp_date || $claim->edc_date || $claim->delivery_date) {
            $obNode = $xml->addChild('OB');
            $obNode->addChild('LMP_DATE', $claim->lmp_date?->format('m/d/Y') ?? '');
            $obNode->addChild('EDC_DATE', $claim->edc_date?->format('m/d/Y') ?? '');
            $obNode->addChild('GRAVIDA', $claim->gravida ?? '');
            $obNode->addChild('PARA', $claim->para ?? '');
            $obNode->addChild('DELIVERY_DATE', $claim->delivery_date?->format('m/d/Y') ?? '');
            $obNode->addChild('DELIVERY_TYPE', $this->esc($claim->deliveryType?->name ?? ''));
            $obNode->addChild('NEWBORN_OUTCOME', $this->esc($claim->newborn_outcome ?? ''));
        }

        return $xml->asXML();
    }

    /**
     * Escape a value for safe XML embedding.
     */
    private function esc(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_XML1, 'UTF-8');
    }
}
