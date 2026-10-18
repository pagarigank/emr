# SITHEMR Database Tables

> ⚠️ **MULTI-TENANT APPLICATION** — Every custom table MUST include `tenant_id` (except `tenants` itself). See `ragdata.md` Section 2.


---

Every **tenant-scoped** custom table follows this structure:
```sql
id              BIGINT UNSIGNED  PK, AUTO_INCREMENT         (never shown in UI grids)
tenant_id       BIGINT UNSIGNED  NOT NULL, INDEX             ← REQUIRED: multi-tenant isolation
--- (business columns here) ---
is_active       TINYINT(1)       NOT NULL, DEFAULT 1         ← business active/inactive flag
history         TINYINT(1)       NOT NULL, DEFAULT 1         ← 1=current, 0=archived (no hard deletes)
created_by      BIGINT UNSIGNED  NULLABLE, FK → users.id
created_date    TIMESTAMP        NULLABLE                    ← set on INSERT
updated_by      BIGINT UNSIGNED  NULLABLE, FK → users.id
updated_date    TIMESTAMP        NULLABLE                    ← set on UPDATE
```

**Rules:**
- No `timestamps()` — use explicit `created_date` / `updated_date`.
- No `DELETE` statements — set `history = 0` to archive.
- All Eloquent models use a global scope: `->where('history', 1)`.
- Auto-increment `id` is **never displayed** in datatable UI grids.

---
### `drugs` (Pharmacy Master List)
```sql
id                  BIGINT UNSIGNED  PK
tenant_id           BIGINT UNSIGNED  FK
drug_code           VARCHAR          UNIQUE
generic_name        VARCHAR          
brand_name          VARCHAR          NULLABLE
drug_strength       VARCHAR          NULLABLE
dosage_form         VARCHAR          NULLABLE
therapeutic_class   VARCHAR          NULLABLE
drug_schedule       VARCHAR          NULLABLE
route               VARCHAR          NULLABLE
active_ingredient   VARCHAR          NULLABLE
manufacturer        VARCHAR          NULLABLE
side_effects        TEXT             NULLABLE
contraindications   TEXT             NULLABLE
reorder_level       INT              
reorder_qty         INT              
is_active           TINYINT(1)       DEFAULT 1
history             TINYINT(1)       DEFAULT 1
philhealth_item_code VARCHAR         NULLABLE
```
*Note: Uses `getDrugNameAttribute()` accessor to combine generic and brand names.*

### `drug_batches` (Pharmacy Inventory/Pricing)
```sql
id                  BIGINT UNSIGNED  PK
tenant_id           BIGINT UNSIGNED  FK
drug_id             BIGINT UNSIGNED  FK -> drugs.id
batch_no            VARCHAR          UNIQUE per drug per tenant
expiry_date         DATE             
quantity_on_hand    DECIMAL(10,2)    
unit_cost           DECIMAL(10,2)    NULLABLE
selling_price       DECIMAL(10,2)    NULLABLE
location            VARCHAR          NULLABLE
is_active           TINYINT(1)       DEFAULT 1
history             TINYINT(1)       DEFAULT 1
```
*Note: Selling Price and Unit Cost are maintained at the batch level (LIFO/FIFO accounting).*
- Unique constraints are **per-tenant** using composite keys `(tenant_id, business_key)`.
- **Exception** — `tenants` table has no `tenant_id` (it IS the root entity).
- **Exception** — `users` table `tenant_id` is nullable (NULL = superadmin).

---

## System / Infrastructure Tables

### 1. `tenants` ✅ (Root entity — NO tenant_id)
Stores company/hospital registrations. 
**CRUD Status**: Completed (`TenantCrud`). Superadmin sees all; Tenant Admins strictly scoped to view/edit their own profile only.

| Column           | Type            | Constraints              | Description                        |
|------------------|-----------------|--------------------------|------------------------------------|
| `id`             | bigint unsigned | PK, auto-increment       | Surrogate PK (not shown in UI)     |
| `tenant_code`    | varchar(30)     | NOT NULL, UNIQUE         | Short code e.g. HOSP01             |
| `tenant_name`    | varchar(150)    | NOT NULL                 | Full company/hospital name         |
| `address`        | varchar(255)    | nullable                 | Street address                     |
| `city`           | varchar(100)    | nullable                 | City                               |
| `contact_number` | varchar(30)     | nullable                 | Contact phone                      |
| `email`          | varchar(100)    | nullable                 | Company email                      |
| `logo_path`      | varchar(255)    | nullable                 | Path to uploaded logo file         |
| `philhealth_hci_code` | varchar(30) | nullable                | PhilHealth-issued HCI facility code (CF2/CF4 headers) |
| `philhealth_accreditation_no` | varchar(50) | nullable          | HCI PhilHealth accreditation number |
| `accreditation_date` | date        | nullable                 | HCI accreditation validity date    |
| `is_active`      | tinyint(1)      | NOT NULL, default 1      | Business active/inactive           |
| `history`        | tinyint(1)      | NOT NULL, default 1      | 1=current, 0=archived              |
| `created_by`     | bigint unsigned | nullable, FK → users.id  | Who created this record            |
| `created_date`   | timestamp       | nullable                 | Creation timestamp                 |
| `updated_by`     | bigint unsigned | nullable, FK → users.id  | Who last updated                   |
| `updated_date`   | timestamp       | nullable                 | Last modification timestamp        |

**Migration**: `0000_12_31_000000_create_tenants_table.php` (runs first) + `2026_06_24_000091_add_philhealth_hci_fields_to_tenants_table.php`
**Model**: `App\Models\Tenant`

---

### 2. `users` ✅ (tenant_id nullable for superadmin)
Stores all users. Two primary roles: `superadmin` and `tenant_admin`.

| Column         | Type            | Constraints                      | Description                              |
|----------------|-----------------|----------------------------------|------------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment               | Surrogate PK (not shown in UI)           |
| `tenant_id`    | bigint unsigned | nullable, INDEX, FK → tenants.id | NULL = superadmin; set = tenant user     |
| `username`     | varchar(60)     | NOT NULL                         | Login username                           |
| `full_name`    | varchar(150)    | NOT NULL                         | User's full name                         |
| `email`        | varchar(150)    | NOT NULL, UNIQUE                 | Email address (used for login)           |
| `password`     | varchar         | NOT NULL                         | Bcrypt hashed password                   |
| `role`         | varchar(30)     | NOT NULL, default 'tenant_admin' | `superadmin` \| `tenant_admin` \| `staff` \| `doctor` |
| `health_care_professional_id` | bigint unsigned | nullable | FK → health_care_professionals.id (links user to doctor profile) |
| `is_active`    | tinyint(1)      | NOT NULL, default 1              | Account enable/disable flag              |
| `history`      | tinyint(1)      | NOT NULL, default 1              | 1=current, 0=archived                    |
| `created_by`   | bigint unsigned | nullable                         | FK → users.id                            |
| `created_date` | timestamp       | nullable                         | Creation timestamp                       |
| `updated_by`   | bigint unsigned | nullable                         | FK → users.id                            |
| `updated_date` | timestamp       | nullable                         | Last modification timestamp              |

**Composite Unique**: `(tenant_id, username)` — usernames unique per tenant.
**Migration**: `0001_01_01_000000_create_users_table.php`
**Model**: `App\Models\User`

**Roles:**
| Role           | tenant_id | Access Scope                        |
|----------------|-----------|-------------------------------------|
| `superadmin`   | NULL      | All tenants — unrestricted          |
| `tenant_admin` | set       | Own tenant only                     |
| `staff`        | set       | Own tenant, limited features        |

**Seeded Accounts:**

| Role           | Email                       | Password    | Tenant             |
|----------------|-----------------------------|-------------|--------------------|
| superadmin     | superadmin@sithemr.com      | Admin@1234  | — (global)         |
| tenant_admin   | admin@demohospital.com      | Admin@1234  | SITHEMR Demo Hospital |

---

## Custom Project Tables

### 3. `departments` ✅ (Migration Ready — pending MSSQL connection)
Stores hospital/clinic departments, scoped per tenant.

| Column         | Type            | Constraints                          | Description                        |
|----------------|-----------------|--------------------------------------|------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                       |
| `tenant_id`    | bigint unsigned | NOT NULL, INDEX                      | Multi-tenant isolation key         |
| `dept_code`    | varchar(20)     | NOT NULL                             | Short dept code (unique per tenant)|
| `dept_name`    | varchar(100)    | NOT NULL                             | Full department name               |
| `description`  | varchar(255)    | nullable                             | Optional description               |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Business active/inactive flag      |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived              |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                      |
| `created_date` | timestamp       | nullable                             | Record creation timestamp          |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                      |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp        |

**Composite Unique**: `(tenant_id, dept_code)`
**Migration**: `2026_06_17_135039_create_departments_table.php`
**Model**: `App\Models\Department`

---

### 4. `countries` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(10)     | NOT NULL, UNIQUE                     | ISO country code (e.g. PH)           |
| `name`         | varchar(100)    | NOT NULL                             | Full country name                    |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `code`
**Migration**: `2026_06_20_000002_create_countries_table.php`
**Model**: `App\Models\Country`

---

### 5. `regions` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `country_id`   | bigint unsigned | NOT NULL, INDEX, FK → countries.id   | Parent country (cascade delete)      |
| `code`         | varchar(20)     | NOT NULL                             | Region code (e.g. NCR)               |
| `name`         | varchar(100)    | NOT NULL                             | Region name                          |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Composite Unique**: `(country_id, code)`
**Migration**: `2026_06_20_000003_create_regions_table.php`
**Model**: `App\Models\Region`

---

### 6. `provinces_cities` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `region_id`    | bigint unsigned | NOT NULL, INDEX, FK → regions.id     | Parent region (cascade delete)       |
| `parent_id`    | bigint unsigned | nullable, FK → provinces_cities.id   | Self-referencing parent (for cities) |
| `code`         | varchar(20)     | NOT NULL                             | Code (e.g. BUL)                      |
| `name`         | varchar(100)    | NOT NULL                             | Province or city name                |
| `type`         | varchar(20)     | NOT NULL, default 'province'         | `province` or `city`                 |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Composite Unique**: `(region_id, code)`
**Migration**: `2026_06_20_000004_create_provinces_cities_table.php`
**Model**: `App\Models\ProvinceCity`

---

### 7. `barangays` ✅ (Global reference — no tenant_id)

| Column            | Type            | Constraints                                | Description                          |
|-------------------|-----------------|--------------------------------------------|--------------------------------------|
| `id`              | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                         |
| `province_city_id`| bigint unsigned | NOT NULL, INDEX, FK → provinces_cities.id  | Parent province/city (cascade delete)|
| `code`            | varchar(20)     | NOT NULL                                   | Barangay code (e.g. 001)             |
| `name`            | varchar(100)    | NOT NULL                                   | Barangay name                        |
| `is_active`       | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                 |
| `history`         | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                |
| `created_by`      | bigint unsigned | nullable                                   | FK → users.id                        |
| `created_date`    | timestamp       | nullable                                   | Creation timestamp                   |
| `updated_by`      | bigint unsigned | nullable                                   | FK → users.id                        |
| `updated_date`    | timestamp       | nullable                                   | Last modification timestamp          |

**Composite Unique**: `(province_city_id, code)`
**Migration**: `2026_06_20_000005_create_barangays_table.php`
**Model**: `App\Models\Barangay`

---

### 8. `religions` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL, UNIQUE                     | Religion code (e.g. RC)              |
| `name`         | varchar(100)    | NOT NULL                             | Full religion name                   |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `code`
**Migration**: `2026_06_20_000006_create_religions_table.php`
**Model**: `App\Models\Religion`

---

### 9. `genders` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL, UNIQUE                     | Gender code (e.g. M, F)              |
| `name`         | varchar(100)    | NOT NULL                             | Full gender name                     |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `code`
**Migration**: `2026_06_20_000007_create_genders_table.php`
**Model**: `App\Models\Gender`

---

### 10. `civil_statuses` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL, UNIQUE                     | Civil status code (e.g. S, M)        |
| `name`         | varchar(100)    | NOT NULL                             | Full civil status name               |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `code`
**Migration**: `2026_06_20_000008_create_civil_statuses_table.php`
**Model**: `App\Models\CivilStatus`

---

### 11. `nationalities` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL, UNIQUE                     | Nationality code (e.g. PH)           |
| `name`         | varchar(100)    | NOT NULL                             | Full nationality name                |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `code`
**Migration**: `2026_06_20_000009_create_nationalities_table.php`
**Model**: `App\Models\Nationality`

---

### 12. `occupations` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL, UNIQUE                     | Occupation code (e.g. DOC)           |
| `name`         | varchar(100)    | NOT NULL                             | Full occupation name                 |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `code`
**Migration**: `2026_06_20_000010_create_occupations_table.php`
**Model**: `App\Models\Occupation`

---

### 13. `blood_types` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(10)     | NOT NULL, UNIQUE                     | Blood type code (e.g. A+)            |
| `name`         | varchar(50)     | NOT NULL                             | Full blood type name                 |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `code`
**Migration**: `2026_06_20_000011_create_blood_types_table.php`
**Model**: `App\Models\BloodType`

---

### 14. `relationships` ✅ (Global reference — no tenant_id)

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL, UNIQUE                     | Relationship code (e.g. SPOUSE)      |
| `name`         | varchar(100)    | NOT NULL                             | Full relationship name               |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `code`
**Migration**: `2026_06_20_000012_create_relationships_table.php`
**Model**: `App\Models\Relationship`

---

---

## Konsulta Module (PhilHealth Primary Care / YAKAP)

### 142. `konsulta_enrollments` ✅ (Tenant-scoped)
Patient enrollment records for the PhilHealth Konsulta primary care package (YAKAP).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `enrollment_code` | varchar(30) | NOT NULL | Auto-generated format: KON-XXXXX |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients.id | Enrolled patient |
| `philhealth_member_id` | bigint unsigned | nullable, FK → philhealth_members.id | Linked PhilHealth member |
| `primary_care_provider_id` | bigint unsigned | nullable, FK → health_care_professionals.id | Assigned primary care provider |
| `enrollment_date` | date | NOT NULL | Date of enrollment |
| `expiry_date` | date | nullable | Enrollment expiry |
| `status` | varchar(30) | NOT NULL, default 'Active' | Active, Transferred, Expired, Cancelled |
| `risk_classification` | varchar(30) | nullable | Low, Moderate, High |
| `fpe_completed` | boolean | NOT NULL, default false | First Physical Exam completed flag |
| `fpe_completed_at` | datetime | nullable | When FPE was completed |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Business active/inactive |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(enrollment_code, tenant_id, history)` — constraint name `konsulta_enr_unique`
**Foreign Keys**: `patient_id` → `patients(id)` NO ACTION, `philhealth_member_id` → `philhealth_members(id)` SET NULL, `primary_care_provider_id` → `health_care_professionals(id)` SET NULL
**Relations**: BelongsTo → `patient()`, `philhealthMember()`, `primaryCareProvider()`; HasMany → `consultations()`, `prescriptions()`, `labReferrals()`
**Migration**: `2026_07_02_120000_create_konsulta_tables.php`
**Model**: `App\Models\Konsulta\KonsultaEnrollment`

### 143. `konsulta_consultations` ✅ (Tenant-scoped)
Primary care consultations under the Konsulta package.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `consultation_code` | varchar(30) | NOT NULL | Auto-generated format: KNC-XXXXX |
| `enrollment_id` | bigint unsigned | nullable (was NOT NULL, see `2026_07_02_120001`), FK → konsulta_enrollments.id | Parent enrollment |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients.id | Patient |
| `provider_id` | bigint unsigned | nullable, FK → health_care_professionals.id | Attending provider |
| `consultation_date` | datetime | NOT NULL | Date and time of consult |
| `chief_complaint` | varchar(500) | nullable | Patient's main complaint |
| `diagnosis` | varchar(500) | nullable | Diagnosis text |
| `icd10_code_id` | bigint unsigned | nullable, FK → icd10_codes.id | ICD-10 diagnosis code |
| `assessment` | text | nullable | Clinical assessment (SOAP) |
| `management_plan` | text | nullable | Management plan (SOAP) |
| `notes` | text | nullable | Additional notes |
| `bp` | varchar(20) | nullable | Blood pressure reading |
| `temperature` | decimal(5,2) | nullable | Temperature in °C |
| `heart_rate` | integer | nullable | Heart rate (bpm) |
| `respiratory_rate` | integer | nullable | Respiratory rate (breaths/min) |
| `weight` | decimal(5,2) | nullable | Weight in kg |
| `height` | decimal(5,2) | nullable | Height in cm |
| `is_fpe` | boolean | NOT NULL, default false | First Physical Exam flag |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Business active/inactive |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(consultation_code, tenant_id, history)` — constraint name `konsulta_cons_unique`
**Foreign Keys**: `enrollment_id` → `konsulta_enrollments(id)` NO ACTION, `patient_id` → `patients(id)` NO ACTION, `provider_id` → `health_care_professionals(id)` SET NULL, `icd10_code_id` → `icd10_codes(id)` SET NULL
**Relations**: BelongsTo → `enrollment()`, `patient()`, `provider()`, `icd10Code()`; HasMany → `prescriptions()`, `labReferrals()`
**Migrations**: `2026_07_02_120000_create_konsulta_tables.php` + `2026_07_02_120001_make_enrollment_id_nullable_in_konsulta_tables.php`
**Model**: `App\Models\Konsulta\KonsultaConsultation`

### 144. `konsulta_prescriptions` ✅ (Tenant-scoped)
Medicine prescriptions issued under Konsulta consultations (GAMOT component).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `consultation_id` | bigint unsigned | NOT NULL, FK → konsulta_consultations.id | Parent consultation |
| `enrollment_id` | bigint unsigned | nullable (was NOT NULL, see `2026_07_02_120001`), FK → konsulta_enrollments.id | Parent enrollment |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients.id | Patient |
| `medicine_name` | varchar(200) | NOT NULL | Name of prescribed medicine |
| `dosage` | varchar(100) | nullable | Dosage instruction |
| `frequency` | varchar(100) | nullable | Frequency (e.g. BID, TID) |
| `duration` | varchar(100) | nullable | Treatment duration |
| `quantity` | integer | NOT NULL, default 0 | Quantity dispensed |
| `is_gamot_covered` | boolean | NOT NULL, default false | Covered by GAMOT benefit |
| `status` | varchar(30) | NOT NULL, default 'Prescribed' | Prescribed, Dispensed, Cancelled |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Business active/inactive |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `consultation_id` → `konsulta_consultations(id)` NO ACTION, `enrollment_id` → `konsulta_enrollments(id)` NO ACTION, `patient_id` → `patients(id)` NO ACTION
**Relations**: BelongsTo → `consultation()`, `enrollment()`, `patient()`
**Migrations**: `2026_07_02_120000_create_konsulta_tables.php` + `2026_07_02_120001_make_enrollment_id_nullable_in_konsulta_tables.php`
**Model**: `App\Models\Konsulta\KonsultaPrescription`

### 145. `konsulta_lab_referrals` ✅ (Tenant-scoped)
Diagnostic test referrals under Konsulta consultations (LABORATORYO component).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `consultation_id` | bigint unsigned | NOT NULL, FK → konsulta_consultations.id | Parent consultation |
| `enrollment_id` | bigint unsigned | nullable (was NOT NULL, see `2026_07_02_120001`), FK → konsulta_enrollments.id | Parent enrollment |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients.id | Patient |
| `test_type` | varchar(200) | NOT NULL | Type of diagnostic test |
| `referral_date` | date | NOT NULL | Date of referral |
| `performed_date` | date | nullable | When test was performed |
| `result` | varchar(500) | nullable | Test result |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Completed, Cancelled |
| `notes` | text | nullable | Additional notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Business active/inactive |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `consultation_id` → `konsulta_consultations(id)` NO ACTION, `enrollment_id` → `konsulta_enrollments(id)` NO ACTION, `patient_id` → `patients(id)` NO ACTION
**Relations**: BelongsTo → `consultation()`, `enrollment()`, `patient()`
**Migrations**: `2026_07_02_120000_create_konsulta_tables.php` + `2026_07_02_120001_make_enrollment_id_nullable_in_konsulta_tables.php`
**Model**: `App\Models\Konsulta\KonsultaLabReferral`

---

## PhilHealth Claims Tables (CF1-CF5 Support)

### 15. `icd10_codes` ✅ (Global reference — no tenant_id)
ICD-10 diagnosis codes for PhilHealth CF2/CF4 claims.

| Column         | Type            | Constraints                          | Description                              |
|----------------|-----------------|--------------------------------------|------------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                             |
| `code`         | varchar(10)     | NOT NULL, UNIQUE                     | ICD-10 code (e.g. J45.0)                 |
| `name`         | varchar(255)    | NOT NULL                             | Diagnosis description                     |
| `category`     | varchar(100)    | nullable                             | Clinical category (e.g. Respiratory)      |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                      |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                     |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                             |
| `created_date` | timestamp       | nullable                             | Creation timestamp                        |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                             |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp               |

**Unique**: `code`
**Migration**: `2026_06_20_000013_create_icd10_codes_table.php`
**Model**: `App\Models\Icd10Code`

---

### 16. `rvs_codes` ✅ (Global reference — no tenant_id)
RVS procedure codes for PhilHealth CF2 claims.

| Column         | Type            | Constraints                          | Description                              |
|----------------|-----------------|--------------------------------------|------------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                             |
| `code`         | varchar(10)     | NOT NULL, UNIQUE                     | RVS code (e.g. 11450)                    |
| `name`         | varchar(255)    | NOT NULL                             | Procedure description                     |
| `type`         | varchar(50)     | nullable                             | Procedure type (e.g. Surgical)           |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                      |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                     |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                             |
| `created_date` | timestamp       | nullable                             | Creation timestamp                        |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                             |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp               |

**Unique**: `code`
**Migration**: `2026_06_20_000014_create_rvs_codes_table.php`
**Model**: `App\Models\RvsCode`

---

### 17. `philhealth_case_rates` ✅ (Global reference — no tenant_id)
All Case Rates (ACR) packages mapping ICD-10/RVS to reimbursement amounts for CF2/CF4.

| Column         | Type            | Constraints                          | Description                              |
|----------------|-----------------|--------------------------------------|------------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                             |
| `rvs_code`     | varchar(10)     | nullable, INDEX                      | Associated RVS code                      |
| `icd10_code`   | varchar(10)     | nullable, INDEX                      | Associated ICD-10 code                   |
| `description`  | varchar(255)    | NOT NULL                             | Case rate description                    |
| `hf_fee`       | decimal(12,2)   | NOT NULL, default 0                  | Health Facility Fee                      |
| `pf_fee`       | decimal(12,2)   | NOT NULL, default 0                  | Professional Fee                         |
| `total_rate`   | decimal(12,2)   | NOT NULL, default 0                  | HF Fee + PF Fee (auto-calculated)        |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                      |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                     |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                             |
| `created_date` | timestamp       | nullable                             | Creation timestamp                        |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                             |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp               |

**Migration**: `2026_06_20_000015_create_philhealth_case_rates_table.php`
**Model**: `App\Models\PhilhealthCaseRate`

---

### 18. `health_care_professionals` ✅ (Tenant-scoped — unified Doctors + HCP)
Unified registry of all medical professionals (Doctors, Nurses, Midwives, etc.) for both clinical use and PhilHealth CF2 claims. Merged from old `doctors` and `health_care_professionals` tables.

| Column                     | Type            | Constraints                                | Description                              |
|----------------------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`                       | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `tenant_id`                | bigint unsigned | NOT NULL, INDEX                            | Multi-tenant isolation key               |
| `professional_code`        | varchar(30)     | NOT NULL                                   | Professional code (unique per tenant)    |
| `professional_type`        | varchar(50)     | NOT NULL, default 'Doctor'                 | Doctor, Nurse, Midwife, etc.             |
| `full_name`                | varchar(150)    | NOT NULL                                   | Full name (auto-generated from parts)    |
| `last_name`                | varchar(50)     | nullable                                   | Last Name (eClaims CF4)                  |
| `first_name`               | varchar(50)     | nullable                                   | First Name (eClaims CF4)                 |
| `middle_name`              | varchar(50)     | nullable                                   | Middle Name (eClaims CF4)                |
| `suffix`                   | varchar(10)     | nullable                                   | Name Suffix (eClaims CF4)                |
| `date_of_birth`            | date            | nullable                                   | Date of Birth (eClaims CF4)              |
| `tin`                      | varchar(30)     | nullable                                   | Tax Identification Number (eClaims CF4)  |
| `specialization`           | varchar(100)    | nullable                                   | Medical specialization                   |
| `prc_license`              | varchar(50)     | nullable                                   | PRC license number                       |
| `philhealth_accreditation` | varchar(50)     | nullable                                   | PhilHealth accreditation number          |
| `department_id`            | bigint unsigned | nullable, INDEX, FK → departments.id       | Assigned department                      |
| `contact_number`           | varchar(30)     | nullable                                   | Contact phone                            |
| `email`                    | varchar(100)    | nullable                                   | Email address                            |
| `is_active`                | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`                  | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`               | bigint unsigned | nullable                                   | FK → users.id                             |
| `created_date`             | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`               | bigint unsigned | nullable                                   | FK → users.id                             |
| `updated_date`             | timestamp       | nullable                                   | Last modification timestamp               |

**Composite Unique**: `(tenant_id, professional_code, history)`
**Foreign Key**: `department_id` → `departments(id)` ON DELETE SET NULL
**Migration**: `2026_06_20_000016_create_health_care_professionals_table.php` (rebuilt by `2026_06_20_000019_rebuild_health_care_professionals_table.php`) + `2026_06_26_154546_add_eclaims_fields_to_health_care_professionals_table.php`
**Model**: `App\Models\HealthCareProfessional`

