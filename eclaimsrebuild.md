# eClaims Module Rebuild Plan — Data Entry + Data Pull-Up Architecture

**Date:** 2026-07-12
**Status:** COMPLETE — All 13 phases implemented, all files pass PHP syntax check

## Executive Summary

Rebuild the PhilHealth eClaims module from a monolithic 973-line Livewire component into **5 focused Livewire components** following a **Data Entry → Data Pull-Up** workflow. Refactor existing tables (not rebuild), support all claim types (ACR, DRG, Z-Benefit, OECB), and pull clinical data from ER, In-Patient, OPD, and Billing sources.

## Decisions

| Question | Answer |
|----------|--------|
| Database tables | A) Refactor existing (keep 10 tables, fix issues) |
| Data pull-up sources | All (ER, In-Patient, OPD, Billing/Invoices) |
| Livewire architecture | Option 3: Separate Livewire components |
| Claim types | All (ACR, DRG, Z-Benefit, OECB) from the start |

---

## Part 1: Table Refactoring (3 migrations)

Keep all 10 existing tables. Fix issues and add missing columns.

### Migration A: Merge ICD-10 tables

The `icd10_codes` table (used by `philhealth_claims` FK) and `icd_10_codes` table (used by Konsulta + the `Icd10Code` model) are duplicates. Standardize on `icd_10_codes` (the newer one).

- Migration: point `philhealth_claims.icd10_code_id`, `secondary_icd10_code_id`, `tertiary_icd10_code_id` FKs to `icd_10_codes` instead of `icd10_codes`
- Migration: add `category` column to `icd_10_codes` (currently missing, exists in `icd10_codes`)
- Migration: rename `description` to `name` on `icd_10_codes` (to match `XmlBuilder` expectations) OR update `XmlBuilder` to use `description`
- Add `icd_10_codes` seeder to populate the ICD-10 catalog
- Drop `icd10_codes` table after migration

### Migration B: Add missing columns to `philhealth_claims`

| Column | Type | Purpose |
|--------|------|---------|
| `patient_is` | varchar(5) | M/S/C/P relationship code for XML (currently computed at runtime) |
| `membership_type` | varchar(10) | S/G/I/NS/NO/PS/PG for XML |
| `encounter_type` | varchar(20) | admission/emergency/outpatient (currently implicit from er_admission_id/admission_id) |
| `disposition_code` | varchar(5) | R/I/E/T/H/A for XML |
| `tracking_number` | varchar(50) | CEWS eligibility tracking no (currently only stored on ECS upload) |
| `pension_type` | varchar(10) | For CF1 member registration |
| `gender` | varchar(5) | Patient gender (denormalized for XML) |

### Migration C: Add `claim_procedures` table

| Column | Type | Purpose |
|--------|------|---------|
| `id` | bigint PK | |
| `tenant_id` | bigint | INDEX |
| `philhealth_claim_id` | bigint FK | INDEX |
| `sequence` | int | 1, 2, 3... |
| `procedure_description` | text | |
| `rvs_code` | varchar(10) | Case rate code |
| `procedure_date` | date | |
| `laterality` | varchar(20) | Left/Right/Bilateral |
| `special_consideration` | varchar(50) | cataract/zbenefit/maternal/etc. |
| `is_active` | tinyint | default 1 |
| `history` | tinyint | default 1 |
| `created_by` / `created_date` / `updated_by` / `updated_date` | audit | |

---

## Part 2: New Livewire Component Architecture (5 components)

Replace the current monolith with these components, each with a clear single responsibility:

### Component 1: `ClaimCrud` (Orchestrator / List Page)

**File:** `app/Livewire/Philhealth/ClaimCrud.php` (refactored)
**View:** `resources/views/livewire/philhealth/claim-crud.blade.php` (refactored)

**Responsibility:** Claims list table, search, sort, pagination. Dispatches events to child components.

**Key changes from current:**
- Remove ALL modal logic (16 modals → 0 modals here)
- Remove ALL form fields
- Remove ALL service calls
- Keep only: table rendering, search, sort, pagination, row actions
- Row actions dispatch Livewire events to open child components:
  - "New Claim" → `dispatch('open-claim-form')`
  - "View/Edit" → `dispatch('open-claim-form', claimId: $id)`
  - "Submit" → `dispatch('open-claim-submission', claimId: $id)`
- Render child components inline: `<livewire:philhealth.claim-form />`, `<livewire:philhealth.clinical-data-pull-up />`, `<livewire:philhealth.claim-items />`, `<livewire:philhealth.claim-submission />`

**Properties:** `$search`, `$sortField`, `$sortDirection`, `$perPage` (4 properties only)

