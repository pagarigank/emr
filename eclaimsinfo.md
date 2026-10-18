# iHOMIS Plus — eClaims Server Communication Architecture

## 1. API Endpoints & Data Flow

### 1.1 Patient Selection → Server Calls

```
MemberClaims.php (View)
  │
  ├── POST /Patient/PatientAdvanceSearch       → Search patients
  │
  ├── POST /MedicalRecords/visitHistory/{hpercode} → Get visit log
  │     Returns: enccode, admdate, admtime, disdate, distime, toecode
  │     Query: PatientRecordModel->visitLogList()
  │       henctr LEFT JOIN hadmlog/herlog/hopdlog
  │       CASE WHEN toecode selects which log table
  │
  ├── POST /Philhealth/PatientLogInfo           → Get encounter details
  │     Payload: { enccode, toecode }
  │     Returns: row from hadmlog/herlog/hopdlog depending on toecode
  │     Model: PhilhealthModel->PatientLogInfo($enccode, $toecode)
  │       toecode='ADMISSION'     → hadmlog
  │       toecode='EMERGENCY'...  → herlog
  │       else                    → hopdlog
  │
  ├── POST /Philhealth/existMemberClaim         → Check if claim exists
  │
  ├── POST /Philhealth/MemberClaimInfo          → Get existing claim data
  │     Query: hpatcon + hpatcon1 + henctr + hperson + hadmlog/herlog/hopdlog
  │     CASE WHEN toecode picks admdate from correct log table
  │
  ├── POST /Philhealth/getEncounterDatetime     → Get encounter timestamps
  │     Payload: { enccode (base64) }
  │     Returns: admdate, disdate (from correct log table per toecode)
  │
  ├── POST /Philhealth/getPBEFData              → Get PBEF verification data
  │     SOAP call to PhilHealth
  │
  └── POST /Philhealth/saveMemberClaim          → Save/Update claim (CF1-CF5)
        Payload: { enccode, accreno, formData... }
        Inserts/Updates:
          → hpatcon (main claim)
          → hpatcon1 (extended claim data)
          → hphicdischargediagnosis (diagnoses/procedures)
          → hprofserv (professional services)
```

### 1.2 Claim Listing (Datatable)

```
Philhealth/MemberClaims/{base64_phicnum}       → POST
  Model: PhilhealthModel->MemberClaims($phicnum)
  Query (Datatables):
    SELECT hpatcon.enccode, hpatcon.accreno, hpatcon.hpercode,
           hpatcon.prdteadm, hpatcon.prdtedis,
           patientname, reltomem, pPatientPIN, pfacility,
           nbb, oecbclaim, ptrackingnumber
    FROM hpatcon
    INNER JOIN hphiclog              ON hphiclog.phicnum = hpatcon.memphicnum
    INNER JOIN henctr                ON henctr.enccode = hpatcon.enccode
    INNER JOIN hperson               ON hperson.hpercode = henctr.hpercode
    LEFT JOIN hhospaccreditation     ON hhospaccreditation.paccreno = hpatcon.accreno
    LEFT JOIN hadmlog/herlog/hopdlog ON enccode
    WHERE hpatcon.memphicnum = ?
```

### 1.3 Claim Log Display (per patient)

```
Philhealth/ClaimLog/{hpercode}                 → GET
  Model: PhilhealthModel->ClaimLog($hpercode)
  Query (Datatables):
    SELECT henctr.enccode, hperson.hpercode,
           DATE_FORMAT(hpatcon1.padmissiondatetime,'%m/%d/%Y') as admdate,
           TIME_FORMAT(hpatcon1.padmissiondatetime,'%h:%i %p') as admtime,
           DATE_FORMAT(hpatcon1.pdischargedatetime,'%m/%d/%Y') as disdate,
           TIME_FORMAT(hpatcon1.pdischargedatetime,'%h:%i %p') as distime,
           CASE WHEN toecode='ADM' THEN 'ADMISSION'
                WHEN toecode IN ('ER','ERADM') THEN 'EMERGENCY ROOM'
                ELSE 'OUT-PATIENT DEPARTMENT' END as toecode
    FROM henctr
    INNER JOIN hpatcon  ON hpatcon.enccode = henctr.enccode
    INNER JOIN hpatcon1 ON hpatcon1.enccode = henctr.enccode
    INNER JOIN hperson  ON hperson.hpercode = henctr.hpercode
    LEFT JOIN hadmlog/herlog/hopdlog ON enccode
    LEFT JOIN hencdiag ON enccode AND tdcode='FINDX' AND primediag='Y'
    WHERE hperson.hpercode = ?
      AND hpatcon.accreno <> ''
    ORDER BY henctr.enctime DESC
```

**Note:** Dates read from `hpatcon1.padmissiondatetime` / `.pdischargedatetime` — NOT from `hadmlog`

---

## 2. Claim Status Flow (Server Round-trip)

### 2.1 Statuses Tracked

| Status | Meaning | Storage Table |
|--------|---------|---------------|
| `IN PROCESS` | Being processed | `philhealth_claim_in_process` |
| `RETURN` | Claim returned with deficiencies | `philhealth_claim_return` + `philhealth_claim_return_requirement` |
| `DENIED` | Claim denied | `philhealth_claim_denied` |
| `WITH CHEQUE` / `WITH VOUCHER` / `VOUCHERING` | Payment issued | `philhealth_claim_payee` |
| (others parsed) | Generic trail | `phicclaimsstatus` |

### 2.2 Status Processing Flow

```
User submits claim (CF1-CF5 complete)
  → Philhealth/saveMemberClaim
  → PhilhealthService->submitClaim()
    → Encrypts payload using cipherkey + passphrase
    → SOAP request to DOH_WEBSERVICE_ECLAIMS_TRANSMITTAL_URL
    → Receives XML response
    → EClaimSubmissionService->saveToClaimStatus(response)
      ├── Parses <TRAIL.PROCESS>     → philhealth_claim_in_process
      ├── Parses <RETURN.DEFECTS>    → philhealth_claim_return
      │                                  philhealth_claim_return_requirement
      │                                  Resets attachment status for re-upload
      ├── Parses <DENIED>            → philhealth_claim_denied
      ├── Parses <CHEQUE>/<VOUCHER>  → philhealth_claim_payee
      ├── Updates hphicclaimmap.pStatus
      └── Refreshes claim list display
```

### 2.3 Status Models (all obfuscated, decode with decode_obfuscated.php)

| Model File | Table |
|------------|-------|
| `PhilhealthClaimStatus.php` | `philhealth_claim_status` / `phicclaimsstatus` |
| `PhilhealthClaimMap.php` | `hphicclaimmap` |
| `PhilhealthClaimReturn.php` | `philhealth_claim_return` |
| `PhilhealthClaimReturnRequirement.php` | `philhealth_claim_return_requirement` |
| `PhilhealthClaimInProcess.php` | `philhealth_claim_in_process` |
| `PhilhealthClaimDenied.php` | `philhealth_claim_denied` |
| `PhilhealthClaimPayee.php` | `philhealth_claim_payee` |
| `PhilhealthClaimAttachment.php` | `philhealth_claim_attachment` |
| `PhilhealthClaimTransmittal.php` | `philhealth_claim_transmittal` |
| `PhilhealthClaimVoucher.php` | `philhealth_claim_voucher` |
| `PhilhealthClaimReceipt.php` | `philhealth_claim_receipt` |