---

### 19. `philhealth_z_benefits` ✅ (Tenant-scoped)
Specialized table to track CF5 requirements for Z-Benefits (catastrophic cases), including pre-authorization and multiple tranches.

| Column                   | Type            | Constraints                                | Description                              |
|--------------------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`                     | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `tenant_id`              | bigint unsigned | NOT NULL, INDEX                            | Multi-tenant isolation key               |
| `philhealth_claim_id`    | bigint unsigned | NOT NULL, INDEX, FK → philhealth_claims.id | Associated main claim record             |
| `z_benefit_code`         | varchar(20)     | NOT NULL                                   | Code of the Z package (e.g., Z001)       |
| `z_benefit_name`         | varchar(255)    | nullable                                   | Description of the Z package             |
| `pre_authorization_code` | varchar(50)     | nullable                                   | Issued by PhilHealth before treatment    |
| `tranche_number`         | tinyint unsigned| NOT NULL, default 1                        | Payment tranche index (1, 2, 3...)       |
| `tranche_amount`         | decimal(12,2)   | NOT NULL, default 0                        | Amount claimed for this specific tranche |
| `status`                 | varchar(30)     | NOT NULL, default 'Pending'                | Approval status                          |
| `is_active`              | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`                | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`             | bigint unsigned | nullable                                   | FK → users.id                             |
| `created_date`           | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`             | bigint unsigned | nullable                                   | FK → users.id                             |
| `updated_date`           | timestamp       | nullable                                   | Last modification timestamp               |

**Foreign Key**: `philhealth_claim_id` → `philhealth_claims(id)` ON DELETE CASCADE
**Migration**: `2026_06_26_175332_create_philhealth_z_benefits_table.php`
**Model**: `App\Models\PhilhealthZBenefit`

---

### 19. `philhealth_members` ✅ (Tenant-scoped)
PhilHealth member records for CF1 claims filing.

| Column            | Type            | Constraints                                | Description                              |
|-------------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`              | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `tenant_id`       | bigint unsigned | NOT NULL, INDEX                            | Multi-tenant isolation key               |
| `pin`             | varchar(30)     | NOT NULL                                   | PhilHealth Identification Number         |
| `last_name`       | varchar(60)     | NOT NULL                                   | Last name                                |
| `first_name`      | varchar(60)     | NOT NULL                                   | First name                               |
| `middle_name`     | varchar(60)     | nullable                                   | Middle name                              |
| `suffix`          | varchar(20)     | nullable                                   | Name suffix (Jr., Sr., III, etc.)        |
| `date_of_birth`   | date            | nullable                                   | Date of birth                            |
| `gender`          | varchar(10)     | nullable                                   | Male/Female                              |
| `membership_type` | varchar(50)     | nullable                                   | Employed, Self-Employed, OFW, etc.       |
| `contact_number`  | varchar(30)     | nullable                                   | Contact phone                            |
| `email`           | varchar(100)    | nullable                                   | Email address                            |
| `mailing_address` | varchar(255)    | nullable                                   | Mailing address                          |
| `employer_name`   | varchar(255)    | nullable                                   | CF1 — Employer name (for employed members)|
| `employer_address`| varchar(255)    | nullable                                   | CF1 — Employer address                    |
| `employer_contact`| varchar(30)     | nullable                                   | CF1 — Employer contact number             |
| `employer_signature_date` | timestamp | nullable                                 | CF1 — Employer certification signature date |
| `zip_code`        | varchar(10)     | nullable                                   | Zip code                                 |
| `is_active`       | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`         | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`      | bigint unsigned | nullable                                   | FK → users.id                             |
| `created_date`    | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`      | bigint unsigned | nullable                                   | FK → users.id                             |
| `updated_date`    | timestamp       | nullable                                   | Last modification timestamp               |

**Composite Unique**: `(tenant_id, pin)`
**Migration**: `2026_06_20_000017_create_philhealth_members_table.php` + `2026_06_23_000087_add_cf1_cf4_clinical_fields_to_philhealth_tables.php` + `2026_06_24_000090_add_cf1_cf2_cf3_fields_to_philhealth_tables.php`
**Model**: `App\Models\PhilhealthMember`

---

### 146. `eecl_items` ✅ (Global reference — no tenant_id)
PhilHealth-prescribed fixed fee schedule for eClaims (Emergency Case Rates, Diagnostic Services, ER Services, Imaging, Medicines, Supplies).

| Column         | Type            | Constraints                          | Description                              |
|----------------|-----------------|--------------------------------------|------------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                             |
| `item_code`    | varchar(30)     | NOT NULL, UNIQUE                     | Item code (e.g. LAB001, ER001, MED001)  |
| `category`     | varchar(100)    | NOT NULL                             | Category (Diagnostic Services, ER Services, Imaging Services, Medicines, Supplies) |
| `subcategory`  | varchar(100)    | nullable                             | Subcategory (e.g. Blood Chemistry, Analgesics) |
| `item_name`    | varchar(300)    | NOT NULL                             | Item name (e.g. Complete Blood Count, Paracetamol 500mg) |
| `description`  | text            | nullable                             | Optional description                     |
| `unit`         | varchar(50)     | nullable                             | Unit (e.g. test, dose, procedure, day)  |
| `fixed_fee`    | decimal(12,2)   | NOT NULL, default 0                  | PhilHealth-prescribed fixed fee in PHP   |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                     |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                   |
| `created_by`   | bigint unsigned | nullable                             | FK → users.id                            |
| `created_date` | timestamp       | nullable                             | Creation timestamp                       |
| `updated_by`   | bigint unsigned | nullable                             | FK → users.id                            |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp              |

**Unique**: `item_code`
**Note**: Global reference data — no `tenant_id` (PhilHealth-prescribed across all facilities). 145 seed records across 5 categories.
**Migration**: `2026_07_12_000010_create_eecl_items_table.php`
**Seeder**: `EeclSeeder` — 145 items (Diagnostic Services: 27, ER Services: 18, Imaging Services: 16, Medicines: 63, Supplies: 21)
**Model**: `App\Models\Oecb\EeclItem`

---

## Clinical Modules

### 20. `patients` ✅ (Tenant-scoped)
Patient demographic records linking all reference masters and geographic hierarchy.

| Column                    | Type            | Constraints                                | Description                              |
|---------------------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`                      | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `tenant_id`               | bigint unsigned | NOT NULL, INDEX                            | Multi-tenant isolation key               |
| `patient_code`            | varchar(20)     | NOT NULL                                   | Auto-generated code (YYYY + 10-digit sequential, e.g. 20260000000001) |
| `philhealth_member_id`    | bigint unsigned | nullable, FK → philhealth_members.id       | Linked PhilHealth member                 |
| `philhealth_pin`          | varchar(30)     | nullable, INDEX                            | Direct PhilHealth PIN for quick lookup in triage/ER/claims; auto-synced from member when `philhealth_member_id` is set |
| `is_philhealth_member`    | tinyint(1)      | NOT NULL, default 0                        | Boolean flag indicating if patient is a PhilHealth member |
| `last_name`               | varchar(60)     | NOT NULL                                   | Last name                                |
| `first_name`              | varchar(60)     | NOT NULL                                   | First name                               |
| `middle_name`             | varchar(60)     | nullable                                   | Middle name                              |
| `maiden_name`             | varchar(60)     | nullable                                   | Maiden name                              |
| `suffix`                  | varchar(20)     | nullable                                   | Name suffix                              |
| `date_of_birth`           | date            | nullable                                   | Date of birth                            |
| `place_of_birth`          | varchar(255)    | nullable                                   | Place of birth                           |
| `gender_id`               | bigint unsigned | nullable, FK → genders.id                  | Gender reference                         |
| `civil_status_id`         | bigint unsigned | nullable, FK → civil_statuses.id           | Civil status reference                   |
| `religion_id`             | bigint unsigned | nullable, FK → religions.id                | Religion reference                       |
| `nationality_id`          | bigint unsigned | nullable, FK → nationalities.id            | Nationality reference                    |
| `occupation_id`           | bigint unsigned | nullable, FK → occupations.id              | Occupation reference                     |
| `blood_type_id`           | bigint unsigned | nullable, FK → blood_types.id              | Blood type reference                     |
| `preferred_language`      | varchar(60)     | nullable                                   | Preferred language                       |
| `national_id_no`          | varchar(30)     | nullable                                   | National ID (e.g., PhilSys)              |
| `passport_no`             | varchar(30)     | nullable                                   | Passport number                          |
| `contact_no`              | varchar(30)     | nullable                                   | Contact number                           |
| `email`                   | varchar(100)    | nullable                                   | Email address                            |
| `address_street`          | varchar(255)    | nullable                                   | Street address                           |
| `barangay_id`             | bigint unsigned | nullable, FK → barangays.id                | Barangay reference                       |
| `city_id`                 | bigint unsigned | nullable, FK → provinces_cities.id         | City/municipality reference              |
| `province_id`             | bigint unsigned | nullable, FK → provinces_cities.id         | Province/state reference                 |
| `region_id`               | bigint unsigned | nullable, FK → regions.id                  | Region reference                         |
| `country_id`              | bigint unsigned | nullable, FK → countries.id                | Country reference                        |
| `zip_code`                | varchar(10)     | nullable                                   | Zip code                                 |
| `father_name`             | varchar(120)    | nullable                                   | Father's name                            |
| `mother_maiden_name`      | varchar(120)    | nullable                                   | Mother's maiden name                     |
| `spouse_name`             | varchar(120)    | nullable                                   | Spouse's name                            |
| `multiple_birth_indicator`| tinyint(1)      | NOT NULL, default 0                        | Multiple birth (twins, triplets) flag    |
| `is_deceased`             | tinyint(1)      | NOT NULL, default 0                        | Patient deceased indicator               |
| `date_of_death`           | date            | nullable                                   | Date of death                            |
| `primary_care_provider_id`| bigint unsigned | nullable, FK → health_care_professionals.id| Primary care physician                   |
| `emergency_contact_name`  | varchar(120)    | nullable                                   | Emergency contact name                   |
| `emergency_contact_no`    | varchar(30)     | nullable                                   | Emergency contact number                 |
| `emergency_relationship`  | varchar(60)     | nullable                                   | Emergency contact relationship           |
| `is_active`               | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`                 | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`              | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `created_date`            | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`              | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `updated_date`            | timestamp       | nullable                                   | Last modification timestamp               |

**Composite Unique**: `(patient_code, tenant_id, history)`
**Relations**: HasMany → `billingInvoices`, `opdConsultations`, `erAdmissions`, `admissions`, `obConsultations`, `yakapEmpanelments`, `yakapFpeRecords`, `yakapScreenings`, `philhealthClaims`
**Migration**: `2026_06_20_000018_create_patients_table.php` + `2026_06_23_000088_add_philhealth_pin_to_patients_table.php`
**Model**: `App\Models\Patient` — added `getFullNameAttribute()` accessor (constructs from first/middle/last/suffix)

---

## Laboratory Modules

### 21. `lab_sections` ✅ (Tenant-scoped)
Laboratory departments/sections (e.g., Hematology, Clinical Chemistry, Microbiology).

| Column         | Type            | Constraints                                | Description                              |
|----------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `tenant_id`    | bigint unsigned | NOT NULL, INDEX                            | Multi-tenant isolation key               |
| `code`         | varchar(20)     | NOT NULL                                   | Section code (e.g., HEM)                 |
| `name`         | varchar(100)    | NOT NULL                                   | Section name                             |
| `description`  | varchar(255)    | nullable                                   | Optional description                     |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`      | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`   | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `created_date` | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`   | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `updated_date` | timestamp       | nullable                                   | Last modification timestamp               |

**Composite Unique**: `(code, tenant_id, history)`
**Migration**: `2026_06_20_000020_create_lab_sections_table.php`
**Model**: `App\Models\LabSection`

---

### 22. `lab_units` ✅ (Global reference — no tenant_id)
Units of measurement for lab tests (e.g., mg/dL, mmol/L, %, cells/uL).

| Column         | Type            | Constraints                                | Description                              |
|----------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `code`         | varchar(20)     | NOT NULL, UNIQUE                           | Unit code (e.g., MG_DL)                  |
| `name`         | varchar(100)    | NOT NULL                                   | Full unit name (e.g., milligrams/deciliter) |
| `category`     | varchar(50)     | nullable                                   | Category (Concentration, Count, Ratio)   |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`      | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`   | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `created_date` | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`   | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `updated_date` | timestamp       | nullable                                   | Last modification timestamp               |

**Unique**: `(code, history)`
**Migration**: `2026_06_20_000021_create_lab_units_table.php`
**Model**: `App\Models\LabUnit`

---

### 23. `specimen_types` ✅ (Global reference — no tenant_id)
Types of biological specimens/samples accepted by the lab (e.g., Whole Blood, Serum, Urine).

| Column         | Type            | Constraints                                | Description                              |
|----------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `code`         | varchar(20)     | NOT NULL, UNIQUE                           | Specimen code (e.g., WB)                 |
| `name`         | varchar(100)    | NOT NULL                                   | Full specimen type name                  |
| `description`  | varchar(255)    | nullable                                   | Optional description                     |
| `container`    | varchar(100)    | nullable                                   | Collection container (e.g., Lavender top)|
| `is_active`    | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`      | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`   | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `created_date` | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`   | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `updated_date` | timestamp       | nullable                                   | Last modification timestamp               |

**Unique**: `(code, history)`
**Migration**: `2026_06_20_000022_create_specimen_types_table.php`
**Model**: `App\Models\SpecimenType`

---

### 24. `lab_instruments` ✅ (Tenant-scoped)
Laboratory analyzers, machines, and equipment.

| Column            | Type            | Constraints                                | Description                              |
|-------------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`              | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `tenant_id`       | bigint unsigned | NOT NULL, INDEX                            | Multi-tenant isolation key               |
| `code`            | varchar(20)     | NOT NULL                                   | Instrument code (e.g., CHEM-01)          |
| `name`            | varchar(100)    | NOT NULL                                   | Instrument name                          |
| `model`           | varchar(100)    | nullable                                   | Model name/number                        |
| `manufacturer`    | varchar(100)    | nullable                                   | Manufacturer name                        |
| `lab_section_id`  | bigint unsigned | nullable, FK → lab_sections.id             | Assigned lab section                     |
| `serial_number`   | varchar(100)    | nullable                                   | Equipment serial number                  |
| `is_active`       | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`         | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`      | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `created_date`    | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`      | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `updated_date`    | timestamp       | nullable                                   | Last modification timestamp               |

**Composite Unique**: `(code, tenant_id, history)`
**Foreign Key**: `lab_section_id` → `lab_sections(id)` ON DELETE SET NULL
**Migration**: `2026_06_20_000023_create_lab_instruments_table.php`
**Model**: `App\Models\LabInstrument`

---

### 25. `lab_tests` ✅ (Tenant-scoped)
Laboratory test catalog — individual tests and assays performed by the lab.

| Column             | Type            | Constraints                                | Description                              |
|--------------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`               | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `tenant_id`        | bigint unsigned | NOT NULL, INDEX                            | Multi-tenant isolation key               |
| `code`             | varchar(20)     | NOT NULL                                   | Test code (e.g., GLU)                    |
| `name`             | varchar(200)    | NOT NULL                                   | Full test name                           |
| `short_name`       | varchar(50)     | nullable                                   | Abbreviated name for labels/reports      |
| `lab_section_id`   | bigint unsigned | nullable, FK → lab_sections.id             | Owning lab section                       |
| `specimen_type_id` | bigint unsigned | nullable, FK → specimen_types.id           | Required specimen/sample                 |
| `lab_unit_id`      | bigint unsigned | nullable, FK → lab_units.id                | Default reporting unit                   |
| `test_type`        | varchar(20)     | NOT NULL, default 'Numeric'                | Numeric, Text, Categorical               |
| `tat_minutes`      | int             | nullable                                   | Standard turnaround time in minutes      |
| `description`      | varchar(500)    | nullable                                   | Test description/methodology            |
| `standard_charge`  | decimal(12,2)   | NOT NULL, default 0                        | Standard fee for this test                |
| `is_active`        | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`          | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`       | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `created_date`     | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`       | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `updated_date`     | timestamp       | nullable                                   | Last modification timestamp               |

**Composite Unique**: `(code, tenant_id, history)`
**Foreign Keys**: `lab_section_id` → `lab_sections(id)`, `specimen_type_id` → `specimen_types(id)`, `lab_unit_id` → `lab_units(id)` — all ON DELETE SET NULL
**Migration**: `2026_06_20_000024_create_lab_tests_table.php`
**Model**: `App\Models\LabTest`

---

### 26. `lab_panels` + `lab_panel_tests` ✅ (Tenant-scoped)
Test panels/profiles that group multiple individual tests for convenient ordering (e.g., CBC, BMP, Lipid Panel).

**`lab_panels`** (parent):

| Column           | Type            | Constraints                                | Description                              |
|------------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`             | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `tenant_id`      | bigint unsigned | NOT NULL, INDEX                            | Multi-tenant isolation key               |
| `code`           | varchar(20)     | NOT NULL                                   | Panel code (e.g., CBC)                   |
| `name`           | varchar(200)    | NOT NULL                                   | Panel name                               |
| `description`    | varchar(500)    | nullable                                   | Panel description                        |
| `standard_charge`| decimal(12,2)   | NOT NULL, default 0                        | Standard fee for this panel               |
| `lab_section_id` | bigint unsigned | nullable, FK → lab_sections.id             | Owning lab section                       |
| `tat_minutes`    | int             | nullable                                   | Target turnaround time in minutes        |
| `is_active`      | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`        | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`     | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `created_date`   | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`     | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `updated_date`   | timestamp       | nullable                                   | Last modification timestamp               |

**Composite Unique**: `(code, tenant_id, history)`
**Foreign Key**: `lab_section_id` → `lab_sections(id)` ON DELETE SET NULL

**`lab_panel_tests`** (pivot):

| Column          | Type            | Constraints                                | Description                              |
|-----------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`            | bigint unsigned | PK, auto-increment                         | Surrogate PK                             |
| `panel_id`      | bigint unsigned | NOT NULL, FK → lab_panels.id ON DELETE CASCADE | Parent panel                         |
| `test_id`       | bigint unsigned | NOT NULL, FK → lab_tests.id ON DELETE CASCADE  | Included test                         |
| `display_order` | int             | NOT NULL, default 0                        | Display/processing order                 |

**Composite Unique**: `(panel_id, test_id)`
**Migration**: `2026_06_20_000025_create_lab_panels_table.php`
**Model**: `App\Models\LabPanel`

---

### 27. `reference_ranges` ✅ (Global — no tenant_id)
Normal reference ranges for lab tests, stratified by age and gender.

| Column              | Type            | Constraints                                | Description                              |
|---------------------|-----------------|--------------------------------------------|------------------------------------------|
| `id`                | bigint unsigned | PK, auto-increment (not shown in UI)       | Surrogate PK                             |
| `lab_test_id`       | bigint unsigned | NOT NULL, FK → lab_tests.id ON DELETE CASCADE | Parent lab test                       |
| `gender`            | varchar(10)     | NOT NULL, default 'Any'                    | Male, Female, Any                        |
| `age_min_years`     | decimal(5,1)    | NOT NULL, default 0                        | Minimum age in years                     |
| `age_max_years`     | decimal(5,1)    | NOT NULL, default 999.9                    | Maximum age in years                     |
| `min_value`         | decimal(18,6)   | nullable                                   | Lower bound of normal range              |
| `max_value`         | decimal(18,6)   | nullable                                   | Upper bound of normal range              |
| `normal_values_text`| varchar(255)    | nullable                                   | Non-numeric range description            |
| `interpretation`    | varchar(255)    | nullable                                   | Clinical interpretation notes            |
| `is_active`         | tinyint(1)      | NOT NULL, default 1                        | Active/inactive flag                      |
| `history`           | tinyint(1)      | NOT NULL, default 1                        | 1=current, 0=archived                     |
| `created_by`        | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `created_date`      | timestamp       | nullable                                   | Creation timestamp                        |
| `updated_by`        | bigint unsigned | NOT NULL                                   | FK → users.id                             |
| `updated_date`      | timestamp       | nullable                                   | Last modification timestamp               |

**Composite Unique**: `(lab_test_id, gender, age_min_years, age_max_years, history)`
**Foreign Key**: `lab_test_id` → `lab_tests(id)` ON DELETE CASCADE
**Migration**: `2026_06_20_000026_create_reference_ranges_table.php`
**Seeder**: `LabReferenceRangeSeeder` — 46 reference ranges across 27 lab tests (age/gender-stratified, Philippine DOH-aligned)
**Model**: `App\Models\ReferenceRange`

---

## CSR / Supply Module Tables

### 28. `supply_units` ✅ (Global reference — no tenant_id)
Stores units of measurement for supply items.

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL                             | Unit code (e.g. PCS, BOX, REAM)      |
| `name`         | varchar(100)    | NOT NULL                             | Full unit name                       |
| `description`  | varchar(255)    | nullable                             | Optional description                 |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `(code, history)`
**Migration**: `2026_06_20_000027_create_supply_units_table.php`
**Model**: `App\Models\Supply\SupplyUnit`

---

### 29. `item_categories` ✅ (Tenant-scoped)
Stores item categories with self-referencing parent for sub-categories.

| Column         | Type            | Constraints                                     | Description                          |
|----------------|-----------------|-------------------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI)            | Surrogate PK                         |
| `tenant_id`    | bigint unsigned | NOT NULL, INDEX                                 | Multi-tenant isolation key           |
| `code`         | varchar(20)     | NOT NULL                                        | Category code                        |
| `name`         | varchar(100)    | NOT NULL                                        | Category name                        |
| `description`  | varchar(255)    | nullable                                        | Optional description                 |
| `parent_id`    | bigint unsigned | nullable, FK → item_categories.id ON DELETE NO ACTION | Self-referencing parent       |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                             | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                             | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | NOT NULL                                        | FK → users.id                        |
| `created_date` | timestamp       | nullable                                        | Creation timestamp                   |
| `updated_by`   | bigint unsigned | NOT NULL                                        | FK → users.id                        |
| `updated_date` | timestamp       | nullable                                        | Last modification timestamp          |

**Composite Unique**: `(code, tenant_id, history)`
**Migration**: `2026_06_20_000028_create_item_categories_table.php`
**Model**: `App\Models\Supply\ItemCategory`

---

### 30. `suppliers` ✅ (Tenant-scoped)
Stores vendor/supplier information.

| Column           | Type            | Constraints                          | Description                              |
|------------------|-----------------|--------------------------------------|------------------------------------------|
| `id`             | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                             |
| `tenant_id`      | bigint unsigned | NOT NULL, INDEX                      | Multi-tenant isolation key               |
| `code`           | varchar(20)     | NOT NULL                             | Supplier code (e.g. SUP001)              |
| `name`           | varchar(150)    | NOT NULL                             | Company name                             |
| `contact_person` | varchar(100)    | nullable                             | Contact person name                      |
| `contact_number` | varchar(30)     | nullable                             | Phone number                             |
| `email`          | varchar(100)    | nullable                             | Email address                            |
| `address`        | varchar(255)    | nullable                             | Street address                           |
| `tin`            | varchar(30)     | nullable                             | Tax Identification Number                |
| `payment_terms`  | varchar(100)    | nullable                             | e.g. Net 30, COD                         |
| `is_active`      | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                     |
| `history`        | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                    |
| `created_by`     | bigint unsigned | NOT NULL                             | FK → users.id                            |
| `created_date`   | timestamp       | nullable                             | Creation timestamp                       |
| `updated_by`     | bigint unsigned | NOT NULL                             | FK → users.id                            |
| `updated_date`   | timestamp       | nullable                             | Last modification timestamp              |

**Composite Unique**: `(code, tenant_id, history)`
**Migration**: `2026_06_20_000029_create_suppliers_table.php`
**Model**: `App\Models\Supply\Supplier`

---

### 31. `stock_locations` ✅ (Tenant-scoped)
Stores inventory locations with hierarchy (Warehouse > Room > Shelf > Bin > Cabinet).

| Column         | Type            | Constraints                                      | Description                              |
|----------------|-----------------|--------------------------------------------------|------------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI)             | Surrogate PK                             |
| `tenant_id`    | bigint unsigned | NOT NULL, INDEX                                  | Multi-tenant isolation key               |
| `code`         | varchar(20)     | NOT NULL                                         | Location code (e.g. WH-01)               |
| `name`         | varchar(100)    | NOT NULL                                         | Location name                            |
| `description`  | varchar(255)    | nullable                                         | Optional description                     |
| `type`         | varchar(30)     | NOT NULL, default 'Room'                         | Warehouse, Room, Shelf, Bin, Cabinet     |
| `parent_id`    | bigint unsigned | nullable, FK → stock_locations.id ON DELETE NO ACTION | Self-referencing parent            |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                              | Active/inactive flag                     |
| `history`      | tinyint(1)      | NOT NULL, default 1                              | 1=current, 0=archived                    |
| `created_by`   | bigint unsigned | NOT NULL                                         | FK → users.id                            |
| `created_date` | timestamp       | nullable                                         | Creation timestamp                       |
| `updated_by`   | bigint unsigned | NOT NULL                                         | FK → users.id                            |
| `updated_date` | timestamp       | nullable                                         | Last modification timestamp              |

**Composite Unique**: `(code, tenant_id, history)`
**Migration**: `2026_06_20_000030_create_stock_locations_table.php`
**Model**: `App\Models\Supply\StockLocation`

---

### 32. `items` ✅ (Tenant-scoped)
Stores inventory item master records.

