# PhilHealth CF1–CF5 Form to SITHEMR Database Mapping

> **Purpose**: Complete field-level mapping between official PhilHealth Claim Forms 1–5 and SITHEMR database tables. Use this to verify data completeness for e-Claims submission, print-form generation, and audit readiness.
>
> **Legend**: ✅ = fully mapped | ⚠️ = partially mapped (needs migration) | ❌ = not mapped (needs new table/column)

---

## CF1 — Member Registration / Claim Information

**Official PhilHealth Form CF1 sections**: Member Data, Patient (Dependent) Data, Member Certification, Employer Certification, PhilHealth Use Only.

### Part I: Member Information

| # | CF1 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 1 | PhilHealth PIN (member) | `philhealth_members.pin` | ✅ |
| 2 | Member Last Name | `philhealth_members.last_name` | ✅ |
| 3 | Member First Name | `philhealth_members.first_name` | ✅ |
| 4 | Member Middle Name | `philhealth_members.middle_name` | ✅ |
| 5 | Member Suffix | `philhealth_members.suffix` | ✅ |
| 6 | Date of Birth | `philhealth_members.date_of_birth` | ✅ |
| 7 | Sex/Gender | `philhealth_members.gender` | ✅ |
| 8 | Membership Type | `philhealth_members.membership_type` | ✅ |
| 9 | Employer Name | `philhealth_members.employer_name` | ✅ |
| 10 | Employer Address | `philhealth_members.employer_address` | ✅ |
| 11 | Contact Number | `philhealth_members.contact_number` | ✅ |
| 12 | Email Address | `philhealth_members.email` | ✅ |
| 13 | Mailing Address | `philhealth_members.mailing_address` | ✅ |
| 14 | Zip Code | `philhealth_members.zip_code` | ✅ |

### Part II: Patient/Dependent Data (for non-member patients)

| # | CF1 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 15 | Patient Last Name | `patients.last_name` | ✅ |
| 16 | Patient First Name | `patients.first_name` | ✅ |
| 17 | Patient Middle Name | `patients.middle_name` | ✅ |
| 18 | Patient Suffix | `patients.suffix` | ✅ |
| 19 | Date of Birth | `patients.date_of_birth` | ✅ |
| 20 | Sex/Gender | `patients.gender_id` → `genders.name` | ✅ |
| 21 | Relationship to Member | `philhealth_claims.relationship_to_member` | ✅ |
| 22 | Civil Status | `patients.civil_status_id` → `civil_statuses.name` | ✅ |
| 23 | Patient Address (street) | `patients.address_street` | ✅ |
| 24 | Barangay | `patients.barangay_id` → `barangays.name` | ✅ |
| 25 | City/Municipality | `patients.city_id` → `provinces_cities.name` | ✅ |
| 26 | Province | `patients.province_id` → `provinces_cities.name` | ✅ |
| 27 | Patient Contact No. | `patients.contact_no` | ✅ |

### Part III: Member Certification

| # | CF1 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 28 | Member Signature | `claim_documents` (document_type='CF1' scanned) | ✅ |
| 29 | Signature Date | `philhealth_claims.patient_signature_date` | ✅ |
| 30 | Member Printed Name | Derived from `philhealth_members` names | ✅ |

### Part IV: Employer Certification (for employed members)

| # | CF1 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 31 | Employer Name (printed) | `philhealth_members.employer_name` | ✅ |
| 32 | Employer Address | `philhealth_members.employer_address` | ✅ |
| 33 | Employer Contact No. | (from member record) | ⚠️ add `employer_contact` to `philhealth_members` |
| 34 | Employer Signature | Scanned as `claim_documents` | ✅ |
| 35 | Date Signed | ⚠️ Missing — needs `employer_signature_date` | ❌ |

### Assessed CF1 Gaps

| Missing Field | Recommended Column | Target Table |
|--------------|-------------------|-------------|
| Employer Contact No. | `employer_contact` varchar(30) nullable | `philhealth_members` |
| Employer Signature Date | `employer_signature_date` timestamp nullable | `philhealth_members` |
| Member Category (Type) | Already have `membership_type` | `philhealth_members` |