---

## 3. Encryption Architecture

### 3.1 Key Sources

| Key | Source | Type | Used For |
|-----|--------|------|----------|
| `APP_KEY` | `.env` (currently **EMPTY**) | 44-char base64 | Fallback: `uhcis.php` config |
| `PASSPHRASE` | `.env` | String `SlPumlNshDOHFevEHzbw` | SOAP payload encryption |
| `cipherkey` | `hhospaccreditation.cipherkey` (DB) | Per-hospital | Per-transaction eClaims encryption |
| Hardcoded fallback | `encryption.php` | `P+Mn0CCB1EEymb2z0OXUK1ltFe7ebEpj5IuO0DIXmoo=` | Used when APP_KEY is empty |

### 3.2 Encryption Layers

```
Layer 1: Crypt Library (application/libraries/Crypt.php)
  ──────────────────────────────────────────────────
  Cipher:     aes-256-cbc
  Key source: config('application_key') from uhcis.php
  Output:     base64(json_encode({
                iv:    base64(iv),
                value: base64(encrypted_data),
                mac:   hash_hmac('sha256', iv.value, key)
              }))

Layer 2: PhicEncryption Library (application/libraries/PhicEncryption.php)
  ─────────────────────────────────────────────────────────────────────────
  Cipher:     aes-256-cbc
  Key source: PASSPHRASE from .env (passed as function parameter)
  Output:     base64(json_encode({
                iv:   base64(iv),
                data: base64(openssl_encrypt(data, 'aes-256-cbc', passphrase, 0, iv))
              }))

Layer 3: eClaims Document Encryption (third_party/eClaimsDocEncryption/)
  ────────────────────────────────────────────────────────────────────────
  CF4DocEncryptor / CF5DocEncryptor — encrypts XML payload
  PhilHealthEClaimsDocEncryptor     — encrypts PDF/image attachments
  Public Key:   @Files/Input/pnpki_philhealth_eclaims_auth_cert.pem
```

### 3.3 Where Keys Are Stored in DB

**Table: `hhospaccreditation`**

| Column | Purpose |
|--------|---------|
| `paccreno` | PK — Accreditation Number |
| `cipherkey` | **Encryption key for eClaims SOAP** |
| `phospitalcode` | PhilHealth Hospital Code (PMCC) |
| `pusername` | eClaims API username |
| `ppassword` | eClaims API password |
| `pfacility` | HOSP / ABTC / DOTS / MCPP / PCBP / CISU / LABS |
| `pstatus` | A = Active |

---

## 4. Key Database Tables for Server Communication

### 4.1 PhilHealth-Specific Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| **`hpatcon`** | Main claim record | `enccode`, `memphicnum`, `accreno`, `prdteadm`, `prdtedis`, `reltomem`, `nbb`, `oecbclaim`, `ptrackingnumber` |
| **`hpatcon1`** | Extended claim data (all CF forms) | `enccode`, `padmissiondatetime`, `pdischargedatetime`, `pdisposition`, `firstcase`, `secondcase`, all PHIC-specific fields |
| **`hphiclog`** | PHIC member master | `phicnum`, `hpercode`, `memlast`, `memfirst`, `memmid`, `typemem` |
| **`hphicdischargediagnosis`** | Diagnoses + procedures for claim | `enccode`, `diag_cnt`, `pDischargeDiagnosis`, `pICDCode`, `pRelatedProcedure`, `pRVSCode`, `pProcedureDate`, `pspecialcon` |
| **`hprofserv`** | Professional services (physicians) | `enccode`, `licno`, `profctr`, `doctype`, `pinclude`, `pdoctoraccrecode` |
| **`hhospaccreditation`** | Hospital accreditation + encryption keys | `paccreno`, `cipherkey`, `phospitalcode`, `pusername`, `ppassword`, `pfacility` |
| **`hphicclaimmap`** | Claim status mapping | `enccode`, `pStatus` |
| **`phicclaimsstatus`** | Raw status log from server | Parsed eClaims server response |
| **`philhealth_claim_return`** | Returned claim deficiencies | Defects from server |
| **`philhealth_claim_return_requirement`** | Return requirements | Specific requirements per return |
| **`philhealth_claim_in_process`** | Processing stages | `<TRAIL.PROCESS>` parsed data |
| **`philhealth_claim_denied`** | Denial reasons | Denial info from server |
| **`philhealth_claim_payee`** | Payment/cheque info | Payee details for WITH CHEQUE status |
| **`philhealth_claim_attachment`** | Uploaded attachments | Encrypted files sent to DOH cloud |
| **`philhealth_claim_transmittal`** | Batch transmittal records | Claim submission batches |

### 4.2 Encounter Tables (linked via `enccode`)

| Table | Type | Key Columns |
|-------|------|-------------|
| **`henctr`** | Master encounter | `enccode`, `hpercode`, `toecode` (ADM/OPD/ER/ERADM/OPDAD), `encdate`, `enctime`, `encstat` |
| **`hadmlog`** | Admission | `enccode`, `admdate`, `admtime`, `disdate`, `distime`, `admtxt`, `disdiag`, `dispcode`, `condcode`, `casenum` |
| **`herlog`** | Emergency | `enccode`, `erdate`, `ertime`, `erdtedis`, `ertmedis`, `ertxt`, `dispcode`, `condcode`, `casenum` |
| **`hopdlog`** | Outpatient | `enccode`, `opddate`, `opdtime`, `opddtedis`, `opdtmedis`, `opdtxt`, `dispcode`, `condcode`, `casenum` |
| **`hperson`** | Patient master | `hpercode`, name fields, demographics |

### 4.3 Key Relationship

```
hphiclog (PHIC member)
  │ phicnum
  ▼
hpatcon (claim) ── enccode ──► henctr (encounter) ── hpercode ──► hperson (patient)
  │ accreno                          │ toecode
  ▼                                  ▼
hhospaccreditation          hadmlog / herlog / hopdlog (per toecode)
  │ cipherkey, username,
  │ password
  ▼
Encrypted SOAP → DOH eClaims Server
```

---

## 5. Configuration

### .env File

```
APP_KEY=                              (should be 44-char base64, currently EMPTY)
PASSPHRASE=SlPumlNshDOHFevEHzbw
ECWSVERSION=ECWS3.0
DOH_WEBSERVICE_ECLAIMS_TRANSMITTAL_URL=https://ihomis-eclaims.doh.gov.ph/ihomisplus3.0/webservice.php?wsdl
DOH_CLOUD_UPLOAD=https://ihomis-eclaimsdoc.doh.gov.ph/uploadclaimattachment.php
DOH_CLOUD_STORAGE=https://ihomis-eclaimsdoc.doh.gov.ph/attachments
```

### Config Files

| File | Key Settings |
|------|-------------|
| `config/encryption.php` | `cipher = aes-256-cbc` |
| `config/uhcis.php` | `application_key`, eClaims URLs, version |
| `config/database.php` | DB connection (reads from env) |