| Column            | Type            | Constraints                                          | Description                              |
|-------------------|-----------------|------------------------------------------------------|------------------------------------------|
| `id`              | bigint unsigned | PK, auto-increment (not shown in UI)                 | Surrogate PK                             |
| `tenant_id`       | bigint unsigned | NOT NULL, INDEX                                      | Multi-tenant isolation key               |
| `code`            | varchar(30)     | NOT NULL                                             | Item code (e.g. ITM001 or generic name)  |
| `name`            | varchar(200)    | NOT NULL                                             | Item name                                |
| `description`     | varchar(500)    | nullable                                             | Optional description                     |
| `item_category_id`| bigint unsigned | nullable, FK → item_categories.id ON DELETE SET NULL | Item category                            |
| `supplier_id`     | bigint unsigned | nullable, FK → suppliers.id ON DELETE SET NULL       | Default supplier                         |
| `supply_unit_id`  | bigint unsigned | nullable, FK → supply_units.id ON DELETE SET NULL    | Unit of measure                          |
| `sku`             | varchar(50)     | nullable                                             | Stock Keeping Unit / manufacturer code   |
| `unit_cost`       | decimal(12,2)   | NOT NULL, default 0                                  | Cost per unit                            |
| `reorder_level`   | int             | NOT NULL, default 0                                  | Minimum stock before reorder             |
| `reorder_qty`     | int             | NOT NULL, default 0                                  | Default reorder quantity                 |
| `is_active`       | tinyint(1)      | NOT NULL, default 1                                  | Active/inactive flag                     |
| `history`         | tinyint(1)      | NOT NULL, default 1                                  | 1=current, 0=archived                    |
| `created_by`      | bigint unsigned | NOT NULL                                             | FK → users.id                            |
| `created_date`    | timestamp       | nullable                                             | Creation timestamp                       |
| `updated_by`      | bigint unsigned | NOT NULL                                             | FK → users.id                            |
| `updated_date`    | timestamp       | nullable                                             | Last modification timestamp              |

**Composite Unique**: `(code, tenant_id, history)`
**Foreign Keys**:
- `item_category_id` → `item_categories(id)` ON DELETE SET NULL
- `supplier_id` → `suppliers(id)` ON DELETE SET NULL
- `supply_unit_id` → `supply_units(id)` ON DELETE SET NULL

**Migration**: `2026_06_20_000031_create_items_table.php`
**Model**: `App\Models\Supply\Item`

---

### 32.5. `item_batches` ✅ (Tenant-scoped)
Stores the batch/lot information, expiry, and quantity on hand for items.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `item_id` | bigint unsigned | NOT NULL, FK → items.id ON DELETE CASCADE | Parent item |
| `batch_no` | varchar(50) | NOT NULL | Batch/lot number |
| `expiry_date` | date | nullable | Expiry date |
| `quantity_on_hand` | int | NOT NULL, default 0 | Current quantity available |
| `unit_cost` | decimal(12,2) | nullable | Unit cost of the item batch |
| `selling_price` | decimal(12,2) | nullable | Selling price of the item batch |
| `location` | varchar(255) | nullable | Storage location |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(tenant_id, item_id, batch_no)`
**Migration**: `2026_06_30_092000_create_item_batches_table.php`
**Model**: `App\Models\Supply\ItemBatch`

---

### 32.6. `item_stock_adjustments` ✅ (Tenant-scoped)
Stores the stock adjustments (addition/deduction/expiry/damage) for item batches.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `item_batch_id` | bigint unsigned | NOT NULL, FK → item_batches.id ON DELETE CASCADE | Parent item batch |
| `type` | varchar(50) | NOT NULL | Addition, Deduction, Expiry, Damage |
| `quantity` | int | NOT NULL | Quantity adjusted |
| `reason` | text | nullable | Reason for adjustment |
| `adjusted_by` | bigint unsigned | nullable, FK → users.id | User who made the adjustment |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Migration**: `2026_06_30_092015_create_item_stock_adjustments_table.php`
**Model**: `App\Models\Supply\ItemStockAdjustment`

---

## Procedures Module Tables

### 33. `procedure_categories` ✅ (Global reference — no tenant_id)
Procedure category groupings (Diagnostic, Surgical, Radiology, etc.).

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL                             | Category code (e.g. DX, SX)          |
| `name`         | varchar(100)    | NOT NULL                             | Category name                        |
| `description`  | varchar(255)    | nullable                             | Optional description                 |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `(code, history)`
**Migration**: `2026_06_20_000032_create_procedure_categories_table.php`
**Model**: `App\Models\Procedures\ProcedureCategory`

---

### 34. `procedures` ✅ (Tenant-scoped)
Procedure/service catalog with standard charges, linked to categories and departments.

| Column            | Type            | Constraints                                      | Description                              |
|-------------------|-----------------|--------------------------------------------------|------------------------------------------|
| `id`              | bigint unsigned | PK, auto-increment (not shown in UI)             | Surrogate PK                             |
| `tenant_id`       | bigint unsigned | NOT NULL, INDEX                                  | Multi-tenant isolation key               |
| `code`            | varchar(30)     | NOT NULL                                         | Procedure code (e.g. CATH, CBC)          |
| `name`            | varchar(200)    | NOT NULL                                         | Procedure name                           |
| `description`     | varchar(500)    | nullable                                         | Optional description                     |
| `category_id`     | bigint unsigned | nullable, FK → procedure_categories.id ON DELETE SET NULL | Procedure category              |
| `department_id`   | bigint unsigned | nullable, FK → departments.id ON DELETE SET NULL | Responsible department                   |
| `standard_charge` | decimal(12,2)   | NOT NULL, default 0                              | Standard/hospital charge                 |
| `is_active`       | tinyint(1)      | NOT NULL, default 1                              | Active/inactive flag                     |
| `history`         | tinyint(1)      | NOT NULL, default 1                              | 1=current, 0=archived                    |
| `created_by`      | bigint unsigned | NOT NULL                                         | FK → users.id                            |
| `created_date`    | timestamp       | nullable                                         | Creation timestamp                       |
| `updated_by`      | bigint unsigned | NOT NULL                                         | FK → users.id                            |
| `updated_date`    | timestamp       | nullable                                         | Last modification timestamp              |

**Composite Unique**: `(code, tenant_id, history)`
**Foreign Keys**: `category_id` → `procedure_categories(id)` ON DELETE SET NULL, `department_id` → `departments(id)` ON DELETE SET NULL
**Migration**: `2026_06_20_000033_create_procedures_table.php`
**Model**: `App\Models\Procedures\Procedure`

---

## Renal Care Module Tables

### 35. `dialysis_types` ✅ (Global reference — no tenant_id)
Dialysis modality/types reference (Hemodialysis, Peritoneal Dialysis, CRRT, etc.).

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL                             | Dialysis type code (e.g. HD, PD)     |
| `name`         | varchar(100)    | NOT NULL                             | Full type name                       |
| `description`  | varchar(255)    | nullable                             | Optional description                 |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `(code, history)`
**Migration**: `2026_06_20_000034_create_dialysis_types_table.php`
**Model**: `App\Models\RenalCare\DialysisType`

---

### 36. `dialysis_sessions` ✅ (Tenant-scoped)
Individual dialysis treatment sessions with pre/post vitals, access type, and status tracking.

| Column             | Type            | Constraints                                      | Description                              |
|--------------------|-----------------|--------------------------------------------------|------------------------------------------|
| `id`               | bigint unsigned | PK, auto-increment (not shown in UI)             | Surrogate PK                             |
| `tenant_id`        | bigint unsigned | NOT NULL, INDEX                                  | Multi-tenant isolation key               |
| `code`             | varchar(30)     | NOT NULL                                         | Session code/number                      |
| `patient_id`       | bigint unsigned | NOT NULL, FK → patients.id ON DELETE CASCADE     | Patient receiving dialysis               |
| `dialysis_type_id` | bigint unsigned | nullable, FK → dialysis_types.id ON DELETE SET NULL | Dialysis modality                   |
| `access_type`      | varchar(50)     | nullable                                         | AV Fistula, AV Graft, Catheter, Permcath |
| `session_date`     | date            | nullable                                         | Date of session                          |
| `pre_weight`       | decimal(6,2)    | nullable                                         | Pre-dialysis weight (kg)                 |
| `post_weight`      | decimal(6,2)    | nullable                                         | Post-dialysis weight (kg)                |
| `pre_bp`           | varchar(15)     | nullable                                         | Pre-dialysis BP (e.g. 120/80)            |
| `post_bp`          | varchar(15)     | nullable                                         | Post-dialysis BP                         |
| `heart_rate`       | int             | nullable                                         | Heart rate (bpm)                         |
| `medications`              | text            | nullable                                         | Medications administered during session  |
| `notes`                    | text            | nullable                                         | Clinical notes/observations              |
| `history_of_present_illness` | text          | nullable                                         | HPI narrative (onset, duration, progression) |
| `physical_exam_findings`   | text            | nullable                                         | Physical examination by system            |
| `assessment`               | text            | nullable                                         | Assessment, differential diagnosis & plan |
| `orders_summary`           | text            | nullable                                         | Medications, labs, imaging, procedures ordered |
| `treatment_notes`          | text            | nullable                                         | Treatment orders/notes/progress           |
| `disposition`              | varchar(30)     | NOT NULL, default 'Pending'                      | Pending, Completed, Transferred, Discharged, Cancelled |
| `discharge_instructions`   | text            | nullable                                         | Follow-up care, medications, restrictions |
| `disposition_datetime`     | timestamp       | nullable                                         | When disposition was set                  |
| `status`           | varchar(30)     | NOT NULL, default 'Scheduled'                    | Scheduled, In Progress, Completed, Cancelled |
| `is_active`        | tinyint(1)      | NOT NULL, default 1                              | Active/inactive flag                     |
| `history`          | tinyint(1)      | NOT NULL, default 1                              | 1=current, 0=archived                    |
| `created_by`       | bigint unsigned | NOT NULL                                         | FK → users.id                            |
| `created_date`     | timestamp       | nullable                                         | Creation timestamp                       |
| `updated_by`       | bigint unsigned | NOT NULL                                         | FK → users.id                            |
| `updated_date`     | timestamp       | nullable                                         | Last modification timestamp              |

**Composite Unique**: `(code, tenant_id, history)`
**Foreign Keys**: `patient_id` → `patients(id)` ON DELETE CASCADE, `dialysis_type_id` → `dialysis_types(id)` ON DELETE SET NULL
**Treatment Workflow**: Clinical notes (`history_of_present_illness`, `physical_exam_findings`, `assessment`, `orders_summary`, `treatment_notes`) managed from Treatment form (3-tab: Clinical Notes, Orders & Interventions, Disposition). Orders dispatch `refreshDsOrders` to dispensing modules on status change. Billing picks up via `dialysis_session_id` FK.
**Migrations**: `2026_06_20_000035_create_dialysis_sessions_table.php` + `2026_06_26_000108_add_clinical_fields_to_dialysis_sessions_table.php`
**Model**: `App\Models\RenalCare\DialysisSession`

---

## ICU Module Tables

### 37. `icu_wards` ✅ (Tenant-scoped)
ICU ward/unit definitions (Medical ICU, Surgical ICU, NICU, etc.).

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `tenant_id`    | bigint unsigned | NOT NULL, INDEX                      | Multi-tenant isolation key           |
| `code`         | varchar(20)     | NOT NULL                             | Ward code (e.g. MICU, SICU)          |
| `name`         | varchar(100)    | NOT NULL                             | Ward name                            |
| `location`     | varchar(255)    | nullable                             | Physical location (floor, wing)      |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Composite Unique**: `(code, tenant_id, history)`
**Migration**: `2026_06_20_000036_create_icu_wards_table.php`
**Model**: `App\Models\Icu\IcuWard`

---

### 38. `icu_beds` ✅ (Tenant-scoped)
Individual ICU bed tracking linked to wards.

| Column         | Type            | Constraints                                      | Description                          |
|----------------|-----------------|--------------------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI)             | Surrogate PK                         |
| `tenant_id`    | bigint unsigned | NOT NULL, INDEX                                  | Multi-tenant isolation key           |
| `code`         | varchar(20)     | NOT NULL                                         | Bed code (e.g. MICU-01)              |
| `name`         | varchar(100)    | NOT NULL                                         | Bed name/description                 |
| `ward_id`      | bigint unsigned | nullable, FK → icu_wards.id ON DELETE SET NULL   | Parent ICU ward                      |
| `status`       | varchar(30)     | NOT NULL, default 'Available'                    | Available, Occupied, Maintenance     |
| `room_rate` | decimal(10,2) | nullable | Daily room rate charge for billing |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                              | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                              | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | NOT NULL                                         | FK → users.id                        |
| `created_date` | timestamp       | nullable                                         | Creation timestamp                   |
| `updated_by`   | bigint unsigned | NOT NULL                                         | FK → users.id                        |
| `updated_date` | timestamp       | nullable                                         | Last modification timestamp          |

**Composite Unique**: `(code, tenant_id, history)`
**Foreign Key**: `ward_id` → `icu_wards(id)` ON DELETE SET NULL
**Migration**: `2026_06_20_000037_create_icu_beds_table.php` (updated by `2026_06_22_160001_add_status_to_ward_rooms_and_icu_beds_table.php`)
**Model**: `App\Models\Icu\IcuBed`

---

## Radiology Module Tables

### 39. `radiology_modalities` ✅ (Global reference — no tenant_id)
Imaging equipment/machine types (X-Ray, CT, MRI, Ultrasound, etc.).

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL                             | Modality code (e.g. XR, CT, MRI)     |
| `name`         | varchar(100)    | NOT NULL                             | Full modality name                   |
| `description`  | varchar(255)    | nullable                             | Optional description                 |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `(code, history)`
**Migration**: `2026_06_21_000038_create_radiology_modalities_table.php`
**Model**: `App\Models\Radiology\RadiologyModality`

---

### 40. `radiology_body_parts` ✅ (Global reference — no tenant_id)
Anatomical regions for radiology exam classification (Chest, Abdomen, Head & Neck, etc.).

| Column         | Type            | Constraints                          | Description                          |
|----------------|-----------------|--------------------------------------|--------------------------------------|
| `id`           | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK                         |
| `code`         | varchar(20)     | NOT NULL                             | Body part code (e.g. CHEST, ABD)     |
| `name`         | varchar(100)    | NOT NULL                             | Full anatomical name                 |
| `description`  | varchar(255)    | nullable                             | Optional description                 |
| `is_active`    | tinyint(1)      | NOT NULL, default 1                  | Active/inactive flag                 |
| `history`      | tinyint(1)      | NOT NULL, default 1                  | 1=current, 0=archived                |
| `created_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `created_date` | timestamp       | nullable                             | Creation timestamp                   |
| `updated_by`   | bigint unsigned | NOT NULL                             | FK → users.id                        |
| `updated_date` | timestamp       | nullable                             | Last modification timestamp          |

**Unique**: `(code, history)`
**Migration**: `2026_06_21_000039_create_radiology_body_parts_table.php`
**Model**: `App\Models\Radiology\RadiologyBodyPart`

---

### 41. `radiology_exam_types` ✅ (Tenant-scoped)
Radiology examination/procedure catalog — the billable imaging services offered by the radiology department.

| Column             | Type            | Constraints                                                  | Description                              |
|--------------------|-----------------|--------------------------------------------------------------|------------------------------------------|
| `id`               | bigint unsigned | PK, auto-increment (not shown in UI)                         | Surrogate PK                             |
| `tenant_id`        | bigint unsigned | NOT NULL, INDEX                                              | Multi-tenant isolation key               |
| `code`             | varchar(30)     | NOT NULL                                                     | Exam code (e.g. CXR-PA, CT-BRAIN)        |
| `name`             | varchar(200)    | NOT NULL                                                     | Exam name                                |
| `description`      | varchar(500)    | nullable                                                     | Optional description                     |
| `modality_id`      | bigint unsigned | nullable, FK → radiology_modalities.id ON DELETE SET NULL    | Imaging modality                         |
| `body_part_id`     | bigint unsigned | nullable, FK → radiology_body_parts.id ON DELETE SET NULL    | Anatomical region                        |
| `department_id`    | bigint unsigned | nullable, FK → departments.id ON DELETE SET NULL             | Responsible department                   |
| `standard_charge`  | decimal(12,2)   | NOT NULL, default 0                                          | Standard fee for the exam                |
| `preparation`      | varchar(500)    | nullable                                                     | Patient preparation instructions         |
| `contrast_type`    | varchar(50)     | nullable                                                     | IV Contrast, Oral Contrast, IV + Oral, Rectal Contrast |
| `tat_minutes`      | int             | nullable                                                     | Target turnaround time in minutes        |
| `is_active`        | tinyint(1)      | NOT NULL, default 1                                          | Active/inactive flag                     |
| `history`          | tinyint(1)      | NOT NULL, default 1                                          | 1=current, 0=archived                    |
| `created_by`       | bigint unsigned | NOT NULL                                                     | FK → users.id                            |
| `created_date`     | timestamp       | nullable                                                     | Creation timestamp                       |
| `updated_by`       | bigint unsigned | NOT NULL                                                     | FK → users.id                            |
| `updated_date`     | timestamp       | nullable                                                     | Last modification timestamp              |

**Composite Unique**: `(code, tenant_id, history)`
**Foreign Keys**: `modality_id` → `radiology_modalities(id)`, `body_part_id` → `radiology_body_parts(id)`, `department_id` → `departments(id)` — all ON DELETE SET NULL
**Migration**: `2026_06_21_000040_create_radiology_exam_types_table.php`
**Model**: `App\Models\Radiology\RadiologyExamType`

---

## MICU Module Tables

### 42. `micu_assessment_parameters` ✅ (Global reference — no tenant_id)
Clinical monitoring parameters used in MICU assessments (HR, BP, RR, O2 Sat, GCS, Temp, etc.).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Parameter code (e.g. HR, BP_SYS) |
| `name` | varchar(100) | NOT NULL | Full parameter name |
| `description` | varchar(255) | nullable | Optional description |
| `category` | varchar(50) | nullable | Parameter category (Vital Signs, Neuro, Hemodynamic, Renal, Metabolic) |
| `default_unit` | varchar(30) | nullable | Default measurement unit (e.g. bpm, mmHg, %) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_21_000041_create_micu_assessment_parameters_table.php`
**Model**: `App\Models\MICU\MicuAssessmentParameter`

---

### 42a. `micu_assessments` 🆕 (Tenant-scoped)
Recorded MICU clinical assessments linked to a patient admission — each assessment captures multiple parameter readings.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `admission_id` | bigint unsigned | NOT NULL, FK → admissions(id) ON DELETE CASCADE | Parent admission |
| `assessed_by` | bigint unsigned | NOT NULL, FK → health_care_professionals(id) ON DELETE NO ACTION | HCP who performed assessment |
| `assessed_at` | timestamp | NOT NULL | When assessment was performed |
| `notes` | text | nullable | Clinical notes/observations |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Relations**: BelongsTo → `admission` (Admission), `assessedBy` (HealthCareProfessional); HasMany → `values` (MicuAssessmentValue)
**Migration**: `2026_06_26_000113_create_micu_assessments_tables.php`
**Model**: `App\Models\MICU\MicuAssessment`

---

### 42b. `micu_assessment_values` 🆕 (No tenant_id — child of micu_assessments)
Individual parameter readings within a MICU assessment — stores the value and unit for each assessment parameter.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `micu_assessment_id` | bigint unsigned | NOT NULL, FK → micu_assessments(id) ON DELETE CASCADE | Parent assessment |
| `micu_assessment_parameter_id` | bigint unsigned | NOT NULL, FK → micu_assessment_parameters(id) ON DELETE NO ACTION | Parameter being measured |
| `value` | varchar(50) | nullable | Measured value |
| `unit` | varchar(30) | nullable | Measurement unit |

**Relations**: BelongsTo → `assessment` (MicuAssessment), `parameter` (MicuAssessmentParameter)
**Migration**: `2026_06_26_000113_create_micu_assessments_tables.php` (same file, second table)
**Model**: `App\Models\MICU\MicuAssessmentValue`

---

## Delivery Module Tables

### 43. `delivery_types` ✅ (Global reference — no tenant_id)
Childbirth delivery method types (NSVD, AVB, C/S, VBAC, etc.).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Delivery type code (e.g. NSVD, C-S_EL) |
| `name` | varchar(100) | NOT NULL | Full type name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_21_000042_create_delivery_types_table.php`
**Model**: `App\Models\Delivery\DeliveryType`

---

### 44. `delivery_complications` ✅ (Global reference — no tenant_id)
Possible complications during childbirth (PPH, Preeclampsia, GDM, Cord Prolapse, Shoulder Dystocia, etc.).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Complication code (e.g. PPH, PREECLAMPSIA) |
| `name` | varchar(100) | NOT NULL | Full complication name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_21_000043_create_delivery_complications_table.php`
**Model**: `App\Models\Delivery\DeliveryComplication`

---

## Wards Module Tables

### 45. `ward_room_types` ✅ (Global reference — no tenant_id)
Room classification types for ward management (Private, Semi-Private, Ward, Suite, Isolation).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Room type code (e.g. PRIVATE, WARD) |
| `name` | varchar(100) | NOT NULL | Full type name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_21_000044_create_ward_room_types_table.php`
**Model**: `App\Models\Ward\WardRoomType`

---

### 46. `ward_rooms` ✅ (Tenant-scoped)
Individual rooms within hospital wards, linked to wards, room types, departments, and pricing.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `code` | varchar(20) | NOT NULL | Room code (e.g. W-101) |
| `name` | varchar(100) | NOT NULL | Room name |
| `ward_id` | bigint unsigned | nullable, FK → wards.id ON DELETE SET NULL | Parent ward (cascade: Wards > Rooms) |
| `room_type_id` | bigint unsigned | nullable, FK → ward_room_types.id ON DELETE SET NULL | Room classification |
| `department_id` | bigint unsigned | nullable, FK → departments.id ON DELETE SET NULL | Responsible department |
| `capacity` | int | NOT NULL, default 1 | Maximum patient capacity |
| `room_rate` | decimal(12,2) | NOT NULL, default 0 | Standard room rate/price per period |
| `extra_bed_rate` | decimal(12,2) | nullable | Additional charge for extra bed |
| `discount_rate` | decimal(5,2) | nullable | Discount percentage (0-100) |
| `status` | varchar(30) | NOT NULL, default 'Available' | Available, Occupied, Maintenance |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(code, tenant_id, history)`
**Foreign Keys**: `ward_id` → `wards(id)` ON DELETE SET NULL, `room_type_id` → `ward_room_types(id)` ON DELETE SET NULL, `department_id` → `departments(id)` ON DELETE SET NULL
**Migration**: `2026_06_21_000045_create_ward_rooms_table.php` (updated by `2026_06_22_000056_add_ward_id_and_pricing_to_ward_rooms_table.php` and `2026_06_22_160001_add_status_to_ward_rooms_and_icu_beds_table.php`)
**Model**: `App\Models\Ward\WardRoom`

---

### 47. `wards` ✅ (Tenant-scoped)
Hospital ward/unit definitions (the parent entity in the Wards > Rooms cascade).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `code` | varchar(20) | NOT NULL | Ward code (e.g. GEN-MED, CARDIO) |
| `name` | varchar(100) | NOT NULL | Ward name |
| `description` | varchar(255) | nullable | Optional description |
| `location` | varchar(255) | nullable | Physical location (floor, building, wing) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(code, tenant_id, history)`
**Migration**: `2026_06_22_000055_create_wards_table.php`
**Model**: `App\Models\Ward\Ward`

---

## OPD Module Tables

### 48. `opd_visit_types` ✅ (Global reference — no tenant_id)
OPD visit type classifications (Booking, General Consultation, Specialist Consultation, Follow-up, Emergency).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Visit type code (e.g. GEN-CON) |
| `name` | varchar(100) | NOT NULL | Full type name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_22_000049_create_opd_visit_types_table.php`
**Model**: `App\Models\Opd\OpdVisitType`

---

### 49. `opd_consultation_types` ✅ (Global reference — no tenant_id)
OPD consultation type classifications (New Patient, Follow-up, Acute, Chronic, Wellness, Pre-Op, Post-Op).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Consultation type code (e.g. NEW-PT) |
| `name` | varchar(100) | NOT NULL | Full type name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_22_000050_create_opd_consultation_types_table.php`
**Model**: `App\Models\Opd\OpdConsultationType`

---

### 50. `opd_referral_sources` ✅ (Global reference — no tenant_id)
OPD referral/source of visit (Walk-in, Physician Referral, ER Transfer, HMO, Company, Other Dept, External Hospital).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Referral source code (e.g. WALK-IN) |
| `name` | varchar(100) | NOT NULL | Full source name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_22_000051_create_opd_referral_sources_table.php`
**Model**: `App\Models\Opd\OpdReferralSource`

---

### 51. `opd_services` ✅ (Global reference — no tenant_id)
Billable OPD services catalog with standard charges (Consultation Fee, Specialist Fee, Injection Fee, Nebulization, ECG, Dressing, Minor Surgery, Medical Certificate).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Service code (e.g. CONS-FEE) |
| `name` | varchar(100) | NOT NULL | Full service name |
| `description` | varchar(255) | nullable | Optional description |
| `standard_charge` | decimal(12,2) | NOT NULL, default 0 | Standard fee for the service |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_22_000052_create_opd_services_table.php`
**Model**: `App\Models\Opd\OpdService`

---