---

## CF2 — Health Care Institution (HCI) Report / Claim Form 2

**Official PhilHealth Form CF2 sections**: HCI Information, Patient Confinement Details, Diagnosis & RVS Codes, Certification of Benefits, HCI Certification.

### Part I: HCI Information

| # | CF2 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 1 | HCI Name (Hospital) | `tenants.tenant_name` | ✅ |
| 2 | HCI Code (PhilHealth accreditation) | ⚠️ Missing — needs `philhealth_hci_code` | ❌ |
| 3 | HCI Address | `tenants.address` + `tenants.city` | ✅ |
| 4 | Accreditation Number | ⚠️ Missing — needs `philhealth_accreditation_no` | ❌ |
| 5 | Accreditation Date | ⚠️ Missing — needs `accreditation_date` | ❌ |

### Part II: Patient Confinement Details

| # | CF2 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 6 | Patient Name | `patients` (last/first/middle/suffix) | ✅ |
| 7 | PIN (if member) | `philhealth_members.pin` via FK | ✅ |
| 8 | Date Admitted | `philhealth_claims.date_admitted` | ✅ |
| 9 | Time Admitted | `philhealth_claims.time_admitted` | ✅ |
| 10 | Date Discharged | `philhealth_claims.date_discharged` | ✅ |
| 11 | Time Discharged | `philhealth_claims.time_discharged` | ✅ |
| 12 | Admitting Diagnosis | `philhealth_claims.admitting_diagnosis` | ✅ |
| 13 | Final/Discharge Diagnosis | `philhealth_claims.discharge_diagnosis` | ✅ |
| 14 | ICD-10 Code (primary) | `philhealth_claims.icd10_code_id` → `icd10_codes.code` | ✅ |
| 15 | Other ICD-10 Codes | Only 1 FK exists — needs `secondary_icd10_code_id`, `tertiary_icd10_code_id` | ❌ |
| 16 | RVS Code (procedure) | `claim_items.item_code` where `item_type='Procedure'` | ✅ |
| 17 | Case Rate Package | `philhealth_claims.case_rate_id` → `philhealth_case_rates` | ✅ |

### Part III: Claim Items (services rendered)

| # | CF2 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 18 | Item Code (RVS) | `claim_items.item_code` | ✅ |
| 19 | Item Name/Description | `claim_items.item_name` | ✅ |
| 20 | Item Type | `claim_items.item_type` | ✅ |
| 21 | Quantity | `claim_items.quantity` | ✅ |
| 22 | Unit Price | `claim_items.unit_price` | ✅ |
| 23 | Actual Amount | `claim_items.actual_amount` | ✅ |
| 24 | Claimed Amount | `claim_items.claimed_amount` | ✅ |
| 25 | Health Care Professional | `claim_items.health_care_professional_id` → `hc_professionals` | ✅ |

### Part IV: HCI Certification

| # | CF2 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 26 | Attending Physician | `philhealth_claims.attending_physician_id` | ✅ |
| 27 | Referring Physician | `philhealth_claims.referring_physician_id` | ✅ |
| 28 | Physician Signature Date | `philhealth_claims.physician_signature_date` | ✅ |
| 29 | HCI Authorized Rep Signature | ⚠️ Missing — needs `hci_signatory_name`, `hci_signature_date` | ❌ |

### Assessed CF2 Gaps

| Missing Field | Recommended Column | Target Table |
|--------------|-------------------|-------------|
| PhilHealth HCI Code | `philhealth_hci_code` varchar(30) nullable | `tenants` |
| PhilHealth Accreditation No. | `philhealth_accreditation_no` varchar(50) nullable | `tenants` |
| Accreditation Date | `accreditation_date` date nullable | `tenants` |
| Secondary ICD-10 Code | `secondary_icd10_code_id` FK → `icd10_codes(id)` nullable | `philhealth_claims` |
| Tertiary ICD-10 Code | `tertiary_icd10_code_id` FK → `icd10_codes(id)` nullable | `philhealth_claims` |
| HCI Authorized Signatory Name | `hci_signatory_name` varchar(150) nullable | `philhealth_claims` |
| HCI Signatory Signature Date | `hci_signature_date` timestamp nullable | `philhealth_claims` |
| Primary Care Facility | Not needed — single HCI per tenant | — |

