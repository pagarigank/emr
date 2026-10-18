# SITHEMR E2E Test Plan — Phase 2

> **Objective**: Validate end-to-end clinical workflows across all modules, focusing on cross-module data integrity, order/billing pipeline, RBAC gating, and UI/UX consistency. Phase 1 (covered in `e2e-test-plan.md`) validated ER Triage → ER Area → Orders → Billing. Phase 2 extends coverage to OPD, OB, Delivery, Admission, Nursing, OR, Renal Care, MICU, Procedures, Pharmacy, Lab Results, Radiology Results, Doctor's Portal, Medical Records, Hospital Transfers, YAKAP, PhilHealth Claims, Fund Sources, and RBAC.

---

## Table of Contents
1. [Authentication & RBAC](#1-authentication--rbac)
2. [OPD Clinical Encounters](#2-opd-clinical-encounters)
3. [OB/GYN Consultations](#3-obgyn-consultations)
4. [Delivery Room (DR) Module](#4-delivery-room-dr-module)
5. [Admission Module](#5-admission-module)
6. [Nursing Module](#6-nursing-module)
7. [OR Module](#7-or-module)
8. [Renal Care / Dialysis](#8-renal-care--dialysis)
9. [MICU Module](#9-micu-module)
10. [Lab Results & Validation](#10-lab-results--validation)
11. [Radiology Results & DICOM](#11-radiology-results--dicom)
12. [Pharmacy Inventory & Drug Formulary](#12-pharmacy-inventory--drug-formulary)
13. [CSR Inventory & Batch Tracking](#13-csr-inventory--batch-tracking)
14. [Procedures Dispensing](#14-procedures-dispensing)
15. [Billing — Patient-Centric View, Manual Charges, Fund Sources, Auto-Invoice](#15-billing--patient-centric-view-manual-charges-fund-sources-auto-invoice)
16. [Doctor's Portal](#16-doctors-portal)
17. [Medical Records Module](#17-medical-records-module)
18. [Hospital Transfers](#18-hospital-transfers)
19. [YAKAP Module](#19-yakap-module)
20. [PhilHealth E-Claims (CF1–CF5, ECS, Cloud, Voucher)](#20-philhealth-e-claims-cf1cf5-ecs-cloud-voucher)
21. [Cross-Encounter Validation](#21-cross-encounter-validation)
22. [Sidebar Gating & RBAC Permissions](#22-sidebar-gating--rbac-permissions)
23. [Data Isolation / Multi-Tenant Scoping](#23-data-isolation--multi-tenant-scoping)
24. [Print & Certificate Workflows](#24-print--certificate-workflows)
25. [Coverage Summary](#25-coverage-summary)

---

## 🔐 Test Account Credentials

Use the following accounts when executing test cases throughout this plan.

| Role | Email | Password |
|------|-------|----------|
| Superadmin | `superadmin@sithemr.com` | `Admin@1234` |
| Tenant Admin | `admin@demohospital.com` | `Admin@1234` |
| Tenant Admin (alt) | `tenant_admin@example.com` | `Admin@1234` |
| Doctor | `doctor@example.com` | `Admin@1234` |

> **Note**: All accounts use the same password `Admin@1234`. The superadmin account has unrestricted access across all tenants. Tenant Admin accounts are scoped to their respective hospital tenant.

---

## 1. Authentication & RBAC

### 1.1 Login Scenarios
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 1.1.1 | Superadmin login | Navigate to `/login`, enter `superadmin@sithemr.com` / `Admin@1234` | Redirect to dashboard. JWT token issued. Sidebar shows all modules. |
| 1.1.2 | Tenant admin login | Login as `admin@demohospital.com` / `Admin@1234` | Redirect to dashboard. Sidebar scoped to tenant modules. |
| 1.1.3 | Invalid credentials | Enter wrong email/password | Error message "Invalid credentials". No redirect. |
| 1.1.4 | Inactive user login | Login as a user with `is_active = 0` | Error message or redirect to login with "Account disabled". |
| 1.1.5 | Permission cache flush on login | Change permissions of a user, logout, login again | New permissions reflected immediately (not stale from cache). |

### 1.2 Role-Based Access
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 1.2.1 | Superadmin sees all sidebar links | Login as superadmin | All 25+ sidebar items visible — Clinical, Ancillary, Financial, Admin. |
| 1.2.2 | Tenant admin sees permitted modules | Login as tenant_admin with specific permissions | Only gated modules visible. Un-permitted links hidden. |
| 1.2.3 | Staff restricted access | Login as staff user with limited permissions | Only assigned permissions reflected in sidebar and pages. |
| 1.2.4 | Doctor restricted access | Login as doctor user | Only doctor-relevant modules visible (Patients, Doctor's Portal, Orders view). |
| 1.2.5 | Direct URL access without permission | Navigate to `/billing` as a user without `billing.view` | `403 Forbidden` or redirect. |

### 1.3 RBAC Role Management UI
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 1.3.1 | List roles | Navigate to `/admin/roles` | Datagrid shows 4 system roles: superadmin, tenant_admin, staff, doctor. System roles have warning badge. |
| 1.3.2 | Edit role permissions | Click Edit on staff role → Permissions tab → toggle some permissions → Save | Permissions updated. Cache flushed for affected users. |
| 1.3.3 | System role protection | Try to delete or rename superadmin role | Delete button hidden/disabled. Name change blocked with warning. |
| 1.3.4 | Archive role | Click Archive on a non-system role | Role hidden from dropdowns (`history = 0`). Users with that role retain it. |
| 1.3.5 | Permission audit log | Change role permissions → navigate to `/admin/audit-log` | Audit entry logged with before/after values, timestamp, and user who made the change. |

---

## 2. OPD Clinical Encounters

### 2.1 OPD Consultation — Create & SOAP Notes
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 2.1.1 | Navigate to OPD | Click OPD > Consultations in sidebar | Datagrid loads with existing consultations. Search, sort, pagination functional. |
| 2.1.2 | Create new OPD consultation | Click "New Consultation" → search & select patient → select clinic, visit type, consultation type → fill chief complaint → Save | OPD-xxxxx code generated. Status = "Pending". Consultation appears in datagrid. |
| 2.1.3 | Nursing check-in vitals | From OPD datagrid, click the patient → Vitals tab → enter BP, HR, Temp, SpO2, Weight, Height → Save | Vitals saved. Status updates to "In Progress". |
| 2.1.4 | Doctor SOAP notes | Click "Consultation" tab → enter HPI, Physical Exam, Assessment, Treatment Plan, ICD-10 code, Disposition → Save | SOAP notes saved. Status = "Completed" if disposition set. |
| 2.1.5 | Order Lab from OPD | In Consultation view → Orders → Lab → select CBC, Urinalysis → priority Routine → Save | LR-xxxxx created. Links to `opd_consultation_id`. Appears in Lab Dispensing. |
| 2.1.6 | Order Pharmacy from OPD | Order Paracetamol 500mg (TID/PO) → Save | PR-xxxxx created. Links to `opd_consultation_id`. CF4 fields (frequency, route) captured. Appears in Pharmacy Dispensing. |
| 2.1.7 | Order CSR from OPD | Order Gauze 5cm x 2 pcs → Save | CR-xxxxx created. Links to `opd_consultation_id`. Appears in CSR Dispensing. |
| 2.1.8 | Order Radiology from OPD | Order Chest X-Ray PA → Save | RR-xxxxx created. Links to `opd_consultation_id`. Appears in Radiology Dispensing. |
| 2.1.9 | Order Procedure from OPD | Order ECG → Save | PRCD-xxxxx created. Links to `opd_consultation_id`. Appears in Procedures Dispensing. |
| 2.1.10 | Verify orders in dispensing modules | Navigate to Lab Dispensing, Pharmacy Dispensing, etc. | All orders from OPD visible with correct source/reference. |

### 2.2 OPD — Cross-Encounter Validation
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 2.2.1 | Active encounter conflict | Create OPD consultation for patient → without disposing, try to create another OPD for same patient | Validation error: "Patient has an active OPD encounter. Please complete it first." |
| 2.2.2 | Disposition clears conflict | Set disposition on first OPD → try creating second OPD for same patient | Second OPD created successfully. |

---

## 3. OB/GYN Consultations

### 3.1 OB Consultation — Create & Treatment Workflow
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 3.1.1 | Navigate to OB | Click OB > Consultations | Datagrid loads. Active/Completed toggle works. |
| 3.1.2 | Create OB consultation | Click "New" → search patient → select visit type (Prenatal) → fill OB fields (G:2 P:1 FT:1 PT:0 A:0 LC:1, LMP date, EDC auto-calc) → Save | OBC-xxxxx code generated. Rose/pink theme visible. |
| 3.1.3 | Clinical Notes tab | Enter HPI, PE, Assessment, Treatment Notes → Save | Notes persisted. |
| 3.1.4 | Orders tab — all 5 types | Place Lab, CSR, Radiology, Pharmacy, Procedure orders | All orders created with correct FKs. CF4 frequency/route captured for pharmacy. |
| 3.1.5 | OR Schedule tab | Click OR Schedule → Select OR room, surgeon, anesthesiologist, procedure, date/time → Save | ORS-xxxxx created. Links to `ob_consultation_id`. Appears in OR Schedule module. |
| 3.1.6 | Disposition | Set disposition to "Delivered" | Patient acknowledged in OB view. Workflow completes. |

---

## 4. Delivery Room (DR) Module

### 4.1 DR Rooms Management
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 4.1.1 | List DR rooms | Navigate to Delivery > Rooms | Datagrid shows DR rooms with Available/Occupied statuses. |
| 4.1.2 | Create DR room | Click New → enter code DR-03, name "Delivery Suite 3", type "LDR", capacity 1, equipment "Fetal Monitor, Delivery Bed" → Save | Room created. Status = "Available". |

### 4.2 DR Schedule — Full Workflow
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 4.2.1 | Create delivery schedule | Navigate to Delivery > Schedule → New → search patient → select DR Room, attending OB, midwife → fill OB history → Save | DRS-xxxxx created. Status = "Scheduled". |
| 4.2.2 | Treatment — Clinical Notes | Click Treatment → enter HPI, PE, Assessment, Treatment Notes → Save | Notes saved. |
| 4.2.3 | Orders & Interventions | Place Lab, CSR, Radiology, Pharmacy, Procedure orders | Orders created with `dr_schedule_id` FK. Appear in respective dispensing modules. |
| 4.2.4 | Neonatal Outcome tab | Enter baby sex (Male), weight (3.2 kg), length (50 cm), head circumference (34 cm), Apgar 1min: 8, Apgar 5min: 9, newborn outcome (Live Birth) → Save | Neonatal data saved. |
| 4.2.5 | Disposition | Set disposition to "Delivered" → fill discharge instructions → Save | Status = "Completed". |
| 4.2.6 | Verify DR orders in billing | Navigate to Billing → For Billing tab | DR-sourced orders appear unbilled. |

---

## 5. Admission Module

### 5.1 Admit from ER
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 5.1.1 | For Admission tab | Navigate to Admission | Tab shows ER encounters where disposition = "Admitted to Ward/ICU". |
| 5.1.2 | Admit patient | Click "Admit" on a completed ER encounter → select Ward/Room, attending/admitting physician → Save | ADM-xxxxx generated. Ward room status changes to "Occupied". Admission appears in "Active Admissions". |
| 5.1.3 | Verify bed occupancy | Navigate to Wards > Rooms | The admitted room shows "Occupied" status. |

### 5.2 Admission — Treatment Workflow
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 5.2.1 | Treatment form | Click "Treatment" on an active admission → 3-tab form opens (Clinical Notes, Orders, Disposition) | Indigo-themed summary card visible with patient info. |
| 5.2.2 | Clinical Notes | Enter HPI, PE, Assessment, Orders Summary, Treatment Notes → Save | Notes persisted. |
| 5.2.3 | Orders from Admission | Place Lab (CBC), Pharmacy (Ceftriaxone), CSR (IV Set) orders | Orders created with `admission_id` FK. Dispensing modules show them. |
| 5.2.4 | Order status sync | Go to Lab Dispensing → update Lab order status to Processing | Back in Admission Treatment view, order badge updates in real-time (via `refreshAdmissionOrders`). |
| 5.2.5 | Discharge patient | Set disposition → enter discharge instructions → Confirm | Status = "Discharged". Ward room set to "Available". Discharge date stamped. |
| 5.2.6 | Verify billing pickup | Navigate to Billing → For Billing tab | Admission orders appear unbilled. |

---

## 6. Nursing Module

### 6.1 Nursing — Patient List
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 6.1.1 | Navigate to Nursing | Click Nursing in sidebar | List of admitted patients. Search, sort, discharged toggle functional. Summary cards show patient counts. |
| 6.1.2 | Click patient to open detail | Click on a patient row | Patient header card with gradient. 4-tab layout: Vitals, Care Notes, Orders, Discharge. |

### 6.2 Nursing — Vitals
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 6.2.1 | Record vitals | Tab 1 → New → enter BP 120/80, HR 78, Temp 36.5, RR 16, SpO2 98, Pain 2 → Save | Vital record created. Displayed in history table. Abnormal values highlighted. |
| 6.2.2 | Edit vital | Click edit on a vital record → change BP → Save | Vital updated. |
| 6.2.3 | Delete vital | Click delete on a vital record | Vital archived (`history = 0`). |

### 6.3 Nursing — Care Notes
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 6.3.1 | Record care note | Tab 2 → New → select shift (AM) → enter SOAP-format note → Save | Care note saved with shift tag. |
| 6.3.2 | Edit/delete care note | Edit to change note text → Save. Delete another note. | Edit updates. Delete archives. |

### 6.4 Nursing — Orders View
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 6.4.1 | View all orders | Tab 3 → Orders | Read-only view of all 5 department orders (Lab, Pharmacy, CSR, Radiology, Procedure) linked via ER admission relationship. Status badges visible. |

### 6.5 Nursing — Discharge
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 6.5.1 | Discharge from Nursing | Tab 4 → set discharge date → enter summary notes → Confirm | Patient discharged. Ward room status reset to "Available". |

### 6.6 Nursing — Room Transfer
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 6.6.1 | Transfer Room button visible | Open Nursing detail view for an admitted patient | "Transfer Room" button visible in patient header card next to room/ward display. |
| 6.6.2 | Open transfer modal | Click "Transfer Room" | Modal opens with: current room/bed read-only display, target type selector (Ward Room / ICU Bed), available rooms/beds dropdown, reason textarea, Confirm Transfer button. |
| 6.6.3 | Transfer to ward room | Select "Ward Room" → choose available room from dropdown → enter reason → Confirm | Old room set to Available. New room set to Occupied. Admission `ward_room_id` updated, `icu_bed_id` cleared. `admission_room_history` record created with old history closed and new history started. Toast: "Patient transferred successfully." |
| 6.6.4 | Transfer to ICU bed | Select "ICU Bed" → choose available bed → Confirm | Old room freed. ICU bed set to Occupied. Admission `icu_bed_id` updated, `ward_room_id` cleared. Room history recorded. |
| 6.6.5 | Cancel transfer | Click Cancel button or X on modal | Modal closes. No changes made. Form reset. |

### 6.7 Nursing — MAR (Medication Administration Record)
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 6.7.1 | View MAR | Tab 5 (if present) → Medication Administration Record | Shows administered medications from linked pharmacy orders. |

---

## 7. OR Module

### 7.1 OR Rooms
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 7.1.1 | List OR rooms | Navigate to OR > Rooms | Datagrid with OR rooms, status badges. |
| 7.1.2 | Create/Edit OR room | New → enter code OR-3, type "General", equipment "C-Arm, Lap Tower" → Save | Room created. Equipment and requirements stored. |

### 7.2 OR Schedule — Create & Treatment
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 7.2.1 | Create OR schedule | Navigate to OR > Schedule → New → search patient → select OR room, surgeon, anesthesiologist, procedure type, date/time → Save | ORS-xxxxx created. Status = "Scheduled". |
| 7.2.2 | Treatment form | Click "Treatment" → 3-tab form (Clinical Notes, Orders, Disposition) | Emerald-themed patient summary card. |
| 7.2.3 | Orders from OR | Place Lab (Crossmatch), Pharmacy (Anesthesia drugs), CSR (Surgical gloves), Procedure orders | All orders created with `or_schedule_id`. Appear in dispensing modules. |
| 7.2.4 | OR Schedule status sync | Approve/Complete items in dispensing → Back in OR Treatment view | Status badges update via `refreshOrOrders`. |
| 7.2.5 | Disposition | Set disposition → instructions → Save | Status = "Completed" or "Transferred". |

### 7.3 OR Schedule from ER/Nursing/OB
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 7.3.1 | OR schedule from ER Area | ER Treatment → OR Schedule tab → schedule procedure | ORS-xxxxx created with `er_admission_id`. Visible in OR Schedule module. |
| 7.3.2 | OR schedule from Nursing | Nursing detail → OR Schedule tab → schedule for admitted patient | ORS-xxxxx created with `admission_id`. Visible in OR Schedule module. |
| 7.3.3 | OR schedule from OB | OB Consultation → OR Schedule tab → schedule | ORS-xxxxx created with `ob_consultation_id`. |

---

## 8. Renal Care / Dialysis

### 8.1 Dialysis Session — Create & Treatment
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 8.1.1 | Create dialysis session | Navigate to Renal Care > Dialysis Sessions → New → search patient → select dialysis type, access type, pre-weight/BP → auto-coded DS-xxxxx → session date defaults to today → Save | Session created. |
| 8.1.2 | Treatment form | Click "Treatment" → 3-tab form (Clinical Notes, Orders, Disposition) | Emerald-themed. |
| 8.1.3 | Orders from Dialysis | Place Lab, CSR, Pharmacy, Radiology, Procedure orders | Orders created with `dialysis_session_id`. Appear in dispensing modules. |
| 8.1.4 | Order status sync | Update order status in dispensing → Back in Dialysis treatment | Badges update via `refreshDsOrders`. |
| 8.1.5 | Disposition | Set disposition → instructions → Save | Session complete. |
| 8.1.6 | Verify billing pickup | Billing → For Billing tab | Dialysis orders visible for billing. |

---

## 9. MICU Module

### 9.1 MICU Patient List
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 9.1.1 | Navigate to MICU | Click MICU > Patients | Datagrid shows patients admitted to MICU beds only (filtered by `icu_wards.code = 'MICU'`). |

### 9.2 MICU Treatment Workflow
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 9.2.1 | Open treatment | Click "Treatment" → 4-tab form: Clinical Notes, Orders, Assessments, Disposition | Rose/pink theme. |
| 9.2.2 | Clinical Notes | Enter HPI, PE, Assessment, Orders Summary, Treatment Notes → Save | Notes persisted. |
| 9.2.3 | Orders (all 5 types) | Place Lab, CSR, Radiology, Pharmacy, Procedure orders | Orders created with `admission_id`. |
| 9.2.4 | MICU Assessment | Assessments tab → New → select parameters (HR, BP, RR, O2 Sat, GCS, Temp) → enter values → Save | `micu_assessment` + `micu_assessment_values` created. |
| 9.2.5 | View assessment history | Assessments tab | Past assessments listed with parameters expandable. |
| 9.2.6 | Disposition | Set disposition → discharge instructions → Save | Status = "Completed/Transferred/Discharged". |

---

## 10. Lab Results & Validation

### 10.1 Lab Results Entry
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 10.1.1 | Navigate to Lab Results | Click Lab > Results | Datagrid with filter tabs (All/Pending/Completed). |
| 10.1.2 | Enter numeric result | Click on a lab request item → enter result value (e.g., 5.2 for WBC) → Save | Flag auto-computed (H/L/N) against reference range. Reference range snapshot stored. |
| 10.1.3 | Enter text result | Enter result_text for a non-numeric test → Save | Text result stored. |
| 10.1.4 | Enter categorical result | Select Positive/Negative/Reactive → Save | Categorical result stored. Flag = "Normal" or "Abnormal". |
| 10.1.5 | Pathologist validation | Click "Validate" on completed result → confirm | `validated_by` + `validated_at` stamped. Status updates. |

### 10.2 Lab Printout
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 10.2.1 | Print lab result | Click Print on a validated result → opens new tab | DOH-compliant print layout with 3-signature block (performed/verified/validated by). |

### 10.3 Lab Results in Doctor's Portal
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 10.3.1 | View lab results in patient chart | Doctor's Portal → Patient Chart → Lab Results tab | Results displayed with flag badges, reference range column, abnormal row highlighting. |

---

## 11. Radiology Results & DICOM

### 11.1 Radiology Results Entry
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 11.1.1 | Navigate to Radiology Results | Click Radiology > Results | Datagrid with All/Pending/Completed filter tabs. |
| 11.1.2 | Enter structured report | Click on a radiology item → enter Technique, Findings, Impression, Full Report, Remarks → Save | Structured report saved. |
| 11.1.3 | Multi-image upload | Upload 2-3 images (JPEG/PNG/DICOM) → mark one as primary | Images stored with thumbnails. Primary flag set. |

### 11.2 DICOM API
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 11.2.1 | Query worklist | `GET /api/dicom/studies` | Returns JSON list of studies. |
| 11.2.2 | Serve DICOM image | `GET /api/dicom/images/{id}` | Returns image file with correct MIME type. |
| 11.2.3 | Store DICOM image | `POST /api/dicom/store` with DICOM file | Image stored, `radiology_result_images` record created with DICOM UIDs. |

### 11.3 Radiology Printout
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 11.3.1 | Print radiology result | Click Print → new tab | DOH-compliant layout with technique/findings/impression, image attachments, 3-signature block. |

---

## 12. Pharmacy Inventory & Drug Formulary

### 12.1 Drug Master
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 12.1.1 | List drugs | Navigate to Pharmacy > Drugs | Datagrid with search, sort, pagination. Columns: Code, Generic Name, Brand Name, Strength, Status. |
| 12.1.2 | Create drug | New → enter drug details (generic name, brand name, strength, dosage form, therapeutic class, route) → Save | Drug created. `philhealth_item_code` optional. |

### 12.2 Drug Batches
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 12.2.1 | Add drug batch | Navigate to Pharmacy > Batches → New → select drug → batch no, expiry date, quantity, unit cost, selling price → Save | Batch created. `getDrugNameAttribute()` displays generic (brand). |
| 12.2.2 | Expired batch visibility | Create batch with past expiry date | Filtered out by `scopeAvailable()` (FEFO). Not selectable in dispensing. |

### 12.3 Pharmacy Purchase Orders
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 12.3.1 | Create purchase order | Pharmacy > Purchase Orders → New → select supplier → add line items (drug + qty + unit cost) → Save as Draft | PO created with Draft status. |
| 12.3.2 | Submit PO | Click Submit → status changes to Submitted | Status = "Submitted". |
| 12.3.3 | Approve PO | Click Approve | Status = "Approved". |
| 12.3.4 | Receive PO (GRN) | Click Receive → enter batch no, expiry date, qty received for each item → Save | Status = "Received". DrugBatch auto-created. StockAdjustment (type = Addition) created. |
| 12.3.5 | Partial receive | Receive only 50% of ordered qty | Status = "Partial". Remaining qty shows as pending. |

### 12.4 Stock Adjustments
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 12.4.1 | Write-off adjustment | Pharmacy > Stock Adjustments → New → search drug → select batch → type = Deduction → qty → reason "Damaged" → Save | `drug_batch.quantity_on_hand` reduced. StockAdjustment record created. |

### 12.5 Pharmacy Inventory Dashboard
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 12.5.1 | View inventory KPIs | Pharmacy > Inventory | 6 summary cards: Total Stock, Batch Count, Near Expiry, Expired, Avg Cost, Stock Value. Per-drug totals with inline batch expansion. |

### 12.6 Pharmacy Reports
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 12.6.1 | Expiry Report | Pharmacy > Reports → Expiry tab | Drugs grouped by expiry category (Expired/Within 30 days/Within 90 days/OK). |
| 12.6.2 | Stock Status Report | Stock Status tab | Items flagged as Low/Over/Adequate stock. |
| 12.6.3 | Consumption Report | Consumption tab → set date range | Most-consumed drugs in period. |

### 12.7 Patient Medication Record
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 12.7.1 | View patient medication history | Pharmacy > Patient Med Record → search patient | All pharmacy requests for that patient listed (via ER/OPD/Admission/OB/OR/DR links). |

---

## 13. CSR Inventory & Batch Tracking

### 13.1 Item Batches
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 13.1.1 | Create item batch | Supply > Item Batches → New → select item → batch no, expiry date, qty, unit cost, selling price → Save | `item_batches` record created. |
| 13.1.2 | Stock adjustment | Supply > Stock Adjustments → New → select item batch → type (Addition/Deduction/Expiry/Damage) → qty → reason → Save | `item_batch.quantity_on_hand` updated. `item_stock_adjustment` record created. |
| 13.1.3 | Verify clinical module filtering | ER/OPD/Admission/OB/DR Supply Orders modal | Only items with `quantity_on_hand > 0` and unexpired appear. |

---

## 14. Procedures Dispensing

### 14.1 Create & Manage Procedure Requests
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 14.1.1 | Navigate to Procedures Dispensing | Click Procedures > Dispensing | Datagrid with request code, department, procedures count, total amount, status, billing. |
| 14.1.2 | Create procedure request | New → select department, doctor → select procedure(s) from pill buttons grouped by category → edit quantity/charge → Save | PRCD-xxxxx code generated. Items created with category context. |
| 14.1.3 | Item status management | Detail modal → change item status (Pending → Approve → Complete) | Item status cascades to parent request. |
| 14.1.4 | Complete All | Click "Complete All" in Actions column | All items set to Complete. Confirmation dialog shown. |

---

## 15. Billing — Patient-Centric View, Manual Charges, Fund Sources, Auto-Invoice

### 15.1 Patient-Centric "For Billing" Tab
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 15.1.1 | For Billing tab loads | Navigate to Billing → Tab 1 | Patient-centric view: grouped by patient name. Each row shows avatar, name, patient ID, item count badge, total amount. |
| 15.1.2 | View patient charges | Click "View Charges" on a patient row | Modal opens showing all line items: source type badge, request code, department, amount, date. Total footer displayed. |
| 15.1.3 | Pay from patient view | Click "Pay" on a patient row → auto-invoices all unbilled items → payment modal opens | Invoice created. Payment modal pre-populated with patient and items. |
| 15.1.4 | Search filters by patient name | Type patient name in search bar | Results filtered by patient name (not request code). |

### 15.2 Invoice Creation & Management
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 15.2.1 | Create invoice with selected items | Select checkboxes on unbilled items → click "Generate Invoice" → modal shows selected items with correct totals → confirm | INV-xxxxx created. Source requests marked Billed with `billing_id`. |
| 15.2.2 | Patient selection persists | Close & reopen invoice modal → previously selected patient still set | Patient field not lost (regression for Bug #9b). |
| 15.2.3 | Invoice detail modal | Click Invoice # in Invoices tab → detail modal opens | Shows line items with source badges, payments, manual charges, totals. |

### 15.3 Payment Recording
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 15.3.1 | Add payment | Invoices tab → detail modal → Add Payment → select method (Cash) → enter amount → Save | PAY-xxxxx created. Invoice balance recalculated. Status updates (Unpaid → Partial → Paid). |
| 15.3.2 | Payment receipt | Payments tab → click printer icon on a payment row | Opens receipt page in new tab with print dialog. |
| 15.3.3 | Invoice cancellation | Click Cancel on invoice → confirm | Invoice status = "Cancelled". Source request `billing_status` reset to Pending. `billing_id` cleared. Payments reversed. |

### 15.4 Manual Charges
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 15.4.1 | Add manual charge | Invoice detail modal → Manual Charges → Add → select type (Miscellaneous), description, qty 1, unit price 500 → Save | Manual charge added. Invoice total recalculated. Balance updated. |
| 15.4.2 | Remove manual charge | Click Remove on a manual charge row | Charge archived. Invoice totals recalculated. |
| 15.4.3 | Manual charge on Paid invoice | Add manual charge to a Paid invoice | Invoice status flips back to Paid → Partial/Unpaid as appropriate. |

### 15.5 Fund Sources
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 15.5.1 | Create fund source | Billing > Fund Sources → New → enter code, name, budget amount → Save | Fund source created with budget. |
| 15.5.2 | Use fund source in payment | Invoice detail → Add Payment → select fund source → Save | Fund source `used_amount` increased by payment amount. |
| 15.5.3 | Budget deduction on payment | Pay with a fund source → verify used/budget | `used_amount` deducted from `budget_amount`. Remaining displayed. |
| 15.5.4 | Budget restoration on cancel | Cancel invoice → payment reversed | Fund source `used_amount` decremented. Budget restored. |

### 15.6 Auto-Invoice Command
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 15.6.1 | Run auto-invoice | `php artisan billing:auto-invoice` | Completed/pending-billable items from all 5 modules collected. Grouped by patient. Invoices created. Records marked Billed. |
| 15.6.2 | Scheduled run | Verify `routes/console.php` | Command registered at `23:59` daily with `withoutOverlapping()`. |
| 15.6.3 | Auto-billed items hidden | Verify manual billing buttons hidden on completed dispensary items | Completed lab/pharmacy/etc. requests don't show billing dropdowns. |

### 15.7 Room & Board Billing (Admission Room History + Noon Cutoff)
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 15.7.1 | Room history recorded on admission | Admit patient → assign ward room | `admission_room_histories` record created with `assigned_at`, room name, ward name, room rate snapshot. |
| 15.7.2 | Room history on transfer | Nursing → Transfer patient to a different room | Previous room history closed (`unassigned_at` set, `is_active = false`). New room history record created. |
| 15.7.3 | Room history on discharge | Discharge the patient | Active room history closed with `unassigned_at` = discharge timestamp. |
| 15.7.4 | Room charge appears in For Billing | Patient discharged → navigate to Billing → For Billing tab | Admission room charge(s) appear as unbilled item(s). If transfers occurred, each room stay shows as a separate line item with distinct room name, days, and rate. |
| 15.7.5 | Noon cutoff — discharge before 12PM | Admit patient Jan 1 10AM → Discharge Jan 3 9AM (before noon) | Charge = 2 days (Jan 1 + Jan 2; Jan 3 before noon not charged). |
| 15.7.6 | Noon cutoff — discharge after 12PM | Admit patient Jan 1 10AM → Discharge Jan 3 2PM (after noon) | Charge = 3 days (Jan 1 + Jan 2 + Jan 3). |
| 15.7.7 | Same-day stay | Admit and discharge on same calendar day | Charge = 1 day. |
| 15.7.8 | Room info displayed in View Charges | For Billing → View Charges on a patient with room charges | Admission items show room name, ward, and days × rate breakdown below the request code. |
| 15.7.9 | Invoice detail shows room breakdown | Create invoice from admission charges → view invoice detail in Invoices tab | Admission line items show `{qty} day(s) @ ₱{rate}/day` in teal text. |

---

## 16. Doctor's Portal

### 16.1 Dashboard
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 16.1.1 | Navigate to Doctor's Portal | Login as doctor → Click Dashboard | Premium UI with deep indigo gradient header, glassmorphism search bar, pill-style segmented controls (OPD/ER/Ward tabs). |
| 16.1.2 | Search patients | Type in search bar | Fuzzy search by name/code. Results appear in dropdown. |
| 16.1.3 | OPD tab | Click OPD tab | Shows active OPD consultations assigned to the logged-in physician. |
| 16.1.4 | ER tab | Click ER tab | Shows active ER admissions for the physician. |
| 16.1.5 | Ward tab | Click Ward tab | Shows active ward admissions for the physician. |

### 16.2 Patient Chart
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 16.2.1 | Open patient chart | Click on a patient from dashboard | Premium summary header with floating data pills. Tabs: SOAP Notes, Orders, Lab Results, Radiology, Medical History. |
| 16.2.2 | SOAP Notes | Enter S-O-A-P fields with icon badges and animated gradient dividers → Save | SOAP notes saved. |
| 16.2.3 | Orders | Place Lab, Pharmacy orders | Orders created via Doctor's context. |
| 16.2.4 | Lab Results tab | View lab results for patient | Flag badges, reference ranges, abnormal row highlighting. |
| 16.2.5 | Radiology Results tab | View radiology results | Structured sections (technique/findings/impression/report), image grid with hover zoom. |
| 16.2.6 | Medical History tab | View past encounters | Timeline view with color-coded cards (OPD/ER/Ward/OB). Clicking loads full encounter detail. |

### 16.3 Physician Authorization
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 16.3.1 | Unauthorized chart access | Doctor A tries to view a patient assigned to Doctor B | 404 or "Unauthorized" — record not loaded. |

---

## 17. Medical Records Module

### 17.1 Patient Search & Encounter History
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 17.1.1 | Navigate to Medical Records | Click Medical Records in sidebar | Patient search with fuzzy autocomplete. |
| 17.1.2 | Search and select patient | Type patient name → select from results | Patient summary card with demographics and encounter stats. Color-coded timeline filter. |
| 17.1.3 | Filter by encounter type | Click OPD/ER/Admission/OB filter pills | Timeline filtered to selected type. |
| 17.1.4 | View encounter detail | Click an encounter card | Detail modal with clinical fields, orders (Lab/Pharmacy/Radiology), billing invoice links. |

### 17.2 Certificates
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 17.2.1 | Medical Certificate | Encounter detail → Certificates dropdown → Medical Certificate → new tab opens | Print-optimized view with hospital letterhead, patient info, attending physician, diagnosis, signature lines. |
| 17.2.2 | Certificate of Admission | Same dropdown → Certificate of Admission | Admission-specific certificate with correct data. |
| 17.2.3 | Discharge Summary | Select Discharge Summary | Discharge details pre-filled. |
| 17.2.4 | Certificate of Treatment | Select Certificate of Treatment | Treatment details pre-filled. |
| 17.2.5 | Clinical Abstract | Select Clinical Abstract | Full clinical summary with all encounter data. |
| 17.2.6 | Print/Close buttons | In certificate print view | Print and Close buttons visible on screen, hidden when printing. |

---

## 18. Hospital Transfers

### 18.1 Hospital Master
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 18.1.1 | Create hospital | Masters > Hospital → New → enter code, name, address, contact, type (Tertiary) → Save | Hospital created. Active in dropdowns. |

### 18.2 Hospital Transfer Workflow
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 18.2.1 | Create transfer | Click Transfers → New Transfer → search patient → select destination hospital, source encounter type, doctor, transport mode (Ambulance) → enter diagnosis → Save | HT-xxxxx code generated. Status = "Pending". |
| 18.2.2 | View transfer in datagrid | Navigate to Transfers main list | Transfer visible with Pending status. View/Edit/Archive actions present. |
| 18.2.3 | Update transfer status | Click Edit → change status to "In Transit" → Save | Status updates. Trackable. |
| 18.2.4 | Complete transfer | Change status to "Completed" → stamp completion datetime | Completion datetime set. |

---

## 19. YAKAP Module

### 19.1 Empanelment
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 19.1.1 | Create empanelment | YAKAP > Empanelments → New → search patient → select year, PCP, clinic, risk classification → Save | YAKAP-xxxxx code generated. Status = "Active". |

### 19.2 FPE (First Patient Encounter)
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 19.2.1 | Record FPE | From empanelment → FPE → enter height, weight, BMI, waist circumference, smoking/alcohol/diet/activity → Family history, past medical hx, immunization, OB history → Save | FPE record created. `fpe_completed` flag set on empanelment. |

### 19.3 Screenings
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 19.3.1 | Record screening | YAKAP > Screenings → New → select empanelment → screening type (Breast), result (Normal), facility, performed by → Save | Screening created. `next_screening_due` calculated. |
| 19.3.2 | View screening history | Empanelment detail → Screenings tab | All 6 screening types shown with status. |

### 19.4 Capitation Payments
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 19.4.1 | Record capitation payment (Tranche 1) | YAKAP > Capitation → New → select empanelment → tranche 1 (40%) → amount → payment code → Save | Payment recorded. Unique constraint prevents duplicate tranche. |
| 19.4.2 | Record Tranche 2 | Same empanelment → tranche 2 (60%) | Second payment recorded. |

### 19.5 YAKAP in OPD
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 19.5.1 | YAKAP flag in OPD consultation | Create OPD consultation → toggle `is_yakap` → select empanelment | OPD flagged as YAKAP. Empanelment linked. `risk_classification` synced. |

---

## 20. PhilHealth E-Claims (CF1–CF5, ECS, Cloud, Voucher)

### 20.1 Claim Creation & CF4 Data Capture
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 20.1.1 | Create claim manually | PhilHealth > E-Claims → New → Section A: select patient, admit/discharge dates, ICD-10, case rate → Save | PHC-xxxxx created. Status = "Draft". |
| 20.1.2 | Section B — Clinical Data | Enter HPI, Past Medical Hx, PE Findings, Course in the Ward | CF4 Parts III-IV data stored. |
| 20.1.3 | Section C — Claim Items | Add line items: drug, lab, radiology, procedure, supply, room & board → auto-calculated totals | Items listed with RVS codes, quantities, unit costs, claimed amounts. Total computed. |
| 20.1.4 | Section D — Outcome | Set outcome (Improved), status (For Submission) → Save | Status = "For Submission". |

### 20.2 Auto-Claim from Patient Registration
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 20.2.1 | Register PhilHealth member | Patient Registration → toggle PhilHealth Member → enter PIN → Save | Draft claim auto-created via `PhilhealthClaim::ensureDraftForPatient()`. Appears in E-Claims list. |

### 20.3 CF1–CF5 PDF Preview & Download
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 20.3.1 | CF1 Preview | Claim detail modal → CF1 button | CF1 form preview in modal. Download PDF button opens PDF in new tab. |
| 20.3.2 | CF2 Preview | CF2 button | CF2 form with HCI data, ICD-10, case rate, items table. |
| 20.3.3 | CF3 Preview | CF3 button | CF3 form with clinical abstract, vitals grid, course in the ward. |
| 20.3.4 | CF4 Preview | CF4 button | CF4 comprehensive form with all parts I-VI. |
| 20.3.5 | CF5 Preview | CF5 button (for DRG claims) | CF5 with DRG code, weight, HF/PF split, break-even analysis. |

### 20.4 OECB Claim
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 20.4.1 | Create OECB claim | PhilHealth > E-Claims → New → Claim Type = OECB → select ER admission with OECB data | OECB package, fee pre-populated. Cf4 auto-formatted chief complaint. |

### 20.5 ECS Upload, Cloud, Acknowledgment, Voucher
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 20.5.1 | Submit claim | Change claim status to "Submitted" | Status = "Submitted". Submit button appears. |
| 20.5.2 | ECS Upload (simulated) | Click "Upload to ECS" | ECS tracking number generated. `ecs_uploaded_at` stamped. |
| 20.5.3 | Cloud Upload | Click "Upload to Cloud" | Cloud folder/file created. `cloud_uploaded_at` stamped. |
| 20.5.4 | Acknowledge | Click "Acknowledge" → enter acknowledgment code | `acknowledgment_code` + `acknowledgment_date` stamped. Status = "Acknowledged". |
| 20.5.5 | Record Voucher | Click "Record Voucher" → enter voucher number, amount, date | `voucher_number`, `voucher_amount`, `voucher_date` stamped. Status = "Paid". |
| 20.5.6 | Mark Denied | Click "Deny" | Status = "Denied". |
| 20.5.7 | Batch workflow | Navigate to Claim Batches → create batch → add claims → ECS Upload → Cloud Upload → Acknowledge batch | Batch-level tracking stamps applied to all claims in batch. |
| 20.5.8 | eSOA Download | Click "Download eSOA" | Print-optimized Electronic Statement of Account with claim summary. |

### 20.6 Claim Status Polling & Reports
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 20.6.1 | Poll single claim | Claim detail → Poll Status | Status poll record created. |
| 20.6.2 | Poll all submitted | `php artisan philhealth:poll-claims` | All Submitted claims polled. |
| 20.6.3 | Claim reports | Navigate to PhilHealth > Reports | Dashboard with 4 summary cards, status pie chart, aging buckets, monthly trend table. |

---

## 21. Cross-Encounter Validation

### 21.1 Active Encounter Detection
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 21.1.1 | ER + OPD conflict | Patient has active ER admission → try to create OPD consultation | Blocked: "Patient has an active ER encounter". |
| 21.1.2 | OPD + Admission conflict | Patient has active OPD → try to admit from ER | Blocked. |
| 21.1.3 | Multiple modules | Same patient active in ER, OPD, Admission simultaneously (edge case) | First encounter blocks the rest. Must dispose before creating new one. |
| 21.1.4 | Valid multi-encounter after disposition | Dispose OPD → create new OPD | Succeeds after disposition cleared. |

---

## 22. Sidebar Gating & RBAC Permissions

### 22.1 Permission-Based Visibility
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 22.1.1 | All sidebar links gated | Inspect `app.blade.php` for `@can()` on each link | Every menu item has a `@can('permission.name')` directive. No un-gated links. |
| 22.1.2 | Clinical module gating | User without `er.triage` | ER menu hidden. |
| 22.1.3 | Ancillary module gating | User without `lab.view` | Lab menu hidden. |
| 22.1.4 | Financial module gating | User without `billing.view` | Billing menu hidden. |
| 22.1.5 | Sub-item gating | User with `lab.view` but without `lab.dispense` | Lab menu visible but Dispensing sub-item hidden. |

### 22.2 Direct URL Access
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 22.2.1 | Unauthorized module access | User without `pharmacy.view` navigates to `/pharmacy/dispensing` | 403 Forbidden or redirect. |
| 22.2.2 | Superadmin bypass | Superadmin navigates to any URL | All modules accessible. |

---

## 23. Data Isolation / Multi-Tenant Scoping

### 23.1 Tenant Separation
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 23.1.1 | Tenant A sees only Tenant A data | Login as Tenant A admin → view any datagrid | Records filtered to `tenant_id = A`. No cross-tenant leakage. |
| 23.1.2 | Tenant B sees only Tenant B data | Login as Tenant B admin | Only Tenant B records visible. |
| 23.1.3 | Superadmin sees all | Login as superadmin → no tenant scope | All tenant records visible. Tenant column shown in grids. |

### 23.2 Tenant ID Assignment
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 23.2.1 | New record gets correct tenant | Create any business record | `tenant_id` auto-set to current user's tenant. |
| 23.2.2 | Composite unique per tenant | Create two records with same code under different tenants | Both succeed (different tenant_ids). Same code under same tenant blocked. |

---

## 24. Print & Certificate Workflows

### 24.1 Invoice Print
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 24.1.1 | Print invoice | Billing → Invoices → View Charges → Invoice pill button → opens new tab | Print-optimized view with manual charges section (if any), service items, payment history, totals. `@media print` CSS hides UI chrome. |

### 24.2 Payment Receipt
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 24.2.1 | Print receipt | Payments tab → printer icon on a payment row | New tab opens with receipt. Print dialog auto-shows. |

### 24.3 Lab Result Print
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 24.3.1 | DOH-compliant printout | Lab Results → Print | Three-signature block (Performed/Verified/Validated by), patient info, test results with flags. |

### 24.4 Radiology Result Print
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 24.4.1 | Structured report print | Radiology Results → Print | Technique, Findings, Impression, Report sections. Image attachments. Three-signature block. |

### 24.5 Billing Statement (Charges Print)
| ID   | Test Case | Steps | Expected Result |
|------|-----------|-------|-----------------|
| 24.5.1 | Print charges statement | Billing → View Charges → Print Charges button | "STATEMENT OF CHARGES" report in new tab. Auto-prints and closes. JSON data via hidden `<pre>` tag. |

---

## 25. Coverage Summary

| Module | Test Cases | Status |
|--------|------------|--------|
| Authentication & RBAC | 1.1.1 – 1.3.5 (16 tests) | ⬜ |
| OPD Clinical Encounters | 2.1.1 – 2.2.2 (12 tests) | ✅ |
| OB/GYN Consultations | 3.1.1 – 3.1.6 (6 tests) | ✅ |
| Delivery Room | 4.1.1 – 4.2.6 (8 tests) | ⬜ |
| Admission Module | 5.1.1 – 5.2.6 (9 tests) | ⬜ |
| Nursing Module | 6.1.1 – 6.7.1 (14 tests) | ⬜ |
| OR Module | 7.1.1 – 7.3.3 (9 tests) | ⬜ |
| Renal Care / Dialysis | 8.1.1 – 8.1.6 (6 tests) | ⬜ |
| MICU Module | 9.1.1 – 9.2.6 (6 tests) | ⬜ |
| Lab Results & Validation | 10.1.1 – 10.3.1 (8 tests) | ⬜ |
| Radiology Results & DICOM | 11.1.1 – 11.3.1 (7 tests) | ⬜ |
| Pharmacy Inventory | 12.1.1 – 12.7.1 (14 tests) | ⬜ |
| CSR Inventory & Batch | 13.1.1 – 13.1.3 (3 tests) | ⬜ |
| Procedures Dispensing | 14.1.1 – 14.1.4 (4 tests) | ⬜ |
| Billing (Patient-Centric, Manual Charges, Fund Sources, Auto-Invoice, Room & Board) | 15.1.1 – 15.7.9 (26 tests) | ⬜ |
| Doctor's Portal | 16.1.1 – 16.3.1 (9 tests) | ⬜ |
| Medical Records | 17.1.1 – 17.2.6 (10 tests) | ⬜ |
| Hospital Transfers | 18.1.1 – 18.2.4 (5 tests) | ⬜ |
| YAKAP Module | 19.1.1 – 19.5.1 (7 tests) | ⬜ |
| PhilHealth E-Claims | 20.1.1 – 20.6.3 (18 tests) | ⬜ |
| Cross-Encounter Validation | 21.1.1 – 21.1.4 (4 tests) | ⬜ |
| Sidebar Gating & RBAC | 22.1.1 – 22.2.2 (7 tests) | ⬜ |
| Data Isolation / Multi-Tenant | 23.1.1 – 23.2.2 (4 tests) | ⬜ |
| Print & Certificate Workflows | 24.1.1 – 24.5.1 (5 tests) | ⬜ |

**Total: ~194 test cases**

### Legend
- ✅ = Passed
- ❌ = Failed (bug found — log in ragdata.md)
- ⬜ = Not yet executed
- 🟡 = Partial / Blocked

### Notes for Test Execution
- Use demo tenant (`admin@demohospital.com`) for tenant-scoped tests.
- Use superadmin (`superadmin@sithemr.com`) for cross-tenant and unrestricted tests.
- Seed data must be present (`php artisan db:seed --class=DemoHospitalSeeder` after `clinical:clear --force`).
- MSSQL must be live for all tests.
- Vite dev server should be running (`npm run dev`) or assets built (`npm run build`).
- JWT auth bypasses must be configured: `SESSION_DRIVER=file`, `CACHE_STORE=file`.
- When a bug is found, log it in `ragdata.md` with the test ID and update this file's Status column.