### 52. `opd_clinics` ✅ (Global reference — no tenant_id)
Sub-specialty clinics within the OPD (General Medicine, Pediatrics, OB-Gyne, HTN/DM, Well-Baby, Family Planning, TB-DOTS, Nutrition).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Clinic code (e.g. GEN-MED) |
| `name` | varchar(100) | NOT NULL | Full clinic name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_22_000053_create_opd_clinics_table.php`
**Model**: `App\Models\Opd\OpdClinic`

---

### 53. `opd_complaints` ✅ (Global reference — no tenant_id)
Common presenting complaints and symptoms for OPD consultations (Fever, Cough, Headache, Abdominal Pain, Body Pain, Colds, Sore Throat, Dizziness, Diarrhea, Vomiting, Chest Pain, Rash, Dyspnea, Fatigue, Cough > 2 Weeks).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Complaint code (e.g. FEVER) |
| `name` | varchar(100) | NOT NULL | Full complaint/symptom name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_22_000054_create_opd_complaints_table.php`
**Model**: `App\Models\Opd\OpdComplaint`

---

### 53.5. `opd_consultations` ✅ (Tenant-scoped)
The core transaction table for Outpatient Department (OPD) patient encounters. Captures nursing vitals during check-in, physician SOAP notes, and acts as the anchor for all OPD clinical orders (Lab, Pharmacy, CSR, Radiology, Procedure).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients.id | Patient receiving consultation |
| `consultation_code` | varchar(30) | NOT NULL | Auto-generated code (e.g. OPD-00001) |
| `opd_clinic_id` | bigint unsigned | nullable, FK → opd_clinics.id | Clinic visited |
| `attending_physician_id` | bigint unsigned | nullable, FK → hcp.id | Doctor |
| `consultation_type_id` | bigint unsigned | nullable, FK → opd_consultation_types.id | Consultation Type |
| `visit_type_id` | bigint unsigned | nullable, FK → opd_visit_types.id | Visit Type |
| `referral_source_id` | bigint unsigned | nullable, FK → opd_referral_sources.id | Referral Source |
| `consultation_datetime` | datetime | NOT NULL | When the consultation occurred |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, In Progress, Completed, Cancelled |
| `vital_*` | various | nullable | Nursing Vitals (BP, HR, Temp, RR, Wt, Ht, SpO2) |
| `chief_complaint` | text | nullable | Why the patient is here |
| `history_of_present_illness` | text | nullable | SOAP: Subjective |
| `physical_exam_findings` | text | nullable | SOAP: Objective |
| `assessment` | text | nullable | SOAP: Assessment / Diagnosis |
| `icd_10_code` | varchar(20) | nullable | Standardized ICD-10 diagnosis code |
| `treatment_plan` | text | nullable | SOAP: Plan |
| `disposition` | varchar(50) | nullable | Discharged, Admitted, Follow-up |
| `is_active`, `history` | tinyint(1) | NOT NULL | Audit flags |

**Unique**: `(consultation_code, tenant_id, history)`
**Migration**: `2026_06_25_025645_create_opd_consultations_table.php` + `2026_06_26_172344_add_icd_10_code_to_encounter_tables.php`
**Model**: `App\Models\Opd\OpdConsultation`

---

### 54. `ob_visit_types` ✅ (Global reference — no tenant_id)
OB visit type classifications (Prenatal, Postnatal, High-Risk, Teen Pregnancy, Post-Abortion Care).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Visit type code (e.g. PRENATAL) |
| `name` | varchar(100) | NOT NULL | Full visit type name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_22_000046_create_ob_visit_types_table.php`
**Model**: `App\Models\Ob\ObVisitType`

---

### 55. `ob_fetal_presentations` ✅ (Global reference — no tenant_id)
Fetal presentation types for delivery management (Vertex, Breech, Transverse, Face, Brow, Compound, Cord).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Presentation code (e.g. BREECH) |
| `name` | varchar(100) | NOT NULL | Full presentation name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_22_000047_create_ob_fetal_presentations_table.php`
**Model**: `App\Models\Ob\ObFetalPresentation`

---

### 56. `ob_contraceptive_methods` ✅ (Global reference — no tenant_id)
Contraceptive method catalog with category classification (Hormonal, IUD, Barrier, Permanent, Natural, Emergency).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Method code (e.g. DMPA) |
| `name` | varchar(100) | NOT NULL | Full method name |
| `description` | varchar(255) | nullable | Optional description |
| `category` | varchar(50) | nullable | Method category (Hormonal / IUD / Barrier / Permanent / Natural / Emergency) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_22_000048_create_ob_contraceptive_methods_table.php`
**Model**: `App\Models\Ob\ObContraceptiveMethod`

---

## OB/GYN Consultation Module — `ob_consultations`

### 56.5. `ob_consultations` 🆕 (Tenant-scoped)

Full OB/GYN consultation record with patient vitals, OB history, clinical notes, orders, OR scheduling, and disposition — mirroring the ER Area treatment workflow with OB-specific fields.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `consultation_code` | varchar(30) | NOT NULL | Auto-generated code (OBC-xxxxx) |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) ON DELETE NO ACTION | Patient |
| `attending_physician_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE NO ACTION | Attending OB/GYN |
| `visit_type_id` | bigint unsigned | nullable, FK → ob_visit_types(id) ON DELETE SET NULL | Visit type (Prenatal, Postnatal, etc.) |
| `gestational_age` | varchar(20) | nullable | Gestational age (e.g. 34w2d) |
| `lmp_date` | date | nullable | Last Menstrual Period date |
| `edc_date` | date | nullable | Expected Date of Confinement |
| `gravida` | tinyint unsigned | nullable | Number of pregnancies |
| `para` | tinyint unsigned | nullable | Number of deliveries |
| `full_term` | tinyint unsigned | nullable | Number of full-term deliveries |
| `preterm` | tinyint unsigned | nullable | Number of preterm deliveries |
| `abortion` | tinyint unsigned | nullable | Number of abortions/miscarriages |
| `living_children` | tinyint unsigned | nullable | Number of living children |
| `obstetrical_history` | text | nullable | Full obstetrical history narrative |
| `history_of_present_illness` | text | nullable | HPI narrative |
| `physical_exam_findings` | text | nullable | Physical examination by system |
| `assessment` | text | nullable | Assessment, differential diagnosis & plan |
| `icd_10_code` | varchar(20) | nullable | Standardized ICD-10 diagnosis code |
| `orders_summary` | text | nullable | Medications, labs, imaging, procedures ordered |
| `treatment_notes` | text | nullable | Treatment orders/notes/progress |
| `disposition` | varchar(30) | NOT NULL, default 'Pending' | Pending, Delivered, Admitted to Ward, Admitted to ICU, Discharged Home, AMA, Expired |
| `discharge_instructions` | text | nullable | Follow-up care, medications, restrictions |
| `disposition_datetime` | timestamp | nullable | When disposition was set |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(consultation_code, tenant_id, history)`
**Foreign Keys**: `patient_id` → `patients(id)` ON DELETE NO ACTION, `attending_physician_id` → `health_care_professionals(id)` ON DELETE NO ACTION, `visit_type_id` → `ob_visit_types(id)` ON DELETE SET NULL
**Relations**: BelongsTo → `patient`, `attendingPhysician` (HealthCareProfessional), `visitType` (ObVisitType); HasMany → `labRequests`, `csrRequests`, `radiologyRequests`, `pharmacyRequests`, `procedureRequests`, `orSchedules`, `billingInvoices`, `drSchedules`
**Treatment Workflow**: Clinical notes (HPI, PE, assessment, orders_summary, treatment_notes) managed from Treatment form (4-tab: Clinical Notes, Orders & Interventions, OR Schedule, Disposition). Orders dispatch `refreshObOrders` to dispensing modules on status change. Billing picks up via `ob_consultation_id` FK. OR schedules can also be created from the OR Schedule tab (linked via `ob_consultation_id`).
**CRUD**: `App\Livewire\Ob\ObConsultationCrud`
**Migrations**: `2026_06_26_114500_create_ob_consultations_table.php` + `2026_06_26_172344_add_icd_10_code_to_encounter_tables.php`
**Model**: `App\Models\Ob\ObConsultation`

---

## Emergency Room (ER) Module Tables

### 57. `er_acuity_levels` ✅ (Global reference — no tenant_id)
Emergency Severity Index (ESI) classification levels.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Acuity code (e.g. ESI-1) |
| `name` | varchar(100) | NOT NULL | Full acuity name |
| `description` | varchar(255) | nullable | Optional description |
| `color_code` | varchar(20) | nullable | Hex color code for UI (e.g. #EF4444) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_20_154851_create_er_acuity_levels_table.php`
**Model**: `App\Models\Er\ErAcuityLevel`

---

### 58. `er_mobility_statuses` ✅ (Global reference — no tenant_id)
Patient mobility status classifications for the ER.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `code` | varchar(20) | NOT NULL | Mobility code (e.g. AMBULATORY) |
| `name` | varchar(100) | NOT NULL | Full mobility name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `(code, history)`
**Migration**: `2026_06_20_154852_create_er_mobility_statuses_table.php`
**Model**: `App\Models\Er\ErMobilityStatus`

---

### 59. `er_triage_records` ✅ (Tenant-scoped)
Emergency Room triage assessment and patient registration link.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `triage_code` | varchar(30) | NOT NULL | Auto-generated ER code |
| `patient_id` | bigint unsigned | nullable, FK → patients.id ON DELETE NO ACTION | Linked patient |
| `er_acuity_level_id` | bigint unsigned | nullable, FK → er_acuity_levels.id ON DELETE NO ACTION | ESI classification |
| `er_mobility_status_id` | bigint unsigned | nullable, FK → er_mobility_statuses.id ON DELETE NO ACTION | Mobility at arrival |
| `triage_datetime` | datetime | NOT NULL | Date and time of triage |
| `chief_complaint` | text | NOT NULL | Reason for visit |
| `vital_temperature` | decimal(5,2) | nullable | Temp in Celsius |
| `vital_heart_rate` | int | nullable | Pulse in bpm |
| `vital_respiratory_rate` | int | nullable | Breathing in rpm |
| `vital_bp_systolic` | int | nullable | Systolic BP |
| `vital_bp_diastolic` | int | nullable | Diastolic BP |
| `vital_o2_saturation` | decimal(5,2) | nullable | SpO2 in % |
| `vital_pain_score` | int | nullable | 0-10 scale |
| `level_of_consciousness` | varchar(50) | nullable | Alert, Voice, Pain, Unresponsive |
| `allergies` | text | nullable | Known allergies |
| `notes` | text | nullable | General triage notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(triage_code, tenant_id, history)`
**Migration**: `2026_06_20_154852_create_er_triage_records_table.php`
**Model**: `App\Models\Er\ErTriageRecord`
---

## YAKAP Module Tables (PhilHealth Yaman ng Kalusugan Para sa Atin Program)

### 95. `yakap_empanelments` 🆕 (Tenant-scoped)
Patient enrollment in the YAKAP program — anchors FPE, screenings, and capitation.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `empanelment_code` | varchar(30) | NOT NULL | Auto-generated code (YAKAP-xxxxx) |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) | Enrolled patient |
| `philhealth_member_id` | bigint unsigned | nullable, FK → philhealth_members(id) | Linked PhilHealth member |
| `empanelment_year` | int | NOT NULL | Year of enrollment |
| `empanelment_date` | date | nullable | Date of empanelment |
| `yes_signed_date` | date | nullable | YES (YAKAP Enrollment Sheet) signed date |
| `yes_reference_no` | varchar(50) | nullable | YES form reference number |
| `primary_care_provider_id` | bigint unsigned | nullable, FK → health_care_professionals(id) | Assigned PCP |
| `opd_clinic_id` | bigint unsigned | nullable, FK → opd_clinics(id) | Assigned clinic |
| `risk_classification` | varchar(20) | nullable | Low, Moderate, High |
| `fpe_completed` | tinyint(1) | NOT NULL, default 0 | First Patient Encounter completed flag |
| `first_consultation_id` | bigint unsigned | nullable, FK → opd_consultations(id) | First YAKAP consultation |
| `status` | varchar(30) | NOT NULL, default 'Active' | Active, Transferred, Expired, Cancelled |
| `notes` | text | nullable | General notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(empanelment_code, tenant_id, history)`
**Foreign Keys**: `fpe_consultation_id` → `opd_consultations(id)` ON DELETE SET NULL (added by migration `2026_06_30_120115_add_fpe_consultation_fk_to_yakap_empanelments.php`, deferred because opd_consultations didn't exist at initial migration)
**Migration**: `2026_06_25_000095_create_yakap_empanelments_table.php` + `2026_06_30_120115_add_fpe_consultation_fk_to_yakap_empanelments.php` (deferred FK)
**Model**: `App\Models\Yakap\YakapEmpanelment`

---

### 96. `yakap_fpe_records` 🆕 (Tenant-scoped)
First Patient Encounter — comprehensive health risk assessment for YAKAP.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `empanelment_id` | bigint unsigned | NOT NULL, FK → yakap_empanelments(id) | Parent empanelment |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) | Patient |
| `consultation_id` | bigint unsigned | nullable, FK → opd_consultations(id) | Linked OPD consultation |
| `physician_id` | bigint unsigned | nullable, FK → health_care_professionals(id) | Performing physician |
| `fpe_datetime` | datetime | nullable | Date/time of FPE |
| `height_cm` | decimal(8,2) | nullable | Height in cm |
| `weight_kg` | decimal(8,2) | nullable | Weight in kg |
| `bmi` | decimal(8,2) | nullable | Auto-calculated BMI |
| `waist_circumference_cm` | decimal(8,2) | nullable | Waist circumference in cm |
| `smoking_status` | varchar(30) | nullable | Never, Former, Current |
| `alcohol_use` | varchar(30) | nullable | None, Moderate, Heavy |
| `diet_type` | varchar(100) | nullable | e.g. Balanced, High-fat |
| `physical_activity_level` | varchar(30) | nullable | Sedentary, Moderate, Active |
| `family_history` | text | nullable | Family medical history |
| `past_medical_history` | text | nullable | Patient's past medical history |
| `immunization_history` | text | nullable | Immunization records |
| `obstetric_history` | text | nullable | OB history (if applicable) |
| `risk_classification` | varchar(20) | nullable | Low, Moderate, High |
| `risk_score` | decimal(8,2) | nullable | Computed risk score |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Completed, Pending, Skipped |
| `notes` | text | nullable | Additional notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `consultation_id` → `opd_consultations(id)` ON DELETE SET NULL (added by migration `2026_06_30_120115_add_fpe_consultation_fk_to_yakap_empanelments.php`)
**Migration**: `2026_06_25_000096_create_yakap_fpe_records_table.php` + `2026_06_30_120115_add_fpe_consultation_fk_to_yakap_empanelments.php` (deferred FK)
**Model**: `App\Models\Yakap\YakapFpeRecord`

---

### 97. `yakap_screenings` 🆕 (Tenant-scoped)
YAKAP cancer screening records — tracks the 6 PhilHealth-recommended screenings.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `empanelment_id` | bigint unsigned | NOT NULL, FK → yakap_empanelments(id) | Parent empanelment |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) | Patient |
| `screening_type` | varchar(50) | NOT NULL | Breast, Cervical, Colon, Prostate, Lung, Oral |
| `screening_name` | varchar(200) | NOT NULL | Full screening name (auto-generated from type) |
| `screening_date` | date | nullable | When screening was performed |
| `result` | varchar(30) | nullable | Normal, Abnormal, Positive, Negative, Inconclusive, Not Done |
| `result_details` | text | nullable | Detailed result description |
| `facility_name` | varchar(200) | nullable | Facility where screening was done |
| `performed_by` | bigint unsigned | nullable, FK → health_care_professionals(id) | Who performed the screening |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Completed, Overdue, Skipped |
| `next_screening_due` | date | nullable | Recommended next screening date |
| `notes` | text | nullable | Additional notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Migration**: `2026_06_25_000097_create_yakap_screenings_table.php`
**Model**: `App\Models\Yakap\YakapScreening`

---

### 98. `yakap_capitation_payments` 🆕 (Tenant-scoped)
Capitation payment records — tracks 40%/60% tranche disbursements per empanelment.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `empanelment_id` | bigint unsigned | NOT NULL, FK → yakap_empanelments(id) | Parent empanelment |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) | Patient |
| `tranche` | tinyint unsigned | NOT NULL | 1 = First (40%), 2 = Second (60%) |
| `amount` | decimal(12,2) | NOT NULL, default 0 | Payment amount |
| `payment_code` | varchar(50) | nullable | Payment reference code |
| `payment_date` | date | nullable | Date of payment |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Paid, Cancelled |
| `philhealth_reference` | varchar(100) | nullable | PhilHealth reference/OR number |
| `notes` | text | nullable | Payment notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(empanelment_id, tranche, history)` — one record per tranche per empanelment
**Migration**: `2026_06_25_000098_create_yakap_capitation_payments_table.php`
**Model**: `App\Models\Yakap\YakapCapitationPayment`

---

### 99. YAKAP Fields on `opd_consultations` (Migration)
Extended `opd_consultations` table with YAKAP-specific columns.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `is_yakap` | tinyint(1) | NOT NULL, default 0 | Flagged as YAKAP consultation |
| `empanelment_id` | bigint unsigned | nullable, FK → yakap_empanelments(id) | Linked empanelment |
| `risk_classification` | varchar(20) | nullable | Risk class at time of consult |
| `fpe_completed` | tinyint(1) | NOT NULL, default 0 | FPE completed flag |

**Migration**: `2026_06_25_025646_add_yakap_fields_to_opd_consultations_table.php` (renamed from `000099` to sort after the table creation at `025645`)
**Model Updated**: `App\Models\Opd\OpdConsultation` — added `is_yakap` cast, `empanelment()` BelongsTo, `yakapFpeRecord()` HasOne

---

### 100. YAKAP Fields on Request/Clinic/HCP Tables (Migration)
Extended existing tables with YAKAP-GAMOT and YAKAP accreditation flags.

| Table | Column | Type | Description |
|-------|--------|------|-------------|
| `pharmacy_request_items` | `is_gamot` | tinyint(1), default 0 | Flagged as YAKAP GAMOT (75 essential meds) |
| `lab_request_items` | `is_yakap_lab` | tinyint(1), default 0 | Flagged as YAKAP laboratory (13 tests) |
| `opd_clinics` | `is_yakap_clinic` | tinyint(1), default 0 | Clinic is YAKAP-accredited |
| `health_care_professionals` | `is_yakap_physician` | tinyint(1), default 0 | Physician is YAKAP-nominated |

**Migration**: `2026_06_25_000100_add_yakap_fields_to_request_tables.php`

---

### 60. `er_zones` ✅ (Tenant-scoped)
Emergency Room Zones classification (e.g. Resuscitation, Observation, Fast Track).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `code` | varchar(20) | NOT NULL | Zone code |
| `name` | varchar(100) | NOT NULL | Zone name |
| `description` | varchar(255) | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(code, tenant_id, history)`
**Migration**: `2026_06_20_163441_create_er_zones_table.php`
**Model**: `App\Models\Er\ErZone`

---

### 61. `er_beds` ✅ (Tenant-scoped)
Individual Emergency Room Beds linked to ER Zones.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `code` | varchar(20) | NOT NULL | Bed code |
| `name` | varchar(100) | NOT NULL | Bed name |
| `er_zone_id` | bigint unsigned | nullable, FK → er_zones.id ON DELETE NO ACTION | Parent Zone |
| `status` | varchar(30) | NOT NULL, default 'Available' | Available, Occupied, Maintenance |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(code, tenant_id, history)`
**Migration**: `2026_06_20_163559_create_er_beds_table.php`
**Model**: `App\Models\Er\ErBed`

---

### 62. `er_admissions` ✅ (Tenant-scoped)
Emergency Room Admissions tracking patient encounter details.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `admission_code` | varchar(30) | NOT NULL | ER Admission Encounter Code |
| `er_triage_record_id` | bigint unsigned | nullable, FK → er_triage_records.id ON DELETE NO ACTION | Linked Triage Record |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients.id ON DELETE NO ACTION | Linked Patient |
| `er_bed_id` | bigint unsigned | nullable, FK → er_beds.id ON DELETE NO ACTION | Assigned ER Bed |
| `attending_physician_id` | bigint unsigned | nullable, FK → health_care_professionals.id ON DELETE NO ACTION | Attending Physician |
| `arrival_mode` | varchar(50) | nullable | Mode of arrival (Ambulance, Walk-in, etc) |
| `admission_datetime` | datetime | NOT NULL | Admission Timestamp |
| `initial_diagnosis`          | text            | nullable                                   | Initial ER Diagnosis                     |
| `history_of_present_illness` | text            | nullable                                   | HPI narrative (onset, duration, progression) |
| `physical_exam_findings`     | text            | nullable                                   | Physical examination by system            |
| `assessment`                 | text            | nullable                                   | Assessment, differential diagnosis & plan |
| `icd_10_code`                | varchar(20)     | nullable                                   | Standardized ICD-10 diagnosis code        |
| `orders_summary`             | text            | nullable                                   | Medications, labs, imaging, procedures ordered |
| `treatment_notes`            | text            | nullable                                   | ER treatment orders/notes/progress        |
| `disposition`                | varchar(50)     | NOT NULL, default 'Pending'                | Discharged Home, Admitted to Ward, etc.   |
| `disposition_datetime`       | datetime        | nullable                                   | Timestamp of disposition                  |
| `discharge_instructions`     | text            | nullable                                   | Follow-up care, medications, restrictions |
| `is_oecb`                    | tinyint(1)      | NOT NULL, default 0                        | OECB (Outpatient Emergency Care Benefit) flag |
| `billing_id`                | bigint unsigned | nullable, INDEX                            | FK → billing_invoices(id); set when billed |
| `billing_status`            | varchar(30)     | NOT NULL, default 'Pending'                | Pending, Billed (track billing state)     |
| `status`                     | varchar(30)     | NOT NULL, default 'Active'                 | Active, Completed, Cancelled              |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(admission_code, tenant_id, history)`
**Relations**: HasMany → `labRequests`, `csrRequests`, `radiologyRequests`, `pharmacyRequests`, `procedureRequests`, `orSchedules`, `drSchedules`, `oecbEntries`; HasOne → `admission`
**Treatment Workflow**: Clinical notes (`history_of_present_illness`, `physical_exam_findings`, `assessment`, `orders_summary`, `treatment_notes`) managed from Treatment form (3-tab: Clinical Notes, Orders & Interventions, Disposition). Orders dispatch `refreshErOrders` to dispensing modules on status change. Billing picks up via `er_admission_id` FK.
**Migration**: `2026_06_20_163606_create_er_admissions_table.php` + `2026_06_25_000101_add_oecb_fields_to_er_tables.php` + `2026_06_25_142613_add_billing_fields_to_er_admissions.php` + `2026_06_26_172344_add_icd_10_code_to_encounter_tables.php` + `2026_07_06_021512_create_oecb_entries_table.php` (drops old OECB columns)
**Model**: `App\Models\Er\ErAdmission`

---

## Admission Module Tables

### 78. `admissions` ✅ (Tenant-scoped)
Formal inpatient admissions bridging ER (or other sources) to Ward or ICU bed assignment.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `admission_code` | varchar(30) | NOT NULL | Auto-generated admission code (ADM-xxxxx) |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) ON DELETE NO ACTION | Admitted patient |
| `er_admission_id` | bigint unsigned | nullable, FK → er_admissions(id) ON DELETE NO ACTION | Originating ER encounter |
| `admission_type` | varchar(30) | NOT NULL | Emergency, Urgent, Elective, Routine |
| `admission_source` | varchar(30) | NOT NULL | ER, OPD, Referral, Transfer |
| `admission_date` | datetime | NOT NULL | Admission date/time |
| `admitting_diagnosis` | text | nullable | Admitting/initial diagnosis |
| `attending_physician_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE NO ACTION | Attending physician |
| `admitting_physician_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE NO ACTION | Admitting physician |
| `ward_room_id` | bigint unsigned | nullable, FK → ward_rooms(id) ON DELETE NO ACTION | Assigned ward room |
| `icu_bed_id` | bigint unsigned | nullable, FK → icu_beds(id) ON DELETE NO ACTION | Assigned ICU bed |
| `status` | varchar(30) | NOT NULL, default 'Admitted' | Admitted, Transferred, Discharged |
| `billing_id` | bigint unsigned | nullable, FK to billing_invoices(id) | Associated invoice for room charges |
| `billing_status` | varchar(50) | default 'Pending' | Status of room billing (Pending, Billed) |
| `discharge_date` | datetime | nullable | Discharge timestamp |
| `notes` | text | nullable | Additional notes |
| `history_of_present_illness` | text | nullable | HPI narrative (onset, duration, progression) |
| `physical_exam_findings` | text | nullable | Physical examination by system |
| `assessment` | text | nullable | Assessment, differential diagnosis & plan |
| `icd_10_code` | varchar(20) | nullable | Standardized ICD-10 diagnosis code |
| `orders_summary` | text | nullable | Medications, labs, imaging, procedures ordered |
| `treatment_notes` | text | nullable | Treatment orders/notes/progress |
| `disposition` | varchar(30) | NOT NULL, default 'Pending' | Pending, Completed, Transferred, Discharged, Cancelled |
| `discharge_instructions` | text | nullable | Follow-up care, medications, restrictions |
| `disposition_datetime` | timestamp | nullable | When disposition was set |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(admission_code, tenant_id, history)`
**Foreign Keys**: `patient_id` → `patients(id)`, `er_admission_id` → `er_admissions(id)`, `attending_physician_id` → `health_care_professionals(id)`, `admitting_physician_id` → `health_care_professionals(id)`, `ward_room_id` → `ward_rooms(id)`, `icu_bed_id` → `icu_beds(id)` — all ON DELETE NO ACTION.
**Relations**: BelongsTo → `patient`, `erAdmission`, `attendingPhysician`, `admittingPhysician`, `wardRoom`, `icuBed`; HasMany → `labRequests`, `csrRequests`, `radiologyRequests`, `pharmacyRequests`, `procedureRequests`, `nursingVitals`, `nursingCareNotes`, `medicationAdministrations`, `micuAssessments`, `orSchedules`, `drSchedules`
**Treatment Workflow**: Clinical notes (`history_of_present_illness`, `physical_exam_findings`, `assessment`, `orders_summary`, `treatment_notes`) managed from Treatment form (3-tab: Clinical Notes, Orders & Interventions, Disposition). Orders dispatch `refreshAdmissionOrders` to dispensing modules on status change. Billing picks up via `admission_id` FK.
**CRUD**: `App\Livewire\Admission\AdmissionCrud`
**Migrations**: `2026_06_22_160000_create_admissions_table.php` + `2026_06_26_000112_add_clinical_fields_to_admissions_table.php` + `2026_06_26_172344_add_icd_10_code_to_encounter_tables.php`
**Model**: `App\Models\Admission`

---

## OR Module Tables

### 79. `or_rooms` ✅ (Tenant-scoped)
Operating room definitions with equipment tracking and availability status.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `code` | varchar(20) | NOT NULL | OR room code (e.g. OR-01) |
| `name` | varchar(100) | NOT NULL | OR room name |
| `location` | varchar(100) | nullable | Physical location (floor, wing) |
| `room_type` | varchar(50) | nullable | General, Orthopedic, Neurosurgery, Cardiothoracic, etc. |
| `capacity` | int | NOT NULL, default 1 | Maximum patient capacity |
| `equipment` | text | nullable | Available equipment (C-Arm, Laparoscopic Tower, etc.) |
| `special_requirements` | text | nullable | Sterilization, laminar airflow, negative pressure, etc. |
| `status` | varchar(30) | NOT NULL, default 'Available' | Available, Occupied, Maintenance, Reserved |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(code, tenant_id, history)`
**Relations**: HasMany → `schedules` (OrSchedule)
**CRUD**: `App\Livewire\Or\OrRoomCrud`
**Migration**: `2026_06_22_170000_create_or_rooms_table.php`
**Model**: `App\Models\Or\OrRoom`