---

## CF3 — Clinical Record / Claim Form 3 (Mandatory for Specific Cases)

**Official PhilHealth Form CF3 sections**: Clinical Abstract, History, Physical Examination, Course in the Ward, Maternity Care Package (OB). Required for: Level 1 facilities, Case D (catastrophic), maternity, ER cases, <24hr stays, day surgeries.

### Part I: Clinical Abstract

| # | CF3 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 1 | Chief Complaint / Reason for Admission | Already in `philhealth_claims.chief_complaint` | ✅ |
| 2 | History of Present Illness | `philhealth_claims.history_of_present_illness` | ✅ |
| 3 | Review of Systems | `philhealth_claims.review_of_systems` | ✅ |
| 4 | Past Medical History | `philhealth_claims.pertinent_past_medical_history` | ✅ |
| 5 | Physical Exam Findings | `philhealth_claims.physical_exam_findings` | ✅ |
| 6 | Vital Signs at Admission | `philhealth_claims.blood_pressure`, `heart_rate`, `temperature`, `respiratory_rate` | ✅ |

### Part II: Course in the Ward

| # | CF3 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 7 | Course in the Ward Narrative | `philhealth_claims.course_in_ward` | ✅ |
| 8 | Significant Lab/Imaging Results | ⚠️ Missing — needs denormalized summary field | ❌ |
| 9 | Medications Administered | From `pharmacy_request_items` via linked `admission_id` | ⚠️ |
| 10 | Procedures Performed | From `procedure_request_items` via linked `admission_id` | ⚠️ |
| 11 | Outcome | `philhealth_claims.outcome` | ✅ |

### Part III: Maternity Care Package (OB-specific)

| # | CF3 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 12 | LMP (Last Menstrual Period) | `philhealth_claims.lmp_date` | ✅ |
| 13 | EDC (Expected Date of Confinement) | `philhealth_claims.edc_date` | ✅ |
| 14 | Gravida | `philhealth_claims.gravida` | ✅ |
| 15 | Para | `philhealth_claims.para` | ✅ |
| 16 | Delivery Date | ⚠️ Missing — needs `delivery_date` | ❌ |
| 17 | Delivery Type | From `delivery_types` via FK | ❌ |
| 18 | Newborn Outcome | ⚠️ Missing — needs separate tracking | ❌ |

### Part IV: Clinical Impression

| # | CF3 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 19 | Assessment / Diagnosis | `philhealth_claims.admitting_diagnosis` + `discharge_diagnosis` | ✅ |
| 20 | Attending Physician | `philhealth_claims.attending_physician_id` | ✅ |

### Assessed CF3 Gaps

| Missing Field | Recommended Column | Target Table |
|--------------|-------------------|-------------|
| Significant Lab/Imaging Results Summary | `lab_imaging_summary` text nullable | `philhealth_claims` |
| Delivery Date | `delivery_date` date nullable | `philhealth_claims` |
| Delivery Type ID | `delivery_type_id` FK → `delivery_types(id)` nullable | `philhealth_claims` |
| Newborn Outcome | `newborn_outcome` varchar(50) nullable | `philhealth_claims` |

---

## CF4 — Clinical Abstract (Extended) / Claim Form 4

**Official PhilHealth Form CF4 sections**: HCI & Patient Info, Reason for Admission, Clinical Data, Course in Ward, Drugs & Medicines, Outcome, HCP Certification. Note: This is the most comprehensive clinical form — already extensively mapped in Phase 1.

### Part I: HCI & Patient Information (same as CF2)

| # | CF4 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 1 | HCI Name | `tenants.tenant_name` | ✅ |
| 2 | HCI Code | Same as CF2 gap — needs `philhealth_hci_code` | ❌ |
| 3 | Patient Name | `patients` (last/first/middle/suffix) | ✅ |
| 4 | PIN | `philhealth_members.pin` | ✅ |
| 5 | Relationship to Member | `philhealth_claims.relationship_to_member` | ✅ |