---

## 6. Obtained Code (Decoders)

Use `decode_obfuscated.php` to read any encrypted file:

```bash
php decode_obfuscated.php application/models/PhilhealthModel.php
php decode_obfuscated.php application/libraries/PhilhealthService.php
php decode_obfuscated.php application/controllers/Philhealth.php
php decode_obfuscated.php application/libraries/EClaimSubmissionService.php
php decode_obfuscated.php application/libraries/Crypt.php
php decode_obfuscated.php application/libraries/PhicEncryption.php
```

---

## 7. Files You Need for Rebuild

| Component | Path | Purpose |
|-----------|------|---------|
| Controller | `application/controllers/Philhealth.php` | All claim API endpoints |
| Model | `application/models/PhilhealthModel.php` | All claim DB queries |
| Service | `application/libraries/PhilhealthService.php` | Business logic + SOAP calls |
| eClaims Service | `application/libraries/EClaimSubmissionService.php` | Status handling from server response |
| Encryption | `application/libraries/Crypt.php` | AES-256 encryption wrapper |
| PHIC Encryption | `application/libraries/PhicEncryption.php` | PHIC-specific encrypt/decrypt |
| View (Member Claims) | `application/views/Philhealth/MemberClaims.php` | Main UI (JS AJAX calls) |
| View (CF4) | `application/views/Philhealth/_claim_form4.php` | Claim form template |
| View (CF3) | `application/views/Philhealth/_claim_form3.php` | Claim form template |
| View (CF2) | `application/views/Philhealth/ClaimForm2.php` | Claim form template |
| Patient Record Model | `application/models/PatientRecordModel.php` | Visit history queries |
| Config | `application/config/encryption.php` | Encryption config |
| Helpers | `application/helpers/fullname_helper.php` | Name formatting utility |
| JS | `assets/scripts/philhealth/` | Client-side validation/member info scripts |
| .env | `.env` | Environment configuration |


---

## 8. Rebuilding with Your Own Tables, Fields & API

Design your own schema and API service from scratch, using the eClaims flow as the requirement — not the old table structures.

### 8.1 Core Domain Model

Define your own entities and relationships. These are the **concepts** you need, not the old table names:

```
Member        ←→ Claim        ←→ ClaimDiagnosis
              ↕                    ↕
         ClaimExtended      ClaimProcedure
              ↕
         Accreditation (cipherkey, credentials)
              ↕
         eClaims Server (SOAP → response → status)
```

### 8.2 Minimum Data Needed per Domain

#### Member (PHIC Member)

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `phic_number` | string, unique | 12-digit PHIC ID |
| `member_last_name` | string, required | |
| `member_first_name` | string, required | |
| `member_middle_name` | string, nullable | |
| `member_suffix` | string, nullable | Jr., Sr., etc |
| `member_type` | string | e.g. MEMBER, DEPENDENT |
| `date_of_birth` | date | |
| `sex` | string | M/F |
| `civil_status` | string, nullable | |
| `spouse_last_name` | string, nullable | |
| `spouse_first_name` | string, nullable | |
| `spouse_middle_name` | string, nullable | |
| `employer_name` | string, nullable | |
| `address_street` | string, nullable | |
| `address_province` | string, nullable | |
| `address_city` | string, nullable | |
| `address_barangay` | string, nullable | |
| `address_zip` | string, nullable | |
| `contact_number` | string, nullable | |
| `email` | string, nullable | |

#### Patient (your master patient)

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `patient_id` | string, unique | Your internal ID |
| `last_name` | string, required | |
| `first_name` | string, required | |
| `middle_name` | string, nullable | |
| `suffix` | string, nullable | |
| `date_of_birth` | date | |
| `sex` | string | |

#### Encounter / Visit

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `encounter_code` | string, unique | Your internal encounter ID |
| `patient_id` | FK → patients | |
| `encounter_type` | string | inpatient / emergency / outpatient |
| `admission_date` | datetime, nullable | |
| `admission_time` | time, nullable | |
| `discharge_date` | datetime, nullable | |
| `discharge_time` | time, nullable | |
| `admitting_diagnosis` | text, nullable | |
| `discharge_diagnosis` | text, nullable | |
| `disposition` | string, nullable | recovered/improved/expired/transferred/ama |
| `disposition_code` | string, nullable | R/I/E/T/H/A |

#### Claim

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `encounter_code` | string, unique | Link to your encounter |
| `member_id` | FK → members | |
| `patient_id` | FK → patients | |
| `accreditation_id` | FK → accreditations | |
| `relationship_to_member` | string | self/spouse/child/parent |
| `patient_pin` | string, nullable | |
| `admission_date_period` | date, nullable | Period of admission (for claim) |
| `discharge_date_period` | date, nullable | Period of discharge |
| `no_balance_billing` | boolean | |
| `oecb_claim` | boolean | |
| `tracking_number` | string, nullable | CEWS tracking |
| `status` | string | draft / submitted / in_process / returned / denied / paid |
| `created_by` | FK → users | |

#### Claim Diagnosis & Procedure

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `claim_id` | FK → claims | |
| `sequence` | integer | 1, 2, 3... |
| `diagnosis_description` | text, nullable | |
| `icd_code` | string, nullable | ICD-10 |
| `procedure_description` | text, nullable | |
| `rvs_code` | string, nullable | Case rate code |
| `procedure_date` | date, nullable | |
| `laterality` | string, nullable | Left / Right / Bilateral |
| `special_consideration` | string, nullable | cataract / zbenefit / maternal / tb-dots / abite / newborn / hiv |

#### Claim Professional Service

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `claim_id` | FK → claims | |
| `license_number` | string | |
| `doctor_type` | string | attending / consultant |
| `include_in_claim` | boolean | |
| `doctor_accreditation_code` | string, nullable | |
| `with_co_pay` | boolean | |

#### Claim Payee

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `claim_id` | FK → claims | |
| `payee_name` | string | |
| `payee_type` | string | HCI / PROFESSIONAL |
| `amount` | decimal | |
| `cheque_number` | string, nullable | |
| `cheque_date` | date, nullable | |
| `voucher_number` | string, nullable | |
| `voucher_date` | date, nullable | |
| `status` | string | with_cheque / with_voucher / vouchered |

#### Claim Status Log

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `claim_id` | FK → claims | |
| `status` | string | in_process / returned / denied / paid |
| `stage` | string, nullable | Process stage name |
| `stage_date` | datetime, nullable | |
| `remarks` | text, nullable | |
| `raw_response` | json, nullable | Full server response |
| `created_at` | timestamp | |

#### Claim Return (Deficiency)

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `claim_id` | FK → claims | |
| `deficiency_code` | string, nullable | |
| `deficiency_description` | text | |

#### Claim Return Requirement

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `claim_return_id` | FK → claim_returns | |
| `requirement_code` | string, nullable | |
| `requirement_description` | text | |

#### Accreditation

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `accreditation_number` | string, unique | PHIC accreditation |
| `facility_type` | string | HOSP/ABTC/DOTS/MCPP/PCBP/CISU/LABS |
| `facility_name` | string, nullable | |
| `hospital_code` | string, nullable | PMCC code |
| `cipher_key` | string, encrypted | eClaims encryption key |
| `api_username` | string, encrypted | eClaims API user |
| `api_password` | string, encrypted | eClaims API pass |
| `is_active` | boolean | |