### 80. `or_schedules` ✅ (Tenant-scoped)
Surgical schedule entries linking patients, OR rooms, and surgical teams with perioperative documentation.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `schedule_code` | varchar(30) | NOT NULL | Auto-generated schedule code (ORS-xxxxx) |
| `or_room_id` | bigint unsigned | NOT NULL, FK → or_rooms(id) ON DELETE NO ACTION | Assigned OR room |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) ON DELETE NO ACTION | Patient undergoing surgery |
| `er_admission_id` | bigint unsigned | nullable, FK → er_admissions(id) ON DELETE NO ACTION | Originating ER encounter |
| `admission_id` | bigint unsigned | nullable, FK → admissions(id) ON DELETE NO ACTION | Originating ward/ICU admission (from Nursing OR Schedule tab) |
| `ob_consultation_id` | bigint unsigned | nullable, FK → ob_consultations(id) ON DELETE NO ACTION | Originating OB consultation (from OB Consultations OR Schedule tab) |
| `surgeon_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE NO ACTION | Primary surgeon |
| `anesthesiologist_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE NO ACTION | Anesthesiologist |
| `procedure_type` | varchar(100) | nullable | Type of surgical procedure |
| `procedure_description` | text | nullable | Detailed procedure description |
| `scheduled_date` | date | NOT NULL | Date of scheduled surgery |
| `start_time` | time | nullable | Surgery start time |
| `end_time` | time | nullable | Surgery end time |
| `status` | varchar(30) | NOT NULL, default 'Scheduled' | Scheduled, In Progress, Completed, Cancelled |
| `pre_op_notes` | text | nullable | Pre-operative notes |
| `intra_op_notes` | text | nullable | Intra-operative notes |
| `post_op_notes` | text | nullable | Post-operative notes |
| `cancellation_reason` | text | nullable | Reason if cancelled |
| `history_of_present_illness` | text | nullable | HPI narrative (onset, duration, progression) |
| `physical_exam_findings` | text | nullable | Physical examination by system |
| `assessment` | text | nullable | Assessment, differential diagnosis & plan |
| `orders_summary` | text | nullable | Medications, labs, imaging, procedures ordered |
| `treatment_notes` | text | nullable | OR treatment orders/notes/progress |
| `disposition` | varchar(30) | NOT NULL, default 'Pending' | Pending, Completed, Transferred, Discharged, Cancelled |
| `discharge_instructions` | text | nullable | Follow-up care, medications, restrictions |
| `disposition_datetime` | timestamp | nullable | When disposition was set |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(schedule_code, tenant_id, history)`
**Foreign Keys**: `or_room_id` → `or_rooms(id)`, `patient_id` → `patients(id)`, `er_admission_id` → `er_admissions(id)`, `admission_id` → `admissions(id)`, `ob_consultation_id` → `ob_consultations(id)`, `surgeon_id` → `health_care_professionals(id)`, `anesthesiologist_id` → `health_care_professionals(id)` — all ON DELETE NO ACTION.
**Relations**: BelongsTo → `orRoom`, `patient`, `erAdmission`, `admission`, `obConsultation` (ObConsultation), `surgeon`, `anesthesiologist`; HasMany → `labRequests`, `csrRequests`, `radiologyRequests`, `pharmacyRequests`, `procedureRequests`
**Treatment Workflow**: Clinical notes (`history_of_present_illness`, `physical_exam_findings`, `assessment`, `orders_summary`, `treatment_notes`) managed from Treatment form (3-tab: Clinical Notes, Orders & Interventions, Disposition). Orders dispatch `refreshOrOrders` to dispensing modules on status change. Billing picks up via `or_schedule_id` FK. OR schedules can also be created from Nursing → OR Schedule tab (linked via `admission_id`).
**CRUD**: `App\Livewire\Or\OrScheduleCrud`
**Migrations**: `2026_06_22_170001_create_or_schedules_table.php` + `2026_06_26_000110_add_clinical_fields_to_or_schedules_table.php` + `2026_06_26_071045_add_admission_id_to_or_schedules_table.php` + `2026_06_26_114700_add_ob_consultation_id_to_or_schedules_table.php`
**Model**: `App\Models\Or\OrSchedule`

---

## Delivery Room (DR) Module Tables

### 80.5. `dr_rooms` 🆕 (Tenant-scoped)

Delivery room definitions with equipment tracking and availability status.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `code` | varchar(20) | NOT NULL | DR room code (e.g. DR-01) |
| `name` | varchar(100) | NOT NULL | DR room name |
| `location` | varchar(100) | nullable | Physical location (floor, wing) |
| `room_type` | varchar(50) | nullable | Labor Room, Delivery Room, LDR, LDRP, C-Section Suite |
| `capacity` | int | NOT NULL, default 1 | Maximum patient capacity |
| `equipment` | text | nullable | Available equipment (Delivery Bed, Fetal Monitor, Ultrasound, Resuscitation Unit, etc.) |
| `special_requirements` | text | nullable | Sterilization, negative pressure, neonatal resuscitation, etc. |
| `status` | varchar(30) | NOT NULL, default 'Available' | Available, Occupied, Maintenance, Reserved |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(code, tenant_id, history)`
**Relations**: HasMany → `schedules` (DrSchedule)
**CRUD**: `App\Livewire\Delivery\DeliveryRoomCrud`
**Migration**: `2026_06_26_115000_create_dr_rooms_table.php`
**Model**: `App\Models\Delivery\DeliveryRoom`

### 80.6. `dr_schedules` 🆕 (Tenant-scoped)

Delivery schedule entries linking patients, delivery rooms, and delivery teams with full delivery, neonatal, and postpartum documentation.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `schedule_code` | varchar(30) | NOT NULL | Auto-generated schedule code (DRS-xxxxx) |
| `dr_room_id` | bigint unsigned | NOT NULL, FK → dr_rooms(id) ON DELETE NO ACTION | Assigned delivery room |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) ON DELETE NO ACTION | Patient in delivery |
| `er_admission_id` | bigint unsigned | nullable, FK → er_admissions(id) ON DELETE NO ACTION | Originating ER encounter |
| `admission_id` | bigint unsigned | nullable, FK → admissions(id) ON DELETE NO ACTION | Originating ward/ICU admission |
| `ob_consultation_id` | bigint unsigned | nullable, FK → ob_consultations(id) ON DELETE NO ACTION | Originating OB consultation |
| `attending_physician_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE NO ACTION | Attending OB/GYN |
| `pediatrician_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE NO ACTION | Pediatrician for newborn |
| `anesthesiologist_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE NO ACTION | Anesthesiologist |
| `midwife_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE NO ACTION | Attending midwife |
| `delivery_type` | varchar(50) | nullable | NSVD, C/S, AVB, Breech Extraction, Vacuum, Forceps, Others |
| `delivery_description` | text | nullable | Detailed delivery description |
| `scheduled_date` | date | NOT NULL | Date of scheduled/actual delivery |
| `start_time` | time | nullable | Delivery start time |
| `end_time` | time | nullable | Delivery end time |
| `status` | varchar(30) | NOT NULL, default 'Scheduled' | Scheduled, In Progress, Completed, Cancelled |
| `gestational_age` | varchar(20) | nullable | Gestational age (e.g. 38w3d) |
| `lmp_date` | date | nullable | Last Menstrual Period date |
| `edc_date` | date | nullable | Expected Date of Confinement |
| `gravida` | tinyint unsigned | nullable | Number of pregnancies |
| `para` | tinyint unsigned | nullable | Number of deliveries |
| `full_term` | tinyint unsigned | nullable | Number of full-term deliveries |
| `preterm` | tinyint unsigned | nullable | Number of preterm deliveries |
| `abortion` | tinyint unsigned | nullable | Number of abortions/miscarriages |
| `living_children` | tinyint unsigned | nullable | Number of living children |
| `pre_op_notes` | text | nullable | Pre-delivery notes |
| `intra_op_notes` | text | nullable | Intra-delivery notes |
| `post_op_notes` | text | nullable | Post-delivery notes |
| `baby_sex` | varchar(10) | nullable | Male, Female |
| `baby_weight` | decimal(6,2) | nullable | Birth weight in kg |
| `baby_length` | decimal(5,2) | nullable | Birth length in cm |
| `head_circumference` | decimal(5,2) | nullable | Head circumference in cm |
| `apgar_score_1min` | tinyint unsigned | nullable | Apgar score at 1 minute (0-10) |
| `apgar_score_5min` | tinyint unsigned | nullable | Apgar score at 5 minutes (0-10) |
| `apgar_score_10min` | tinyint unsigned | nullable | Apgar score at 10 minutes (0-10) |
| `delivery_complications` | text | nullable | Complications during delivery |
| `newborn_outcome` | varchar(30) | nullable | Live Birth, Stillbirth, Early Neonatal Death, Late Neonatal Death |
| `history_of_present_illness` | text | nullable | HPI narrative |
| `physical_exam_findings` | text | nullable | Physical examination by system |
| `assessment` | text | nullable | Assessment, differential diagnosis & plan |
| `orders_summary` | text | nullable | Medications, labs, imaging, procedures ordered |
| `treatment_notes` | text | nullable | Treatment orders/notes/progress |
| `disposition` | varchar(30) | NOT NULL, default 'Pending' | Pending, Delivered, Admitted to Ward, Admitted to NICU, Discharged Home, Transferred, AMA, Expired, Cancelled |
| `discharge_instructions` | text | nullable | Follow-up care, medications, restrictions |
| `disposition_datetime` | timestamp | nullable | When disposition was set |
| `cancellation_reason` | text | nullable | Reason if cancelled |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(schedule_code, tenant_id, history)`
**Foreign Keys**: `dr_room_id` → `dr_rooms(id)`, `patient_id` → `patients(id)`, `er_admission_id` → `er_admissions(id)`, `admission_id` → `admissions(id)`, `ob_consultation_id` → `ob_consultations(id)`, `attending_physician_id` → `health_care_professionals(id)`, `pediatrician_id` → `health_care_professionals(id)`, `anesthesiologist_id` → `health_care_professionals(id)`, `midwife_id` → `health_care_professionals(id)` — all ON DELETE NO ACTION.
**Relations**: BelongsTo → `drRoom`, `patient`, `erAdmission`, `admission`, `obConsultation`, `attendingPhysician`, `pediatrician`, `anesthesiologist`, `midwife`; HasMany → `labRequests`, `csrRequests`, `radiologyRequests`, `pharmacyRequests`, `procedureRequests`
**Treatment Workflow**: Clinical notes (`history_of_present_illness`, `physical_exam_findings`, `assessment`, `orders_summary`, `treatment_notes`) managed from Treatment form (4-tab: Clinical Notes, Orders & Interventions, Neonatal Outcome, Disposition). Orders dispatch `refreshDrOrders` to dispensing modules on status change. Billing picks up via `dr_schedule_id` FK.
**CRUD**: `App\Livewire\Delivery\DeliveryScheduleCrud`
**Migrations**: `2026_06_26_115001_create_dr_schedules_table.php`
**Model**: `App\Models\Delivery\DeliverySchedule`

---

## Lab Dispensing Module Tables

### 63. `lab_requests` ✅ (Tenant-scoped)
Lab test requisitions/orders from requesting departments, with billing status for billing pickup. Charge slip reference is the request_code when billing_status = 'Billed'.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id`        | bigint unsigned | NOT NULL, INDEX                             | Multi-tenant isolation key                |
| `er_admission_id`  | bigint unsigned | nullable, FK → er_admissions.id ON DELETE SET NULL, INDEX | ER Admission that ordered this       |
| `opd_consultation_id` | bigint unsigned | nullable, FK → opd_consultations.id ON DELETE SET NULL, INDEX | OPD Consultation that ordered this |
| `admission_id`     | bigint unsigned | nullable, FK → admissions.id ON DELETE SET NULL, INDEX    | Ward Admission that ordered this     |
| `dialysis_session_id` | bigint unsigned | nullable, FK → dialysis_sessions.id ON DELETE NO ACTION, INDEX | Dialysis Session that ordered this |
| `ob_consultation_id` | bigint unsigned | nullable, FK → ob_consultations.id ON DELETE SET NULL, INDEX | OB Consultation that ordered this |
| `or_schedule_id` | bigint unsigned | nullable, FK → or_schedules.id ON DELETE NO ACTION, INDEX | OR Schedule that ordered this |
| `dr_schedule_id` | bigint unsigned | nullable, FK → dr_schedules.id ON DELETE SET NULL, INDEX | DR Schedule that ordered this |
| `request_code`     | varchar(30)     | NOT NULL                                   | Auto-generated request code (LR-00001)    |
| `patient_id`       | bigint unsigned | NOT NULL, FK → patients.id ON DELETE NO ACTION | Patient being tested                   |
| `requesting_department_id` | bigint unsigned | nullable, FK → departments.id ON DELETE NO ACTION | Department making the request |
| `requesting_doctor_id` | bigint unsigned | nullable, FK → health_care_professionals.id ON DELETE NO ACTION | Doctor making the request |
| `priority` | varchar(20) | NOT NULL, default 'Routine' | Routine, Urgent, STAT |
| `request_notes` | text | nullable | Clinical notes for the lab |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Collected, Processing, Completed, Cancelled |
| `billing_status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Billed, Exempt, Credited |
| `billing_id` | bigint unsigned | nullable, INDEX | Future FK → billing module |
| `total_charge` | decimal(12,2) | NOT NULL, default 0 | Sum of all line item charges |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(request_code, tenant_id, history)`
**Foreign Keys**: `patient_id` → `patients(id)`, `requesting_department_id` → `departments(id)`, `requesting_doctor_id` → `health_care_professionals(id)` — all ON DELETE NO ACTION. `er_admission_id` → `er_admissions(id)` ON DELETE SET NULL. `opd_consultation_id` → `opd_consultations(id)` ON DELETE SET NULL. `admission_id` → `admissions(id)` ON DELETE SET NULL. `dialysis_session_id` → `dialysis_sessions(id)` ON DELETE NO ACTION. `ob_consultation_id` → `ob_consultations(id)` ON DELETE SET NULL. `or_schedule_id` → `or_schedules(id)` ON DELETE NO ACTION. `dr_schedule_id` → `dr_schedules(id)` ON DELETE SET NULL.
**Indexes**: `billing_status`, `billing_id`, `er_admission_id`, `opd_consultation_id`, `admission_id`, `dialysis_session_id`, `ob_consultation_id`, `or_schedule_id`, `dr_schedule_id`
**Migrations**: `2026_06_22_000057_create_lab_requests_table.php` + `2026_06_26_000109_add_dialysis_session_id_to_requests_tables.php` + `2026_06_26_000111_add_or_schedule_id_to_requests_tables.php` + `2026_06_26_114600_add_ob_consultation_id_to_requests_tables.php` + `2026_06_26_115002_add_dr_schedule_id_to_requests_tables.php`
**Model**: `App\Models\LabRequest`

---

### 64. `lab_request_items` ✅ (Tenant-scoped)
Individual line items within a lab request — each item represents a single test or a panel.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `lab_request_id` | bigint unsigned | NOT NULL, FK → lab_requests.id ON DELETE CASCADE | Parent lab request |
| `item_type` | varchar(20) | NOT NULL | Test, Panel |
| `lab_test_id` | bigint unsigned | nullable, FK → lab_tests.id ON DELETE SET NULL | Individual test reference |
| `lab_panel_id` | bigint unsigned | nullable, FK → lab_panels.id ON DELETE SET NULL | Panel reference |
| `test_name` | varchar(200) | NOT NULL | Denormalized test/panel name |
| `charge_amount` | decimal(12,2) | NOT NULL, default 0 | Line item charge |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Collected, Processing, Completed, Cancelled |
| `result_value` | text | nullable | Numeric result stored as string for flexibility |
| `result_text` | text | nullable | Text-based result |
| `result_categorical` | varchar(100) | nullable | Categorical result (Positive/Negative/Reactive) |
| `flag` | varchar(10) | nullable | H, L, N, Critical, Abnormal, Normal |
| `reference_range` | varchar(100) | nullable | Snapshot of reference range text at entry time |
| `validated_by` | bigint unsigned | nullable, FK ? users.id ON DELETE NO ACTION | User who validated the result |
| `validated_at` | timestamp | nullable | When result was validated |
| `result_notes` | text | nullable | Result interpretation notes |
| `result_date` | timestamp | nullable | When result was entered |
| `performed_by` | bigint unsigned | nullable, FK → users.id ON DELETE NO ACTION | User who performed the test |
| `verified_by` | bigint unsigned | nullable, FK → users.id ON DELETE NO ACTION | User who verified the result |
| `verified_date` | timestamp | nullable | When result was verified |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `lab_request_id` ? `lab_requests(id)` ON DELETE CASCADE, `lab_test_id` ? `lab_tests(id)` ON DELETE SET NULL, `lab_panel_id` ? `lab_panels(id)` ON DELETE SET NULL, `performed_by` ? `users(id)` ON DELETE NO ACTION, `verified_by` ? `users(id)` ON DELETE NO ACTION, `validated_by` ? `users(id)` ON DELETE NO ACTION
**Indexes**: `lab_request_id`, `status`
**Migration**: `2026_06_22_000058_create_lab_request_items_table.php` + `2026_06_28_000001_add_lab_result_enhancements_table.php`
**Model**: `App\Models\LabRequestItem`

---

## CSR / Supply Dispensing Module Tables

### 65. `csr_requests` ✅ (Tenant-scoped)
CSR/Supply requisition requests from hospital departments, with billing status for billing pickup. Supports partial fulfillment via Dispense modal — items can be dispensed in partial quantities, tracking `given_quantity` per item. Charge slip reference is the request_code when billing_status = 'Billed'.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id`        | bigint unsigned | NOT NULL, INDEX                             | Multi-tenant isolation key                |
| `er_admission_id`  | bigint unsigned | nullable, FK → er_admissions.id ON DELETE SET NULL, INDEX | ER Admission that ordered this       |
| `opd_consultation_id` | bigint unsigned | nullable, FK → opd_consultations.id ON DELETE SET NULL, INDEX | OPD Consultation that ordered this |
| `admission_id`     | bigint unsigned | nullable, FK → admissions.id ON DELETE SET NULL, INDEX    | Ward Admission that ordered this     |
| `dialysis_session_id` | bigint unsigned | nullable, FK → dialysis_sessions.id ON DELETE NO ACTION, INDEX | Dialysis Session that ordered this |
| `ob_consultation_id` | bigint unsigned | nullable, FK → ob_consultations.id ON DELETE SET NULL, INDEX | OB Consultation that ordered this |
| `or_schedule_id` | bigint unsigned | nullable, FK → or_schedules.id ON DELETE NO ACTION, INDEX | OR Schedule that ordered this |
| `dr_schedule_id` | bigint unsigned | nullable, FK → dr_schedules.id ON DELETE SET NULL, INDEX | DR Schedule that ordered this |
| `request_code`     | varchar(30)     | NOT NULL                                   | Auto-generated request code (CR-00001)    |
| `requesting_department_id` | bigint unsigned | nullable, FK → departments.id ON DELETE NO ACTION | Department making the request      |
| `requesting_person` | varchar(150) | nullable | Name of person requesting |
| `requesting_doctor_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE SET NULL | Doctor who ordered the request |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Approved, Partial, Complete, Cancelled |
| `billing_status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Billed, Exempt, Credited |
| `billing_id` | bigint unsigned | nullable, INDEX | Future FK → billing module |
| `total_amount` | decimal(12,2) | NOT NULL, default 0 | Sum of all line item costs |
| `notes` | text | nullable | Additional request notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(request_code, tenant_id, history)`
**Foreign Keys**: `requesting_department_id` → `departments(id)` ON DELETE NO ACTION. `requesting_doctor_id` → `health_care_professionals(id)` ON DELETE SET NULL. `er_admission_id` → `er_admissions(id)` ON DELETE SET NULL. `opd_consultation_id` → `opd_consultations(id)` ON DELETE SET NULL. `admission_id` → `admissions(id)` ON DELETE SET NULL. `dialysis_session_id` → `dialysis_sessions(id)` ON DELETE NO ACTION. `ob_consultation_id` → `ob_consultations(id)` ON DELETE SET NULL. `or_schedule_id` → `or_schedules(id)` ON DELETE NO ACTION. `dr_schedule_id` → `dr_schedules(id)` ON DELETE SET NULL.
**Indexes**: `billing_status`, `billing_id`, `er_admission_id`, `opd_consultation_id`, `admission_id`, `dialysis_session_id`, `ob_consultation_id`, `or_schedule_id`, `dr_schedule_id`, `requesting_doctor_id`
**Migrations**: `2026_06_22_000059_create_csr_requests_table.php` + `2026_06_26_000109_add_dialysis_session_id_to_requests_tables.php` + `2026_06_26_000111_add_or_schedule_id_to_requests_tables.php` + `2026_06_26_114600_add_ob_consultation_id_to_requests_tables.php` + `2026_06_26_115002_add_dr_schedule_id_to_requests_tables.php` + `2026_06_27_000001_add_requesting_doctor_id_to_csr_and_radiology_requests.php`
**Model**: `App\Models\CsrRequest`

---

### 66. `csr_request_items` ✅ (Tenant-scoped via parent)
Individual line items within a CSR request — each item represents a single supply item with quantity and cost.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `csr_request_id` | bigint unsigned | NOT NULL, FK → csr_requests.id ON DELETE CASCADE | Parent CSR request |
| `item_id` | bigint unsigned | nullable, FK → items.id ON DELETE SET NULL | Reference to supply item master |
| `item_name` | varchar(200) | NOT NULL | Denormalized item name |
| `item_code` | varchar(30) | NOT NULL | Denormalized item code |
| `supply_unit_name` | varchar(50) | NOT NULL | Denormalized unit name (e.g. Box, Each) |
| `quantity` | decimal(12,2) | NOT NULL, default 0 | Requested quantity |
| `given_quantity` | decimal(12,2) | NOT NULL, default 0 | Cumulative quantity actually dispensed; updated via Dispense modal |
| `unit_cost` | decimal(12,2) | NOT NULL, default 0 | Cost per unit at time of request |
| `total_cost` | decimal(12,2) | NOT NULL, default 0 | given_quantity × unit_cost (updated on each dispense) |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Approved, Partial, Complete, Cancelled |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `csr_request_id` → `csr_requests(id)` ON DELETE CASCADE, `item_id` → `items(id)` ON DELETE SET NULL
**Indexes**: `csr_request_id`
**Migrations**: `2026_06_22_000060_create_csr_request_items_table.php` (base), `2026_06_22_000066_add_given_quantity_to_csr_request_items_table.php` (added `given_quantity`)
**Model**: `App\Models\CsrRequestItem`

---

## Radiology Dispensing Module Tables