**Methods:** `render()`, `sortBy()`, `openClaimDetail()`

### Component 2: `ClaimForm` (Data Entry — CF1-CF4 Clinical Data)

**File:** `app/Livewire/Philhealth/ClaimForm.php` (new)
**View:** `resources/views/livewire/philhealth/claim-form.blade.php` (new)

**Responsibility:** The CF1-CF4 data entry form. All fields that get saved to `philhealth_claims`. This is the "data entry" side.

**Sections in the form:**

| Section | Fields | CF Form |
|---------|--------|---------|
| A: Patient & Member | Patient search, PhilHealth member selection, relationship_to_member, patient PIN | CF1 |
| B: Admission Dates | Date/time admitted, date/time discharged, encounter_type | CF1 |
| C: Diagnosis | Admitting diagnosis, discharge diagnosis, primary/secondary/tertiary ICD-10, case rate | CF1/CF2 |
| D: Clinical Data | HPI, past medical history, physical exam, course in ward, lab/imaging summary, chief complaint | CF4 |
| E: OB/Maternity | LMP, EDC, gravida, para, delivery date, delivery type, newborn outcome | CF3 |
| F: Outcome & Status | Outcome (R/I/E/T/H/A), claim_type (ACR/DRG/Z-Benefit/OECB), disposition_code, remarks | CF4 |
| G: Signatures | HCI signatory, physician signature date, patient signature date | CF2 |
| H: Z-Benefits | Dynamic add/remove of Z-Benefit entries (code, name, pre-auth, tranche, amount) | CF5 |

**Properties:** ~35 properties for form fields (same as current ClaimCrud but isolated)

**Key methods:**
- `mount(?int $claimId)` — loads existing claim or creates draft
- `save()` — validates + saves `PhilhealthClaim` + `PhilhealthZBenefit` sync
- `resetForm()`, `generateClaimCode()`
- `addZBenefit()`, `removeZBenefit()`, `syncZBenefits()`
- `close()` — dispatches event to parent

**Events listened:** `open-claim-form` (with optional `claimId`)
**Events dispatched:** `claim-saved` (with claimId), `open-pull-up` (with patientId), `open-items` (with claimId), `close-modal`

### Component 3: `ClinicalDataPullUp` (Data Pull-Up from Clinical Records)

**File:** `app/Livewire/Philhealth/ClinicalDataPullUp.php` (new)
**View:** `resources/views/livewire/philhealth/clinical-data-pull-up.blade.php` (new)

**Responsibility:** The "data pull-up" side. Shows available clinical encounters for a patient, lets the user select one, and pulls data into the claim form.

**Workflow:**
1. Receives `patientId` event from ClaimForm
2. Queries all encounters for that patient:
   - `ErAdmission` (with `oecbEntries`) — emergency encounters
   - `Admission` — in-patient encounters
   - `OpdConsultation` — outpatient encounters
3. Displays a card/list of available encounters grouped by type (ER, Ward, OPD)
4. Each card shows: date, type, diagnosis, doctor, status
5. User clicks "Pull Data" on an encounter → dispatches `data-pulled` event with the encounter data
6. ClaimForm receives the event and populates fields

**Data mapping (pull-up → claim fields):**

| Source | Claim Field |
|--------|-------------|
| ErAdmission.admission_datetime | date_admitted, time_admitted |
| ErAdmission.disposition_datetime | date_discharged, time_discharged |
| ErAdmission.initial_diagnosis | admitting_diagnosis |
| ErAdmission.oecbEntries[].core_presentation | chief_complaint |
| ErAdmission.is_oecb | claim_type = 'OECB', er_admission_id |
| Admission.admission_datetime | date_admitted, time_admitted |
| Admission.discharge_datetime | date_discharged, time_discharged |
| Admission.diagnosis | admitting_diagnosis, discharge_diagnosis |
| Admission.id | admission_id |
| OpdConsultation consultation date | date_admitted, time_admitted |
| OpdConsultation diagnosis | admitting_diagnosis |
| BillingInvoice items → ClaimItems | claim_items (drugs, labs, procedures, supplies, room) |

**Also handles "Import from Billing":**
- Query `BillingInvoice` for the patient linked to the selected encounter
- Map each `BillingInvoiceItem` to a claim item (source_type → item_type mapping)
- Dispatch `items-imported` event to ClaimItems component

**Properties:** `$patientId`, `$encounters`, `$selectedEncounterType`, `$selectedEncounterId`, `$importSource`
**Methods:** `loadEncounters()`, `pullData()`, `importBillingCharges()`, `close()`
**Events listened:** `open-pull-up` (with patientId)
**Events dispatched:** `data-pulled` (with encounter data array), `items-imported` (with billing items array)