#### Claim Attachment

| Field | Type | Notes |
|-------|------|-------|
| `id` | auto PK | |
| `claim_id` | FK → claims | |
| `file_type` | string | pdf / jpg / png |
| `file_name` | string | |
| `file_path` | string | Local encrypted path |
| `cloud_url` | string, nullable | DOH cloud URL after upload |
| `status` | string | pending / uploaded / failed |

### 8.3 Your Own API Service Design

The architecture follows a simple pattern: **Save locally → Queue → Encrypt → Send to DOH → Parse response → Store status**.

```
┌────────────────────────────────────────────────────┐
│                 YOUR APPLICATION                    │
│                                                     │
│  ┌──────────┐    ┌──────────┐    ┌──────────────┐  │
│  │ Frontend │◄──►│ API      │◄──►│ DB (your     │  │
│  │ (Vue/    │    │ Controllers│   │  tables)     │  │
│  │  React)  │    │          │    └──────────────┘  │
│  └──────────┘    └────┬─────┘           ▲          │
│                       │                 │          │
│                       ▼                 │          │
│                 ┌──────────┐            │          │
│                 │ Queue Job│────────────┘          │
│                 │ (Async)  │                       │
│                 └────┬─────┘                       │
│                      │                             │
│                      ▼                             │
│                 ┌──────────┐   ┌────────────────┐ │
│                 │Encryption│──►│ SOAP to DOH    │ │
│                 │Service   │   │ eClaims Server │ │
│                 └──────────┘   └───────┬────────┘ │
│                                        │          │
│                                        ▼          │
│                                  ┌──────────┐     │
│                                  │ Parse XML │     │
│                                  │ Response  │     │
│                                  └────┬─────┘     │
│                                       │            │
│                          ┌────────────┼────┐       │
│                          ▼            ▼    ▼       │
│                     IN PROCESS   RETURN   DENIED   │
│                     WITH CHEQUE  (store status)    │
└────────────────────────────────────────────────────┘
```

### 8.4 Your API Endpoints

Design your own routes — this is the complete set you need:

```php
// === Patient / Encounter ===
GET  /api/patients?search={term}              // Search patients
GET  /api/patients/{id}/encounters            // Get visit history

// === Member ===
GET  /api/members?search={term}               // Search PHIC members
GET  /api/members/{id}                        // Member details

// === Claim CRUD ===
GET  /api/claims?member_id={id}               // List claims per member
GET  /api/claims/{id}                         // Get single claim with all sub-records
POST /api/claims                               // Create/update claim + diagnoses + services
DELETE /api/claims/{id}                        // Delete claim

// === eClaims Submission ===
POST /api/claims/{id}/submit                  // Queue claim for eClaims submission
GET  /api/claims/{id}/status                  // Get claim status trail
POST /api/claims/{id}/resubmit                // Resubmit after return

// === Attachments ===
POST /api/claims/{id}/attachments             // Upload attachment
GET  /api/claims/{id}/attachments             // List attachments
DELETE /api/claims/{id}/attachments/{aid}     // Delete attachment

// === Accreditation (admin) ===
GET  /api/accreditations
POST /api/accreditations
PUT  /api/accreditations/{id}
```

### 8.5 Your Encryption Service

Same algorithm, your implementation:

```php
class EClaimsEncryption
{
    // AES-256-CBC encrypt with base64(json) output
    public static function encrypt(string $data, string $key): string
    {
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode(json_encode([
            'iv'   => base64_encode($iv),
            'data' => base64_encode($encrypted),
        ]));
    }

    public static function decrypt(string $payload, string $key): string
    {
        $json = json_decode(base64_decode($payload));
        $iv = base64_decode($json->iv);
        $encrypted = base64_decode($json->data);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }
}
```

### 8.6 Your EClaims Service (SOAP → DOH)

```php
class EClaimsService
{
    public function __construct(
        private string $wsdl,
        private string $passphrase,
        private string $version,
    ) {}

    public function submit(string $claimXml, array $accreditation): array
    {
        // 1. Encrypt XML payload using cipher_key from accreditation
        $encrypted = EClaimsEncryption::encrypt($claimXml, $accreditation['cipher_key']);

        // 2. Build SOAP envelope with credentials
        $soap = $this->buildSoap(
            hospitalCode: $accreditation['hospital_code'],
            username: $accreditation['api_username'],
            password: $accreditation['api_password'],
            encryptedData: $encrypted,
        );

        // 3. Send to DOH
        $response = Http::contentType('text/xml')
            ->timeout(120)
            ->send('POST', $this->wsdl, ['body' => $soap])
            ->body();

        // 4. Parse status from response XML
        return $this->parseStatus($response);
    }

    private function parseStatus(string $xml): array
    {
        $results = [];
        // <TRAIL><PROCESS><STAGE>, <DATE>, <REMARKS>
        // <RETURN><DEFECTS><CODE>, <DESC>, <REQUIREMENTS><REQUIREMENT><CODE>, <DESC>
        // <DENIED><CODE>, <REASON>, <DATE>
        // <CHEQUE> or <VOUCHER> or <PAYEE> with <NAME>, <TYPE>, <AMOUNT>, <CHEQUENO>, <VOUCHERNO>, etc.
        // ...parse using SimpleXML or DOMDocument...
        return $results;
    }
}
```

### 8.7 eClaims XML Payload Fields (What DOH expects)

When building your claim XML, these are the fields DOH checks:

| XML Element | Source | Required |
|-------------|--------|----------|
| `Version` | .env (`ECWSVERSION`) | Yes |
| `Patient/Enccode` | Your encounter ID | Yes |
| `Patient/AccreditationNo` | Accreditation number | Yes |
| `Patient/MemberPIN` | PHIC number of member | Yes |
| `Patient/PatientPIN` | Patient PIN (if different) | No |
| `Patient/LastName` | Patient last name | Yes |
| `Patient/FirstName` | Patient first name | Yes |
| `Patient/MiddleName` | Patient middle name | No |
| `Patient/Suffix` | Patient name suffix | No |
| `Patient/AdmissionDateTime` | Admit date + time | Yes |
| `Patient/DischargeDateTime` | Discharge date + time | Yes |
| `Patient/Disposition` | R/I/E/T/H/A | Yes |
| `Patient/AdmittingDiagnosis` | Admitting diagnosis text | Yes |
| `Patient/DischargeDiagnosis` | Final diagnosis text | Yes |
| `Patient/FirstCaseRate` | Primary case rate code | Yes |
| `Patient/SecondCaseRate` | Secondary case rate code | No |
| `Diagnosis[]/Sequence` | 1, 2, 3... | Yes |
| `Diagnosis[]/ICDCode` | ICD-10 code | Yes |
| `Diagnosis[]/Description` | Diagnosis text | Yes |
| `Diagnosis[]/ProcedureCode` | RVS code | If procedure |
| `Diagnosis[]/ProcedureDesc` | Procedure text | If procedure |
| `Diagnosis[]/ProcedureDate` | Date performed | If procedure |
| `Diagnosis[]/Laterality` | L / R / B | If applicable |
| `ProfessionalService[]/LicenseNo` | PRC license | Yes |
| `ProfessionalService[]/DoctorType` | ATEND / CONSU | Yes |
| `ProfessionalService[]/AccreditationCode` | PHIC accred | Yes |