### 67. `radiology_requests` ✅ (Tenant-scoped)
Radiology exam requisitions from hospital departments, with billing status for billing pickup. Charge slip reference is the request_code when billing_status = 'Billed'.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id`        | bigint unsigned | NOT NULL, INDEX                             | Multi-tenant isolation key                |
| `er_admission_id`  | bigint unsigned | nullable, FK → er_admissions.id ON DELETE SET NULL, INDEX | ER Admission that ordered this       |
| `opd_consultation_id` | bigint unsigned | nullable, FK → opd_consultations.id ON DELETE SET NULL, INDEX | OPD Consultation that ordered this |
| `admission_id`     | bigint unsigned | nullable, FK → admissions.id ON DELETE SET NULL, INDEX    | Ward Admission that ordered this     |
| `dialysis_session_id` | bigint unsigned | nullable, FK → dialysis_sessions.id ON DELETE NO ACTION, INDEX | Dialysis Session that ordered this |
| `ob_consultation_id` | bigint unsigned | nullable, FK → ob_consultations.id ON DELETE SET NULL, INDEX | OB Consultation that ordered this |
| `or_schedule_id` | bigint unsigned | nullable, FK → or_schedules.id ON DELETE NO ACTION, INDEX | OR Schedule that ordered this |
| `dr_schedule_id` | bigint unsigned | nullable, FK → dr_schedules.id ON DELETE SET NULL, INDEX | DR Schedule that ordered this |
| `request_code`     | varchar(30)     | NOT NULL                                   | Auto-generated request code (RR-00001)    |
| `requesting_department_id` | bigint unsigned | nullable, FK → departments.id ON DELETE NO ACTION | Department making the request      |
| `requesting_person` | varchar(150) | nullable | Name of person requesting |
| `requesting_doctor_id` | bigint unsigned | nullable, FK → health_care_professionals(id) ON DELETE SET NULL | Doctor who ordered the request |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Approved, Partial, Complete, Cancelled |
| `billing_status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Billed, Exempt, Credited |
| `billing_id` | bigint unsigned | nullable, INDEX | Future FK → billing module |
| `total_amount` | decimal(12,2) | NOT NULL, default 0 | Sum of all line item charges |
| `notes` | text | nullable | Additional request notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(request_code, tenant_id, history)`
**Foreign Keys**: `requesting_department_id` → `departments(id)` ON DELETE NO ACTION. `requesting_doctor_id` → `health_care_professionals(id)` ON DELETE SET NULL. `er_admission_id` → `er_admissions(id)` ON DELETE SET NULL. `opd_consultation_id` → `opd_consultations(id)` ON DELETE SET NULL. `admission_id` → `admissions(id)` ON DELETE SET NULL. `dialysis_session_id` → `dialysis_sessions(id)` ON DELETE NO ACTION. `ob_consultation_id` → `ob_consultations(id)` ON DELETE SET NULL. `or_schedule_id` → `or_schedules(id)` ON DELETE NO ACTION. `dr_schedule_id` → `dr_schedules(id)` ON DELETE SET NULL.
**Indexes**: `billing_status`, `er_admission_id`, `opd_consultation_id`, `admission_id`, `dialysis_session_id`, `ob_consultation_id`, `or_schedule_id`, `dr_schedule_id`, `requesting_doctor_id`
**Migrations**: `2026_06_22_000061_create_radiology_requests_table.php` + `2026_06_26_000109_add_dialysis_session_id_to_requests_tables.php` + `2026_06_26_000111_add_or_schedule_id_to_requests_tables.php` + `2026_06_26_114600_add_ob_consultation_id_to_requests_tables.php` + `2026_06_26_115002_add_dr_schedule_id_to_requests_tables.php` + `2026_06_27_000001_add_requesting_doctor_id_to_csr_and_radiology_requests.php`
**Model**: `App\Models\Radiology\RadiologyRequest`

---

### 68. `radiology_request_items` ✅ (Tenant-scoped via parent)
Individual line items within a radiology request — each item represents a single radiology exam type with charge.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `radiology_request_id` | bigint unsigned | NOT NULL, FK → radiology_requests.id ON DELETE CASCADE | Parent radiology request |
| `exam_type_id` | bigint unsigned | nullable, FK → radiology_exam_types.id ON DELETE SET NULL | Reference to exam type master |
| `exam_name` | varchar(200) | NOT NULL | Denormalized exam name |
| `modality_name` | varchar(100) | nullable | Denormalized modality name (e.g. X-Ray, CT Scan, MRI) |
| `charge_amount` | decimal(12,2) | NOT NULL, default 0 | Line item charge (pre-populated from exam type standard_charge) |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Approved, Partial, Complete, Cancelled |
| `result_text` | text | nullable | Radiologist reading / full report |
| `impression` | text | nullable | Radiologist impression / conclusion |
| `technique` | text | nullable | Technique description |
| `findings` | text | nullable | Detailed radiologic findings |
| `doctor_notes` | text | nullable | Additional / internal notes |
| `image_url` | varchar(255) | nullable | Legacy single image path (use radiology_result_images table) |
| `result_date` | timestamp | nullable | When result was finalized |
| `radiologist_id` | bigint unsigned | nullable, FK → health_care_professionals.id | The radiologist who reported the result |
| `performed_by` | bigint unsigned | nullable, FK → users.id ON DELETE SET NULL | Radiologic technologist who performed the exam |
| `performed_at` | timestamp | nullable | When the exam was performed |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `radiology_request_id` → `radiology_requests(id)` ON DELETE CASCADE, `exam_type_id` → `radiology_exam_types(id)` ON DELETE SET NULL, `radiologist_id` → `health_care_professionals(id)` ON DELETE SET NULL, `performed_by` → `users(id)` ON DELETE SET NULL
**Indexes**: `radiology_request_id`, `status`
**Migration**: `2026_06_22_000062_create_radiology_request_items_table.php` + `2026_06_26_171611_add_result_fields_to_radiology_request_items_table.php` + `2026_06_29_000001_add_radiology_result_enhancements_table.php`
**Model**: `App\Models\Radiology\RadiologyRequestItem`

---

### 69. `radiology_result_images` ✅ (Tenant-scoped via parent)
Multi-image attachments for radiology exam results with DICOM UID support for PACS integration.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `radiology_request_item_id` | bigint unsigned | NOT NULL, FK → radiology_request_items.id ON DELETE CASCADE | Parent radiology result item |
| `filename` | varchar(255) | NOT NULL | Stored filename (hash) |
| `original_name` | varchar(255) | NOT NULL | Original upload filename |
| `path` | varchar(500) | NOT NULL | Storage path relative to disk root |
| `mime_type` | varchar(100) | nullable | Image/DICOM MIME type |
| `file_size` | int unsigned | nullable | File size in bytes |
| `dicom_study_uid` | varchar(255) | nullable | DICOM Study Instance UID for PACS linkage |
| `dicom_series_uid` | varchar(255) | nullable | DICOM Series Instance UID |
| `dicom_sop_uid` | varchar(255) | nullable | DICOM SOP Instance UID |
| `is_primary` | tinyint(1) | NOT NULL, default 0 | Primary/representative image flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `radiology_request_item_id` → `radiology_request_items(id)` ON DELETE CASCADE
**Indexes**: `radiology_request_item_id`, `dicom_study_uid`
**Migration**: `2026_06_29_000002_create_radiology_result_images_table.php`
**Model**: `App\Models\Radiology\RadiologyResultImage`

---

## Pharmacy Module Tables

### 69. `pharmacy_requests` ✅ (Tenant-scoped)
Medication/Pharmacy requisition requests from hospital departments, with billing status for billing pickup. Supports medication-specific fields (dosage, frequency, route, duration) and partial fulfillment via Dispense modal. Charge slip reference is the request_code when billing_status = 'Billed'.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `er_admission_id` | bigint unsigned | nullable, FK → er_admissions.id ON DELETE SET NULL, INDEX | ER Admission that ordered this |
| `opd_consultation_id` | bigint unsigned | nullable, FK → opd_consultations.id ON DELETE SET NULL, INDEX | OPD Consultation that ordered this |
| `admission_id`    | bigint unsigned | nullable, FK → admissions.id ON DELETE SET NULL, INDEX    | Ward Admission that ordered this     |
| `dialysis_session_id` | bigint unsigned | nullable, FK → dialysis_sessions.id ON DELETE NO ACTION, INDEX | Dialysis Session that ordered this |
| `ob_consultation_id` | bigint unsigned | nullable, FK → ob_consultations.id ON DELETE SET NULL, INDEX | OB Consultation that ordered this |
| `or_schedule_id` | bigint unsigned | nullable, FK → or_schedules.id ON DELETE NO ACTION, INDEX | OR Schedule that ordered this |
| `dr_schedule_id` | bigint unsigned | nullable, FK → dr_schedules.id ON DELETE SET NULL, INDEX | DR Schedule that ordered this |
| `request_code` | varchar(30) | NOT NULL | Auto-generated request code (PR-00001) |
| `requesting_department_id` | bigint unsigned | nullable, FK → departments.id ON DELETE NO ACTION | Department making the request |
| `requesting_person` | varchar(150) | nullable | Name of person requesting |
| `requesting_doctor_id` | bigint unsigned | nullable, FK → health_care_professionals.id ON DELETE NO ACTION | Doctor making the request |
| `priority` | varchar(20) | NOT NULL, default 'Routine' | Routine, Urgent, STAT |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Approved, Partial, Complete, Cancelled |
| `billing_status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Billed, Exempt, Credited |
| `billing_id` | bigint unsigned | nullable, INDEX | Future FK → billing module |
| `total_amount` | decimal(12,2) | NOT NULL, default 0 | Sum of all line item costs |
| `notes` | text | nullable | Additional request notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(request_code, tenant_id, history)`
**Foreign Keys**: `er_admission_id` → `er_admissions(id)` ON DELETE SET NULL, `opd_consultation_id` → `opd_consultations(id)` ON DELETE SET NULL, `admission_id` → `admissions(id)` ON DELETE SET NULL, `requesting_department_id` → `departments(id)` ON DELETE NO ACTION, `requesting_doctor_id` → `health_care_professionals(id)` ON DELETE NO ACTION, `dialysis_session_id` → `dialysis_sessions(id)` ON DELETE NO ACTION, `ob_consultation_id` → `ob_consultations(id)` ON DELETE SET NULL, `or_schedule_id` → `or_schedules(id)` ON DELETE NO ACTION, `dr_schedule_id` → `dr_schedules(id)` ON DELETE SET NULL
**Indexes**: `billing_status`, `billing_id`, `er_admission_id`, `opd_consultation_id`, `admission_id`, `dialysis_session_id`, `ob_consultation_id`, `or_schedule_id`, `dr_schedule_id`
**Migrations**: `2026_06_22_000067_create_pharmacy_requests_table.php` + `2026_06_26_000109_add_dialysis_session_id_to_requests_tables.php` + `2026_06_26_000111_add_or_schedule_id_to_requests_tables.php` + `2026_06_26_114600_add_ob_consultation_id_to_requests_tables.php` + `2026_06_26_115002_add_dr_schedule_id_to_requests_tables.php`
**Model**: `App\Models\Pharmacy\PharmacyRequest`

---

### 70. `pharmacy_request_items` ✅ (Tenant-scoped via parent)
Individual line items within a pharmacy request — each item represents a single medication with dosage, frequency, route, duration, and dispensing tracking. Now linked to Drug formulary via `drug_id` and batch-tracked via `drug_batch_id`.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `pharmacy_request_id` | bigint unsigned | NOT NULL, FK → pharmacy_requests.id ON DELETE CASCADE | Parent pharmacy request |
| `item_id` | bigint unsigned | nullable, FK → items.id ON DELETE SET NULL | Reference to medication item master (legacy) |
| `drug_id` | bigint unsigned | nullable, FK → drugs.id | Reference to drug formulary (primary) |
| `drug_batch_id` | bigint unsigned | nullable, FK → drug_batches.id | Specific batch dispensed |
| `item_name` | varchar(200) | NOT NULL | Denormalized medication name |
| `generic_name` | varchar(200) | nullable | CF4: Denormalized generic name |
| `brand_name` | varchar(200) | nullable | CF4: Denormalized brand name |
| `item_code` | varchar(30) | nullable | Denormalized item code |
| `philhealth_item_code` | varchar(50) | nullable | CF4/eSOA: PhilHealth Medicine Library code |
| `supply_unit_name` | varchar(100) | nullable | Denormalized unit name |
| `dosage` | varchar(100) | nullable | e.g. 500mg, 1g |
| `dosage_form` | varchar(50) | nullable | CF4: Tablet, Capsule, Syrup, Injectable |
| `frequency` | varchar(100) | nullable | e.g. BID, TID, QID, QHS, PRN |
| `route` | varchar(100) | nullable | e.g. PO, IV, IM, SC, SL, TOP |
| `duration` | varchar(100) | nullable | e.g. 7 days, 2 weeks |
| `quantity` | decimal(12,2) | NOT NULL, default 1 | Requested quantity |
| `given_quantity` | decimal(12,2) | NOT NULL, default 0 | Cumulative quantity dispensed |
| `unit_cost` | decimal(12,2) | NOT NULL, default 0 | Cost per unit at time of request |
| `total_cost` | decimal(12,2) | NOT NULL, default 0 | given_quantity × unit_cost |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Approved, Partial, Complete, Cancelled |
| `dispensed_date` | timestamp | nullable | CF4/eSOA: Actual dispense time (service_date) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `pharmacy_request_id` → `pharmacy_requests(id)` ON DELETE CASCADE, `item_id` → `items(id)` ON DELETE SET NULL, `drug_id` → `drugs(id)`, `drug_batch_id` → `drug_batches(id)`
**Indexes**: `pharmacy_request_id`, `status`
**Migrations**: `2026_06_22_000068_create_pharmacy_request_items_table.php`, `2026_06_22_000074_add_drug_id_to_pharmacy_request_items_table.php`, `2026_06_25_000092_add_eclaims_fields_to_pharmacy_tables.php`
**Model**: `App\Models\Pharmacy\PharmacyRequestItem`

---

### 71. `drugs` ✅ (Tenant-scoped)
Drug formulary / drug master catalog — separate from CSR `items`. Core pharmacy drug database with therapeutic classification, safety info, and reorder parameters.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `drug_code` | varchar(30) | NOT NULL | Unique drug code (e.g. AMLO-5) |
| `philhealth_item_code` | varchar(50) | nullable | CF4/eSOA: PhilHealth Medicine Library code |
| `generic_name` | varchar(200) | NOT NULL | Generic/INN name (e.g. Amlodipine) |
| `brand_name` | varchar(200) | nullable | Brand/trade name (e.g. Norvasc) |
| `drug_strength` | varchar(50) | nullable | Strength/concentration (e.g. 5mg, 500mg/5mL) |
| `dosage_form` | varchar(50) | nullable | Tablet, Capsule, Injection, Syrup, Cream, Inhaler |
| `therapeutic_class` | varchar(100) | nullable | e.g. Antihypertensive, Antibiotic, NSAID |
| `drug_schedule` | varchar(30) | nullable | Rx (prescription), Non-Rx (OTC), Controlled |
| `route` | varchar(30) | nullable | Default administration route (PO, IV, IM, SC, SL, TOP, INH, PR) |
| `active_ingredient` | varchar(200) | nullable | Active pharmaceutical ingredient |
| `manufacturer` | varchar(200) | nullable | Manufacturer/labeler name |
| `side_effects` | text | nullable | Common side effects |
| `contraindications` | text | nullable | Contraindications and precautions |
| `reorder_level` | int | NOT NULL, default 0 | Minimum stock before reorder |
| `reorder_qty` | int | NOT NULL, default 0 | Default reorder quantity |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(drug_code, tenant_id, history)`
**Relations**: HasMany → `drug_batches`
**CRUD**: `App\Livewire\Pharmacy\DrugCrud`
**Migration**: `2026_06_22_000069_create_drugs_table.php`, `2026_06_25_000092_add_eclaims_fields_to_pharmacy_tables.php`
**Model**: `App\Models\Pharmacy\Drug`

---

### 72. `drug_batches` ✅ (Tenant-scoped)
Lot/batch inventory tracking for drugs — enables FEFO (First Expiry, First Out) dispensing.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `drug_id` | bigint unsigned | NOT NULL, FK → drugs.id | Parent drug |
| `batch_no` | varchar(50) | NOT NULL | Manufacturer batch/lot number |
| `expiry_date` | date | NOT NULL | Expiration date (FEFO key) |
| `quantity_on_hand` | decimal(12,2) | NOT NULL, default 0 | Current available stock |
| `unit_cost` | decimal(12,2) | NOT NULL, default 0 | Cost per unit |
| `selling_price` | decimal(12,2) | NOT NULL, default 0 | Selling/dispensing price |
| `location` | varchar(100) | nullable | Physical storage location (shelf/bin) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(drug_id, batch_no, tenant_id, history)`
**Foreign Key**: `drug_id` → `drugs(id)`
**Scope**: `scopeAvailable()` — `where('quantity_on_hand', '>', 0)->where('expiry_date', '>=', now())`
**CRUD**: `App\Livewire\Pharmacy\DrugBatchCrud`
**Migration**: `2026_06_22_000070_create_drug_batches_table.php`
**Model**: `App\Models\Pharmacy\DrugBatch`

---

### 73. `purchase_orders` ✅ (Tenant-scoped)
Drug procurement purchase orders with full workflow (Draft → Submitted → Approved → Partial/Received → Cancelled).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `po_code` | varchar(30) | NOT NULL | Purchase order code (PO-00001) |
| `supplier_id` | bigint unsigned | nullable, FK → suppliers.id ON DELETE SET NULL | Supplier/vendor |
| `order_date` | date | NOT NULL | Date order was placed |
| `expected_date` | date | nullable | Expected delivery date |
| `status` | varchar(30) | NOT NULL, default 'Draft' | Draft, Submitted, Approved, Partial, Received, Cancelled |
| `total_amount` | decimal(12,2) | NOT NULL, default 0 | Total order value |
| `notes` | text | nullable | Order notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(po_code, tenant_id, history)`
**Foreign Key**: `supplier_id` → `suppliers(id)` ON DELETE SET NULL
**Relations**: HasMany → `purchase_order_items`, BelongsTo → `supplier`
**CRUD**: `App\Livewire\Pharmacy\PurchaseOrderCrud`
**Migration**: `2026_06_22_000071_create_purchase_orders_table.php`
**Model**: `App\Models\Pharmacy\PurchaseOrder`

---

### 74. `purchase_order_items` ✅ (Child of purchase_orders — no tenant_id)
Line items within a purchase order. No tenant_id (inherited via parent PO).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `purchase_order_id` | bigint unsigned | NOT NULL, FK → purchase_orders.id ON DELETE CASCADE | Parent PO |
| `drug_id` | bigint unsigned | NOT NULL, FK → drugs.id | Drug being ordered |
| `quantity_ordered` | decimal(12,2) | NOT NULL, default 0 | Quantity ordered |
| `quantity_received` | decimal(12,2) | NOT NULL, default 0 | Quantity received so far |
| `unit_cost` | decimal(12,2) | NOT NULL, default 0 | Cost per unit |
| `total_cost` | decimal(12,2) | NOT NULL, default 0 | Total line cost (qty_ordered × unit_cost) |
| `batch_no` | varchar(50) | nullable | Manufacturer batch/lot (filled on receipt) |
| `expiry_date` | date | nullable | Expiration date (filled on receipt / GRN) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `purchase_order_id` → `purchase_orders(id)` ON DELETE CASCADE, `drug_id` → `drugs(id)`
**Indexes**: `purchase_order_id`
**Relations**: BelongsTo → `purchase_order`, BelongsTo → `drug`
**Migration**: `2026_06_22_000072_create_purchase_order_items_table.php`
**Model**: `App\Models\Pharmacy\PurchaseOrderItem`

---

### 75. `stock_adjustments` ✅ (Tenant-scoped)
Audit trail for all inventory changes — write-offs, damages, theft, returns, transfers, corrections, dispensing. Records +/– quantity changes per batch.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `adjustment_code` | varchar(30) | NOT NULL | Auto-generated code (ADJ-00001) |
| `drug_id` | bigint unsigned | nullable, FK → drugs.id | Drug being adjusted |
| `drug_batch_id` | bigint unsigned | nullable, FK → drug_batches.id | Specific batch |
| `adjustment_type` | varchar(30) | NOT NULL | Damage, Expired, Theft, Return, Transfer, Correction, Dispensed, Write-off |
| `quantity` | decimal(12,2) | NOT NULL | Positive = stock in, Negative = stock out |
| `unit_cost` | decimal(12,2) | NOT NULL, default 0 | Cost per unit at time of adjustment |
| `reason` | text | nullable | Reason/explanation for adjustment |
| `reference_type` | varchar(50) | nullable | Source system (Manual, Dispensing, GRN) |
| `reference_id` | bigint unsigned | nullable | FK to source record (purchase_order_id, pharmacy_request_id) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(adjustment_code, tenant_id, history)`
**Foreign Keys**: `drug_id` → `drugs(id)`, `drug_batch_id` → `drug_batches(id)`
**Relations**: BelongsTo → `drug`, BelongsTo → `drug_batch`
**CRUD**: `App\Livewire\Pharmacy\StockAdjustmentCrud`
**Migration**: `2026_06_22_000073_create_stock_adjustments_table.php`
**Model**: `App\Models\Pharmacy\StockAdjustment`

### 76. `procedure_requests` ✅ (Tenant-scoped)
Procedure request/dispensing from any department. Links to ER admissions for treatment orders. Uses `PRCD-xxxxx` request code format.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `request_code` | varchar(30) | NOT NULL | Auto-generated code (PRCD-00001) |
| `er_admission_id` | bigint unsigned | nullable, FK → er_admissions(id) on delete SET NULL | Originating ER encounter |
| `opd_consultation_id` | bigint unsigned | nullable, FK → opd_consultations.id ON DELETE SET NULL, INDEX | OPD Consultation that ordered this |
| `admission_id`    | bigint unsigned | nullable, FK → admissions.id ON DELETE SET NULL, INDEX    | Ward Admission that ordered this     |
| `dialysis_session_id` | bigint unsigned | nullable, FK → dialysis_sessions.id ON DELETE NO ACTION, INDEX | Dialysis Session that ordered this |
| `ob_consultation_id` | bigint unsigned | nullable, FK → ob_consultations.id ON DELETE SET NULL, INDEX | OB Consultation that ordered this |
| `or_schedule_id` | bigint unsigned | nullable, FK → or_schedules.id ON DELETE NO ACTION, INDEX | OR Schedule that ordered this |
| `dr_schedule_id` | bigint unsigned | nullable, FK → dr_schedules.id ON DELETE SET NULL, INDEX | DR Schedule that ordered this |
| `requesting_department_id` | bigint unsigned | nullable, FK → departments(id) on delete NO ACTION | Department making the request |
| `requesting_doctor_id` | bigint unsigned | nullable, FK → health_care_professionals(id) on delete NO ACTION | Doctor making the request |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Approved, Partial, Complete, Cancelled |
| `billing_status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Billed, Exempt, Credited |
| `billing_id` | bigint unsigned | nullable, INDEX | FK to future billing_invoices table |
| `total_amount` | decimal(12,2) | NOT NULL, default 0 | Sum of all line item total_charge values |
| `notes` | text | nullable | Clinical notes or special instructions |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(request_code, tenant_id, history)`
**Foreign Keys**: `er_admission_id` → `er_admissions(id)` on delete SET NULL, `opd_consultation_id` → `opd_consultations(id)` ON DELETE SET NULL, `admission_id` → `admissions(id)` ON DELETE SET NULL, `requesting_department_id` → `departments(id)` on delete NO ACTION, `requesting_doctor_id` → `health_care_professionals(id)` on delete NO ACTION, `dialysis_session_id` → `dialysis_sessions(id)` ON DELETE NO ACTION, `ob_consultation_id` → `ob_consultations(id)` ON DELETE SET NULL, `or_schedule_id` → `or_schedules(id)` ON DELETE NO ACTION, `dr_schedule_id` → `dr_schedules(id)` ON DELETE SET NULL
**Relations**: HasMany → `items` (ProcedureRequestItem), BelongsTo → `requestingDepartment`, BelongsTo → `requestingDoctor`, BelongsTo → `erAdmission`, BelongsTo → `opdConsultation`, BelongsTo → `admission`, BelongsTo → `dialysisSession`, BelongsTo → `obConsultation`, BelongsTo → `orSchedule`, BelongsTo → `drSchedule`
**CRUD**: `App\Livewire\Procedures\ProcedureDispensingCrud`
**Migrations**: `2026_06_22_1641_create_procedure_requests_table.php` + `2026_06_26_000109_add_dialysis_session_id_to_requests_tables.php` + `2026_06_26_000111_add_or_schedule_id_to_requests_tables.php` + `2026_06_26_114600_add_ob_consultation_id_to_requests_tables.php` + `2026_06_26_115002_add_dr_schedule_id_to_requests_tables.php`
**Model**: `App\Models\Procedures\ProcedureRequest`

### 77. `procedure_request_items` ✅ (Tenant-scoped, inherited via parent)
Line items for procedure requests. Denormalizes procedure_name and category_name for referential integrity.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `procedure_request_id` | bigint unsigned | NOT NULL, FK → procedure_requests(id) on delete CASCADE | Parent request |
| `procedure_id` | bigint unsigned | nullable, FK → procedures(id) on delete SET NULL | Procedure reference |
| `procedure_name` | varchar(200) | NOT NULL | Denormalized procedure name |
| `category_name` | varchar(100) | nullable | Denormalized category name |
| `quantity` | int | NOT NULL, default 1 | Requested quantity |
| `qty_given` | int | NOT NULL, default 0 | Quantity actually performed/dispensed |
| `unit_charge` | decimal(12,2) | NOT NULL, default 0 | Charge per unit |
| `total_charge` | decimal(12,2) | NOT NULL, default 0 | quantity × unit_charge |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Approved, Partial, Complete, Cancelled |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `procedure_request_id` → `procedure_requests(id)` on delete CASCADE, `procedure_id` → `procedures(id)` on delete SET NULL
**Relations**: BelongsTo → `procedureRequest`, BelongsTo → `procedure`
**CRUD**: `App\Livewire\Procedures\ProcedureDispensingCrud`
**Migration**: `2026_06_22_1642_create_procedure_request_items_table.php`
**Model**: `App\Models\Procedures\ProcedureRequestItem`

---

## Billing Module Tables

> **Auto-Invoice Command**: `php artisan billing:auto-invoice` — scheduled daily at `23:59` via `routes/console.php`. Automatically creates invoices for completed billable service requests, OECB charges, and discharged room charges. Groups items by patient. See `app/Console/Commands/BillingAutoInvoice.php`.

> **Medical Records Module**: `GET /medical-records` — read-only patient encounter history viewer at `App\Livewire\MedicalRecordsCrud`. Aggregates all encounter types (OPD, ER Triage, ER Treatment, Admission, OB) into a unified timeline with encounter detail modals showing clinical fields, orders (Lab/Pharmacy/Radiology), and billing invoices. Supports 5 printable certificates (Medical Certificate, Certificate of Admission, Discharge Summary, Certificate of Treatment, Clinical Abstract). Route: `routes/web.php:263`. Sidebar: Main section after Patients (violet theme).

> **Doctor Portal Medical Records Integration**: The Doctor's Patient Chart (`App\Livewire\Doctors\PatientChartCrud`) now includes a "Medical Records" tab replacing the old "Past Records" tab. Doctors can click any past encounter in the timeline to view full clinical details, lab results (with abnormal flags and reference ranges), pharmacy orders, and radiology orders for that specific encounter. 4 encounter detail loaders: `loadPastOpd()`, `loadPastEr()`, `loadPastWard()`, `loadPastOb()`. An "Open Full Records" link opens the standalone Medical Records module in a new tab.

### 81. `billing_invoices` ✅ (Tenant-scoped)
Master invoice record — aggregates service charges from all dispensing modules into a single patient invoice.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `invoice_code` | varchar(30) | NOT NULL | Auto-generated invoice code (INV-xxxxx) |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) ON DELETE NO ACTION | Patient being billed |
| `er_admission_id` | bigint unsigned | nullable, FK → er_admissions(id) ON DELETE SET NULL, INDEX | Originating ER encounter |
| `opd_consultation_id` | bigint unsigned | nullable, FK → opd_consultations(id) ON DELETE SET NULL, INDEX | Originating OPD encounter |
| `admission_id` | bigint unsigned | nullable, FK → admissions(id) ON DELETE SET NULL, INDEX | Originating admission |
| `dialysis_session_id` | bigint unsigned | nullable, FK → dialysis_sessions(id) ON DELETE NO ACTION, INDEX | Originating dialysis session |
| `ob_consultation_id` | bigint unsigned | nullable, FK → ob_consultations(id) ON DELETE SET NULL, INDEX | Originating OB consultation |
| `or_schedule_id` | bigint unsigned | nullable, FK → or_schedules(id) ON DELETE NO ACTION, INDEX | Originating OR schedule |
| `dr_schedule_id` | bigint unsigned | nullable, FK → dr_schedules(id) ON DELETE SET NULL, INDEX | Originating DR schedule |
| `invoice_date` | date | NOT NULL | Date of invoice |
| `total_amount` | decimal(12,2) | NOT NULL, default 0 | Sum of all line item total_price values |
| `discount_type` | varchar(20) | nullable | Discount type: 'percentage' or 'fixed' |
| `discount_value` | decimal(12,2) | NOT NULL, default 0 | Discount value entered (e.g. 10 for 10%, or 500 for ₱500 fixed) |
| `discount_amount` | decimal(12,2) | NOT NULL, default 0 | Computed discount = % of total_amount or fixed value |
| `paid_amount` | decimal(12,2) | NOT NULL, default 0 | Sum of all payments applied |
| `balance` | decimal(12,2) | NOT NULL, default 0 | total_amount - discount_amount - paid_amount |
| `status` | varchar(30) | NOT NULL, default 'Unpaid' | Unpaid, Partial, Paid, Cancelled, Credited |
| `notes` | text | nullable | Additional invoice notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(invoice_code, tenant_id, history)`
**Foreign Keys**: `patient_id` → `patients(id)` ON DELETE NO ACTION, `er_admission_id` → `er_admissions(id)` ON DELETE SET NULL, `opd_consultation_id` → `opd_consultations(id)` ON DELETE SET NULL, `admission_id` → `admissions(id)` ON DELETE SET NULL, `dialysis_session_id` → `dialysis_sessions(id)` ON DELETE NO ACTION, `ob_consultation_id` → `ob_consultations(id)` ON DELETE SET NULL, `or_schedule_id` → `or_schedules(id)` ON DELETE NO ACTION, `dr_schedule_id` → `dr_schedules(id)` ON DELETE SET NULL
**Relations**: BelongsTo → `patient`, `erAdmission`, `opdConsultation`, `admission`, `dialysisSession`, `obConsultation`, `orSchedule`, `drSchedule`; HasMany → `items` (BillingInvoiceItem), `payments` (BillingPayment), `manualCharges` (BillingManualCharge); Accessor: `getNetAmountAttribute()` → returns `total_amount - discount_amount`
**CRUD**: `App\Livewire\Billing\BillingCrud`
**Routes**:
- `GET /billing` → `billing` (main 4-tab SPA)
- `GET /billing/invoice/{id}/print` → `billing.invoice.print` (print-optimized invoice view, new browser tab; eager-loads patient/erAdmission/admission/items/payments/manualCharges)
- `GET /billing/payment/{id}/receipt` → `billing.payment.receipt` (print-optimized receipt view, new browser tab; eager-loads invoice.patient/invoice.items/invoice.manualCharges)
**Migrations**: `2026_06_23_000075_create_billing_invoices_table.php` + `2026_06_25_000105_add_discount_fields_to_billing_invoices.php` + `2026_06_26_000109_add_dialysis_session_id_to_requests_tables.php` + `2026_06_26_000111_add_or_schedule_id_to_requests_tables.php` + `2026_06_26_114600_add_ob_consultation_id_to_requests_tables.php` + `2026_06_26_115002_add_dr_schedule_id_to_requests_tables.php`
**Model**: `App\Models\Billing\BillingInvoice`

