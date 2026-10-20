# PhilHealth eClaims 3.0 & DRG Shadow Billing Technical Specification

## 1. Regulatory Framework & Governance

PhilHealth's electronic claims processing has transitioned from legacy batch PDF uploads to **eClaims Version 3.0**, standardizing on machine-readable XML schemas and preparing accredited Health Care Institutions (HCIs) for the **Philippine Diagnosis-Related Groups (PHL-DRG)** provider payment mechanism.

| Circular / Advisory | Title & Subject | Regulatory Impact |
| :--- | :--- | :--- |
| **PhilHealth Circular No. 2023-0026** | *Mandatory Submission of Electronic Statement of Account (eSOA) as XML File* | Scanned PDF billing statements are obsolete. Claims without valid eSOA XML are returned to hospital (RTH). |
| **PhilHealth Circular No. 2024-0006** | *Implementation of Shadow Billing for the Transition to Diagnosis-Related Groups (DRG), Revision 1* | Established parallel claims processing: hospitals are reimbursed via All Case Rates (ACR) while submitting Claim Form 5 (CF5) for DRG cost-weight modeling. |
| **PhilHealth Circular No. 2026-0012** | *Implementation of Shadow Billing for the Transition to Diagnosis-Related Groups (DRG), Revision 2* | Mandatory CF5 XML submission for all Level 1–3 hospitals and infirmaries for all inpatient admissions. |
| **PhilHealth Circular No. 2020-0010** | *Electronic Claim Form 4 (eCF4) Data Specification* | Structured clinical abstract capture: Chief complaint, HPI, physical exam, vitals, course in ward, and itemized prescribed medications with PNDF drug codes. |
| **PhilHealth Circular No. 2024-0033 & 2025-0020** | *Outpatient Emergency Care Benefit (OECB / FBE) & Essential Emergency Care List (EECL Annex D)* | Covers 14 Core Emergency Presentations and fixed-fee EECL items (medicines, diagnostics, supplies, procedures) for outpatient emergency encounters within 24 hours. |

---

## 2. eClaims 3.0 XML Element Hierarchy & Schema

PhilHealth validates all incoming claim submissions against `eClaimsDef.dtd` (Document Type Definition) via the PhilHealth Electronic Claims Web Service (PECWS).

```
<eCLAIMS>
  ├── <eTRANSMITTAL (Transmittal Header: pHospCode, pTransmittalNo, pProcessDate, pTotalClaimCount, ...)>
  └── <CLAIM (Claim Node: pClaimNo, pPhilhealthClaimType, pHIClaimNo, pHIAdmissionDate, pHIDischargeDate, ...)>
        ├── <MEMBER (PhilHealth Member Data: pPIN, pMemberShipType, pMemberLastName, pMemberFirstName, ...)>
        ├── <PATIENT (Patient Demographics: pPatientIs, pPatientLastName, pPatientFirstName, pPatientGender, ...)>
        ├── <ICDCODES (Diagnostic ICD-10 Codes: pICDCode, pICDType [F=Final, S=Secondary])>
        ├── <PROCEDURE (Surgical/Medical Procedures: pRVSCode, pRVU, pOperationDate)>
        ├── <PACKAGE (Case Rate ACR: pCaseRateCode, pCaseRateAmount, pCaseRateHF, pCaseRatePF)>
        ├── <PARTICULARS (eSOA Itemized Billing Library per Circular 2023-0026)>
        │     ├── <ITEM pCategory="Drugs and Medicine" pItemCode="..." pItemDescription="..." pQuantity="..." pUnitPrice="..." pTotalAmount="..."/>
        │     ├── <ITEM pCategory="Laboratory and Diagnostics" .../>
        │     ├── <ITEM pCategory="Medical Supplies" .../>
        │     ├── <ITEM pCategory="Operating Room Fees" .../>
        │     └── <ITEM pCategory="Debit Room Charges" .../>
        ├── <CF4 (Clinical Abstract per Circular 2020-0010)>
        │     ├── <CLINICAL pHciCaseNo="..." pHciTransNo="..." .../>
        │     ├── <VITALS pBP="..." pHR="..." pRR="..." pTemp="..."/>
        │     └── <MEDICINES>
        │           ├── <MEDICINE pDrugCode="..." pGenericCode="..." pGenericName="..." pDosage="..." pRoute="..." pFrequency="..." pQuantity="..." pTotalCost="..."/>
        │           └── <!-- If no drugs given: pDrugCode="NOMED0000000000000000000000000" pGenericCode="NOMED" -->
        ├── <CF5 (DRG Shadow Billing per Circular 2026-0012)>
        │     └── <DRG_SHADOW_BILLING pDrgCode="..." pDrgDescription="..." pDrgWeight="..." pBaseRate="..." pHFAmount="..." pPFAmount="..." pTotalDRG="..." pActualACRAmount="..." pVariance="..."/>
        ├── <OBSERVATION (Name-Value Pairs for Clinical & Administrative Attributes)>
        │     ├── <VALUE pParticulars="Chief Complaint" pValue="..."/>
        │     └── <VALUE pParticulars="Patient Type" pValue="Inpatient|Outpatient|Emergency"/>
        └── <SYSTEM pSystemName="SITHEMR" pSystemVersion="1.0"/>
```

---

## 3. Data Entry Requirements (CF1 to CF5)

To pass validation without RTH (Returned to Hospital), an encounter must have complete records across all five claim sections:

### Claim Form 1 (CF1) — Member & Beneficiary Data
- **Member Information**: PhilHealth Identification Number (PIN - 12 digits), Membership Category (`S` = Employed Private, `G` = Employed Government, `I` = Indigent, `NS` = Individually Paying/Self-Employed, `PS` = Lifetime Member, `PG` = Senior Citizen), Full Name, Date of Birth, Mailing Address, Employer Name and TIN.
- **Patient Information**: Relationship to Member (`M` = Member, `S` = Spouse, `C` = Child, `P` = Parent), Patient PIN (if dependent is also a member), Full Name, Date of Birth, Gender, Civil Status.

### Claim Form 2 (CF2) — HCI & Clinical Charges Report
- **Confinement Period**: Date & Time of Admission, Date & Time of Discharge (format `MM-DD-YYYY` and `HH:MM`).
- **Diagnoses**: Admitting Diagnosis, Primary Discharge Diagnosis, Secondary & Tertiary Diagnoses (ICD-10 without periods, e.g., `I10`, `J189`).
- **Accreditation**: Attending Physician PhilHealth Accreditation Number (PAN), Surgeon PAN, Anesthesiologist PAN.
- **Financial Breakdown**: Total Actual Hospital Charges, Senior Citizen / PWD Discount, PhilHealth Benefit Deductions, Net Patient Out-of-Pocket / Copay.

### Claim Form 3 (CF3) — Special Clinical Records
- **Obstetrical History**: Gravida, Para, Full-term, Pre-term, Abortion, Living children (TPAL), LMP, EDC, Delivery Type, Maternal and Newborn Outcome.
- **Diagnostic Summaries**: Significant surgical findings, pathology reports, imaging results.

### Claim Form 4 (CF4) — Clinical Abstract
- **Reason for Admission**: Chief Complaint, History of Present Illness (HPI), Pertinent Past Medical History, Review of Systems (ROS).
- **Physical Examination & Vitals**: Blood Pressure (systolic/diastolic in mmHg), Heart Rate (bpm), Respiratory Rate (cpm), Temperature (°C).
- **Course in the Ward**: Daily physician notes, clinical response, complications.
- **Medications Administered**: Itemized list of all drugs dispensed with PNDF codes, route, dose, frequency, and total cost.

### Claim Form 5 (CF5) — DRG Shadow Billing Statement
- **Grouper Inputs**: Primary ICD-10, Secondary Comorbidities/Complications (CC/MCC), RVS Surgical Procedures, Patient Age, Gender, and Discharge Disposition (`Recovered`, `Improved`, `Transferred`, `Died`, `HAMA`).
- **DRG Outputs**: Derived DRG Code, Relative Weight (RW), Base Rate, Health Facility (HF) Share (60%), Professional Fee (PF) Share (40%), Total Shadow Bill, ACR Comparison, and Variance.

---

## 4. Shadow Billing & DRG Mechanics

Under PhilHealth Circulars 2024-0006 and 2026-0012:
1. **Parallel Payment Track**:
   - The hospital continues to be reimbursed based on the current **All Case Rates (ACR)** policy.
   - Concurrently, the hospital generates and attaches **Claim Form 5 (CF5)** XML.
2. **Formula**:
   $$\text{Total DRG Amount} = \text{Base Rate} \times \text{DRG Relative Weight (RW)}$$
   $$\text{Hospital Fee (HF)} = \text{Total DRG Amount} \times 0.60$$
   $$\text{Professional Fee (PF)} = \text{Total DRG Amount} \times 0.40$$
   $$\text{Variance} = \text{Total DRG Amount} - \text{ACR Reimbursed Amount}$$
3. **Variance Interpretation**:
   - **Positive Variance (Surplus)**: Under DRG, the hospital would earn more than under ACR (rewarding efficiency and complex case management).
   - **Negative Variance (Deficit)**: The hospital's DRG rate is lower than ACR, signaling resource optimization opportunities.

---

## 5. Pharmacy DRG & Medication Dispensing Standards

1. **Inpatient Drug Bundling**:
   - Under DRG, individual drugs administered during an inpatient stay are **bundled into the DRG Base Rate / HF fee**. No separate reimbursement claims are paid per tablet or vial.
   - However, **100% itemized transparency is legally mandatory** in eSOA XML and CF4 XML for cost-weight calculation.
2. **eSOA Category Mapping**:
   - All pharmacy dispensations must be tagged with `pCategory="Drugs and Medicine"`.
   - `pItemCode` must reflect the official PhilHealth Medicine Library code from the Philippine National Drug Formulary (PNDF).
3. **Mandatory CF4 Medicine Attributes**:
   - `pGenericName`: Generic title, salt, and strength (e.g., `Amoxicillin Trihydrate 500mg`).
   - `pBrandName`: Commercial brand or `Generic`.
   - `pDosage`: Strength and form (e.g., `500mg Capsule`).
   - `pRoute`: `Oral`, `Intravenous (IV)`, `Intramuscular (IM)`, `Subcutaneous (SC)`, `Inhalation`, `Topical`.
   - `pInstructionFrequency`: `Every 8 hours`, `Once daily`, `Twice daily`, `STAT`.
   - `pQuantity` & `pTotalCost`.
4. **The "No Medicine" (NOMED) Rule**:
   - If an encounter involved **no drug administration** (e.g. diagnostic admission or minor wound suturing without antibiotics), the system **MUST NOT pass blank fields or "N/A"**.
   - It must explicitly submit:
     - `pDrugCode="NOMED0000000000000000000000000"`
     - `pGenericCode="NOMED"`
     - `pGenericName="NONE"`
     - `pQuantity="0"`
     - `pTotalCost="0.00"`
5. **Outpatient Emergency (EECL) & YAKAP**:
   - **EECL (Annex D)**: 63 essential emergency medicines with statutory fixed fees (e.g., Paracetamol IV ₱150, Epinephrine ₱120).
   - **YAKAP**: 53 essential outpatient drugs bundled under primary care capitation.