### 8.8 Disposition Mapping (Your Logic)

| Your Disposition | Source Data | PHIC Code |
|------------------|-------------|-----------|
| Recovered | discharged + recovered | `R` |
| Improved | discharged + improved | `I` |
| Transferred | transferred out | `T` |
| Expired | died (any reason) | `E` |
| Absconded | left without notice | `A` |
| Home/AMA | against medical advice | `H` |

### 8.9 Encounter Type → Label Mapping

| Your Encounter Type | Display Label |
|---------------------|---------------|
| inpatient / ADM | ADMISSION |
| emergency / ER | EMERGENCY ROOM |
| emergency_to_admission / ERADM | EMERGENCY ROOM |
| outpatient / OPD | OUT-PATIENT DEPARTMENT |
| outpatient_to_admission / OPDAD | OUT-PATIENT DEPARTMENT |

### 8.10 Relationship to Member Mapping

| Your Relationship | PHIC Code |
|-------------------|-----------|
| self / member | `6` |
| spouse | `1` |
| child | `2` or `3` |
| parent | `4` |

### 8.11 Claim Type by Facility

| Your Facility Type | Claim Type Label |
|--------------------|------------------|
| hospital / HOSP | Hospital |
| animal_bite / ABTC | Animal Bite Treatment Center |
| tb_dots / DOTS | TB-DOTS Facility |
| maternal / MCPP | Maternity Care Package Provider |
| primary_care / PCBP | Primary Care Benefit Provider |
| isolation_unit / CISU | Community Isolation Unit |
| laboratory / LABS | Testing Laboratory |

### 8.12 Special Consideration Codes

| Your Program Code | PHIC Code | Description |
|-------------------|-----------|-------------|
| cataract | CATAR | Cataract Package |
| z_benefit | ZBENE | Z-Benefit Package |
| maternal | MCPKG | Maternal Care Package |
| tb_dots | TDOTS | TB-DOTS Package |
| animal_bite | ABITE | Animal Bite Treatment |
| newborn | NEWBO | Newborn Care Package |
| hiv_aids | OHATP | Outpatient HIV/AIDS Treatment Package |

### 8.13 Claim Status Flow (State Machine)

```
draft ──► submitted ──► in_process ──► paid (with_cheque / with_voucher)
              │               │
              │               ├──► returned ──► resubmitted ──► in_process
              │               │
              │               └──► denied
              │
              └──► failed (network/error)
```

### 8.14 Required .env Configuration

```env
APP_KEY=base64:your-32-byte-key-here
PASSPHRASE=your-eclaims-passphrase
ECWSVERSION=ECWS3.0
DOH_ECLAIMS_WSDL=https://ihomis-eclaims.doh.gov.ph/ihomisplus3.0/webservice.php?wsdl
DOH_CLOUD_UPLOAD=https://ihomis-eclaimsdoc.doh.gov.ph/uploadclaimattachment.php
DOH_CLOUD_STORAGE=https://ihomis-eclaimsdoc.doh.gov.ph/attachments
```

### 8.15 Critical Gotchas from iHOMIS Bug History (2026-07-10)

These are hard-learned lessons from production bugs discovered during the iHOMIS eClaims implementation. Avoid these patterns when rebuilding.

#### 8.15.1 Never JOIN-check extended claim tables

**The Bug**: `getHpatcon1($enccode)` used a 3-table INNER JOIN (`henctr → hpatcon → hpatcon1`) to check if an extended claim record exists. When a transaction rolled back, `hpatcon` was cleaned up but `hpatcon1` remained as an orphan from a prior committed attempt. The JOIN returned 0 rows (since hpatcon was gone), so the code did INSERT instead of UPDATE → duplicate key → rollback → infinite loop.

**The Fix**: Always query the target table directly when checking existence:
```php
// BROKEN — cascading JOIN dependency
SELECT accreno FROM henctr
INNER JOIN hpatcon  ON hpatcon.enccode  = henctr.enccode
INNER JOIN hpatcon1 ON hpatcon1.enccode = henctr.enccode
WHERE henctr.enccode = ?;

// FIXED — direct table check
SELECT enccode FROM hpatcon1 WHERE enccode = ?;
```

**Rule**: When checking "does this record exist before I decide INSERT vs UPDATE?", query **only** the table you intend to write to. Do not rely on JOIN chains where a parent record's absence hides the child record's existence.

#### 8.15.2 Always check INSERT/UPDATE return values in transactions

**The Bug**: `saveMemberClaim()` called `insert('hpatcon1', ...)` without checking the return value. If the insert failed (e.g. duplicate key), the transaction still committed or silently rolled back, leaving the UI in an inconsistent state with no error message.

**The Fix**: Every write operation inside a transaction must be checked:
```php
$ok = $this->SqlModel->insert('hpatcon1', $data);
if (!$ok) {
    $this->db->trans_rollback();
    return error_response('Failed to save. Check if claim already exists.');
}
```

#### 8.15.3 hpatcon and hpatcon1 must be atomic

The main claim table (`hpatcon`) and the extended claim table (`hpatcon1`) are logically one entity. If either write fails, both must be rolled back. The ClaimsTable datatable only reads from `hpatcon` — if `hpatcon1` has data but `hpatcon` is missing, the claim is invisible.

**Design rule**: Either use a single table with all columns, or wrap paired writes in a proper transaction with rollback-on-failure for both.

#### 8.15.4 The `syncEncounter` pattern is safer than manual INSERT-vs-UPDATE

The CF4 submission flow uses `syncEncounter()` which follows a clean pattern:
1. `check()` — query the target table directly
2. `update()` — always call update (not conditional insert-vs-update)

If the row doesn't exist, the update affects 0 rows (not an error). This avoids the entire INSERT-vs-UPDATE decision tree. Consider using `INSERT ... ON DUPLICATE KEY UPDATE` or upsert patterns instead of conditional insert/update logic.

#### 8.15.5 Clean orphans proactively

Before inserting a new row in a child table, delete any orphan that might exist from a prior failed save:
```sql
DELETE FROM hpatcon1
WHERE enccode = ?
  AND enccode NOT IN (SELECT enccode FROM hpatcon WHERE enccode = ?);
```

This is a belt-and-suspenders approach that prevents the infinite rollback loop scenario.

#### 8.15.6 `encounter_map` table is unused

A migration `221_add_encounter_map.php` created an `encounter_map` table but it is never referenced in any controller, model, or view. If you need an encounter mapping layer, build it from scratch.

---

## 9. Official PhilHealth Specs — Cross Reference

Sources: PhilHealth eClaims Implementation Guide (PeCIG), eCCSA Tech Specs v1.04, PhilHealth Circular 2017-0030, PA2024-0032

### 9.1 Official XML Field Names & Date Format