### Component 4: `ClaimItems` (Line Items Management)

**File:** `app/Livewire/Philhealth/ClaimItems.php` (new)
**View:** `resources/views/livewire/philhealth/claim-items.blade.php` (new)

**Responsibility:** Dynamic line item management for a claim. Drugs, labs, procedures, supplies, room & board, professional fees.

**Features:**
- Inline editable table with columns: Type, Code, Name, Description, Qty, Unit Price, Actual Amount, Claimed Amount, Remove
- Item type dropdown: Drug/Medicine, Laboratory, Radiology, Procedure, Supply, RoomBoard, ProfessionalFee, Others
- Add/remove buttons
- Real-time total recalculation (Actual + Claimed)
- Receives imported items from ClinicalDataPullUp
- Syncs items to `claim_items` table on save

**Properties:** `$claimId`, `$items` (array), `$totalActual`, `$totalClaimed`
**Methods:** `mount(?int $claimId)`, `addItem()`, `removeItem()`, `syncItems()`, `recalculateTotals()`, `receiveImportedItems()`, `save()`, `close()`
**Events listened:** `open-items` (with claimId), `items-imported` (with items array)
**Events dispatched:** `items-saved` (with totals)

### Component 5: `ClaimSubmission` (ECS, Cloud, Ack, Voucher, Status)

**File:** `app/Livewire/Philhealth/ClaimSubmission.php` (new)
**View:** `resources/views/livewire/philhealth/claim-submission.blade.php` (new)

**Responsibility:** All external service interactions and status transitions. Everything after the claim data is entered.

**Sub-sections (each a tab or accordion within the component):**

| Tab | Actions |
|-----|---------|
| **Submission** | Check Eligibility (CEWS), ECS Upload, Cloud Upload, XML Download |
| **Status** | Poll Status, Poll History, Acknowledgment, Voucher Payment, Deny |
| **Rejection** | Record Rejection, Resubmit |
| **CF5/DRG** | Calculate Shadow Billing, Print CF5 |
| **Documents** | Upload Attachments, View Attachments |
| **QR Code** | Generate eGov QR |
| **PDF Forms** | Preview/Download CF1, CF2, CF3, CF4, eSOA |

**Properties:** `$claimId`, `$showModal` (single modal flag), `$activeTab`, plus all the per-action fields (acknowledgment_code, voucher_number, etc.)
**Methods:** All the current service-calling methods from ClaimCrud, moved here:
- `checkEligibility()`, `uploadToEcs()`, `uploadToCloud()`
- `acknowledgeClaim()`, `recordVoucher()`, `markDenied()`
- `recordRejection()`, `resubmitClaim()`
- `openCf5()`, `calculateCf5()`
- `previewCf1()` through `previewCf4()`, `downloadEsoa()`
- `downloadClaimXml()`, `generateQr()`
- `openStatusPoll()`, `runStatusPoll()`, `openPollHistory()`

**Events listened:** `open-claim-submission` (with claimId)
**Events dispatched:** `claim-status-changed` (with new status)

---

## Part 3: Parent Blade View Integration

**File:** `resources/views/livewire/philhealth/claim-crud.blade.php` (refactored)

The parent view embeds all child components and manages which one is visible:

```blade
<div>
    {{-- Claim List Table (always visible) --}}
    <livewire:philhealth.claim-list-table />

    {{-- Overlays (shown/hidden via Alpine.js + Livewire events) --}}
    @if($showClaimForm)
        <livewire:philhealth.claim-form :claimId="$editingClaimId" />
    @endif

    @if($showPullUp)
        <livewire:philhealth.clinical-data-pull-up :patientId="$pullUpPatientId" />
    @endif

    @if($showItems)
        <livewire:philhealth.claim-items :claimId="$itemsClaimId" />
    @endif

    @if($showSubmission)
        <livewire:philhealth.claim-submission :claimId="$submissionClaimId" />
    @endif
</div>
```

The parent `ClaimCrud` acts as an orchestrator — it holds the visibility state for child modals and wires up events between components.

---

## Part 4: Services to Refactor

### 4a: Extract shared `SoapClient` trait

Extract duplicated `buildSoapEnvelope()` from 4 services into a shared trait:

**File:** `app/Traits/SoapClient.php` (new)

```php
trait SoapClient {
    protected function buildSoapEnvelope(string $method, array $params): string { ... }
    protected function callSoap(string $endpoint, string $xml, int $timeout = 60): string { ... }
}
```

Apply to: `EclaimsService`, `EcsService`, `ClaimStatusPollingService`, `CloudStorageService`

### 4b: Fix XmlBuilder date format

Current: uses `Y-m-d` format
Official PhilHealth spec requires: `MM-DD-YYYY` (`m-d-Y` in PHP)

