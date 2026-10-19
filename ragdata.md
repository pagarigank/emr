# SITHEMR Project RAG Data

This file tracks the overarching details, decisions, and modifications made throughout the lifetime of the project.

## SESSION STATE — 2026-10-19 (Production Server 500 Error Resolved & Verified)

### 2026-10-19 (Production 500 Error Fix — Case-Sensitive Namespace on Linux)
- **[BUGFIX]** Resolved `500 Internal Server Error` on `https://emr.sititsolutions.com/reports`:
  - **Root Cause**: `app/models/er/ertriagerecord.php` was declared with `namespace App\Models\ER;` (uppercase `ER`), while `HospitalReportsDashboard.php` and `PdfReportController.php` imported `use App\Models\Er\ErTriageRecord;` (titlecase `Er`). On Windows, filesystem case-insensitivity masked this mismatch; on Linux, Composer's optimized classmap and PSR-4 resolution failed with `Class "App\Models\Er\ErTriageRecord" not found`.
  - **Fix**:
    1. Updated `app/Models/er/ertriagerecord.php` namespace to `App\Models\Er;` to match the rest of the ER models (`ErAdmission`, `ErBed`, `ErZone`).
    2. Added a backward-compatible alias `class_alias(ErTriageRecord::class, 'App\Models\ER\ErTriageRecord')` so any legacy code referencing `ER` continues to resolve.
    3. Regenerated production optimized classmap (`composer dump-autoload -o`) and recached Laravel (`config:cache`, `route:cache`, `view:cache`).
  - **Verification**: Verified authenticated HTTP requests against the production server:
    - `https://emr.sititsolutions.com/reports` -> **HTTP 200** (151,715 bytes rendered)
    - `https://emr.sititsolutions.com/reports/print/ahsr` -> **HTTP 200** (28,765 bytes rendered)
    - `https://emr.sititsolutions.com/reports/pdf/ahsr` -> **HTTP 200** (884,927 bytes PDF generated)

### 2026-10-19 (Production Deployment to 192.168.100.201 & Migration Execution)
- **[DEPLOYMENT]** Safely deployed the latest application codebase and migrations to the production server (`192.168.100.201` / `https://emr.sititsolutions.com`):
  - **1. Pre-Deployment Configuration Safety**:
    - Checked existing production `/var/www/sithemr/.env` to avoid overwriting production configurations (`APP_URL`, `DB_HOST`, `DB_USERNAME`, `APP_KEY`). Preserved existing working configuration.
    - Generated a safe rollback backup archive at `/home/sitadmin/sithemr_pre_deploy_*.tar.gz`.
  - **2. Selective File Transfer (33 Files)**:
    - Transferred only the new and modified application files across `app/`, `resources/`, and `routes/`:
      - Controllers & Livewire: `PdfReportController.php`, `HospitalReportsDashboard.php`, `MicuPatientCrud.php`, `DrugCrud.php`, `SecurityHeaders.php`, `MedicalRecordsCrud.php`, `BillingCrud.php`, dispensing components (`Lab`, `Opd`, `Pharmacy`, `Procedures`, `Radiology`, `Supply`), and models (`Admission`, `AdmissionRoomHistory`).
      - Routes & Styles: `routes/web.php`, `resources/css/app.css`.
      - Views: `hospital-reports-dashboard.blade.php`, `document-print.blade.php`, `invoice-print.blade.php`, medical certificate/billing/prescription PDF views.
  - **3. Composer & Dependencies**:
    - Executed `composer install --no-dev --optimize-autoloader` installing `barryvdh/laravel-dompdf` (v3.1.2) and generating optimized classmaps.
  - **4. Database Migrations**:
    - Ran `php artisan migrate --force` against production MSSQL database (`sithemr` on `127.0.0.1:1433`). All 191 tables confirmed fully up to date.
  - **5. Frontend Asset Compilation & Caching**:
    - Compiled production Vite bundle (`npm run build`) in 1.11s generating `public/build/assets/app-BKDpBm6J.css` and JS chunks.
    - Cleared and regenerated optimized Laravel caches: `config:cache`, `route:cache`, `view:cache`.
  - **6. Verification & Health Checks**:
    - Verified all core classes (`PdfReportController`, `HospitalReportsDashboard`, `Pdf` facade) resolve via classmap.
    - Verified DomPDF generates binary PDF buffers without errors on production CLI.
    - Verified web server returns `HTTP 200` on `https://emr.sititsolutions.com/login` and `HTTP 302` for protected routes.

### 2026-10-19 (Application Color Theming Alignment — Zero Dark Themes)
- **[UI/THEME]** Aligned the Reports Hub and Standalone Print Document views with the application's clean light healthcare color scheme:
  - **1. Reports Document Catalog Tray**:
    - Replaced dark background (`slate-900 / teal-950`) with clean white (`bg-white border border-gray-200/90 shadow-sm`).
    - Standardized card backgrounds to light gray (`bg-gray-50/60 hover:bg-white border-gray-200/90`).
    - Aligned buttons with the application's primary light blue theme (`bg-light-blue-600 hover:bg-light-blue-700 text-white`) and clean white border buttons.
  - **2. Tab 2 (AHSR / DOH Statistical Banner)**:
    - Replaced dark teal gradient (`from-teal-800 to-cyan-900`) with clean white card styling (`bg-white border border-gray-200/90 shadow-sm`).
    - Converted inner KPI blocks to light cards (`bg-gray-50/80 border border-gray-200/80`) with high-contrast text (`text-gray-900` / `text-teal-700`).
  - **3. Standalone Print Document Action Bar (`/reports/print/{type}`)**:
    - Converted sticky top action bar from dark slate (`#0f172a`) to clean white (`#ffffff` with `#e2e8f0` border).
    - Converted date inputs and document selector to clean white with slate text and subtle borders.
  - **4. Verified via Playwright**:
    - Captured screenshots of Dashboard Overview, Document Catalog Tray, AHSR tab banner, and Standalone Print View. Confirmed zero dark elements remain.

### 2026-10-19 (Individual Report Document Types, Dedicated Clean Printouts & True PDF Exports)
- **[FEATURE]** Completely redesigned the report printing architecture so reports print as **individual official hospital documents** without navigation menus, sidebars, or web controls:
  - **1. Standalone Document Print Engine (`/reports/print/{type?}`)**:
    - Route `GET /reports/print/{type?}` handled by `PdfReportController::hospitalReportsPrintView` rendering `resources/views/pages/reports/document-print.blade.php`.
    - Fully isolated from the main web application layout (zero sidebar, zero header, zero web navigation).
    - Floating `.no-print-bar` at top containing:
      - Quick document selector dropdown across all 10 official hospital report document types.
      - Date range filters (`From` / `To`) with instant update.
      - `🖨️ Print Document (Ctrl+P)` button (triggers `window.print()`).
      - `📄 Download True PDF` button (links to `/reports/pdf/{type}`).
      - `✕ Back to Reports Hub` link.
    - Physical paper canvas styling: A4/Letter sheet format with official letterhead, control reference numbers, date ranges, data tables, and formal tripartite signature blocks (Prepared by / Attested by / Approved by Medical Center Chief).
    - Print media rules: `@media print { .no-print-bar { display: none !important; } @page { margin: 10mm 12mm; size: letter portrait; } }`.
  - **2. 10 Dedicated Individual Document Types**:
    1. `ahsr`: DOH Annual Hospital Statistical Report (AHSR / OHSRS Form 1 - BOR, ALOS, Bed Days, GDR/NDR, clinical departmental volume, top 10 morbidity).
    2. `morbidity`: Ten (10) Leading Causes of Morbidity & Mortality Report (ICD-10 clinical diagnoses, case counts, male/female distribution, mortality rates).
    3. `census`: Daily Inpatient Midnight Bed Census & Room Occupancy Report (24:00H room utilization summary, full roster of currently admitted inpatients with days stayed and diagnoses).
    4. `opd`: Outpatient Department (OPD) Consultation Registry Logbook (consultations, clinics, attending physicians, assessments).
    5. `er`: Emergency Room (ER) 24-Hour Triage & Clinical Encounters Logbook (ESI Levels 1-5, chief complaints, patient dispositions).
    6. `pidsr`: DOH PIDSR Weekly Notifiable Disease Report (WNDR / RA 11332 - Category I 24h immediate CIF alerts, Category II weekly conditions).
    7. `ancillary`: Diagnostic & Ancillary Services Workload Summary (Clinical Laboratory test sections and Radiology & Imaging modality outputs).
    8. `pharmacy`: Pharmacy Dangerous Drugs (DDB S-2) & Stock Expiry Report (controlled substances dispensing log and near-expiry batch tracking).
    9. `financial`: Daily Cashier Collections & Official Receipts Remittance Statement (payment channels, gross totals, PhilHealth deductions, net vault remittance).
    10. `philhealth`: PhilHealth e-Claims Performance & Transmittal Summary (All Case Rates, approved, denied, RTH claims, turnaround time).
  - **3. Global Print Safety in App Layout (`resources/css/app.css`)**:
    - Added `@media print { header, aside, nav, .sidebar, .no-print, [role="navigation"], #sidebar { display: none !important; } }` ensuring that accidental browser `Ctrl+P` anywhere in the app never includes navigation menus or headers.
  - **4. Reports Hub Document Catalog (`resources/views/livewire/reports/hospital-reports-dashboard.blade.php`)**:
    - Added an "Individual Official Hospital Documents & Regulatory Printouts" 10-document catalog tray right beneath the main navigation tabs with 1-click `🖨️ Print View` and `📄 True PDF` buttons for every document.
    - Added inline document print/PDF actions to individual tab banners (AHSR, Census, PIDSR, Financial).
  - **Verification**:
    - Playwright automated tests verified that all 10 document print views render with 0 sidebars, 0 navigation menus, and clean official letterhead.
    - True PDF download event confirmed generating valid PDF files (`Hospital_Report_AHSR_*.pdf`).
- **[FEATURE]** Completely transformed the placeholder `/reports` page into a production-grade, interactive **Hospital Executive & Regulatory Reports Hub** with True PDF exports:
  - **1. Interactive Multi-Tab Dashboard (`app/Livewire/Reports/HospitalReportsDashboard.php` & `resources/views/livewire/reports/hospital-reports-dashboard.blade.php`)**:
    - **Tab 1: Executive Overview**: High-level KPI cards (Bed Occupancy Rate %, Total Encounters, Average Length of Stay, Total Collections & Billed amounts), Department volume progress bars (OPD, ER, Inpatient, OB, Dialysis), and key clinical quality indicators.
    - **Tab 2: DOH Annual Health Facility Statistical Report (AHSR / OHSRS)**: Mandatory metrics per DOH Administrative Order No. 2012-0012:
      - Implementing Bed Capacity (IBC: 62 beds) vs Authorized Bed Capacity (ABC).
      - Inpatient Service Days (Bed Days) and dynamic Bed Occupancy Rate (BOR %).
      - Average Length of Stay (ALOS), Gross Death Rate (GDR %), and Net Death Rate (NDR %, excluding deaths <48h).
      - Top 10 Leading Causes of Morbidity formatted with ICD-10 codes, case frequencies, and percentage shares.
      - Maternal & Child Health delivery metrics (Normal Spontaneous Delivery vs Cesarean Section).
    - **Tab 3: Daily Census & Movement Log**:
      - Live Midnight Inpatient Bed Census table (Ward/ICU room name, capacity, rate, operational status).
      - Recent Outpatient (OPD) Consultations log (date, code, patient, clinic, attending physician, assessment).
      - Emergency Room (ER) arrivals and Triage ESI level (1–5) distribution.
    - **Tab 4: Disease Surveillance & PIDSR (RA 11332)**:
      - Priority notifiable diseases log tracking Category I (24h Immediate CIF) and Category II (Weekly WNDR) conditions (Dengue, Leptospirosis, Typhoid, ILI, Rabies, AFP, Measles).
    - **Tab 5: Ancillary & Pharmacy Services**:
      - Laboratory test request volumes and completion rate statistics.
      - Radiology modality volume (X-Ray, Ultrasound, CT Scan).
      - Pharmacy near-expiry stock monitoring (batches ≤30 days, 31–90 days).
    - **Tab 6: Billing, Revenue & PhilHealth**:
      - Billed invoices vs Cashier collections vs Accounts Receivable (A/R) balance.
      - PhilHealth e-Claims status breakdown (Total Claimed, Claims Paid, Denied, Returned to Hospital - RTH).
    - **Filtering & Presets**: Instant date range filter presets (`Today`, `This Week`, `This Month`, `YTD`, `All Records`, `Custom Date`) that dynamically recalculate all metrics and tables.
  - **2. True PDF Regulatory Export Engine (`app/Http/Controllers/PdfReportController.php` & `resources/views/pdf/hospital-report.blade.php`)**:
    - Added `hospitalReportsPdf(Request $request, string $type = 'ahsr')` generating vector True PDFs via DomPDF.
    - Route `/reports/pdf/{type?}` registered with permission `reports.view`.
    - Features official hospital letterhead, DOH facility licensing credentials, PhilHealth accreditation, control numbers, KPI callout summary grids, structured data tables, and formal signature blocks (Head of Medical Records / HIMD, Chief Nurse, and Medical Center Chief / Administrator).
    - Supports export for all 6 report types: `ahsr`, `overview`, `census`, `pidsr`, `ancillary`, and `financial`.
  - **Verification**:
    - Automated Playwright E2E testing verified 100% pass rate:
      - UI dashboard renders with 0 errors and smoothly navigates all 6 tabs.
      - All 6 True PDF endpoints (`/reports/pdf/overview`, `ahsr`, `census`, `pidsr`, `ancillary`, `financial`) return HTTP 200 OK with valid `application/pdf` payloads (~886 KB each).


- **[BUGFIX]** Audited and resolved issues preventing certificate printing in the Medical Records module (`/medical-records`):
  - **1. Missing Livewire Action / Browser Popup Blocker Suppression (`resources/views/livewire/medical-records-crud.blade.php`)**:
    - The Certificates dropdown used buttons with `wire:click="printCertificate('...')" onclick="openPrintTab(...)"`.
    - Modern browsers (Chrome, Edge, Safari) identify synthetic popup calls originating from Livewire button clicks as unsolicited popups and silently block them from opening.
    - Replaced the button triggers with direct, semantic `<a href="/medical-records/certificate/{type}/{encounterType}/{id}/{patientId}" target="_blank" @click="open = false">` links. Native links with `target="_blank"` are standard browser behavior and are never intercepted or suppressed by popup blockers.
    - Enabled "Certificate of Treatment" for both `er` and `er_triage` encounter types.
  - **2. Duplicate Method Declaration Fatal Error (`app/Livewire/medicalrecordscrud.php`)**:
    - Removed a duplicate declaration of `public function printCertificate(...)` at the bottom of the component class that caused `PHP Fatal error: Cannot redeclare App\Livewire\MedicalRecordsCrud::printCertificate()`, breaking Livewire updates on the page.
    - Updated `loadErTriageDetail(int $id)` to set `'print_type' => 'er_triage'` (previously hardcoded to `'er'`, which collided with `ErAdmission`).
  - **3. Encounter Resolution & ER Triage Model Support (`app/Http/Controllers/PdfReportController.php`)**:
    - Imported `App\Models\Er\ErTriageRecord`.
    - Extended `medicalRecordsCertificate()` to query `ErTriageRecord::withoutGlobalScopes()->with(['acuityLevel', 'mobilityStatus'])->find($encounterId)` when `encounterType` is `'er_triage'`, `'triage'`, or `'er triage'` (with an automatic fallback if an `ErAdmission` is not found).
    - Added data mappings for ER Triage: encounter code (`ERT#id` or `er_triage_code`), triage date/time, chief complaint / triage assessment, attending physician, and vitals (`blood_pressure`, `heart_rate`, `temperature`, `respiratory_rate`, `oxygen_saturation`, `pain_scale`).
  - **4. String vs DateTime Fatal Error (`PdfReportController.php` & `pdf/medical-certificate.blade.php`)**:
    - Fixed `Call to a member function format() on string` on encounters where dates (`triage_date`, `consultation_datetime`, `discharge_date`, etc.) are returned as raw strings rather than Carbon instances.
    - Implemented a safe `$formatDate` helper in `PdfReportController` and wrapped date parsing/age computation in `medical-certificate.blade.php` to guarantee error-free rendering even with incomplete date fields.
    - Safe-guarded laboratory and pharmacy order lookups to only query models with matching foreign key columns (`opd_consultation_id`, `er_admission_id`, `admission_id`, `ob_consultation_id`).
  - **Verification**:
    - Tested all 12 certificate types across encounter types via Playwright with 100% pass rate:
      - ER Triage: Medical Certificate (200 OK, 1.27 MB PDF), Certificate of Treatment (200 OK, 1.27 MB PDF), Clinical Abstract (200 OK, 1.27 MB PDF).
      - OPD Consultation: Medical Certificate, Clinical Abstract.
      - ER Treatment / Admission: Medical Certificate, Certificate of Treatment, Clinical Abstract.
      - Inpatient Admission: Medical Certificate, Certificate of Confinement, Discharge Summary, Clinical Abstract.
    - Verified UI flow on `http://127.0.0.1:8000/medical-records` with Playwright screenshot confirming the dropdown opens with valid links directly opening the True PDF certificates in new tabs.


- **[BUGFIX]** Diagnosed and resolved the root causes preventing Statement of Account and Invoice printing:
  - **1. X-Frame-Options: DENY Blocking Modals (`app/Http/Middleware/SecurityHeaders.php`)**:
    - `SecurityHeaders` was setting `X-Frame-Options: DENY` on all responses, causing modern browsers to reject iframe rendering of same-origin modals with a broken/sad document error page (`ERR_BLOCKED_BY_RESPONSE`).
    - Changed `X-Frame-Options` to `SAMEORIGIN`, properly allowing the application's own modals to embed statement and report views while still guarding against external clickjacking.
  - **2. Relative URLs vs APP_URL (`app/Livewire/Billing/billingcrud.php` & `resources/views/livewire/billing/billing-crud.blade.php`)**:
    - Replaced `route('billing.statement')` and `route('billing.invoice.print')` absolute URLs (which generated `http://localhost/...` without port 8000) with relative paths (`/billing/statement/...` and `/billing/invoice/...`).
  - **3. Dual HTML Preview & True PDF for Statements (`routes/web.php` & `statement-of-account.blade.php`)**:
    - Updated `/billing/statement/{patientId}/{mode}` to return crisp, print-ready HTML by default for modal iframes and direct printing, eliminating browser PDF plugin crashes.
    - Added `?format=pdf&download=1` parameter so the "Download PDF" button continues to deliver vector True PDF documents.
    - Updated modal print button with an inline JS handler that prints the iframe contents natively via `iframe.contentWindow.print()` and added an "Open in New Tab" button.
  - **4. Invoice Print 500 Fatal Error (`resources/views/pages/billing/invoice-print.blade.php`)**:
    - Resolved `Call to undefined method stdClass::getKey()` when printing invoices. Eloquent collection `merge()` was attempting to key-map `stdClass` manual charge objects; fixed by casting to base collection via `$invoice->items->toBase()->concat($manualItems)`.
    - Added manual charge adjustments to `resources/views/pdf/billing-invoice.blade.php` so all adjustments appear on True PDF invoices.
    - Added 1-click "Print HTML" and "PDF" action buttons to both the Invoice Detail Modal and the Invoices table rows.
  - **Verification**:
    - Verified `GET /billing/statement/13/detailed` returns 200 OK HTML.
    - Verified `GET /billing/invoice/1/print?mode=detail` returns 200 OK HTML.
    - Verified `GET /billing/invoice/1/pdf` returns 200 OK `application/pdf`.
    - Verified UI with Playwright screenshots confirming clean embedded rendering in the modal without sad face.

### 2026-10-18 (Billing Module — Auto-Computing Days Stayed to Date & Accrued Room Charges)
- **[BUGFIX] / [FEATURE]** Fixed issue where the Billing module was not auto-computing the number of days stayed to date and accrued room charges for active inpatient confinements:
  - **1. Admission Model Days Stayed Accessor (`app/Models/Admission.php`)**:
    - Added `days_stayed` accessor (`getDaysStayedAttribute()`) implementing standard hospital length-of-stay rule: calendar days from `admission_date->startOfDay()` to `(discharge_date ?? now())->startOfDay() + 1`; subtracts 1 day if discharged before 12:00 PM noon on multi-day stays; minimum 1 day. Dynamically advances with `now()` while the patient remains admitted.
    - Added `computeAccruedRoomCharges()` calculating cumulative Room & Board across all `roomHistories` (or active room history fallback, or current ward/ICU room rate).
  - **2. AdmissionRoomHistory Model Accessors (`app/Models/AdmissionRoomHistory.php`)**:
    - Added `days_stayed` accessor (`getDaysStayedAttribute()`) and `total_charge` accessor (`getTotalChargeAttribute()`).
    - Handles active room stays safely where `unassigned_at` is null by falling back to `admission->discharge_date ?? now()`.
  - **3. Billing Livewire Component (`app/Livewire/Billing/BillingCrud.php`)**:
    - Fixed fatal crash in `createInvoice()` when invoicing active admissions where `$h->unassigned_at` is null (`Call to a member function copy() on null`), now using `$h->days_stayed` and `$h->total_charge`.
    - In `getUnbilledRequestsProperty()`, fixed eager loading of `wardRoom` and `icuBed`; unified stay calculation to `$r->days_stayed` and `$h->days_stayed`.
    - In `getPatientAccountsProperty()`, linked active admission details: added admission code, room name, days stayed to date (`$latestAdmission->days_stayed`), and unbilled room charges.
    - In `viewPatientLedger()`, added `$viewingPatientDaysStayed`, `$viewingPatientRoomRate`, `$viewingPatientAccruedRoomCharge`, and `$viewingPatientIsAdmitted` properties for the Ledger modal.
  - **4. Billing Blade Views (`resources/views/livewire/billing/billing-crud.blade.php`)**:
    - Restored Tab 2 ("For Billing") and Tab 3 ("Invoices") in the tab navigation with dynamic unbilled count badge.
    - In Tab 1 "Patient Accounts", added real-time `[Room Name] • [Xd to date]` badge and `Unbilled: ₱...` pill under admitted patients.
    - In Ledger modal, added Days Stayed (`to date`), Room/Bed location, and Accrued Room & Board in the demographics header.
    - In Ledger modal, added an Active Inpatient Confinement notice banner detailing length of stay, daily rate, and total accrued room charge to date.
  - **5. Statement of Account & Reports**:
    - Updated `routes/web.php` and `resources/views/livewire/billing/statement-of-account.blade.php` to calculate and display inpatient room details, auto-computed days stayed to date, and accrued room charges.
    - Updated `resources/views/livewire/admission/admission-crud.blade.php` detail modal with auto-computed Days Stayed to date.
    - Updated `app/Http/Controllers/PdfReportController.php` confinement certificate to compute Length of Stay to date.
  - **Verification**:
    - Verified backend math via PHP test script: ADM-00001 (88 days @ ₱2,500/day = ₱220,000.00), ADM-00002 (83 days @ ₱800/day = ₱66,400.00).
    - Verified UI with Playwright screenshots for Tab 1 (Patient Accounts), Tab 2 (For Billing with 3 pending items), Ledger Modal with room banner, and Charges Modal displaying itemized days stayed.


### 2026-10-18 (True PDF Clinical Reports, Prescriptions & Medical Certificates)
- **[FEATURE]** Implemented a high-fidelity True PDF generation suite using `Barryvdh\DomPDF\Facade\Pdf` with native `DejaVu Sans` typography, official hospital letterhead, doctor credentials, and clean layouting.
  - **1. Doctor's Prescription (Rx Slip) PDF** (`GET /opd/consultation/{id}/pdf/prescription`):
    - Official hospital header, patient demographics (Age, Gender, Vitals, Address, Hospital ID).
    - Attending physician banner with PRC License, PhilHealth Accreditation, PTR validation, and S2 License.
    - Prominent classic **℞** symbol, itemized medications with Generic Name, Brand Name, Strength, Form, Dispense Quantity with `#` notation, Sig / Directions for use, and special instructions.
    - If no electronic items exist, renders formatted blank physician lines for handwritten prescribing.
    - Legal compliance notes (Generics Law compliance, 30-day validity) and doctor's official signature line.
  - **2. Medical Certificate Suite (True PDF)** (`GET /opd/consultation/{id}/pdf/certificate` and `/medical-records/certificate/{type}/{encounterType}/{encounterId}/{patientId}`):
    - Replaced previous raw HTML `window.print()` view with true binary vector PDF generation.
    - Supports dynamic certificate types:
      - `fit_to_work`: Certificate of Medical Clearance & Fit to Work (declaring patient physically and medically fit to resume normal duties/activities).
      - `illness` / `sick_leave`: Medical Certificate & Sick Leave Excuse (including customizable rest days, start/end dates, and recuperation advice).
      - `general`: Standard consultation medical certificate.
      - `clearance`: Activity/travel medical clearance.
      - `admission`, `discharge`, `treatment`, `abstract`: Hospital confinement, discharge, ER emergency care, and clinical abstract certificates.
    - Features official hospital seal / verification stamp box, dual signatures (Medical Records officer seal + Attending physician credentials), and standard legal caveat ("Not valid for medico-legal or court proceedings unless signed under oath").
  - **3. Clinical Consultation Summary / Medical Abstract PDF** (`GET /opd/consultation/{id}/pdf/summary`):
    - Full SOAP consultation record for patient referral or insurance abstract.
    - Structured Vitals Grid (BP, Heart Rate, Respiratory Rate, Temperature, SpO2, Weight, Height, BMI).
    - Subjective (Chief Complaint & HPI), Objective (Physical Examination), Assessment (Diagnosis & ICD-10 Code), Plan & Management.
    - Itemized Doctor's Orders breakdown (Prescribed Medications, Laboratory requests, Radiology & Imaging requests, CSR supplies & Procedures).
    - Clinical disposition, next follow-up date, and attending physician signature.
  - **4. Diagnostic & Clinical Order Requisition PDF** (`GET /opd/consultation/{id}/pdf/orders`):
    - Requisition slip for Laboratory, Radiology, and CSR supplies.
    - Request codes, priority flags, exam descriptions, and specimen collector / receiving unit sign-off lines.
  - **5. Billing Invoice PDF** (`GET /billing/invoice/{id}/pdf`):
    - True PDF tax invoice with grouped line items (Pharmacy, Lab, Radiology, Supplies, Procedures), gross totals, discounts, payments applied, and cashier sign-off.
  - **Controller & Routes**:
    - Created `app/Http/Controllers/PdfReportController.php` with `prescription()`, `medicalCertificate()`, `consultationSummary()`, `orderRequisition()`, `medicalRecordsCertificate()`, and `billingInvoicePdf()`.
    - Wired routes in `routes/web.php` under OPD, Medical Records, and Billing with `inline` `Content-Disposition` for instant in-browser viewing and vector printing.
  - **UI Integration**:
    - `resources/views/livewire/opd/opd-consultation-crud.blade.php`: Added "Reports & Certificates (True PDF)" panel in the Doctor's consultation sidebar with 1-click links for Rx, MedCert, Summary, and Orders. Added quick PDF badges in the datagrid table rows. Added interactive Medical Certificate configuration modal allowing doctors to select type (Fit to Work, Sick Leave with rest days, Clearance) and enter custom diagnosis/remarks before generating.
    - `app/Livewire/Opd/OpdConsultationCrud.php`: Added state and modal handlers (`$showMedCertModal`, `$medCertType`, `$medCertRestDays`, `$medCertDiagnosis`, `$medCertRemarks`).
  - **E2E Verification**:
    - Tested all endpoints via Playwright with authenticated session; verified 200 OK, `Content-Type: application/pdf`, and `%PDF` binary headers across all reports.

## SESSION STATE — 2026-10-18 (OPD Doctor's Orders Theme & UX Matching is COMPLETE & verified)

### 2026-10-18 (OPD Module — Doctor's Orders Theme & UX Standardization)
- **[UI] / [FEATURE]** Harmonized the Outpatient Department (OPD) consultation module's Doctor's Orders workflow to match the overall SITHEMR hospital theme and design system.
  - **Sidebar Order Launcher**: Replaced generic buttons with a refined "Doctor's Orders" launcher featuring consistent department accent icon chips:
    - Pharmacy / Medications: Emerald (`text-emerald-700 bg-emerald-100 hover:bg-emerald-50`)
    - Laboratory Tests: Purple (`text-purple-700 bg-purple-100 hover:bg-purple-50`)
    - Medical Supplies (CSR): Amber (`text-amber-700 bg-amber-100 hover:bg-amber-50`)
    - Radiology / Imaging: Indigo (`text-indigo-700 bg-indigo-100 hover:bg-indigo-50`)
    - Clinical Procedures: Rose (`text-rose-700 bg-rose-100 hover:bg-rose-50`)
  - **Active Orders Display**: Redesigned the Active Doctor's Orders section under the SOAP form. Replaced unstyled tables with structured department cards, quick action pills, request codes (`LR-00007`), status badges (`Completed`, `Processing`, `Pending`), priority indicators (STAT with pulsating dot, Urgent, Routine), tabular Philippine Peso formatting (`₱`), and responsive empty-state cards.
  - **5 Order Modals Unified**:
    - Consistent `fixed inset-0 bg-gray-900/60 backdrop-blur-sm` backdrop.
    - Gradient headers matching department tokens.
    - Real-time debounced search bar (`$orderSearch`) filtering formulary drugs, diagnostic tests, CSR inventory items, and radiology modalities without full page reloads.
    - Clean inline prescription sig fields (Qty, Frequency, Route) when items are selected.
    - Live selection count pill (e.g., "1 test selected") and disabled submit button when empty.
    - Accessible spinners (`wire:loading`) on submission buttons.
  - **Backend Updates** (`app/Livewire/Opd/OpdConsultationCrud.php`):
    - Added `$orderSearch` live property; reset in `openOrderModal()` and `closeModal()`.
    - Added eager loading for `SupplyItem::with('unit')`, `RadiologyExamType::with('modality')`, and `Procedure::with('category')`.
    - Added user flash feedback on successful order submissions.
  - **Dispensing & Billing Integration (Fully Connected & Verified)**:
    - All 5 order types (`PharmacyRequest`, `LabRequest`, `CsrRequest`, `RadiologyRequest`, `ProcedureRequest`) store `opd_consultation_id` and resolve `patient_id` through `resolvePatientId()`.
    - Respective dispensing modules (`/pharmacy/dispensing`, `/lab/dispensing`, `/supply/dispensing`, `/radiology/dispensing`, `/procedure/dispensing`) query and display OPD requests with clinic names (e.g. `OPD - General Medicine Clinic`) and patient details.
    - Automatic Ledger Charging (`PatientAccountEntry::postCharge`): When items are dispensed or marked complete, each module executes `postChargeToLedger()` which records a debit charge on the patient's billing account tagged with `opd_consultation_id`.
    - Invoicing / Billing (`BillingCrud`): `getUnbilledRequestsProperty` aggregates completed OPD service requests with `billing_status = 'Pending'` for statement generation and patient ledger tracking (`/billing`).
    - Added real-time Livewire event synchronization: Dispensing modules now dispatch `refreshOpdOrders` to `OpdConsultationCrud` (with `#[On('refreshOpdOrders')]`) when orders are completed or dispensed.

## SESSION STATE — 2026-08-10 (Patient Appointment Scheduling is COMPLETE & verified)

**Environment:** `E:\sithemr`, Laravel 12, PHP 8.2.12, XAMPP, SQL Server (DB `sithemr`, 127.0.0.1:1433, user `root`, pass `P@ssw0rd`). Dev server `php artisan serve` runs on port 8000. `.env` `APP_URL=http://localhost` (≠ 127.0.0.1 — use relative URLs, not `route()` absolute output). Admin login: `admin@demohospital.com` / `Admin@12345`.

**Feature status: FULLY WORKING, all E2E verified via Playwright (0 console errors).** See the 2026-08-10 `[FEATURE]` entry below for architecture/files. The login page now has a "Book an Appointment" link (`/book-appointment`). Public flow (department→doctor→date→slot→book) works; overlap prevention excludes taken slots (18→17 verified); doctor schedule page (admin `/doctors/schedule`) shows/edit/reschedule/mark done/cancel all verified.

**Current DB test data in `doctor_appointments`:**
- APT-00001 = "Overlap Test Patient" — status **Cancelled** (2026-08-11, 08:00–08:30, Dr. Maria Santos id 1)
- APT-00002 = "Overlap Test Patient" — status **Completed** (2026-08-11, 08:30–09:00, Dr. Maria Santos id 1)
- Delete these rows if a clean slate is wanted.

**Remaining next steps (optional):**
1. Test a **non-admin doctor** user on `/doctors/schedule` — verify they only see their own appointments (isAdmin gate in `AppointmentScheduleCrud.php` uses role superadmin/tenant_admin; doctor role filtered by `health_care_professional_id`).
2. Optionally add the "Book an Appointment" link to the *public* 404/other guest pages (only login page done so far).
3. If `APP_URL` is ever changed to match the real host, the login link could revert to `route('public.book')`.

**Gotchas to remember:** Multi-tenancy (`tenant_id` = hospitals.id, DEMO hospital id 1); history-column soft delete (always `history=1` / re-add in `withoutGlobalScopes()`); models `$timestamps=false`, `$guarded=['id']`; Livewire 3 — no `wire:change`, and cascading UI needs `wire:model.live` (plain `wire:model` is deferred); LSP errors on Livewire files are false positives; Playwright installed locally via `npm install playwright --no-save` (NOT in package.json) — test scripts were ad-hoc `.cjs` files and were deleted after passing; `npx playwright install chromium` already downloaded the browser.

### 2026-08-10 (Doctors — Patient Appointment Scheduling Feature)
- **[FEATURE]** Implemented a public, no-login patient appointment booking page plus a doctor-side scheduling CRUD with overlap prevention.
  - **Public booking page** (`GET /book-appointment`, name `public.book`): patients pick Department → Doctor → Date → Time Slot (grid) → enter name/phone/email/reason → submit → receive reference code `APT-00001`. Renders standalone via `->layout('layouts.guest')`.
    - Livewire component: `app/Livewire/Public/AppointmentBooking.php` + view `resources/views/livewire/public/appointment-booking.blade.php`
    - Slot generation: config `config/appointment.php` (slot_minutes default 30, working hours 08:00–17:00). `loadAvailableSlots()` excludes overlapping bookings and past slots on today's date.
    - Race-condition-safe booking: `book()` wraps conflict check + insert in `DB::transaction()` with `lockForUpdate()`.
    - No auth required: `app/Http/Middleware/JwtMiddleware.php` skip list includes `book-appointment`, and Livewire updates whose referer ends in `/book-appointment` (or `/login`) bypass JWT.
  - **Doctor scheduling CRUD** (`GET /doctors/schedule`, name `doctors.schedule`, permission `doctors.schedule`): search + status/date/doctor filters, edit/reschedule modal with available-slot grid, Mark In Progress / Mark Done / Cancel. Non-admin doctors see only their own appointments.
    - Livewire component: `app/Livewire/Doctors/AppointmentScheduleCrud.php` + view `resources/views/livewire/doctors/appointment-schedule-crud.blade.php`
    - `save()` re-checks overlap server-side before rescheduling.
  - **Data**: new table `doctor_appointments` (migration `2026_08_10_000001_create_doctor_appointments_table.php`), model `app/Models/DoctorAppointment.php` (ScopedByTenant, relationships `department()`/`doctor()`).
  - **Permissions**: `PermissionSeeder.php` added `doctors.schedule` permission + assigned to doctor role (66 permissions / 214 role-permission assignments seeded).
  - **Login page**: added a "Book an Appointment" link (`/book-appointment`) below the login form in `resources/views/livewire/auth/login.blade.php`.
  - **Sidebar**: added "Patient Scheduling" link (calendar icon, active on `doctors.schedule`) in `resources/views/components/layouts/app.blade.php`; Doctors Portal link now active only on `doctors.dashboard`.
  - **Testing setup**: HCP departments assigned — Dr. Maria Santos (id 1) → Cardiology (1), Dr. Juan Dela Cruz (id 2) → General Surgery (10), Nurse Beatrice Reyes (id 3) → Pediatrics (5).
  - **Livewire 3 gotcha (fixed)**: plain `wire:model` is deferred and does NOT re-render; must use `wire:model.live` for cascading selects/date so `updatedX()` hooks fire and the UI updates. Also removed all `wire:change="updatedX"` attributes (Livewire 3 throws `DirectlyCallingLifecycleHooksNotAllowedException`).
  - **E2E verified (Playwright, no console errors)**: login page link → booking flow created APT-00001/APT-00002; overlap prevention removes taken slot (18→17 slots); admin schedule page shows bookings; reschedule modal excludes conflicting slots but includes own slot; status → In Progress → Completed; cancel works.
  - **Files modified**: `routes/web.php`, `app/Http/Middleware/JwtMiddleware.php`, `database/seeders/PermissionSeeder.php`, `resources/views/components/layouts/app.blade.php`, `resources/views/livewire/auth/login.blade.php`.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-24 (Nursing Orders — Batch Queue System with Auto-Save Tab Switch)
- **[FEATURE]** Implemented batch queue system for multi-tab order placement with auto-save on tab switch.
  - **Problem**: User had to click "Add to Queue" for each tab before switching, losing selections if they forgot.
  - **Solution**: Auto-save selections when switching tabs + update existing batches (no duplicates).
  - **PHP changes**:
    - Added `$previousOrderType` property to track last active tab
    - Modified `updatedOrderType()` to auto-save current tab's selections to batchOrders before switching, then restore new tab's selections from batchOrders
    - Modified `queueOrder()` to update existing batch entries instead of always appending (prevents duplicates)
    - Modified `clearBatch()` to also reset current selection properties
  - **Blade changes**:
    - Enlarged order modal from `max-w-3xl` to `max-w-5xl` for easier navigation
    - Increased modal height from `max-h-[75vh]` to `max-h-[85vh]`
  - **Files modified**: `app/Livewire/Nursing/NursingCrud.php`, `resources/views/livewire/nursing/nursing-crud.blade.php`
  - **User flow**: Check items on Lab tab → switch to Pharmacy (Lab auto-saved) → check Pharmacy items → switch back to Lab (restored) → modify Lab selections → switch to CSR (Lab updated, CSR loads) → click "Add CSR to Order" → click "Place X Order(s)"
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-23 (Nursing Orders Enhancement — ER-Style Full-Featured Orders)
- **[FEATURE]** Enhanced Nursing Module's Orders tab to match the ER's full-featured Orders section with item-level details, status management, billing, and cancel actions. Both ER and Nursing now have consistent, full-featured order management.
  - **Before**: Nursing orders showed only 5 columns (Request #, Source, Status, Charge/Total, Date) with no item details, no status management, no billing display
  - **After**: Enhanced to 7-8 columns with inline item details, individual status dropdowns, billing status, priority, and cancel buttons — matching ER's layout
  - **Changes**:
    - Added model imports: `LabRequestItem`, `CsrRequestItem`, `PharmacyRequestItem`, `RadiologyRequestItem`, `ProcedureRequestItem`
    - Added 13 new methods: `refreshOrders()`, `updateLabItemStatus()`, `cancelLabRequest()`, `updateCsrItemStatus()`, `cancelCsrRequest()`, `updateRadiologyItemStatus()`, `cancelRadiologyRequest()`, `updatePharmacyItemStatus()`, `cancelPharmacyRequest()`, `updateProcedureItemStatus()`, `cancelProcedureRequest()`
    - Updated `render()` method — Added `with('items')` eager loading on all 5 order queries
    - Updated `viewAdmission()` — Changed eager loading to include `.items` on all ER and admission relationships
    - Rewrote entire Orders tab Blade section with ER-style tables per order type
  - **Order type sections**: Lab (purple), Pharmacy (emerald), CSR (amber), Radiology (indigo), Procedure (rose)
  - **Features per section**: Section header with icon/title/count badge, type-specific "Request" button, 7-8 column table, items shown inline with per-item status badges and Alpine.js dropdown menus for status changes, cancel buttons on request level with `wire:confirm`
  - **Status workflows**:
    - Lab: Pending → Collected → Processing → Completed / Cancelled
    - Pharmacy/CSR/Radiology/Procedure: Pending → Approved → Partial → Complete / Cancelled
  - **Rollup logic**: When an item's status changes, the parent request's status is recalculated from all items' statuses using `match(true)` with `every()`, `contains()`, `count()` checks
  - **Auto-refresh**: Added `wire:poll.30s="refreshOrders"` for real-time updates
  - **Files modified**: `app/livewire/nursing/nursingcrud.php` (PHP methods + render/viewAdmission), `resources/views/livewire/nursing/nursingcrud.blade.php` (Orders tab section)
  - **Verification**: All features tested in browser — Lab LR-00002 and Pharmacy PR-00002 displayed with item details, status dropdowns functional, parent status rollup working, billing/priority/cancel buttons present
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-23 (Nursing — In-Patient Vitals/Orders Not Displaying Fix)
- **[BUGFIX]** Fixed nursing module In-Patient detail view showing "No vital signs recorded yet" and empty orders despite data existing in the database.
  - **Root cause**: In `app/livewire/nursing/nursingcrud.php:963`, the condition `if ($this->activeAdmissionId)` used PHP truthiness check. The demo admission has `id = 0`, and in PHP `0` is falsy — so the entire data query block (vitals, care notes, orders, MAR, lab/radiology/pharmacy requests) was **skipped**, returning empty collections to the view.
  - **Fix**: Changed to `if ($this->activeAdmissionId !== null)` on line 963 (main data block) and line 992 (OR schedules ternary). This ensures `0` is treated as a valid admission ID rather than falsy.
  - **Lesson**: PHP's `if ($var)` is falsy for `0`, `''`, `null`, `false`. Always use `!== null` when checking nullable integer IDs that could legitimately be `0`.
  - **Files modified**: `app/livewire/nursing/nursingcrud.php` (2 lines)
  - **Verification**: All 4 detail tabs tested in browser — Vitals shows 2 existing records, Orders shows LR-00002, Care Notes and MAR empty (expected).
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-22 (Pharmacy & CSR — Encounter-Wide Print Feature)
- **[FEATURE]** Added dual print capability to Pharmacy Dispensing module — users can now print either a single pharmacy request or all medications issued to the patient for the entire encounter.
  - **Routes**: `GET /pharmacy/request/{id}/print` (single request), `GET /pharmacy/request/{id}/encounter-print` (all requests for the encounter)
  - **Templates**: `resources/views/pages/pharmacy/request-print.blade.php` (green theme, single request), `resources/views/pages/pharmacy/encounter-print.blade.php` (green theme, grouped by request with subtotals and grand total)
  - **Encounter resolution**: Checks `er_admission_id`, `opd_consultation_id`, `admission_id`, `dialysis_session_id`, `or_schedule_id`, `dr_schedule_id`, `ob_consultation_id` to find all linked pharmacy requests for the same encounter
  - **Detail modal buttons**: Green "Print Request" (single), Blue "Print Encounter Medications" (encounter-wide)
  - **Files modified**: `routes/web.php`, `resources/views/pages/pharmacy/request-print.blade.php`, `resources/views/pages/pharmacy/encounter-print.blade.php`, `resources/views/livewire/pharmacy/pharmacy-dispensing-crud.blade.php`
- **[FEATURE]** Replicated the same dual print capability to the CSR/Supply Dispensing module.
  - **Routes**: `GET /supply/request/{id}/print` (single request), `GET /supply/request/{id}/encounter-print` (all requests for the encounter)
  - **Templates**: `resources/views/pages/supply/request-print.blade.php` (sky blue theme, single request), `resources/views/pages/supply/encounter-print.blade.php` (sky blue theme, grouped by request with subtotals and grand total)
  - **CSR items** use: `item_name`, `item_code`, `supply_unit_name`, `quantity`, `given_quantity`, `unit_cost`, `total_cost`, `status` (no dosage/frequency/route like pharmacy)
  - **CSR signature lines**: Released By (CSR Staff), Received By, Verified By (Head of CSR)
  - **Detail modal buttons**: Sky blue "Print Request" (single), Blue "Print Encounter Supplies" (encounter-wide)
  - **Files modified**: `routes/web.php`, `resources/views/pages/supply/request-print.blade.php`, `resources/views/pages/supply/encounter-print.blade.php`, `resources/views/livewire/supply/csr-dispensing-crud.blade.php`
  - **Verification**: All routes pass `php -l` syntax check. LSP errors in Livewire blade files are false positives (`@this` in `@script` blocks compiles server-side).
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-22 (Billing — Duplicate Charge Bugfix on Result Modification, Idempotency Guard)
- **[BUGFIX]** Fixed duplicate charges posted to patient ledger when modifying lab results (and all order types). Completing a lab dispensing then modifying the result caused a second charge row to be inserted, doubling the patient's balance.
  - **Root Cause 1 (Primary)**: `LabRequest::postChargeToLedger()` (and `CsrRequest`, `PharmacyRequest`, `RadiologyRequest`, `ProcedureRequest` equivalents) had **no idempotency check** — every call to `postChargeToLedger()` blindly called `PatientAccountEntry::postCharge()` which unconditionally created a new `patient_account_entries` row. When a lab result was re-saved after modification, the parent request status remained `'Completed'`, triggering the charge post again.
  - **Root Cause 2 (Secondary)**: `source_type` mismatch between charge posting and invoice creation. `LabRequest::postChargeToLedger()` wrote `source_type => 'Laboratory'` (line 112), but `BillingCrud::postChargesToLedger()` (line 641) and `BillingAutoInvoice` (line 62) searched for `source_type => 'Lab'`. The duplicate guard in invoice creation **never matched** lab charges because of this string mismatch.
  - **Fix**: Added idempotency check to all 5 `postChargeToLedger()` methods:
    1. Before posting, queries `PatientAccountEntry::withoutGlobalScopes()->where('source_type', '...')->where('source_id', $id)->where('entry_type', 'charge')->where('history', 1)->first()`
    2. If existing active charge found: updates debit amount if changed, recalculates balance, returns early (no new row)
    3. If not found: creates charge as normal
    4. Fixed `LabRequest` source_type from `'Laboratory'` → `'Lab'` to match `BillingCrud` convention
  - **Files modified**: `app/Models/LabRequest.php:93`, `app/Models/CsrRequest.php:93`, `app/Models/Pharmacy/PharmacyRequest.php:96`, `app/Models/Radiology/RadiologyRequest.php:94`, `app/Models/Procedures/ProcedureRequest.php:96`
  - **Note**: Existing duplicate charges in the database are NOT cleaned up by this fix. A data cleanup (set `history=0` on duplicate entries) may be needed for affected patients.
  - **Verification**: All 5 files pass `php -l` syntax check.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-17 (Billing UI — Manual Charge Rename, Ledger Refresh, Payments Tab Fix)
- **[UI]** Renamed "Manual Charge" to "Adjustment" throughout the billing UI and print statements. Updated labels in the billing CRUD modal and invoice-print.blade.php. Backend method names kept unchanged to avoid breaking Livewire bindings.
- **[BUGFIX]** Fixed the patient ledger modal not refreshing after recording a payment. Added `refreshLedger()` call in `addPayment()` when `showLedgerModal` is open.
- **[BUGFIX]** Fixed patient name display in the Payments tab. Changed from `$pay->invoice?->patient?` (which failed when `billing_invoice_id` was null) to `$pay->patient?` using the direct `BillingPayment::patient()` relationship.
- **[UI]** Updated Payments tab columns: "Invoice" column renamed to "Reference/Source", "Reference" column renamed to "OR/Ref #".
- **[BUGFIX]** Fixed 403 "Unauthorized action" error when editing users in Masters > Users. Root cause was strict type comparison (`!==`) of `tenant_id` values — cast both sides to `(int)` for safe comparison in `UserCrud.php`.
- **[RESTORE]** Restored corrupted `UserCrud.php` where the `save()` method body was accidentally merged into `openEdit()` during a previous edit.
- **[SETUP]** Installed `frontend-design` skill globally via `npx skills add anthropics/skills@frontend-design -g -y`.
- **[FILES MODIFIED]** `app/Livewire/Billing/BillingCrud.php`, `resources/views/livewire/billing/billing-crud.blade.php`, `resources/views/pages/billing/invoice-print.blade.php`, `app/Livewire/Masters/UserCrud.php`
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-15 (Billing — Ledger Modal Enhancement, Payment Duplicate Prevention, Revoke/Cancel Flow, Balance Fix)
- **[ENHANCEMENT]** Patient Ledger modal enlarged to `max-w-[95vw]` / `max-h-[95vh]`. Added patient demographics header section with responsive 6-column grid showing: Patient Code, DOB, Gender, Contact, Address, Latest Encounter (ER/Admission/OPD/OB with colored badge), Encounter Date, Physician, Room/Bed. Loads patient info + most recent encounter across all encounter types.
- **[ENHANCEMENT]** Added checkbox selection to ledger table rows with select-all header. Selected rows highlighted with amber background. Action bar appears when items selected with "Cancel Charges" and "Revoke Payments" buttons plus confirmation step.
- **[FEATURE]** `cancelSelectedEntries()` — Sets `history=0` on selected charge ledger entries. Deactivates linked `billing_payments` records. Recalculates all patient balances from scratch.
- **[FEATURE]** `revokeSelectedPayments()` — Sets `history=0` on selected payment ledger entries AND corresponding `billing_payments` record. Restores fund source budget via `restoreBudget()`. Recalculates all patient balances from scratch.
- **[BUGFIX]** Fixed "Record Payment" double-entry on double-click. Added duplicate OR/reference number validation in `addPayment()` — checks `billing_payments` for existing `reference_no` within same tenant before inserting. UI uses `wire:loading` + `wire:lock` to disable button during submission.
- **[BUGFIX]** `billing_payments.billing_invoice_id` changed from NOT NULL to nullable. Standalone payments (not linked to an invoice) now correctly insert without constraint violation. Migration `2026_07_15_000003`.
- **[BUGFIX]** Fixed patient outstanding balance incorrect after revoking payments. Root cause: `getLedger()`, `getPatientBalance()`, `recalculateBalance()`, and `getPatientAccountsProperty()` all used `withoutGlobalScopes()` without re-applying `history=1` filter, so revoked entries (history=0) were still counted in balance calculations.
  - `PatientAccountEntry::getLedger()` — added `->where('history', 1)` to exclude revoked entries from display.
  - `PatientAccountEntry::recalculateBalance()` — added `->where('history', 1)` to previous balance lookup and subsequent entries cascade, so revoked entries are skipped in the balance chain.
  - `PatientAccountEntry::getPatientBalance()` — added `->where('history', 1)` so outstanding balance reads from last active entry only.
  - `BillingCrud::getPatientAccountsProperty()` — added `->where('history', 1)` to patient accounts datatable query.
  - `BillingCrud::recalculatePatientBalance()` — rewrote to recalculate all active entries from scratch in order (not just the last entry), handling revoked entries anywhere in the chain.
  - Summary stats (totalCharges/totalPayments) — added `->where('history', 1)`.
- **[DOCS]** Updated `tables.md` — billing_payments `billing_invoice_id` now nullable, migrations list updated, patient_account_entries CRUD section updated with balance rules.
- **[DOCS]** Updated `unified_schema.sql` — `billing_payments` CREATE TABLE `billing_invoice_id` changed to NULL, added ALTER TABLE statement for idempotent migration.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-15 (Billing — Statement of Account Print 404 Fix)
- **[BUGFIX]** Fixed "Print Summary" and "Print Detailed" buttons on the Patient Ledger modal producing a 404 when clicked. The statement PDF modal appeared but the iframe showed a 404 / blank page.
  - **Root cause — Issue 1 (Primary): Livewire 3 event payload format mismatch.**
    `Livewire.on('openStatementPdf', (url) => { iframe.src = url })` — In Livewire 3, `$this->dispatch('eventName', key: value)` delivers the payload as a **named-parameter object** `{url: '...'}`, not a plain string. The old handler assigned the entire object to `iframe.src`, which the browser serialized as `[object Object]` — a URL that doesn't exist → 404.
  - **Root cause — Issue 2 (Secondary): Race condition between re-render and JS event.**
    `printStatement()` set `$showStatementModal = true` AND dispatched the JS event in the same Livewire cycle. The re-render inserts `<iframe src="about:blank">` into the DOM, but the `Livewire.on` event fires **before** the new DOM is painted, so `getElementById('statement-iframe')` returned `null` and the src was never set.
  - **Fix**: Eliminated the JS event dispatch entirely. Added `public string $statementUrl = ''` to `BillingCrud`. `printStatement()` now sets `$this->statementUrl = $url` as a Livewire property. The blade renders the iframe with `src="{{ $statementUrl }}"` — Livewire hydrates the correct URL server-side the moment the modal is shown, with no JS timing dependency.
  - **Secondary fix**: Updated the `Livewire.on('openStatementPdf', ...)` JS handler to correctly unwrap both Livewire 3 array and object payload formats as a safe fallback.
  - **Files modified**:
    - `app/Livewire/Billing/BillingCrud.php` — Added `$statementUrl` property; updated `printStatement()` to set it; updated `closeStatementModal()` to reset it; removed `dispatch('openStatementPdf', ...)` call.
    - `resources/views/livewire/billing/billing-crud.blade.php` — Changed `<iframe src="about:blank">` → `<iframe src="{{ $statementUrl }}"`; updated `Livewire.on` handler with Livewire 3-compatible payload unwrapping.
  - **Pattern note**: For PDF/print routes loaded into iframes from a Livewire component, always use a **Livewire public property bound directly to the blade `src` attribute** rather than a JS `dispatch` event. The session cookie is present on same-origin iframe loads so JWT auth via `session('jwt_token')` works correctly. The `?token=` query param fallback in `JwtMiddleware` is the safety net.
  - **Verification**: `php -l` passes on `BillingCrud.php`. Route `billing.statement` confirmed in route list with `permission:billing.view` middleware. `JwtMiddleware` reads token from session for same-origin requests.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-13 (OR Schedule — Treatment Button Not Working Fix)
- **[BUGFIX]** Fixed the "Treatment" button on the OR Schedule page (`/or/schedule`) appearing to do nothing when clicked. The button fired `openTreatment()` but the component re-render showed no change.
  - **Root cause**: The `OrScheduleCrud` component had a `public $activeSchedule = null;` property (line 90) declared alongside a computed property `getActiveScheduleProperty()`. In Livewire 3, the public property gets dehydrated/hydrated on every request cycle. During re-render, the public property's `null` value could conflict with the computed property or the local `$activeSchedule` variable passed via `compact()` in `render()`, preventing the treatment form from displaying. The working `DeliveryScheduleCrud` component does NOT have this public property — it relies solely on the computed property and the render-time local variable.
  - **Fix**: Removed `public $activeSchedule = null;` from `OrScheduleCrud`. The computed property `getActiveScheduleProperty()` and the local variable in `render()` (passed via `compact()`) are sufficient and match the working Delivery component pattern.
  - **File modified**: `app/Livewire/Or/OrScheduleCrud.php:90` — removed `public $activeSchedule = null;`
  - **Verification**: `php -l` passes. All clinical columns confirmed present in DB (migration `2026_06_26_000110` already ran). All 5 order relationships (Lab, CSR, Radiology, Pharmacy, Procedure) confirmed on the OrSchedule model. No errors in `laravel.log`.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (Delivery Schedule Treatment — Missing Lab/CSR/Radiology Order Tables)
- **[BUGFIX]** The delivery schedule treatment view only displayed Pharmacy and Procedure order tables, despite the PHP component loading all 5 order types (Lab, CSR, Radiology, Pharmacy, Procedure). Lab, CSR, and Radiology orders were created via modals but invisible in the treatment view.
  - **Root cause**: The Blade template was missing display tables for 3 of 5 order types. The data was loaded (`$labOrders`, `$csrOrders`, `$radiologyOrders`) but never rendered.
  - **Fix**: Added 3 missing order display tables following the existing Pharmacy/Procedure pattern:
    - **Lab Orders** (purple theme) — columns: Request #, Tests (`test_name`), Priority, Status, Billing, Total, Date, Actions. Status flow: Pending → Collected → Processing → Completed / Cancelled. Includes status-specific action dropdown per item.
    - **CSR/Supply Orders** (teal theme) — columns: Request #, Items (`item_name`, qty), Status, Billing, Total, Date, Actions. Status flow: Pending → Approved → Partial → Complete / Cancelled. Includes qty display and item-level status actions.
    - **Radiology Orders** (sky theme) — columns: Request #, Exams (`exam_name`), Priority, Status, Billing, Total, Date, Actions. Status flow: Pending → Approved → Partial → Complete / Cancelled. Includes priority badge and item-level status actions.
  - **File modified**: `resources/views/livewire/delivery/delivery-schedule-crud.blade.php` — inserted before Pharmacy Orders section.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (Claim Form — Patient Search Fix for Edit Mode)
- **[BUGFIX]** When editing a claim, the patient name was not displaying in the Claim Form's patient search field.
  - **Root cause**: `ClaimForm` is always mounted (hidden via CSS `hidden`). Alpine's `x-init="search = $wire.patient_search"` only runs once on first mount — when `patient_search` was still empty. When `loadClaim()` sets the property on a later edit, Alpine never re-reads it.
  - **Fix**: Added `wire:key="patient-search-{{ $editingId }}"` to force Alpine component destroy/recreate when the claim changes, triggering `x-init` to re-run. Wrapped the init in `$nextTick()` to ensure the Livewire DOM update completes before Alpine reads the property.
  - **File modified**: `resources/views/livewire/philhealth/claim-form.blade.php:10`
- **[BUGFIX]** Alpine expression error `$wire.__instance.get is not a function` on the claim form patient dropdown.
  - **Root cause**: `$wire.__instance.get('patients')` is a Livewire 2 API. Livewire 3 uses `$wire.patients` directly.
  - **Fix**: Replaced `$wire.__instance.get('patients')` with `$wire.patients`.
  - **File modified**: `resources/views/livewire/philhealth/claim-form.blade.php:15`
- **[ENHANCEMENT]** When editing a claim, `loadClaim()` now pre-populates the `$patients` collection with the existing patient so the dropdown stays populated. Same in `selectPatient()` after selection.
  - **File modified**: `app/Livewire/Philhealth/ClaimForm.php:213,386`
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (Triage PhilHealth Checkbox — Now Visible for Existing Patients)
- **[BUGFIX]** The PhilHealth Member checkbox in the ER Triage form was only visible when the "New Patient" toggle was ON (inside the `@if($is_new_patient)` block). Existing patients being triaged had no way to flag themselves as PhilHealth members, preventing draft eClaim auto-creation.
  - **Fix (Blade)**: Moved the PhilHealth checkbox out of the new-patient form into a shared section below the patient details conditional (`@if($patient_id || $is_new_patient)`). Now visible for both new patients being registered AND existing patients being triaged.
  - **Fix (PHP)**: Added `elseif` branch in `save()` for existing patients: when `is_philhealth_member` is toggled on, updates the patient's `is_philhealth_member` flag in the DB and calls `PhilhealthClaim::ensureDraftForPatient()` to create the draft eClaim.
  - **Files modified**: `resources/views/livewire/er/er-triage-record-crud.blade.php`, `app/Livewire/Er/ErTriageRecordCrud.php`
  - **Verification**: PHP syntax check passes.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (Bugfix — ClinicalDataPullUp null patientId 500 Error)
- **[BUGFIX]** Fixed `TypeError: ClinicalDataPullUp::openPullUp(): Argument #1 ($patientId) must be of type int, null given` (500) when opening a Draft claim from the claims list.
  - **Root cause**: `ClaimCrud::openEdit()` dispatched `open-pull-up` with `patientId: $claim?->patient_id`, which evaluates to `null` when the claim lookup fails or `patient_id` is null. The `openPullUp(int $patientId)` signature rejected the null argument.
  - **Fix**: Changed `ClinicalDataPullUp::openPullUp()` parameter from `int $patientId` to `?int $patientId = null` with early return guard. Also hardened the dispatch in `ClaimCrud::openEdit()` to only route to `open-pull-up` when `$claim?->patient_id` is truthy, otherwise falls back to `open-claim-form`.
  - **Files modified**: `app/Livewire/Philhealth/ClinicalDataPullUp.php:29`, `app/Livewire/Philhealth/ClaimCrud.php:71`
  - **Verification**: Both files pass `php -l` syntax check.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (Claims Draft Flow — Visual Badge, Smart Tab Default, Auto-Link ER Admission)
- **[UI]** Added "Needs Data" amber badge to Draft claims in the claims list that were auto-created from triage but have no clinical data yet (no `er_admission_id`, no `admission_id`, no `admitting_diagnosis`). Badge appears next to the status badge with tooltip: "Draft from triage — open and pull clinical data".
- **[UX]** When editing a Draft claim that lacks clinical data, the tabbed view now defaults to the **Clinical Pull-Up** tab instead of the Claim Form. This immediately shows the patient's ER/Ward/OPD encounters so staff can pull data first, then switch to the form to review/save. Non-draft or partially-filled claims still default to Claim Form.
- **[AUTO-LINK]** When a triaged patient is transferred to an ER bed (`saveTransfer()` in `ErTriageRecordCrud`), the system now auto-links the newly created `ErAdmission` to the patient's existing Draft `PhilhealthClaim`. Sets `er_admission_id`, `encounter_type=emergency`, and `date_admitted` — eliminating the need to manually pull ER data for that encounter.
- **Files modified**: `app/Livewire/Philhealth/ClaimCrud.php` (smart tab default in `openEdit()`), `resources/views/livewire/philhealth/claim-crud.blade.php` (Needs Data badge), `app/Livewire/Er/ErTriageRecordCrud.php` (auto-link in `saveTransfer()`)
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (Documentation Update — eecl_items Table Added to Schema Docs)
- **[DOCS]** Added the `eecl_items` table (PhilHealth-prescribed fixed fee schedule for eClaims) to both documentation files:
  - **`tables.md`** — Added as table #146 (Global reference, no `tenant_id`) in the PhilHealth Claims section. Columns: `item_code` (unique), `category`, `subcategory`, `item_name`, `description`, `unit`, `fixed_fee`, standard audit columns. Includes seeder note (145 items across 5 categories).
  - **`unified_schema.sql`** — Added T-SQL CREATE TABLE equivalent with `UQ_eecl_items_item_code` UNIQUE constraint, `IX_eecl_items_category` index, and standard audit indexes. Placed after `philhealth_members` and before `philhealth_case_rates`.
  - **Note**: `eecl_items` is global reference data (no `tenant_id`) — the fixed fee schedule is prescribed by PhilHealth and identical across all facilities.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (eClaims Redesign — Drawer Wizard → In-Place Tabbed Page)
- **[UI/ARCHITECTURE]** Replaced the right-sliding drawer wizard with an in-place tabbed page for the PhilHealth eClaims module. The 4-step wizard (Pull-Up → Form → Items → Submit) is now a flat tabbed layout where all tabs are freely accessible in any order.
  - **Navigation**: In-place content swap (same URL `/philhealth/claims`, no route change). Claims list → click "New Claim" or edit → switches `viewMode` from `list` to `entry` → shows tab bar + tab content in the main content panel.
  - **Tab bar**: Clinical Pull-Up, Claim Form, Claim Items, Submission — each tab button dispatches the corresponding event to its child component when clicked.
  - **Child components stay mounted**: CSS `hidden` class toggling (not `@if` conditional rendering) keeps all 4 children alive — switching tabs doesn't lose unsaved form data.
  - **Edit flow**: Same tabbed layout for both create and edit. `openEdit()` defaults to the Claim Form tab; `openSubmission()` defaults to the Submission tab.
  - **Back button**: "Back to Claims" button returns to list view and refreshes the claims list.
  - **Visual layout**: Back button + claim badge at top, tab bar below, active tab content fills the page width.
- **[REFACTOR]** Removed drawer/wizard infrastructure from all 10 files:
  - **ClaimCrud.php**: Replaced `$drawerOpen`, `$currentStep`, `$drawerClaimId`, `$drawerMode` with `$viewMode` ('list'|'entry'), `$activeTab` ('pullup'|'form'|'items'|'submission'), `$entryClaimId`, `$entryMode` ('create'|'edit'). Replaced `goToStep()`, `nextStep()`, `prevStep()`, `activateCurrentStep()`, `closeDrawer()` with `switchTab(string $tab)`, `backToList()`.
  - **claim-crud.blade.php**: Removed drawer overlay (`z-50`, `min(55vw, 960px)`), backdrop, slide-in animation, step indicator, footer navigation, `<style>` block. Added `@if($viewMode === 'list')` / `@else` structure with tab bar.
  - **ClinicalDataPullUp.php, ClaimForm.php, ClaimItems.php, ClaimSubmission.php**: Removed `$renderMode` property and `mount()` parameter. Removed `closeDrawer()` methods. Simplified `close()` methods.
  - **clinical-data-pull-up.blade.php, claim-form.blade.php, claim-items.blade.php, claim-submission.blade.php**: Removed `@if($renderMode === 'drawer')` / `@else` dual rendering. Removed legacy modal blocks. Kept only the drawer-mode content (content-only, no wrapper). Added empty-state fallbacks when `$showModal` is false.
  - **Files modified**: `app/Livewire/Philhealth/ClaimCrud.php`, `resources/views/livewire/philhealth/claim-crud.blade.php`, `app/Livewire/Philhealth/ClinicalDataPullUp.php`, `resources/views/livewire/philhealth/clinical-data-pull-up.blade.php`, `app/Livewire/Philhealth/ClaimForm.php`, `resources/views/livewire/philhealth/claim-form.blade.php`, `app/Livewire/Philhealth/ClaimItems.php`, `resources/views/livewire/philhealth/claim-items.blade.php`, `app/Livewire/Philhealth/ClaimSubmission.php`, `resources/views/livewire/philhealth/claim-submission.blade.php`.
  - **Event flow preserved unchanged**: `data-pulled`, `items-imported`, `claim-saved`, `claim-status-changed` events still work the same way.
  - **Verification**: All 5 PHP files pass `php -l` syntax check.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (Sidebar Icon Coloring — Section-Accent Colors)
- **[UI]** Replaced plain gray icons on the sidebar navigation with each section's accent color, making the sidebar visually scannable and more polished.
  - **Parent button icons**: Changed `text-gray-400` → `text-{color}-400` (e.g., OPD indigo, ER rose, Lab amber, Pharmacy emerald, PhilHealth blue).
  - **Flat link icons (inactive)**: Changed `text-gray-400` → `text-{color}-400` (e.g., Dashboard light-blue, Doctors Portal emerald, Nursing emerald, Medical Records violet).
  - **Child submenu links**: Changed `text-gray-600` (inherited from `<a>` tag) → explicit `text-{color}-400` on the `<a>` tag, so both text and SVG icons inherit the section color via `currentColor`.
  - **Section header labels** ("Main", "Clinical Modules", etc.) remain `text-gray-400` — they are category labels, not interactive items.
  - **Color palette**: light-blue (Dashboard, Patients, Transactions, Reports, Role Permissions), emerald (Doctors Portal, Nursing, Pharmacy, Billing, YAKAP), violet (Medical Records), indigo (OPD), rose (ER, Delivery), teal (Admission & Wards, Konsulta), pink (OB), slate (OR, Masters), cyan (Renal Care), amber (Laboratory, Roles, Audit Trail), purple (Radiology), sky (Procedures), orange (CSR/Supply), blue (PhilHealth).
  - **Hover states unchanged**: Parent buttons still use `group-hover:text-{color}-500`, child links still use `hover:text-{color}-700` — only the default/inactive state changed from gray to the accent color.
  - 26 `replaceAll` edits total (14 for parent buttons + flat links, 12 for child submenu items).
  - **File modified**: `resources/views/components/layouts/app.blade.php`
  - **Verification**: Blade syntax check passes, zero `text-gray-600` remaining on interactive elements, only 5 `text-gray-400` instances left (all on section header `<p>` labels).
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (EECL System — Full Implementation for OECB Claims)
- **[ARCHITECTURE]** Implemented the PhilHealth EECL (Essential Emergency Care List) system for OECB claims, covering database, models, seeding, printable forms, XML generation, and UI integration.
  - **Context**: PhilHealth Circular No. 2024-0033 requires an EECL Summary Form (Annex D) as attachment for all OECB-FBE claims. The EECL lists fixed-fee diagnostic services, ER services, medicines, and supplies that can be billed under Package Code `OPER1`.
  - **New Files Created**:
    - `database/migrations/2026_07_12_000010_create_eecl_items_table.php` — Creates `eecl_items` table with columns: `id`, `item_code` (unique), `category`, `subcategory`, `item_name`, `description`, `unit`, `fixed_fee`, `is_active`, `history`, audit columns.
    - `database/migrations/2026_07_12_000011_add_eecl_item_id_to_oecb_entries.php` — Adds `eecl_item_id` (FK→eecl_items), `quantity`, `total_amount` to `oecb_entries` table.
    - `app/Models/Oecb/EeclItem.php` — Model with `$table = 'eecl_items'`, `$guarded = []`, global scope `where('history', 1)`, `fixed_fee` cast to `decimal:2`. Has `oecbEntries()` HasMany relationship.
    - `database/seeders/EeclSeeder.php` — Seeds 145 EECL items across categories: Diagnostic Services (lab tests: CBC, urinalysis, blood chemistry, coagulation, cardiac markers, special tests), Imaging Services (X-ray, ultrasound, CT scan, MRI), ER Services (consultation, bed, monitoring, procedures like intubation/CPR/suturing), Medicines (analgesics, antibiotics, cardiovascular, respiratory, emergency, anesthesia, obstetric, electrolytes, GI), Supplies (IV, surgical, airway, monitoring, general). Each item has a PhilHealth-prescribed fixed fee in Philippine Peso.
    - `resources/views/pages/philhealth/eeclpdf.blade.php` — EECL Summary Form (Annex D) PDF template (claim-based). 7-part structure: Part I (HCI & Patient Info), Part II (ER Encounter Details with admission code, date/time, attending physician, disposition, triage level), Part III (Core Presentation & Diagnosis with ICD-10, package code OPER1), Part IV (Clinical Information: HPI, physical exam, vitals), Part V (OECB Fee Summary table from oecb_entries), Part VI (Services/Medicines/Supplies table from claim_items), Part VII (Certification & Attestation with signature lines). Uses consistent styling with CF1-CF5 templates (Courier New, 8.5pt, 16mm margins, bordered sections).
    - `resources/views/pages/er/eeclpdf.blade.php` — EECL Summary Form (Annex D) PDF template (ER admission-based). Same 7-part structure but works directly from `$admission` (ErAdmission model) without requiring a PhilHealth claim. Enables printing EECL immediately from ER treatment form before claim creation.
  - **Files Modified**:
    - `app/Models/Oecb/OecbEntry.php` — Added `eeclItem()` BelongsTo relationship, added `quantity` and `total_amount` to `$casts`.
    - `app/Services/Philhealth/XmlBuilder.php` — Added OECB PACKAGE element generation: when `claim_type === 'OECB'` and no `caseRate`, emits `<PACKAGE pPackageRVS="OPER1" pPackageName="OECB-FBE" pPackageAmount="...">` using sum of `oecb_entries.total_amount` or `oecb_fixed_fee`.
    - `app/Services/Philhealth/OecbClaimFormatter.php` — Updated `format()` to append triage level in brackets (e.g., "Declared Complaint Difficulty of Breathing [Emergent]"). Updated `findOecbAdmission()` to eager-load `triageRecord.acuityLevel`.
    - `app/Livewire/Philhealth/ClaimSubmission.php` — Added `previewEecl()` method. Updated `loadPreview()` to also eager-load `erAdmission.triageRecord.acuityLevel` and `erAdmission.oecbEntries`.
    - `resources/views/livewire/philhealth/claim-submission.blade.php` — Added EECL PDF download link (teal color, "Annex D — OECB Summary") to PDF Forms tab in both drawer and modal modes.
    - `resources/views/livewire/er/er-admission-crud.blade.php` — Added "Print EECL (Annex D)" teal button in OECB section, visible when `$treatingId` is set and OECB entries exist. Links to `route('er.admission.eeclpdf', $treatingId)` as direct PDF download (opens in new tab).
    - `routes/web.php` — Added two EECL routes: `er.admission.eeclpdf` (ER admission-based, uses `$admission`) and `philhealth.claims.eeclpdf` (claim-based, uses `$claim`). Both return DomPDF download.
    - `database/seeders/DatabaseSeeder.php` — Added `EeclSeeder::class` after `OecbSeeder::class`.
  - **Key Architecture Decisions**:
    - EECL items are global reference data (no `tenant_id`) — the fixed fee schedule is prescribed by PhilHealth and identical for all hospitals.
    - `oecb_entries` now links to `eecl_items` via `eecl_item_id` FK, with `quantity` and `total_amount` columns for multi-quantity billing.
    - Two EECL PDF templates exist: one for claim context (`pages/philhealth/eeclpdf.blade.php` using `$claim`), one for ER admission context (`pages/er/eeclpdf.blade.php` using `$admission`).
    - The `eeclpdf.blade.php` templates follow the exact same styling conventions as CF1-CF5 templates (Courier New, bordered sections, monospace peso amounts).
    - Package Code `OPER1` is hardcoded in both the XML builder and the EECL Summary Form per PhilHealth circular.
    - ER print button uses `<a>` tag (not Livewire action) because it's a direct PDF download route.
  - **Consistency**: EECL entries flow: ER Admission (is_oecb=true, addOecbEntry) → oecb_entries (linked to eecl_items) → Claim (claim_type=OECB) → XML (PACKAGE pPackageRVS=OPER1) + EECL Summary Form PDF.
  - **Database Status**: Both migrations ran successfully (eecl_items table created, oecb_entries altered). EeclSeeder seeded 145 items.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (OB Consultation → Delivery Room Schedule Migration)
- **[ARCHITECTURE]** Converted the OB Consultation module's OR Schedule functionality to use the Delivery Room (DR) system instead.
  - **Root cause**: `ObConsultationCrud` was creating `OrSchedule` records (operating room) when OB treatments should route through `DeliverySchedule` (delivery room). The `dr_schedules` table already had `ob_consultation_id` FK and OB-specific fields (gestational age, gravida, para, LMP, EDC, delivery type, neonatal outcome) that `or_schedules` lacked.
  - **Files modified**:
    - `app/Livewire/Ob/ObConsultationCrud.php` — Replaced `OrSchedule`/`OrRoom` imports with `DeliverySchedule`/`DeliveryRoom`/`DeliveryType`/`DeliveryComplication`. Renamed all form properties (`or_room_id` → `dr_room_id`, `or_procedure_type` → `delivery_type`, etc). Renamed methods (`openOrScheduleForm` → `openDrScheduleForm`, `saveOrSchedule` → `saveDrSchedule`). `saveDrSchedule()` now creates `DeliverySchedule` records with OB history auto-populated from the consultation (gestational age, LMP, EDC, gravida, para, GPA). New fields: `dr_pediatrician_id`, `dr_midwife_id`. `render()` loads `$drSchedules`, `$drRooms`, `$doctors`, `$deliveryTypes`, `$deliveryComplications`. Delivery type dropdown: NSVD, C/S, AVB, Breech, Vacuum, Forceps, Others.
    - `resources/views/livewire/ob/ob-consultation-crud.blade.php` — Tab button: "OR Schedule" → "Delivery Room". Tab content header: "DR Schedule". Button: "Schedule Delivery". Table: "OR Room" → "DR Room", "Surgeon" → "Physician". Modal: "Schedule Delivery" with delivery type dropdown, attending physician, anesthesiologist, pediatrician, midwife selects. All `wire:click` handlers updated to DR methods.
  - **Consistency**: DR schedules created from OB appear in `/delivery/schedule` (standalone DR page) because `DeliveryScheduleCrud::render()` queries all `dr_schedules` by `tenant_id` with no source filter.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (eClaims SPA Drawer — All 4 Child Components Drawer Mode)
- **[UI]** Completed drawer mode conversion for all 4 eClaims child components, enabling SPA-style right-side drawer navigation in the claims workflow.
  - `resources/views/livewire/philhealth/clinical-data-pull-up.blade.php` — Added `$renderMode === 'drawer'` conditional. Drawer mode renders ER/Ward/OPD pull-up cards as inline content (no modal wrapper, no backdrop). Legacy modal mode preserved in `@else` block.
  - `resources/views/livewire/philhealth/claim-items.blade.php` — Added drawer mode conditional. Drawer mode renders items table + Add Item button as inline content. Legacy modal preserved.
  - `resources/views/livewire/philhealth/claim-submission.blade.php` — Added drawer mode conditional. **Added CF5 and eSOA PDF download buttons** to the PDF Forms tab (previously only CF1-CF4). CF5 button links to `route('philhealth.claims.cf5pdf', $claimId)`, eSOA links to `route('philhealth.claims.esoapdf', $claimId)`. Both use `<a target="_blank">` for direct DomPDF download. Conditionally shown with `@if($claimId)`.
  - All 3 files follow the same pattern: `@if($renderMode === 'drawer') @if($showModal) ... content only ... @endif @else ... full modal with fixed inset-0 wrapper ... @endif`
  - All 5 PHP components pass `php -l` syntax check.
- **[FILES MODIFIED]** `clinical-data-pull-up.blade.php`, `claim-items.blade.php`, `claim-submission.blade.php`
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-12 (eClaims Module Complete Rebuild — Monolith to 5 Livewire Components)
- **[ARCHITECTURE]** Rebuilt the PhilHealth eClaims module from a monolithic 973-line `ClaimCrud` component into **5 focused Livewire components** following a Data Entry + Data Pull-Up workflow.
  - **ClaimCrud** (orchestrator, ~160 lines): Claims list table, search, sort, pagination. Dispatches events to child components. Embeds children via `<livewire:philhealth.xxx />`.
  - **ClaimForm** (~350 lines): CF1-CF4 data entry form — patient/member, admission dates, diagnoses, clinical data, OB/maternity, outcome/status, Z-Benefits. Listens for `data-pulled` events from ClinicalDataPullUp.
  - **ClinicalDataPullUp** (~180 lines): Read-only encounter browser for ER/Ward/OPD. Shows encounter cards with Pull Data and Import Billing buttons. Dispatches `data-pulled` and `items-imported` events.
  - **ClaimItems** (~250 lines): Dynamic line item management — inline editable table for drugs, labs, procedures, supplies, room & board. Handles `claim_procedures` sync for PROCEDURE XML elements.
  - **ClaimSubmission** (~300 lines): Tabbed panel (Submission, Status, Rejection, CF5/DRG, PDFs) for all external service interactions — CEWS, ECS, Cloud, Ack, Voucher, Deny, Rejection, Resubmit, Status Poll, QR Code, CF1-4 PDF previews.
- **[DATABASE]** 4 migrations created:
  - `2026_07_12_000001` — Merge `icd10_codes` into `icd_10_codes`, add `category` column, drop old table
  - `2026_07_12_000002` — Add missing columns to `philhealth_claims` (`patient_is`, `membership_type`, `encounter_type`, `disposition_code`, `tracking_number`, `pension_type`, `gender`, `second_case_rate_id`)
  - `2026_07_12_000003` — Create `claim_procedures` table (separate from `claim_items`)
  - `2026_07_12_000004` — Add `rvs_code_id` and `icd10_code_id` FKs to `philhealth_case_rates`, backfill from text columns
- **[SEEDER]** `PhilhealthReferenceSeeder` — Seeds 50 common RVS codes + 31 case rates with HF/PF fees, linking to `icd_10_codes` via FK.
- **[TRAITS]** Extracted shared `SoapClient` trait (`app/Traits/SoapClient.php`) with `buildSoapEnvelope()`, `callSoapEndpoint()`, `parseSoapBody()`. Applied to `EclaimsService`, `EcsService`, `CloudStorageService`, `ClaimStatusPollingService`.
- **[XML BUILDER]** Fixed XmlBuilder: dates now `m-d-Y`/`h:i:sA`, added MEMBER/PATIENT child elements, PROCEDURE element generation from `claim_procedures`, SecondCaseRate support, ICD-10 dot stripping, `mapMembershipType()`/`mapClaimType()`.
- **[MODELS]** Updated `PhilhealthClaim` (added `secondCaseRate()`, `claimProcedures()`), `PhilhealthCaseRate` (added `rvsCode()`, `icd10Code()`, `claims()`, `secondaryClaims()`), `PhilhealthMember` (added `$fillable`, `claims()`, `patients()`, `full_name` accessor). Created `ClaimProcedure` model.
- **[CONFIG]** Added `'drg' => ['base_rate' => env('PHILHEALTH_DRG_BASE_RATE', 35000)]` to `config/philhealth.php`.
- **[FILES CREATED]** 10 new files: 4 components, 4 views, 1 trait, 1 seeder.
- **[FILES REFACTORED]** `ClaimCrud.php` (973→160 lines), `claim-crud.blade.php` (1000+→110 lines), `XmlBuilder.php`, 4 service files.
- **[FILES UPDATED]** `PhilhealthClaim.php`, `PhilhealthCaseRate.php`, `PhilhealthMember.php`, `config/philhealth.php`, `DatabaseSeeder.php`.
- **[DOCS]** Created `eclaimsrebuild.md` (720 lines) with full architecture spec. Updated `ragdata.md` (this entry).
- **[PENDING]** `php artisan migrate --force` + `db:seed --force` to apply to database.

### 2026-07-12 (Login Page — Premium Split-Panel Redesign, Light Blue Theme)
- **[UI REDESIGN]** Completely overhauled the login page from a simple centered card to a full-screen **split-panel layout** with premium design treatment.
  - **Left Panel (Branding)** — Deep sky-to-ocean blue gradient (`#0369a1 → #0ea5e9 → #38bdf8`), animated background orbs, floating particles, CSS grid overlay, SITHEMR branding with medical cross icon, headline with feature list (PhilHealth eClaims, ER Workflow, Billing), and bottom stats bar (15+ Modules, CF1–5, OECB).
  - **Right Panel (Form)** — Light blue glassmorphism card (`rgba(255,255,255,0.85)` with `backdrop-filter: blur(16px)`) on a `#f0f9ff → #e0f2fe` gradient background. Frosted white card with sky-blue input borders (`#bae6fd`), glowing focus ring (`rgba(14,165,233,0.15)`), centered logo icon, uppercase tracked field labels in `#0369a1`.
  - **Animations**: `pulse-slow` for background orbs, `float` for particles, `ping-slow` for the live badge dot, `shimmer` for the Sign In button's sliding sheen effect.
  - **Sign In button**: Gradient from `#0ea5e9 → #0284c7 → #0369a1`, `box-shadow: 0 4px 20px rgba(14,165,233,0.4)`, lifts `translateY(-1px)` on hover with stronger glow.
  - **Password toggle**: Eye/eye-off SVG icons in `#7dd3fc`, hover brightens to `#0ea5e9`.
  - **Error states**: Red-tinted banners (`#fef2f2 / #fecaca`) for both session flash errors and Livewire `$error` property.
  - **Security note**: Shield-check icon + "256-bit encrypted · Secure session · Multi-tenant isolated" caption below form.
  - **Mobile responsive**: Left branding panel hidden on `< lg` breakpoints; mobile header shows logo + name inline above the form.
- **[LAYOUT]** Updated `guest.blade.php`:
  - Added Google Fonts **Inter** (weights 300–800) via `<link>` preconnect — consistent with rest of the application.
  - Body `font-family` overridden to `'Inter'` via inline `<style>` tag.
  - Body background changed from `bg-gradient-to-br from-light-blue-50 via-white to-light-blue-100` to bare `min-h-screen` with inline `background: #f0f9ff` (matches right panel gradient start color for seamless blending on mobile).
- **[FILES MODIFIED]** `resources/views/livewire/auth/login.blade.php`, `resources/views/components/layouts/guest.blade.php`
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-11 (Billing — Pre-Invoice Manual Charges (DB-Backed), Patient Auto-Populate Fix, Invoice Print Modes)
- **[FEATURE]** Added pre-invoice manual charge support to the Billing For Billing tab — staff can now add estimated/standalone charges (e.g., wheelchair rental, transport fees, room differential) to a patient *before* an invoice is created.
  - **DB-backed storage**: All manual charges are stored in the `billing_manual_charges` table with `billing_invoice_id = NULL` for staging. Two migrations enabled this:
    - `2026_07_11_152549_make_billing_invoice_id_nullable_on_billing_manual_charges_table.php` — drops FK, alters `billing_invoice_id` to nullable via raw `DB::statement`, re-adds FK
    - `2026_07_11_152803_add_patient_id_to_billing_manual_charges_table.php` — adds `patient_id` column + indexes via raw `DB::statement`
  - **Removed**: `$patientManualCharges` in-memory array — all charges persist in the database, surviving page refreshes and logouts. Modal refresh issue eliminated.
  - **Private helper**: `stagingManualCharges()` queries `billing_manual_charges WHERE billing_invoice_id IS NULL AND history=1`, grouped by `patient_id`
  - **Methods**:
    - `openManualChargeForPatient(int $patientId)` — opens the manual charge form with patient context
    - `addManualChargeForPatient()` — saves to DB with `billing_invoice_id=null, patient_id=<id>`
    - `removeManualChargeForPatient(int $patientId, int $index)` — soft-deletes by setting `history=0`
    - `closePatientManualChargeForm()` — closes form and resets state
  - **`getUnbilledPatientsProperty()`** — queries `stagingManualCharges()` instead of in-memory array; includes patients with *only* manual charges
  - **`refreshViewingCharges()`** — queries DB for manual charges with `patient_id` filter
  - **`computeDiscount()`** — queries DB for staging charges total in discount base
  - **`createInvoice()`** — after creating invoice items, queries staging charges for the patient and links them to the new invoice by updating `billing_invoice_id` (no duplicate records created); calls `recalculateInvoiceTotals()`
  - **`render()`** — `$totalUnbilled` summary card sums staging charges directly from DB
- **[UI]** Tab 1 patient rows now show:
  - "Add Charge" button (sky blue, `+` icon) per patient row
  - Manual charge count badge below patient name (e.g., "+2 manual charge(s)")
  - View Charges modal now has a 6th "actions" column with remove buttons for manual charges
  - View Charges modal footer includes "Add Manual Charge" button alongside Print and Create Invoice & Pay
  - Pre-invoice manual charge modal (sky-themed) with: charge type dropdown, description, quantity, unit price, notes, live total preview
  - Create Invoice modal's "Selected Items" section now shows manual charges alongside service requests with combined total
- **[FIX]** Fixed "Create Invoice + Pay" not auto-populating patient in the invoice modal.
  - **Root cause**: `payPatientCharges()` set `patient_id`, `patient_search`, encounter FKs, and `selectedServiceItems`, then called `openCreateInvoice()` which called `resetInvoiceForm()` — wiping all the data just set.
  - **Fix**: `payPatientCharges()` now directly calls `resetInvoiceForm()` first, then sets all fields (using `skipPatientSearchUpdate` flag), generates the invoice code, and opens the modal — bypassing `openCreateInvoice()` entirely.
  - Also updated the validation to allow creating an invoice with *only* manual charges (no service requests required).
- **[FEATURE]** Added print mode selector to the invoice print page (`invoice-print.blade.php`) — users can now choose between **Summary** and **Detail** views before printing.
  - **Mode selector**: No-print toggle bar at top of invoice with "Summary" and "Detail" tabs. Active mode highlighted in emerald. Switching modes reloads the page with `?mode=summary` or `?mode=detail` query parameter. Default is `detail`.
  - **Detail mode**: Groups all line items (invoice items + manual charges) by `source_type` with a colored group header badge showing item count. Each item shown with description, request code, qty, unit price, total. Group subtotal row after each group.
  - **Summary mode**: Groups by `source_type` showing only the source badge, type name, item count, and group total. Grand total row at bottom with total item count.
  - **Group ordering**: Lab, Pharmacy, CSR, Radiology, Procedure, OECB, Admission, then manual charge types (Miscellaneous, Administrative, Supply, Professional Fee, Transport, Equipment, Room Differential, Others).
  - Manual charges are merged into the items collection before grouping, so they appear alongside service items in both modes.
  - CSS additions: `.group-header`, `.group-subtotal`, `.summary-row`, `.summary-total`, `.mode-selector`, `.mode-label` styles for the new layouts.
- **[FILES MODIFIED]** `app/Livewire/Billing/BillingCrud.php`, `resources/views/livewire/billing/billing-crud.blade.php`, `resources/views/pages/billing/invoice-print.blade.php`
- **[MIGRATIONS]** `2026_07_11_152549_make_billing_invoice_id_nullable_on_billing_manual_charges_table.php`, `2026_07_11_152803_add_patient_id_to_billing_manual_charges_table.php`
- **[SEEDER CLEANUP]** Stripped all transactional demo data from `DemoDataSeeder.php` (1925 → 1283 lines, 642 lines removed). The seeder now only seeds reference/master data — zero dispensing or billing demo records. Removed sections:
  - Radiology Requests (5 demo requests with 9 exam items)
  - Purchase Orders (3 demo POs with items)
  - Stock Adjustments (3 demo adjustments)
  - Procedure Requests (3 demo requests with items)
  - Billing Demo Data (1 invoice, invoice items, partial payment)
  - OECB Demo Data (1 ER-OECB-00001 admission with 2 OECB entries)
  - Nursing Demo Data (1 vital sign record, 1 care note)
  - Also removed 9 unused model imports (`CsrRequest`, `CsrRequestItem`, `RadiologyRequest`, `RadiologyRequestItem`, `PurchaseOrder`, `PurchaseOrderItem`, `StockAdjustment`, `ProcedureRequest`, `ProcedureRequestItem`)
  - Lab test prices were added to all 27 tests (e.g., CBC ₱350, FBS ₱180, Dengue NS1 ₱500) and saved via `standard_charge` — these are reference data and were kept.
  - All reference data sections preserved: departments, ICU wards/beds, procedures, radiology modalities/body parts/exam types, MICU parameters, delivery types/complications, ward rooms, OPD types/services/clinics/complaints, OB types, lab sections/units/specimens/tests, supply units/categories/suppliers/locations/items, drugs/batches, fund sources.
  - Verified: `php artisan clinical:clear --force` → `php artisan db:seed --force` — all seeders pass, 10 patients seeded, zero transactional records.
- **[DOCS]** Updated `ragdata.md` (this entry), `tables.md` (Table 94 — billing_manual_charges), `unified_schema.sql` (billing_manual_charges fixes).

### 2026-07-06 (OECB Module Refactor — Multi-Entry Support via `oecb_entries` Table)
- **[REFACTOR]** Replaced the single scalar OECB columns (`core_presentation`, `oecb_package_code`, `oecb_fixed_fee`) on `er_admissions` with a one-to-many `oecb_entries` table, enabling multiple OECB entries per ER admission (like Lab/Pharmacy line items).
  - **Migration** `2026_07_06_021512_create_oecb_entries_table.php` ✅ — Creates `oecb_entries` table with `tenant_id`, `er_admission_id` (FK CASCADE), `core_presentation`, `oecb_package_code`, `oecb_fixed_fee`, `billing_id`, `billing_status`, audit columns. Drops old scalar columns from `er_admissions`.
  - **Migration** `2026_07_06_021515_seed_oecb_entries_from_er_admissions.php` ❌ **Deleted** — Seed tried to read `core_presentation` from `er_admissions` but `021512` had already dropped those columns. No legacy data to migrate.
  - **New Model** `App\Models\Oecb\OecbEntry` — BelongsTo `ErAdmission`
  - **Updated** `ErAdmission` — added `oecbEntries()` HasMany, removed old OECB casts
  - **Updated** `ErAdmissionCrud` — replaced scalar `$core_presentation`/`$oecb_package_code`/`$oecb_fixed_fee` with `$oecbEntries[]` array, added `addOecbEntry()`/`removeOecbEntry()`/`updatedOecbEntries()`/`syncOecbEntries()` methods
  - **Updated** `er-admission-crud.blade.php` — replaced single toggle+dropdown with dynamic add/remove entry list (same pattern as Lab/Pharmacy order items)
  - **Updated** `BillingCrud` — iterates `oecbEntries` for unbilled requests; invoice items use `update_table='oecb_entries'`; restore table changed from `er_admissions` to `oecb_entries`
  - **Updated** `ClaimCrud` + `claim-crud.blade.php` — loads `oecbEntries` relation, displays all entries with summed totals
  - **Updated** `MedicalRecordsCrud` — shows all OECB entries in encounter detail
  - **Updated** `OecbClaimFormatter` — lists all core presentations separated by comma
  - **Updated** `BillingAutoInvoice` — iterates `oecbEntries` per admission, uses `update_table='oecb_entries'`
  - **Updated** `DemoDataSeeder` — demo admission now creates 2 OECB entries (Fever ₱5,500 + Severe Headache ₱7,500)
  - **Docs**: Updated `ragdata.md` (this entry), `tables.md` (added table 105 `oecb_entries`, removed old scalar columns from table 62, updated relationship/migration references)

### 2026-07-06 (ER Area 500 — Invalid object name `oecb_entries`)
- **[BUGFIX]** Fixed `Invalid object name 'oecb_entries'` (SQLSTATE 42S02) on `/er/area` when `openTreatment()` tried to eager-load `oecbEntries` relationship.
  - **Root Cause**: Two issues blocked the migration:
    1. **Stale migration** `2026_07_06_021509_create_oecb_entries_table.php` was auto-generated with only `$table->id()` + `$table->timestamps()` — a stub with no real columns. This ran *before* the real migration `021512`, creating a minimal table that the proper migration could not overwrite.
    2. **Seed migration** `2026_07_06_021515_seed_oecb_entries_from_er_admissions.php` queried `core_presentation` from `er_admissions`, but `021512` drops those columns in the same batch — the column was already gone by the time the seed ran.
  - **Fix**:
    - Deleted stale migration `021509_create_oecb_entries_table.php` (auto-generated stub)
    - Deleted seed migration `021515_seed_oecb_entries_from_er_admissions.php` (no legacy data to migrate, ordering impossible to fix without re-timestamping)
    - Ran `php artisan migrate` — `oecb_entries` table created successfully
  - **Verification**: `/er/area` loads at HTTP 200 with no console errors.

### 2026-07-02 (OB Consultation Module — Code Generator Unique Constraint Fix)
- **[BUGFIX]** Fixed `UniqueConstraintViolationException` (Duplicate `RR-00001`, `LR-00001`, etc.) during order creation in the OB Consultation module (`app/Livewire/Ob/ObConsultationCrud.php`).
  - **Root Cause**: The module previously generated request codes using a brittle `substr($lastCode, 4) + 1` logic. If another module generated codes with different formats (e.g., `RAD-00004` from OPD or `DS-00001` from Renal Care), `substr` would parse non-numeric strings, resulting in code generation collisions and database insertion failures.
  - **Resolution**: Replaced the code generation logic across all request types (Lab, CSR, Radiology, Pharmacy, Procedures, OR Schedule) in `ObConsultationCrud.php` to use a robust `max('id') + 1` approach (e.g., `(RadiologyRequest::withoutGlobalScopes()->where('tenant_id', $tid)->max('id') ?? 0) + 1`), ensuring unique sequences immune to string formatting conflicts.
- **[DOCS]** Updated `ragdata.md` (this entry) and updated `e2ephase2.md` to mark OPD Clinical Encounters and OB/GYN Consultations test cases as Passed (✅).

### 2026-07-01 (Lab Dispensing — Syntax Error Fix)
- **[BUGFIX]** Fixed a PHP Parse Error (500 Internal Server Error) in `app/Livewire/Lab/LabDispensingCrud.php`. A previous addition of `updateBillingStatus()` had accidentally overwritten the top portion of the `render()` method, leaving trailing query builder methods orphaned. Restored the complete `render()` method to correctly fetch and pass `items`, `departments`, `doctors`, and `patients` variables to the view.

### 2026-07-01 (Data Isolation Reconciliation & UI Fixes)
- **[BUGFIX]** Diagnosed patient search failure: `admin@demohospital.com` was assigned to `tenant_id = 2`, but an earlier data migration script moved all patients to `tenant_id = 1`, causing the tenant isolation scope to naturally hide all records.
- **[DATA]** Cleared all patient clinical data (`php artisan clinical:clear --force`), re-seeded 10 demo patients (`PatientSeeder`), and dynamically updated `DemoHospitalSeeder` to assign ER Zones, ER Beds, Wards, and Pharmacy Drugs to the active `DEMO01` tenant.
- **[DATA]** Migrated the `admin@demohospital.com` user and all new patient records to `tenant_id = 1` to perfectly align with the existing reference data (preventing duplicate key constraint errors on ER Zones).
- **[UI FIX]** Fixed invisible Action buttons in ER Zones (`er-zone-crud.blade.php`) and ER Beds (`er-bed-crud.blade.php`) by replacing legacy FontAwesome icons (`fas fa-edit`, `fas fa-trash`, `fas fa-plus`) with modern SVG Heroicons, matching the rest of the application.

### 2026-07-01 (Doctors Portal Premium UI Redesign)
- **[FEATURE]** Completely overhauled the UI/UX for the Doctors Portal (`dashboard-crud.blade.php` and `patient-chart-crud.blade.php`) using modern web design principles (glassmorphism, deep gradients, animated hovering interactions).
- **[UI FIX]** Dashboard: Upgraded the header with a deep indigo gradient and glassmorphism search bar. Segmented controls (OPD/ER/Ward tabs) now feature pill-style floating designs with department-specific colors (Indigo, Rose, Emerald). Replaced standard data tables with elevated, shadow-hovering card rows and pulsing status indicators.
- **[UI FIX]** Patient Chart: Built a premium patient summary header with floating data pills. Redesigned the SOAP notes form with elevated inputs, clear S-O-A-P icon badges, and animated gradient dividers.
- **[UI FIX]** Orders & Results: Implemented a stunning glassmorphism modal (`backdrop-blur-2xl`) for new clinical orders. Rebuilt Lab and Radiology results into standalone cards with distinct background colors for abnormal/critical flags and smooth image thumbnail hover zoom animations.
- **[UI FIX]** Medical History: Converted the past encounters list into a beautiful vertical timeline with color-coordinated nodes and expandable hover effects.


### 2026-07-01 (ER Triage — Patient Search Fix + Modal Dropdown Clipping Fix)
- **[BUGFIX]** Added `middle_name` and `maiden_name` to ER Triage patient search query (`ErTriageRecordCrud.php:369-374`) — previously only searched `last_name`, `first_name`, and `patient_code`, causing patients stored under middle/maiden name (like "Penduko") to never appear.
- **[UI FIX]** Fixed ER Triage modal dropdown clipping — moved the entire patient search input + dropdown OUTSIDE the `overflow-y-auto` scrollable container. The search section now sits between the error summary and the scrollable body in a non-clipping `div`. The dropdown uses `absolute z-50` positioning in a parent with no overflow restrictions, so results are fully visible regardless of modal height or scroll position.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-01 (Billing — Print Charges Fix)
- **[UI]** Rewrote Print button for View Charges modal. Previous approaches (CSS `@media print` on in-page modal, `@json` in `<script>` tag with `window.open`) either showed blank pages or caused JS syntax errors. Current approach:
  - Charges data stored in a hidden `<pre id="charges-print-json">` tag with `data-patient` attribute — avoids `@json` inside `<script>` context
  - `printCharges()` JavaScript function reads JSON via `JSON.parse(el.textContent)`, builds a clean "STATEMENT OF CHARGES" report, opens a new window via `window.open('', '_blank')` (safe from user onclick gesture), auto-prints, and closes
  - String concatenation used throughout (no template literals) to avoid JS parsing issues
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-02 (Konsulta Module — ICD-10 FK Table Mismatch Fix)
- **[BUGFIX]** Fixed `FK constraint "konsulta_consultations_icd10_code_id_foreign"` conflict (500) when saving a consultation. Root cause: `konsulta_consultations` was created with `icd10_code_id` FK pointing to the **old** `icd10_codes` table (no underscore, migration `2026_06_20_000013`), but `KonsultaCrud` loads ICD-10 codes from the `Icd10Code` model which queries the **new** `icd_10_codes` table (with underscore, migration `2026_06_26_172332`). IDs from the two separate tables differ, causing the FK violation.
- **[FIX]** Created migration `2026_07_02_130001_fix_konsulta_icd10_fk.php` — drops `konsulta_consultations_icd10_code_id_foreign` and re-adds it referencing `icd_10_codes(id)` ON DELETE NO ACTION.
- **[DOCS]** Updated `ragdata.md` and `tables.md` (corrected `icd10_code_id` FK reference in table 110 definition).

### 2026-07-02 (Konsulta Module — NULL enrollment_id Fix)
- **[BUGFIX]** Fixed `Cannot insert NULL into column 'enrollment_id'` (500) when saving a consultation from the Consultations tab (no enrollment context). `konsulta_consultations.enrollment_id` was NOT NULL, but the form opened via `openCreateConsultation()` with no `$enrollmentId` passes null.
- **[FIX]** Created migration `2026_07_02_120001_make_enrollment_id_nullable_in_konsulta_tables.php` — makes `enrollment_id` nullable in `konsulta_consultations`, `konsulta_prescriptions`, and `konsulta_lab_referrals`.
- **[DOCS]** Updated `ragdata.md` and `tables.md` (enrollment_id documented as nullable in table 110–112 definitions).

### 2026-07-02 (NursingCrud — Invalid Column Inserts on Order Placement)
- **[BUGFIX]** Fixed `Invalid column name 'requesting_person'` 500 error on `/nursing` when placing a Lab order. Root cause: `placeOrder()` builds a shared `$data` array with keys for all 5 request table schemas, but each `place*Order()` method was not unsetting keys that don't exist on its target table.
- **[FIX]** Corrected all 5 `place*Order()` methods in `NursingCrud.php` to unset inapplicable columns before `::create()`:

| Method | Table | Columns now correctly unset |
|---|---|---|
| `placeLabOrder` | `lab_requests` | `notes`, `total_amount`, `requesting_person` |
| `placePharmacyOrder` | `pharmacy_requests` | `request_notes`, `total_charge` |
| `placeCsrOrder` | `csr_requests` | `patient_id`, `request_notes`, `total_charge`, `priority` |
| `placeRadiologyOrder` | `radiology_requests` | `patient_id`, `priority`, `request_notes`, `total_charge` |
| `placeProcedureOrder` | `procedure_requests` | `requesting_person`, `request_notes`, `total_charge` |

- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-02 (In-Patient Transfer Room — Nursing Module)
- **[FEATURE]** Added "Transfer Room" functionality to the Nursing detail view, allowing in-patients to be moved between ward rooms or to/from ICU beds.
- **Files Modified:**
  - `app/Livewire/Nursing/NursingCrud.php` — Added:
    - Properties: `$showTransferModal`, `$transferTargetType` ('ward_room'|'icu_bed'), `$transferTargetId`, `$transferReason`
    - Methods: `openTransferModal()`, `saveTransfer()`, `closeTransferModal()`, `resetTransferForm()`
    - `saveTransfer()` logic: frees old room/bed (sets `status = 'Available'`), clears old FK, assigns new room/bed (sets `status = 'Occupied'`), updates admission FKs. If switching types (ward→ICU or ICU→ward), clears the other type's FK. Refreshes admission data and dispatches success toast.
    - Render: loads `$availableWardRooms` and `$availableIcuBeds` (both scoped by tenant, status = 'Available')
    - Eager loading: added `icuBed.ward` to `viewAdmission()`, `saveTransfer()` refresh, and `render()` list query
    - Discharge: updated to use `WardRoom::where()` model instead of raw DB query
  - `resources/views/livewire/nursing/nursing-crud.blade.php` — Added:
    - "Transfer Room" button in the patient header card (next to room/ward display), with swap icon SVG
    - Transfer modal (emerald-themed) with: current room/bed read-only display, target type selector (Ward Room / ICU Bed), available rooms/beds dropdown (contextual, updates with `wire:model`), reason textarea, Confirm Transfer button
    - Patient header now shows ICU bed info when patient is in ICU (`$activeAdmission->icuBed?->name`)
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-02 (Room Transfer History + Per-Room Billing with Noon Cutoff)
- **[FEATURE]** Created `admission_room_histories` table and `AdmissionRoomHistory` model to track every room/bed assignment for an admission.
  - **Migration**: `2026_07_02_000001_create_admission_room_histories_table.php` — columns: `admission_id`, `room_type` (ward_room/icu_bed), `room_id`, `room_name`, `ward_name`, `room_rate` (snapshot at assignment), `assigned_at`, `unassigned_at`, `is_active`, audit columns. FK → `admissions.id`.
  - **Model**: `App\Models\AdmissionRoomHistory` — with `recordAssignment()` and `closeActive()` static helpers.
  - **Admission model**: Added `roomHistories()` HasMany relationship.
- **[INTEGRATION]** Room history is recorded/closed at all touchpoints:
  - `AdmissionCrud::saveAdmit()` — records initial room assignment on admission.
  - `AdmissionCrud::discharge()` — closes active room history on discharge.
  - `NursingCrud::saveTransfer()` — closes old history + records new room assignment on transfer.
  - `NursingCrud::confirmDischarge()` — closes active room history on nursing discharge.
- **[BILLING]** Room charge calculation changed from 24h `ceil()` to **calendar-day with noon cutoff**:
  - Formula: `days = diffInDays(startOfDay) + 1`. If discharged before 12PM noon on a multi-day stay, subtract 1.
  - `getUnbilledRequestsProperty()` — now queries `admission_room_histories` for per-room breakdown. Each room stay generates a separate line item with its own days/rate/room_name. Falls back to admission's current room if no history exists.
  - `createInvoice()` — handles both per-history items (`adm_{id}_hist_{hid}`) and flat admission items (`adm_{id}`) with the new day calculation.
- **[UI]** Room information displayed in billing:
  - **View Charges modal**: For Admission items, shows room name, ward, days x rate breakdown below the request code.
  - **Invoice detail modal**: For Admission line items, shows `{qty} day(s) @ ₱{rate}/day` in teal text.
- **[FILES CREATED]** `database/migrations/2026_07_02_000001_create_admission_room_histories_table.php`, `app\Models\AdmissionRoomHistory.php`
- **[FILES MODIFIED]** `app\Models\Admission.php`, `app\Livewire\Admission\AdmissionCrud.php`, `app\Livewire\Nursing\NursingCrud.php`, `app\Livewire\Billing\BillingCrud.php`, `resources/views/livewire/billing/billing-crud.blade.php`
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-15 — Statement of Account: Fix PayloadTooLargeException

- **[BUGFIX]** Fixed `Livewire\Exceptions\PayloadTooLargeException` (1188KB > 1024KB limit) when closing the Statement of Account print modal. The PDF was base64-encoded and stored in a Livewire public property (`$statementPdfBase64`), causing the entire PDF to be sent in every Livewire request payload.
  - **Root cause**: Storing a base64 PDF (1MB+) in a Livewire public property means it gets serialized into every request/response cycle. Closing the modal triggered a re-render that included the full PDF data.
  - **Fix**: Removed `$statementPdfBase64` property entirely. PDF is now generated server-side via a dedicated route (`/billing/statement/{patientId}/{mode}`) and served directly as `application/pdf` response. The modal iframe loads the route URL via a Livewire event (`openStatementPdf`), so the PDF never touches the Livewire payload.
  - **Files modified**:
    - `app/Livewire/Billing/BillingCrud.php` — Removed `$statementPdfBase64` property. Simplified `printStatement()` to dispatch route URL. Removed unused `Pdf` facade import.
    - `resources/views/livewire/billing/billing-crud.blade.php` — Iframe `src` starts as `about:blank`, updated via `Livewire.on('openStatementPdf')` JS event. Added `@script` block for the event listener. Download button now uses `window.open(iframe.src)`.
    - `routes/web.php` — Added `/billing/statement/{patientId}/{mode}` route that generates PDF via dompdf and returns inline response.
- **[DOCS]** Updated `ragdata.md` (this entry).
### 2026-07-01 (Lab Dispensing — Missing `updateBillingStatus()` Method)
- **[BUGFIX]** Fixed 500 error `Method [updateBillingStatus] not found` on `/lab/dispensing` — `LabDispensingCrud.php` was missing the `updateBillingStatus()` method that all other dispensing CRUDs have. Added method following the same pattern: validates status against `['Billed', 'Exempt', 'Credited', 'Pending']`, updates `billing_status` on `LabRequest`, dispatches toast, refreshes parent order components.
- **[UI]** Hidden the billing dropdown button on `lab-dispensing-crud.blade.php` when lab request status is `'Completed'` — completed requests are handled by auto-billing so manual billing actions are unnecessary. Wrapped in `@unless($item->status === 'Completed')` / `@endunless`.

### 2026-07-01 (Hide Billing Dropdown on Completed Requests — RAD, CSR, Pharmacy, Procedures)
- **[UI]** Applied the same billing button hiding from Lab to all other dispensing modules: wrapped billing actions dropdown in `@unless($item->status === 'Completed')` / `@endunless` so completed requests (auto-billed via scheduler) don't show manual billing buttons.
  - `radiology-dispensing-crud.blade.php`
  - `csr-dispensing-crud.blade.php`
  - `pharmacy-dispensing-crud.blade.php`
  - `procedure-dispensing-crud.blade.php`

### 2026-07-01 (Billing Workflow — Patient-Centric For Billing Tab)
- **[FEATURE]** Rewrote Tab 1 ("For Billing") from a flat request-centric list with checkboxes to a patient-centric view in `BillingCrud.php` and `billing-crud.blade.php`:
  - **`getUnbilledPatientsProperty()`** — New computed property at `BillingCrud.php:388-409`. Groups all unbilled service requests by `patient_id`, computing per-patient totals and item counts. Sorted alphabetically by patient name.
  - **Patient table** — Shows: patient avatar (first initial), name, patient ID, item count badge, total amount, and action buttons. Hover-revealed actions: **View Charges** (opens modal) and **Pay** (auto-invoices + opens payment modal).
  - **`viewPatientCharges(int $patientId)`** — Sets modal state showing all line items for the selected patient (source type badge, request code, department, amount, date). Includes "Create Invoice & Pay" button in modal footer.
  - **`closeChargesModal()`** — Resets modal state.
  - **`payPatientCharges(int $patientId)`** — Pre-selects all patient's unbilled items and opens the Invoice creation modal. Sets `patient_id`, context FKs (`er_admission_id`, `admission_id`, etc.) from the first item, and `selectedServiceItems` to all item IDs.
  - **Properties added**: `showChargesModal`, `viewingPatientCharges`, `viewingPatientName`.
  - **Search** now filters by patient name (placeholder updated).
  - **Paginated**: Clientside `array_slice`-based pagination with "Load more" button.
  - **View Charges modal**: Full line-item detail with source badges, department, date, amount, and total footer.

- **[UI]** Removed the Billing status column (header `<th>` + badge `<td>`) from the datatables of all 5 dispensing pages.
  - `lab-dispensing-crud.blade.php` (colspan 9→8)
  - `radiology-dispensing-crud.blade.php` (colspan 8→7)
  - `csr-dispensing-crud.blade.php` (colspan 8→7)
  - `pharmacy-dispensing-crud.blade.php` (colspan 9→8)
  - `procedure-dispensing-crud.blade.php` (colspan 7→6)
- **[UI]** Removed the entire billing dropdown button (Mark as Billed / Exempt / Credited / Pending) from the Actions column in all 5 dispensing pages — billing is now fully handled through the Billing module.
  - Removed `@unless($item->status === 'Completed')` / `@endunless` + billing dropdown `div` from `lab`, `radiology`, `csr`, `pharmacy`, `procedure` dispensing blades.
- **[DOCS]** Updated `ragdata.md` with all 2026-07-01 entries.



### 2026-07-01 (ER Area 500 Error — Duplicate/Broken render() Method)
- **[BUGFIX]** Fixed `ParseError` on `/er/area` (500 Internal Server Error) — `ErAdmissionCrud.php:1063` had broken syntax: a duplicate `render()` method (line 1006) was overwritten by copy-pasted code from `requestProcedureOrder()`, leaving `'procedure_name' => $procedure->name` orphaned with no array/query context. A second correct `render()` existed at line 1201 along with duplicated method bodies (`updateProcedureItemStatus`, `cancelProcedureRequest`, `generateProcedureRequestCode`, `saveDisposition`, `closeModal`). Removed 194 lines of duplicated/broken code.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-01 (Doctors Portal Bugfixes — Namespace Imports, Missing Column, HCP Tenant Mismatch)
- **[BUGFIX]** Fixed `Class "App\Models\OpdConsultation" not found` in `DashboardCrud.php:50` and `PatientChartCrud.php:85` — both files imported `App\Models\OpdConsultation` but the class is at `App\Models\Opd\OpdConsultation`. Same fix for `App\Models\ErAdmission` → `App\Models\Er\ErAdmission`.
- **[BUGFIX]** Fixed `Invalid column name 'admitting_physician_id'` on ER tab in `DashboardCrud.php:62-65` — `er_admissions` table only has `attending_physician_id`, not `admitting_physician_id`. Removed the `admitting_physician_id` OR clause from the ER query.
- **[BUGFIX]** Added `erBed()` alias method to `App\Models\Er\ErAdmission` — the existing `bed()` relationship was called as `erBed()` in `PatientChartCrud.php:567` and `MedicalRecordsCrud.php:291`.
- **[BUGFIX]** Linked `doctor@example.com` (User ID 4) to `health_care_professional_id = 1` (Dr. Maria Santos) — was null, causing "Your account is not linked to a Healthcare Professional profile" warning in doctors dashboard.
- **[BUGFIX]** Fixed `tenant_admin@example.com` permissions — stale cache returned empty `permissions_list`. Already fixed by earlier `optimize:clear`.
- **[BUGFIX]** Fixed "Link to Doctor Profile" dropdown showing no options for tenant admin — HCP records were in tenant 2 but tenant admin is in tenant 1. Updated all 3 HCP records (`tenant_id` 2 → 1).
- **[UI FIX]** Fixed triage modal patient search dropdown clipping — removed `overflow-hidden` from modal container, added `relative z-10` to modal body, increased dropdown `z-20` → `z-50` and `max-h-60` → `max-h-72` so results are visible above the footer.
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-06-30 (CSR Inventory Management Architecture)
- **[FEATURE]** Implemented inventory tracking for CSR (Central Supply Room) items.
  - **Migrations**:
    - Created `item_batches` table to track item stock with `batch_no`, `expiry_date`, `quantity_on_hand`, `unit_cost`, `selling_price`, `location`.
    - Created `item_stock_adjustments` table for inventory adjustments with `item_batch_id`, `type` (Addition/Deduction/Expiry/Damage), `quantity`, `reason`, `adjusted_by`.
  - **Models**: `ItemBatch` and `ItemStockAdjustment`. `Item` model updated with `batches` and `stockAdjustments` relationships + `getTotalStockAttribute()`.
  - **Livewire Components**: `App\Livewire\Supply\ItemBatchCrud` (batch management) and `App\Livewire\Supply\ItemStockAdjustmentCrud` (stock adjustments).
  - **Clinical Integration**: Updated `OrScheduleCrud`, `ObConsultationCrud`, `DeliveryScheduleCrud`, `MicuPatientCrud`, `OpdConsultationCrud`, `ErAdmissionCrud`, and `AdmissionCrud` to filter `supplyItems` / `csrItems` where `quantity_on_hand > 0` and unexpired.
- **[BUGFIX]** Fixed a 500 error in `ItemBatchCrud` where it incorrectly referenced the view `livewire.pharmacy.item-batch-crud` instead of `livewire.supply.item-batch-crud`.
- **[BUGFIX]** Corrected typos in `ItemStockAdjustmentCrud` where the class and model were mistakenly named `ItemItemStockAdjustmentCrud` and `ItemItemStockAdjustment`.


### 2026-06-30 (Demo Seeder & Pharmacy Module Bugfix)
- **[SEEDER]** Created `DemoHospitalSeeder.php` to populate Tenant ID 1 with a robust set of initial master data to facilitate immediate testing and demonstrations. The seeder populates: Wards (3), Ward Rooms (5), ER Zones (4), ER Beds (8), Pharmacy Drugs (10), Item Categories (2), Supply Units (3), and CSR Items (10).
- **[FIX]** Wrote a custom `insertData` closure in the seeder leveraging `updateOrInsert` because Laravel's `insertOrIgnore` is not supported on SQL Server (`sqlsrv` driver). This ensures the seeder is fully idempotent and can be re-run indefinitely without duplicate key errors.
- **[BUGFIX]** Fixed a 500 error in `app/Livewire/Pharmacy/DrugBatchCrud.php` where searching for a drug threw `Invalid column name 'drug_name'`.
- **[REFACTOR]** The `drugs` table deliberately splits names into `generic_name` and `brand_name`. Updated the `Drug` model with a `getDrugNameAttribute()` accessor (and added it to `$appends`) to dynamically concatenate `generic_name (brand_name)`. Modified the Livewire query constraints to explicitly search against the real columns (`generic_name`, `brand_name`, `drug_code`) while retaining `$drug['drug_name']` compatibility for the frontend arrays.

### 2026-06-30 (Database Migration Complete — Missing Tables + Migration Ordering Fixes)
- **[FIX]** Login redirect caused 500 error because only 6 migrations had been run (users, tenants, RBAC). All 150+ core business tables (patients, er_admissions, admissions, opd_consultations, billing_invoices, etc.) didn't exist — dashboard `mount()` failed querying non-existent tables.
- **[FIX]** Migration ordering issue: `2026_06_25_000099_add_yakap_fields_to_opd_consultations_table.php` (timestamp `000099`) sorted before `2026_06_25_025645_create_opd_consultations_table.php` (timestamp `025645`), causing ALTER on non-existent table. Renamed to `2026_06_25_025646_add_yakap_fields_to_opd_consultations_table.php` to sort after table creation.
- **[FIX]** YAKAP FK dependency: `yakap_empanelments.fpe_consultation_id` and `yakap_fpe_records.consultation_id` referenced `opd_consultations` which didn't exist yet at migration `000095`/`000096`. Removed those FK declarations from those migrations and created a new deferred migration `2026_06_30_120115_add_fpe_consultation_fk_to_yakap_empanelments.php` that adds them after `opd_consultations` exists.
- **[RESULT]** `php artisan migrate --force` ran successfully. All 150+ migrations now show as "Ran". Database schema is fully migrated.

### 2026-06-30 (Sidebar Gating Fix — Duplicate Reports Link + Permission Cache on Login)
- **[FIX]** Removed duplicate un-gated Reports link in `resources/views/components/layouts/app.blade.php:684-688` — a second Reports link existed with no `@can('reports.view')` gate, making it visible to all users regardless of permissions.
- **[FIX]** Added `$user->flushPermissionCache()` in `app/Livewire/Auth/Login.php:56-57` — permission cache was not flushed on login, causing stale menus for up to 1 hour if permissions were changed between sessions.

### 2026-06-30 (RBAC Phase 9 — Testing & Hardening)
- **[RBAC]** Completed Phase 9 of the RBAC implementation — comprehensive test suite + manual E2E walkthrough:
  - **22 Test Files Created/Updated:**
    - `tests/Feature/Rbac/BladeDirectivesTest.php` (7 tests) — `@can()`, `@cannot()`, `@role()` Blade directive rendering with permission checks
    - `tests/Feature/Rbac/UserHasPermissionTest.php` (7 tests) — `hasPermission()` for all 4 roles (superadmin, tenant_admin, staff, doctor), cache behavior, inactive user returns no permissions
    - `tests/Feature/Rbac/CheckPermissionMiddlewareTest.php` (5 tests) — unauthenticated denied, lacks permission denied, has permission allowed, JSON forbidden response, superadmin bypass
    - `tests/Unit/Rbac/PermissionCacheBenchmarkTest.php` (3 tests) — cache miss timing, cache hit timing, cache hit faster than miss (logged, not asserted)
  - **Targeted Migration Strategy**: All test `setUp()` methods use same 5-migration list (`000001`, `000002`, `000004`, `000005` + users table) to avoid the broken `opd_consultations` migration — no `RefreshDatabase` trait used
  - **Fixes Applied:**
    - `app/Models/User.php` — `hasPermission()` now returns false for inactive users; superadmin bypass logic refined
    - `app/Http/Middleware/CheckPermission.php` — added `isSuperAdmin()` bypass before permission lookup
    - `database/factories/UserFactory.php` — uses `full_name` instead of `name`, added `username`, no `remember_token`/`email_verified_at`
  - **E2E Walkthrough**: Superadmin and Tenant Admin logins verified at `http://127.0.0.1:8000/login` — dashboard loads correctly. Staff/Doctor user creation and sidebar rendering verification pending.
- **[DOCS]** Updated `rbac-phase1.md` — Phase 9 marked complete, all 5 deliverables checked. Updated `ragdata.md` (this entry).

### 2026-06-30 (RBAC Phase 8 — Migration from Ad-Hoc `isSuperAdmin()` Checks)
- **[RBAC]** Completed Phase 8 — audited and cleaned up 69+ inline `isSuperAdmin()` calls across the codebase:
  - **Audit Summary:**
    - 30+ tenant-scoping calls (`->when(!$isSuperAdmin, ...)`) — **Kept** as-is (multi-tenant data isolation, not permission-based)
    - 16 superadmin-only auth calls (UserCrud, TenantCrud) — **Kept** as legitimate role checks (only superadmins can manage superadmin users)
    - 1 dead code block in `PermissionCrud` — **Removed**
    - 1 docblock in `JwtMiddleware` — **Deprecated** `is_superadmin` request attribute, attribute kept for backward compat
    - 2 session/API response removals — **Removed** `is_superadmin` from `Login.php` session data and `AuthController.php` API response
    - 4 User model role helpers (`isSuperAdmin()`, `isTenantAdmin()`, `isStaff()`, `isDoctor()`) — **Marked `@deprecated`**, kept for core RBAC resolution + tenant-scoping
  - **Files Touched:**
    - `app/Models/User.php` — 4 helpers marked `@deprecated` with migration notes
    - `app/Livewire/Masters/PermissionCrud.php` — removed dead `isSuperAdmin()` method
    - `app/Http/Middleware/JwtMiddleware.php` — deprecated `is_superadmin` attribute in docblock
    - `app/Livewire/Auth/Login.php` — removed `is_superadmin` from session data
    - `app/Http/Controllers/AuthController.php` — removed `is_superadmin` from API response
- **[DOCS]** Updated `rbac-phase1.md` — Phase 8 marked complete, all 6 deliverables checked. Updated `ragdata.md` (this entry).

### 2026-06-30 (RBAC Phase 7 — Role Management UI)
- **[RBAC]** Completed Phase 7 — full CRUD for roles, replacing hardcoded `role` column with `role_id` FK:
  - **Migration `000004`**: Created `roles` table (`id`, `name`, `display_name`, `description`, `is_system`, `is_active`, audit columns). Seeded 4 system roles inline: superadmin, tenant_admin, staff, doctor.
  - **Migration `000005`**: Added `role_id` FK (nullable, `no action` on delete) to `users` and `role_permissions` tables. Backfilled existing rows by matching `role` string name. Updated `role_permissions` unique constraint from `(role, permission_id, tenant_id)` to `(role_id, permission_id, tenant_id)`.
  - **Model**: `App\Models\Role` — `$timestamps = false`, `$fillable`, `is_system`/`is_active` casts, `users()` HasMany, `scopeActive()` for `is_active && history=1`.
  - **User Model Updates**: Added `roleRelation()` BelongsTo via `role_id`. Virtual `getRoleAttribute()` accessor reads from related Role model when loaded, falling back to raw `role` column for backward compat. `getPermissionsListAttribute()` matches via both `role_permissions.role` and `role_permissions.role_id`.
  - **PermissionSeeder Update**: Now looks up role IDs via `DB::table('roles')->pluck('id', 'name')` and inserts `role_id` alongside `role` name column.
  - **UserCrud Update**: Role dropdown loads from DB via `$roles = Role::active()->orderBy('display_name')->get()`. Renders `$r->name`/`$r->display_name`. Superadmin option hidden from non-superadmin users.
  - **Livewire Component**: `App\Livewire\Admin\RoleCrud` — datagrid with search/sort/pagination, create/edit modal with 2 tabs (Details + Permissions). Permissions tab shows grouped permission checkboxes. Saves role details and role-permission assignments with audit logging (`LogsPermissionAudit`). Flushes permission cache for all affected users on permission change. System roles protected from name/delete modification, only soft-archived via `history=0`.
  - **Blade View**: `resources/views/livewire/admin/role-crud.blade.php` — emerald/amber theme, permission checkboxes grouped by module, system role warnings.
  - **Routes**: `GET /admin/roles` → page wrapper at `resources/views/pages/admin/roles.blade.php`
  - **Sidebar**: "Roles" link under Administration sub-menu.
- **[DOCS]** Updated `rbac-phase1.md` — Phase 7 marked complete, all 8 deliverables checked. Updated `ragdata.md` (this entry).

### 2026-06-30 (RBAC Phase 6 — Permission Audit Trail)
- **[RBAC]** Completed Phase 6 of the RBAC implementation — full audit trail for all permission changes:
  - **Migration**: `2026_06_30_000003_create_permission_audit_log_table.php` — `permission_audit_log` table with action, entity_type, entity_id, role, user_id, permission_id, permission_name (denormalized), old_value, new_value (JSON text), changed_by (FK→users), changed_date, tenant_id, history + indexes on action, entity_type, changed_date.
  - **Model**: `App\Models\PermissionAuditLog` — with `changedBy()`, `permission()`, `affectedUser()` relationships.
  - **Trait**: `App\Livewire\Concerns\LogsPermissionAudit` — reusable `logAudit()` helper that auto-populates `changed_by` and `tenant_id` from JWT request attributes.
  - **PermissionCrud Integration**: Logs `created`, `updated`, and `archived` actions with old/new value JSON snapshots.
  - **RolePermissionCrud Integration**: Computes diff between old and new permission sets; logs `assigned` per added permission, `revoked` per removed permission.
  - **UserPermissionCrud Integration**: Computes diff between old and new overrides; logs `override_allowed`, `override_denied`, `override_removed` per changed permission.
  - **View-Only Audit Log Page**: `App\Livewire\Admin\AuditLogCrud` — datagrid with search, sort, pagination, filters (action dropdown, date range, entity type, role). Displays colored action badges, entity details, changed-by user, expandable old/new value JSON diff. Amber/rose theme. Route: `GET /admin/audit-log` (gated with `admin.masters`). Sidebar link in Administration section.
  - **Files Created**: `app/Models/PermissionAuditLog.php`, `app/Livewire/Concerns/LogsPermissionAudit.php`, `app/Livewire/Admin/AuditLogCrud.php`, `resources/views/livewire/admin/audit-log-crud.blade.php`, `resources/views/pages/admin/audit-log.blade.php`
  - **Files Modified**: `app/Livewire/Masters/PermissionCrud.php`, `app/Livewire/Admin/RolePermissionCrud.php`, `app/Livewire/Admin/UserPermissionCrud.php`, `routes/web.php`, `resources/views/components/layouts/app.blade.php`
- **[DOCS]** Updated `rbac-phase1.md` — Phase 6 marked complete, all 5 deliverables checked. Updated `ragdata.md` (this entry).

### 2026-06-30 (RBAC Phase 5 — Complete Sidebar Gating)
- **[RBAC]** Completed Phase 5 of the RBAC implementation — every sidebar menu item in `resources/views/components/layouts/app.blade.php` is now gated with `@can()` directives:
  - **Clinical Modules**: ER (`@can('er.triage')`), Admission & Wards (`@can('admission.view')`), Nursing (`@can('nursing.view')`), OB (`@can('ob.view')`), Delivery (`@can('delivery.view')`), OR (`@can('or.view')`), Renal Care (`@can('renal.view')`)
  - **Ancillary & Logistics**: Lab (`@can('lab.view')`), Radiology (`@can('radiology.view')`), Pharmacy (`@can('pharmacy.view')`), Procedures (`@can('procedure.view')`), CSR/Supply (`@can('csr.view')`)
  - **Financial & Claims**: Billing (`@can('billing.view')`), PhilHealth (`@can('philhealth.view')`), YAKAP (`@can('yakap.view')`), Transactions (`@can('billing.view')`), Reports (`@can('reports.view')`)
  - **Sub-items**: Each child link gated with its specific permission (e.g., `@can('lab.dispense')` on Lab Dispensing, `@can('admin.masters')` on master data items, `@can('reports.view')` on report links)
  - **Administration**: Already gated from Phase 1 (`@can('admin.masters')`)
- **[DOCS]** Updated `rbac-phase1.md` — Phase 5 marked complete, all 5 deliverables checked. Updated `ragdata.md` (this entry).

### 2026-06-30 (Doctor Portal — Medical Records Integration with Full Past Encounter Drill-Down)
- **[INTEGRATION]** Enhanced Doctor's Patient Chart (`App\Livewire\Doctors\PatientChartCrud`) with a new **"Medical Records" tab** replacing the old "Past Records" tab:
  - Timeline view shows all past encounters (OPD, ER, Ward, OB) as color-coded clickable cards with type badge, encounter code, date, physician, and diagnosis
  - Clicking a past encounter loads full detail via `viewPastEncounter()` — clinical fields grid, lab results table (with abnormal flag highlighting and reference ranges), pharmacy orders, and radiology orders
  - 4 encounter detail loaders: `loadPastOpd()`, `loadPastEr()`, `loadPastWard()`, `loadPastOb()` — each eager-loads all related orders and results for that specific encounter
  - "Open Full Records" link opens the standalone Medical Records module in a new tab
- **[ENHANCED]** `getMedicalHistoryProperty()` — now includes `id`, `type_key`, `code`, and `color` properties in each history entry for clickable drill-down support
- **[FILES MODIFIED]** `app/Livewire/Doctors/PatientChartCrud.php`, `resources/views/livewire/doctors/patient-chart-crud.blade.php`
- **[DOCS]** Updated `ragdata.md` (this entry).

---

### 2026-06-30 (Medical Records Module — Patient Encounter History & Certificates)
- **[MODULE BUILT]** Medical Records module — centralized read-only view of complete patient encounter history across all clinical modules:
  - **Livewire Component** `App\Livewire\MedicalRecordsCrud` — patient search with fuzzy autocomplete (name/code), patient summary card with demographics and encounter stats, color-coded timeline filter by encounter type (All/OPD/ER Triage/ER Treatment/Admission/OB), encounter detail modal showing full clinical fields, associated Lab/Pharmacy/Radiology orders, and billing invoice links.
  - **Encounter Detail Loaders** — `loadOpdDetail()`, `loadErTriageDetail()`, `loadErTreatmentDetail()`, `loadAdmissionDetail()`, `loadObDetail()` — each eager-loads encounter-specific relationships (physician, clinic, bed, room, ward, orders, invoices) and formats fields for display.
  - **5 Printable Certificates** — Medical Certificate, Certificate of Admission, Discharge Summary, Certificate of Treatment, Clinical Abstract. Each auto-fills patient demographics, encounter data, attending physician, diagnosis, and services rendered. Opens in a new browser tab for native print/PDF.
- **[PRINT VIEW]** Created `resources/views/pages/medical-records/certificate-print.blade.php` — professional print-optimized template with: gradient hospital letterhead header, patient information grid (name, code, DOB, age, gender, PH PIN, contact, address), encounter details table, services rendered table (type/code/items/status), attending physician and hospital representative signature lines, print/close buttons (hidden in print). Supports all 5 certificate types via dynamic body content.
- **[BLADE VIEW]** Created `resources/views/livewire/medical-records-crud.blade.php` — full SPA with: gradient patient summary card, sticky sidebar timeline filter, encounter cards grouped by type with status badges, encounter detail modal with accordion sections (fields, clinical notes, lab/pharmacy/radiology orders, billing invoices), certificate dropdown menu in modal header.
- **[ROUTES]** Added 2 routes in `routes/web.php`: `GET /medical-records` (page) and `GET /medical-records/certificate/{type}/{encounterType}/{encounterId}/{patientId}` (certificate print) — route handler builds certificate data using match expressions for polymorphic encounters, loads lab/pharmacy orders dynamically, serves printable Blade view.
- **[SIDEBAR]** Added "Medical Records" link in Main section after Patients — violet theme icon, active state detection via `request()->is('medical-records*')`.
- **[FILES CREATED]** `app/Livewire/MedicalRecordsCrud.php`, `resources/views/livewire/medical-records-crud.blade.php`, `resources/views/pages/medical-records.blade.php`, `resources/views/pages/medical-records/certificate-print.blade.php`
- **[FILES MODIFIED]** `routes/web.php`, `resources/views/components/layouts/app.blade.php`
- **[DOCS]** Updated `ragdata.md` (this entry).

---

### 2026-06-30 (Billing Auto-Invoice — Automated Invoice Creation)
- **[FEATURE]** Created `app/Console/Commands/BillingAutoInvoice.php` — artisan command `billing:auto-invoice` that:
  - Collects all completed/pending-billable items from Lab, Pharmacy, CSR, Radiology, and Procedure requests
  - Collects OECB charges from completed ER encounters (`is_oecb = true`, `status = 'Completed'`)
  - Collects room charges from discharged admissions
  - Groups all items by `patient_id` and auto-creates one invoice per patient
  - Marks source records as `Billed` with `billing_id` reference
  - Uses `DB::transaction` per invoice for atomicity
  - Reports created invoices and total billable items processed
- **[SCHEDULE]** Registered `billing:auto-invoice` in `routes/console.php` — runs daily at `23:59` with `withoutOverlapping()`, logging to `storage/logs/billing-auto-invoice.log`
- **[ENHANCEMENT]** Updated `BillingCrud::getUnbilledRequestsProperty()` — changed room charge query from `where('status', 'Discharged')` to `whereIn('status', ['Admitted', 'Discharged'])` so **actively admitted** patients' daily room charges also appear in the "For Billing" tab. Active admissions use `now()` as the end date for daily charge computation.
- **[FILE CREATED]** `app/Console/Commands/BillingAutoInvoice.php`
- **[FILES MODIFIED]** `routes/console.php`, `app/Livewire/Billing/BillingCrud.php`
- **[DOCS]** Updated `ragdata.md` (this entry).

---

### 2026-06-29 (Automated In-Patient Room & Board Billing)
- **[FEATURE]** Implemented dynamic room & board billing for in-patients in `BillingCrud.php`, calculating duration of stay and applying `room_rate` from either Ward Room or ICU Bed.
- **[MIGRATION]** Added `room_rate` (decimal, 10,2) to `icu_beds` via `2026_06_29_131244_add_room_rate_to_icu_beds_table.php`.
- **[MIGRATION]** Added `billing_id` (FK to `billing_invoices.id`) and `billing_status` (string, default 'Pending') to `admissions` via `2026_06_29_131248_add_billing_fields_to_admissions_table.php`.
- **[UI]** Updated `IcuBedCrud` and blade view to support setting daily `room_rate`.
- **[FEATURE]** Added "Import from Billing" to PhilHealth Claims (`ClaimCrud.php`), dynamically loading room charges, meds, and supplies from central `BillingInvoice` directly into CF2/CF4 items.
- **[DOCS]** Updated `ragdata.md` and `tables.md` to reflect new schema changes for `icu_beds`, `admissions`, and `philhealth_claims`.

---
### 2026-06-29 (Bug Fixes: Radiology Results — User schema mismatches, Modal layout & width)
- **[FIX]** `Call to undefined method User::roles()` — User model uses `role` column (string), not `roles()` relation; replaced `whereHas('roles')` with `whereIn('role', [...])` in `RadiologyResultsCrud`, `RadiologyDispensingCrud`, and `radiology-dispensing-crud.blade.php`
- **[FIX]** `Invalid column name 'name'` — `users` table has `full_name`, not `name`; updated all queries and blade references (`->name` → `->full_name`) across 6 files
- **[FIX]** Added missing `use App\Models\User` import in `RadiologyDispensingCrud.php`; removed duplicate `WithFileUploads`/`WithPagination` imports in `RadiologyResultsCrud.php`
- **[MODAL WIDTH]** Result entry modal: `max-w-3xl` → `max-w-6xl`; Detail modal: `max-w-3xl` → `max-w-5xl`; Dispensing modals: `max-w-2xl`/`max-w-3xl` → `max-w-4xl`/`max-w-5xl`
- **[MODAL LAYOUT]** Detail modal re-arranged into 2-column grid: Technique↔Impression, Findings↔Full Report, Remarks↔Images; added `min-h` placeholders for empty sections

---

### 2026-06-29 (Radiology Results Module — Dedicated Results Manager, Multi-Image Upload, DICOM API, Print View)
- **[FEATURE]** Created `RadiologyResultsCrud` — dedicated results Livewire component with filter tabs (All/Pending/Completed), search, sort, detail modal showing full structured report (technique, findings, impression, report, remarks), image gallery, and audit trail (radiologist + technologist)
- **[MIGRATION 1]** Added `impression` (text), `technique` (text), `findings` (text), `performed_by` (FK→users), `performed_at` (timestamp) to `radiology_request_items`
- **[MIGRATION 2]** Created `radiology_result_images` table — multi-image support with DICOM UIDs (study/series/sop), is_primary flag, mime_type, file_size
- **[MODEL]** `RadiologyRequestItem` enhanced with `performedBy()` relation, `images()` HasMany, `primaryImage()`, computed accessors (`status_badge_class`, `patient_name`, `request_code`, `image_count`, `report_summary`)
- **[MODEL]** Created `RadiologyResultImage` model — belongsTo RadiologyRequestItem
- **[PRINT]** DOH-compliant `result-print.blade.php` with technique/findings/impression sections, image attachments, three-signature block (Performed By / Read By / Date Reported)
- **[DICOM API]** `DicomController` with endpoints: `GET /api/dicom/studies` (query worklist), `GET /api/dicom/modalities` (list supported), `GET /api/dicom/worklist` (MWL), `GET /api/dicom/images/{id}` (serve), `GET /api/dicom/images/{id}/thumbnail`, `POST /api/dicom/store` (receive DICOM pushes)
- **[DICOM COMPATIBILITY]** Supports modalities common in Philippine hospitals: X-Ray (Fuji/Siemens/Philips/GE/Shimadzu), CT (GE/Siemens/Canon/Philips), MRI (GE/Siemens/Philips/Canon), US (GE/Siemens/Mindray/Samsung/Esaote/Fuji), Mammo (GE/Siemens/Hologic/Fuji/IMS Giotto)
- **[ENHANCED DispensingCrud]** Result modal now includes technique, findings, impression, full report, remarks fields + multi-image gallery with delete
- **[ENHANCED PatientChart]** Radiology results tab shows structured sections (technique/findings/impression/report), image grid with hover zoom, Print Result button
- **[ROUTES]** Added `radiology.results` (GET /radiology/results), `radiology.result.print` (GET /radiology/result/{id}/print), 6 DICOM API routes
- **[SIDEBAR]** Added "Results" link under Radiology sub-menu (before Dispensing)
- **[FILES CREATED]** `2026_06_29_000001_add_radiology_result_enhancements_table.php`, `2026_06_29_000002_create_radiology_result_images_table.php`, `RadiologyResultsCrud.php`, `radiology-results-crud.blade.php`, `pages/radiology/results.blade.php`, `pages/radiology/result-print.blade.php`, `RadiologyResultImage.php`, `Api/DicomController.php`
- **[FILES MODIFIED]** `RadiologyRequestItem.php`, `RadiologyDispensingCrud.php`, `radiology-dispensing-crud.blade.php`, `PatientChartCrud.php`, `patient-chart-crud.blade.php`, `routes/web.php`, `app.blade.php`

---

### 2026-06-28 (Philippine-Standard Lab Reference Ranges Seeder — 46 Ranges Across 27 Tests)
- **[SEEDER]** Created `LabReferenceRangeSeeder` — seeds DOH-aligned reference ranges for all 27 lab tests
- **[COVERAGE]** 46 ranges total: Clinical Chemistry (13 tests, 20 ranges), Hematology (9 tests, 22 ranges), Serology (4 tests, 4 ranges), Urinalysis (1 test, 1 range)
- **[DEMOGRAPHICS]** Age-stratified (adult M/F, pediatric, infant) for HGB, WBC, RBC, HCT, CREA, UA; gender-specific for CREA, UA, HGB, HCT, RBC, ESR, ALT, AST, HDL
- **[FLAG COMPUTATION]** Each numeric test stores min/max defining the normal interval; `computeFlag()` auto-assigns H/L/N based on patient result vs. range. Categorical tests (HBsAg, VDRL, Dengue, Urinalysis) use `normal_values_text` for Abnormal/Normal classification.
- **[REGISTERED]** Added `LabReferenceRangeSeeder::class` to `DatabaseSeeder.php` after `DemoDataSeeder`
- **[FILE]** `database/seeders/LabReferenceRangeSeeder.php`

---

### 2026-06-28 (Lab Results Workflow — Result Entry, Flag Computation, Pathologist Validation, DOH-Compliant Printout)
- **[TAG]** 9 tasks completed: migration, model, Livewire component (LabResultsCrud), blade view (datagrid + modals), enhanced LabDispensingCrud (result modal with reference range preview + auto-flag computation), enhanced PatientChartCrud (doctor's lab results tab with flag badges + reference range column + abnormal row highlighting), DOH-compliant print view, page wrapper, routes + sidebar
- **[MIGRATION]** Added `flag` (varchar(10)), `reference_range` (varchar(100)), `validated_by` (FK→users), `validated_at` (timestamp) to `lab_request_items`
- **[FEATURE]** Flag computation: Matches result_value against ReferenceRange model using patient age + gender; returns H/L/N/Abnormal/Normal/Critical
- **[FEATURE]** DOH compliance: AO 2021-0037 electronic signature standards with three-signature block (performed/verified/validated by)
- **[FILES CREATED]** `2026_06_28_000001_add_lab_result_enhancements_table.php`, `LabResultsCrud.php`, `lab-results-crud.blade.php`, `pages/lab/results.blade.php`, `pages/lab/result-print.blade.php`
- **[FILES MODIFIED]** `LabRequestItem.php`, `LabDispensingCrud.php`, `lab-dispensing-crud.blade.php`, `PatientChartCrud.php`, `patient-chart-crud.blade.php`, `routes/web.php`, `app.blade.php`
- **[ROUTES]** `GET /lab/results` (lab.results), `GET /lab/result/{id}/print` (lab.result.print)

---
### 2026-06-28 (Sidebar Reorganization & Sub-menu Icons)
- **[UI/UX]** Complete overhaul of the main application sidebar menu (`resources/views/components/layouts/app.blade.php`).
- **[ENHANCEMENT]** Reorganized the 25+ flat menu items into 5 logical categories with subtle uppercase section headers: MAIN, CLINICAL MODULES, ANCILLARY & LOGISTICS, FINANCIAL & CLAIMS, ADMINISTRATION.
- **[ENHANCEMENT]** Replaced duplicated generic SVGs with unique, context-aware Heroicons for every primary menu module (e.g., Beaker for Lab, Medical Cross for Pharmacy, X-Ray for Radiology, Warning for ER, etc.).
- **[ENHANCEMENT]** Consolidated UI: Moved 'Fund Sources' natively inside the Billing collapsible dropdown. Clustered Admission, Wards, Room Types, and Hospital Transfers under a single "Admission & Wards" menu.
- **[ENHANCEMENT]** Integrated categorized inline sub-menu icons (small SVG markers) based on link context:
  - Document icon for Records/Consultations/Invoices/Admissions
  - Location pin for Wards/Clinics/Regions
  - Cube box for Dispensing/Inventory/Tests
  - Bullet list for Categories/Types/Methods
  - User profile for Patients/Members/Doctors
  - Chart bar for Reports/Dashboards
  - Gear/cog for Settings/Codes/Rates
- **[FIX]** Resolved a 500 Internal Server Error caused by injecting SVG strings into Laravel blade syntax (`{{ request()->is() }}`). Used a native PHP heredoc script to cleanly generate the sidebar HTML and avoid blade brace conflicts.

### 2026-06-28 (E2E Testing — Billing Workflow Fixes + Patient Autocomplete Race Conditions)
- **[E2E TEST]** Full billing workflow tested end-to-end:
  - **20.1.3** Checked Pharmacy (PR-00004, ₱340.88) + CSR (CR-00003, ₱270.00) → clicked "Generate Invoice" → modal shows **Selected Service Requests (2)** with correct items and total ₱610.88 ✅ (previously empty — **BUG #9a FIXED**)
  - **20.1.4** Searched "Santos" → selected "Santos, Maria" → "Selected: Santos, Maria" persisted, not lost ✅ (previously patient_id was wiped — **BUG #9b FIXED**)
  - **20.1.5** Clicked "Create Invoice" → INV-00001 created → Invoices tab shows INV-00001, Santos, Maria, ₱610.88, Unpaid ✅
  - **20.1.6** Added payment PAY-00001 (₱610.88, Cash) → invoice status updates to **Paid** ✅
  - **20.1.7** Payments tab shows PAY-00001 with Print Receipt link ✅
  - **20.1.8** Cancelled INV-00001 → status changes to **Cancelled** → unbilled restored to ₱6,110.88 ✅
- **[FIX]** Bug #9a — `resetInvoiceForm()` cleared `selectedServiceItems` on modal open:
  - Root cause: `resetInvoiceForm()` included `'selectedServiceItems'` in the `$this->reset([...])` array, wiping all checked items when the modal opened
  - Fix: Removed `'selectedServiceItems'` from `resetInvoiceForm()` reset array in `app/Livewire/Billing/BillingCrud.php:689`
  - Items now persist from For Billing tab checkboxes into Generate Invoice modal
- **[FIX]** Bug #9b — `updatedPatientSearch()` race condition clearing `patient_id`:
  - Root cause: `selectPatient()` sets `patient_id` then sets `patient_search = null`, which triggers `updatedPatientSearch()` hook that wipes `patient_id`
  - Fix: Added `$skipPatientSearchUpdate` guard flag to `BillingCrud.php` — set to `true` in `selectPatient()` before setting `patient_search`, checked in `updatedPatientSearch()` to skip clearing
- **[FIX]** Bug #11 (Delivery) + Bug #15 (OR) + Bug #16 (OB) — Same `patient_search` race condition in 3 additional Livewire components:
  - `app/Livewire/Delivery/DeliveryScheduleCrud.php` — Added `$skipPatientSearchUpdate` flag
  - `app/Livewire/Or/OrScheduleCrud.php` — Added `$skipPatientSearchUpdate` flag
  - `app/Livewire/Ob/ObConsultationCrud.php` — Added `$skipPatientSearchUpdate` flag
- **[DOCS]** Updated `e2e-test-plan.md` (Billing results, Delivery/OR/OB bugs #15/#16, 14 total bugs found). Updated `ragdata.md` (this entry).

### 2026-06-28 (E2E Testing — Hospital Transfer, MICU Bugs #7/#8 Fix, Admission/PatientChart Lab Column Fixes)
- **[E2E TEST]** Verified Hospital Transfer module (`/transfers`) — Bug #12 was already resolved (Livewire JS loads fine):
  - **22.1** Page loads with datagrid and "New Transfer" button ✅
  - **22.2** "New Transfer" modal opens with all fields (patient search, destination hospital, source encounter type, doctor, transport mode) ✅
  - **22.3** Created transfer HT-00001 (Gonzales, Carlos → St. Lukes Medical Center, Ambulance, Dr. Dela Cruz, Acute Myocardial Infarction) ✅
  - **22.4** Transfer visible in datagrid with Pending status, View/Edit and Archive actions ✅
- **[FIX]** Bug #7 — `SQLSTATE[42S22]: Invalid column name 'test_name'` across MICU and Admission modules:
  - `lab_tests` table uses column `name`, NOT `test_name`. Blade templates and Livewire components were incorrectly referencing `$test->test_name` (LabTest model).
  - **Files fixed (4):**
    - `app/Livewire/MICU/MicuPatientCrud.php:183` — `'lab_test_name' => $item->test_name` → `'test_name' => $item->name`
    - `resources/views/livewire/micu/micu-patient-crud.blade.php:136` — `'itemName' => 'lab_test_name'` → `'itemName' => 'test_name'`
    - `app/Livewire/Admission/AdmissionCrud.php:337` — `'lab_test_name' => $item->test_name` → `'test_name' => $item->name`
    - `resources/views/livewire/admission/admission-crud.blade.php:196` — `$item->lab_test_name` → `$item->test_name` (LabRequestItem column IS `test_name`, not `lab_test_name`)
  - **Additional file fixed:** `app/Livewire/Doctors/PatientChartCrud.php:218` — `'name' => $item->test_name` → `'name' => $item->name` (same LabTest column bug)
- **[FIX]** Bug #8 — `Call to undefined relationship [section] on model [App\Models\LabTest]`:
  - `LabTest` defines the relationship as `labSection()`, not `section()`. Blade templates were calling `$test->section->name`.
  - **Files fixed (2):**
    - `resources/views/livewire/micu/micu-patient-crud.blade.php:365` — `$test->section` → `$test->labSection`
    - `resources/views/livewire/admission/admission-crud.blade.php:487` — `$test->section` → `$test->labSection`
- **[DOCS]** Updated `e2e-test-plan.md` (MICU, Transfer, Admission results; bugs #7/#8/#12/#13/#14 statuses; coverage summary). Updated `ragdata.md` (this entry).

### 2026-06-27 (E2E Testing — ER Module + Bug Fix: Missing requesting_doctor_id Column)
- **[E2E TEST]** Ran Playwright E2E tests on ER Triage & ER Area (sections 12.1–12.2):
  - **12.1.1–12.1.4** ER Triage — Navigate, create record, fill vitals/acuity, save ✅
  - **12.1.5** Transfer to ER — assign bed (ER BED 1), physician (Dr. Dela Cruz), mode (Ambulance), diagnosis ✅
  - **12.2.1** Navigate `/er/area` — active admission datagrid visible ✅
  - **12.2.2** Click "Treatment" — 4-tab form loads (Clinical Notes, Orders, OR Schedule, Disposition) ✅
  - **12.2.3** Fill Clinical Notes (HPI, PE, Assessment, Treatment Notes) → auto-save ✅
  - **12.2.4** Lab Order — selected CBC + Urinalysis (Urgent) → LR-00001 created ✅
  - **12.2.5** CSR Supply Order — selected Gauze/IV-Set/Gloves → **500 error** ❌
  - **12.2.6** Radiology Order — selected Ultrasound Abdomen → **500 error** ❌
  - **12.2.7** Pharmacy Order — Ceftriaxone 1g (BID/IV) + Paracetamol 500mg (TID/PO) → created ✅
  - **12.2.8** Procedure Order — Appendectomy → created ✅
  - **12.2.9** OR Schedule — OR-1, Surgeon Dr. Dela Cruz, Appendectomy → ORS-00001 created ✅
  - **12.2.10** Disposition — "Admitted to Ward" + instructions → saved, patient moved to Completed ✅
  - **12.2.11** Verify orders in dispensing modules → LR-00001 in /lab/dispensing ✅, CR-00001 in /supply/dispensing ✅, RR-00001 in /radiology/dispensing ✅, PR-00001 in /pharmacy ✅, PRCD-00001 in /procedures ✅
  - **12.2.12** Change item status (Collect CBC sample in /lab/dispensing) → ER tab refreshes showing Processing status ✅
- **[FIX]** Added `requesting_doctor_id` column (nullable FK→`health_care_professionals`) to both `csr_requests` and `radiology_requests` tables — was missing from both tables causing `500 error` when ER treatment form tried to insert `requesting_doctor_id` into the create array.
  - **Migration**: `2026_06_27_000001_add_requesting_doctor_id_to_csr_and_radiology_requests.php` ✅ RUN
- **[E2E TEST 12.3 — OECB]** Full OECB workflow tested end-to-end:
  - **12.3.1** Created new triage (Santos, Maria → ERA-20260627-9034), transferred to ER, toggled OECB ON → sky-themed section with "Add OECB Entry" button appeared ✅
  - **12.3.2** Added 2 entries: "Fever" → OECB-006, ₱5,500.00 auto-populated; "Severe Headache" → OECB-005, ₱7,500.00 auto-populated. Total shown: ₱13,000.00 ✅
  - **12.3.3** Set disposition "Discharged Home" → /billing shows 2 OECB entries with sky badge totaling ₱13,000.00 ✅
  - **12.3.4** (Regression) Single OECB entry still works as before ✅
- **[E2E TEST — OR Schedule Verification]** ORS-00001 visible in /or/schedule: Scheduled, OR-1, Gonzales, Carlos, Appendectomy, Dr. Dela Cruz ✅
- **[DOCS]** Updated `ragdata.md` (this entry), `tables.md`, `e2e-test-plan.md` (ER + OECB results).

### 2026-06-27 (Hospital Transfer Module — Inter-Facility Transfers)
- **[FEATURE]** Created complete Hospital Transfer module for inter-facility patient transfers:
  - **Migrations (2 new — RUN):**
    - `2026_06_27_000001_create_hospitals_table.php` ✅ — Creates `hospitals` table with: code, name, address, contact_number, hospital_type (Primary/Secondary/Tertiary/Specialty), is_active, audit columns. Composite unique: `(code, tenant_id, history)`.
    - `2026_06_27_000002_create_hospital_transfers_table.php` ✅ — Creates `hospital_transfers` table with: transfer_code (HT-xxxxx), patient_id (FK→patients), destination_hospital_id (FK→hospitals), source_type (polymorphic: er_admission/admission/opd_consultation/or_schedule/ob_consultation/dialysis_session/dr_schedule), source_id, transfer_reason, transfer_diagnosis, transferred_by (FK→hcp), accompanying_personnel, transport_mode (Ambulance/Private/Walk-in/Air/Other), condition_on_transfer, status (Pending/In Transit/Completed/Cancelled), transfer_datetime, completed_datetime, notes. Composite unique: `(transfer_code, tenant_id, history)`.
  - **Models (2 new):**
    - `App\Models\Hospital` — Simple model with `$table = 'hospitals'`, `$timestamps = false`, `$guarded = []`.
    - `App\Models\HospitalTransfer` — BelongsTo: patient(), destinationHospital(), transferredBy() (HealthCareProfessional). MorphTo: source() (polymorphic). Global scope history=1. Casts: transfer_datetime and completed_datetime as datetime.
  - **Livewire Components (2 new):**
    - `App\Livewire\Masters\HospitalCrud` — Simple CRUD (112 lines) following GenderCrud pattern with search, sort, pagination, modal create/edit, hospital_type dropdown (Primary/Secondary/Tertiary/Specialty).
    - `App\Livewire\Transfer\HospitalTransferCrud` — Full CRUD (200+ lines) with: patient fuzzy search, source type dropdown (7 encounter types), hospital dropdown (active only), doctor dropdown (from HCP), auto-generated transfer_code (HT-xxxxx), status management (Pending/In Transit/Completed/Cancelled), transport mode selector, per-tenant scoped.
  - **Blade Views (4 new):** `hospital-crud.blade.php` (emerald-themed), `hospital-transfer-crud.blade.php` (amber-themed), `pages/masters/hospital.blade.php`, `pages/transfer/hospital-transfers.blade.php`.
  - **Routes:** Added `GET /masters/hospital` → `masters.hospital` in masters group; new `GET /transfers` → `transfers.index` in transfers group.
  - **Sidebar:** Added "Hospital" link under Masters sub-menu (after Relationship); new "Transfers" top-level menu item (amber theme, ambulance SVG icon) between Reports and Masters.
- **[DOCS]** Updated `ragdata.md` (this entry) and `tables.md` (new hospitals and hospital_transfers tables).

### 2026-06-27 (Doctors Portal — Bug Fixes: Auth Check, Atomic Codes, Blade References, full_name Accessor)
- **[FIX]** Added `getFullNameAttribute()` accessor to `Patient` model (`app/Models/Patient.php:16`) — constructs full name from `first_name`, `middle_name`, `last_name`, `suffix`. Resolves `$patient->full_name` calls used across 40+ locations.
- **[FIX]** Fixed blade template column references in `dashboard-crud.blade.php`:
  - ER tab: `admission_date` → `admission_datetime` (ErAdmission model uses `admission_datetime`)
  - Ward tab: `admission_datetime` → `admission_date` (Admission model uses `admission_date`)
- **[FIX]** Fixed `patient-chart-crud.blade.php` display fields:
  - MRN label: `patient_id` → `patient_code` (patient_id is PK, MRN is patient_code)
  - Gender: `gender` → `gender?->name` (gender is a BelongsTo relationship, not a direct column)
- **[FIX]** Added physician authorization check in `PatientChartCrud::loadRecord()` — verifies `auth()->user()->health_care_professional_id` matches `attending_physician_id` (OPD) or `attending_physician_id`/`admitting_physician_id` (ER/Ward). Sets `record = null` if unauthorized, triggering 404.
- **[FIX]** Fixed non-atomic request code generation in `submitOrder()` — replaced `count() + 1` with auto-increment `id`-based codes (`'LR-' . str_pad($req->id, 5, '0', STR_PAD_LEFT)`), eliminating race condition duplicates under concurrent access.
- **[FIX]** Added `patient_id` to lab (`LabRequest`) and radiology (`RadiologyRequest`) order creation in `submitOrder()` — both models have `patient()` BelongsTo relationships via `patient_id` column, but the field was missing from the create array.
- **[FIX]** Fixed patient search in `DashboardCrud.php` — changed from non-existent `full_name` to `first_name`/`last_name`/`patient_code` matching.
- **[DOCS]** Updated `ragdata.md` and `tables.md` (Patient model full_name accessor).

### 2026-06-26 (PhilHealth Auto-EClaims — Member Toggle + Draft Claim on Patient Registration)
- **[FEATURE]** Added `is_philhealth_member` toggle to both patient registration paths (PatientCrud + ER Triage).
- When toggled ON, the PhilHealth PIN/Member fields become visible; when OFF they are hidden.
- **Auto-claim creation**: `PhilhealthClaim::ensureDraftForPatient()` — when a patient is registered as a PhilHealth member, a draft claim (`status='Draft'`, `claim_type='ACR'`) is automatically created and appears in the eClaims list.
  - Method checks for existing draft claims to avoid duplicates.
  - Auto-generates claim code (`PHC-xxxxx`) from the same sequence as manual claims.
  - Sets `patient_id`, `philhealth_member_id` (if linked), `tenant_id`, audit columns.
- **Files modified**:
  - `app/Models/PhilhealthClaim.php:118-142` — Added `ensureDraftForPatient()` static method.
  - `app/Models/Patient.php:76-78` — Added `philhealthClaims()` HasMany relation.
  - `app/Livewire/PatientCrud.php` — Added `is_philhealth_member` property; `save()` calls `ensureDraftForPatient()` on create.
  - `app/Livewire/Er/ErTriageRecordCrud.php` — Added `is_philhealth_member` property; `save()` calls `ensureDraftForPatient()` when creating new patients.
  - `resources/views/livewire/patient-crud.blade.php` — Toggle in Identity tab; conditional member record + PIN fields.
  - `resources/views/livewire/er/er-triage-record-crud.blade.php` — Toggle in quick registration form; conditional PIN field.
- **Workflow**: Register patient → toggle "PhilHealth Member" → enter PIN → save → draft claim auto-created → appears in eClaims list (ClaimCrud) → modify & fill remaining info.

### 2026-06-26 (Cross-Encounter Validation — Active Encounter Conflict Detection)
- **[FEATURE]** Created `App\Livewire\Concerns\ChecksActiveEncounters` trait (`ensureNoActiveEncounter(int $patientId)`) that prevents a patient from having multiple simultaneous active encounters across modules.
- **Validation logic** (scoped by tenant):
  - `er_admissions`: `disposition = 'Pending'` = active
  - `admissions`: `disposition = 'Pending'` = active
  - `opd_consultations`: `disposition IS NULL` = active
  - `ob_consultations`: `disposition = 'Pending'` = active
  - `dr_schedules`: `disposition = 'Pending'` = active
- **Integration points** (all call `ensureNoActiveEncounter()` before insert):
  - `ErTriageRecordCrud::save()` — after patient creation, only on `!isEditing`
  - `AdmissionCrud::saveAdmit()` — after fetching `ErAdmission`, before creating Admission
  - `OpdConsultationCrud::saveEncounter()` — after validation, before `OpdConsultation::create()`
  - `ObConsultationCrud::save()` — after validation, before `ObConsultation::create()`
  - `DeliveryScheduleCrud::save()` — after validation, only in `else` (new) branch
- Throws `addError('patient_id', ...)` listing all active encounters found. User must dispose existing encounter(s) before creating a new one.

### 2026-06-26 (Patient Code Format — Year + 10-Digit Sequential)
- **[CHANGE]** Patient code format changed from `PT-{date}-{rand}` / `PT-{5-digit}` to `{YEAR}{10-DIGIT SEQUENTIAL}` (e.g. `20260000000001`).
- Updated `PatientCrud::generatePatientCode()` — queries last code matching current year prefix, extracts 10-digit sequence from position 4, increments by 1.
- Updated `ErTriageRecordCrud` — added matching `generatePatientCode()` method; inline `'PT-' . now()->format('Ymd') . '-' . rand(1000, 9999)` replaced with `$this->generatePatientCode()`.

### 2026-06-26 (DR Schedule 500 Error — Duplicate Treatment Form Fix)
- **[FIX]** `/delivery/schedule` returned `500 Internal Server Error` — `MultipleRootElementsDetectedException` for `delivery.delivery-schedule-crud`. Root cause: 271 lines of duplicate treatment form content (lines 936–1207) were pasted after the root `</div>` closing tag, creating a second root element. Livewire 3 only allows one root HTML element per component.
- **[FIX]** Removed the duplicate copy (tabs, clinical notes, neonatal fields, orders grid, disposition, all 5 order modals, disposition modal). The component now has a single root `<div>` containing the `@if(showTreatmentForm)/@else(datagrid)/@endif` branching.

### 2026-06-26 (Clinical Data Cleanup — ClearScript + Model/FK Fixes)
- **[TOOL]** Created `php artisan clinical:clear` command (`App\Console\Commands\ClearClinicalData`) to wipe all patient-facing clinical data while preserving reference/system tables. Clears 38 tables in dependency order (children before parents), resets identity seeds via `DBCC CHECKIDENT`. Supports `--force` flag to skip confirmation.
- **[SCRIPT]** Created `clear-clinical.bat` (Windows) and `clear-clinical.sh` (Git Bash) convenience scripts.
- **[FIX]** `billing_invoices` was missing from the clear list — added after `billing_payments`.
- **[USAGE]** Ran `php artisan clinical:clear --force` — deleted 108 records across 38 tables (patients, ER, OPD, admissions, nursing, dialysis, OR, requests, billing, demo data).
- **[MODEL FIX]** `ErAdmission`, `Admission`, `ObConsultation` — added missing `drSchedules()` HasMany + imports.
- **[MODEL FIX]** `OrSchedule` — added missing `obConsultation()` BelongsTo + import.
- **[OB MODULE COMPLETION]** Created `resources/views/pages/ob/consultations.blade.php` page wrapper, added `GET /ob/consultations` route → `ob.consultations`, added "Consultations" sidebar link under OB sub-menu.
- **[DOCS]** Updated `ragdata.md` and `tables.md` (drSchedules on ErAdmission/Admission/ObConsultation, obConsultation on OrSchedule).

### 2026-06-26 (Delivery Room (DR) Module — Full Delivery Management Workflow)
- **[FEATURE]** Designed complete Delivery Room (DR) module with full delivery management, neonatal tracking, and treatment workflow:
  - **Migrations (3 new — RUN):**
    - `2026_06_26_115000_create_dr_rooms_table.php` ✅ — Creates `dr_rooms` table with: code, name, location, room_type (Labor Room/Delivery Room/LDR/LDRP/C-Section Suite), capacity, equipment, special_requirements, status (Available/Occupied/Maintenance/Reserved), audit columns.
    - `2026_06_26_115001_create_dr_schedules_table.php` ✅ — Creates `dr_schedules` table with: schedule_code (DRS-xxxxx), dr_room_id, patient_id, er_admission_id, admission_id, ob_consultation_id, attending_physician_id, pediatrician_id, anesthesiologist_id, midwife_id, delivery_type (NSVD/C/S/AVB/Breech/Vacuum/Forceps/Others), delivery_description, scheduled_date, start_time, end_time, status (Scheduled/In Progress/Completed/Cancelled), OB history fields (gestational_age, lmp_date, edc_date, gravida, para, full_term, preterm, abortion, living_children), pre/intra/post_op_notes, neonatal fields (baby_sex, baby_weight, baby_length, head_circumference, apgar_score_1min/5min/10min), delivery_complications, newborn_outcome (Live Birth/Stillbirth/Early Neonatal Death/Late Neonatal Death), clinical notes (HPI, PE, assessment, orders_summary, treatment_notes), disposition (Pending/Delivered/Admitted to Ward/Admitted to NICU/Discharged Home/Transferred/AMA/Expired/Cancelled), discharge_instructions, disposition_datetime, cancellation_reason, audit columns.
    - `2026_06_26_115002_add_dr_schedule_id_to_requests_tables.php` ✅ — Adds `dr_schedule_id` FK (SET NULL) to lab_requests, csr_requests, radiology_requests, pharmacy_requests, procedure_requests, billing_invoices. **[FIX]** Migration initially used `$t->foreign("fk_{$table}_dr_schedule")` which passed constraint name as column name — corrected to `$t->foreignId('dr_schedule_id')->constrained('dr_schedules')->onDelete('SET NULL')->index(...)`.
  - **Models:**
    - `App\Models\Delivery\DeliveryRoom` — With relationships: hasMany schedules (DrSchedule). Global scope history=1.
    - `App\Models\Delivery\DeliverySchedule` — With relationships: patient, drRoom, erAdmission, admission, obConsultation, attendingPhysician (HCP), pediatrician (HCP), anesthesiologist (HCP), midwife (HCP); HasMany → labRequests, csrRequests, radiologyRequests, pharmacyRequests, procedureRequests. Global scope history=1.
  - **Livewire Components (2 new):**
    - `App\Livewire\Delivery\DeliveryRoomCrud` — Full CRUD matching OR Room pattern with room type dropdown, equipment/special requirements textareas, status management.
    - `App\Livewire\Delivery\DeliveryScheduleCrud` — Full CRUD with:
      - Patient search autocomplete, create modal with OB history fields (G/P/FT/PT/A, gestational age, LMP, EDC).
      - Treatment form with 4 Alpine.js tabs (rose/pink maternal theme): Clinical Notes, Orders & Interventions (5 order types), Neonatal Outcome, Disposition.
      - Delivery-specific fields: delivery type, attending/pediatrician/midwife dropdowns, pre/intra/post-op notes.
      - Neonatal outcome tracking: baby sex, weight, length, head circumference, Apgar scores (1/5/10 min), delivery complications, newborn outcome.
      - 5 order modals (Lab, CSR, Radiology, Pharmacy, Procedure) matching OR/OB pattern.
      - Department resolver using `OB` department code (DR is part of OB/GYN).
      - Auto-generated codes: schedule `DRS-xxxxx`, orders reuse existing `LR-`/`CR-`/etc. patterns.
      - Disposition: Pending, Delivered, Admitted to Ward, Admitted to NICU, Discharged Home, Transferred, AMA, Expired, Cancelled.
  - **Blade Views (4 new):** `delivery-room-crud.blade.php`, `delivery-schedule-crud.blade.php`, `pages/delivery/rooms.blade.php`, `pages/delivery/schedule.blade.php` — Rose/pink maternal theme, datagrid with Active/Completed toggle, treatment form with 4 tabs, order modals, neonatal outcome section.
  - **Updated Models:** `ErAdmission`, `Admission`, `ObConsultation` — each gains `drSchedules()` HasMany relationship.
  - **Routes:** Added `GET /delivery/rooms` → `delivery.rooms` and `GET /delivery/schedule` → `delivery.schedule` in `routes/web.php`.
  - **Sidebar:** Added "Rooms" and "Schedule" links under Delivery sub-menu; changed Delivery menu to rose/pink theme.
- **[DOCS]** Updated `ragdata.md` and `tables.md` (new DR module section with dr_rooms, dr_schedules tables; dr_schedule_id on lab/csr/radiology/pharmacy/procedure_requests + billing_invoices).

### 2026-06-26 (OB Consultation Module — Full ER-like Treatment Workflow)
- **[FEATURE]** Created complete OB/GYN Consultation module with full ER-like 4-tab treatment workflow:
  - **Migrations (3 new — RUN):**
    - `2026_06_26_114500_create_ob_consultations_table.php` ✅ — Creates `ob_consultations` table with: consultation_code, patient_id, attending_physician_id, visit_type_id, OB fields (gestational_age, lmp_date, edc_date, gravida, para, full_term, preterm, abortion, living_children, obstetrical_history), clinical notes (HPI, PE, assessment, orders_summary, treatment_notes), disposition, audit columns.
    - `2026_06_26_114600_add_ob_consultation_id_to_requests_tables.php` ✅ — Adds `ob_consultation_id` FK (SET NULL) to lab_requests, csr_requests, radiology_requests, pharmacy_requests, procedure_requests, billing_invoices.
    - `2026_06_26_114700_add_ob_consultation_id_to_or_schedules_table.php` ✅ — Adds `ob_consultation_id` FK (SET NULL) to or_schedules.
  - **Model:** `App\Models\Ob\ObConsultation` — With relationships: patient, attendingPhysician, visitType, labRequests, csrRequests, radiologyRequests, pharmacyRequests, procedureRequests, orSchedules, billingInvoices. Global scope `history=1`.
  - **Livewire Component:** `App\Livewire\Ob\ObConsultationCrud` (~850 lines) — Full CRUD with:
    - Patient search autocomplete, create modal with OB fields (G/P/FT/PT/A, gestational age, LMP, EDC).
    - Treatment form with 4 Alpine.js tabs (rose/pink theme): Clinical Notes (OB fields + HPI/PE/Assessment/Treatment Notes), Orders & Interventions (5 order types with inline status management), OR Schedule (datagrid + Schedule OR modal), Disposition.
    - 5 order modals (Lab, CSR, Radiology, Pharmacy, Procedure) with CF4 frequency/route per drug.
    - OR Schedule modal with OR room selector, surgeon/anesthesiologist, pre/intra/post-op notes.
    - Disposition: Pending, Delivered, Admitted to Ward, Admitted to ICU, Discharged Home, AMA, Expired.
    - Department resolver using `OB` department code.
    - Auto-generated codes: consultation `OBC-xxxxx`, orders `LR-`/`CR-`/`RR-`/`PR-`/`PRCD-xxxxx`, OR schedules `ORS-xxxxx`.
  - **Blade View:** `resources/views/livewire/ob/ob-consultation-crud.blade.php` — Full blade with rose/pink theme, datagrid (Active/Completed toggle), treatment form with sticky save button, all modals.
  - **Page Wrapper:** `resources/views/pages/ob/consultations.blade.php` — `<livewire:ob.ob-consultation-crud />`
  - **Route:** `GET /ob/consultations` → `ob.consultations` (via `ObConsultationCrud` Livewire component).
  - **Sidebar:** Added "Consultations" link under OB sub-menu.
  - **Model Update:** `OrSchedule` — added `obConsultation()` BelongsTo relationship.
- **[DOCS]** Updated `ragdata.md` and `tables.md` (new table 56.5 `ob_consultations`, ob_consultation_id on or_schedules + 5 request tables + billing_invoices).

### 2026-06-26 (ICU & MICU Sidebar Menus Hidden)
- **[HOUSEKEEPING]** Both ICU and MICU sidebar sub-menus in `app.blade.php` are now wrapped in Blade comments (`{{-- --}}`) to hide them from the navigation until further notice. The routes and Livewire components remain functional — only the sidebar links are hidden.

### 2026-06-26 (ER Area — OR Schedule Tab)
- **[FEATURE]** Added 4th tab "OR Schedule" to ER treatment form (alongside Clinical Notes, Orders & Interventions, Disposition):
  - **Model Updates**:
    - `ErAdmission` — added `orSchedules()` HasMany relation to `App\Models\Or\OrSchedule` via `er_admission_id`.
  - **Livewire Component (`ErAdmissionCrud`)** — Added:
    - OR Schedule form fields and modal (OR room, surgeon, anesthesiologist, procedure type/description, date/time, pre/intra/post-op notes).
    - `openOrScheduleForm()` / `saveOrSchedule()` / `closeOrScheduleModal()` methods.
    - `generateOrScheduleCode()` — auto-generates `ORS-xxxxx` codes (shared sequence with Nursing/OR module).
    - Render: loads `erOrSchedules` for current ER admission + `orRooms` + `orDoctors`.
  - **ER Blade** — Added:
    - 4th tab button with hospital-building SVG icon.
    - 4th tab content: datagrid of OR schedules (Schedule #, OR Room, Procedure, Surgeon, Anesthesiologist, Date/Time, Status badges), empty state, "Schedule OR" button.
    - OR Schedule modal with OR room selector, date/time pickers, surgeon/anesthesiologist dropdowns, procedure type/description inputs, pre/intra/post-op notes.
  - **Integration**: OR schedules created from ER appear automatically in the OR Schedule module (`OrScheduleCrud`) since they share the same `or_schedules` table. Uses `er_admission_id` FK (already existed in `or_schedules`).
- **[DOCS]** Updated `ragdata.md` and `tables.md` (ErAdmission orSchedules relation).

### 2026-06-26 (Nursing — OR Schedule Tab for In-Patients)
- **[FEATURE]** Added 6th tab "OR Schedule" to Nursing detail view (admitted patients), alongside Vitals, Care Notes, Orders, MAR, and Discharge:
  - **Migration (1 new):**
    - `2026_06_26_071045_add_admission_id_to_or_schedules_table.php` — Adds `admission_id` (unsignedBigInteger, nullable, FK → admissions ON DELETE NO ACTION) + index to `or_schedules` table, enabling OR schedules to be linked from nursing admissions.
  - **Model Updates**:
    - `OrSchedule` — added `admission()` BelongsTo.
    - `Admission` — added `orSchedules()` HasMany; added `OrSchedule` import.
  - **Nursing Livewire Component (`NursingCrud`)** — Added:
    - OR Schedule form fields and modal (OR room, surgeon, anesthesiologist, procedure type/description, date, time).
    - `openOrScheduleForm()` / `saveOrSchedule()` / `closeOrScheduleModal()` methods.
    - `generateOrScheduleCode()` — auto-generates `ORS-xxxxx` codes.
    - Render: loads `admissionOrSchedules` for current admission + `orRooms` + `orDoctors`.
  - **Nursing Blade** — Added:
    - Tab 6 button with hospital-building SVG icon.
    - Tab 6 content: datagrid of OR schedules for the current admission (Schedule #, OR Room, Procedure, Surgeon, Date/Time, Status with color-coded badges), empty state, "Schedule OR" button.
    - OR Schedule modal with OR room selector, date/time pickers, surgeon/anesthesiologist dropdowns, procedure type/description inputs.
  - **Integration**: OR schedules created from Nursing appear automatically in the OR Schedule module (`OrScheduleCrud`) since they share the same `or_schedules` table. Clinical data entry (treatment workflow, orders, disposition) can be performed from the OR module.
- **[DOCS]** Updated `ragdata.md` and `tables.md` (or_schedules admission_id column, admissions orSchedules relation).

### 2026-06-26 (MICU Patient Module — Treatment Workflow + Clinical Assessments)
- **[FEATURE]** Created dedicated MICU Patient Management module with admission-based patient list filtered to MICU ward, full treatment workflow, and MICU-specific clinical assessment recording:
  - **Migration (1 new):**
    - `2026_06_26_000113_create_micu_assessments_tables.php` — Creates `micu_assessments` (tenant_id, admission_id FK→admissions CASCADE, assessed_by FK→hcp, assessed_at, notes) and `micu_assessment_values` (micu_assessment_id FK CASCADE, micu_assessment_parameter_id FK NO ACTION, value, unit).
  - **Models**: `MicuAssessment` (BelongsTo admission, assessedBy; HasMany values), `MicuAssessmentValue` (BelongsTo assessment, parameter).
  - **Livewire Component (`MicuPatientCrud`)** — ~500 lines with:
    - MICU patient datagrid filtered to patients admitted in MICU beds (via icu_beds → icu_wards code='MICU'), with search, sort, pagination
    - Full treatment workflow: `openTreatment()`, `saveTreatment()`, `closeTreatment()` with 5 clinical notes fields (HPI, Physical Exam, Assessment, Orders Summary, Treatment Notes)
    - 5 order modals (Lab, CSR, Radiology, Pharmacy, Procedure) with item checkboxes, quantity inputs, charge display, priority/notes
    - Inline order datagrids with per-item status dropdowns and cancel buttons
    - MICU assessment recording: `openAssessment()`/`saveAssessment()` with dynamic parameter inputs grouped by category from `micu_assessment_parameters`
    - Disposition management with status (Pending/Completed/Transferred/Discharged/Cancelled), discharge instructions
    - `refreshAdmissionOrders` event listener for real-time order status sync from dispensing modules
  - **Blade View** — Rose/pink-themed 4-tab Alpine.js layout (Clinical Notes, Orders & Interventions, Assessments, Disposition). Assessment modal with dynamic parameter inputs. 5 color-coded order modals. Disposition modal. MICU datagrid with "Treatment" button.
  - **Route + Sidebar**: Added `GET /micu/patients` → `micu.patients` route and "Patients" sidebar link under MICU sub-menu.
  - **Reuses `admissions` table** for treatment workflow columns (migration 112 clinical fields) — no separate ICU admission table needed.
  - **Reuses existing `admission_id` FK** on all 5 request tables and billing — no new FK migration needed.
- **[FIX]** Fixed incorrect column names in orderBy() calls across `MicuPatientCrud` and `AdmissionCrud` — `test_name`→`name`, `item_name`→`name`, `exam_name`→`name`, `drug_name`→`generic_name`, `procedure_name`→`name` (LabTest, Item, RadiologyExamType, Drug, Procedure models).
- **[FIX]** Fixed incorrect relationship names — `LabTest::with('section')`→`with('labSection')`, `Item::with('category')`→`with('itemCategory')`.
- **[DOCS]** Updated `ragdata.md` and `tables.md` (MICU assessment tables, admissions relations update).

### 2026-06-26 (Admission Treatment Workflow — ICU/Ward Clinical Notes, Orders, Disposition)
- **[FEATURE]** Implemented full treatment workflow for Ward/ICU Admissions, replicating the ER/Renal Care/OR pattern with 3-tab form, 5 order types, and disposition management:
  - **Migration (1 new):**
    - `2026_06_26_000112_add_clinical_fields_to_admissions_table.php` — Added 8 new columns to `admissions`: `history_of_present_illness`, `physical_exam_findings`, `assessment`, `orders_summary`, `treatment_notes` (text), `disposition` (varchar 30, default 'Pending'), `discharge_instructions` (text), `disposition_datetime` (timestamp).
  - **No new FK migration needed** — All 5 request tables already have `admission_id` from migration 080, `billing_invoices` already has `admission_id` from migration 075. All request models already have `admission()` BelongsTo + patient name fallback. `Admission` model already has HasMany relationships for all 5 order types. `BillingInvoice` already has `admission()` BelongsTo. BillingCrud already collects `admission_id` from all source types.
  - **Model Updates**: `Admission` — added `disposition_datetime` to `$casts`.
  - **Livewire Component (`AdmissionCrud`)** — Major enhancement (~250 lines → ~600 lines) with:
    - Treatment form: `openTreatment()`, `saveTreatment()`, `closeTreatment()` with 5 clinical notes fields (HPI, Physical Exam, Assessment, Orders Summary, Treatment Notes)
    - 5 order modals (Lab, CSR, Radiology, Pharmacy, Procedure) with item checkboxes, quantity inputs, charge display, priority/notes
    - Inline order datagrids with per-item status dropdowns (Pending → Collected/Processing/Completed for Lab; Pending → Approved/Partial/Complete for others) and cancel buttons
    - Order code generation: `LR-`/`CR-`/`RR-`/`PR-`/`PRCD-` + 5-digit zero-padded sequential
    - Disposition management with status (Pending/Completed/Transferred/Discharged/Cancelled), discharge instructions, `openDisposition()`/`saveDisposition()`
    - `refreshAdmissionOrders` event listener for real-time order status sync from dispensing modules
    - All existing CRUD functionality preserved (admit/discharge/detail/search/sort/pagination/ICU-Ward selectors)
  - **Blade View** — Major enhancement (~363 lines → ~1100+ lines) with: indigo-themed patient summary card (admission code, patient, location, attending), 3-tab Alpine.js treatment form (Clinical Notes, Orders & Interventions, Disposition), 5 order modals with color-coded headers (indigo/amber/cyan/emerald/rose), disposition modal, datagrid "Treatment" (indigo) button
  - **Dispensing Integration**: All 5 dispensing CRUDs now dispatch `refreshAdmissionOrders` events to `AdmissionCrud` in addition to existing `refreshErOrders`/`refreshDsOrders`/`refreshOrOrders`.
  - **Billing Integration**: Already handles `admission_id` from all source types — no changes needed.
- **[DOCS]** Updated `ragdata.md` (this entry) and `tables.md` (admissions clinical columns, migration 112, treatment workflow).

### 2026-06-26 (Documentation Cleanup — tables.md FK/Migration Consistency)
- **[DOCS]** Fixed `tables.md` FK summary gaps and missing migration references:
  - Added migration `000111` to all 6 affected tables (lab_requests, csr_requests, radiology_requests, pharmacy_requests, procedure_requests, billing_invoices)
  - Added missing `opd_consultation_id` and `admission_id` FK references to FK summary lines for all 5 request tables + billing_invoices
  - Added missing `opdConsultation` and `admission` Relations entries to procedure_requests and billing_invoices
  - Fixed `philhealth_claims.er_admission_id` inline constraint to include `ON DELETE NO ACTION`
  - Added missing index entries (`opd_consultation_id`, `admission_id`) to indexes lines

### 2026-06-26 (OR Schedule Treatment Workflow — Clinical Notes, Orders, Disposition)
- **[FEATURE]** Implemented full treatment workflow for OR Schedules, replicating the ER/Renal Care pattern with 3-tab form, 5 order types, and disposition management:
  - **Migrations (2 new):**
    - `2026_06_26_000110_add_clinical_fields_to_or_schedules_table.php` — Added 8 new columns to `or_schedules`: `history_of_present_illness`, `physical_exam_findings`, `assessment`, `orders_summary`, `treatment_notes` (text), `disposition` (varchar 30, default 'Pending'), `discharge_instructions` (text), `disposition_datetime` (timestamp).
    - `2026_06_26_000111_add_or_schedule_id_to_requests_tables.php` — Added `or_schedule_id` (unsignedBigInteger, nullable, FK → or_schedules ON DELETE NO ACTION) + index to `lab_requests`, `csr_requests`, `radiology_requests`, `pharmacy_requests`, `procedure_requests`, and `billing_invoices`.
  - **Model Updates**: `OrSchedule` — added `disposition_datetime` cast + HasMany relationships for `labRequests()`, `csrRequests()`, `radiologyRequests()`, `pharmacyRequests()`, `procedureRequests()`. All 5 request models — added `orSchedule()` BelongsTo + fallback in `getPatientNameAttribute()` (5th priority, after dialysisSession, before direct patient). `BillingInvoice` — added `orSchedule()` BelongsTo.
  - **Livewire Component (`OrScheduleCrud`)** — Major enhancement with:
    - Treatment form: `openTreatment()`, `saveTreatment()`, `closeTreatment()` with 5 clinical notes fields (HPI, Physical Exam, Assessment, Orders Summary, Treatment Notes)
    - 5 order modals (Lab, CSR, Radiology, Pharmacy, Procedure) matching the ER/Renal Care pattern with item checkboxes, quantity inputs, charge display, priority/notes
    - Inline order datagrids with per-item status dropdowns (Pending → Collected/Processing/Completed for Lab; Pending → Approved/Partial/Complete for others) and cancel buttons
    - Order code generation: `LR-`/`CR-`/`RR-`/`PR-`/`PRCD-` + 5-digit zero-padded sequential
    - Disposition management with status (Pending/Completed/Transferred/Discharged/Cancelled), discharge instructions, `openDisposition()`/`saveDisposition()`
    - `refreshOrOrders` event listener for real-time order status sync from dispensing modules
    - `wire:poll.30s` auto-refresh on orders tab
    - Department resolver using `OR` department code
    - All existing CRUD functionality preserved (create/edit/delete/datagrid/search/patient-fix)
  - **Blade View** — Major enhancement with: emerald-themed patient summary card (schedule code, patient, procedure, surgeon, anesthesiologist, date/time), 3-tab Alpine.js treatment form (Clinical Notes, Orders & Interventions, Disposition), 5 order modals, disposition modal, datagrid "Treatment" (emerald) + "Dispo" (green) action buttons
  - **Dispensing Integration**: All 5 dispensing CRUDs (Lab, CSR, Radiology, Pharmacy, Procedure) now dispatch `refreshDsOrders` and `refreshOrOrders` events in addition to `refreshErOrders` — covering dialysis session and OR schedule order updates.
  - **Billing Integration**: `getUnbilledRequestsProperty()` now collects `dialysis_session_id` and `or_schedule_id` from all 5 source types, enabling billing pickup of dialysis and OR-sourced orders.
- **[DOCS]** Updated `ragdata.md` (this entry) and `tables.md` (or_schedules clinical columns, or_schedule_id on 6 request/billing tables).

### 2026-06-26 (Dialysis Session Form Improvements — Fuzzy Patient Search, Auto Code, Default Date)
- **[IMPROVEMENT]** Dialysis Session CRUD patient search changed from `<select>` listing all patients to fuzzy search:
  - Added `patient_search` property; only queries patients matching `%like%` on `last_name`, `first_name`, or `patient_code`.
  - Create mode shows search input with live dropdown; edit mode shows static patient text.
  - Datagrid search also now includes patient name/code matching via `orWhereHas`.
- **[IMPROVEMENT]** Code field now auto-generated (`DS-00001`, `DS-00002`...) via `generateCode()` method, incremented from last record. Manual code input replaced with read-only display.
- **[IMPROVEMENT]** Session date defaults to today's date on create via `now()->format('Y-m-d')`.
- **[FIX]** Removed manual unique validation for `code` (now auto-generated).
- **[FIX]** Added missing `updated_by`/`updated_date` in `DialysisSessionCrud::save()` create array — caused SQL 23000 `NULL` insert error.

### 2026-06-26 (Renal Care Treatment Workflow — Clinical Notes, Orders, Disposition)
- **[FEATURE]** Implemented full treatment workflow for Renal Care Dialysis Sessions, replicating the ER Area pattern with 3-tab form, 5 order types, and disposition management:
  - **Migrations (2 new):**
    - `2026_06_26_000108_add_clinical_fields_to_dialysis_sessions_table.php` — Added 8 new columns to `dialysis_sessions`: `history_of_present_illness`, `physical_exam_findings`, `assessment`, `orders_summary`, `treatment_notes` (text), `disposition` (varchar 30, default 'Pending'), `discharge_instructions` (text), `disposition_datetime` (timestamp).
    - `2026_06_26_000109_add_dialysis_session_id_to_requests_tables.php` — Added `dialysis_session_id` (unsignedBigInteger, nullable, FK → dialysis_sessions ON DELETE NO ACTION) + index to `lab_requests`, `csr_requests`, `radiology_requests`, `pharmacy_requests`, `procedure_requests`, and `billing_invoices`.
  - **[FIX]** Initial migration 000109 used `ON DELETE SET NULL` which caused MSSQL `cycles or multiple cascade paths` error on `csr_requests` (already has 3 SET NULL FKs to er_admissions/opd_consultations/admissions). Changed to `ON DELETE NO ACTION` — resolved.
  - **Model Updates**: `DialysisSession` — added casts for `disposition_datetime` (datetime) + HasMany relationships for `labRequests()`, `csrRequests()`, `radiologyRequests()`, `pharmacyRequests()`, `procedureRequests()`. All 5 request models — added `dialysisSession()` BelongsTo + fallback in `getPatientNameAttribute()`. `BillingInvoice` — added `dialysisSession()` BelongsTo.
  - **Livewire Component (`DialysisSessionCrud`)** — Complete rewrite with:
    - Treatment form: `openTreatment()`, `saveTreatment()`, `closeTreatment()` with 5 clinical notes fields
    - 5 order modals (Lab, CSR, Radiology, Pharmacy, Procedure) with item checkboxes, quantity inputs, charge display, priority/notes
    - Inline order datagrids with per-item status dropdowns (Pending → Collected/Processing/Completed for Lab; Pending → Approved/Partial/Complete for others) and cancel buttons
    - Order code generation: `LR-`/`CR-`/`RR-`/`PR-`/`PRCD-` + 5-digit zero-padded sequential
    - Disposition management with status (Pending/Completed/Transferred/Discharged/Cancelled), discharge instructions, `openDisposition()`/`saveDisposition()`
    - `refreshDsOrders` event listener for real-time order status sync from dispensing modules
    - `wire:poll.30s` auto-refresh on orders tab
    - Department resolver using `NEPHRO` department code
  - **Blade View** — Complete rewrite with: emerald-themed patient summary card, 3-tab Alpine.js treatment form (Clinical Notes, Orders & Interventions, Disposition), 5 order modals (Lab/CSR/Radiology/Pharmacy/Procedure with item checklists), disposition modal, scrollable form body with sticky footer, datagrid "Treatment" (emerald) + "Dispo" (green) action buttons
  - **Preserved existing features**: fuzzy patient search, auto code generation (DS-xxxxx), default session date, create/edit/archive
- **[DOCS]** Updated `ragdata.md` (this entry) and `tables.md` (dialysis_sessions clinical columns, dialysis_session_id on 6 request/billing tables).

### 2026-06-26 (ER Area — Treatment Button Label Update)
- **[UPDATE]** Changed "Treat" button label to "Treatment" in ER Area Actions column for consistency with Renal Care module.

### 2026-06-26 (ER Area Review — Treatment Workflow Confirmed)
- **[REVIEW]** Confirmed ER Area is fully implemented with Treatment workflow:

### 2026-06-26 (OR Schedule — Missing Patient Field in Create/Edit Form)
- **[FIX]** OR Schedule create/edit form was missing a patient selection field despite `patient_id` being a required column (DB: NOT NULL, Validation: `['required']`). The form would fail validation on every save attempt.
  - **Component** (`OrScheduleCrud.php`): Added `patient_search` property, `updatedPatientSearch()` hook (clears patient_id on search change), `selectPatient()` method, patient query in `render()` (searches `last_name`/`first_name`/`patient_code`, top 10), `patient_search` in `resetForm()`.
  - **Blade** (`or-schedule-crud.blade.php`): Added full-width fuzzy patient search input with dropdown results, selected patient display with clear button, error display — matching the same pattern used in Billing, Dialysis Sessions, and other modules.
  - "Treatment" button in Actions column opens full treatment form with 3-tab layout (Clinical Notes, Orders & Interventions, Disposition).
  - All 5 dispensing modules (Lab, CSR, Radiology, Pharmacy, Procedure) dispatch `refreshErOrders` events to ER when items are processed.
  - Billing module picks up ER admissions (including OECB) via `getUnbilledRequestsProperty()` with `er_admission_id` FK.
  - ER treatment form supports inline status updates (Pending → Collected → Processing → Completed) for all order types.
  - Label updated from "Treat" to "Treatment" for clarity.

### 2026-06-25 (Fund Source CRUD — Budget Tracking for Payments)
- **[FEATURE]** Implemented **Fund Source** CRUD in the Billing module for payment budget tracking:
  - **Migration** `2026_06_25_000106_create_fund_sources_table.php` ✅ — Creates `fund_sources` table with `code`, `name`, `description`, `budget_amount`, `used_amount`, tenant-scoped.
  - **Migration** `2026_06_25_000107_add_fund_source_id_to_billing_payments.php` ✅ — Adds nullable `fund_source_id` FK on `billing_payments`.
  - **Migration** `2026_06_25_165330_add_budget_fields_to_fund_sources.php` ✅ — Adds `budget_amount` and `used_amount` columns to `fund_sources`.
  - **Model** `App\Models\Billing\FundSource` — With `remainingAmount()`, `deductBudget()`, `restoreBudget()` methods.
  - **Livewire Component** `App\Livewire\Billing\FundSourceCrud` — Full CRUD with datagrid showing budget/used/remaining (color-coded), create/edit modal with budget input and used/remaining summary, archive confirmation.
  - **Blade** `fund-source-crud.blade.php` — Datagrid with ₱ formatting, budget progress, emerald-themed CRUD modal, amber archive confirmation.
  - **Route** `GET /billing/fund-sources` → `billing.fund-sources` — Page wrapper + sidebar link under Billing.
  - **Payment Integration** (`BillingCrud`): `addPayment()` now calls `$fundSource->deductBudget($amount)` when a fund source is selected. `confirmDeleteInvoice()` restores budgets via `restoreBudget()` for all payments linked to the cancelled invoice.
  - **Blade** (`billing-crud.blade.php`): Added "Budget Source" dropdown in the Add Payment modal, listing active fund sources.
- **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (table 95 fund_sources, updated table 93 billing_payments fund_source_id).
- **[Bug Fix]** Same `updated_by`/`updated_date` NOT NULL constraint bug as previously fixed in supply CRUDs (ragdata.md line 837) — `FundSourceCrud::create()` was missing `'updated_by'` and `'updated_date'` in the create array, causing SQL 23000 on save. Fixed by adding both fields.

### 2026-06-25 (OECB Billing Integration — Invoice Generation & Demo Data)
- **[FEATURE]** Integrated OECB ER admissions into the Billing module for invoice generation:
  - **Migration** `2026_06_25_142613_add_billing_fields_to_er_admissions.php` ✅ — Added `billing_id` (nullable unsignedBigInteger) and `billing_status` (varchar 30, default 'Pending') to `er_admissions` after `oecb_fixed_fee`.
  - **BillingCrud** (`app/Livewire/Billing/BillingCrud.php`):
    - `getUnbilledRequestsProperty()` — Added OECB query pulling `is_oecb=true`, `status=Completed`, `billing_status=Pending`, `billing_id=null` ER admissions. Merges into unbilled results with sky-themed badge.
    - `createInvoice()` — Added `elseif ($type === 'oecb')` handler reading `oecb_fixed_fee` as amount, creating invoice item with description `"OECB: [Core Presentation] [[Package Code]]"`, targeting `update_table='er_admissions'`.
    - `confirmDeleteInvoice()` — Added `'OECB' => 'er_admissions'` to the source type → table mapping for invoice cancellation rollback.
  - **Blade** (`billing-crud.blade.php`): Added `'OECB' => 'bg-sky-50 text-sky-700 border border-sky-200/50'` badge style to the For Billing tab source_type match + invoice detail modal source_type badge match.
  - **Demo Data** (`DemoDataSeeder`): Added OECB demo section — creates demo patient (if missing), demo HCP (if missing), and 1 completed OECB ER admission (`ER-OECB-00001`, Fever, ₱5,500, `billing_status=Pending`) ready for billing pickup.
  - **Docs**: Updated `ragdata.md` (changelog), `tables.md` (table 62 billing fields, table 101 billing fields).
  - **[2026-07-06 REFACTOR]** All OECB billing logic updated to use `oecb_entries` table instead of scalar `er_admissions` columns. `update_table` changed from `er_admissions` to `oecb_entries`. Restore mapping changed to `'OECB' => 'oecb_entries'`. See top-level 2026-07-06 entry.

### 2026-06-25 (OECB Module — Outpatient Emergency Care Benefit)
- **[FEATURE]** Implemented the **OECB (Outpatient Emergency Care Benefit)** module — PhilHealth's fixed-fee emergency care package covering 14 core presentations under Annex A & B.
  - **Migrations Applied** (4 new):
    - `2026_06_25_000101_add_oecb_fields_to_er_tables` ✅ — Added `is_oecb` (boolean), `core_presentation` (varchar 200), `oecb_package_code` (varchar 30), `oecb_fixed_fee` (decimal 12,2) to `er_admissions` (after `disposition`).
    - `2026_06_25_000102_add_er_admission_id_to_philhealth_claims` ✅ — Added `er_admission_id` FK → `er_admissions(id)` to `philhealth_claims` (made idempotent since column already existed).
    - `2026_06_25_000103_create_oecb_core_presentations_table` ✅ — Created `oecb_core_presentations` table with code/name/description/sort_order.
    - `2026_06_25_000104_create_oecb_fee_schedules_table` ✅ — Created `oecb_fee_schedules` table with package_code/core_presentation_id/fixed_fee. FK + composite unique on `(package_code, core_presentation_id, history)`.
  - **Models**: `App\Models\Oecb\OecbCorePresentation` (HasMany feeSchedules), `App\Models\Oecb\OecbFeeSchedule` (BelongsTo corePresentation). Both with global scope `history=1` and `$timestamps=false`.
  - **Seeder** (`OecbSeeder`) — Seeded 14 core presentations (DOB/CHP/AMS/SAP/SHD/FVR/TRA/PSN/DEH/SLZ/ALR/OBS/NEO/OTH) with corresponding fee schedules. Package codes OECB-001 to OECB-014, fixed fees ranging ₱5,500–₱13,000. Idempotent via `updateOrCreate`. Added to `DatabaseSeeder` between `ReferenceDataSeeder` and `DemoDataSeeder`.
  - **ER Treatment Form** (`ErAdmissionCrud`) — Added OECB toggle in treatment header (sky theme), core presentation dropdown auto-populates package code + fixed fee from fee schedule via `updatedCorePresentation()` hook. Validates `required_if:is_oecb,true` for core_presentation. Clears OECB data when toggled off.
  - **[2026-07-06 REFACTOR]** Replaced single dropdown with dynamic add/remove entry list via `$oecbEntries[]` array + `OecbEntry` model.
  - **CF4 Auto-Format** (`OecbClaimFormatter`) — Formats chief complaint as `"Declared Complaint [Core Presentation]"` for OECB claims, falls back to raw chief_complaint. `saveFormattedChiefComplaint()` persists to claim record. Used in `XmlBuilder` and CF3/CF4 PDF views via `app(OecbClaimFormatter::class)->format($claim)`.
  - **[2026-07-06]** Updated to list all `oecbEntries` core presentations comma-separated.
  - **eClaims OECB Support** (`ClaimCrud`) — Added `OECB` claim_type option with Section A2 ER admission selector, auto-populated package/fee preview. `er_admission_id` stored on claim. `saveFormattedChiefComplaint()` called after create/update.
  - **[2026-07-06]** ER admission selector now shows all OECB entries with summed total fee.
  - **Migration Fix** — #102 made idempotent: checks `Schema::hasColumn()` before adding `er_admission_id` and wraps FK creation in try/catch to handle existing constraint gracefully.
- **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (tables 101-104, updated table 62 OECB fields, updated table 87 er_admission_id).

### 2026-06-25 (CF4 / eClaims Drug Compliance — Pharmacy Module)
- **[MIGRATION 092]** `2026_06_25_000092_add_eclaims_fields_to_pharmacy_tables` applied successfully.
  - **`pharmacy_request_items`** — added: `generic_name` (varchar 200), `brand_name` (varchar 200), `dosage_form` (varchar 50), `philhealth_item_code` (varchar 50), `dispensed_date` (timestamp). All nullable.
  - **`drugs`** — added: `philhealth_item_code` (varchar 50) for PhilHealth Medicine Library code mapping (eSOA XML `pItemCode` field).
- **[ENHANCEMENT]** ER Area (`ErAdmissionCrud.php` + `er-admission-crud.blade.php`) — Order Medications modal now captures **per-drug Frequency** (text input, e.g. BID, TID, q8h, PRN) and **Route** (dropdown: PO/IV/IM/SC/SL/TOP/PR/INH/NEB) per selected drug. Route pre-fills from drug master.
- **[ENHANCEMENT]** Nursing (`NursingCrud.php` + `nursing-crud.blade.php`) — Place Order → Pharmacy section now shows same per-drug Frequency and Route inputs. Frequency/Route only appear when `orderType === 'Pharmacy'`.
- **[ENHANCEMENT]** `PharmacyDispensingCrud.php` — `processDispense()` now stamps `dispensed_date = now()` at actual dispense time. This is the eSOA **service_date** field required by PhilHealth.
- **[ENHANCEMENT]** All three pharmacy order paths (ER, Nursing, Pharmacy Dispensing CRUD) now denormalize `generic_name`, `brand_name`, `dosage_form`, `philhealth_item_code` at order-creation time — ensuring an audit-proof snapshot for CF4 and eSOA submission even if the drug master is later edited.
- **[COMPLIANCE NOTE]** PhilHealth CF4 Part V requires per-drug: Generic Name, Brand Name, Dosage+Form, Route, Frequency, Quantity, Unit Cost, Total Cost, Service Date. All fields are now captured. The `philhealth_item_code` field on the `drugs` master should be populated from the PhilHealth Medicine Library once available.

### 2026-06-25 (Billing — Manual Charge Visibility Fix)
- **[FIX]** "Add Manual Charge" button was hidden on `Paid` invoices due to an overly strict status check (`status !== 'Paid' && status !== 'Cancelled'`).
  - **Root cause**: The original condition blocked modifications on fully-paid invoices. However, admitted patients may still need additional charges posted even after a partial payment has settled the existing balance.
  - **Resolution**: Simplified `$canModifyCharges` logic — the button is now **visible on all invoice statuses except `Cancelled`**. When a new charge is added via `addManualCharge()`, the existing `recalculateInvoiceTotals()` method automatically recalculates the invoice `total_amount`, `balance`, and flips `status` back from `Paid` → `Partial`/`Unpaid` as appropriate.
  - **Rule**: `$canModifyCharges = $detailInvoice->status !== 'Cancelled'` — applies to both the "Add Manual Charge" button and the per-row "Remove" button on manual charge rows.
  - **Applies to**: `resources/views/livewire/billing/billing-crud.blade.php` (blade condition in invoice detail modal).

### 2026-06-25 (UI/UX Scaling & Bug Fixes — Pharmacy, ER, Nursing)
- **[ENHANCEMENT]** Modal Widths Expanded: Scaled up critical modals across the application to provide better visibility for the newly added CF4 compliance fields (Frequency, Route) and general data entry.
  - **ER Module**: Expanded Order modals (Pharmacy, CSR, Radiology, Lab, Procedure) from `max-w-lg` to `max-w-3xl`.
  - **Nursing Module**: Expanded the Place Order modal from `max-w-2xl` to `max-w-4xl`.
  - **Pharmacy Module**: Expanded Create/Edit Request (`max-w-6xl`), Dispense Medications (`max-w-5xl`), and View Details (`max-w-4xl`) modals for improved data density visibility.
- **[FIX]** Addressed an issue in `nursing-crud.blade.php` where clicking the modal's close/cancel buttons failed to dismiss the 'Place Order' modal. The root cause was an invalid Livewire 3 property assignment syntax (`wire:click="showOrderModal = false"`). It was corrected to use the proper magic method syntax (`wire:click="$set('showOrderModal', false)"`) for all relevant buttons and the backdrop.
- **[FIX]** `ParseError: syntax error, unexpected token "if"` on `/billing` caused by using `{{-- Blade comments --}}` inside a `@php` block. Blade comment syntax is invalid inside `@php` — replaced with standard PHP `//` comments.
- **[ENHANCEMENT]** Added `admission` to the eager-loaded relationships on `$detailInvoice` in `BillingCrud.php` render method (`['patient', 'erAdmission', 'admission', 'items', 'payments', 'manualCharges']`) to support future admission-status-aware logic.

### 2026-06-25 (Schema Fix — Pending Migrations Applied)
- **[FIX]** `billing_manual_charges` table was missing from the database, causing a `500 Internal Server Error` on `/billing` with `SQLSTATE[42S02]: Invalid object name 'billing_manual_charges'`.
  - **Root cause**: Three migrations created on 2026-06-24 were never applied to the database (`Pending` state).
  - **Resolution**: Ran `php artisan migrate` which applied:
    - `2026_06_24_000089_create_billing_manual_charges_table.php` ✅ DONE — creates the `billing_manual_charges` table (ad-hoc charges on invoices).
    - `2026_06_24_000090_add_cf1_cf2_cf3_fields_to_philhealth_tables.php` ✅ DONE — adds CF2/CF3 supplementary fields to `philhealth_claims` (secondary/tertiary ICD-10, HCI signatory, lab/imaging summary, OB delivery fields) and `philhealth_members` (employer contact, employer signature date).

### 2026-06-25 (OPD Clinical Encounters & Orders Integration)
- **[FEATURE]** Implemented the **OPD Clinical Encounters** module (`OpdConsultationCrud`), standardizing outpatient visits based on proven ER and Nursing workflows.
  - **Schema Updates**:
    - Created `opd_consultations` table to track visits, linking patients to clinics and doctors, and directly storing nursing check-in vitals and physician SOAP notes.
    - Updated `lab_requests`, `pharmacy_requests`, `csr_requests`, `radiology_requests`, `procedure_requests`, and `billing_invoices` with an `opd_consultation_id` to allow direct ordering from an OPD visit.
  - **UI/UX Implementation**:
    - Built the `OpdConsultationCrud` Livewire component and Blade view (`opd-consultation-crud.blade.php`), featuring a datagrid of encounters.
    - Integrated **Nursing Check-in Modal** for recording vitals (BP, HR, Temp, SpO2, Weight, Height) and Chief Complaint before the doctor sees the patient.
    - Integrated **Doctor Consultation View** providing structured SOAP note fields (HPI, Physical Exam, Assessment, Treatment Plan, Disposition).
    - Ported the **"ORDERS"** modal functionality from ER Area, allowing physicians to request Labs and Pharmacy directly from the OPD screen. Pharmacy orders enforce the capture of CF4 metadata fields (`frequency`, `route`).
    - Added comprehensive order capability for **CSR (Supplies)**, **Radiology**, and **Procedures** mirroring the ER/Nursing workflows.
  - **Bug Fixes (Post-Implementation)**:
    - Fixed a `500 Internal Server Error` caused by attempting to `orderBy('last_name')` on the `health_care_professionals` table, which uses a single `full_name` column.
    - Fixed a `500 Internal Server Error` (Invalid column name 'updated_at') by setting `public $timestamps = false;` in the `OpdConsultation` model since the project uses custom `created_date` and `updated_date` columns.
    - Corrected namespace imports in `OpdConsultation` and `OpdConsultationCrud` to reflect that `LabRequest`, `LabTest`, `CsrRequest`, and `Item` models are located in the root `App\Models` directory rather than subdirectories.
- **[DOCUMENTATION]** Updated `tables.md` to document the new `opd_consultations` table and relationships.
    - `2026_06_24_000091_add_philhealth_hci_fields_to_tenants_table.php` ✅ DONE — adds PhilHealth HCI accreditation fields to `tenants` (philhealth_hci_code, philhealth_accreditation_no, accreditation_date).
- **[NOTE]** Add Manual Charge button in the invoice detail modal is **conditionally shown only when invoice status is `Unpaid` or `Partial`**. It is hidden for `Paid` and `Cancelled` invoices to prevent modification of finalized records. This is by design.

### 2026-06-24 (Billing — Invoice Print & Payment Receipt Enhancement)
- **[FEATURE]** Wired up full print/receipt workflow for Billing module:
  - **Invoice Print** (`GET /billing/invoice/{id}/print` → `billing.invoice.print`) — already existed but updated eager-loading to include `manualCharges` so manual charges now appear on the printed invoice. Print button moved to the **modal header** (always visible, no scroll required) as an `Invoice` pill button next to the close X.
  - **Payment Receipt** (`GET /billing/payment/{id}/receipt` → `billing.payment.receipt`) — **NEW route** added, loading payment with `invoice.patient`, `invoice.items`, `invoice.manualCharges`. Maps to existing `receipt-print.blade.php` which was created but previously had no route or UI trigger.
  - **Decision — New Tab over Modal Preview** — Chose browser-tab approach over Livewire modal preview because: (a) no Livewire round-trip overhead, (b) native `@media print` CSS already hides UI chrome, (c) staff can save as PDF from print dialog without any extra package, (d) receipt and invoice can be open simultaneously without closing modals.
  - **Print Receipt Buttons** — Added to 2 locations using `group-hover:opacity-100` reveal pattern:
    - **Invoice Detail Modal → Payments table** — teal `Receipt` pill button (hover-reveal per row).
    - **Payments Tab (Tab 3)** — Added 8th action column with teal printer icon (hover-reveal per row, consistent with other modules).
  - **invoice-print.blade.php** — Added **Manual Charges** section between service items and totals: sky-themed sub-header row + charge type badge + description/qty/unit price/total per manual charge row. Only shown when `manualCharges->where('history', 1)->count() > 0`.
  - **Billing UI Modernization** — Added `wire:transition` animations for tab switching and modal reveals to improve the UX feel. Improved empty states for all 4 tabs (Unbilled, Invoices, Payments, Reports) using cleaner SVGs, colored icon backgrounds, and descriptive text.
  - **Nursing Order Bug Fix (documented)** — "Place Order" button was silently disabled because `count($orderSelection) === 0` locked the frontend button before Livewire could re-render after checkbox clicks. Fixed by removing the frontend count-lock; backend validation (`orderSelection` required, min 1 item) now handles it with `@error` display feedback.
- **[ROUTES SUMMARY]** Billing routes as of 2026-06-24:
  - `GET /billing` → `billing` (main SPA)
  - `GET /billing/invoice/{id}/print` → `billing.invoice.print` (invoice print page — opens in new tab)
  - `GET /billing/payment/{id}/receipt` → `billing.payment.receipt` (payment receipt page — opens in new tab)

### 2026-06-24 (CF1–CF5 Printouts / PDF Generation)
- **[FEATURE]** Built printable PDF views for all PhilHealth claim forms (CF1–CF4), complementing the existing CF5 PDF:
  - **CF1 PDF** (`cf1pdf.blade.php`) — Member Registration / Claim Information form with Part I (Member Data), Part II (Patient/Dependent Data), Part III (Member Certification), Part IV (Employer Certification), and signature lines.
  - **CF2 PDF** (`cf2pdf.blade.php`) — Health Care Institution Report with Part I (HCI Info), Part II (Patient Confinement Details including admission/discharge, ICD-10 primary/secondary/tertiary, case rate), Part III (Claim Items table with totals), Part IV (HCI Certification with physicians and signatory).
  - **CF3 PDF** (`cf3pdf.blade.php`) — Clinical Record with Part I (Clinical Abstract: chief complaint, HPI, ROS, past med hx, PE findings, vital signs grid), Part II (Course in the Ward with lab/imaging summary), Part III (OB/Maternity Data conditionally shown), Part IV (Clinical Impression).
  - **CF4 PDF** (`cf4pdf.blade.php`) — Comprehensive Clinical Abstract with Part I (HCI & Patient Info), Part II (Reason for Admission), Part III (Physical Examination with vitals grid), Part IV (Course in the Ward), Part V (Full itemized claim items table), Part VI (Outcome & Certification with all signatures).
  - **Routes** — Added 4 new PDF download routes under `philhealth.*` prefix: `claims/{claim}/cf1pdf`, `cf2pdf`, `cf3pdf`, `cf4pdf` — all using `barryvdh/laravel-dompdf` with proper eager-loading.
  - **Blade View** — Added CF1/CF2/CF3/CF4 preview buttons (rose/indigo/emerald/violet themed) in the claim detail modal, adjacent to the existing CF5 button. Each opens the form preview in a modal via `wire:click` calling `previewCf1/2/3/4()` methods.
  - **Modal Preview** — Added `showPreviewModal` with full-page preview of the form HTML rendered inside a scrollable modal container. Includes a "Download PDF" button in the modal header that opens the actual PDF in a new tab.
- **[DOCS]** Updated `ragdata.md` (changelog) and `cf1-cf5-mapping.md`.

### 2026-06-24 (Manual Charges on Invoices)
- **[FEATURE]** Added **Manual Charges** to Billing module — allows ad-hoc charges to be added directly on a patient's invoice from the invoice detail modal:
  - **Migration** — Created `2026_06_24_000089_create_billing_manual_charges_table.php` (new table #94). ✅ RUN
  - **Model** — `App\Models\Billing\BillingManualCharge` (BelongsTo billingInvoice) with charge_type, description, quantity, unit_price, total_price, notes.
  - **BillingInvoice model** — Added `manualCharges()` HasMany relationship.
  - **Livewire Component** — Updated `App\Livewire\Billing\BillingCrud` with: `openManualChargeForm()`, `addManualCharge()` (creates charge + recalculates invoice totals), `removeManualCharge()` (soft-deletes + recalculates), `recalculateInvoiceTotals()` (sums item totals + manual charge totals, updates total_amount/balance/status).
  - **Blade View** — Added "Manual Charges" section in invoice detail modal with: "Add Manual Charge" button (sky theme), charge type dropdown (Miscellaneous, Administrative Fee, Supply/Materials, Professional Fee, Transport/Escort, Equipment Rental, Room/Accommodation Differential, Others — based on common hospital billing categories), description input, quantity/unit price inputs with live total preview, remove button per charge row with hover-reveal.
  - **Totals Sync** — Adding or removing a manual charge recalculates invoice `total_amount`, `balance`, and `status` (Unpaid/Partial/Paid) to reflect the updated charges.
- **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (table 94).

### 2026-06-23 (E-Claims Phase 1 — CF4 Data Capture)
- **[MODULE BUILT]** PhilHealth E-Claims Phase 1 — Core data capture infrastructure for PhilHealth claim forms CF4:
  - **Migrations** — Created `2026_06_23_000078_create_philhealth_claims_table.php`, `2026_06_23_000079_create_claim_items_table.php`, `2026_06_23_000080_create_claim_batches_table.php`, `2026_06_23_000081_create_claim_documents_table.php` (4 new tables, #87-90). ✅ RUN
  - **Models** — `App\Models\PhilhealthClaim` (BelongsTo patient/philhealthMember/icd10Code/caseRate/claimBatch + HasMany items/documents), `App\Models\ClaimItem` (BelongsTo philhealthClaim), `App\Models\ClaimBatch` (HasMany claims), `App\Models\ClaimDocument` (BelongsTo philhealthClaim). All with global scope history=1.
  - **Livewire Component** — `App\Livewire\Philhealth\ClaimCrud` with full CRUD + CF4 data capture:
    - Datagrid of PhilHealth claims (Claim #, Patient, Status badge, Type, Claimed Amount, Outcome, Date) with search, sort, pagination, detail modal showing full claim + items with totals.
    - **Section A — Patient & Admission Details**: patient search autocomplete, PhilHealth member selector, admission/discharge date+time inputs, admitting/discharge diagnosis, ICD-10 code dropdown, case rate selector.
    - **Section B — CF4 Clinical Data**: History of Present Illness, Pertinent Past Medical History, Physical Examination Findings (per system), Course in the Ward — matching CF4 Parts III-IV.
    - **Section C — Claim Items (CF4 Part V)**: dynamic add/remove rows for Drugs/Medicine, Laboratory, Radiology, Procedure, Supply, Room & Board, Professional Fee, Others — with item code (RVS), name, quantity, unit price, actual amount, claimed amount. Auto-recalculation of totals.
    - **Section D — Outcome & Status**: status state machine (Draft → For Submission → Submitted → Acknowledged → Paid / Denied), claim type (ACR/DRG/Special), outcome (Improved/Recovered/HAMA/Expired/Absconded/Transferred), remarks.
  - **Claim Status State Machine**: 6 states — Draft, For Submission, Submitted, Acknowledged, Paid, Denied.
  - **Code Generation**: Auto-generated PHC-xxxxx claim codes, sequential per tenant.
  - **Items Sync**: Create/edit updates claim items with archive of removed items.
  - **Blade Views** — Created `resources/views/livewire/philhealth/claim-crud.blade.php` (~550 lines) and `resources/views/pages/philhealth/claims.blade.php`.
  - **Routes** — Added `GET /philhealth/claims` named `philhealth.claims`.
  - **Sidebar** — Added "E-Claims" link inside PhilHealth sub-menu (between Members and closing div).
- **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (tables 87-90).

### 2026-06-23 (Billing Module)
- **[MODULE BUILT]** Billing & Invoices — full billing workflow integrating all service dispensing modules:
  - **Migrations** — Created and ran `2026_06_23_000075_create_billing_invoices_table.php`, `2026_06_23_000076_create_billing_invoice_items_table.php`, `2026_06_23_000077_create_billing_payments_table.php` (3 new tables). ✅ RUN
  - **Models** — `App\Models\Billing\BillingInvoice` (HasMany items + HasMany payments + BelongsTo patient/erAdmission/admission), `App\Models\Billing\BillingInvoiceItem` (BelongsTo invoice), `App\Models\Billing\BillingPayment` (BelongsTo invoice). All with global scope history=1.
  - **Livewire Component** — `App\Livewire\Billing\BillingCrud` with 3-tab SPA (Alpine.js):
    - **"For Billing" tab** — aggregated view of all unbilled completed service requests across Lab, Pharmacy, CSR, Radiology, and Procedures modules with checkboxes for multi-select billing.
    - **"Invoices" tab** — sortable datagrid (Invoice #, Patient, Date, Total, Paid, Balance, Status, Actions) with search, pagination, detail modal showing line items + payments + running totals, add payment button, cancel invoice with automated rollback of linked request billing_status to Pending.
    - **"Payments" tab** — datagrid of all payments (Payment Code, Invoice, Patient, Date, Amount, Method, Reference) with search/pagination.
  - **Invoice Creation** — patient search autocomplete, auto-generated INV-xxxxx codes, select-from-unbilled-services workflow, auto-links billing_id + sets billing_status='Billed' on all source requests upon creation.
  - **Payment Recording** — auto-generated PAY-xxxxx codes, Cash/Credit Card/Check/Bank Transfer/HMO/PhilHealth payment methods, real-time invoice balance/status recalculation (Unpaid/Partial/Paid).
  - **Summary Cards** — dashboard cards showing Unbilled Services total, Outstanding Balance, and Total Collected.
  - **Blade Views** — Created `resources/views/livewire/billing/billing-crud.blade.php` (~650 lines) and `resources/views/pages/billing/billing.blade.php`.
  - **Routes** — Added `GET /billing` named `billing`.
  - **Sidebar** — Top-level "Billing" nav item with receipt icon (emerald theme), between Admission and OR menus.
  - **Seed Data** — Added 1 demo invoice with items from completed lab/pharmacy requests and 1 partial cash payment to `DemoDataSeeder`, idempotent via `updateOrCreate`.

### 2026-06-22 (Procedures Dispensing Module)
- **[MODULE BUILT]** Procedures Dispensing — full dispensing workflow for procedure requests from all departments:
  - **Migrations** — Created and ran `2026_06_22_1641_create_procedure_requests_table.php` and `2026_06_22_1642_create_procedure_request_items_table.php` (2 new tables with tenant_id, er_admission_id FK for ER integration, billing_status + billing_id for billing module pickup, procedure-specific fields: quantity, qty_given, category_name, unit_charge). ✅ RUN
  - **Models** — Created `App\Models\Procedures\ProcedureRequest` (HasMany items + BelongsTo requestingDepartment, requestingDoctor, erAdmission) and `App\Models\Procedures\ProcedureRequestItem` (BelongsTo procedureRequest + BelongsTo procedure). Global scopes history=1.
  - **Livewire Component** — `App\Livewire\Procedures\ProcedureDispensingCrud` with full CRUD. Features: auto-generated request codes (PRCD-XXXXX), department/doctor dropdowns, procedure selector grouped by category (clickable pill buttons from procedures table with category context), dynamic line items with editable quantity and unit charge, real-time total calculation with subtotals per item, item-level status management (Pending → Approve/Complete → Partial/Complete → Done), item status cascade to parent (all Complete → Complete; mixed → Partial; all Cancelled → Cancelled), per-item status buttons in detail modal, bulk Complete All with confirmation, cancel support (cascading to items with confirmation dialog), billing status dropdown (Pending/Billed/Exempt/Credited) with wallet icon dropdown.
  - **Blade Views** — Created `resources/views/livewire/procedures/procedure-dispensing-crud.blade.php` with full datagrid (Request Code, Department, Procedures Count, Total Amount, Status, Billing, Actions), create/edit modal with procedure pill selector grouped by category, detail modal with item status management, cancel confirmation modal. Created `resources/views/pages/procedures/dispensing.blade.php` page view.
  - **Routes** — Added `GET /procedures/dispensing` under `procedures.*` route prefix.
  - **Sidebar** — Added "Dispensing" link under the Procedures submenu.
  - **Seed Data** — Added 3 procedure request demos with 5 items total (ECG/CHEST-XR/ABDOMEN-US) to `DemoDataSeeder`, idempotent via `updateOrCreate`.
- **[FEATURE]** Added Procedure ordering to ER Area (Orders & Interventions tab) — follows same pattern as Lab/CSR/Radiology/Pharmacy orders:
  - **ErAdmission.php** — added `procedureRequests()` HasMany relationship
  - **ErAdmissionCrud.php** — added Procedure order modal with procedure checklist (grouped by category), per-item quantity inputs, notes field; `requestProcedureOrder()` creates PRCD-xxxxx request with items using `generateProcedureRequestCode()`; `updateProcedureItemStatus()` and `cancelProcedureRequest()` for inline status management; `generateProcedureRequestCode()` private method consistent with other code generators
  - **View** — added Procedure Orders card (indigo theme, Request #, Procedures, Status, Billing, Total, Date, Actions) with inline per-item status change dropdowns (Pending → Approved/Complete/Cancel, Approved → Partial/Complete, Partial → Complete) and Cancel button; added Order Procedure modal with procedure checklist showing category context and standard charges
  - **Status Sync** — `refreshErOrders` dispatched from `ProcedureDispensingCrud` on create/update/cancel/billing-change; `wire:poll.30s` auto-refresh on the orders tab

---

### 2026-06-22 / OR Module

- **[MODULE BUILT]** **OR (Operating Room) Module** — complete OR management with rooms and scheduling:
  - **Database Migrations**:
    - `2026_06_22_170000_create_or_rooms_table.php` — `or_rooms` table with code, name, location, room_type, capacity, equipment, special_requirements, status (Available/Occupied/Maintenance/Reserved), audit columns. Unique on `(code, tenant_id, history)`.
    - `2026_06_22_170001_create_or_schedules_table.php` — `or_schedules` table with schedule_code (ORS-xxxxx), or_room_id, patient_id, er_admission_id (nullable), surgeon_id, anesthesiologist_id, procedure_type, procedure_description, scheduled_date, start_time, end_time, status (Scheduled/In Progress/Completed/Cancelled), pre/intra/post-op notes, cancellation_reason, audit columns. FKs use ON DELETE NO ACTION.
  - **Models**: `App\Models\Or\OrRoom` (hasMany → schedules), `App\Models\Or\OrSchedule` (belongsTo → orRoom, patient, erAdmission, surgeon, anesthesiologist). Both with global scope, $timestamps=false.
  - **Livewire Components**: `App\Livewire\Or\OrRoomCrud` (full CRUD with room type dropdown, equipment/special requirements textareas, status management), `App\Livewire\Or\OrScheduleCrud` (full CRUD with OR room selector, patient search, surgeon/anesthesiologist selects, procedure type dropdown, time pickers, pre/intra/post-op notes, cancellation reason, auto-generated ORS-xxxxx code).
  - **Views**: `resources/views/livewire/or/or-room-crud.blade.php` + `resources/views/pages/or/rooms.blade.php`, `resources/views/livewire/or/or-schedule-crud.blade.php` + `resources/views/pages/or/schedule.blade.php`.
  - **Routes**: `/or/rooms` (named `or.rooms`), `/or/schedule` (named `or.schedule`) — prefixed under `or.*`.
  - **Sidebar**: Collapsible "OR" sub-menu between Admission and Emergency Room in `app.blade.php`, with Rooms and Schedule links, clipboard-icon SVG.
- **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (tables 79-80).

---


- **[FEATURE]** Created **Admission Module** — bridges ER disposition to formal patient admission into Ward or ICU:
  - **Database Migrations**:
    - `2026_06_22_160000_create_admissions_table.php` — `admissions` table with patient_id, er_admission_id, admission_type, admission_source, admission_date, admitting_diagnosis, attending/admitting physician, ward_room_id, icu_bed_id, status, discharge_date, notes, audit columns. Unique on `(admission_code, tenant_id, history)`. FKs use ON DELETE NO ACTION.
    - `2026_06_22_160001_add_status_to_ward_rooms_and_icu_beds_table.php` — adds `status` (varchar(30), default 'Available') to `ward_rooms` and `icu_beds` for occupancy tracking.
  - **Model**: `App\Models\Admission` with global scope `where('history', 1)`, `$casts`, and relationships: `patient()`, `erAdmission()`, `attendingPhysician()`, `admittingPhysician()`, `wardRoom()`, `icuBed()`.
  - **Livewire Component**: `App\Livewire\Admission\AdmissionCrud` — two-tab SPA using Alpine.js:
    - **"For Admission" tab** — lists ER admissions where disposition contains "Admitted to Ward" or "Admitted to ICU" with no linked admission. Columns: Encounter #, Patient Name, Disposition (color-coded badge), ER Attending, Disposition Date/Time, Admit button.
    - **"Active Admissions" tab** — lists current admissions with status "Admitted". Datagrid with search, sortable columns, pagination. Columns: Admission Code, Type, Patient Name, Ward/Room, Attending, Admission Date, Actions (View / Discharge).
    - **Admit Modal** — auto-fills patient info and admitting diagnosis from ER. Fields: admission code (auto-generated ADM-xxxxx), date/time, admission type (default Emergency), source (default ER), attending/admitting physician selects, ward/room or ICU ward/bed cascading selects based on disposition, admitting diagnosis, notes.
    - **Discharge Flow** — confirmation modal sets status to "Discharged", discharge_date to now, sets ward_room/icu_bed status back to "Available".
    - **Detail Modal** — displays full admission info including patient, physicians, location, diagnosis, dates.
  - **Views**: `resources/views/livewire/admission/admission-crud.blade.php`, `resources/views/pages/admission/admission.blade.php`.
  - **Route**: `/admission` named `admission` — added between Ward and ER routes in `routes/web.php`.
  - **Sidebar**: Top-level "Admission" nav item with user-plus icon between Wards and Emergency Room in `app.blade.php`.
  - **ErAdmission Update**: Added `admission(): HasOne` relationship to `App\Models\Er\ErAdmission`.
- **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (table 78, updated ward_rooms/icu_beds docs, updated ErAdmission relations).

---


## 2. MULTI-TENANT ARCHITECTURE

> ⚠️ **THIS IS A MULTI-TENANT APPLICATION. ALL TABLE DESIGNS MUST INCLUDE `tenant_id`.**

Every custom business table **MUST** include a `tenant_id` (unsignedBigInteger) column. This column:
- Links each row to a specific tenant (hospital/clinic/branch).
- Scopes all queries to the current authenticated tenant.
- Must be part of composite unique constraints (e.g., `dept_code` unique per tenant, not globally).
- Must always be populated on `INSERT` using the current tenant session.
- Must always filter on `SELECT` using `where('tenant_id', $currentTenantId)`.

A future `tenants` master table will be created to manage tenant records. Until tenant auth is wired, `tenant_id = 1` is used as the development default.

---

## 3. PROJECT-WIDE DATA STANDARDS

### 3.1 Audit Columns (Required on ALL custom tables)
Every table must include the following columns, in this order after business fields:

| Column         | Type                | Description                              |
|----------------|---------------------|------------------------------------------|
| `history`      | tinyint, default 1  | `1` = current/active, `0` = archived     |
| `created_by`   | bigint unsigned     | FK → users.id (who created the record)   |
| `created_date` | timestamp, nullable | When the record was created              |
| `updated_by`   | bigint unsigned     | FK → users.id (who last updated)         |
| `updated_date` | timestamp, nullable | When the record was last updated         |

- **No `timestamps()` Laravel magic** — we use explicit `created_date` and `updated_date`.
- **No hard deletes** — always set `history = 0` to archive a record.
- All Eloquent models must have `public $timestamps = false;`.
- All models must define a global scope filtering `where('history', 1)` to hide archived records from normal queries.

### 3.2 Datagrid Standards
- **Never display the auto-increment `id` column** in any datagrid/table.
- Use business-meaningful codes/names as the first visible column.
- Always include sortable columns with directional arrows.
- Always include a live search bar and a per-page rows selector (10/25/50).
- Pagination links are always shown below the grid.
- Audit columns (`created_date`, `updated_date`) are shown in the grid for traceability.

### 3.3 Soft-Delete (History) Standard
- Deleting = setting `history = 0` (archiving), stamping `updated_by` and `updated_date`.
- UI uses an **amber** confirmation modal (not red) to visually indicate archiving, not destruction.
- Hard-deleted rows never appear in UI queries (global scope enforces `history = 1`).
- Restoration must be done directly at the database level or via a future admin restore utility.

---

## 4. Architecture & Layout

- **Master Layout**: `resources/views/components/layouts/app.blade.php`
  - **Header**: Logo, hamburger sidebar toggle, animated user profile dropdown (Alpine.js).
  - **Sidebar**: Animated, collapsible; includes Dashboard, Transactions, Reports, and a Masters sub-menu.
   - **Masters Sub-menu**: Company, Department, Users, Doctors, Country, Region, Province/State, City/Municipality, Barangay, Religion, Gender, Civil Status, Nationality, Occupation, Blood Type, Relationship.
  - **Main Content**: Right-side area loaded via `wire:navigate` (SPA-like, no full-page reload).
  - **Footer**: Simple copyright footer.
- **SPA Navigation**: Livewire `wire:navigate` on all sidebar links.

---

## 5. Database Configuration

| Setting    | Value          |
|------------|----------------|
| Driver     | `sqlsrv`       |
| Host       | `127.0.0.1`    |
| Port       | `1433`         |
| Database   | `sithemr`      |
| Username   | `root`         |
| Password   | `P@ssw0rd`     |

> **Note**: `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync` — set to avoid DB crashes while MSSQL is offline. Revert to `database` once MSSQL is live.

---

## 6. Modules Built

### 6.1 Department Master (Masters > Department)
- **Route**: `GET /masters/department`
- **Livewire Component**: `App\Livewire\Masters\DepartmentCrud`
- **View**: `resources/views/livewire/masters/department-crud.blade.php`
- **Model**: `App\Models\Department`
- **Migration**: `2026_06_17_135039_create_departments_table.php`
- **Features**: Sortable datagrid (no ID shown), live search, pagination, create/edit modal, history-based archive, audit timestamps, tenant-scoped.

### 6.2 Doctor Master (Masters > Doctors)
- **Route**: `GET /masters/doctors`
- **Livewire Component**: `App\Livewire\Masters\DoctorCrud`
- **View**: `resources/views/livewire/masters/doctor-crud.blade.php`
- **Model**: `App\Models\Doctor`
- **Migration**: `2026_06_20_000001_create_doctors_table.php`
- **Features**: Sortable datagrid (Code, Full Name, Specialization, License No., Department, Contact, Status — no ID shown), live search, pagination, create/edit modal with department dropdown, history-based archive, audit timestamps, tenant-scoped.

### 6.3 Country Master (Masters > Country)
- **Route**: `GET /masters/country`
- **Livewire Component**: `App\Livewire\Masters\CountryCrud`
- **View**: `resources/views/livewire/masters/country-crud.blade.php`
- **Model**: `App\Models\Country`
- **Migration**: `2026_06_20_000002_create_countries_table.php`
- **Features**: Global reference (no tenant_id), sortable datagrid (Code, Name, Status), live search, pagination, create/edit modal, archive.

### 6.4 Region Master (Masters > Region)
- **Route**: `GET /masters/region`
- **Livewire Component**: `App\Livewire\Masters\RegionCrud`
- **View**: `resources/views/livewire/masters/region-crud.blade.php`
- **Model**: `App\Models\Region`
- **Migration**: `2026_06_20_000003_create_regions_table.php`
- **Features**: Cascade FK → countries, global reference, sortable datagrid (Code, Name, Country, Status), country dropdown on form.

### 6.5 Province/City Master (Masters > Province/State & City/Municipality)
- **Route**: `GET /masters/province` and `GET /masters/city`
- **Livewire Component**: `App\Livewire\Masters\ProvinceCityCrud`
- **View**: `resources/views/livewire/masters/province-city-crud.blade.php`
- **Model**: `App\Models\ProvinceCity`
- **Migration**: `2026_06_20_000004_create_provinces_cities_table.php`
- **Features**: Cascade FK → regions, `type` column distinguishes province vs city, sortable datagrid (Code, Name, Type, Region, Status), region dropdown + type selector on form. Shared component for both sidebar links.

### 6.6 Barangay Master (Masters > Barangay)
- **Route**: `GET /masters/barangay`
- **Livewire Component**: `App\Livewire\Masters\BarangayCrud`
- **View**: `resources/views/livewire/masters/barangay-crud.blade.php`
- **Model**: `App\Models\Barangay`
- **Migration**: `2026_06_20_000005_create_barangays_table.php`
- **Features**: Cascade FK → provinces_cities, sortable datagrid (Code, Name, Province/City, Status), province/city dropdown on form.

### 6.7 Religion Master (Masters > Religion)
- **Route**: `GET /masters/religion`
- **Livewire Component**: `App\Livewire\Masters\ReligionCrud`
- **View**: `resources/views/livewire/masters/religion-crud.blade.php`
- **Model**: `App\Models\Religion`
- **Migration**: `2026_06_20_000006_create_religions_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Status), create/edit modal, archive.

### 6.8 Gender Master (Masters > Gender)
- **Route**: `GET /masters/gender`
- **Livewire Component**: `App\Livewire\Masters\GenderCrud`
- **View**: `resources/views/livewire/masters/gender-crud.blade.php`
- **Model**: `App\Models\Gender`
- **Migration**: `2026_06_20_000007_create_genders_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Status), create/edit modal, archive.

### 6.9 Civil Status Master (Masters > Civil Status)
- **Route**: `GET /masters/civil-status`
- **Livewire Component**: `App\Livewire\Masters\CivilStatusCrud`
- **View**: `resources/views/livewire/masters/civil-status-crud.blade.php`
- **Model**: `App\Models\CivilStatus`
- **Migration**: `2026_06_20_000008_create_civil_statuses_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Status), create/edit modal, archive.

### 6.10 Nationality Master (Masters > Nationality)
- **Route**: `GET /masters/nationality`
- **Livewire Component**: `App\Livewire\Masters\NationalityCrud`
- **View**: `resources/views/livewire/masters/nationality-crud.blade.php`
- **Model**: `App\Models\Nationality`
- **Migration**: `2026_06_20_000009_create_nationalities_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Status), create/edit modal, archive.

### 6.11 Occupation Master (Masters > Occupation)
- **Route**: `GET /masters/occupation`
- **Livewire Component**: `App\Livewire\Masters\OccupationCrud`
- **View**: `resources/views/livewire/masters/occupation-crud.blade.php`
- **Model**: `App\Models\Occupation`
- **Migration**: `2026_06_20_000010_create_occupations_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Status), create/edit modal, archive.

### 6.12 Blood Type Master (Masters > Blood Type)
- **Route**: `GET /masters/blood-type`
- **Livewire Component**: `App\Livewire\Masters\BloodTypeCrud`
- **View**: `resources/views/livewire/masters/blood-type-crud.blade.php`
- **Model**: `App\Models\BloodType`
- **Migration**: `2026_06_20_000011_create_blood_types_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Status), create/edit modal, archive.

### 6.13 Relationship Master (Masters > Relationship)
- **Route**: `GET /masters/relationship`
- **Livewire Component**: `App\Livewire\Masters\RelationshipCrud`
- **View**: `resources/views/livewire/masters/relationship-crud.blade.php`
- **Model**: `App\Models\Relationship`
- **Migration**: `2026_06_20_000012_create_relationships_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Status), create/edit modal, archive.

---

### 6.14 ICD-10 Codes (PhilHealth > ICD-10 Codes)
- **Route**: `GET /philhealth/icd10-codes`
- **Livewire Component**: `App\Livewire\Philhealth\Icd10CodeCrud`
- **View**: `resources/views/livewire/philhealth/icd10-code-crud.blade.php`
- **Model**: `App\Models\Icd10Code`
- **Migration**: `2026_06_20_000013_create_icd10_codes_table.php`
- **Features**: Global reference, sortable datagrid (Code, Description, Category, Status), create/edit modal, archive.

### 6.15 RVS Codes (PhilHealth > RVS Codes)
- **Route**: `GET /philhealth/rvs-codes`
- **Livewire Component**: `App\Livewire\Philhealth\RvsCodeCrud`
- **View**: `resources/views/livewire/philhealth/rvs-code-crud.blade.php`
- **Model**: `App\Models\RvsCode`
- **Migration**: `2026_06_20_000014_create_rvs_codes_table.php`
- **Features**: Global reference, sortable datagrid (Code, Description, Type, Status), create/edit modal, archive.

### 6.16 PhilHealth Case Rates (PhilHealth > Case Rates)
- **Route**: `GET /philhealth/case-rates`
- **Livewire Component**: `App\Livewire\Philhealth\CaseRateCrud`
- **View**: `resources/views/livewire/philhealth/case-rate-crud.blade.php`
- **Model**: `App\Models\PhilhealthCaseRate`
- **Migration**: `2026_06_20_000015_create_philhealth_case_rates_table.php`
- **Features**: Global reference, sortable datagrid (RVS Code, ICD-10, Description, HF Fee, PF Fee, Total Rate, Status), auto-calculated total, create/edit modal, archive.

### 6.17 Health Care Professionals (PhilHealth > HC Professionals)
- **Route**: `GET /philhealth/hc-professionals`
- **Livewire Component**: `App\Livewire\Philhealth\HcpCrud`
- **View**: `resources/views/livewire/philhealth/hcp-crud.blade.php`
- **Model**: `App\Models\HealthCareProfessional`
- **Migration**: `2026_06_20_000016_create_health_care_professionals_table.php`
- **Features**: Tenant-scoped, unified Doctor+HCP registry, sortable datagrid (Code, Full Name, Specialization, PRC License, Department, Status), create/edit modal, archive.

### 6.18 PhilHealth Members (PhilHealth > Members)
- **Route**: `GET /philhealth/members`
- **Livewire Component**: `App\Livewire\Philhealth\MemberCrud`
- **View**: `resources/views/livewire/philhealth/member-crud.blade.php`
- **Model**: `App\Models\PhilhealthMember`
- **Migration**: `2026_06_20_000017_create_philhealth_members_table.php`
- **Features**: Tenant-scoped, sortable datagrid (PIN, Last Name, First Name, Type, Status), create/edit modal with full CF1 fields, archive.

### 6.19 Patient Registration (Patients)
- **Route**: `GET /patients`
- **Livewire Component**: `App\Livewire\PatientCrud`
- **View**: `resources/views/livewire/patient-crud.blade.php`
- **Model**: `App\Models\Patient`
- **Migration**: `2026_06_20_000018_create_patients_table.php`
- **Features**: Tenant-scoped, auto-generated patient code, 5-tab SPA modal (Alpine.js), cascading address dropdowns via `parent_id`, PhilHealth member link, emergency contacts, sortable datagrid.

### 6.20 Lab Sections (Laboratory > Sections)
- **Route**: `GET /laboratory/sections`
- **Livewire Component**: `App\Livewire\Laboratory\LabSectionCrud`
- **View**: `resources/views/livewire/laboratory/lab-section-crud.blade.php`
- **Model**: `App\Models\LabSection`
- **Migration**: `2026_06_20_000020_create_lab_sections_table.php`
- **Features**: Tenant-scoped, sortable datagrid (Code, Name, Description, Status), create/edit modal, archive.

### 6.21 Lab Units (Laboratory > Units)
- **Route**: `GET /laboratory/units`
- **Livewire Component**: `App\Livewire\Laboratory\LabUnitCrud`
- **View**: `resources/views/livewire/laboratory/lab-unit-crud.blade.php`
- **Model**: `App\Models\LabUnit`
- **Migration**: `2026_06_20_000021_create_lab_units_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Category, Status), create/edit modal, archive.

### 6.22 Specimen Types (Laboratory > Specimen Types)
- **Route**: `GET /laboratory/specimen-types`
- **Livewire Component**: `App\Livewire\Laboratory\SpecimenTypeCrud`
- **View**: `resources/views/livewire/laboratory/specimen-type-crud.blade.php`
- **Model**: `App\Models\SpecimenType`
- **Migration**: `2026_06_20_000022_create_specimen_types_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Container, Status), create/edit modal, archive.

### 6.23 Lab Instruments (Laboratory > Instruments)
- **Route**: `GET /laboratory/instruments`
- **Livewire Component**: `App\Livewire\Laboratory\LabInstrumentCrud`
- **View**: `resources/views/livewire/laboratory/lab-instrument-crud.blade.php`
- **Model**: `App\Models\LabInstrument`
- **Migration**: `2026_06_20_000023_create_lab_instruments_table.php`
- **Features**: Tenant-scoped, FK → lab sections, sortable datagrid (Code, Name, Model, Manufacturer, Section, Status), create/edit modal, archive.

### 6.24 Lab Tests (Laboratory > Tests)
- **Route**: `GET /laboratory/tests`
- **Livewire Component**: `App\Livewire\Laboratory\LabTestCrud`
- **View**: `resources/views/livewire/laboratory/lab-test-crud.blade.php`
- **Model**: `App\Models\LabTest`
- **Migration**: `2026_06_20_000024_create_lab_tests_table.php`
- **Features**: Tenant-scoped, FK → section/specimen/unit, Numeric/Text/Categorical test types, TAT tracking, sortable datagrid.

### 6.25 Lab Panels (Laboratory > Panels)
- **Route**: `GET /laboratory/panels`
- **Livewire Component**: `App\Livewire\Laboratory\LabPanelCrud`
- **View**: `resources/views/livewire/laboratory/lab-panel-crud.blade.php`
- **Model**: `App\Models\LabPanel`
- **Migration**: `2026_06_20_000025_create_lab_panels_table.php`
- **Features**: Tenant-scoped, BelongsToMany pivot with display_order, test multi-select, sortable datagrid.

### 6.26 Reference Ranges (Laboratory > Reference Ranges)
- **Route**: `GET /laboratory/reference-ranges`
- **Livewire Component**: `App\Livewire\Laboratory\ReferenceRangeCrud`
- **View**: `resources/views/livewire/laboratory/reference-range-crud.blade.php`
- **Model**: `App\Models\ReferenceRange`
- **Migration**: `2026_06_20_000026_create_reference_ranges_table.php`
- **Features**: Global reference, FK → lab tests, age/gender stratified, composite unique on (lab_test_id, gender, age_min, age_max).

### 6.27 Supply Units (CSR/Supply > Units)
- **Route**: `GET /csr-supply/units`
- **Livewire Component**: `App\Livewire\CsrSupply\SupplyUnitCrud`
- **View**: `resources/views/livewire/csr-supply/supply-unit-crud.blade.php`
- **Model**: `App\Models\Supply\SupplyUnit`
- **Migration**: `2026_06_20_000027_create_supply_units_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Status), create/edit modal, archive.

### 6.28 Item Categories (CSR/Supply > Item Categories)
- **Route**: `GET /csr-supply/item-categories`
- **Livewire Component**: `App\Livewire\CsrSupply\ItemCategoryCrud`
- **View**: `resources/views/livewire/csr-supply/item-category-crud.blade.php`
- **Model**: `App\Models\Supply\ItemCategory`
- **Migration**: `2026_06_20_000028_create_item_categories_table.php`
- **Features**: Tenant-scoped, self-referencing parent_id for sub-categories, parent category dropdown, sortable datagrid.

### 6.29 Suppliers (CSR/Supply > Suppliers)
- **Route**: `GET /csr-supply/suppliers`
- **Livewire Component**: `App\Livewire\CsrSupply\SupplierCrud`
- **View**: `resources/views/livewire/csr-supply/supplier-crud.blade.php`
- **Model**: `App\Models\Supply\Supplier`
- **Migration**: `2026_06_20_000029_create_suppliers_table.php`
- **Features**: Tenant-scoped, TIN, payment terms, contact details, sortable datagrid.

### 6.30 Stock Locations (CSR/Supply > Stock Locations)
- **Route**: `GET /csr-supply/stock-locations`
- **Livewire Component**: `App\Livewire\CsrSupply\StockLocationCrud`
- **View**: `resources/views/livewire/csr-supply/stock-location-crud.blade.php`
- **Model**: `App\Models\Supply\StockLocation`
- **Migration**: `2026_06_20_000030_create_stock_locations_table.php`
- **Features**: Tenant-scoped, self-referencing parent_id, type dropdown (Warehouse/Room/Shelf/Bin/Cabinet), sortable datagrid.

### 6.31 Items (CSR/Supply > Items)
- **Route**: `GET /csr-supply/items`
- **Livewire Component**: `App\Livewire\CsrSupply\ItemCrud`
- **View**: `resources/views/livewire/csr-supply/item-crud.blade.php`
- **Model**: `App\Models\Supply\Item`
- **Migration**: `2026_06_20_000031_create_items_table.php`
- **Features**: Tenant-scoped, FK → categories/suppliers/units, SKU, unit cost, reorder level/qty, sortable datagrid.

### 6.32 Procedure Categories (Procedures > Categories)
- **Route**: `GET /procedures/categories`
- **Livewire Component**: `App\Livewire\Procedures\ProcedureCategoryCrud`
- **View**: `resources/views/livewire/procedures/procedure-category-crud.blade.php`
- **Model**: `App\Models\Procedures\ProcedureCategory`
- **Migration**: `2026_06_20_000032_create_procedure_categories_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Description, Status), create/edit modal, archive.

### 6.33 Procedures (Procedures > Procedures)
- **Route**: `GET /procedures/procedures`
- **Livewire Component**: `App\Livewire\Procedures\ProcedureCrud`
- **View**: `resources/views/livewire/procedures/procedure-crud.blade.php`
- **Model**: `App\Models\Procedures\Procedure`
- **Migration**: `2026_06_20_000033_create_procedures_table.php`
- **Features**: Tenant-scoped, FK → categories + departments, standard_charge tracking, sortable datagrid.

### 6.34 Dialysis Types (Renal Care > Dialysis Types)
- **Route**: `GET /renal-care/dialysis-types`
- **Livewire Component**: `App\Livewire\RenalCare\DialysisTypeCrud`
- **View**: `resources/views/livewire/renal-care/dialysis-type-crud.blade.php`
- **Model**: `App\Models\RenalCare\DialysisType`
- **Migration**: `2026_06_20_000034_create_dialysis_types_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Description, Status), create/edit modal, archive.

### 6.35 Dialysis Sessions (Renal Care > Dialysis Sessions)
- **Route**: `GET /renal-care/dialysis-sessions`
- **Livewire Component**: `App\Livewire\RenalCare\DialysisSessionCrud`
- **View**: `resources/views/livewire/renal-care/dialysis-session-crud.blade.php`
- **Model**: `App\Models\RenalCare\DialysisSession`
- **Migration**: `2026_06_20_000035_create_dialysis_sessions_table.php`
- **Features**: Tenant-scoped, FK → patients + dialysis_types, pre/post weight/BP/HR tracking, access_type dropdown, status tracking (Scheduled/In Progress/Completed/Cancelled).

### 6.36 ICU Wards (ICU > Wards)
- **Route**: `GET /icu/wards`
- **Livewire Component**: `App\Livewire\Icu\IcuWardCrud`
- **View**: `resources/views/livewire/icu/icu-ward-crud.blade.php`
- **Model**: `App\Models\Icu\IcuWard`
- **Migration**: `2026_06_20_000036_create_icu_wards_table.php`
- **Features**: Tenant-scoped, location field, sortable datagrid (Code, Name, Location, Status), create/edit modal, archive.

### 6.37 ICU Beds (ICU > Beds)
- **Route**: `GET /icu/beds`
- **Livewire Component**: `App\Livewire\Icu\IcuBedCrud`
- **View**: `resources/views/livewire/icu/icu-bed-crud.blade.php`
- **Model**: `App\Models\Icu\IcuBed`
- **Migration**: `2026_06_20_000037_create_icu_beds_table.php`
- **Features**: Tenant-scoped, FK → ICU wards, sortable datagrid (Code, Name, Ward, Status), create/edit modal with ward dropdown, archive.

### 6.38 Radiology Modalities (Radiology > Modalities)
- **Route**: `GET /radiology/modalities`
- **Livewire Component**: `App\Livewire\Radiology\ModalityCrud`
- **View**: `resources/views/livewire/radiology/modality-crud.blade.php`
- **Model**: `App\Models\Radiology\RadiologyModality`
- **Migration**: `2026_06_21_000038_create_radiology_modalities_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Description, Status), create/edit modal, archive.

### 6.39 Radiology Body Parts (Radiology > Body Parts)
- **Route**: `GET /radiology/body-parts`
- **Livewire Component**: `App\Livewire\Radiology\BodyPartCrud`
- **View**: `resources/views/livewire/radiology/body-part-crud.blade.php`
- **Model**: `App\Models\Radiology\RadiologyBodyPart`
- **Migration**: `2026_06_21_000039_create_radiology_body_parts_table.php`
- **Features**: Global reference, sortable datagrid (Code, Name, Description, Status), create/edit modal, archive.

### 6.40 Radiology Exam Types (Radiology > Exam Types)
- **Route**: `GET /radiology/exam-types`
- **Livewire Component**: `App\Livewire\Radiology\ExamTypeCrud`
- **View**: `resources/views/livewire/radiology/exam-type-crud.blade.php`
- **Model**: `App\Models\Radiology\RadiologyExamType`
- **Migration**: `2026_06_21_000040_create_radiology_exam_types_table.php`
- **Features**: Tenant-scoped, FK → modalities + body parts + departments, standard_charge, TAT, contrast_type dropdown, preparation field, sortable datagrid with eager-loaded modality/body part names.

---

## 7. JWT Authentication System

### 7.1 Package
- **Package**: `tymon/jwt-auth ^2.3` (compatible with PHP 8.2 + Laravel 12)
- **Secret**: Stored in `.env` as `JWT_SECRET`
- **TTL**: Configurable in `config/jwt.php` (default 60 min)

### 7.2 Token Flow
```
POST /api/auth/login  →  validate credentials  →  issue JWT
    JWT payload contains: sub (user_id), tenant_id, name, email, exp
    Token stored in: session('jwt_token') for web, Bearer header for API
```

### 7.3 Middleware: `JwtMiddleware` (`App\Http\Middleware\JwtMiddleware`)
- **Alias**: `custom.jwt` (Renamed from `jwt.auth` to avoid conflict with `tymon/jwt-auth`'s internal middleware)
- **Global Registration**: Appended to the global `web` middleware group in `bootstrap/app.php` so that it automatically protects Livewire internal routes (like `/livewire/update`). This ensures Livewire controller methods have access to the authenticated user's session data.
- **Exclusions**: 
  - Specifically ignores auth routes (`/login`, `/api/auth/login`) and Livewire assets to prevent redirect loops.
  - **CRITICAL**: Explicitly bypasses JWT validation for `/livewire/update` requests if their HTTP `referer` header indicates they originated from the `/login` page. Without this, the login button (which sends a Livewire update) would get intercepted by the middleware and trigger an infinite redirect back to the login page before authentication could even occur!
- **Token resolution order**: session → Bearer header → query param
- Extracts `tenant_id` and `role` from JWT claims and stores them in `$request->attributes`
- Redirects to `/login` on failure for web requests; returns JSON 401 for API requests.
- Checks `is_active` flag on the user model

### 7.4 Livewire Architectural Patterns
- **Modals**: To prevent CSS z-index blocking issues where the backdrop prevents input interaction, always use this explicit DOM structure for modals:
  ```html
  <div class="fixed inset-0 z-50 flex items-center justify-center">
      {{-- Backdrop --}}
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
      {{-- Panel --}}
      <div class="relative bg-white z-10 ...">
          ...
      </div>
  </div>
  ```
- **Datatables**: When designing datatables for CRUD modules, DO NOT include the `Created` and `Updated` audit columns in the table view to keep the UI clean. Audit information is still tracked in the database (`created_by`, `updated_by`, etc.), but should not clutter the main listing.

### 7.5 API Endpoints (`/api/auth/...`)
| Method | Endpoint         | Auth  | Description                  |
|--------|------------------|-------|------------------------------|
| POST   | /api/auth/login  | None  | Issue a JWT token            |
| GET    | /api/auth/me     | JWT   | Return authenticated user    |
| POST   | /api/auth/refresh| JWT   | Refresh expiring token       |
| POST   | /api/auth/logout | JWT   | Invalidate current token     |

### 7.6 Key Files
| File | Description |
|------|-------------|
| `app/Http/Middleware/JwtMiddleware.php` | Core JWT validation middleware |
| `app/Http/Controllers/AuthController.php` | JWT login/me/refresh/logout |
| `app/Livewire/Auth/Login.php` | Livewire login component (Uses `JWTAuth::attempt` directly to bypass `php artisan serve` loopback deadlocks) |
| `app/Livewire/Auth/Logout.php` | Livewire logout button component |
| `resources/views/livewire/auth/login.blade.php` | Login page UI |
| `resources/views/components/layouts/guest.blade.php` | Guest layout (no sidebar) |
| `resources/views/pages/auth/login.blade.php` | Login page view wrapper |
| `config/jwt.php` | JWT configuration |
| `config/auth.php` | api guard uses jwt driver |
| `bootstrap/app.php` | Middleware alias `custom.jwt` registered |

### 7.6 Multi-Tenant in JWT
- `tenant_id` is embedded in every JWT token via `getJWTCustomClaims()` on the User model.
- The middleware extracts it and attaches it to the request, making it available to all Livewire components and controllers via `$request->attributes->get('tenant_id')`.

---

## 8. PhilHealth Claims Support Modules (CF1-CF5)

The following modules provide the master data required to populate PhilHealth Claim Forms 1 through 5:

### 9.1 ICD-10 Codes (PhilHealth > ICD-10 Codes) — CF2/CF4
- **Route**: `GET /philhealth/icd10-codes`
- **Livewire Component**: `App\Livewire\Philhealth\Icd10CodeCrud`
- **View**: `resources/views/livewire/philhealth/icd10-code-crud.blade.php`
- **Model**: `App\Models\Icd10Code`
- **Migration**: `2026_06_20_000013_create_icd10_codes_table.php`
- **Features**: Global reference, sortable datagrid (Code, Description, Category, Status), create/edit modal with category field, archive.

### 9.2 RVS Codes (PhilHealth > RVS Codes) — CF2
- **Route**: `GET /philhealth/rvs-codes`
- **Livewire Component**: `App\Livewire\Philhealth\RvsCodeCrud`
- **View**: `resources/views/livewire/philhealth/rvs-code-crud.blade.php`
- **Model**: `App\Models\RvsCode`
- **Migration**: `2026_06_20_000014_create_rvs_codes_table.php`
- **Features**: Global reference, sortable datagrid (Code, Description, Type, Status), create/edit modal with procedure type, archive.

### 9.3 PhilHealth Case Rates (PhilHealth > Case Rates) — CF2/CF4
- **Route**: `GET /philhealth/case-rates`
- **Livewire Component**: `App\Livewire\Philhealth\CaseRateCrud`
- **View**: `resources/views/livewire/philhealth/case-rate-crud.blade.php`
- **Model**: `App\Models\PhilhealthCaseRate`
- **Migration**: `2026_06_20_000015_create_philhealth_case_rates_table.php`
- **Features**: Global reference, sortable datagrid (RVS Code, ICD-10, Description, HF Fee, PF Fee, Total Rate, Status), auto-calculated total (HF + PF), create/edit modal, archive.

### 9.4 Health Care Professionals (PhilHealth > HC Professionals) — CF2
- **Route**: `GET /philhealth/hc-professionals`
- **Livewire Component**: `App\Livewire\Philhealth\HcpCrud`
- **View**: `resources/views/livewire/philhealth/hcp-crud.blade.php`
- **Model**: `App\Models\HealthCareProfessional`
- **Migration**: `2026_06_20_000016_create_health_care_professionals_table.php`
- **Features**: Tenant-scoped, sortable datagrid (Code, Full Name, Specialization, PRC License, Status), create/edit modal with PhilHealth accreditation field, archive.

### 9.5 PhilHealth Members (PhilHealth > Members) — CF1
- **Route**: `GET /philhealth/members`
- **Livewire Component**: `App\Livewire\Philhealth\MemberCrud`
- **View**: `resources/views/livewire/philhealth/member-crud.blade.php`
- **Model**: `App\Models\PhilhealthMember`
- **Migration**: `2026_06_20_000017_create_philhealth_members_table.php`
- **Features**: Tenant-scoped, sortable datagrid (PIN, Last Name, First Name, Type, Status), create/edit modal with full CF1 fields (PIN, name parts, DOB, gender, membership type, contact, address), archive.

---

## 9. Changelog

### 2026-06-17
- Initialized Laravel 12 project in `e:\sithemr`.
- Configured `.env` for MSSQL (`sqlsrv`).
- Hotfix: `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`.
- Installed and configured Livewire v4, Alpine.js, Tailwind CSS v4 offline.
- Built animated sidebar layout with collapsible Masters sub-menu.
- **[Standard Defined]** Multi-tenant, no-hard-delete (history), audit columns, no ID in datagrid.
- **[CRUD Built]** Department Master with all standards applied.
- **[CRUD Built]** Company / Tenant Master (Strict role-based logic: Superadmin manages all, Tenant Admin can only view/edit their own profile).
- **[Auth Built]** JWT authentication: middleware, AuthController, Login/Logout Livewire components, guest layout, login page. All web routes protected by `custom.jwt`.
- **[Tenants & Users Built]** Created `tenants` table (root entity) and updated `users` table to support `superadmin` (no tenant_id) and `tenant_admin` (with tenant_id) roles.
- **[Seeding]** Added `SuperadminSeeder` and `TenantSeeder` to create initial superadmin and demo tenant accounts.

### 2026-06-20
- **[Fix Layout]** Removed orphaned `</div>` that was closing the `flex-1 flex overflow-hidden` container prematurely in `app.blade.php`, causing `<main>` with `{{ $slot }}` to drop below sidebar as a vertical sibling of `<footer>`. Fixed by removing the extra `</div>` so that `<nav>` and `<main>` remain as flex-row children inside the flex-1 container.
- **[Fix]** Removed dead `$successMessage` block from `department-crud.blade.php` — the variable was never defined in the component, causing a 500 error on page load. Success feedback uses `$this->dispatch('toast', ...)` instead.
- **[CRUD Built]** Doctor Master (Masters > Doctors) with all standards applied: sortable datagrid (Code, Full Name, Specialization, License No., Department, Contact, Status), live search, pagination, create/edit modal with department dropdown, archive confirmation modal with amber styling.
- **[Migration]** Ran `2026_06_20_000001_create_doctors_table.php` — MSSQL is now live. Consider reverting `SESSION_DRIVER`, `CACHE_STORE`, and `QUEUE_CONNECTION` from `file`/`sync` to `database`.
- **[CRUD Built]** Country, Region, Province/City, and Barangay masters — global reference data (no tenant_id) with cascade hierarchy: Country → Region → Province/City → Barangay. Each has full CRUD with search, sort, pagination, archive. Both "Province/State" and "City/Municipality" sidebar links share the same `ProvinceCityCrud` component with a `type` discriminator.
- **[Migration]** Ran `2026_06_20_000002_create_countries_table.php`, `2026_06_20_000003_create_regions_table.php`, `2026_06_20_000004_create_provinces_cities_table.php`, `2026_06_20_000005_create_barangays_table.php`.
- **[Sidebar]** Added "Region" link between Country and Province/State in the Masters sub-menu.
- **[Fix]** Added `protected $table = 'provinces_cities'` to `ProvinceCity` model — Laravel's pluralizer inferred `province_cities` from the class name, but the actual table is `provinces_cities`, causing a 500 error on `/masters/city` and `/masters/province`.
- **[CRUD Built]** Religion, Gender, and Civil Status masters — global reference data, each with sortable datagrid, live search, pagination, create/edit modal, archive.
- **[Sidebar]** Added "Gender" and "Civil Status" links at the end of the Masters sub-menu.
- **[Migration]** Ran `2026_06_20_000006_create_religions_table.php`, `2026_06_20_000007_create_genders_table.php`, `2026_06_20_000008_create_civil_statuses_table.php`.
- **[CRUD Built]** Nationality, Occupation, Blood Type, and Relationship masters — global reference data, each with sortable datagrid, live search, pagination, create/edit modal, archive.
- **[Migration]** Ran `2026_06_20_000009_create_nationalities_table.php`, `2026_06_20_000010_create_occupations_table.php`, `2026_06_20_000011_create_blood_types_table.php`, `2026_06_20_000012_create_relationships_table.php`.
- **[Sidebar & Routes]** Added all 4 new modules to sidebar and routes. Created missing blade views and page wrappers.
- **[CRUD Built]** PhilHealth CF1-CF5 support modules — ICD-10 Codes, RVS Codes, Case Rates, HC Professionals, and PhilHealth Members. Each with sortable datagrid, live search, pagination, create/edit modal, archive. New "PhilHealth" sub-menu in sidebar.
- **[Migration]** Ran `2026_06_20_000013_create_icd10_codes_table.php` through `2026_06_20_000017_create_philhealth_members_table.php` (5 new tables).
- **[Lab Modules Built]** Created 7 Laboratory master data CRUD modules under new "Laboratory" sidebar submenu:
  - **Lab Sections** — lab departments (Hematology, Chemistry, Microbiology, etc.), tenant-scoped with code/name/description.
  - **Lab Units** — units of measurement (mg/dL, mmol/L, %, cells/uL), global reference with category.
  - **Specimen Types** — sample types (Whole Blood, Serum, Urine, etc.), global reference with container info.
  - **Lab Instruments** — analyzers/equipment, tenant-scoped with model/manufacturer/serial number, FK → lab_sections.
  - **Lab Test Catalog** — individual test definitions, tenant-scoped with FK → section/specimen/unit, test_type (Numeric/Text/Categorical), and TAT.
  - **Lab Panels** — test groups/profiles (CBC, BMP, etc.), tenant-scoped with BelongsToMany pivot `lab_panel_tests` for test selection with display_order.
  - **Reference Ranges** — normal values per test by age/gender (Any/Male/Female), composite unique on (lab_test_id, gender, age_min, age_max).
- **[Migration]** Ran migrations `2026_06_20_000020` through `2026_06_20_000026` (7 new tables + 1 pivot).
- **[CRUD Built]** Patient Registration module — full patient demographic record linking all reference masters (gender, civil status, religion, nationality, occupation, blood type) and geographic hierarchy (country → region → province → city → barangay). Features: auto-generated patient code (PT-00001), optional PhilHealth member link, cascading address dropdowns via `wire:model.live`, emergency contact fields, sortable datagrid (Patient Code, Last Name, First Name, DOB, Contact No, Status), live search, pagination, create/edit modal, archive. New "Patients" link in sidebar below Reports.
- **[EMR Compliance]** Upgraded Patient Registration module to include standard EMR/HL7/PHCDI data elements: `maiden_name`, `place_of_birth`, `preferred_language`, `national_id_no`, `passport_no`, `father_name`, `mother_maiden_name`, `spouse_name`, `multiple_birth_indicator`, `is_deceased`, `date_of_death`, and linked `primary_care_provider_id` (FK → health_care_professionals).
- **[Migration]** Ran `2026_06_20_000018_create_patients_table.php` and `2026_06_20_145946_add_emr_standard_fields_to_patients_table.php`.
- **[Refactor HC Professionals]** Merged `doctors` and `health_care_professionals` tables into unified `health_care_professionals` table with new columns: `professional_type` (Doctor, Nurse, Midwife, etc.) and `department_id` (FK → departments). Kept `prc_license` and `philhealth_accreditation`. Removed DrCrud, Doctor model, doctor-crud.blade.php, and <code>/masters/doctors</code> route. Replaced both DoctorCrud and old HcpCrud with a single unified HcpCrud component under PhilHealth. Both sidebar "Doctors" (Masters) and old "HC Professionals" (PhilHealth) replaced by single unified "HC Professionals" entry under PhilHealth.
- **[CSR/Supply Modules Built]** Created 5 CSR/Supply master data CRUD modules under new "CSR/Supply" sidebar submenu (between Laboratory and PhilHealth):
  - **Supply Units** — units of measurement for supply items (PCS, BOX, REAM, etc.), global reference, sortable datagrid, live search, pagination, CRUD modal, archive.
  - **Item Categories** — item categories with self-referencing parent_id for sub-categories, tenant-scoped, parent category dropdown in modal.
  - **Suppliers** — vendor/supplier management with contact details, TIN, payment terms, tenant-scoped.
  - **Stock Locations** — inventory location hierarchy (Warehouse > Room > Shelf > Bin > Cabinet) with self-referencing parent_id, tenant-scoped, type dropdown.
  - **Items** — inventory item master with FK references to categories, suppliers, and supply units; includes SKU, unit cost, reorder level/qty, tenant-scoped with sortable datagrid.
- **[Migration Fix]** Fixed self-referencing FK issue on `item_categories` and `stock_locations` — changed `ON DELETE SET NULL` to `ON DELETE NO ACTION` to avoid SQL Server "cycles or multiple cascade paths" error.
- **[Migration]** Ran migrations `2026_06_20_000027` through `2026_06_20_000031` (5 new supply tables).

### 2026-06-19 (continued)
- **[CRUD Built]** 3 new hospital modules with full CRUD:
  - **Procedures** — procedure_categories (global, no tenant_id) + procedures (tenant-scoped, FK → categories + departments), with standard_charge tracking.
  - **Renal Care** — dialysis_types (global, no tenant_id) + dialysis_sessions (tenant-scoped, FK → patients + dialysis_types, with weight/BP/HR/access_type/status tracking).
  - **ICU** — icu_wards (tenant-scoped, location field) + icu_beds (tenant-scoped, FK → wards).
- **[Migration]** Ran migrations `2026_06_20_000032` through `2026_06_20_000037` (6 new tables, batch 12).
- **[Sidebar]** Added Procedures (collapsible), Renal Care (collapsible), ICU (collapsible) sub-menus between CSR/Supply and PhilHealth.
- **[Seed Data]** Added `ReferenceDataSeeder` — seeds 8 procedure categories (Diagnostic, Surgical, Radiology, etc.) and 8 dialysis types (Hemodialysis, Peritoneal Dialysis, CRRT, etc.), global reference data.
- **[Seed Data]** Added `DemoDataSeeder` — seeds demo hospital data (10 departments, 5 ICU wards, 22 ICU beds, 20 procedures with standard charges) for the DEMO01 tenant.

### 2026-06-20
- **[CRUD Built]** 3 new Radiology modules under new "Radiology" sidebar submenu (between Procedures and Renal Care):
  - **Modalities** — imaging equipment types (X-Ray, CT, MRI, Ultrasound, Mammography, Nuclear Medicine, DEXA, Angiography), global reference (no tenant_id), sortable datagrid.
  - **Body Parts** — anatomical regions (Chest, Abdomen, Head & Neck, Spine segments, Extremities, Pelvis, Breast), global reference (no tenant_id), sortable datagrid.
  - **Exam Types** — tenant-scoped radiology exam catalog with FK references to modalities, body parts, and departments; includes standard charge, TAT, contrast type (dropdown: None/IV Contrast/Oral Contrast/IV + Oral/Rectal Contrast), patient preparation field, and sortable datagrid showing modality/body part names via eager-loaded relationships.
- **[Migration]** Ran migrations `2026_06_21_000038` through `2026_06_21_000040` (3 new tables: radiology_modalities, radiology_body_parts, radiology_exam_types).
- **[Seed Data]** Added radiology seed data to `DemoDataSeeder`: 8 modalities, 10 body parts, 17 exam types (Chest X-Ray PA/AP, CT Brain/Abdomen with/without contrast, MRI Brain/L-Spine, Ultrasound Abdomen/Pelvis, Mammogram Screening/Diagnostic, X-Ray Extremities/Spine, DEXA Hip).

### 2026-06-20 (continued)
- **[Migrations]** Ran migrations `2026_06_21_000041` through `2026_06_21_000045` (5 new tables: micu_assessment_parameters, delivery_types, delivery_complications, ward_room_types, ward_rooms).
- **[CRUD Built]** 5 new modules from 3 new sidebar sub-menus (between ICU and PhilHealth):
  - **MICU > Assessment Parameters** (global reference with category + default_unit fields)
  - **Delivery > Types** (global reference — NSVD, AVB, C/S, VBAC)
  - **Delivery > Complications** (global reference — PPH, Preeclampsia, GDM, etc.)
  - **Wards > Room Types** (global reference — Private, Semi-Private, Ward, Suite, Isolation)
  - **Wards > Rooms** (tenant-scoped with FK → room_types + departments, capacity tracking)
- **[Seed Data]** Added MICU/Delivery/Wards seed data to `DemoDataSeeder`: 13 assessment parameters, 5 delivery types, 10 complications, 5 room types, 10 ward rooms linked to departments.

### 2026-06-22
- **[Migrations]** Ran migrations `2026_06_22_000049` through `2026_06_22_000054` (6 new OPD tables: opd_visit_types, opd_consultation_types, opd_referral_sources, opd_services, opd_clinics, opd_complaints).
- **[CRUD Built]** Wards Master (Wards > Wards) — tenant-scoped ward entity with code/name/description/location, creating the parent cascade: Wards > Rooms. All existing rooms in seed data linked to proper wards.
- **[Migrations]** Ran `2026_06_22_000055_create_wards_table.php` and `2026_06_22_000056_add_ward_id_and_pricing_to_ward_rooms_table.php` (2 new migrations). Added `ward_id` FK, `room_rate`, `extra_bed_rate`, and `discount_rate` columns to `ward_rooms`.
- **[Pricing]** Ward Rooms now include room rate (required), extra bed rate (optional), and discount percentage (optional) for billing/charging purposes.
- **[Seed Data]** Added demo ward data to `DemoDataSeeder`: 8 wards (Gen Med, Cardiology, Pediatrics, Surgery, Ortho, OB-Gyne, Pulmo, Neuro) and updated 10 ward rooms with ward assignments and pricing.
- **[CRUD Built]** 6 OPD modules under new "OPD" sidebar sub-menu (between Wards and PhilHealth):
  - **OPD Visit Types** — booking/general/specialist/follow-up/emergency visit classification.
  - **OPD Consultation Types** — new patient/follow-up/acute/chronic/wellness/pre-op/post-op.
  - **OPD Referral Sources** — walk-in/physician referral/ER transfer/HMO/company/other dept/external hospital.
  - **OPD Services** — billable OPD services catalog with standard_charge (consultation fees, injection, ECG, dressing, minor surgery, medical cert).
  - **OPD Clinics** — sub-specialty clinics within OPD (General Medicine, Pediatrics, OB-Gyne, HTN/DM, Well-Baby, Family Planning, TB-DOTS, Nutrition).
  - **OPD Complaints** — common presenting complaints/symptoms (fever, cough, headache, abdominal pain, etc.).
- **[Seed Data]** Added OPD seed data to `DemoDataSeeder`: 5 visit types, 7 consultation types, 7 referral sources, 9 services with charges, 8 clinics, 15 complaints.
- **[Migrations]** Ran migrations `2026_06_22_000046` through `2026_06_22_000048` (3 new OB tables: ob_visit_types, ob_fetal_presentations, ob_contraceptive_methods).
- **[CRUD Wired Up]** 3 OB modules under new "OB" sidebar sub-menu (between OPD and PhilHealth):
  - **OB Visit Types** — prenatal/postnatal/high-risk/teen pregnancy/post-abortion care visit classification.
  - **OB Fetal Presentations** — vertex/breech/transverse/face/brow/compound/cord presentation types.
  - **OB Contraceptive Methods** — hormonal/IUD/barrier/permanent/natural/emergency methods with category field.
- **[Seed Data]** Added OB seed data to `DemoDataSeeder`: 5 visit types, 7 fetal presentations, 15 contraceptive methods with categories.
- **[CRUD Built]** Lab Dispensing (Laboratory > Dispensing) — full dispensing form with patient search, department/doctor assignment, priority selection (Routine/Urgent/STAT), dynamic line items (individual tests or panels), editable charges, real-time total calculation, billing status tracking for future billing module integration, detail view, cancel support.
- **[Migrations]** Ran `2026_06_22_000057_create_lab_requests_table.php` and `2026_06_22_000058_create_lab_request_items_table.php` (2 new tables). Fixed `verified_by` FK from `SET NULL` to `NO ACTION` to avoid MSSQL multiple cascade paths error.
- **[Seed Data]** Added comprehensive lab demo data to `DemoDataSeeder`: 5 lab sections, 12 lab units, 7 specimen types, 27 lab tests (Chemistry, Hematology, Serology, Urinalysis), 6 lab panels (Lipid Profile, LFT, RFT, Electrolytes, CBC Panel, Coagulation Profile), and 5 lab requests with items (varying priorities/statuses/billing statuses).
- **[CRUD Built]** CSR Dispensing (CSR/Supply > Dispensing) — full dispensing form with department dropdown, requesting person field, supply item selector from existing items table, dynamic line items with editable quantity/unit cost, real-time total calculation, billing_status tracking for future billing module integration, detail view, cancel support.
- **[Migrations]** Ran `2026_06_22_000059_create_csr_requests_table.php` and `2026_06_22_000060_create_csr_request_items_table.php` (2 new tables). Fixed duplicate index on `billing_id` in migration 000059.
- **[Seed Data]** Added comprehensive CSR/supply demo data to `DemoDataSeeder`: 12 supply units, 8 item categories, 5 suppliers, 22 supply items (syringes/gloves/masks/gauze/IV fluids/sutures/lab consumables), and 5 CSR requests with items (varying statuses/billing statuses across ER/Cardio/Surgery/Pedia/Lab departments). Made all seed data idempotent using `updateOrCreate`.
- **[Lab Dispensing Fix]** Changed lab request seeding from `create` to `updateOrCreate` for idempotent re-runs. Removed old child items on reseed to avoid duplicates.
- **[Migration Bug Fix]** Fixed `2026_06_22_000059_create_csr_requests_table.php` — removed duplicate `$table->index('billing_id')` that conflicted with `billing_id->index()` on the column definition (MSSQL error: index name already exists).
- **[CRUD Built]** Radiology Dispensing (Radiology > Dispensing) — full dispensing form for radiology exam requests from all departments. Includes department dropdown, requesting person text field, exam type selector dropdown with JS add button (searches existing exam types from radiology_exam_types table with modality context), dynamic line items with pre-populated standard_charge from exam type definition (editable in real-time), billing_status tracking (Pending/Billed/Exempt/Credited) with nullable billing_id for future billing module integration, detail view modal with full request breakdown (modality, charge per exam), cancel support with cascading item status update, sortable datagrid (Request Code, Department, Requested By, Exams Count, Total Amount, Status, Billing Status).
- **[Migrations]** Created and ran `2026_06_22_000061_create_radiology_requests_table.php` and `2026_06_22_000062_create_radiology_request_items_table.php` (2 new tables with billing_status + billing_id for billing module pickup).
- **[Models]** Created `App\Models\Radiology\RadiologyRequest` (with HasMany items + BelongsTo requestingDepartment) and `App\Models\Radiology\RadiologyRequestItem` (with BelongsTo radiologyRequest + BelongsTo examType).
- **[Seed Data]** Added 5 radiology demo requests with 10 exam items (RR-00001 through RR-00005) covering ER/Cardio/Ortho/Neuro/RAD departments with varying statuses (Pending/Approved/Complete/Cancelled) and billing statuses (Pending/Billed/Credited).
- **[Sidebar]** Added "Dispensing" link under the Radiology submenu in the main sidebar layout.
- **[Bug Fix]** Fixed `SQLSTATE[23000]` NOT NULL constraint error on all 5 supply master CRUD create operations (`SupplyUnitCrud`, `ItemCategoryCrud`, `SupplierCrud`, `StockLocationCrud`, `ItemCrud`). The `updated_by` column is NOT NULL in all 5 tables (`supply_units`, `item_categories`, `suppliers`, `stock_locations`, `items`), but Livewire `create()` calls were omitting it. Added `'updated_by' => $this->currentUserId(), 'updated_date' => null` to all 5 create arrays.
- **[Seed Data]** Added 8 stock location demo records to `DemoDataSeeder` with parent-child hierarchy: Main Warehouse (Warehouse), Storage Room A/B (Room), Shelf A-01/A-02 (Shelf), Bin A-01/A-02 (Bin), OR Supply Cabinet (Cabinet). Seeded in order so `parent_id` FK resolves correctly.
- **[Bug Fix]** Fixed `addSelectedTest`/`addSelectedPanel` JS functions in Lab Dispensing (`resources/views/livewire/lab/lab-dispensing-crud.blade.php`) — moved `<script>` block outside `@if($showModal)` so functions are always defined in DOM. Prevents "function not defined" errors on Livewire re-render.
- **[Bug Fix]** Fixed `addSelectedExam` JS function in Radiology Dispensing (`resources/views/livewire/radiology/radiology-dispensing-crud.blade.php`) — same `@if($showModal)` script scoping issue. Moved `<script>` block after `@endif`. CSR Dispensing already used `@script`/`@endscript` (Livewire 3) and had no such issue.
- **[Migration]** Added `standard_charge` (decimal 12,2, default 0) to `lab_tests` and `lab_panels` tables via `2026_06_22_000063_add_standard_charge_to_lab_tests_and_panels.php`.
- **[Feature]** Added `standard_charge` field to Lab Test CRUD (Laboratory > Tests) — new sortable datagrid column (&#8369; format) and form input in create/edit modal.
- **[Feature]** Added `standard_charge` field to Lab Panel CRUD (Laboratory > Panels) — same datagrid column and form input.
- **[Bug Fix]** Lab Dispensing `addTest()` and `addPanel()` now pull `charge_amount` from the test/panel's `standard_charge` instead of hardcoding `'0'`. Users can still override charges per line item in the dispensing form.
- **[Bug Fix]** Added missing `updated_by`/`updated_date` in `LabTestCrud::create()` and `LabPanelCrud::create()` to prevent NOT NULL constraint errors (same issue as previously fixed in supply CRUDs).
- **[Migration]** Added clinical documentation columns to `er_admissions` via `2026_06_22_000064_add_clinical_documentation_to_er_admissions.php` — `history_of_present_illness`, `physical_exam_findings`, `assessment`, `orders_summary`, `discharge_instructions`.
- **[Migration]** Added `er_admission_id` FK to `lab_requests`, `csr_requests`, and `radiology_requests` via `2026_06_22_000065_add_er_admission_id_to_dispensing_requests.php` — links dispensing orders back to the originating ER admission. Request models (`LabRequest`, `CsrRequest`, `RadiologyRequest`) updated with `belongsTo(ErAdmission::class)`; `ErAdmission` model updated with `hasMany` for all three.
- **[ER Treatment Form]** Replaced the simple Orders textarea with a full **Orders & Interventions** section containing:
  - **Orders Summary** textarea (manual documentation of meds, IV fluids, consults, procedures)
  - **Laboratory Orders** card — datagrid of linked lab requests with status badges (Pending/Processing/Completed/Cancelled), colored by priority (STAT red/Urgent amber/Routine blue), items as tags, total charge, creation date. "Request Lab Test" button opens a modal checklist of all available `lab_tests` with standard charges.
  - **Supply Orders (CSR)** card — same pattern: datagrid of linked `csr_requests` with item names/quantities, status badges, totals. "Request Supply" modal lists available items from `items` table with unit costs.
  - **Radiology Orders** card — same pattern: datagrid of linked `radiology_requests` with exam names/modalities, status badges, totals. "Request Exam" modal lists available `radiology_exam_types` with standard charges.
  - All "Place Order" buttons create properly structured requests (with `request_code` auto-generated using the same LR-/CR-/RR- pattern, `er_admission_id`, patient association for lab, items with charges, etc.). Orders appear in the respective **Lab Dispensing**, **CSR Dispensing**, and **Radiology Dispensing** modules automatically, and status updates from dispensing flow back to the ER datagrid.
- **[Redesign]** Completely redesigned ER Area (Emergency Room > ER Area) — replaced the simple edit/disposition modal workflow with a full-screen treatment form accessible via a prominent **"Treat"** button on the datagrid.
  - **Patient Header Card** — displays encounter #, acuity badge, patient demographics (age/gender), assigned bed/zone, attending physician, arrival time + elapsed duration, and triage vital signs (Temp, HR, RR, BP, SpO2, Pain Score, LOC, Arrival Mode) in a compact grid.
  - **Initial Diagnosis Banner** — amber highlight bar showing the triage initial diagnosis.
  - **3-tab Alpine.js SPA form** with structured clinical documentation:
    - **Clinical Notes** — History of Present Illness, Physical Examination Findings, Assessment & Plan, Treatment Notes (each in separate cards with icons).
    - **Orders & Interventions** — Medications, IV fluids, lab/imaging orders, procedures performed, consults (monospace textarea with placeholder examples).
    - **Disposition** — Disposition dropdown with completion warning, discharge instructions textarea, and a "Confirm Disposition" button that sets disposition, marks encounter as Completed, frees the ER bed, and persists discharge instructions.
  - **Orders & Interventions Tab** — redesigned with 3 service-specific cards (Lab, CSR, Radiology) each showing a datagrid of linked requests with real-time status, plus "Request" buttons that open modals for placing new orders directly from the ER encounter.
  - **Datagrid improvements** — "Treat" and "Dispo" buttons as labeled pill buttons instead of icon-only buttons; patient age/gender in subtext; bed zone name; compact time display.

### 2026-06-20 (Later)
- **[Database]** Updated `provinces_cities` table to include a `parent_id` column (self-referencing FK) to correctly establish the hierarchy: `Country > Region > Province > City > Barangay`.
- **[Refactor]** Consolidated `ProvinceCrud` and `CityCrud` into a single `ProvinceCityCrud` component that uses a `type` parameter (`province` or `city`) to distinguish functionality and enforce the `parent_id` relationship.
- **[Refactor]** Modernized the Patient Registration form into a 5-tab SPA using Alpine.js (`x-data="{ activeTab: 1 }"`), significantly improving UX. Replaced standard submission with a Previous/Next wizard flow.
- **[Bug Fix]** Fixed a bug in Patient Registration where `updated_by` and `updated_date` were missing during `create`, causing a SQL `NOT NULL` constraint error.

### 2026-06-21
- **[Migrations]** Ran migrations `2026_06_20_154851_create_er_acuity_levels_table.php`, `2026_06_20_154852_create_er_mobility_statuses_table.php`, and `2026_06_20_154852_create_er_triage_records_table.php`. Note: Changed `ON DELETE SET NULL` to `ON DELETE NO ACTION` to fix SQL Server cycle errors.
- **[CRUD Built]** Built 3 Emergency Room modules: Acuity Levels (Global ESI levels 1-5), Mobility Statuses (Global), and Triage Records (Tenant-scoped).
- **[SPA Refactor]** Upgraded the `ErTriageRecordCrud` to a 2-step Alpine.js SPA wizard to combine Quick Patient Registration and clinical triage data entry seamlessly into one form, with multi-step backend validation via Livewire's `nextStep` method.

### 2026-06-21 (Later)
- **[Migrations]** Added `status` to `er_triage_records`. Created `er_zones`, `er_beds`, and `er_admissions` tables to handle ER Area workflow.
- **[Workflow]** Added a "Transfer to ER" workflow to `ErTriageRecordCrud` allowing users to assign an ER bed, attending physician, and mode of arrival, automatically creating an active `er_admissions` encounter and changing triage status to `Transferred`.

### 2026-06-22 (Later)
- **[Enhancement]** Enhanced ER Area Orders & Interventions tab with real-time status sync between ER and Lab/CSR/Radiology dispensing modules:
  - Added Livewire event listeners (`refreshErOrders`) so dispensing status changes propagate back to the open ER treatment form in real-time.
  - Added `wire:poll.30s` auto-refresh on the Orders tab for periodic sync of order statuses.
  - Dispensing modules (Lab, CSR, Radiology) now dispatch `refreshErOrders` browser events on create/update/delete when the request is linked to an ER admission.
  - Added inline Cancel action buttons on all three order datagrids (Lab, CSR, Radiology) within the ER treatment form, allowing quick cancellation without navigating to the dispensing module.
  - Added `billing_status` column display to all three order datagrids (Pending/Billed/Exempt/Credited) with color-coded badges.
  - Added inline order status update methods (`updateLabItemStatus`, `updateCsrItemStatus`, `updateRadiologyItemStatus`) to ErAdmissionCrud that cascade item-level status changes up to parent request.
  - Enhanced Lab Order modal with priority dropdown (Routine/Urgent/STAT) and notes input field.
  - Enhanced CSR Order modal with per-item quantity input fields and notes field (previously defaulted to qty=1 with no quantity UI).
  - Enhanced Radiology Order modal with priority dropdown and notes input field.
  - Added `erAdmission()` BelongsTo relationship to `RadiologyRequest` model for consistency with `LabRequest` and `CsrRequest`.
- **[UI]** Added inline per-item status change dropdowns to all three order datagrids in the ER treatment form:
  - Each order item now shows its name + current status badge + a vertical dots (&#8942;) dropdown menu with available status transitions (e.g. Pending → Collected/Processing/Completed/Cancel, Approved → Partial/Complete/Cancel).
  - Status transitions are computed per-item based on current status (prevents invalid transitions).
  - Uses Alpine.js `x-data`/`x-show`/`@click.outside` for dropdown toggle behavior with auto-close on outside click or Escape key.
  - Status changes cascade up to the parent request status via existing `updateLabItemStatus`/`updateCsrItemStatus`/`updateRadiologyItemStatus` methods.
  - Item pills are now displayed in a vertical list (`.space-y-1`) instead of inline wrap for better readability with status badges.
- **[Bug Fix]** Fixed `Unable to locate a class or view for component [app-layout]` error on Radiology Dispensing page (`GET /radiology/dispensing`). Page `resources/views/pages/radiology/dispensing.blade.php` was using `<x-app-layout>` instead of `<x-layouts.app>`, which caused a 500 error. Fixed by aligning with the pattern used by `lab/dispensing.blade.php` and `supply/dispensing.blade.php`.
- **[Feature]** Added Charge Slip / Billing Pickup workflow to all three dispensing modules (Lab, CSR, Radiology):
  - Added `updateBillingStatus(int $id, string $status)` method to `LabDispensingCrud`, `CsrDispensingCrud`, and `RadiologyDispensingCrud` — updates `billing_status` on the request record and dispatches `refreshErOrders` to ER treatment form if linked to an ER admission.
  - Added inline billing status dropdown (Alpine.js `x-data`) in the Actions column of each dispensing datagrid, with a wallet icon button that opens contextual options based on current status:
    - **Pending** → Mark as Billed, Mark as Exempt, Mark as Credited
    - **Billed** → Mark as Exempt, Mark as Credited, Reset to Pending
    - **Exempt** → Mark as Billed, Mark as Credited, Reset to Pending
    - **Credited** → Mark as Billed, Mark as Exempt, Reset to Pending
  - The request code (LR-XXXXX / CR-XXXXX / RR-XXXXX) serves as the charge slip reference when `billing_status` is set to Billed, enabling future billing module pickup via `billing_id`.
  - All status changes cascade to the ER treatment form in real-time when the request originates from an ER admission.
- **[Feature]** Added item-level status management ("task done / item given" workflow) to all three dispensing modules:
  - Added `updateItemStatus(int $itemId, string $status)` method to each dispensing component — updates a single item's status and cascades to parent request status (e.g., all items Completed → parent Completed; mixed Processing/Collected → parent Processing).
  - Added `completeAllItems(int $requestId)` method to each dispensing component — bulk-marks all items as Completed/Complete with a single click and dispatches `refreshErOrders` to the ER treatment form.
  - Added green checkmark "Complete All" button in the datagrid Actions column (visible when request is not already Completed/Complete/Cancelled), with `wire:confirm` confirmation dialog.
  - Enhanced the Detail modal items table in all three modules:
    - **Lab**: Added per-item status buttons — Pending → Collect / Done, Collected → Process / Done, Processing → Done. Status badges are now color-coded.
    - **CSR**: Added Status column (was missing) with color-coded badges. Added per-item actions — Pending → Approve / Done, Approved → Partial / Done, Partial → Done.
    - **Radiology**: Added per-item status buttons — Pending → Approve / Done, Approved → Partial / Done, Partial → Done.
  - All item status changes in the detail modal trigger `$refresh` so the modal UI updates in real-time without manual close/reopen.
- **[Feature]** Added partial fulfillment / group dispensing workflow to CSR Dispensing:
  - **Migration** `2026_06_22_000066_add_given_quantity_to_csr_request_items_table.php` — adds `given_quantity` (decimal 12,2, default 0) column to `csr_request_items` to track cumulative quantity actually dispensed per item. ✅ RUN
  - Added `openDispense(int $id)` method to `CsrDispensingCrud` — opens a modal showing unserved items (status ≠ Complete/Cancelled) grouped together, with their requested qty, already-given qty, and a "Qty to Give" input.
  - Added `processDispense()` method — validates and updates each item: adds qty to `given_quantity`, sets item status to `Partial` (if partial) or `Complete` (if fully given), recalculates `total_cost` as `given_quantity × unit_cost`, cascades parent request status (all Complete → Complete; any Partial → Partial), and recalculates `total_amount` from all items' dispensed costs.
  - Added amber clipboard-list "Dispense" button in the datagrid Actions column (visible when request is not already Complete/Cancelled).
  - Dispense modal table shows: item name/code/unit, requested qty, already given (green if > 0), max-constrained qty input, unit cost, and real-time line total via `wire:model.live`. Footer shows aggregate "Total to Dispense".
  - Dispatches `refreshErOrders` to ER treatment form when request originates from an ER admission.

### 2026-06-22 (Pharmacy Module)
- **[MODULE BUILT]** Pharmacy Dispensing module — dedicated medication dispensing module separate from CSR/Supply:
  - **Migrations** — Created and ran `2026_06_22_000067_create_pharmacy_requests_table.php` and `2026_06_22_000068_create_pharmacy_request_items_table.php` (2 new tables with tenant_id, er_admission_id FK for ER integration, billing_status + billing_id for billing module pickup, medication-specific fields: dosage, frequency, route, duration). ✅ RUN
  - **Models** — Created `App\Models\Pharmacy\PharmacyRequest` (with HasMany items + BelongsTo requestingDepartment, requestingDoctor, erAdmission) and `App\Models\Pharmacy\PharmacyRequestItem` (with BelongsTo pharmacyRequest + BelongsTo item).
  - **Livewire Component** — `App\Livewire\Pharmacy\PharmacyDispensingCrud` with full CRUD, medication-specific fields (dosage, frequency, route dropdown: PO/IV/IM/SC/SL/TOP/PR/INH, duration), auto-generated request codes (PR-XXXXX), department/doctor assignment, priority selection (Routine/Urgent/STAT), dynamic line item table, real-time total calculation, item-level status management (Pending → Approve/Complete → Partial/Complete → Done), item status cascade to parent, per-item status buttons in detail modal, bulk Complete All with confirmation, cancel support (cascading to items), billing status dropdown (Pending/Billed/Exempt/Credited), and partial fulfillment dispensing modal (qty-to-give inputs with max constraints, real-time line totals via `wire:model.live`, aggregate footer).
  - **Blade Views** — Created `resources/views/livewire/pharmacy/pharmacy-dispensing-crud.blade.php` (625+ lines) with full datagrid, create/edit modal, detail modal, dispense modal, cancel confirmation, billing pickup UI. Created `resources/views/pages/pharmacy/dispensing.blade.php`.
  - **Routes** — Added `GET /pharmacy/dispensing` under `pharmacy.*` route prefix.
  - **Sidebar** — Added "Pharmacy" collapsible sub-menu with "Dispensing" link (emerald theme), positioned between CSR/Supply and Procedures.
   - **ER Integration** — Dispatching `refreshErOrders` to ER treatment form when request originates from an ER admission (via `er_admission_id`).

### 2026-06-22 (Pharmacy Module Expansion — Drug Formulary, Batches, Inventory, Procurement)
- **[MODULE BUILT]** Drug Formulary / Drug Master — dedicated drug catalog (separate from CSR `items`):
  - **Migration** `2026_06_22_000069_create_drugs_table.php` — `drugs` table with drug_code, generic_name, brand_name, drug_strength, dosage_form, therapeutic_class, drug_schedule, route, active_ingredient, manufacturer, side_effects, contraindications, reorder levels. Multi-tenant with global scope.
  - **Model** `App\Models\Pharmacy\Drug.php` — hasMany DrugBatch, global scope history=1.
  - **CRUD** `DrugCrud.php` + drug-crud.blade.php + drugs.blade.php page with sortable datagrid, create/edit modal, archive.
- **[MODULE BUILT]** Drug Batches / Inventory (Lot/Expiry Tracking):
  - **Migration** `2026_06_22_000070_create_drug_batches_table.php` — `drug_batches` with batch_no, expiry_date, quantity_on_hand, unit_cost, selling_price, location, FK→drugs, composite unique (drug_id, batch_no, tenant_id, history).
  - **Model** `App\Models\Pharmacy\DrugBatch.php` — belongsTo Drug, scopeAvailable() for FEFO (quantity > 0 AND expiry >= today).
  - **CRUD** `DrugBatchCrud.php` + drug-batch-crud.blade.php + drug-batches.blade.php page.
- **[MODULE BUILT]** Stock Dashboard (Inventory Overview):
  - **CRUD** `DrugInventoryCrud.php` — computes 6 KPIs (total_stock, batch_count, near_expiry, expired, avg_cost, stock_value) plus per-drug totals with inline batch expansion. Summary cards row at top.
  - **View** `drug-inventory-crud.blade.php` + inventory.blade.php page.
- **[MODULE BUILT]** Purchase Order Management (Procurement):
  - **Migration** `2026_06_22_000071_create_purchase_orders_table.php` — `purchase_orders` with po_code, supplier_id, order_date, expected_date, status (Draft/Submitted/Approved/Partial/Received/Cancelled), total_amount, notes, FK→suppliers.
  - **Migration** `2026_06_22_000072_create_purchase_order_items_table.php` — `purchase_order_items` with purchase_order_id, drug_id, qty_ordered, qty_received, unit_cost, total_cost, batch_no, expiry_date. No tenant_id (inherited via PO parent).
  - **Models** `PurchaseOrder.php` (HasMany items + BelongsTo supplier), `PurchaseOrderItem.php` (BelongsTo purchaseOrder + BelongsTo drug).
  - **CRUD** `PurchaseOrderCrud.php` — full workflow: Draft→Submit→Submitted→Approve→Approved→Receive→Partial/Received. GRN processing auto-creates DrugBatch + StockAdjustment records on receipt.
  - **View** `purchase-order-crud.blade.php` + purchase-orders.blade.php page.
- **[MODULE BUILT]** Stock Adjustments (Write-off, Damage, Theft, Return, Transfer, Correction):
  - **Migration** `2026_06_22_000073_create_stock_adjustments_table.php` — `stock_adjustments` with adjustment_code, drug_id, drug_batch_id, adjustment_type, quantity (+/-), unit_cost, reason, reference_type, reference_id for full audit trail.
  - **Model** `App\Models\Pharmacy\StockAdjustment.php` — belongsTo Drug + DrugBatch.
  - **CRUD** `StockAdjustmentCrud.php` — drug autocomplete search, batch dropdown, auto-adjusts DrugBatch quantity_on_hand on save.
  - **View** `stock-adjustment-crud.blade.php` + stock-adjustments.blade.php page.
- **[UPDATED]** Dispensing now uses Drug model instead of Item:
  - **Migration** `2026_06_22_000074_add_drug_id_to_pharmacy_request_items_table.php` — adds drug_id (FK→drugs) and drug_batch_id (FK→drug_batches) to pharmacy_request_items.
  - **PharmacyDispensingCrud** updated: `addItem()` now searches Drug table by generic_name/brand_name/drug_code, auto-fills dosage from drug_strength and route from drug route. `processDispense()` now requires batch selection, deducts from DrugBatch quantity_on_hand, creates StockAdjustment (type=Dispensed, reference=Dispensing) for audit trail.
  - **View** updated: drug search dropdown showing generic_name + brand_name + strength, batch selector in dispense modal (FEFO-ordered, shows batch_no + expiry + available qty), batch info in detail modal.
- **[MODULE BUILT]** Patient Medication Record — patient search with autocomplete showing all pharmacy requests (via ER admissions). `PatientMedicationRecord.php` + view + page.
- **[MODULE BUILT]** Pharmacy Reports — 3-tab report: Expiry Report (grouped by expiry category), Stock Status (Low/Over/Adequate), Consumption Report (date-filtered, most consumed drugs).
- **[SEED DATA]** Added pharmacy seed data to `DemoDataSeeder`: 23 drugs (Cardiovascular, Antibiotics, Analgesics, GI, Respiratory, Endocrine, IV Fluids), 73 drug batches with varied expiry dates, 3 purchase orders (Received/Submitted/Approved), 3 stock adjustments. All idempotent via `updateOrCreate`.
- **[BUG FIX]** Fixed SQL Server multiple cascade paths error on migration 000074 — changed `onDelete('set null')` to no explicit onDelete on drug_batch_id FK. PharmacyRequestItem model updated with drug() and drugBatch() BelongsTo relations.
- **[BUG FIX]** Fixed 500 error on `/pharmacy/stock-adjustments` and `/pharmacy/inventory` — both `stock-adjustment-crud.blade.php` and `drug-inventory-crud.blade.php` were using `$items->links('livewire.custom-pagination')` referencing a pagination view that doesn't exist. Changed to default `$items->links()` (Laravel Tailwind pagination), consistent with all other components.
- **[FEATURE]** Added Pharmacy ordering to ER Area (Orders & Interventions tab) — follows same pattern as Lab/CSR/Radiology orders:
  - **ErAdmission.php** — added `pharmacyRequests()` HasMany relationship
  - **ErAdmissionCrud.php** — added Pharmacy order modal with drug search (generic_name, brand_name, strength, dosage_form), priority selection (Routine/Urgent/STAT), per-item quantity inputs; `requestPharmacyOrder()` creates PR-xxxxx request with items; `updatePharmacyItemStatus()` and `cancelPharmacyRequest()` for inline status management
  - **View** — added Pharmacy Orders card (datagrid with Request #, Medications, Priority, Status, Billing, Total, Date, Actions) and Order Medications modal with drug checklist
  - **Status Sync** — `refreshErOrders` already dispatched from `PharmacyDispensingCrud` on dispense/update/cancel/billing-change; `wire:poll.30s` auto-refresh on the orders tab

### 2026-06-22 (UI/UX Modernization)
- **[Design Standards]** Established a new premium aesthetic for ER SPA modules (Triage & ER Area) to serve as a baseline for future UI development:
  - **Glassmorphism:** All modal backgrounds and sticky datatable headers use frosted glass effects (`bg-slate-900/60 backdrop-blur-md` for modals, `bg-gray-50/90 backdrop-blur-md sticky top-0 z-10` for table headers). Modals also use `transform transition-all shadow-2xl` for smooth entry and depth.
  - **Premium Gradients:** The Patient Encounter summary card and other critical header sections use vibrant gradients (`bg-gradient-to-br from-light-blue-600 via-light-blue-700 to-indigo-800`) instead of flat solid colors.
  - **Datatable Layouts:** Standard tables are wrapped in clean borders with subtle shadows (`rounded-2xl border border-gray-200 shadow-md bg-white overflow-hidden`).
  - **Hover Micro-Interactions:** Table rows use smooth color transitions (`hover:bg-slate-50 transition-colors duration-200`) and use the `group` class to only reveal action buttons (`group-hover:opacity-100`) when the row is actively hovered, drastically reducing visual clutter.

### 2026-06-23 (Billing Module)
- **[MODULE BUILT]** Billing & Invoices — full billing workflow with 3 new tables (billing_invoices, billing_invoice_items, billing_payments), 3 models, single Livewire component with 3-tab SPA (For Billing / Invoices / Payments), auto-generated INV-xxxxx/PAY-xxxxx codes, patient search, multi-service invoice creation, payment recording with balance recalculation, invoice cancellation with source request rollback, summary dashboard cards, sidebar link, route, seed data. See top changelog for full details.
- **[ENHANCEMENT]** Added `billingInvoices()` hasMany relationship to Patient and ErAdmission models
- **[BUG FIX]** Fixed '—' patient_name display for Pharmacy/CSR/Radiology/Procedure unbilled requests — now resolves patient via `erAdmission.patient` eager-loaded relationship
- **[FEATURE]** Added invoice print/PDF generation — new `GET /billing/invoice/{id}/print` route with dedicated print-optimized view (`invoice-print.blade.php`) with `@media print` CSS, professional layout with source badges, payment history, and print button
- **[FEATURE]** Added Billing Reports tab (Tab 4) with aging receivables (0-30/31-60/61-90/90+ day buckets), daily collections (last 30 days), and monthly revenue (12-month history with collection rate %)
- **[UI/UX]** Applied premium gradient design standards to summary cards (amber/rose/emerald gradients), glassmorphism to modals (`transform transition-all shadow-2xl`, `backdrop-blur-md` sticky headers), sticky table headers with frosted glass effect, and group-hover opacity reveal for action buttons

### 2026-06-23 (In-Patient Nursing Module)
- **[MODULE BUILT]** In-Patient Nursing — full nursing workflow for admitted patients, modeled after the ER Area pattern:
  - **Tables (84-85):** `nursing_vitals` and `nursing_care_notes` — both tenant-scoped, linked to `admissions` table
  - **Models:** `App\Models\Nursing\NursingVital`, `App\Models\Nursing\NursingCareNote` — with `admission()` BelongsTo and global `history=1` scope
  - **Admission model** updated with `nursingVitals()` and `nursingCareNotes()` HasMany relationships
  - **Livewire Component:** `App\Livewire\Nursing\NursingCrud` — full SPA with:
    - **List view:** Admitted patients datagrid with search/sort/pagination, toggle for discharged patients, 3 summary cards (gradient)
    - **Detail/Treatment view** — patient header with premium gradient card, 4-tab navigation:
      - **Tab 1 — Vitals:** Record/edit/delete vital signs (temp, HR, RR, BP, SpO2, pain, LOC, blood sugar) with abnormal value highlighting, datagrid history
      - **Tab 2 — Care Notes:** SOAP-format nursing notes with shift (AM/PM/Night) tagging, edit/delete
      - **Tab 3 — Orders:** Read-only view of all 5 department orders (Lab, Pharmacy, CSR, Radiology, Procedure) linked via ER admission relationship
      - **Tab 4 — Discharge:** Discharge workflow with date picker, summary notes, ward room status reset
  - **Route:** `GET /nursing` (named `nursing`) — placed between Admission and Billing
  - **Sidebar:** "Nursing" nav link (clipboard-check icon, emerald theme) between Admission and Billing
  - **Seed data:** 1 demo vital record + 1 demo SOAP care note for first admission
- **[ENHANCEMENT]** Added `full_name` accessor assumed on `HealthCareProfessional` — doctor names displayed in nursing module via `attendingPhysician->full_name`

### 2026-06-23 (Nursing Debug & Vite IPv6 Fix)
- **[BUG FIX]** Ran pending migration `2026_06_23_000080_add_admission_id_to_request_tables.php` — fixed `SQLSTATE[42S22]: Invalid column name 'admission_id'` 500 error on nursing page
- **[BUG FIX]** Ran pending migration `2026_06_23_000081_create_nursing_medication_administrations_table.php`
- **[BUG FIX]** Fixed Vite dev server IPv6 issue — added `host: '127.0.0.1'` and `port: 5173` to `vite.config.js` to force IPv4 binding. Previously Vite was binding to `[::1]:5173` (IPv6) while the page was served on `127.0.0.1:8000` (IPv4), causing cross-protocol resource loading failures in Windows browsers.
- **[VITE]** Built production assets (`npm run build`) to `public/build/` for fallback when Vite dev server is down
- **[INVESTIGATED]** "Place Order button does nothing" — Livewire component initializes correctly, wire:snapshot valid, livewire.js loads from `/vendor/livewire/livewire.js?id=4735e441`. Suspected root cause: Vite IPv6→IPv4 mismatch causing app.js failure on Windows. Vite dev server config fixed; needs browser retest.

---
### 2026-06-23 (Nursing Procedure Order & Billing Fixes)
- **[BUG FIX]** Fixed `placeProcedureOrder()` in `NursingCrud.php` — was passing `requesting_person` and `request_notes` columns that don't exist on `procedure_requests` table (table uses `notes` instead). Changed `unset($data['notes'], $data['total_amount'], $data['total_charge'])` to `unset($data['requesting_person'], $data['request_notes'], $data['total_charge'])` — keeps `notes` and `total_amount` which exist on the table.
- **[BUG FIX]** Fixed billing module `getUnbilledRequestsProperty()` in `BillingCrud.php` — Pharmacy, CSR, Radiology, and Procedure unbilled queries only eager-loaded `erAdmission.patient`, so nursing-created orders (which have `admission_id` but no `er_admission_id`) showed `'—'` for patient name. Added `'admission.patient'` eager load with fallback: tries `erAdmission.patient` first, then `admission.patient`.
- **[TABLES]** Added `nursing_medication_administrations` (table 86) documentation to `tables.md`.

### 2026-06-23 (E-Claims Phase 3 — ECS Upload, Cloud Storage, Acknowledgment, eSOA, Voucher)
- **[MODULE BUILT]** PhilHealth E-Claims Phase 3 — complete submission & payment workflow for e-Claims:
  - **Migrations** — Created and ran `2026_06_23_000082_add_ecs_cloud_acknowledgment_to_philhealth_claims_table.php` and `2026_06_23_000083_add_ecs_cloud_to_claim_batches_table.php` (10 new columns on `philhealth_claims`: ecs_tracking_no, ecs_uploaded_at, cloud_folder, cloud_file, cloud_uploaded_at, acknowledgment_code, acknowledgment_date, voucher_number, voucher_amount, voucher_date. 6 new columns on `claim_batches`: ecs_tracking_no, ecs_uploaded_at, cloud_folder, cloud_uploaded_at, acknowledgment_code, voucher_reference). ✅ RUN
  - **Models Updated** — `PhilhealthClaim` — added casts for decimal, datetime of new columns; added `scopeForBatch()`. `ClaimBatch` — added casts for ecs_uploaded_at, cloud_uploaded_at.
  - **Services** (3 new):
    - **EcsService** (`app/Services/Philhealth/EcsService.php`) — SOAP client for PhilHealth ECS `eClaimsUpload()` web service. Builds XML via XmlBuilder, sends via curl SOAP envelope, parses response for tracking number, supports single-claim and batch upload.
    - **CloudStorageService** (`app/Services/Philhealth/CloudStorageService.php`) — SOAP client for PhilHealth eCCSA Cloud Storage API. `createFolder()` and `uploadFile()` methods with base64-encoded XML content.
    - **AcknowledgmentService** (`app/Services/Philhealth/AcknowledgmentService.php`) — workflow orchestration with `acknowledgeClaim()`, `acknowledgeBatch()`, `recordVoucher()`, `markDenied()` — updates claim/batch status and stamps all PhilHealth-related columns.
  - **eSOA Report** — created `resources/views/pages/philhealth/esoapdf.blade.php` — print-optimized Electronic Statement of Account with `@page` CSS rules. Shows hospital header, patient/member info, ICD-10/case rate, itemized claim items with totals, case rate breakdown (HF/PF), acknowledgment/voucher info, signature block. Designed for browser "Print to PDF". Route: `GET /philhealth/claims/{claim}/esoapdf` named `philhealth.claims.esoapdf`.
  - **BatchCrud Updated** — added ECS upload modal, cloud upload modal, acknowledgment modal with code input. New methods: `uploadBatchToEcs()`, `uploadBatchToCloud()`, `acknowledgeBatch()`. Status badges (ECS/CLOUD) in datagrid. Detail modal shows ECS tracking, cloud folder, acknowledgment code, voucher reference. Action buttons conditional on batch status.
  - **ClaimCrud Updated** — added ECS upload, cloud upload, acknowledgment, voucher recording, deny workflow. New methods: `uploadClaimToEcs()`, `uploadClaimToCloud()`, `acknowledgeClaim()`, `recordVoucher()`, `markDenied()`, `downloadEsoa()`, `downloadClaimXml()`. Status badges (ECS/CLOUD/V) in datagrid. Detail modal shows all Phase 3 fields with conditionally visible action buttons (ECS/Cloud upload for Submitted/Acknowledged, Acknowledge for Submitted, Voucher for Acknowledged/Submitted, Deny for non-Paid/Denied statuses, eSOA download always available).
  - **Claim Status State Machine Expanded**: Draft → For Submission → Submitted → ECS Upload → Cloud Upload → Acknowledged → Paid / Denied (with voucher tracking).
  - **Routes** — added eSOA route with route-model binding.
-   **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (tables 87-90 column additions).

### 2026-06-23 (E-Claims Phase 4 — Status Polling, eGov QR, CF5/DRG, Reports, Rejection Handling)
- **[MODULE BUILT]** PhilHealth E-Claims Phase 4 — monitoring, DRG analysis, QR codes, and reporting:
  - **Migrations** — Created and ran `2026_06_23_000084_create_claim_rejections_table.php`, `2026_06_23_000085_create_claim_status_polls_table.php`, `2026_06_23_000086_create_cf5_shadow_billings_table.php` (3 new tables: claim_rejections, claim_status_polls, cf5_shadow_billings).
  - **Models** — `ClaimRejection`, `ClaimStatusPoll`, `Cf5ShadowBilling` with BelongsTo philhealthClaim.
  - **Services** (4 new):
    - **ClaimStatusPollingService** — SOAP-based status polling with `pollClaim()`, `pollBatch()`, `pollAllSubmitted()`.
    - **Cf5ShadowBillingService** — DRG code derivation from ICD-10 prefix, 15 DRG codes with weights (0.7–1.6), HF/PF split calculation, break-even analysis.
    - **QrCodeService** — eGov Super App QR URL generation with fallback SVGs.
  - **Artisan Command** — `php artisan philhealth:poll-claims` with `--batch=` and `--claim=` options.
  - **ClaimCrud** — added rejection modal, CF5 modal, QR modal, status poll modal, poll history modal, resubmit flow.
  - **ReportsCrud** — full dashboard with 4 summary cards, status pie chart, aging buckets, monthly trend table.
  - **Claim Status State Machine Final**: Draft → For Submission → Submitted → Acknowledged → Paid / Denied → Resubmitted → For Submission (loop).

### 2026-06-23 (Phase 5 — CF5 Print Form, CSV Export, Scheduler, Email Notifications, Batch Resubmission)
- **[UPGRADE]** Added 5 new productivity features:
  - **CF5 Print Form** — `resources/views/pages/philhealth/cf5pdf.blade.php` — print-optimized DRG shadow billing report with DRG classification, financial comparison table, break-even analysis, itemized claim items. Route: `GET /philhealth/claims/{claim}/cf5pdf` named `philhealth.claims.cf5pdf`. Print CF5 button in CF5 modal.
  - **CSV Export** — Added `exportCsv()` to `ReportsCrud.php` — streams CSV with summary, status breakdown, aging, monthly trend. Export CSV button in reports view. No external packages needed — uses PHP `fputcsv()` with `response()->streamDownload()`.
  - **Scheduled Task** — Added daily scheduling to `routes/console.php`: `Schedule::command('philhealth:poll-claims')->dailyAt('02:00')` with `withoutOverlapping()`, `runInBackground()`, log file output.
  - **Email Notifications** — Created `app/Mail/ClaimStatusNotification.php` (Mailable with markdown template) and `resources/views/mail/claim-status-notification.blade.php`. Integrated into `AcknowledgmentService` — sends email on acknowledge/paid/denied status changes to `philhealth.hospital.email`.
  - **Batch-Level Resubmission** — Added `resubmitBatch()` to `BatchCrud.php` — resubmits all denied claims in a batch with rejection carry-over and email notification. Resubmit Denied button in batch detail modal.
  - **Missing Phase 4 Modals** — Added CF5/DRG, QR Code, Rejection, Status Poll, and Poll History modals to `claim-crud.blade.php` (were present in Controller but missing from view).
  - **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (tables 91-93).

### 2026-06-23 (Phase 5 continued — Dashboard Widget, Excel Export, PDF Downloads, .env, Tests)
- **[UPGRADE]** Added 5 more productivity features:
  - **Dashboard Widget** — Updated `resources/views/components/⚡dashboard.blade.php` — Livewire inline component now shows 8 PhilHealth stat cards: total claims, pending (For Submission), submitted/acknowledged, approval rate %, paid count, denied count, this month claimed amount (₱), this month collected amount (₱ with collection %). Auto-fetches on mount via `PhilhealthClaim` model queries.
  - **Excel Export** — Installed `phpoffice/phpspreadsheet ^5.8`. Added `exportExcel()` to `ReportsCrud.php` — generates formatted `.xlsx` with styled headers (dark blue), auto-sized columns, and 4 sections: Summary (6 metrics), Status Breakdown (status/count/amount), Aging (bucket/count/amount), Monthly Trend (12-month with collection %). Savest to temp file with `deleteFileAfterSend`. View added "Excel" button (blue theme) next to CSV button.
  - **CF5 & eSOA PDF Downloads** — Installed `barryvdh/laravel-dompdf ^3.1`. Updated both routes in `routes/web.php` (`claims/{claim}/esoapdf` and `claims/{claim}/cf5pdf`) to use `\Barryvdh\DomPDF\Facade\Pdf::loadView()` with `->download()` instead of returning raw HTML views. Both now generate downloadable `.pdf` files.
  - **Environment Variables** — Added full PhilHealth config block to `.env.example` (PHILHEALTH_HOSPITAL_CODE, PHILHEALTH_CEWS_USERNAME, PHILHEALTH_ECLAIMS_USERNAME, PHILHEALTH_CLOUD_USERNAME, PHILHEALTH_DTD_URL, and all related endpoint/credential/timeout vars) with descriptive comments.
  - **Unit Tests** — Created 10 passing tests (25 assertions) in `tests/Unit/Philhealth/`:
    - `Cf5ShadowBillingServiceTest.php` — 5 tests: DRG derivation for hypertension, unmapped ICD-10 fallback (DRG-999), HF/PF 60/40 split math, normal delivery DRG-015 mapping, claimed amount fallback when no case rate.
    - `XmlBuilderTest.php` — 5 tests: valid XML document structure, transmittal attribute `pHospCode`, claim element with `pHIClaimNo`, `ICDCODES` element when ICD-10 is set, `PACKAGE` element when case rate is set.
  - **Packages Installed**: `phpoffice/phpspreadsheet` (Excel), `barryvdh/laravel-dompdf` (PDF).
- **[DOCS]** Updated `ragdata.md` (changelog) — this entry.

### 2026-06-23 (PhilHealth PIN on Patients — Triage/Registration PIN capture, auto-sync)
- **[SCHEMA]** Added `philhealth_pin` (varchar(30), nullable, indexed) to `patients` table via migration `2026_06_23_000088_add_philhealth_pin_to_patients_table.php` — direct PIN storage on patient record for quick lookup during triage, ER, and claims entry (no FK join needed).
- **[MODEL]** `Patient.php` — added `saving` boot event that auto-syncs `philhealth_pin` from the linked `PhilhealthMember.pin` when `philhealth_member_id` is set but `philhealth_pin` is empty.
- **[PATIENT REGISTRATION]** `PatientCrud.php` — added `$philhealth_pin` property, validation rule, openEdit/resetForm population. `patient-crud.blade.php` — added PhilHealth PIN text input on Tab 1 next to member dropdown.
- **[TRIAGE]** `ErTriageRecordCrud.php` — added `$patient_philhealth_pin` property for new patient quick registration form, included in validation and Patient::create() call. `er-triage-record-crud.blade.php` — added PhilHealth PIN input field in the "New Patient" quick registration section.
- **[DOCS]** Updated `tables.md` (patients table: new column + migration reference). Updated `ragdata.md` (this entry).

### 2026-06-23 (CF1-CF5 Schema Gaps — 16 new fields across 3 tables)
- **[SCHEMA]** Added 16 missing CF1-CF5 fields via migration `2026_06_23_000087_add_cf1_cf4_clinical_fields_to_philhealth_tables.php`:
  - **`philhealth_claims` (14 new columns):**
    - `relationship_to_member` — CF1: patient's relationship to PhilHealth member (Self/Spouse/Child/Parent/Sibling/Other)
    - `chief_complaint` — CF4 Part III: reason for consultation
    - `review_of_systems` — CF4 Part III: ROS narrative
    - `blood_pressure`, `heart_rate`, `temperature`, `respiratory_rate` — CF4 Part III: vital signs at admission
    - `lmp_date`, `edc_date`, `gravida`, `para` — CF4 (OB): obstetrical data
    - `attending_physician_id`, `referring_physician_id` — CF2/CF4: FK → health_care_professionals
    - `patient_signature_date`, `physician_signature_date` — CF1/CF4: signature timestamps
    - Added `BelongsTo` relationships: `attendingPhysician()`, `referringPhysician()` in model
  - **`claim_items` (1 new column):**
    - `health_care_professional_id` — CF2: FK → health_care_professionals for per-item HCP attribution (e.g., which doctor's PF)
    - Added `BelongsTo` relationship: `healthCareProfessional()` in model
  - **`philhealth_members` (2 new columns):**
    - `employer_name`, `employer_address` — CF1: employer info for employed members
  - **[DOCS]** Updated `tables.md` with all new columns and migration references. Updated `ragdata.md` (this entry).

### 2026-06-23 (Bug Fix — `$items`/`$claimItems` rename, CI workflow, Walkthrough doc)
- **[BUG FIX]** Fixed `Call to a member function links() on array` error on `/philhealth/claims` (500 error). Root cause: public property `public array $items` (`ClaimCrud.php:49`) shadowed the paginated `$items` variable passed via `compact()` in `render()`. Livewire auto-exposed the array property to the view, so `$items->links()` received an array instead of `LengthAwarePaginator`.
  - Renamed `public array $items` → `public array $claimItems` in `ClaimCrud.php`
  - Updated all internal references: `addItem()`, `removeItem()`, `recalculateTotals()`, `openEdit()`, `syncItems()`, `resetForm()`
  - Updated `claim-crud.blade.php`: `@foreach($items...)` → `@foreach($claimItems...)`, all `wire:model="items.*"` → `wire:model="claimItems.*"`
  - The paginated `$items` (lines 46, 80) now correctly resolves to the `LengthAwarePaginator` from `compact()`.
- **[CI]** Created `.github/workflows/eclaims-tests.yml` — GitHub Actions workflow that runs on push/PR to main/develop when PhilHealth files change. Matrix: PHP 8.2 & 8.3 on Ubuntu. Steps: checkout, PHP setup (mbstring, pdo_sqlite, dom, xml, zip), composer install, `.env` setup, `php artisan test tests/Unit/Philhealth/` (10 tests, 25 assertions). Uses SQLite in-memory — no external DB needed.
- **[DOCS]** Created `ECLAIMS_WALKTHROUGH.md` — comprehensive manual test walkthrough (230+ lines, 42 checkbox test cases). Covers: Claim CRUD, pagination/search, eligibility, ECS/Cloud upload, acknowledgment, voucher, deny/rejection, CF5/DRG, QR code, status polling, resubmission (claim + batch), batch CRUD, reports (CSV + Excel), dashboard widget, PDF downloads (eSOA, CF5, XML), email notifications, scheduler command, state machine diagram.
- **[DOCS]** Updated `ragdata.md` (changelog) — this entry.

### 2026-06-25 (Cross-Module Clinical Dispensing & Billing Visibility Fix)
- **[ENHANCEMENT]** Modified RadiologyDispensingCrud and ProcedureDispensingCrud to eager-load erAdmission.patient, opdConsultation.patient, and dmission.patient, matching the standard set by Lab, CSR, and Pharmacy.
- **[ENHANCEMENT]** Updated blade views for RadiologyDispensingCrud and ProcedureDispensingCrud to utilize the $item->patient_name attribute in their listing tables and detail views.
- **[ENHANCEMENT]** Updated BillingCrud to use the $item->patient_name attribute for all request types when populating the Unbilled Requests queue (tab 1), ensuring OPD consultations accurately display patient names instead of returning missing values. Added opd_consultation_id relation to eager loads and model properties.

- **[DOCS]** Updated `tables.md` to include the newly added `opd_consultation_id` field for Lab, Pharmacy, CSR, Radiology, Procedure, and Billing Invoice tables, reflecting the schema changes made for OPD clinic encounter integration.

### 2026-06-25 (OECB Module — PhilHealth Outpatient Emergency Care Benefit)
- **[MODULE BUILT]** OECB (Outpatient Emergency Care Benefit) — full implementation per PhilHealth Circular 2024-0033:
  - **Migrations (5 new, #101-#105):**
    - `2026_06_25_000101_add_oecb_fields_to_er_tables.php` — adds `is_oecb` (bool), `core_presentation` (varchar 200), `oecb_package_code` (varchar 30), `oecb_fixed_fee` (decimal 12,2) to `er_admissions`
    - `2026_06_25_000102_add_er_admission_id_to_philhealth_claims.php` — adds `er_admission_id` (FK) to `philhealth_claims` for OECB claim-ER admission linking
    - `2026_06_25_000103_create_oecb_core_presentations_table.php` — Annex B lookup: code, name, description, sort_order
    - `2026_06_25_000104_create_oecb_fee_schedules_table.php` — Annex A: package_code, core_presentation_id (FK), description, fixed_fee, composite unique
  - **Models (5):** `App\Models\Oecb\OecbCorePresentation` (with feeSchedules HasMany), `App\Models\Oecb\OecbFeeSchedule` (with corePresentation BelongsTo), `App\Models\Oecb\OecbEntry` (BelongsTo ErAdmission, added 2026-07-06). All with global history=1 scope, no timestamps.
  - **Updated Models:** `ErAdmission` — added `is_oecb` boolean cast, `oecb_fixed_fee` decimal cast (removed 2026-07-06), `oecbEntries()` HasMany relation (added 2026-07-06). `PhilhealthClaim` — added `erAdmission()` BelongsTo.
  - **Livewire Component Updates:**
    - `ErAdmissionCrud` — removed hardcoded `$corePresentations` array, now loads from DB; added `updatedCorePresentation()` hook that auto-populates `oecb_package_code` and `oecb_fixed_fee` from fee schedule on selection; **[2026-07-06]** replaced scalar OECB props with `$oecbEntries[]` array + `addOecbEntry()`/`removeOecbEntry()`/`updatedOecbEntries()`/`syncOecbEntries()` for multi-entry support
    - `ClaimCrud` — added `er_admission_id` field, OECB claim type option, OECB ER admission selector in form, auto-formatted chief complaint via `OecbClaimFormatter::saveFormattedChiefComplaint()` on save
  - **Services (1 new):** `App\Services\Philhealth\OecbClaimFormatter` — `format()` returns `"Declared Complaint [Core Presentation]"`, `saveFormattedChiefComplaint()` persists it, `findOecbAdmission()` locates matching OECB ER admission by patient
  - **XML Integration:** `XmlBuilder` — adds `Chief Complaint` OBSERVATION element in both single and batch XML using `OecbClaimFormatter::format()`
  - **PDF Updates:**
    - `cf4pdf.blade.php` — uses `OecbClaimFormatter::format()` for chief complaint, shows OECB header row + core presentation/package code/fixed fee/ER encounter code when `claim_type === 'OECB'`
    - `cf3pdf.blade.php` — uses `OecbClaimFormatter::format()` for chief complaint
    - `esoapdf.blade.php` — shows OECB badge + ER encounter details when OECB claim
  - **Blade Updates:** `er-admission-crud.blade.php` — OECB toggle in treatment form header with conditional core presentation dropdown, package code/fixed fee auto-fill, OECB badge in datagrid. **[2026-07-06]** Replaced single dropdown with dynamic add/remove entry list showing all entries with per-entry Package Code/Fixed Fee and total sum. `claim-crud.blade.php` — OECB claim type option, Section A2 with ER admission selector + auto-populated OECB data preview, OECB badge in datagrid.
  - **Seeder:** `OecbSeeder` (registered in `DatabaseSeeder`) — 14 Annex B core presentations (DOB–OTH) with codes, names, descriptions, sort order + 14 Annex A fee schedules (package codes OECB-001 to OECB-014, fees ₱5,500–₱13,000) using `updateOrCreate`
- **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (tables 101-104, updated er_admissions/philhealth_claims schemas).

### 2026-06-25 (YAKAP Module — PhilHealth's Yaman ng Kalusugan Para sa Atin Program)
- **[MODULE BUILT]** YAKAP (Yaman ng Kalusugan Para sa Atin) — comprehensive PhilHealth primary care benefit package implementation:
  - **Migrations (6 new, #95-#100):**
    - `2026_06_25_000095_create_yakap_empanelments_table.php` — `yakap_empanelments`: patient enrollment tracking per year with empanelment_code, yes_signed_date/reference_no, primary_care_provider_id, opd_clinic_id, risk_classification (Low/Moderate/High), fpe_completed flag, first_consultation_id, status (Active/Transferred/Expired/Cancelled). Composite unique: (empanelment_code, tenant_id, history).
    - `2026_06_25_000096_create_yakap_fpe_records_table.php` — `yakap_fpe_records`: First Patient Encounter — health risk assessment with lifestyle factors (smoking, alcohol, diet, physical activity), anthropometrics (height/weight/BMI/waist circumference), family/past medical history, immunization history, obstetric history, risk classification + score, status (Completed/Pending/Skipped). FK → yakap_empanelments.
    - `2026_06_25_000097_create_yakap_screenings_table.php` — `yakap_screenings`: 6 cancer screenings tracking (Breast, Cervical, Colon, Prostate, Lung, Oral) with auto-name generation, screening_date, result (Normal/Abnormal/Positive/Negative/Inconclusive), result_details, facility_name, performed_by, next_screening_due, status (Pending/Completed/Overdue/Skipped). FK → yakap_empanelments + patients.
    - `2026_06_25_000098_create_yakap_capitation_payments_table.php` — `yakap_capitation_payments`: capitation payment tracking per empanelment with tranche (1=40% first / 2=60% second), amount, payment_code, payment_date, status (Pending/Paid/Cancelled), philhealth_reference. FK → yakap_empanelments + patients.
    - `2026_06_25_000099_add_yakap_fields_to_opd_consultations_table.php` — adds `is_yakap` (bool), `empanelment_id` (FK), `risk_classification`, `fpe_completed` to `opd_consultations`.
    - `2026_06_25_000100_add_yakap_fields_to_request_tables.php` — adds `is_gamot` to `pharmacy_request_items`, `is_yakap_lab` to `lab_request_items`, `is_yakap_clinic` to `opd_clinics`, `is_yakap_physician` to `health_care_professionals`.
  - **Models (4 new in `app/Models/Yakap/`):** `YakapEmpanelment`, `YakapFpeRecord`, `YakapScreening`, `YakapCapitationPayment` — all with global history=1 scope, BelongsTo relationships to patient/empanelment, audit columns.
  - **Updated Models:** `OpdConsultation` — added `is_yakap` cast, `empanelment()` BelongsTo, `yakapFpeRecord()` HasOne. `Patient` — added `yakapEmpanelments()`, `yakapFpeRecords()`, `yakapScreenings()` HasMany.
  - **Livewire Components (5 new in `app/Livewire/Yakap/`):**
    - `DashboardCrud` — overview KPIs: total empaneled, FPE completed/pending, screenings done/pending/overdue, capitation paid/pending, risk classification breakdown bar chart, quick-link cards.
    - `EmpanelmentCrud` — full CRUD with patient search autocomplete, year/date inputs, YES reference, provider/clinic dropdowns, risk classification, status management, FPE completion toggle.
    - `FpeCrud` — full CRUD with empanelment search, physician selector, lifestyle factors (smoking/alcohol/diet/physical activity), anthropometrics with auto-BMI calculation, family/past medical/immunization/obstetric histories, risk classification.
    - `ScreeningCrud` — full CRUD for 6 cancer screenings with auto-screening_name on type selection, date, result dropdown, result_details, facility, performed_by, status, next_screening_due.
    - `CapitationCrud` — full CRUD for payment tranches 1/2, amount, payment code, date, status, PhilHealth reference number.
  - **Blade Views (6 new in `resources/views/livewire/yakap/`):** `empanelment-crud`, `fpe-crud`, `screening-crud`, `capitation-crud`, `dashboard-crud` — all consistent with existing emerald-theme design patterns (rounded-xl, shadow-sm, hover:bg-gray-50 rows, sortable datagrids, glassmorphism modals).
  - **Page Wrappers (5 new in `resources/views/pages/yakap/`):** `dashboard`, `empanelments`, `fpe-records`, `screenings`, `capitation`.
  - **Routes:** New `Route::prefix('yakap')->name('yakap.')` group in `routes/web.php` with 5 routes: dashboard, empanelments, fpe-records, screenings, capitation.
  - **Sidebar:** New "YAKAP" collapsible sub-menu in `app.blade.php` (heart icon, emerald theme) between PhilHealth and closing `</div>` — with Dashboard, Empanelments, FPE Records, Screenings, Capitation links.
- **[DOCS]** Updated `ragdata.md` (changelog) and `tables.md` (tables 95-100).

### 2026-06-25 (Discount Fields, Fund Sources & OECB Billing — Final Integration)
- **[MIGRATION 105]** `2026_06_25_000105_add_discount_fields_to_billing_invoices` ✅ — Added `discount_type` (varchar 20, nullable: 'percentage'/'fixed'), `discount_value` (decimal 12,2, default 0), `discount_amount` (decimal 12,2, default 0) to `billing_invoices`.
- **[MIGRATION 106]** `2026_06_25_000106_create_fund_sources_table` ✅ — Created `fund_sources` table with `id`, `tenant_id`, `code`, `name`, `description`, `is_active`, `history`, audit. Unique on `(code, tenant_id, history)`.
- **[MIGRATION 107]** `2026_06_25_000107_add_fund_source_id_to_billing_payments` ✅ — Added `fund_source_id` FK → `fund_sources(id)` ON DELETE SET NULL to `billing_payments`.
- **[MODEL]** `App\Models\Billing\FundSource` — New model with global scope `history=1`, `$timestamps=false`.
- **[MODEL]** `App\Models\Billing\BillingPayment` — Added `fundSource()` BelongsTo relationship.
- **[MODEL]** `App\Models\Billing\BillingInvoice` — Already had `discount_value`/`discount_amount` casts + `getNetAmountAttribute()` accessor (`total_amount - discount_amount`).
- **[FEATURE: Discount UI]** `BillingCrud` + `billing-crud.blade.php`:
  - Added `discount_type`, `discount_value`, `discount_amount` public properties.
  - `updatedDiscountType()` / `updatedDiscountValue()` hooks auto-compute discount: percentage → `total * value / 100`, fixed → `min(value, total)`.
  - **Invoice Create Modal**: Discount section with type selector (None/Percentage/Fixed), value input, live preview of computed amount and net total.
  - **Invoice Detail Modal**: Discount row between Total and Paid lines showing `-₱discount_amount` + net total. Uses `net_amount` accessor.
  - **Invoice Print** (`invoice-print.blade.php`): Discount line between Subtotal and Total Paid.
  - `createInvoice()` now persists `discount_type`, `discount_value`, `discount_amount`; balance calculated as `total - discount_amount`.
  - `resetInvoiceForm()` resets all discount fields.
- **[FEATURE: Fund Sources]** `BillingCrud` + `billing-crud.blade.php`:
  - `fund_sources` (collection) and `payment_fund_source_id` properties loaded in `render()` via `FundSource::where('is_active', true)`.
  - **Payment Modal**: Budget Source dropdown (wallet icon) after Payment Method, sourced from DB.
  - `addPayment()` persists `fund_source_id` to `billing_payments`.
  - `resetPaymentForm()` clears `payment_fund_source_id`.
- **[SEEDER]** `DemoDataSeeder` — Added 5 fund sources: PRIVATE-PAY, PHILHEALTH, HMO, GOV-SUBSIDY, CHARITY (all tenant-scoped, idempotent via `updateOrCreate`).
- **[DOCS]** Updated `ragdata.md` (changelog), `tables.md` (table 81 discount columns, table 83 fund_source_id + fundSource relation, new table 105 fund_sources).

### 2026-06-25 (Discount Editing on Existing Invoices — Invoice Detail Modal)
- **[FEATURE]** Discount fields are now editable directly on existing invoices from the Invoice Detail Modal:
  - `BillingCrud.php`: Added `detail_discount_type`, `detail_discount_value`, `detail_discount_amount` properties. `updatedDetailDiscountType()` / `updatedDetailDiscountValue()` hooks + `computeDetailDiscount()` for live preview. `saveInvoiceDiscount()` persists discount and recalculates balance/status.
  - `render()` auto-populates detail discount fields from the invoice when the modal opens.
  - `recalculateInvoiceTotals()` now factors in `discount_amount` when computing balance (so manual charges + discount coexist correctly).
  - **Blade** (`billing-crud.blade.php`): When `$canModifyCharges` is true (invoice not Cancelled), the totals section shows an inline discount editor with type dropdown (%/₱), value input, live preview of amount/net, Save and Clear buttons. On Cancelled invoices, the discount row is read-only.
- **[DOCS]** Updated `ragdata.md` (changelog).

### 2026-06-26 (Delivery and OB Billing Integration)
- **[ENHANCEMENT]** Integrated the Delivery Room (DR) and OB Consultation modules into the Billing queue.
  - **Models**: Updated `LabRequest`, `PharmacyRequest`, `CsrRequest`, `RadiologyRequest`, and `ProcedureRequest` with `drSchedule()` and `obConsultation()` relationships. Their `getPatientNameAttribute()` accessors now properly fallback to DR and OB relationships if the request originated there.
  - **BillingInvoice**: Added `drSchedule()` and `obConsultation()` relationships to map to existing `dr_schedule_id` and `ob_consultation_id` columns.
  - **BillingCrud**: Updated `getUnbilledRequestsProperty()` to eager load DR and OB relationships across all clinical queries and accurately map the `patient_id` and `patient_name`. Updated `createInvoice()` to inject `dr_schedule_id` and `ob_consultation_id` when saving invoices.
- **[DOCS]** Updated `ragdata.md` (changelog). Note: `tables.md` already had these fields documented from a previous iteration.

### 2026-06-26 (Healthcare Professional eClaims Compliance Updates)
- **[MIGRATION]** Created `2026_06_26_154546_add_eclaims_fields_to_health_care_professionals_table` to add `last_name`, `first_name`, `middle_name`, `suffix`, `date_of_birth`, and `tin` columns to the `health_care_professionals` table for PhilHealth eClaims CF4 compliance.
- **[LIVEWIRE]** Updated `HcpCrud.php` to include validation for the new fields, requiring First Name and Last Name. The `save()` method now automatically aggregates these separate fields to seamlessly generate the legacy `full_name` column, preserving compatibility across all other modules.
- **[BLADE]** Redesigned the "Add/Edit Professional" modal in `hcp-crud.blade.php`. Replaced the single "Full Name" field with distinct inputs for First Name, Last Name, Middle Name, Suffix, Date of Birth, and TIN using a responsive grid layout.
- **[DOCS]** Updated `tables.md` (Table 18) and `ragdata.md` (changelog) to document the schema changes.

### 2026-06-26 (Doctors Module & Dashboard Implementation)
- **[MIGRATION]** Created `2026_06_26_155932_add_hcp_id_to_users_table.php` to add `health_care_professional_id` to the `users` table, linking authentication to professional profiles.
- **[MODELS]** Updated `User` model with `healthCareProfessional()` relationship and added `health_care_professional_id` to fillable fields and custom JWT claims.
- **[LIVEWIRE/BLADE]** Updated `UserCrud.php` and `user-crud.blade.php` to support assigning the "Doctor" role and linking a specific `health_care_professional_id` during user creation/editing.
- **[DASHBOARD]** Created `Doctors\DashboardCrud` Livewire component and view. The dashboard aggregates active `OpdConsultation`, `ErAdmission`, and `Admission` (Ward) records for the currently authenticated doctor using their linked `health_care_professional_id`.
- **[EMR CHART]** Created `Doctors\PatientChartCrud` component and view. This provides a unified EMR interface for doctors to document clinical notes (History of Present Illness, Physical Exam, Assessment, Treatment Notes) across OPD, ER, and Ward encounters.
- **[ROUTING/UI]** Added `Route::prefix('doctors')` in `routes/web.php` and integrated a conditional "Doctors Portal" sidebar menu item in `app.blade.php` (visible only to users with an assigned `health_care_professional_id`).
- **[DISPENSING STANDARDIZATION]** Refactored all clinical dispensing requests (`AdmissionCrud`, `DeliveryScheduleCrud`, `MicuPatientCrud`, `NursingCrud`, `ObConsultationCrud`, `DialysisSessionCrud`, `OpdConsultationCrud`, `ErAdmissionCrud`) to dynamically capture the authenticated user's `full_name` as the `requesting_person` and their linked `health_care_professional_id` as the `requesting_doctor_id` instead of using hardcoded strings (e.g., "Nursing Station", "OB Attending").

### 2026-06-26 (Laboratory & Radiology Results Integration)
- **[MIGRATION]** Created `2026_06_26_171611_add_result_fields_to_radiology_request_items_table` to add `result_text`, `doctor_notes`, `image_url`, `result_date`, and `radiologist_id` to `radiology_request_items` table.
- **[MODEL]** Updated `RadiologyRequestItem` model to cast `result_date` and added a `radiologist()` relationship to `HealthCareProfessional`.
- **[LABORATORY UI]** Updated `LabDispensingCrud` and `lab-dispensing-crud.blade.php`. Added a new **"Input Results"** modal that appears when an item is in the `Processing` state. Fields include numeric value, categorical status, textual reading, and clinical notes. Saving auto-updates the status to `Completed` and handles parent request cascading.
- **[RADIOLOGY UI]** Updated `RadiologyDispensingCrud` and `radiology-dispensing-crud.blade.php` (now utilizing `WithFileUploads`). Added an **"Input Results"** modal for `Approved`/`Partial` items. Allows input of official result text, doctor notes, radiologist selection, and an optional image/DICOM upload. Auto-updates status to `Complete`.
- **[EMR CHART VIEW]** Updated `PatientChartCrud` and `patient-chart-crud.blade.php`. The "Lab & Imaging Results" tab now actively queries and lists `Completed` lab results (as a clean data table) and `Complete` radiology results (as rich text cards with image links). Results are eager-loaded across OPD, ER, and Ward encounters.

### 2026-06-26 (EMR Phase 1: ICD-10 & CPOE Integration)
- **[MIGRATION/SEEDER]** Created `2026_06_26_172332_create_icd_10_codes_table` and `Icd10Seeder` to establish a centralized dictionary of diagnosis codes. Created `2026_06_26_172344_add_icd_10_code_to_encounter_tables` to add `icd_10_code` to `opd_consultations`, `er_admissions`, `admissions`, and `ob_consultations`.
- **[MODELS]** Created `Icd10Code` model to manage the dictionary.
- **[EMR CHART]** Upgraded `PatientChartCrud` and `patient-chart-crud.blade.php` for physician documentation.
  - **ICD-10 Auto-coding:** Implemented a smart autocomplete search box in the Assessment tab. Doctors can search for diseases or codes (e.g., "Asthma" or "J45") and select the official ICD-10 code, which persists to the encounter's `icd_10_code` field for PhilHealth CF4 compliance.
  - **CPOE (Computerized Physician Order Entry):** Added a "New Clinical Order" modal in the Active Orders tab. Features a 3-tab interface (Laboratory, Radiology, Pharmacy). Includes a dynamic search bar that queries `LabTest`, `RadiologyExamType`, or `Drug` models respectively. Allows adding items to a cart, defining priorities, and adding sig/dosages for medications.
  - **Backend Logic:** Implemented `submitOrder()` in `PatientChartCrud` which wraps order creation in a database transaction, automatically generating the `request_code` (e.g., `LR-00001`, `PR-00002`), attaching the authenticated doctor's ID, linking the specific encounter ID, and instantly routing the request to the respective departmental queue (Lab, Rad, CSR/Pharmacy).
- **[DOCS]** Updated `tables.md` to document the new `icd_10_codes` table (Table 105) and the newly added `icd_10_code` field across all clinical encounter tables.

### 2026-06-26 (Centralized Medical Records History for Physicians)
- **[MODELS]** Updated `Patient` model to add `hasMany` relationships for `opdConsultations`, `erAdmissions`, `admissions` (Ward), and `obConsultations`. This enables rapid querying of a patient's entire hospital history.
- **[EMR CHART BACKEND]** Updated `PatientChartCrud` to include a `getMedicalHistoryProperty()` method. This method queries all historical OPD, ER, Ward, and OB encounters for the patient, merges them into a single collection, filters out the current active encounter, and sorts them chronologically (descending) by timestamp.
- **[EMR CHART UI]** Updated `patient-chart-crud.blade.php` to include a new **"Past Records"** tab.
  - Implemented a responsive, vertical timeline UI to display the fetched medical history.
  - Each timeline card is color-coded by encounter type (e.g., ER is Red, Ward is Indigo, OPD is Green).
  - Cards display the Date, Encounter Type, Attending Physician, Primary Diagnosis (ICD-10 or text), a snippet of the Clinical Notes (HPI), and the Final Disposition.
- **[DOCS]** Updated `tables.md` to formally document the `Patient` model's relationships to the various encounter tables.

### 2026-06-26 (Patient Data Seeding & Bug Fixes)
- **[SEEDER]** Created `PatientSeeder` and added it to `DatabaseSeeder`. This seeder creates 10 distinct demo patients: 5 with active PhilHealth memberships (creating the linked `PhilhealthMember` records and syncing the `philhealth_pin`), and 5 without PhilHealth memberships. This establishes a reliable dataset for testing the eClaims and Billing workflows.
- **[BUGFIX]** Fixed a Livewire strict typing error in `PatientCrud.php`. When editing patients with `null` values for optional text fields (like `middle_name`, `suffix`, `contact_no`), a `TypeError` was thrown because the properties were strictly typed as `string`. Handled this by using null coalescing operators (`?? ''`) during data assignment in the `openEdit()` method.

### 2026-06-27 (PhilHealth eClaims CF5 Support)
- **[MIGRATION]** Created `2026_06_26_175332_create_philhealth_z_benefits_table.php` to track Claim Form 5 (CF5) data for PhilHealth Z-Benefits (catastrophic cases). This table stores pre-authorization codes, tranche numbers, and specific tranche amounts.
- **[MODELS]** Created `PhilhealthZBenefit` model and linked it to the central `PhilhealthClaim` model via a `zBenefits()` HasMany relationship. This formally completes the database schema required for the full PhilHealth CF1-CF5 standard suite.
- **[DOCS]** Updated `tables.md` to document the new `philhealth_z_benefits` table schema.
### 2026-06-28 (Patient Selection Optimization & Cross-Module Validation)
- **[UI/UX]** Transitioned patient selection interfaces from static <select> dropdowns to dynamic, server-side fuzzy search autocomplete inputs.
  - Replaced legacy static <select> tags in OpdConsultationCrud and LabDispensingCrud with <input wire:model.live.debounce.300ms="patient_search"> bound to an absolute dropdown list.
  - Verified that all other clinical modules (ObConsultationCrud, ErTriageRecordCrud, DeliveryScheduleCrud, OrScheduleCrud, DialysisSessionCrud, and HospitalTransferCrud) are correctly aligned with this optimized fuzzy search architecture.
  - Implemented the selectPatient() logic to properly bind the backend ID and toggle a user-friendly "clear selection" state.
- **[BACKEND]** Enhanced ChecksActiveEncounters trait to enforce strict encounter isolation. Upgraded the ensureNoActiveEncounter() method to intercept validation checks natively by throwing a ValidationException instead of just logging a soft error. This strictly prevents the system from saving a new encounter (e.g., ER or Admission) if the patient already has an active, undisposed encounter in another module.

---

### 2026-06-30 (RBAC Phase 4 — Route-Level & Component-Level Gating)
- **[RBAC]** Implemented Phase 4 — permission middleware on all routes + reusable ChecksPermissions trait:
  - **Route Middleware**: Added `->middleware('permission:module.action')` to every route/group in `routes/web.php`:
    - Dashboard: `dashboard.view`
    - Transactions (billing summary): `billing.view`
    - Reports: `reports.view`
    - Settings: `admin.settings`
    - Masters: `admin.tenants` (company), `admin.departments`, `admin.users`, `admin.masters` (all reference data + permissions)
    - Admin: `admin.masters` (role-permissions), `admin.users` (user permission overrides)
    - Transfers: `admission.view`
    - Patients: `patient.view`
    - Lab: `lab.manage` (settings), `lab.dispense`, `lab.result`
    - CSR/Supply: `csr.manage` (settings), `csr.dispense`
    - Pharmacy: `pharmacy.manage` (settings), `pharmacy.dispense`, `pharmacy.view` (PMR), `reports.view`
    - Procedures: `procedure.manage` (settings), `procedure.dispense`
    - Radiology: `radiology.manage` (settings), `radiology.dispense`, `radiology.result` (+ DICOM API)
    - Renal Care: `renal.view`
    - ICU/MICU: `admission.view`
    - Delivery: `delivery.view`
    - ER: `er.triage`, `er.manage`
    - Admission: `admission.view`
    - Nursing: `nursing.view`
    - Billing: `billing.view`, `billing.manage` (fund-sources)
    - OR: `or.view`
    - Ward: `admission.view`
    - YAKAP: `yakap.view` (dashboard), `yakap.manage` (data management)
    - OPD: `opd.view`
    - OB: `ob.view`
    - Doctors: `doctors.dashboard`, `doctors.chart`
    - PhilHealth: `philhealth.view` (reference data), `philhealth.manage` (claims/batches)
    - Medical Records: `medical-records.view`
  - **ChecksPermissions Trait**: Created `app/Livewire/Concerns/ChecksPermissions.php` with `checkPermission(string $permission)` method — checks `auth('api')->user()->hasPermission()` and aborts 403 if unauthorized
  - **TenantCrud**: Replaced `if (! $this->isSuperAdmin) { abort(403); }` with `$this->checkPermission('admin.tenants')` in `create()`, `save()` create-path, `confirmArchive()`, `archive()`
  - **UserCrud**: Added `$this->checkPermission('admin.users')` at top of `openCreate()`, `openEdit()`, `save()`, `confirmDelete()`, `delete()`
  - **Blade Directive**: Registered `@cannot('permission')` in AppServiceProvider as inverse of `@can`
  - **tenant-crud.blade.php**: Replaced `$isSuperAdmin` with `@can('admin.tenants')` / `@cannot('admin.tenants')` for Add button, Archive button, tenant_code disabled, is_active disabled
- **[DOCS]** Updated `ragdata.md` (this entry), `rbac-phase1.md` (Phase 4 checkboxes)

### Files Created
- `app/Livewire/Concerns/ChecksPermissions.php`

### Files Modified
- `routes/web.php` — permission middleware on all routes
- `app/Providers/AppServiceProvider.php` — registered `@cannot` Blade directive
- `app/Livewire/Masters/TenantCrud.php` — checkPermission('admin.tenants') for authorization
- `app/Livewire/Masters/UserCrud.php` — checkPermission('admin.users') for authorization
- `resources/views/livewire/masters/tenant-crud.blade.php` — @can/@cannot instead of $isSuperAdmin

---

### 2026-06-30 (RBAC Phase 3 — User-Level Permission Overrides)
- **[RBAC]** Implemented Phase 3 — user-level permission allow/deny overrides on top of role-based permissions:
  - **Migration**: `2026_06_30_000002_create_user_permissions_table.php` — `user_permissions` table with `user_id`, `permission_id`, `type` (allow/deny), `tenant_id`. Unique: `(user_id, permission_id, tenant_id)`. ✅ RUN.
  - **Model**: `App\Models\UserPermission` — `$timestamps = false`, BelongsTo user + permission
  - **User Model Enhanced**: `getPermissionsListAttribute()` now queries `UserPermission` after role permissions — `type=allow` entries are added, `type=deny` entries are filtered out. Results cached 1hr. Added `userPermissions()` HasMany.
  - **Livewire Component**: `App\Livewire\Admin\UserPermissionCrud` — mounts with `$userId`, loads user, shows all permissions grouped by module with Allow/Neutral/Deny toggle buttons. `save()` deletes old overrides in transaction, bulk inserts new ones, flushes user's permission cache.
  - **Blade View**: `resources/views/livewire/admin/user-permission-crud.blade.php` — Header with user name + role badge, amber info banner explaining overrides, 3-column responsive grid with colored toggle buttons (green/gray/red), "Save Overrides" button.
  - **Page Wrapper**: `resources/views/pages/admin/user-permissions.blade.php` — `<livewire:admin.user-permission-crud :userId="$userId" />`
  - **Route**: `GET /admin/users/{userId}/permissions` → `admin.users.permissions`
  - **User Grid Updated**: `user-crud.blade.php` — Added amber shield icon link in Actions column for permission overrides
- **[DOCS]** Updated `ragdata.md` (this entry), `tables.md` (user_permissions table), `rbac-phase1.md` (Phase 3 checkboxes)

### Files Created
- `database/migrations/2026_06_30_000002_create_user_permissions_table.php`
- `app/Models/UserPermission.php`
- `app/Livewire/Admin/UserPermissionCrud.php`
- `resources/views/livewire/admin/user-permission-crud.blade.php`
- `resources/views/pages/admin/user-permissions.blade.php`

### Files Modified
- `app/Models/User.php` — permissions_list accessor with overrides, userPermissions(), import
- `resources/views/livewire/masters/user-crud.blade.php` — permissions shield button
- `routes/web.php` — added `/admin/users/{userId}/permissions` route

---

### 2026-06-30 (RBAC Phase 2 — Permission Management UI)
- **[RBAC]** Implemented Phase 2 — admin interface for managing permissions and role-permission assignments:
  - **PermissionCRUD**: `App\Livewire\Masters\PermissionCrud` — full CRUD with search (name/display_name/module/description), sort by any column, pagination (10/25/50), create/edit modal with name (alpha_dash unique), display_name, module dropdown, description, is_active toggle, archive (soft-delete) confirmation modal. Light-blue themed datagrid with columns: Permission Key (monospace), Display Name, Module (badge), Description, Status (Active/Inactive badges), Actions (Edit + Archive).
  - **PermissionCrud Blade**: `resources/views/livewire/masters/permission-crud.blade.php` — modal forms following department-crud pattern, full form validation
  - **Page Wrapper**: `resources/views/pages/masters/permissions.blade.php` — `<livewire:masters.permission-crud />`
  - **Route**: `GET /permissions` inside masters group → `masters.permissions`
  - **Role-Permission Assignment**: `App\Livewire\Admin\RolePermissionCrud` — role selector dropdown (superadmin/tenant_admin/staff/doctor), permission checkboxes grouped by module, `loadPermissions()` queries role_permissions, `save()` deletes existing + bulk inserts new mappings, flushes permission cache for ALL users with that role
  - **RolePermissionCrud Blade**: `resources/views/livewire/admin/role-permission-crud.blade.php` — 3-column responsive checkbox grid by module, each shows checkbox + display_name + name (monospace), Save Permissions button with loading state
  - **Role Permissions Page Wrapper**: `resources/views/pages/admin/role-permissions.blade.php` — `<livewire:admin.role-permission-crud />`
  - **Route**: `GET /admin/role-permissions` → `admin.role-permissions`
  - **Sidebar**: Added "Permissions" link under Masters dropdown, "Role Permissions" under Administration section
- **[DOCS]** Updated `ragdata.md` (this entry), `tables.md`, `rbac-phase1.md` (Phase 2 checkboxes)

### Files Created
- `resources/views/livewire/masters/permission-crud.blade.php`
- `resources/views/pages/masters/permissions.blade.php`
- `app/Livewire/Admin/RolePermissionCrud.php`
- `resources/views/livewire/admin/role-permission-crud.blade.php`
- `resources/views/pages/admin/role-permissions.blade.php`

### Files Modified
- `routes/web.php` — added masters.permissions + admin.role-permissions routes
- `resources/views/components/layouts/app.blade.php` — sidebar links

---

### 2026-06-30 (RBAC Phase 1 — Permissions & Role-Based Access Control)
- **[RBAC]** Implemented Phase 1 of RBAC — lightweight database-backed permission system built on existing `role` column:
  - **Migration**: `2026_06_30_000001_create_permissions_tables.php` — `permissions` (65 rows) + `role_permissions` (211 mappings) tables
  - **Model**: `App\Models\Permission` — simple model with `$timestamps = false`
  - **Seeder**: `database/seeders/PermissionSeeder.php` — seeds 65 permissions across 23 modules with 211 role→permission assignments for all 4 roles
  - **Middleware**: `App\Http\Middleware\CheckPermission` — route middleware `permission:name`, returns 403 for unauthorized users
  - **Blade Directives**: `@can('name')` and `@role('name')` registered via `Blade::if()` in `AppServiceProvider`
  - **JWT Claims**: User model `getJWTCustomClaims()` now includes `permissions` array; `config/jwt.php` `persistent_claims` includes all custom claims for token refresh survival
  - **User Model**: Added `isDoctor()`, `hasPermission()`, `permissions_list` accessor (cached 1hr for non-superadmin), `flushPermissionCache()`
  - **Sidebar Gating**: Doctors Portal gated with `@can('doctors.dashboard')`, Administration section gated with `@can('admin.masters')`
- **[FIX]** Lab/Radiology auto-billing bug — removed 4 occurrences of premature `billing_status = 'Billed'` from `LabDispensingCrud` (x2), `LabResultsCrud` (x1), `RadiologyDispensingCrud` (x1), `RadiologyResultsCrud` (x1). Items no longer skip the "For Billing" tab.
- **[DOCS]** Created `rbac-phase1.md` with full 9-phase RBAC roadmap
- **[DOCS]** Updated `ragdata.md` (this entry), `tables.md` (permissions + role_permissions tables)

### Files Created
- `database/migrations/2026_06_30_000001_create_permissions_tables.php`
- `app/Models/Permission.php`
- `database/seeders/PermissionSeeder.php`
- `app/Http/Middleware/CheckPermission.php`
- `rbac-phase1.md`

### Files Modified
- `app/Models/User.php` — `isDoctor()`, `hasPermission()`, `permissions_list`, JWT claims
- `app/Providers/AppServiceProvider.php` — Blade `@can`/`@role` directives
- `bootstrap/app.php` — `permission` middleware alias
- `config/jwt.php` — `persistent_claims`
- `database/seeders/DatabaseSeeder.php` — PermissionSeeder first
- `resources/views/components/layouts/app.blade.php` — sidebar gating
- `app/Livewire/Lab/LabDispensingCrud.php` — removed auto-billing (x2)
- `app/Livewire/Lab/LabResultsCrud.php` — removed auto-billing
- `app/Livewire/Radiology/RadiologyDispensingCrud.php` — removed auto-billing
- `app/Livewire/Radiology/RadiologyResultsCrud.php` — removed auto-billing

### 2026-07-01
- **[BUG FIX]** Fixed `Call to undefined method App\Models\Item::batches()` 500 error on `/admission`. `AdmissionCrud.php:778` was referencing `\App\Models\Item` (wrong model, no `batches()` relationship). Changed to `\App\Models\Supply\Item` which has the proper `batches()` hasMany defined at `Supply/Item.php:22`.
- **[BUG FIX]** Fixed subsequent `Call to undefined relationship [itemCategory]` on `\App\Models\Supply\Item`. The eager load `with('itemCategory')` used the wrong relationship name — renamed to `with('category')` matching the actual method `category()` at `Supply/Item.php:18`.
- **[UI]** Lightened Doctors Portal header gradient from `from-slate-900 to-indigo-900` to `from-indigo-800 to-indigo-600` across `dashboard-crud.blade.php`, `patient-chart-crud.blade.php` (patient card overlay), and the past encounter detail hero header.
- **[UI]** Upgraded sidebar icons with section-matching colors in `app.blade.php`: OPD→indigo, ER→rose, Admission & Wards→teal, OB→pink, OR→slate, Renal Care→cyan, Laboratory→amber, Radiology→purple, Procedures→sky, CSR/Supply→orange, PhilHealth→blue, Masters→slate. Each section now has a distinct hover/active color on both its parent icon and sub-menu links, replacing the monochrome light-blue default.

### 2026-07-01 (Second Session — Verification & Testing)
- **[TEST]** Ran PHPUnit (34 tests): 27 passed, 7 pre-existing failures (6 PhilHealth `icd_10_codes.description` NOT NULL constraint + 1 ExampleTest 302 redirect). No regressions from changes.
- **[TEST]** PHP syntax check: All 4 modified PHP files pass `php -l` — `ItemBatchCrud.php`, `ItemStockAdjustmentCrud.php`, `LabDispensingCrud.php`, `BillingCrud.php`.
- **[CLEANUP]** Cleared compiled Blade view cache — stale `storage/framework/views/` had cached old billing dropdown buttons from before removal.
- **[VERIFIED]** All 5 dispensing blades (Lab, Radiology, CSR, Pharmacy, Procedures): no billing status column in datatable, no billing dropdown in Actions column. Billing status still visible in read-only detail modal (intentional).
- **[VERIFIED]** `BillingCrud::payPatientCharges()` method chain verified: `payPatientCharges()` → `openCreateInvoice()` → modal opens with all patient's items pre-selected.
- **[VERIFIED]** `ItemBatchCrud` uses `code`/`name` (not `item_code`/`item_name`) in item search queries and selects. `ItemStockAdjustmentCrud` similarly fixed.
- **[VERIFIED]** `LabDispensingCrud::updateBillingStatus()` present at line 348 — matches pattern of all 4 other dispensing CRUDs.
- **[VERIFIED]** All routes registered: `/billing` (4 routes), `/supply/inventory` + `/supply/dispensing` + 6 more supply routes.

### 2026-07-01 (Third Session — Radiology Auto-Billing on Approval)
- **[FEATURE]** Radiology auto-billing: when a radiology item is approved (`updateItemStatus(itemId, 'Approved')`), the parent request's `billing_status` is automatically set to `Billed` (only if currently `Pending`). This ensures the charge is captured at approval time even if detailed result entry (technique, findings, impression) happens later. Applied to:
  - `RadiologyDispensingCrud::updateItemStatus()` — auto-bills on `Approved` status
  - `RadiologyDispensingCrud::completeAllItems()` — auto-bills when bulk-completing all items

### 2026-07-01 (Procedures Dispensing & Billing — Missing `patient()` Relationship Fix)
- **[BUGFIX]** Fixed `Call to undefined relationship [patient] on model [App\Models\Procedures\ProcedureRequest]` — 500 error on `/procedures/dispensing` and `/billing` due to missing `patient()` relationship. `ProcedureRequest` model has no `patient_id` column; patient name is resolved through polymorphic encounter relations.
  - `ProcedureDispensingCrud.php:338` and `:371` — Removed `'patient'` from both datagrid and detail modal `with()` arrays; added `dialysisSession.patient`, `orSchedule.patient`, `drSchedule.patient`, `obConsultation.patient`
  - `BillingCrud.php:343` — Removed `'patient'` from the `getUnbilledRequestsProperty()` ProcedureRequest query
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-01 (Fourth Session — Billing Page `wire:navigate` JS SyntaxError Fix)

#### Problem Chain
A three-stage bug existed in `resources/views/livewire/billing/billing-crud.blade.php` causing two distinct errors when navigating to `/billing` via `wire:navigate`:

1. **`Uncaught SyntaxError: Failed to execute 'replaceWith' on 'Element': Invalid or unexpected token`** — Livewire's `swapCurrentPageWithNewHtml()` crashed on navigation.
2. **Visible JS code leaking into the DOM** — The text `'; var w=window.open('','_blank'); if(w){w.document.write(html);w.document.close()} }` appeared as rendered page content below the billing table.

#### Root Cause Analysis

The `billing-crud.blade.php` file had a bare `<script>` block containing the `printCharges()` helper function. Inside this block, a JS string contained the CSS at-rule `@page{margin:20mm 15mm}`.

- **Stage 1 — Blade `@page` directive clash**: Blade's template engine scanned the `<script>` body and interpreted `@page` as a Blade directive, outputting nothing/garbage in its place. This truncated the JS string mid-literal, causing the remainder of the function to leak into the page as visible text. This also produced a malformed HTML payload that broke Livewire's `element.replaceWith(htmlString)` call during `wire:navigate`.
- **Stage 2 — `@verbatim` applied, but `&#8369;` HTML entity in JS**: Applied `@verbatim`/`@endverbatim` to stop Blade processing. However, the previous fix had introduced `&#8369;` (peso sign HTML entity) inside the `<script>` block. HTML entities are decoded in HTML context but **not** inside `<script>` tags — the browser JS engine saw the literal `&` character as an invalid token → `SyntaxError: Invalid or unexpected token (at billing:844:13)`.
- **Stage 3 — Root issue: wrong directive for Livewire 4**: Neither `@verbatim` nor character escaping addressed the fundamental incompatibility. Livewire 4's `wire:navigate` uses `element.replaceWith(htmlString)` to swap pages. When the HTML string contains a `<script>` block, the HTML5 parser terminates the script context at `</script>`, corrupting the surrounding HTML and triggering the `replaceWith` SyntaxError. Livewire 4 ships `@script`/`@endscript` specifically to solve this — it extracts the script from the component's HTML before DOM morphing and re-executes it separately after navigation.

#### Fix Applied
- **`resources/views/livewire/billing/billing-crud.blade.php`**:
  - Replaced `@verbatim <script>...</script> @endverbatim` with `@script <script>...</script> @endscript` — Livewire 4's proper `wire:navigate`-safe script directive.
  - Moved `@page` CSS at-rule into a separate JS variable `var pageRule='@@page{margin:20mm 15mm}'` — Blade `@@` escape outputs a literal `@` so Blade does not interpret it as a directive.
  - Changed `function printCharges(){}` to `window.printCharges = function(){};` — assigning to `window` ensures the function is accessible from `onclick="printCharges()"` in Livewire-managed DOM after re-render.
  - Replaced all `&#8369;` (HTML entity, invalid in JS) and `₱` (literal character, problematic inside `@verbatim`) with `\u20B1` (JS Unicode escape — safe in any JS string context).
  - Ran `php artisan view:clear` and `php artisan cache:clear` after each fix iteration.

#### Key Rule Learned
> **In Livewire 4 components, NEVER use bare `<script>` tags.** Always use `@script`/`@endscript`. This ensures scripts are extracted from the Livewire component HTML payload before `wire:navigate` does DOM morphing, preventing `replaceWith` parse failures. Additionally, any CSS at-rules (`@media`, `@page`, `@keyframes`) embedded inside JS strings must use the Blade `@@` escape (e.g. `@@page`) to prevent Blade directive misinterpretation.

### Files Modified
- `resources/views/livewire/billing/billing-crud.blade.php` — `printCharges()` script block: `@verbatim` → `@script`, `@page` → `@@page` in JS variable, `window.printCharges` assignment, `\u20B1` Unicode escape

### 2026-07-02 (Treatment Button Bug — Falsy `treatingId=0` Fix Across All Modules)
- **[BUGFIX]** Root cause: All treatment forms (Dialysis, ER, Delivery, OB, OR) used truthy checks (`if ($this->showTreatmentForm && $this->treatingId)`) on a nullable integer property. A DB record with `id=0` made `treatingId=0`, which is **falsy** in PHP — causing the treatment orders block to be silently skipped. The form appeared but orders never loaded.
- **[FIX]** Changed all truthy/falsy checks to explicit null comparisons (`!== null` / `=== null`):
  - `DialysisSessionCrud.php:838` — `$this->treatingId` → `$this->treatingId !== null`
  - `DialysisSessionCrud.php:328` — `!$this->treatingId` → `$this->treatingId === null`
  - `ErAdmissionCrud.php:348,1037` — same fixes
  - `DeliveryScheduleCrud.php:409,429,980` — same fixes
  - `ObConsultationCrud.php:429` — same fix
  - `OrScheduleCrud.php:342,362,913` — same fixes
- **[FIX]** Added null-safe operators on order queries in `DialysisSessionCrud.php:848-852` (`$activeSession?->labRequests()...`) to prevent crash if `find()` returns null
- **[FIX]** Also added per-order-modal close methods (`closeOrderLab`, `closeOrderCsr`, etc.) to Dialysis blade — prevents order modal backdrop/X from destroying the entire treatment form
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-02 (Konsulta Module — PhilHealth Primary Care Benefit Package)
- **[FEATURE]** Built the complete Konsulta (Konsultasyong Sulit at Tama) module for PhilHealth's primary care benefit package (YAKAP — KONSULTA component).
- **Migration**: `2026_07_02_120000_create_konsulta_tables.php` — 4 tables:
  - `konsulta_enrollments` — Patient enrollment in Konsulta package with enrollment_code (KON-XXXXX), patient_id, philhealth_member_id, primary_care_provider_id, enrollment_date, expiry_date, status, risk_classification, fpe_completed tracking.
  - `konsulta_consultations` — Primary care consultations with consultation_code (KNC-XXXXX), enrollment_id, patient_id, provider_id, consultation_date, chief_complaint, diagnosis, icd10_code_id, SOAP fields (assessment, management_plan), vitals (bp, temperature, heart_rate, respiratory_rate, weight, height), is_fpe flag.
  - `konsulta_prescriptions` — GAMOT medicine prescriptions tied to consultations with medicine_name, dosage, frequency, duration, quantity, is_gamot_covered, status.
  - `konsulta_lab_referrals` — LABORATORYO diagnostic test referrals with test_type, referral_date, performed_date, result, status, notes.
- **Models** (4): `App\Models\Konsulta\KonsultaEnrollment`, `KonsultaConsultation`, `KonsultaPrescription`, `KonsultaLabReferral` — all with `$timestamps = false`, `$guarded = []`, booted global scope `history = 1`, proper BelongsTo relationships.
- **Livewire Component**: `App\Livewire\Konsulta\KonsultaCrud` — Single integrated tabbed CRUD (Enrollments / Consultations / Prescriptions / Lab Referrals) with:
  - Patient search autocomplete for both enrollment and consultation forms
  - Enrollment CRUD with risk classification, status management, FPE tracking
  - Consultation CRUD with vitals, ICD-10 code selector, SOAP fields, FPE checkbox
  - Prescription inline creation with GAMOT coverage flag
  - Lab referral inline creation with status tracking
  - Consultation detail view modal with drill-down prescriptions and lab referrals
  - Stats cards (total_enrolled, fpe_completed, total_consults, total_rx, gamot_rx, pending_labs)
  - Archive soft-delete via history=0
- **Blade View**: `resources/views/livewire/konsulta/konsulta-crud.blade.php` — Teal/indigo/amber/sky theme, 6 stats cards, tab navigation, 5 modals, sortable data tables with status badges.
- **Route**: `GET /konsulta` under `konsulta.` prefix, gated with `permission:konsulta.view`
- **Sidebar**: Added "Konsulta" link under Financial & Claims section (after YAKAP), teal theme, stethoscope SVG icon, gated with `@can('konsulta.view')`
- **Permission**: Created `konsulta.view` permission (ID: 66), assigned to tenant_admin role, cache flushed.
- **Bugfixes applied**:
  - Patient search used `hospital_no` column (doesn't exist) → changed to `patient_code`
  - `selectPatient()` queried `PhilhealthMember::where('patient_id', ...)` (no such column) → reads `philhealth_member_id` directly from Patient record
  - Consultation modal had no patient search → added `consultPatientSearch` property, `selectConsultPatient()` method, patient search field with autocomplete dropdown
  - ICD-10 codes dropdown was empty → registered `Icd10Seeder` in `DatabaseSeeder.php`, seeded 25 codes
- **[BUGFIX]** `enrollment_id` was NOT NULL in all 3 konsulta child tables, but standalone consultations (created from Consultations tab, not from enrollment context) have no enrollment. Created migration `2026_07_02_120001` to make `enrollment_id` nullable in `konsulta_consultations`, `konsulta_prescriptions`, and `konsulta_lab_referrals`.
- **[FILE CREATED]** `database/migrations/2026_07_02_120001_make_enrollment_id_nullable_in_konsulta_tables.php`
- **[FILES CREATED]** `database/migrations/2026_07_02_120000_create_konsulta_tables.php`, `app/Models/Konsulta/KonsultaEnrollment.php`, `app/Models/Konsulta/KonsultaConsultation.php`, `app/Models/Konsulta/KonsultaPrescription.php`, `app/Models/Konsulta/KonsultaLabReferral.php`, `app/Livewire/Konsulta/KonsultaCrud.php`, `resources/views/livewire/konsulta/konsulta-crud.blade.php`, `resources/views/pages/konsulta/konsulta.blade.php`
- **[FILES MODIFIED]** `routes/web.php`, `resources/views/components/layouts/app.blade.php`, `database/seeders/DatabaseSeeder.php`, `resources/views/livewire/masters/permission-crud.blade.php` (module field changed from select to input with datalist for custom module entry)
- **[DOCS]** Updated `ragdata.md` (this entry), `tables.md` (4 new konsulta table definitions).

---

### 2026-07-15 — Delivery Room Treatment Form: Added 3-Tab Clinical Layout

- **[BUGFIX]** Delivery Room blade (`delivery-schedule-crud.blade.php`) was missing the Clinical Notes tab and tab navigation bar. The `x-data="{ activeTab: 'clinical' }"` was declared but no tab buttons were rendered, and the Clinical Notes section (HPI, Physical Exam, Assessment, Treatment Notes textareas) was entirely absent. Orders sections were always visible without tab switching.
- **[FILES MODIFIED]** `resources/views/livewire/delivery/delivery-schedule-crud.blade.php`:
  - Added 3-tab navigation bar (Clinical Notes / Orders & Interventions / Disposition) with rose theming, matching the OR Room pattern
  - Added Clinical Notes section with 4 textareas: History of Present Illness, Physical Examination Findings, Assessment & Plan, Treatment Notes — wrapped in `x-show="activeTab === 'clinical'"`
  - Added Orders Summary textarea at the top of the Orders tab — wrapped all 5 order sections (Lab, CSR, Radiology, Pharmacy, Procedure) in `x-show="activeTab === 'orders'"`
  - Verified Disposition section already had correct `x-show="activeTab === 'disposition'"` attribute
  - Added `<form wire:submit="saveTreatment">` wrapper around clinical and orders tabs
- **No DB migration needed** — `dr_schedules` table already had all clinical columns (hpi, physical_exam, assessment, orders_summary, treatment_notes, pre_op_notes, intra_op_notes, post_op_notes, disposition, discharge_instructions)
- **No PHP component changes needed** — `DeliveryScheduleCrud.php` already had `openTreatment()`, `saveTreatment()`, and all clinical properties
- **Reference**: OR Room blade (`or-schedule-crud.blade.php` lines 52-117) used as exact template, with emerald→rose color substitution

---

### 2026-07-15 — PhilHealth eClaims 3.0 XML Generator & API Client

- **[FEATURE]** Implemented PhilHealth eClaims 3.0 XML generator and multi-part REST API client per `philhealth-spec.md` blueprint. Rewritten from spec placeholders to use actual `philhealth_claims` schema with Eloquent models and relationships.
- **[FILES CREATED]**:
  - `app/Services/PhilHealthXmlService.php` — Generates eClaims 3.0 compliant eSOA XML payloads. Uses `PhilhealthClaim` Eloquent model with eager loading. Maps 15 XML blocks: HCI_CODE (from `tenants.philhealth_hci_code`), CLAIM_CODE, PATIENT (from `patients.*`), PHILHEALTH_MEMBER (from `philhealth_members.*`), ENCOUNTER (date_admitted/time_admitted/outcome/disposition_code), DIAGNOSIS (icd10Code/secondary/tertiary), CLINICAL (chief_complaint/HPI/physical_exam/course_in_ward), VITAL_SIGNS, AMOUNTS (total_claimed_amount/total_actual_amount), CF5_BILLING (from `cf5_shadow_billings.*`), PHYSICIAN, TRACKING (ecs_tracking_no/acknowledgment_code), OB (lmp_date/edc_date/gravity/para/delivery). Enforces multi-tenant isolation by resolving `tenant.philhealth_hci_code` from claim's `tenant_id`.
  - `app/Services/PhilHealthApiService.php` — Streams XML payloads to PhilHealth sandbox via `multipart/form-data` HTTP POST with Bearer token auth. Reads config from `config('services.philhealth.*')`.
  - `app/Http/Controllers\PhilHealthTestController.php` — Invokable controller for end-to-end sandbox testing. Auto-resolves HCI code from claim's tenant. Accepts `?claim_id=N` query param or uses first available claim. Returns JSON with XML preview snippet.
- **[FILES MODIFIED]**:
  - `app\Models\PhilhealthClaim.php` — Added `tenant()` BelongsTo relationship (FK: `tenant_id` → `tenants`).
  - `config/services.php` — Added `philhealth` config block (env, test_url, username, password, dev_token).
  - `routes/web.php` — Added `GET /philhealth/test-pipeline` route under existing PhilHealth route group.
- **[ENV VARS REQUIRED]**: `PHILHEALTH_ENV`, `PHILHEALTH_TEST_URL`, `PHILHEALTH_USERNAME`, `PHILHEALTH_PASSWORD`, `PHILHEALTH_DEV_TOKEN` — add to `.env` file.
- **Verification checkpoints**: (1) No scanned PDFs — uploadFile passes raw XML string via `->attach()`, (2) Multi-tenant guard — generateEsoaXml resolves `tenant.philhealth_hci_code` from claim's `tenant_id` and throws if mismatched, (3) Data type preservation — TOTAL_CLAIMED_AMOUNT/TOTAL_ACTUAL_AMOUNT/DRG amounts all formatted to 2-4 decimal places via `number_format()`.

### Field Mapping (spec placeholder → actual schema):
| Spec Placeholder | Actual Column/Relationship |
|---|---|
| `hci_code` | `tenants.philhealth_hci_code` (via `$claim->tenant`) |
| `encounter_number` | `philhealth_claims.claim_code` |
| `patient_first_name` | `patients.first_name` (via `$claim->patient`) |
| `patient_last_name` | `patients.last_name` (via `$claim->patient`) |
| `egov_qr_status` | `philhealth_claims.ecs_tracking_no` |
| `total_amount` | `philhealth_claims.total_claimed_amount` |
| `drg_code` | `cf5_shadow_billings.drg_code` (via `$claim->cf5ShadowBilling`) |

---

### 2026-07-15 — Billing Refactor: Patient Account Ledger System

- **[FEATURE]** Replaced invoice-centric billing with a patient account ledger system. Charges now post directly to a `patient_account_entries` ledger table, giving a real-time patient balance without requiring invoice creation first. Invoices become optional statements/snapshots.
- **[ARCHITECTURE CHANGE]**:
  - **Before**: Clinical charges → `billing_status='Pending'` → Staff manually groups into Invoice → Invoice Items → Payments on Invoice
  - **After**: Clinical charges → `patient_account_entries` ledger (auto-accumulate) → Payments applied to patient account → Statements/Invoices generated on-demand
- **[FILES CREATED]**:
  - `database/migrations/2026_07_15_000001_create_patient_account_entries_table.php` — Ledger table with `entry_type` (charge/payment/adjustment/discount/philhealth_credit), `source_type` (Lab/Pharmacy/CSR/Radiology/Procedure/OECB/Admission/Manual/Payment), `debit`/`credit`/`balance` columns, encounter scope FKs, optional `billing_invoice_id` link.
  - `database/migrations/2026_07_15_000002_add_patient_encounter_fields_to_billing_payments_table.php` — Adds `patient_id`, `admission_id`, `er_admission_id`, `opd_consultation_id` to `billing_payments` for patient-level payment tracking.
  - `app/Models/Billing/PatientAccountEntry.php` — Eloquent model with static helpers: `postCharge()`, `postPayment()`, `postAdjustment()`, `recalculateBalance()`, `getPatientBalance()`, `getLedger()`, `getTotalCharges()`, `getTotalPayments()`. Scopes: `charges()`, `payments()`, `forPatient()`, `unbilled()`.
- **[FILES MODIFIED]**:
  - `app/Livewire/Billing/BillingCrud.php` — Added `getPatientAccountsProperty()` (reads from ledger), `viewPatientLedger()`, `postChargesToLedger()`. Updated `createInvoice()` to post charges to ledger alongside creating invoice items. Updated `addPayment()` to post payment to ledger. Updated `confirmDeleteInvoice()` to reverse ledger entries. Tab structure: 5 tabs (Patient Accounts / For Billing / Invoices / Payments / Reports).
  - `app/Models/Billing/BillingPayment.php` — Added `patient()`, `admission()`, `erAdmission()` BelongsTo relationships.
  - `resources/views/livewire/billing/billing-crud.blade.php` — Added Patient Accounts tab (Tab 1) with ledger table showing patient_name, total_charges, total_payments, balance, entry_count, last_activity, and action buttons (Ledger/Pay/Charge). Added Patient Ledger modal with full debit/credit/balance history table. Updated tab indices (For Billing→Tab 2, Invoices→Tab 3, Payments→Tab 4, Reports→Tab 5).
- **[BACKWARD COMPATIBILITY]**: Existing invoice flow preserved. `billing_invoices`, `billing_invoice_items`, `billing_manual_charges` tables unchanged. New ledger entries are created alongside invoice creation. `BillingPayment` records still created for backward compat.
- **[NO BREAKING CHANGES]**: All existing functionality works. The ledger is additive — new charges post to both the ledger and the existing invoice flow.

### 2026-07-15 — Auto-Post Charges to Patient Account Ledger (All Dispensing Modules)

- **[ENHANCEMENT]** Applied auto-posting of charges to the patient account ledger (`patient_account_entries`) across ALL 5 dispensing modules + OECB, achieving full parity with the Radiology pattern. Previously only Radiology auto-posted; CSR was completely missing ledger posting.
- **[CSR — BIGGEST GAP FIXED]** `app/Livewire/Supply/CsrDispensingCrud.php`:
  - `updateItemStatus()` — Added `CsrRequest::postChargeToLedger()` when parent status becomes `'Complete'`. Added auto `billing_status='Billed'` when completing.
  - `completeAllItems()` — Added `CsrRequest::postChargeToLedger()` + auto `billing_status='Billed'`.
  - `processDispense()` — Added `CsrRequest::postChargeToLedger()` + auto `billing_status='Billed'`.
- **[PHARMACY]** `app/Livewire/Pharmacy/PharmacyDispensingCrud.php`:
  - `updateItemStatus()` — Added auto `billing_status='Billed'` when parent status becomes `'Complete'` (ledger posting already existed).
  - `completeAllItems()` — Added auto `billing_status='Billed'`.
  - `processDispense()` — Added auto `billing_status='Billed'`.
- **[LAB]** `app/Livewire/Lab/LabDispensingCrud.php`:
  - `updateItemStatus()` — Added auto `billing_status='Billed'` when parent status becomes `'Completed'` (note: Lab uses `'Completed'` not `'Complete'`).
  - `completeAllItems()` — Added auto `billing_status='Billed'`.
  - `saveResult()` — Added auto `billing_status='Billed'`.
- **[PROCEDURE]** `app/Livewire/Procedures/ProcedureDispensingCrud.php`:
  - `updateItemStatus()` — Added auto `billing_status='Billed'` when parent status becomes `'Complete'` (ledger posting already existed).
  - `completeAllItems()` — Added auto `billing_status='Billed'`.
- **[RADIOLOGY]** — Already had full parity. No changes needed.
- **[OECB]** `app/Models/Oecb/OecbEntry.php`:
  - Added `postChargeToLedger()` static method (resolves patient via `erAdmission->patient_id`, posts charge with `source_type='OECB'`).
  - `app/Livewire/Er/ErAdmissionCrud.php` `saveDisposition()` — When ER admission status becomes `'Completed'`, auto-sets `billing_status='Billed'` on all pending OECB entries and calls `OecbEntry::postChargeToLedger()` for each.
- **[POSTING PATTERN]** All modules now follow the same two-tier pattern:
  - **TIER 1 (Real-Time)**: `postChargeToLedger()` → `PatientAccountEntry::postCharge()` at item/request completion time
  - **TIER 2 (Batch)**: `billing:auto-invoice` CLI creates `BillingInvoice` + `BillingInvoiceItem` records from unbilled ledger entries
- **[BILLING STATUS]** All 5 dispensing modules now auto-set `billing_status = 'Billed'` on completion (previously only Radiology did this). This prevents double-posting from the batch CLI and gives real-time billing visibility.
- **[FILES MODIFIED]**:
  - `app/Livewire/Supply/CsrDispensingCrud.php` — 3 methods updated
  - `app/Livewire/Pharmacy/PharmacyDispensingCrud.php` — 3 methods updated
  - `app/Livewire/Lab/LabDispensingCrud.php` — 3 methods updated
  - `app/Livewire/Procedures/ProcedureDispensingCrud.php` — 2 methods updated
  - `app/Models/Oecb/OecbEntry.php` — Added `postChargeToLedger()`
  - `app/Livewire/Er/ErAdmissionCrud.php` — Added OECB auto-post in `saveDisposition()`
- **[DOCS]** Updated `ragdata.md` (this entry).

### 2026-07-15 — Billing UI Cleanup: Hidden Tabs + Statement of Account PDF

- **[UI]** Hidden the "For Billing" (Tab 2) and "Invoices" (Tab 3) tabs from the Billing page. These tabs are now commented out in the blade — the ledger-based workflow replaces them. Remaining visible tabs: Patient Accounts (Tab 1) / Payments (Tab 4) / Reports (Tab 5).
  - **File modified**: `resources/views/livewire/billing/billing-crud.blade.php` — Tab 2 and Tab 3 button HTML wrapped in `{{-- --}}` blade comment.
- **[FEATURE]** Added **Statement of Account** PDF generation and in-app viewer to the Patient Account Ledger modal.
  - **Print Summary** button — generates a PDF with charges grouped by source type (Pharmacy, Lab, CSR, Radiology, Procedure, OECB, etc.) with subtotals per group.
  - **Print Detailed** button — generates a PDF with the full ledger transaction history (Date, Type, Source, Reference, Description, Debit, Credit, Running Balance).
  - PDF is generated server-side using `barryvdh/laravel-dompdf` (letter/portrait), base64-encoded, and displayed in an embedded iframe modal within the app.
  - Modal includes **Print** button (triggers `iframe.contentWindow.print()`) and **Download PDF** button (data-URI download link).
- **[FILES MODIFIED]**:
  - `app/Livewire/Billing/BillingCrud.php` — Added `$showStatementModal`, `$statementPdfBase64`, `$statementMode` properties. Rewrote `printStatement(string $mode)` to generate PDF via dompdf and set modal state. Added `closeStatementModal()`.
  - `resources/views/livewire/billing/billing-crud.blade.php` — Added print buttons (Print Summary / Print Detailed) to ledger modal footer. Added PDF viewer modal (z-[60]) with iframe embed, print/download buttons, and close handler. Removed old `@script` JS listener for new-tab approach.
- **[FILE CREATED]**:
  - `resources/views/livewire/billing/statement-of-account.blade.php` — dompdf-compatible HTML template using table-based layouts (no flexbox/grid). Includes hospital header from tenant, patient info bar, summary cards (charges/payments/balance), and two render modes (summary grouped by source type, detailed full ledger). Footer with authorized signature line.
- **[DOCS]** Updated `ragdata.md` (this entry).