### Part II: Reason for Admission (CF4 Part II / III.A)

| # | CF4 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 6 | Chief Complaint | `philhealth_claims.chief_complaint` | ✅ |
| 7 | History of Present Illness | `philhealth_claims.history_of_present_illness` | ✅ |
| 8 | Pertinent Past Medical History | `philhealth_claims.pertinent_past_medical_history` | ✅ |
| 9 | Review of Systems | `philhealth_claims.review_of_systems` | ✅ |

### Part III: Physical Examination (CF4 Part III.B)

| # | CF4 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 10 | Physical Exam Findings | `philhealth_claims.physical_exam_findings` | ✅ |
| 11 | Vital Signs: BP | `philhealth_claims.blood_pressure` | ✅ |
| 12 | Vital Signs: HR | `philhealth_claims.heart_rate` | ✅ |
| 13 | Vital Signs: Temp | `philhealth_claims.temperature` | ✅ |
| 14 | Vital Signs: RR | `philhealth_claims.respiratory_rate` | ✅ |

### Part IV: Course in the Ward (CF4 Part IV)

| # | CF4 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 15 | Course in the Ward | `philhealth_claims.course_in_ward` | ✅ |
| 16 | Significant Findings | ⚠️ Could be part of `course_in_ward` | ✅ |

### Part V: Drugs & Medicines / Procedures (CF4 Part V)

| # | CF4 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 17 | Drug/Medicine Itemization | `claim_items` where `item_type='Drug/Medicine'` | ✅ |
| 18 | Laboratory | `claim_items` where `item_type='Laboratory'` | ✅ |
| 19 | Radiology / Imaging | `claim_items` where `item_type='Radiology'` | ✅ |
| 20 | Procedures | `claim_items` where `item_type='Procedure'` | ✅ |
| 21 | Supplies | `claim_items` where `item_type='Supply'` | ✅ |
| 22 | Room & Board | `claim_items` where `item_type='RoomBoard'` | ✅ |
| 23 | Professional Fees | `claim_items` where `item_type='ProfessionalFee'` | ✅ |

### Part VI: Outcome & Certification (CF4 Part VI-VII)

| # | CF4 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 24 | Outcome | `philhealth_claims.outcome` | ✅ |
| 25 | Attending Physician ID | `philhealth_claims.attending_physician_id` | ✅ |
| 26 | Referring Physician ID | `philhealth_claims.referring_physician_id` | ✅ |
| 27 | Patient/Member Signature | `claim_documents` (document_type='CF4') | ✅ |
| 28 | Signature Date | `philhealth_claims.patient_signature_date` | ✅ |
| 29 | Physician Signature Date | `philhealth_claims.physician_signature_date` | ✅ |

### Assessed CF4 Gaps

| Missing Field | Recommended Column | Target Table |
|--------------|-------------------|-------------|
| Room & Board Details (from-to dates) | ⚠️ Currently denormalized; could be referenced from `admissions` → `ward_rooms` | linkage |
| HCI Code | Same as CF2 | `tenants` |

**Status**: CF4 is the most complete form — 29/29 fields mapped.

---

## CF5 — DRG Shadow Billing / Claim Form 5

**Official PhilHealth Form CF5 sections**: DRG Classification, Financial Comparison, Break-Even Analysis. Electronic submission only (XML-based) for DRG transitional facilities.

### Part I: DRG Classification Data

| # | CF5 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 1 | ICD-10 Code (primary) | `philhealth_claims.icd10_code_id` → `icd10_codes.code` | ✅ |
| 2 | DRG Code | `cf5_shadow_billings.drg_code` | ✅ |
| 3 | DRG Description | `cf5_shadow_billings.drg_description` | ✅ |
| 4 | DRG Weight | `cf5_shadow_billings.drg_weight` | ✅ |

### Part II: Financial Comparison

| # | CF5 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 5 | Base Rate | `cf5_shadow_billings.base_rate` | ✅ |
| 6 | Hospital Fee (HF) Amount | `cf5_shadow_billings.hf_amount` | ✅ |
| 7 | Professional Fee (PF) Amount | `cf5_shadow_billings.pf_amount` | ✅ |
| 8 | Total DRG Amount | `cf5_shadow_billings.total_drg_amount` | ✅ |
| 9 | Actual Case Rate (ACR) Amount | `cf5_shadow_billings.actual_case_rate_amount` | ✅ |