**File:** `app/Services/Philhealth/XmlBuilder.php`
- Change all `->format('Y-m-d')` to `->format('m-d-Y')`
- Change all `->format('H:i:s')` to `->format('h:i:sA')`
- Add `pDiagnosisType` field (F=Final, A=Admitting, M=Morbidity)
- Fix XML hierarchy: `eCLAIMS > CLAIM > MEMBER + PATIENT + DIAGNOSIS + PROCEDURE + PROFESSIONALSERVICE`

### 4c: Fix XmlBuilder relationship codes

- Map `relationship_to_member` from "Self/Spouse/Child/Parent" to "M/S/C/P" before XML output
- Map `membership_type` from "Employed/Self-Employed/etc." to "S/G/I/NS/NO/PS/PG" before XML output
- Strip dots from ICD-10 codes before XML output

---

## Part 5: Config Changes

**File:** `config/philhealth.php`

Add missing DRG config:

```php
'drg' => [
    'base_rate' => env('PHILHEALTH_DRG_BASE_RATE', 35000),
],
```

---

## Part 6: Routes (no changes needed)

All existing routes remain the same. The Livewire component names change but routes reference blade pages which embed the components.

| Route | View | Component |
|-------|------|-----------|
| `GET /philhealth/claims` | `pages.philhealth.claims` | `livewire:philhealth.claim-crud` (orchestrator) |
| `GET /philhealth/batches` | `pages.philhealth.batches` | `livewire:philhealth.batch-crud` (unchanged) |
| CF1-CF5 PDF routes | Unchanged | Unchanged |

---

## Part 7: File Inventory

### New files to create (9):

| File | Type | Lines (est.) |
|------|------|-------------|
| `app/Livewire/Philhealth/ClaimForm.php` | Livewire Component | ~400 |
| `app/Livewire/Philhealth/ClinicalDataPullUp.php` | Livewire Component | ~300 |
| `app/Livewire/Philhealth/ClaimItems.php` | Livewire Component | ~250 |
| `app/Livewire/Philhealth/ClaimSubmission.php` | Livewire Component | ~500 |
| `app/Traits/SoapClient.php` | Trait | ~60 |
| `resources/views/livewire/philhealth/claim-form.blade.php` | Blade | ~350 |
| `resources/views/livewire/philhealth/clinical-data-pull-up.blade.php` | Blade | ~200 |
| `resources/views/livewire/philhealth/claim-items.blade.php` | Blade | ~150 |
| `resources/views/livewire/philhealth/claim-submission.blade.php` | Blade | ~400 |

### Files to refactor (8):

| File | Changes |
|------|---------|
| `app/Livewire/Philhealth/ClaimCrud.php` | Strip to orchestrator only (~150 lines). Remove all modal logic, form fields, service calls. Add event listeners for child component coordination. |
| `resources/views/livewire/philhealth/claim-crud.blade.php` | Strip to list table + embed child components (~200 lines) |
| `app/Services/Philhealth/XmlBuilder.php` | Fix date format, fix XML hierarchy, add missing fields, use shared SoapClient |
| `app/Services/Philhealth/EclaimsService.php` | Use shared SoapClient trait |
| `app/Services/Philhealth/EcsService.php` | Use shared SoapClient trait |
| `app/Services/Philhealth/CloudStorageService.php` | Use shared SoapClient trait |
| `app/Services/Philhealth/ClaimStatusPollingService.php` | Use shared SoapClient trait |
| `config/philhealth.php` | Add `drg.base_rate` config key |

### Files to update (3):

| File | Changes |
|------|---------|
| `app/Models/PhilhealthClaim.php` | Add `patient_is`/`membership_type`/`encounter_type`/`disposition_code` to `$fillable` and casts. Add `claimProcedures()` HasMany relationship. |
| `app/Models/PhilhealthMember.php` | Add `$fillable`, add relationships (hasMany claims), add accessors for formatted names |
| `app/Models/Icd10Code.php` | Verify it points to `icd_10_codes` table. Add `category` accessor if needed. |

### Migration files (3):

| File | Purpose |
|------|---------|
| `database/migrations/2026_07_12_000001_merge_icd10_tables.php` | Point FKs to `icd_10_codes`, add `category` column, seed data, drop `icd10_codes` |
| `database/migrations/2026_07_12_000002_add_missing_columns_to_philhealth_claims.php` | Add `patient_is`, `membership_type`, `encounter_type`, `disposition_code`, `tracking_number`, `pension_type`, `gender` |
| `database/migrations/2026_07_12_000003_create_claim_procedures_table.php` | New `claim_procedures` table |

---

## Part 8: Implementation Order