---

### 82. `billing_invoice_items` ✅ (Child of billing_invoices — no tenant_id)
Individual line items copied from source service requests at time of billing. Stores denormalized source references for audit trail.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `billing_invoice_id` | bigint unsigned | NOT NULL, FK → billing_invoices(id) ON DELETE CASCADE | Parent invoice |
| `source_type` | varchar(30) | NOT NULL | Lab, Pharmacy, CSR, Radiology, Procedure, Manual |
| `source_id` | bigint unsigned | nullable | FK to the originating request table |
| `source_request_code` | varchar(30) | nullable | Denormalized request code for cross-reference |
| `description` | varchar(255) | NOT NULL | Line item description |
| `quantity` | decimal(12,2) | NOT NULL, default 1 | Quantity |
| `unit_price` | decimal(12,2) | NOT NULL, default 0 | Price per unit |
| `total_price` | decimal(12,2) | NOT NULL, default 0 | quantity × unit_price |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `billing_invoice_id` → `billing_invoices(id)` ON DELETE CASCADE
**Indexes**: `source_type`, `source_id`
**Migration**: `2026_06_23_000076_create_billing_invoice_items_table.php`
**Model**: `App\Models\Billing\BillingInvoiceItem`

---

### 83. `billing_payments` ✅ (Tenant-scoped)
Payment records applied against invoices. Supports multiple payment methods and partial payments.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment (not shown in UI) | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `billing_invoice_id` | bigint unsigned | nullable, FK → billing_invoices(id) ON DELETE CASCADE | Parent invoice — nullable for standalone payments not linked to an invoice |
| `payment_code` | varchar(30) | NOT NULL | Auto-generated payment code (PAY-xxxxx) |
| `payment_date` | date | NOT NULL | Date of payment |
| `amount` | decimal(12,2) | NOT NULL, default 0 | Payment amount |
| `payment_method` | varchar(50) | NOT NULL, default 'Cash' | Cash, Credit Card, Check, Bank Transfer, HMO, PhilHealth, Others |
| `fund_source_id` | bigint unsigned | nullable, FK → fund_sources(id) ON DELETE SET NULL, INDEX | Budget source (funding origin for the payment) |
| `reference_no` | varchar(100) | nullable | Check number, OR number, transaction ID |
| `notes` | text | nullable | Payment notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Post-migration additions** (from `2026_07_15_000002_add_patient_encounter_fields_to_billing_payments_table.php`):

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `patient_id` | bigint unsigned | nullable, INDEX | FK → patients.id — direct patient reference for ledger posting |
| `admission_id` | bigint unsigned | nullable, INDEX | FK → admissions.id — encounter link |
| `er_admission_id` | bigint unsigned | nullable, INDEX | FK → er_admissions.id — ER encounter link |
| `opd_consultation_id` | bigint unsigned | nullable, INDEX | FK → opd_consultations.id — OPD encounter link |

**Composite Unique**: `(payment_code, tenant_id, history)`
**Foreign Keys**: `billing_invoice_id` → `billing_invoices(id)` ON DELETE CASCADE, `fund_source_id` → `fund_sources(id)` ON DELETE SET NULL
**Relations**: BelongsTo → `invoice` (BillingInvoice), `fundSource` (FundSource), `patient` (Patient), `admission` (Admission), `erAdmission` (ErAdmission)
**Migrations**: `2026_06_23_000077_create_billing_payments_table.php` + `2026_06_25_000107_add_fund_source_id_to_billing_payments.php` + `2026_07_15_000002_add_patient_encounter_fields_to_billing_payments_table.php` + `2026_07_15_000003_make_billing_invoice_id_nullable_in_billing_payments_table.php`
**Model**: `App\Models\Billing\BillingPayment`

---

### 105. `fund_sources` ✅ (Tenant-scoped)
Fund source / budget origin catalog for payments. Used to track where payment funds come from (e.g. Private Pay, PhilHealth, HMO, Government Subsidy, Charity). Each fund source has a budget cap and tracks used amount via automatic deduction on payment recording.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `code` | varchar(30) | NOT NULL | Unique code (e.g. PRIVATE-PAY, PHILHEALTH, HMO, GOV-SUBSIDY, CHARITY) |
| `name` | varchar(200) | NOT NULL | Display name |
| `description` | text | nullable | Optional description |
| `budget_amount` | decimal(12,2) | NOT NULL, default 0 | Total budget allocated to this fund source |
| `used_amount` | decimal(12,2) | NOT NULL, default 0 | Total amount consumed from this budget (auto-deducted when payments are recorded against it) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(code, tenant_id, history)` — unique per tenant
**Relations**: HasMany → `billing_payments` (via fund_source_id)
**Methods**: `remainingAmount()` → `max(0, budget_amount - used_amount)`; `deductBudget(amount)` → increments `used_amount`; `restoreBudget(amount)` → decrements `used_amount` (floor 0)
**CRUD**: `FundSourceCrud` Livewire component with datagrid (budget/used/remaining columns), create/edit modal, archive. Sidebar link under Billing.
**Migrations**: `2026_06_25_000106_create_fund_sources_table.php` + `2026_06_25_165330_add_budget_fields_to_fund_sources.php`
**Model**: `App\Models\Billing\FundSource`
**Livewire**: `App\Livewire\Billing\FundSourceCrud`
**Route**: `GET /billing/fund-sources` named `billing.fund-sources`
**Blade**: `resources/views/livewire/billing/fund-source-crud.blade.php`
**Payment Integration**: `BillingCrud::addPayment()` calls `FundSource::deductBudget()` when a fund source is selected. `BillingCrud::confirmDeleteInvoice()` calls `FundSource::restoreBudget()` on all linked payments.

---

### 84. `nursing_vitals` ✅ (Tenant-scoped)

Vital signs monitoring for admitted (in-patient) patients. Mirrors the vital sign fields from ER triage but is linked to the admissions table instead of er_admissions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `admission_id` | bigint unsigned | NOT NULL, FK → admissions(id) ON DELETE CASCADE | Parent admission record |
| `recorded_by` | bigint unsigned | nullable | FK → users.id (nurse who recorded) |
| `recorded_at` | datetime | NOT NULL | Timestamp of vital sign measurement |
| `vital_temperature` | decimal(4,1) | nullable | Body temperature in Celsius |
| `vital_heart_rate` | int | nullable | Heart rate in beats per minute |
| `vital_respiratory_rate` | int | nullable | Respiratory rate in breaths per minute |
| `vital_bp_systolic` | int | nullable | Systolic blood pressure in mmHg |
| `vital_bp_diastolic` | int | nullable | Diastolic blood pressure in mmHg |
| `vital_o2_saturation` | decimal(5,2) | nullable | Oxygen saturation in % |
| `vital_pain_score` | int | nullable | Pain scale 0-10 |
| `level_of_consciousness` | varchar(50) | nullable | Alert, Verbal, Pain, Unresponsive |
| `blood_sugar` | decimal(5,2) | nullable | Capillary blood glucose in mg/dL |
| `notes` | text | nullable | Free-text notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |

**Foreign Keys**: `admission_id` → `admissions(id)` ON DELETE CASCADE
**Migration**: `2026_06_23_000078_create_nursing_vitals_table.php`
**Model**: `App\Models\Nursing\NursingVital`

---

### 85. `nursing_care_notes` ✅ (Tenant-scoped)

SOAP-format nursing care notes for admitted patients. Supports 3 shifts (AM, PM, Night) per care date.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `admission_id` | bigint unsigned | NOT NULL, FK → admissions(id) ON DELETE CASCADE | Parent admission record |
| `nurse_id` | bigint unsigned | nullable | FK → users.id (nurse author) |
| `care_date` | date | NOT NULL | Date of care |
| `shift` | varchar(20) | NOT NULL, default 'AM' | AM, PM, Night |
| `subjective` | text | nullable | Patient-reported symptoms (S) |
| `objective` | text | nullable | Observed signs and measurements (O) |
| `assessment` | text | nullable | Nursing assessment/interpretation (A) |
| `plan` | text | nullable | Nursing plan/interventions (P) |
| `notes` | text | nullable | Additional free-text notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Index**: `(admission_id, care_date, shift)` — composite index for fast lookup by date/shift per admission
**Foreign Keys**: `admission_id` → `admissions(id)` ON DELETE CASCADE
**Migration**: `2026_06_23_000079_create_nursing_care_notes_table.php`
**Model**: `App\Models\Nursing\NursingCareNote`

---

### 86. `nursing_medication_administrations` ✅ (Tenant-scoped)

Medication Administration Record (MAR) for in-patients. Tracks every dose administered — drug name, dosage, route, and who administered it. Optionally links back to the originating pharmacy request item.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `admission_id` | bigint unsigned | NOT NULL, FK → admissions(id) ON DELETE CASCADE | Parent admission |
| `pharmacy_request_item_id` | bigint unsigned | nullable, FK → pharmacy_request_items(id) ON DELETE SET NULL | Optional link to pharmacy order item |
| `drug_name` | varchar(200) | NOT NULL | Name of administered drug |
| `dosage` | varchar(100) | nullable | Prescribed dosage (e.g. 500mg) |
| `dose_given` | varchar(100) | nullable | Actual dose administered |
| `route` | varchar(50) | nullable | PO, IV, IM, SC, etc. |
| `administered_at` | datetime | NOT NULL | When the dose was given |
| `administered_by` | bigint unsigned | nullable, FK → users(id) ON DELETE SET NULL | Nurse who administered |
| `notes` | text | nullable | Administration notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |

**Index**: `(admission_id, administered_at)` — composite index for fast MAR lookup
**Foreign Keys**: `admission_id` → `admissions(id)` ON DELETE CASCADE, `pharmacy_request_item_id` → `pharmacy_request_items(id)` ON DELETE SET NULL, `administered_by` → `users(id)` ON DELETE SET NULL
**Migration**: `2026_06_23_000081_create_nursing_medication_administrations_table.php`
**Model**: `App\Models\Nursing\NursingMedicationAdministration`

---

## Billing Manual Charges Table

### 94. `billing_manual_charges` 🆕 (Tenant-scoped)

Ad-hoc charges manually added to a patient's bill — covers miscellaneous fees, administrative charges, supplies not in inventory, transport/escort fees, equipment rentals, room differentials, and other non-system-generated billable items. Supports pre-invoice staging: charges can be added with `billing_invoice_id = NULL` before an invoice is created, then linked on invoice creation.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `billing_invoice_id` | bigint unsigned | **nullable**, FK → billing_invoices(id) ON DELETE CASCADE | Parent invoice. NULL = pre-invoice staging charge |
| `patient_id` | bigint unsigned | nullable, INDEX | FK → patients.id. Set on staging charges for patient grouping |
| `charge_type` | varchar(50) | NOT NULL, default 'Miscellaneous' | Miscellaneous, Administrative, Supply, Professional Fee, Transport, Equipment, Room Differential, Others |
| `description` | varchar(255) | NOT NULL | Description of the charge |
| `quantity` | decimal(12,2) | NOT NULL, default 1 | Quantity |
| `unit_price` | decimal(12,2) | NOT NULL, default 0 | Price per unit |
| `total_price` | decimal(12,2) | NOT NULL, default 0 | quantity × unit_price |
| `notes` | text | nullable | Internal notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Indexes**: `idx_billing_manual_charges_patient_id` (patient_id), `idx_billing_manual_charges_invoice_null` (filtered: billing_invoice_id WHERE IS NULL)
**Foreign Key**: `billing_invoice_id` → `billing_invoices(id)` ON DELETE CASCADE
**Relations**: BelongsTo → `billingInvoice`, BelongsTo → `patient`
**CRUD**: Managed inline from `App\Livewire\Billing\BillingCrud` — pre-invoice staging via `addManualChargeForPatient()`, `removeManualChargeForPatient()`, `stagingManualCharges()`. Linked to invoice on `createInvoice()` via `billing_invoice_id` update. Post-invoice via `addManualCharge()`, `removeManualCharge()`, `recalculateInvoiceTotals()`.
**Print**: Manual charges appear on the printed invoice (`billing.invoice.print`) in a sky-themed "Manual Charges" sub-section between service items and totals.
**Migrations**: `2026_06_24_000089_create_billing_manual_charges_table.php`, `2026_07_11_152549_make_billing_invoice_id_nullable_on_billing_manual_charges_table.php`, `2026_07_11_152803_add_patient_id_to_billing_manual_charges_table.php`
**Model**: `App\Models\Billing\BillingManualCharge`

---

### 106. `patient_account_entries` 🆕 (Tenant-scoped — Patient Account Ledger)

Patient account ledger — the single source of truth for all financial activity per patient. Every charge, payment, adjustment, and PhilHealth credit posts here as a double-entry bookkeeping line. Replaces the old workflow where financial status was only visible through unbilled virtual computations. The `balance` column stores a running balance after each entry (recalculated on insert). Used by `BillingCrud` Patient Accounts tab and Patient Ledger modal.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `patient_id` | bigint unsigned | NOT NULL, INDEX | FK → patients.id — the patient this entry belongs to |
| `admission_id` | bigint unsigned | nullable, INDEX | FK → admissions.id — encounter scope (in-patient) |
| `er_admission_id` | bigint unsigned | nullable, INDEX | FK → er_admissions.id — encounter scope (ER) |
| `opd_consultation_id` | bigint unsigned | nullable, INDEX | FK → opd_consultations.id — encounter scope (OPD) |
| `or_schedule_id` | bigint unsigned | nullable, INDEX | FK → or_schedules.id — encounter scope (OR) |
| `dr_schedule_id` | bigint unsigned | nullable, INDEX | FK → dr_schedules.id — encounter scope (Delivery Room) |
| `ob_consultation_id` | bigint unsigned | nullable, INDEX | FK → ob_consultations.id — encounter scope (OB) |
| `dialysis_session_id` | bigint unsigned | nullable, INDEX | FK → dialysis_sessions.id — encounter scope (Dialysis) |
| `entry_type` | varchar(20) | NOT NULL | `charge`, `payment`, `adjustment`, `discount`, `philhealth_credit` |
| `source_type` | varchar(30) | NOT NULL | `Lab`, `Pharmacy`, `CSR`, `Radiology`, `Procedure`, `OECB`, `Admission`, `Manual`, `RoomBoard` |
| `source_id` | bigint unsigned | nullable | FK to originating request (polymorphic) |
| `source_code` | varchar(30) | nullable | Denormalized request code for quick display |
| `debit` | decimal(12,2) | NOT NULL, default 0 | Charge amount — patient owes |
| `credit` | decimal(12,2) | NOT NULL, default 0 | Payment/discount — reduces balance |
| `balance` | decimal(12,2) | NOT NULL, default 0 | Running balance after this entry |
| `description` | text | nullable | Line item description |
| `notes` | text | nullable | Internal notes |
| `billing_invoice_id` | bigint unsigned | nullable, INDEX | FK → billing_invoices.id — linked invoice when generated |
| `fund_source_id` | bigint unsigned | nullable, INDEX | FK → fund_sources.id — fund source for payments |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(tenant_id, patient_id, id)` — enforces tenant-patient isolation
**Foreign Keys**: `patient_id` → `patients(id)` NO ACTION, `billing_invoice_id` → `billing_invoices(id)` NO ACTION, `fund_source_id` → `fund_sources(id)` NO ACTION
**Entry Types**:
| Type | When Posted | debit | credit |
|------|-------------|-------|--------|
| `charge` | Service completed (Lab, Pharmacy, etc.) | amount | 0 |
| `payment` | Payment recorded by cashier | 0 | amount |
| `adjustment` | Manual balance correction | + or − | + or − |
| `discount` | Discount applied | 0 | amount |
| `philhealth_credit` | PhilHealth claim approved | 0 | amount |

**CRUD**: Accessed via `App\Livewire\Billing\BillingCrud` — Tab 1 "Patient Accounts" shows all patients with account activity. `viewPatientLedger()` opens the ledger modal with patient demographics header, checkbox selection, and Cancel Charges / Revoke Payments actions. `postChargesToLedger()` batch-posts pending charges from clinical tables.
**Balance Rules**: All display/balance queries (`getLedger()`, `getPatientBalance()`, `recalculateBalance()`, `getPatientAccountsProperty()`) filter `history = 1`. When an entry is revoked (history=0), `recalculatePatientBalance()` recalculates all active entries from scratch in order. Internal operations (charge posting, invoice cancellation) use `withoutGlobalScopes()` to access all rows.
**Static Helpers**: `PatientAccountEntry::postCharge()`, `postPayment()`, `postAdjustment()`, `recalculateBalance()`, `getPatientBalance()`, `getLedger()`
**Migrations**: `2026_07_15_000001_create_patient_account_entries_table.php`
**Model**: `App\Models\Billing\PatientAccountEntry`

---

**charge_type categories** (based on common hospital billing practices):
| Type | Common Examples |
|------|----------------|
| Miscellaneous | Items without a specific category, catch-all |
| Administrative | Medical certificate, chart copy fee, documentation fee |
| Supply | Wound care supplies, non-stock materials, specialized dressings |
| Professional Fee | Specialist consultation add-on, second opinion fee |
| Transport | Medical escort, ambulance service, patient transfer |
| Equipment | Wheelchair rental, oxygen tank, nebulizer rental |
| Room Differential | Room upgrade surcharge, accommodation adjustment |
| Others | Late charges, missed charges, one-off items |

---

## PhilHealth E-Claims Module Tables (Phase 4)

### 91. `claim_rejections` ✅ (Tenant-scoped)

Tracks claim rejection details from PhilHealth, enabling resubmission workflow.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `philhealth_claim_id` | bigint unsigned | NOT NULL, FK → philhealth_claims(id) ON DELETE NO ACTION | Parent claim |
| `rejection_code` | varchar(50) | nullable | PhilHealth rejection code |
| `rejection_reason` | text | nullable | Detailed reason for rejection |
| `rejection_date` | date | nullable | Date of rejection |
| `resubmission_status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Resubmitted, Resolved |
| `resubmitted_at` | datetime | nullable | When the claim was resubmitted |
| `resolution_notes` | text | nullable | Notes on how the rejection was resolved |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `philhealth_claim_id` → `philhealth_claims(id)` ON DELETE NO ACTION
**Migration**: `2026_06_23_000084_create_claim_rejections_table.php`
**Model**: `App\Models\ClaimRejection`

---

### 92. `claim_status_polls` ✅ (Tenant-scoped)

Audit log of every status poll made to the PhilHealth web service for a claim.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `philhealth_claim_id` | bigint unsigned | nullable, FK → philhealth_claims(id) ON DELETE NO ACTION | Polled claim |
| `claim_batch_id` | bigint unsigned | nullable, FK → claim_batches(id) ON DELETE NO ACTION | Parent batch |
| `polled_at` | datetime | NOT NULL | When the poll was made |
| `status_before` | varchar(30) | nullable | Claim status before the poll |
| `status_after` | varchar(30) | nullable | Claim status after the poll |
| `response_data` | text | nullable | JSON-encoded SOAP response |
| `poll_source` | varchar(20) | NOT NULL, default 'manual' | manual, auto |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Keys**: `philhealth_claim_id` → `philhealth_claims(id)` ON DELETE NO ACTION, `claim_batch_id` → `claim_batches(id)` ON DELETE NO ACTION
**Migration**: `2026_06_23_000085_create_claim_status_polls_table.php`
**Model**: `App\Models\ClaimStatusPoll`

---

### 93. `cf5_shadow_billings` ✅ (Tenant-scoped)

DRG (Diagnosis-Related Group) shadow billing records for CF5 comparative analysis.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `philhealth_claim_id` | bigint unsigned | NOT NULL, FK → philhealth_claims(id) ON DELETE NO ACTION | Parent claim |
| `drg_code` | varchar(20) | NOT NULL | DRG classification code |
| `drg_description` | varchar(255) | nullable | DRG description |
| `drg_weight` | decimal(10,4) | NOT NULL, default 0 | DRG weight factor |
| `base_rate` | decimal(12,2) | NOT NULL, default 0 | Base rate amount (₱35,000 default) |
| `hf_amount` | decimal(12,2) | NOT NULL, default 0 | Hospital Fee (60% of total) |
| `pf_amount` | decimal(12,2) | NOT NULL, default 0 | Professional Fee (40% of total) |
| `total_drg_amount` | decimal(12,2) | NOT NULL, default 0 | Total DRG = base_rate × drg_weight |
| `actual_case_rate_amount` | decimal(12,2) | NOT NULL, default 0 | Actual PhilHealth case rate |
| `difference_amount` | decimal(12,2) | NOT NULL, default 0 | DRG − Case Rate (gain/loss) |
| `status` | varchar(30) | NOT NULL, default 'Calculated' | Calculated, Reviewed, Submitted |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | NOT NULL | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | NOT NULL | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(philhealth_claim_id, history)` — one active DRG record per claim
**Foreign Keys**: `philhealth_claim_id` → `philhealth_claims(id)` ON DELETE NO ACTION
**Migration**: `2026_06_23_000086_create_cf5_shadow_billings_table.php`
**Model**: `App\Models\Cf5ShadowBilling`