The DTD defines element names that MUST match exactly. The old iHOMIS used `p`-prefixed names internally but the actual XML sent to DOH uses different casing.

| Official XML Element | Type | Required | Format | Notes |
|---------------------|------|----------|--------|-------|
| `eCLAIMS` | Root | Yes | — | Single root element |
| `TRANSMITTAL` | Header | Yes | — | Contains transmittal info |
| `pUserName` | String(20) | Yes | — | Provided by PhilHealth |
| `pUserPassword` | String(20) | Yes | — | Provided by PhilHealth |
| `pHospitalCode` | String(12) | Yes | — | PMCC number |
| `pHospitalTransmittalNo` | String(20) | Yes | — | Unique per hospital |
| `pTotalClaims` | String(3) | Yes | Integer | Number of claims in batch |
| `pHospitalEmail` | String(150) | No | — | Where to send communication |
| `CLAIM` | Container | Yes | — | Per-claim wrapper |
| `pClaimNumber` | String(12) | Yes | — | Hospital-generated unique |
| `pTrackingNumber` | String(20) | No | `####-####-####-####` | From eligibility check |
| `pClaimSeriesLhio` | String(15) | No | — | PHIC-assigned (response) |
| `pReceiptTicketNumber` | String(18) | No | — | PHIC-assigned (response) |
| `pAdmissionDate` | String(10) | **Yes** | **`MM-DD-YYYY`** | NOT Y-m-d or m/d/Y |
| `pAdmissionTime` | String(10) | Yes | `HH:MM:SSAM/PM` | e.g. 02:30:00PM |
| `pDischargeDate` | String(10) | **Yes** | **`MM-DD-YYYY`** | Critical format difference |
| `pDischargeTime` | String(10) | Yes | `HH:MM:SSAM/PM` | |
| `pAdmissionDiagnosis` | String(500) | Yes | — | Free text |
| `pFinalDiagnosis` | String(500) | Yes | — | Free text |
| `pPatientIs` | String(1) | **Yes** | **`M`/`S`/`C`/`P`** | See section 9.2 |
| `pMemberShipType` | String(2) | Yes | `S`/`G`/`I`/`NS`/`NO`/`PS`/`PG` | See section 9.3 |
| `pPIN` | String(12) | Yes | 2-9-1 format | PHIC ID with check digit |
| `pMemberLastName` | String(60) | Yes | — | |
| `pMemberFirstName` | String(60) | Yes | — | |
| `pMemberMiddleName` | String(60) | No | — | |
| `pMemberSuffix` | String(5) | No | `JR`/`SR`/`III` | NO period after |
| `pMemberBirthDate` | String(10) | Yes | `MM-DD-YYYY` | |
| `pPatientLastName` | String(60) | Yes | — | Same as member if `M` |
| `pPatientFirstName` | String(60) | Yes | — | |
| `pPatientMiddleName` | String(60) | No | — | |
| `pPatientSuffix` | String(5) | No | — | |
| `pPatientBirthDate` | String(10) | Yes | `MM-DD-YYYY` | |
| `pPatientGender` | String(1) | Yes | `M`/`F` | |
| `pMailingAddress` | String(150) | Yes | — | Where payment notice goes |
| `pMobileNo` | String(20) | No | — | Member contact |
| `pLandlineNo` | String(20) | No | — | |
| `pZipCode` | String(4) | Yes | 4-digit | PH zip code |
| `pEmailAddress` | String(150) | No | — | Contact email |
| `pPEN` | String(12) | Yes | 2-9-1 format | Employer number |
| `pEmployerName` | String(100) | Yes | — | |
| `DIAGNOSIS` | Container | Yes | — | Can have multiple |
| `pICDCode` | String(10) | **Yes** | ICD-10 code | Without dot (e.g. `I10`) |
| `pDiagnosisDescription` | String(500) | Yes | — | |
| `pDiagnosisType` | String(1) | Yes | `F`=Final, `A`=Admitting, `M`=Morbidity | |
| `PROCEDURE` | Container | No | — | If surgical/procedure |
| `pRVSCode` | String(6) | Yes | RVS code | Case rate or procedure |
| `pRVU` | Integer | Yes | — | Relative value unit |
| `pOperationDate` | String(10) | Yes | `MM-DD-YYYY` | |
| `PROFESSIONALSERVICE` | Container | Yes | — | Per doctor |
| `pProfessionalLastName` | String(60) | Yes | — | |
| `pProfessionalFirstName` | String(60) | Yes | — | |
| `pProfessionalMiddleName` | String(60) | No | — | |
| `pProfessionalSuffix` | String(5) | No | — | |
| `pLicenseNumber` | String(20) | Yes | — | PRC license |
| `pDoctorType` | String(20) | Yes | `ATTENDING`/`CONSULTANT` | |
| `pAccreditationCode` | String(20) | Yes | — | PHIC professional acc |
| `DOCUMENTS` | Container | No | — | Scanned attachments |
| `DOCUMENT` | Element | No | — | Per document |

### 9.2 Official Relationship Codes (`pPatientIs`)

| Code | Meaning | Old iHOMIS Equivalent |
|------|---------|----------------------|
| `M` | Patient is the member (Self) | `6` |
| `S` | Patient is spouse | `1` |
| `C` | Patient is child | `2` or `3` |
| `P` | Patient is parent | `4` |

**Important**: You MUST send `M`/`S`/`C`/`P` — NOT numeric codes.

### 9.3 Official Member Type Codes (`pMemberShipType`)

| Code | Description |
|------|-------------|
| `S` | Employed Private |
| `G` | Employer Government |
| `I` | Indigent |
| `NS` | Individually Paying (Self-employed) |
| `NO` | OFW |
| `PS` | Non-Paying Private |
| `PG` | Non-Paying Government |

### 9.4 Official Web Service Method

The SOAP method is NOT `submitClaim`. The official PeCIG defines:

```
Function: eClaims Upload (pUserName, pPassword, pHospitalCode, pXML)
Returns:  pReturnCode, pReturnDescription, pReceiptTicketNumber,
          pTransmittalDate, pErrorCode, pErrorDescription,
          pClaimSeriesLhio, pConfirmationNumber
```

For status checking:
```
Function: eClaims Status Inquiry (pUserName, pPassword, pHospitalCode, pClaimNumber)
```

The WSDL is available at:
- Test: `https://cws.philhealth.gov.ph/bin` (via proxy server)
- Production: Configured per HCI

### 9.5 Official Response Codes

| pReturnCode | Meaning |
|-------------|---------|
| `00` | Successful upload |
| `01` | Transmittal error |
| `02` | Claim file error |
| `03` | System error |

Each error maps to `pErrorCode` + `pErrorDescription` for debugging.

### 9.6 eClaims 3.0 Changes (Per PA2024-0032)

| Change | Details |
|--------|---------|
| **eSOA Required** | Electronic Statement of Account for all ACR claims (Circular 2023-0026) |
| **CF5 Required** | DRG/Shadow Billing data (Circular 2024-0006) |
| **eGov QR Code** | QR code capture during admission process |
| **Test Environment** | Now open for eClaims 3.0 connection |
| **Compliance** | Separate certification per module (eSOA, DRG, QR) |

### 9.7 Cloud Storage API (eCCSA) — Official Specs