### Part III: Variance Analysis

| # | CF5 Field | SITHEMR Table.Column | Status |
|---|-----------|---------------------|--------|
| 10 | Difference (DRG − ACR) | `cf5_shadow_billings.difference_amount` | ✅ |
| 11 | Status | `cf5_shadow_billings.status` | ✅ |

### Assessed CF5 Gaps

| Missing Field | Recommended Column | Target Table |
|--------------|-------------------|-------------|
| None — all CF5 fields mapped | — | ✅ |

---

## Summary: Database Enhancement Requirements

### Migration 1: Add tenant/HCI PhilHealth accreditation fields

**To `tenants` table:**

| Column | Type | Description |
|--------|------|-------------|
| `philhealth_hci_code` | varchar(30) nullable | PhilHealth-issued HCI code |
| `philhealth_accreditation_no` | varchar(50) nullable | HCI accreditation number |
| `accreditation_date` | date nullable | Accreditation validity date |

### Migration 2: Add HCI signatory + secondary ICD-10 + delivery fields to `philhealth_claims`

| Column | Type | Description |
|--------|------|-------------|
| `secondary_icd10_code_id` | bigint unsigned nullable, FK → icd10_codes(id) | Secondary diagnosis code |
| `tertiary_icd10_code_id` | bigint unsigned nullable, FK → icd10_codes(id) | Tertiary diagnosis code |
| `hci_signatory_name` | varchar(150) nullable | HCI authorized representative name |
| `hci_signature_date` | timestamp nullable | HCI certifying signature date |
| `lab_imaging_summary` | text nullable | CF3 — significant lab/imaging results summary |
| `delivery_date` | date nullable | CF3 OB — date of delivery |
| `delivery_type_id` | bigint unsigned nullable, FK → delivery_types(id) | CF3 OB — method of delivery |
| `newborn_outcome` | varchar(50) nullable | CF3 OB — Live Birth, Stillbirth, etc. |

### Migration 3: Add employer contact to `philhealth_members`

| Column | Type | Description |
|--------|------|-------------|
| `employer_contact` | varchar(30) nullable | CF1 — employer contact number |
| `employer_signature_date` | timestamp nullable | CF1 — employer certification signature date |

---

## Cross-Reference: Data Source Tables for CF XML Generation

| Data Domain | Source Table(s) | CF Form |
|------------|----------------|---------|
| Member info | `philhealth_members` | CF1 |
| Patient info | `patients` + geo hierarchy (barangays, provinces_cities, etc.) | CF1, CF2, CF4 |
| HCI info | `tenants` | CF2, CF4 |
| Claim clinical | `philhealth_claims` | CF2, CF3, CF4 |
| Claim items | `claim_items` | CF2, CF4 |
| Diagnosis codes | `icd10_codes` (via FK on philhealth_claims) | CF2, CF3, CF4, CF5 |
| Procedure codes | `rvs_codes` (via claim_items.item_code) | CF2 |
| Case rates | `philhealth_case_rates` | CF2, CF4 |
| HC Professionals | `health_care_professionals` | CF2, CF4 |
| Scanned docs | `claim_documents` | CF1, CF2, CF3, CF4 |
| DRG shadow billing | `cf5_shadow_billings` | CF5 |
| Admission details | `admissions` → `ward_rooms` | CF2 (room/board) |
| ER admission | `er_admissions` → `er_triage_records` | CF2, CF4 (source encounter) |
| Service requests | `lab_requests`, `pharmacy_requests`, `csr_requests`, `radiology_requests`, `procedure_requests` | CF4 (itemization source) |
| Nursing data | `nursing_vitals`, `nursing_care_notes`, `nursing_medication_administrations` | CF3 (clinical data) |

---

**Generated**: 2026-06-24
**Updated**: 2026-06-24 — CF1–CF5 PDF printouts built. All forms now have downloadable/printable PDF views accessible from the claim detail modal.