---

## OECB Module Tables (Outpatient Emergency Care Benefit)

### 101. `er_admissions` — OECB Flag (Remaining in Table 62)
> **Note**: `core_presentation`, `oecb_package_code`, and `oecb_fixed_fee` were removed from this table in `2026_07_06_021512_create_oecb_entries_table.php` and migrated to the `oecb_entries` table (Table 105) for multi-entry support.

Only the `is_oecb` flag remains on `er_admissions` (see Table 62).

**Migration**: `2026_06_25_000101_add_oecb_fields_to_er_tables.php`

---

### 102. `philhealth_claims` — `er_admission_id` FK (Added to Table 87)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `er_admission_id` | bigint unsigned | nullable, FK → er_admissions(id) ON DELETE NO ACTION, INDEX | Links OECB claim to originating ER admission |

**Migration**: `2026_06_25_000102_add_er_admission_id_to_philhealth_claims.php`
**Note**: The column pre-existed in the table; migration was made idempotent. See Table 87 for full `philhealth_claims` schema.

---

### 103. `oecb_core_presentations` 🆕 (Global reference)

PhilHealth OECB Annex A: 14 core presentations defining emergency conditions eligible for the OECB fixed-fee package.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `code` | varchar(20) | NOT NULL, UNIQUE | Core presentation code (DOB, CHP, AMS, SAP, SHD, FVR, TRA, PSN, DEH, SLZ, ALR, OBS, NEO, OTH) |
| `name` | varchar(200) | NOT NULL | Full name (e.g. Difficulty of Breathing) |
| `description` | text | nullable | Clinical description of the presentation |
| `sort_order` | int | NOT NULL, default 0 | Display/priority order |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `code`
**Relations**: HasMany → `feeSchedules` (OecbFeeSchedule)
**CRUD**: Seeded via `OecbSeeder`, managed via `OecbCorePresentation` model
**Migration**: `2026_06_25_000103_create_oecb_core_presentations_table.php`
**Model**: `App\Models\Oecb\OecbCorePresentation`

---

### 104. `oecb_fee_schedules` 🆕 (Global reference)

PhilHealth OECB Annex B: Fixed fee schedule mapping each core presentation to a package code and fixed reimbursement amount.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `package_code` | varchar(30) | NOT NULL | Package code (e.g. OECB-001) |
| `core_presentation_id` | bigint unsigned | NOT NULL, FK → oecb_core_presentations(id) ON DELETE NO ACTION | Linked core presentation |
| `description` | text | nullable | Package description |
| `fixed_fee` | decimal(12,2) | NOT NULL, default 0 | Fixed fee amount (₱5,500–₱13,000) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(package_code, core_presentation_id, history)` — one fee per presentation per package
**Foreign Key**: `core_presentation_id` → `oecb_core_presentations(id)` ON DELETE NO ACTION
**Relations**: BelongsTo → `corePresentation` (OecbCorePresentation)
**Seeded Data** (14 records):

| Package | Code | Core Presentation | Fixed Fee |
|---------|------|-------------------|-----------|
| OECB-001 | DOB | Difficulty of Breathing | ₱9,500 |
| OECB-002 | CHP | Chest Pain | ₱12,000 |
| OECB-003 | AMS | Altered Mental Status | ₱10,000 |
| OECB-004 | SAP | Severe Abdominal Pain | ₱8,500 |
| OECB-005 | SHD | Severe Headache | ₱7,500 |
| OECB-006 | FVR | Fever | ₱5,500 |
| OECB-007 | TRA | Trauma/Injury | ₱11,000 |
| OECB-008 | PSN | Poisoning/Overdose | ₱13,000 |
| OECB-009 | DEH | Severe Dehydration | ₱6,500 |
| OECB-010 | SLZ | Active Seizure | ₱9,500 |
| OECB-011 | ALR | Severe Allergic Reaction | ₱8,500 |
| OECB-012 | OBS | Obstetric Emergency | ₱12,000 |
| OECB-013 | NEO | Neonatal Emergency | ₱10,000 |
| OECB-014 | OTH | Other Emergency | ₱7,000 |

**Migration**: `2026_06_25_000104_create_oecb_fee_schedules_table.php`
**Model**: `App\Models\Oecb\OecbFeeSchedule`

---

### 105. `oecb_entries` 🆕 (Tenant-scoped)

Stores individual OECB package entries per ER admission, replacing the old scalar `core_presentation`, `oecb_package_code`, `oecb_fixed_fee` columns on `er_admissions`. Supports multiple OECB entries per admission.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation |
| `er_admission_id` | bigint unsigned | NOT NULL, FK → er_admissions(id) ON DELETE CASCADE, INDEX | Parent ER admission |
| `core_presentation` | varchar(200) | nullable | Core presentation name (e.g. Difficulty of Breathing) |
| `oecb_package_code` | varchar(30) | nullable | OECB package code (e.g. OECB-001) |
| `oecb_fixed_fee` | decimal(12,2) | nullable | OECB Annex A fixed fee amount |
| `billing_id` | bigint unsigned | nullable, INDEX | FK → billing_invoices(id); set when billed |
| `billing_status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Billed (track billing state) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Migrations**: `2026_07_06_021512_create_oecb_entries_table.php` (seed migration `021515` deleted — no legacy data to migrate)
**Model**: `App\Models\Oecb\OecbEntry`
**Relations**: BelongsTo → `erAdmission`

---

### 106. `icd_10_codes` 🆕 (Global reference)

Standardized ICD-10 Diagnosis Dictionary used for PhilHealth eClaims auto-coding and EMR assessment standardization.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `code` | varchar(20) | NOT NULL, UNIQUE | ICD-10 Code (e.g. J45.909) |
| `description` | varchar(500) | NOT NULL | Full disease name/description |
| `category` | varchar(100) | nullable | Category grouping (e.g. Respiratory, Circulatory) — added by merge migration |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `code`
**CRUD**: Seeded via `Icd10Seeder`, managed via `Icd10Code` model. Used in OPD, ER, Ward, and OB encounters via `icd_10_code` field.
**Migrations**: `2026_06_26_172332_create_icd_10_codes_table.php`, `2026_07_12_000001_merge_icd10_tables.php` (added `category`, merged old `icd10_codes` table)
**Model**: `App\Models\Icd10Code`

---

### 107. `hospitals` 🆕 (Tenant-scoped)
Reference table of hospitals/facilities for inter-facility transfers.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `code` | varchar(30) | NOT NULL | Hospital code (unique per tenant) |
| `name` | varchar(200) | NOT NULL | Hospital/facility name |
| `address` | varchar(255) | nullable | Street address |
| `contact_number` | varchar(30) | nullable | Contact phone number |
| `hospital_type` | varchar(30) | NOT NULL, default 'Primary' | Primary, Secondary, Tertiary, Specialty |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(code, tenant_id, history)`
**Migration**: `2026_06_27_000001_create_hospitals_table.php`
**Model**: `App\Models\Hospital`

---

### 108. `hospital_transfers` 🆕 (Tenant-scoped)
Inter-facility patient transfer records linking source encounters to destination hospitals.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `transfer_code` | varchar(20) | NOT NULL | Auto-generated code (HT-xxxxx) |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) | Patient being transferred |
| `destination_hospital_id` | bigint unsigned | NOT NULL, FK → hospitals(id) | Receiving hospital/facility |
| `source_type` | varchar(50) | NOT NULL | Polymorphic: er_admission, admission, opd_consultation, or_schedule, ob_consultation, dialysis_session, dr_schedule |
| `source_id` | bigint unsigned | NOT NULL | Polymorphic FK to source encounter |
| `transfer_reason` | text | nullable | Reason for transfer |
| `transfer_diagnosis` | text | nullable | Diagnosis at time of transfer |
| `transferred_by` | bigint unsigned | nullable, FK → health_care_professionals(id) | HCP who ordered/authorized the transfer |
| `accompanying_personnel` | varchar(255) | nullable | Names/roles of accompanying staff |
| `transport_mode` | varchar(30) | NOT NULL, default 'Ambulance' | Ambulance, Private, Walk-in, Air, Other |
| `condition_on_transfer` | text | nullable | Patient condition summary at transfer |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, In Transit, Completed, Cancelled |
| `transfer_datetime` | timestamp | nullable | When the transfer occurred/will occur |
| `completed_datetime` | timestamp | nullable | When the transfer was completed |
| `notes` | text | nullable | Additional notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(transfer_code, tenant_id, history)`
**Foreign Keys**: `patient_id` → `patients(id)`, `destination_hospital_id` → `hospitals(id)`, `transferred_by` → `health_care_professionals(id)`
**Relations**: BelongsTo → `patient` (Patient), `destinationHospital` (Hospital), `transferredBy` (HealthCareProfessional); MorphTo → `source` (polymorphic to ErAdmission/Admission/OpdConsultation/OrSchedule/ObConsultation/DialysisSession/DrSchedule)
**Migration**: `2026_06_27_000002_create_hospital_transfers_table.php`
**Model**: `App\Models\HospitalTransfer`

---

## RBAC Tables (Phase 1)

### 99. `permissions` 🆕 (Global — no tenant_id)
Stores granular permission definitions organized by module.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `name` | varchar(100) | NOT NULL, UNIQUE | Permission key (e.g. `billing.view`) |
| `display_name` | varchar(200) | NOT NULL | Human-readable name (e.g. "View Billing Records") |
| `module` | varchar(100) | nullable | Module grouping (e.g., Billing, Laboratory, Patient) |
| `guard_name` | varchar(40) | NOT NULL, default 'web' | Auth guard |
| `description` | text | nullable | Optional description |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Unique**: `name`
**Migration**: `2026_06_30_000001_create_permissions_tables.php`
**Model**: `App\Models\Permission`

---

### 100. `role_permissions` 🆕 (Global — no tenant_id by default)
Pivot table mapping roles (string values from `users.role`) to permissions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `role` | varchar(40) | NOT NULL | Role name (superadmin, tenant_admin, staff, doctor) |
| `permission_id` | bigint unsigned | NOT NULL, FK → permissions(id) ON DELETE CASCADE | Associated permission |
| `tenant_id` | bigint unsigned | nullable | Optional per-tenant override |
| `created_date` | timestamp | nullable | Creation timestamp |

**Composite Unique**: `(role, permission_id, tenant_id)`
**Foreign Key**: `permission_id` → `permissions(id)` ON DELETE CASCADE
**Migration**: `2026_06_30_000001_create_permissions_tables.php`

---

### 101. `user_permissions` 🆕 (Tenant-scoped)
Stores user-level permission allow/deny overrides on top of role-based permissions.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `user_id` | bigint unsigned | NOT NULL, FK → users(id) ON DELETE CASCADE | Target user |
| `permission_id` | bigint unsigned | NOT NULL, FK → permissions(id) ON DELETE CASCADE | The permission being overridden |
| `type` | varchar(10) | NOT NULL | `allow` (grant extra) or `deny` (revoke from role) |
| `tenant_id` | bigint unsigned | nullable | Optional per-tenant scope |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |

**Composite Unique**: `(user_id, permission_id, tenant_id)`
**Foreign Keys**: `user_id` → `users(id)` ON DELETE CASCADE, `permission_id` → `permissions(id)` ON DELETE CASCADE
**Migration**: `2026_06_30_000002_create_user_permissions_table.php`
**Model**: `App\Models\UserPermission`
**Relations**: BelongsTo → `user` (User), `permission` (Permission)

---

### 102. `roles` 🆕 (Global — tenant_id not applicable, system-level)
Stores system role definitions. 4 system roles seeded by migration: superadmin, tenant_admin, staff, doctor.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `name` | varchar(40) | NOT NULL, UNIQUE | Role key (superadmin, tenant_admin, staff, doctor) |
| `display_name` | varchar(100) | NOT NULL | Human-readable name |
| `description` | text | nullable | Role description |
| `is_system` | tinyint(1) | NOT NULL, default 0 | System-protected role (name/delete locked) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | datetime | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | datetime | nullable | Last modification timestamp |

**Unique**: `name`
**Seeded Data**: superadmin (Unrestricted), tenant_admin (Full tenant access), staff (Limited), doctor (Linked to HCP)
**Migration**: `2026_06_30_000004_create_roles_table.php`
**Model**: `App\Models\Role`
**Relations**: HasMany → `users` (via role_id), HasMany → `rolePermissions` (via role_id)

**Note**: `users.role_id` FK added by migration `2026_06_30_000005_add_role_id_to_users_and_role_permissions.php`. The legacy `users.role` string column remains for backward compatibility.

---

### 103. `permission_audit_log` 🆕 (Tenant-scoped)
Audit trail for all permission changes — logs creates, updates, archives, assignments, and revocations with old/new JSON snapshots.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `action` | varchar(40) | NOT NULL, INDEX | created, updated, archived, assigned, revoked, override_allowed, override_denied, override_removed |
| `entity_type` | varchar(60) | NOT NULL, INDEX | permission, role_permission, user_permission |
| `entity_id` | bigint unsigned | nullable | FK to the entity's table |
| `role` | varchar(40) | nullable | Role name at time of change (denormalized) |
| `user_id` | bigint unsigned | nullable | FK → users.id (affected user, if user-level override) |
| `permission_id` | bigint unsigned | nullable | FK → permissions.id |
| `permission_name` | varchar(100) | nullable | Permission name at time of change (denormalized) |
| `old_value` | text | nullable | JSON snapshot of previous state |
| `new_value` | text | nullable | JSON snapshot of new state |
| `changed_by` | bigint unsigned | NOT NULL | FK → users.id (who made the change) |
| `changed_date` | datetime | NOT NULL, INDEX | When the change occurred |
| `tenant_id` | bigint unsigned | nullable | Tenant scope |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |

**Indexes**: `action`, `entity_type`, `changed_date`
**Migration**: `2026_06_30_000003_create_permission_audit_log_table.php`
**Model**: `App\Models\PermissionAuditLog`
**Relations**: BelongsTo → `changedBy` (User), `permission` (Permission), `affectedUser` (User)
**Trait**: `App\Livewire\Concerns\LogsPermissionAudit` — reusable `logAudit()` helper for Livewire components

---

## Nursing Module — Room Transfer History

### 108. `admission_room_histories` 🆕 (Tenant-scoped)

Tracks every ward room / ICU bed assignment for an admission, enabling per-room billing with a noon-cutoff day calculation and full audit trail of patient transfers.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `admission_id` | bigint unsigned | NOT NULL, FK → admissions(id) ON DELETE NO ACTION | Parent admission |
| `room_type` | varchar(20) | NOT NULL | `ward_room` or `icu_bed` |
| `room_id` | bigint unsigned | NOT NULL | FK to `ward_rooms.id` or `icu_beds.id` (polymorphic by room_type) |
| `room_name` | varchar(100) | nullable | Room/bed name snapshot at assignment time |
| `ward_name` | varchar(100) | nullable | Ward name snapshot at assignment time |
| `room_rate` | decimal(10,2) | NOT NULL, default 0 | Daily rate snapshot at assignment time (₱/day) |
| `assigned_at` | timestamp | NOT NULL | When patient was assigned to this room |
| `unassigned_at` | timestamp | nullable | When patient left this room (null = currently assigned) |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Key**: `admission_id` → `admissions(id)` ON DELETE NO ACTION
**Static Helpers**:
- `recordAssignment(admissionId, roomType, roomId, roomName, wardName, roomRate, tenantId, userId, now)` — closes any active history first, then inserts new active record
- `closeActive(admissionId, now, userId)` — sets `unassigned_at` on the current open record
**Touchpoints**: `AdmissionCrud::saveAdmit()` (initial), `AdmissionCrud::discharge()`, `NursingCrud::saveTransfer()`, `NursingCrud::confirmDischarge()`
**Billing**: `BillingCrud` queries this table for per-room line items; room charge = `days × room_rate` using calendar-day with noon cutoff rule (if discharged before 12:00 noon on multi-day stay, subtract 1 day)
**Migration**: `2026_07_02_000001_create_admission_room_histories_table.php`
**Model**: `App\Models\AdmissionRoomHistory`
**Relations**: BelongsTo → `admission` (Admission)

---

## Konsulta Module Tables (PhilHealth Primary Care Benefit)

### 109. `konsulta_enrollments` 🆕 (Tenant-scoped)

Patient enrollments in the PhilHealth KONSULTA (Konsultasyong Sulit at Tama) primary care benefit package.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `enrollment_code` | varchar(30) | NOT NULL | Auto-generated code (KON-XXXXX) |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) ON DELETE NO ACTION | Enrolled patient |
| `philhealth_member_id` | bigint unsigned | nullable, FK → philhealth_members(id) | PhilHealth membership link |
| `primary_care_provider_id` | bigint unsigned | nullable, FK → health_care_professionals(id) | Assigned primary care provider |
| `enrollment_date` | date | NOT NULL | Date of enrollment |
| `expiry_date` | date | nullable | Enrollment expiry date |
| `status` | varchar(30) | NOT NULL, default 'Active' | Active, Inactive, Transferred, Expired |
| `risk_classification` | varchar(100) | nullable | Risk classification level |
| `fpe_completed` | tinyint(1) | NOT NULL, default 0 | First Patient Encounter completed flag |
| `fpe_completed_at` | timestamp | nullable | When FPE was marked complete |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Composite Unique**: `(enrollment_code, tenant_id, history)`
**Foreign Keys**: All ON DELETE NO ACTION
**Relations**: BelongsTo → `patient` (Patient), `philhealthMember` (PhilhealthMember), `primaryCareProvider` (HealthCareProfessional); HasMany → `consultations` (KonsultaConsultation)
**Migration**: `2026_07_02_120000_create_konsulta_tables.php`
**Model**: `App\Models\Konsulta\KonsultaEnrollment`

---

### 110. `konsulta_consultations` 🆕 (Tenant-scoped)

Primary care consultation records under KONSULTA — each visit with full SOAP notes, vitals, ICD-10 coding, and FPE tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `consultation_code` | varchar(30) | NOT NULL | Auto-generated code (KNC-XXXXX) |
| `enrollment_id` | bigint unsigned | **nullable**, FK → konsulta_enrollments(id) ON DELETE NO ACTION | Parent enrollment (nullable — supports standalone consultations) |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) ON DELETE NO ACTION | Patient |
| `provider_id` | bigint unsigned | nullable, FK → health_care_professionals(id) | Consulting provider |
| `consultation_date` | datetime | NOT NULL | Date and time of consultation |
| `chief_complaint` | text | nullable | Chief complaint |
| `diagnosis` | text | nullable | Clinical diagnosis |
| `icd10_code_id` | bigint unsigned | nullable, FK → icd_10_codes(id) | Standardized ICD-10 code |
| `assessment` | text | nullable | SOAP Assessment |
| `management_plan` | text | nullable | SOAP Plan |
| `notes` | text | nullable | Additional notes |
| `bp` | varchar(20) | nullable | Blood pressure (e.g. "120/80") |
| `temperature` | decimal(5,2) | nullable | Temperature (°C) |
| `heart_rate` | int | nullable | Heart rate (bpm) |
| `respiratory_rate` | int | nullable | Respiratory rate (breaths/min) |
| `weight` | decimal(6,2) | nullable | Weight (kg) |
| `height` | decimal(6,2) | nullable | Height (cm) |
| `is_fpe` | tinyint(1) | NOT NULL, default 0 | First Patient Encounter flag — triggers FPE completion on parent enrollment |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Note**: `enrollment_id` made nullable by migration `2026_07_02_120001` to allow standalone consultations from the Consultations tab (no enrollment context).
**Relations**: BelongsTo → `enrollment`, `patient`, `provider`, `icd10Code`; HasMany → `prescriptions`, `labReferrals`
**Migration**: `2026_07_02_120000_create_konsulta_tables.php` + `2026_07_02_120001_make_enrollment_id_nullable_in_konsulta_tables.php`
**Model**: `App\Models\Konsulta\KonsultaConsultation`

---

### 111. `konsulta_prescriptions` 🆕 (Tenant-scoped)

GAMOT medicine prescriptions issued during KONSULTA consultations — tracks PhilHealth-covered medicines under the GAMOT benefit.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `consultation_id` | bigint unsigned | NOT NULL, FK → konsulta_consultations(id) ON DELETE NO ACTION | Parent consultation |
| `enrollment_id` | bigint unsigned | **nullable**, FK → konsulta_enrollments(id) ON DELETE NO ACTION | Parent enrollment (nullable) |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) ON DELETE NO ACTION | Patient |
| `medicine_name` | varchar(200) | NOT NULL | Prescribed medicine name |
| `dosage` | varchar(100) | nullable | Dosage (e.g. "500mg") |
| `frequency` | varchar(100) | nullable | Frequency (e.g. "TID", "q8h") |
| `duration` | varchar(100) | nullable | Duration (e.g. "7 days") |
| `quantity` | int | NOT NULL, default 1 | Quantity prescribed |
| `is_gamot_covered` | tinyint(1) | NOT NULL, default 0 | Covered under PhilHealth GAMOT benefit |
| `status` | varchar(30) | NOT NULL, default 'Prescribed' | Prescribed, Dispensed, Cancelled |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Migration**: `2026_07_02_120000_create_konsulta_tables.php` + `2026_07_02_120001`
**Model**: `App\Models\Konsulta\KonsultaPrescription`
**Relations**: BelongsTo → `consultation` (KonsultaConsultation), `enrollment` (KonsultaEnrollment), `patient` (Patient)

---

### 112. `konsulta_lab_referrals` 🆕 (Tenant-scoped)

LABORATORYO diagnostic test referrals issued during KONSULTA consultations — tracks PhilHealth-covered lab referrals under the KONSULTA benefit.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation key |
| `consultation_id` | bigint unsigned | NOT NULL, FK → konsulta_consultations(id) ON DELETE NO ACTION | Parent consultation |
| `enrollment_id` | bigint unsigned | **nullable**, FK → konsulta_enrollments(id) ON DELETE NO ACTION | Parent enrollment (nullable) |
| `patient_id` | bigint unsigned | NOT NULL, FK → patients(id) ON DELETE NO ACTION | Patient |
| `test_type` | varchar(200) | NOT NULL | Type of diagnostic test |
| `referral_date` | date | NOT NULL | Date of referral |
| `performed_date` | date | nullable | Date test was performed |
| `result` | text | nullable | Test result/findings |
| `status` | varchar(30) | NOT NULL, default 'Pending' | Pending, Completed, Cancelled |
| `notes` | text | nullable | Additional notes |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Migration**: `2026_07_02_120000_create_konsulta_tables.php` + `2026_07_02_120001`
**Model**: `App\Models\Konsulta\KonsultaLabReferral`
**Relations**: BelongsTo → `consultation` (KonsultaConsultation), `enrollment` (KonsultaEnrollment), `patient` (Patient)

---

## eClaims Module — Claim Procedures

### 113. `claim_procedures` 🆕 (Tenant-scoped)

Stores individual procedure line items for PhilHealth eClaims — one row per PROCEDURE element in the XML submission. Used to generate the `<PROCEDURE>` elements in the eClaims SOAP envelope.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | bigint unsigned | PK, auto-increment | Surrogate PK |
| `tenant_id` | bigint unsigned | NOT NULL, INDEX | Multi-tenant isolation |
| `philhealth_claim_id` | bigint unsigned | NOT NULL, FK → philhealth_claims(id) ON DELETE CASCADE, INDEX | Parent claim |
| `sequence` | int | NOT NULL, default 1 | Procedure sequence order (1, 2, 3…) |
| `procedure_description` | text | nullable | Description of the procedure performed |
| `rvs_code` | varchar(10) | nullable, INDEX | RVS code (e.g. 20480 for Lap Chole) |
| `procedure_date` | date | nullable | Date the procedure was performed |
| `laterality` | varchar(20) | nullable | Left, Right, Bilateral |
| `special_consideration` | varchar(50) | nullable | cataract, zbenefit, maternal, etc. |
| `is_active` | tinyint(1) | NOT NULL, default 1 | Active/inactive flag |
| `history` | tinyint(1) | NOT NULL, default 1 | 1=current, 0=archived |
| `created_by` | bigint unsigned | nullable | FK → users.id |
| `created_date` | timestamp | nullable | Creation timestamp |
| `updated_by` | bigint unsigned | nullable | FK → users.id |
| `updated_date` | timestamp | nullable | Last modification timestamp |

**Foreign Key**: `philhealth_claim_id` → `philhealth_claims(id)` ON DELETE CASCADE
**Relations**: BelongsTo → `philhealthClaim` (PhilhealthClaim), `rvsCode` (RvsCode, by code text)
**CRUD**: Managed via `ClaimItems` Livewire component (modal with inline editable table)
**XML**: Each row becomes a `<PROCEDURE>` element in the eClaims SOAP envelope, ordered by `sequence`
**Migration**: `2026_07_12_000003_create_claim_procedures_table.php`
**Model**: `App\Models\ClaimProcedure`