**Not SOAP** — it's a REST API for document uploads:

```
Endpoint: POST {cloud_storage_url}
Type:     Multi-part form data
Auth:     name + password (HCI credentials)
```

| Form Field | Type | Description |
|------------|------|-------------|
| `uploadFile` | File | Encrypted claim attachment (PDF/JPEG) |
| `name` | String | HCI cloud storage username |
| `password` | String | HCI cloud storage password |
| `description` | String | Remark about the file |

**Response Codes:**

| HTTP | Result | Meaning |
|------|--------|---------|
| 200 | `ECAV_1_1` | Invalid encrypted file (cannot decrypt) |
| 200 | `ECAV_1_2` | CF4 XML validation error (decryptable but invalid) |
| 200 | `ECAV_1_3` | CF5 validation error |
| 200 | Success JSON | File uploaded and validated |
| 500 | Error | Server error |

**cURL Example (from official spec):**
```bash
curl -v \
  -F "uploadFile=@/path/to/encrypted_file.pdf" \
  -F "name=hciuser1" \
  -F "password=P@$$w0rd" \
  -F "description=CSF SAMPLE" \
  https://ihomis-eclaimsdoc.doh.gov.ph/uploadclaimattachment.php
```

### 9.8 DTD Conformance (PhilHealth Requirement)

Before submission, the XML file MUST conform to the DTD at:
`http://cdp.philhealth.gov.ph/dtd/eClaimsDef.dtd`

The DTD defines the strict element hierarchy:

```
eCLAIMS
  └── TRANSMITTAL (pUserName, pUserPassword, pHospitalCode, pHospitalTransmittalNo, ...)
  └── CLAIM (pClaimNumber, pTrackingNumber, ...)
        └── MEMBER (pPIN, pMemberShipType, pMemberLastName, ...)
        └── PATIENT (pPatientIs, pPatientLastName, pAdmissionDate, pDischargeDate, ...)
              └── DIAGNOSIS (pICDCode, pDiagnosisDescription, pDiagnosisType)
              └── PROCEDURE (pRVSCode, pRVU, pOperationDate)
              └── PROFESSIONALSERVICE (pLicenseNumber, pDoctorType, ...)
      └── DOCUMENTS
            └── DOCUMENT (...)
```

### 9.9 Corrected XML Builder (Based on Official Specs)

Replace the old `buildClaimXml` with this DTD-compliant version:

```php
private function buildClaimXml(Claim $claim): string
{
    $xml = new \XMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);

    $xml->startElement('eCLAIMS');

    // Transmittal Header
    $xml->startElement('TRANSMITTAL');
    $xml->writeElement('pUserName', $this->username);
    $xml->writeElement('pUserPassword', $this->password);
    $xml->writeElement('pHospitalCode', $this->hospitalCode);
    $xml->writeElement('pHospitalTransmittalNo', $claim->transmittal_no);
    $xml->writeElement('pTotalClaims', '1');
    $xml->writeElement('pHospitalEmail', config('mail.from.address'));
    $xml->endElement(); // TRANSMITTAL

    // Claim
    $xml->startElement('CLAIM');
    $xml->writeElement('pClaimNumber', $claim->enccode);
    $xml->writeElement('pTrackingNumber', $claim->tracking_number ?? '');

    // Member
    $xml->startElement('MEMBER');
    $xml->writeElement('pPIN', $claim->member->phicnum);
    $xml->writeElement('pMemberShipType', $claim->member->membership_type);
    $xml->writeElement('pMemberLastName', strtoupper($claim->member->memlast));
    $xml->writeElement('pMemberFirstName', strtoupper($claim->member->memfirst));
    $xml->writeElement('pMemberMiddleName', strtoupper($claim->member->memmid ?? ''));
    $xml->writeElement('pMemberSuffix', strtoupper($claim->member->memsuffix ?? ''));
    $xml->writeElement('pMemberBirthDate', $claim->member->membdate?->format('m-d-Y'));
    $xml->writeElement('pMailingAddress', strtoupper($claim->member->mailing_address ?? ''));
    $xml->writeElement('pMobileNo', $claim->member->memcontactno ?? '');
    $xml->writeElement('pLandlineNo', $claim->member->memlandline ?? '');
    $xml->writeElement('pZipCode', $claim->member->memzip ?? '');
    $xml->writeElement('pEmailAddress', $claim->member->pemailaddress ?? '');
    $xml->writeElement('pPEN', $claim->member->pen ?? '');
    $xml->writeElement('pEmployerName', strtoupper($claim->member->empname ?? ''));
    $xml->endElement(); // MEMBER

    // Patient
    $xml->startElement('PATIENT');
    $xml->writeElement('pPatientIs', $claim->patient_is);      // M/S/C/P
    $xml->writeElement('pPatientLastName', strtoupper($claim->patient_last_name));
    $xml->writeElement('pPatientFirstName', strtoupper($claim->patient_first_name));
    $xml->writeElement('pPatientMiddleName', strtoupper($claim->patient_middle_name ?? ''));
    $xml->writeElement('pPatientSuffix', strtoupper($claim->patient_suffix ?? ''));
    $xml->writeElement('pPatientBirthDate', $claim->patient_birthdate?->format('m-d-Y'));
    $xml->writeElement('pPatientGender', $claim->patient_gender);
    $xml->writeElement('pAdmissionDate', $claim->extended?->padmissiondatetime?->format('m-d-Y'));
    $xml->writeElement('pAdmissionTime', $claim->extended?->padmissiondatetime?->format('h:i:sA'));
    $xml->writeElement('pDischargeDate', $claim->extended?->pdischargedatetime?->format('m-d-Y'));
    $xml->writeElement('pDischargeTime', $claim->extended?->pdischargedatetime?->format('h:i:sA'));
    $xml->writeElement('pAdmissionDiagnosis', strtoupper($claim->extended?->padmittingdiagnosis ?? ''));
    $xml->writeElement('pFinalDiagnosis', strtoupper($claim->extended?->pdischargediagnosis ?? ''));

    // Diagnoses
    foreach ($claim->diagnoses as $dx) {
        $xml->startElement('DIAGNOSIS');
        $xml->writeElement('pICDCode', $dx->pICDCode);
        $xml->writeElement('pDiagnosisDescription', strtoupper($dx->pDischargeDiagnosis));
        $xml->writeElement('pDiagnosisType', 'F');  // F=Final
        $xml->endElement(); // DIAGNOSIS
    }

    // Procedures
    foreach ($claim->diagnoses->where('pRVSCode', '!=', '') as $dx) {
        $xml->startElement('PROCEDURE');
        $xml->writeElement('pRVSCode', $dx->pRVSCode);
        $xml->writeElement('pRVU', '1');
        $xml->writeElement('pOperationDate', $dx->pProcedureDate?->format('m-d-Y'));
        $xml->endElement(); // PROCEDURE
    }

    // Professional Services
    foreach ($claim->professionalServices as $svc) {
        $xml->startElement('PROFESSIONALSERVICE');
        $xml->writeElement('pProfessionalLastName', strtoupper($svc->doctor_last_name));
        $xml->writeElement('pProfessionalFirstName', strtoupper($svc->doctor_first_name));
        $xml->writeElement('pProfessionalMiddleName', strtoupper($svc->doctor_middle_name ?? ''));
        $xml->writeElement('pProfessionalSuffix', strtoupper($svc->doctor_suffix ?? ''));
        $xml->writeElement('pLicenseNumber', $svc->licno);
        $xml->writeElement('pDoctorType', $svc->doctype === 'ATEND' ? 'ATTENDING' : 'CONSULTANT');
        $xml->writeElement('pAccreditationCode', $svc->pdoctoraccrecode ?? '');
        $xml->endElement(); // PROFESSIONALSERVICE
    }

    $xml->endElement(); // CLAIM
    $xml->endElement(); // eCLAIMS

    return $xml->outputMemory();
}
```