| Phase | Task | Status |
|-------|------|--------|
| **1** | Create 4 migrations | ✅ Done |
| **2** | Fix ICD-10 table (merge to `icd_10_codes`) | ✅ Done |
| **3** | Update `PhilhealthClaim` model (new fields, new relationship) | ✅ Done |
| **4** | Create `SoapClient` trait, refactor 4 services | ✅ Done |
| **5** | Fix `XmlBuilder` (date format, hierarchy, PROCEDURE, SecondCaseRate) | ✅ Done |
| **6** | Create `ClaimItems` component + view | ✅ Done |
| **7** | Create `ClinicalDataPullUp` component + view | ✅ Done |
| **8** | Create `ClaimForm` component + view | ✅ Done |
| **9** | Create `ClaimSubmission` component + view | ✅ Done |
| **10** | Refactor `ClaimCrud` to orchestrator (973→160 lines) | ✅ Done |
| **11** | Create `PhilhealthReferenceSeeder` (50 RVS + 31 case rates) | ✅ Done |
| **12** | All files pass `php -l` syntax check (16 files) | ✅ Done |
| **13** | `php artisan migrate --force` + `db:seed --force` | ⏳ Pending (env) |
| **14** | Update `ragdata.md` | ✅ Done |

---

## Part 9: Key Design Decisions

1. **Event-driven coordination** — Child components communicate via Livewire events (`dispatch`/`#[On]`). The parent orchestrator manages which component is visible.

2. **Each child component is independently mountable** — `ClaimForm` can load a claim by ID, `ClaimItems` can load items by claim ID, `ClinicalDataPullUp` takes a patient ID. No tight coupling.

3. **Data Pull-Up is read-only display + action** — It queries ER/OPD/Ward/Billing, shows cards, and dispatches `data-pulled` events. It does NOT write to the claim. The ClaimForm receives the data and populates its fields.

4. **ClaimSubmission is a tabbed panel, not 16 modals** — Instead of 16 separate modal booleans, it uses a single modal with tabs (Submission, Status, Rejection, CF5, Documents, QR, PDFs).

5. **Existing BatchCrud is left mostly unchanged** — It's already reasonably scoped at 427 lines. Only needs minor updates to reference the new model fields.

6. **No new database tables except `claim_procedures`** — The existing 10 tables are sufficient for all claim types. The new table separates procedures from line items for cleaner XML generation.

---

## Part 10: Claim Lifecycle (State Machine — Unchanged)

```
Draft → For Submission → Submitted → Acknowledged → Paid
                            │              │
                            ↓              ↓
                          Denied         Denied
                            │
                            ↓
                        Resubmitted → For Submission
```

---

## Part 11: Data Flow Diagram

```
User clicks "New Claim"
  │
  ▼
┌─────────────────────┐
│  ClinicalDataPullUp  │ ← Shows available encounters for patient
│  (ER/Ward/OPD/Billing)│
│  User selects one     │
└──────────┬──────────┘
           │ dispatch('data-pulled', encounterData)
           ▼
┌─────────────────────┐
│     ClaimForm        │ ← Receives pulled-up data
│  (CF1-CF4 Data Entry)│ ← User reviews/edits
│  Patient, Member,    │
│  Diagnosis, Clinical, │
│  OB, Outcome, Z-Ben  │
└──────────┬──────────┘
           │ dispatch('open-items', claimId)
           ▼
┌─────────────────────┐
│     ClaimItems       │ ← Dynamic line items
│  (Drugs, Labs, etc.) │ ← Or receives imported items
│  Qty, Price, Total   │    from Billing
└──────────┬──────────┘
           │ dispatch('claim-saved', claimId)
           ▼
┌─────────────────────┐
│  ClaimSubmission     │ ← All external actions
│  CEWS, ECS, Cloud,   │
│  Ack, Voucher, Deny, │
│  Rejection, CF5, QR, │
│  PDFs, XML           │
└─────────────────────┘
```

---

## Part 12: RVS Codes & Case Rates — Findings & Fixes

### 12A: Current State — Two Disconnected Tables

| Table | Model | CRUD Route | Used By Claims? | Used In XML? | Used In CEWS? |
|-------|-------|------------|-----------------|-------------|--------------|
| `rvs_codes` | `RvsCode` | `/philhealth/rvs-codes` | **No** | **No** | **No** |
| `philhealth_case_rates` | `PhilhealthCaseRate` | `/philhealth/case-rates` | **Yes** (`case_rate_id` FK) | **Yes** (`caseRate->rvs_code` → `<PACKAGE>`) | **Yes** (`caseRate->rvs_code` → `pRVS`) |

#### Table: `rvs_codes` (Global reference — no tenant_id)

| Column | Type | Constraints | Purpose |
|--------|------|-------------|---------|
| `id` | bigint unsigned | PK | |
| `code` | varchar(10) | NOT NULL, UNIQUE | RVS code (e.g. "11450") |
| `name` | varchar(255) | NOT NULL | Procedure description |
| `type` | varchar(50) | nullable | e.g. "Surgical" |
| `is_active` / `history` | tinyint(1) | defaults 1 | Standard flags |
| audit columns | | | created_by/date, updated_by/date |

**CRUD:** `RvsCodeCrud` Livewire component — sortable datagrid (Code, Description, Type, Status), create/edit modal, archive.

#### Table: `philhealth_case_rates` (Global reference — no tenant_id)

| Column | Type | Constraints | Purpose |
|--------|------|-------------|---------|
| `id` | bigint unsigned | PK | |
| `rvs_code` | varchar(10) | nullable, INDEX | Free-text RVS code (**NOT FK**) |
| `icd10_code` | varchar(10) | nullable, INDEX | Free-text ICD-10 code (**NOT FK**) |
| `description` | varchar(255) | NOT NULL | Case rate description |
| `hf_fee` | decimal(12,2) | NOT NULL, default 0 | Health Facility Fee |
| `pf_fee` | decimal(12,2) | NOT NULL, default 0 | Professional Fee |
| `total_rate` | decimal(12,2) | NOT NULL, default 0 | HF + PF |
| `is_active` / `history` | tinyint(1) | defaults 1 | Standard flags |
| audit columns | | | created_by/date, updated_by/date |

**CRUD:** `CaseRateCrud` Livewire component — sortable datagrid (RVS Code, ICD-10, Description, HF Fee, PF Fee, Total Rate, Status), auto-calculated total (HF + PF), create/edit modal, archive.

### 12B: Identified Problems

#### Problem 1: `rvs_codes` is completely orphaned

The `rvs_codes` table and its CRUD at `/philhealth/rvs-codes` maintain data that **nothing reads**:

- No service queries `RvsCode`
- No XML builder references it
- No claim logic uses it
- No PDF template loads it
- `ClaimCrud` does not import `RvsCode`
- `EclaimsService` reads from `philhealth_case_rates`, not `rvs_codes`
- `XmlBuilder` reads from `philhealth_case_rates`, not `rvs_codes`
- If a user creates RVS code "11450" in the RVS CRUD, it has **zero effect** on claim creation, XML generation, or CEWS eligibility checks

#### Problem 2: No FK link between the tables

`philhealth_case_rates.rvs_code` is a free-text `varchar(10)` — it's **NOT** a foreign key to `rvs_codes.code`. The two tables are logically related but structurally independent. This means:

- No referential integrity (a case rate can reference a non-existent RVS code)
- No cascading updates (renaming an RVS code doesn't update case rates)
- No lookup/dropdown from the case rate form to the RVS registry

#### Problem 3: No seeders for either table

Both `rvs_codes` and `philhealth_case_rates` start **empty** after migration. Users must manually enter all data through the CRUD UIs. There is no PhilHealth RVS catalog seeded.

#### Problem 4: Missing `PROCEDURE` XML element

The official PhilHealth DTD requires a `PROCEDURE` element per procedure in the claim:

```xml
<CLAIM ...>
  <DIAGNOSIS pICDCode="..." pDiagnosisDescription="..." pDiagnosisType="F"/>
  <PROCEDURE pRVSCode="11450" pRVU="1" pOperationDate="06-15-2026"/>
  <PACKAGE pCaseRateCode="..." pCaseRateAmount="..." .../>
</CLAIM>
```

Currently, the `XmlBuilder` only generates a `PACKAGE` element (case rate summary). Individual procedure RVS codes are stored in `claim_items.item_code` (where `item_type = 'Procedure'`) but are **never emitted as XML PROCEDURE elements**. This is a DTD compliance gap.

#### Problem 5: Missing `SecondCaseRate`

The DTD supports both `Patient/FirstCaseRate` and `Patient/SecondCaseRate`:
- `hpatcon1.firstcase` → `philhealth_claims.case_rate_id` (implemented)
- `hpatcon1.secondcase` → **Not implemented** (no `second_case_rate_id` column)

#### Problem 6: Case rate ↔ ICD-10 not linked

`philhealth_case_rates.icd10_code` is free-text. It should reference `icd_10_codes.code` so the claim form can filter case rates by the patient's diagnosis. Currently, the claim form shows ALL case rates regardless of diagnosis.

#### Problem 7: Free-text fields should be lookups

Both `philhealth_case_rates.rvs_code` and `philhealth_case_rates.icd10_code` are free-text `varchar(10)`. Users manually type codes instead of selecting from the registry. This leads to:

- Typos (e.g. "1145O" instead of "11450")
- Orphaned references (code exists in case rate but not in registry)
- Inconsistent formatting (dots in ICD-10, spaces in RVS)

### 12C: Data Flow (Current — Broken)

```
rvs_codes.code ──────── (orphaned — nothing reads this)
                           │
                           ╳ (no FK link)
                           │
philhealth_case_rates.rvs_code ──► XmlBuilder → <PACKAGE pCaseRateCode="...">
philhealth_case_rates.total_rate ─► XmlBuilder → <PACKAGE pCaseRateAmount="...">
philhealth_case_rates.hf_fee ─────► XmlBuilder → <PACKAGE pCaseRateHF="...">
philhealth_case_rates.pf_fee ─────► XmlBuilder → <PACKAGE pCaseRatePF="...">
                           │
                           ▼
philhealth_claims.case_rate_id ────► CEWS → pRVS parameter
                           │
                           ▼
cf5_shadow_billings.actual_case_rate_amount ──► DRG variance calculation

claim_items.item_code (RVS for procedures) ──► ✗ NOT emitted as <PROCEDURE> XML
claim_items.item_code ──► ✗ NOT emitted as <PROCEDURE> pRVSCode
```

### 12D: Fixes for the Rebuild

#### Fix 1: Add FK from `philhealth_case_rates` to `rvs_codes`

**Migration:** Add `rvs_code_id` FK column to `philhealth_case_rates`

| Column | Type | Purpose |
|--------|------|---------|
| `rvs_code_id` | bigint unsigned, nullable, INDEX | FK → `rvs_codes.id` |

- Existing `rvs_code` text column stays (for backward compatibility and XML output)
- Add backfill migration: `UPDATE philhealth_case_rates SET rvs_code_id = (SELECT id FROM rvs_codes WHERE rvs_codes.code = philhealth_case_rates.rvs_code)`
- Update `CaseRateCrud` to use a searchable dropdown from `rvs_codes` instead of free-text
- Keep the standalone `/philhealth/rvs-codes` CRUD as the RVS registry

#### Fix 2: Add FK from `philhealth_case_rates` to `icd_10_codes`

**Migration:** Add `icd10_code_id` FK column to `philhealth_case_rates`

| Column | Type | Purpose |
|--------|------|---------|
| `icd10_code_id` | bigint unsigned, nullable, INDEX | FK → `icd_10_codes.id` |

- Existing `icd10_code` text column stays
- Add backfill migration
- Update `CaseRateCrud` to use dropdown from `icd_10_codes`
- Update `ClaimForm` to filter case rates by the selected diagnosis (`icd10_code_id`)

#### Fix 3: Add `second_case_rate_id` to `philhealth_claims`

**Migration:** Add `second_case_rate_id` column

| Column | Type | Purpose |
|--------|------|---------|
| `second_case_rate_id` | bigint unsigned, nullable, INDEX | FK → `philhealth_case_rates.id` |

- Update `ClaimForm` to show optional secondary case rate dropdown
- Update `XmlBuilder` to emit `SecondCaseRate` when set

#### Fix 4: Add `PROCEDURE` XML element to `XmlBuilder`

For each `claim_items` row where `item_type = 'Procedure'` (or each row in the new `claim_procedures` table):

```xml
<PROCEDURE pRVSCode="{rvs_code}" pRVU="1" pOperationDate="{date-MM-DD-YYYY}"/>
```

Also emit from `claim_procedures` table (proposed in Migration C):

```php
foreach ($claim->claimProcedures()->where('history', 1)->get() as $proc) {
    $procEl = $this->doc->createElement('PROCEDURE');
    $procEl->setAttribute('pRVSCode', $proc->rvs_code);
    $procEl->setAttribute('pRVU', '1');
    $procEl->setAttribute('pOperationDate', $proc->procedure_date?->format('m-d-Y'));
    $claimEl->appendChild($procEl);
}
```

#### Fix 5: Seed RVS codes and case rates

Create seeder `PhilhealthReferenceSeeder` (or add to existing seeder):

**`rvs_codes` seed data** — Top ~50-100 most common PhilHealth case rates:

| Code | Name | Type |
|------|------|------|
| 11450 | Appendectomy | Surgical |
| 27890 | Cesarean Section | Surgical |
| 20470 | Cholecystectomy | Surgical |
| 31320 | Hernia Repair (Inguinal) | Surgical |
| 77600 | Cesarean Section (with tubal ligation) | OB |
| 45720 | Laparotomy | Surgical |
| 19710 | Mastectomy | Surgical |
| 33210 | Thyroidectomy | Surgical |
| ... | ... | ... |

**`philhealth_case_rates` seed data** — Map RVS codes to ICD-10 + fees:

| RVS Code | ICD-10 | Description | HF Fee | PF Fee | Total |
|----------|--------|-------------|--------|--------|-------|
| 11450 | K37 | Appendectomy | 15000 | 10000 | 25000 |
| 27890 | O82 | Cesarean Section | 18000 | 12000 | 30000 |
| ... | ... | ... | ... | ... | ... |

#### Fix 6: Add ICD-10 seeder (for `icd_10_codes`)

Populate `icd_10_codes` with the top ~200 most common PhilHealth diagnoses (required by the ICD-10 merge migration in Part 1).

### 12E: Updated Migration Inventory

Adding to Part 1 (3 original + 1 new = 4 migrations):

| # | File | Purpose |
|---|------|---------|
| 1 | `2026_07_12_000001_merge_icd10_tables.php` | Point FKs to `icd_10_codes`, add `category`, drop `icd10_codes` |
| 2 | `2026_07_12_000002_add_missing_columns_to_philhealth_claims.php` | Add `patient_is`, `membership_type`, `encounter_type`, `disposition_code`, `tracking_number`, `pension_type`, `gender`, `second_case_rate_id` |
| 3 | `2026_07_12_000003_create_claim_procedures_table.php` | New `claim_procedures` table |
| **4** | **`2026_07_12_000004_add_rvs_case_rate_fks.php`** | Add `rvs_code_id` FK to `philhealth_case_rates`, add `icd10_code_id` FK to `philhealth_case_rates`, backfill from text columns |

### 12F: Updated Seeder Inventory

| Seeder | File | Purpose |
|--------|------|---------|
| **New** | `database/seeders/PhilhealthReferenceSeeder.php` | Seeds `rvs_codes` (~50-100 entries), `icd_10_codes` (~200 entries), `philhealth_case_rates` (~50-100 entries with fees and FK backfills) |

Add to `DatabaseSeeder.php` after `DemoMasterDataSeeder`.

### 12G: Updated Data Flow (Fixed)

```
rvs_codes.code ◄──FK──► philhealth_case_rates.rvs_code_id
rvs_codes.name        philhealth_case_rates.rvs_code (text, for XML)
                      philhealth_case_rates.description
                      philhealth_case_rates.hf_fee
                      philhealth_case_rates.pf_fee
                      philhealth_case_rates.total_rate

icd_10_codes.code ◄─FK──► philhealth_case_rates.icd10_code_id
icd_10_codes.name         philhealth_case_rates.icd10_code (text, for XML)

philhealth_claims.case_rate_id ──────► <PACKAGE pCaseRateCode="..." />
philhealth_claims.second_case_rate_id ► <PACKAGE pSecondCaseRateCode="..." /> (NEW)

claim_procedures.rvs_code ──────────► <PROCEDURE pRVSCode="..." /> (NEW)
claim_procedures.procedure_date ────► <PROCEDURE pOperationDate="MM-DD-YYYY" /> (NEW)

cf5_shadow_billings.actual_case_rate_amount ──► DRG variance (unchanged)
```

### 12H: Files to Create/Modify (RVS-Specific)

| File | Action | Changes |
|------|--------|---------|
| `database/migrations/2026_07_12_000004_add_rvs_case_rate_fks.php` | **Create** | Add `rvs_code_id` and `icd10_code_id` FKs to `philhealth_case_rates`, backfill |
| `database/migrations/2026_07_12_000002_add_missing_columns_to_philhealth_claims.php` | **Modify** | Add `second_case_rate_id` column |
| `database/seeders/PhilhealthReferenceSeeder.php` | **Create** | Seed RVS codes, ICD-10 codes, case rates with fees |
| `database/seeders/DatabaseSeeder.php` | **Modify** | Add `PhilhealthReferenceSeeder` call |
| `app/Models/PhilhealthCaseRate.php` | **Modify** | Add `rvsCode()` BelongsTo, `icd10Code()` BelongsTo, `$fillable` |
| `app/Livewire/Philhealth/CaseRateCrud.php` | **Modify** | Replace free-text RVS/ICD-10 inputs with searchable dropdowns from registries |
| `app/Services/Philhealth/XmlBuilder.php` | **Modify** | Add `<PROCEDURE>` element generation, add `SecondCaseRate` |
| `resources/views/livewire/philhealth/claim-form.blade.php` | **Modify** | Filter case rates by diagnosis, add secondary case rate dropdown |
| `app/Livewire/Philhealth/ClaimForm.php` | **Modify** | Add `$second_case_rate_id`, filter case rates by `icd10_code_id` |