### 9.10 Corrections to Apply to Your Implementation

| Item | What We Documented Before | Official Spec Says | Fix |
|------|--------------------------|-------------------|-----|
| Date format | `Y-m-d` or `m/d/Y` | **`MM-DD-YYYY`** | Use `m-d-Y` in PHP format |
| Time format | `H:i:s` | **`HH:MM:SSAM/PM`** | Use `h:i:sA` |
| Relationship | numeric `1,2,3,4,6` | **`M/S/C/P`** | Map before sending |
| Membership type | codes from `typemem` | **`S/G/I/NS/NO/PS/PG`** | Map before sending |
| XML root element | `EClaims` | **`eCLAIMS`** (case-sensitive) | Must be all caps |
| Claim wrapper | `Patient` as root | **`CLAIM` > `MEMBER` + `PATIENT`** | Correct hierarchy |
| Web method name | `submitClaim` | **`eClaims Upload`** | Use correct SOAP function |
| Diagnosis type | Not sent | **`pDiagnosisType`** (`F`/`A`/`M`) | Must include |
| Document upload | SOAP | **REST multi-part form** | Different API entirely |
| ICD code format | With/without dot | **Without dot** | Strip dots before sending |

---

## 10. Known Issues & Fix Plan (Discovered 2026-07-10)

### 10.1 Bug: `hpatcon1` Orphan Rows from `getHpatcon1` Join Logic

**Error**: `Duplicate entry '...' for key 'hpatcon1.PRIMARY'` when re-saving a claim after a rollback.

**Root Cause**: `PhilhealthModel->getHpatcon1($enccode)` uses a 3-table INNER JOIN instead of querying `hpatcon1` directly:

```sql
-- CURRENT (broken) — requires enccode to exist in ALL THREE tables
SELECT accreno FROM henctr
INNER JOIN hpatcon  ON hpatcon.enccode  = henctr.enccode
INNER JOIN hpatcon1 ON hpatcon1.enccode = henctr.enccode
WHERE henctr.enccode = '$enccode';
```

When the transaction rolls back (e.g. `hpatcon1` duplicate), `hpatcon` is cleaned up but `hpatcon1` orphan remains from a **previous** committed attempt. On re-save:
1. `getHpatcon1` returns **0 rows** (henctr + hpatcon were rolled back; only `hpatcon1` orphan remains)
2. Code sees `num_rows() == 0` and does **INSERT** (not UPDATE)
3. INSERT hits duplicate key → rollback again → infinite loop

**Fix** — Change `getHpatcon1` to query `hpatcon1` directly:

```sql
-- FIXED — check hpatcon1 only, no JOIN dependency
SELECT enccode FROM hpatcon1 WHERE enccode = ?;
```

```php
// PhilhealthModel.php — getHpatcon1
function getHpatcon1($enccode)
{
    return $this->db->get_where('hpatcon1', ['enccode' => $enccode]);
}
```

### 10.2 Bug: Missing Error Handling on `hpatcon1` Insert

In `saveMemberClaim()`, the return value of `SqlModel->insert('hpatcon1', ...)` is never checked:

```php
// saveMemberClaim() ~line 685
if ($hpatcon1->num_rows() > 0) {
    $this->SqlModel->update('hpatcon1', 'enccode', $hpatcon1_data, $patientenccode);
} else {
    $this->SqlModel->insert('hpatcon1', $hpatcon1_data);  // RETURN VALUE IGNORED
}
$this->db->trans_complete();
```

If the insert fails, the transaction still commits (or rolls back without feedback), leaving the UI in an inconsistent state.

**Fix**: Check the return value and return an error response:

```php
if ($hpatcon1->num_rows() > 0) {
    $updated = $this->SqlModel->update('hpatcon1', 'enccode', $hpatcon1_data, $patientenccode);
} else {
    $updated = $this->SqlModel->insert('hpatcon1', $hpatcon1_data);
}

if (!$updated) {
    $this->db->trans_rollback();
    return $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode([
            'status' => 'error',
            'code' => 400,
            'message' => 'Failed to save claim details. Check if claim already exists.'
        ]));
}
```

### 10.3 Symptom: ClaimsTable (Datatable) Shows Empty Despite Data in `hpatcon1`

The datatable query (`PhilhealthModel->MemberClaims`) reads **only** from `hpatcon`:

```sql
SELECT ... FROM hpatcon
INNER JOIN henctr ON henctr.enccode = hpatcon.enccode
INNER JOIN hperson ON ...
LEFT JOIN hhospaccreditation ON ...
WHERE hpatcon.memphicnum = ?;
```

`hpatcon1` is **not joined**. Even if `hpatcon1` has data, if `hpatcon` is missing (rolled back or deleted), the claim is invisible.

**Fix**: Apply 10.1 + 10.2 to prevent orphan creation. Existing orphans can be cleaned up:

```sql
-- Find orphans: rows in hpatcon1 with no matching hpatcon
SELECT h1.enccode FROM hpatcon1 h1
LEFT JOIN hpatcon hc ON hc.enccode = h1.enccode
WHERE hc.enccode IS NULL;

-- Delete them (after verifying they're truly orphans)
DELETE h1 FROM hpatcon1 h1
LEFT JOIN hpatcon hc ON hc.enccode = h1.enccode
WHERE hc.enccode IS NULL;
```

### 10.4 Preventive: Runtime Orphan Cleanup in `saveMemberClaim()`

Before inserting into `hpatcon1`, delete any orphan that might exist from a prior failed save:

```php
// saveMemberClaim() — before hpatcon1 insert
if ($this->input->post('formIden') == 'insert') {
    // Clean orphan from previous failed save (if any)
    $this->PhilhealthModel->deleteOrphanHpatcon1($patientenccode);
}
```

```php
// PhilhealthModel
function deleteOrphanHpatcon1($enccode)
{
    $this->db->where('enccode', $enccode);
    $this->db->where("enccode NOT IN (SELECT enccode FROM hpatcon WHERE enccode = '$enccode')", null, false);
    return $this->db->delete('hpatcon1');
}
```

### 10.5 Priority Order

1. **Fix `getHpatcon1`** (10.1) — stops the infinite rollback loop
2. **Add error handling** (10.2) — surfaces failures to the UI instead of silent rollback
3. **Clean up existing orphans** (10.3) — recovers invisible claims
4. **Add preventive cleanup** (10.4) — belt-and-suspenders against future orphans
 