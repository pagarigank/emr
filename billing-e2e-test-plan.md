# E2E Test Plan: ER → Admission → Billing (Room Charges Pickup)

**App URL:** http://127.0.0.1:8000
**Credentials:** admin@demohospital.com / Admin@1234
**Date:** 2026-06-30

---

## Instructions

- Perform each step sequentially.
- Mark `[ ]` if the step **passes** (works as expected).
- Mark `[ ]` if the step **fails** (describe the actual behavior in notes).
- If a step fails, note the error/behavior and continue if possible.

---

## Prerequisites

- [ ] Application is accessible at the URL above
- [ ] Can log in with the provided credentials
- [ ] Demo data is seeded (patients, doctors, ward rooms, ICU beds, etc.)

---

## Phase 1: ER Triage — Create a Patient Encounter

| # | Step | Expected Result | Pass/Fail | Notes |
|---|------|-----------------|-----------|-------|
| 1.1 | Navigate to **ER Triage** (`/er/triage`) | Page loads with datagrid and "New Triage" button | [ ] | |
| 1.2 | Click **"New Triage"** button | Create modal opens with patient search/quick-registration form | [ ] | |
| 1.3 | In Quick Patient Registration, enter: First Name = "E2E", Last Name = "TestPatient", DOB = "1990-01-15", Gender = "Male" | Fields accept input | [ ] | |
| 1.4 | Fill Triage fields: Chief Complaint = "Chest pain", Acuity = "ESI-2" (Emergent), BP = "150/90", HR = "110", Temp = "38.5", RR = "22", O2 Sat = "95", Pain Score = "7" | All fields accept input | [ ] | |
| 1.5 | Click **"Save"** or **"Next"** to complete triage | Triage record is created with a triage code. Patient is created and visible in the datagrid. | [ ] | |
| 1.6 | Verify the new triage record appears in the datagrid | Row shows Patient Name, Triage Code, Acuity badge, Status, Datetime | [ ] | |

---

## Phase 2: ER Area — Transfer to ER & Treatment

| # | Step | Expected Result | Pass/Fail | Notes |
|---|------|-----------------|-----------|-------|
| 2.1 | Navigate to **ER Area** (`/er/area`) | Page loads with active ER admissions datagrid (may be empty) | [ ] | |
| 2.2 | Go back to ER Triage, find the E2E patient, click **"Transfer to ER"** | Transfer modal opens | [ ] | |
| 2.3 | Assign: ER Bed (select any available bed), Attending Physician (select any doctor), Arrival Mode = "Ambulance", Initial Diagnosis = "Acute Coronary Syndrome" | Fields accept input | [ ] | |
| 2.4 | Click **"Transfer"** | Patient appears in ER Area datagrid with Active status. Triage status changes to "Transferred". | [ ] | |
| 2.5 | In ER Area, click **"Treatment"** button for the patient | Treatment form opens with Patient Header Card (name, acuity, bed, vitals) and 3 tabs: Clinical Notes, Orders & Interventions, Disposition | [ ] | |
| 2.6 | **Tab: Clinical Notes** — Fill in: HPI = "Patient presents with chest pain radiating to left arm for 2 hours", PE = "Patient diaphoretic, heart sounds regular", Assessment = "ACS suspect", Treatment Notes = "ECG, labs, monitor" | Fields accept and save | [ ] | |
| 2.7 | Click **Save** on Clinical Notes tab | Data persists (reload tab to verify) | [ ] | |
| 2.8 | **Tab: Orders & Interventions** — Click **"Request Lab Test"** | Lab order modal opens with test checklist | [ ] | |
| 2.9 | Select at least 2 lab tests (e.g., CBC, Cardiac Enzymes), set Priority = "STAT", click **"Place Order"** | Lab request created (LR-xxxxx). Card appears in Orders tab with Pending status. | [ ] | |
| 2.10 | Click **"Request Pharmacy"** — select a drug (e.g., Aspirin), set quantity, frequency = "BID", Route = "PO", click **"Place Order"** | Pharmacy request created (PR-xxxxx) | [ ] | |
| 2.11 | **Tab: Disposition** — Set Disposition = "Admitted to Ward", add Discharge Instructions = "For further monitoring", click **"Confirm Disposition"** | Disposition saved. ER admission status changes to "Completed". Ward room should become available for Admission module pickup. | [ ] | |

---

## Phase 3: Admission — Admit Patient to Ward

| # | Step | Expected Result | Pass/Fail | Notes |
|---|------|-----------------|-----------|-------|
| 3.1 | Navigate to **Admission** (`/admission`) | Page loads with two tabs: "For Admission" and "Active Admissions" | [ ] | |
| 3.2 | **"For Admission" tab** — should show the E2E patient from ER disposition | Patient row appears with Encounter #, Name, Disposition ("Admitted to Ward"), Admit button | [ ] | |
| 3.3 | Click **"Admit"** button | Admit modal opens with patient info auto-filled | [ ] | |
| 3.4 | Verify: Admission Code is auto-generated (ADM-xxxxx), Admission Type = "Emergency", Source = "ER", Attending Physician is selectable | Auto-fill works | [ ] | |
| 3.5 | Select a **Ward Room** (e.g., Gen Med Ward, Room W-101) | Ward room dropdown populates with available rooms | [ ] | |
| 3.6 | Set Admission Date = today, click **"Admit"** | Admission created. Patient moves to "Active Admissions" tab with status "Admitted". Selected ward room status changes to "Occupied". | [ ] | |
| 3.7 | Click **"View"** on the admission row | Detail modal shows full admission info: patient, physicians, ward room, dates, diagnosis | [ ] | |
| 3.8 | Click **"Treatment"** button | Treatment form opens with 3 tabs: Clinical Notes, Orders & Interventions, Disposition | [ ] | |
| 3.9 | Verify that lab and pharmacy orders from ER are visible in **Orders & Interventions** tab | Orders from Phase 2 appear with their status | [ ] | |

---

## Phase 4: Billing — Invoice Generation with Room Charges

| # | Step | Expected Result | Pass/Fail | Notes |
|---|------|-----------------|-----------|-------|
| 4.1 | Navigate to **Billing** (`/billing`) | Page loads with 4 tabs: For Billing, Invoices, Payments, Reports | [ ] | |
| 4.2 | **Tab: "For Billing"** — should show unbilled items | Multiple request rows appear (lab, pharmacy from ER, plus admission room charges) | [ ] | |
| 4.3 | **Verify Room & Board row** — should show the ward room charge with daily `room_rate` from the selected ward room | A "Room & Board" or similar row appears for the admission, showing the rate and days stayed | [ ] | |
| 4.4 | Check the checkbox next to **Room & Board** line item | Checkbox is selected | [ ] | |
| 4.5 | Also check the lab and pharmacy service requests for this patient | Multiple items selected | [ ] | |
| 4.6 | Click **"Generate Invoice"** button | Invoice Create modal opens | [ ] | |
| 4.7 | **Verify Selected Service Requests** section shows all checked items with correct amounts | Room charges, lab, and pharmacy items all listed with correct ₱ amounts | [ ] | |
| 4.8 | Search for patient "TestPatient" in the patient search field and select | Patient selected, "Selected: TestPatient, E2E" shows | [ ] | |
| 4.9 | (Optional) Set a discount: Discount Type = "Percentage", Discount Value = "10" | Live preview shows computed discount amount and net total | [ ] | |
| 4.10 | Click **"Create Invoice"** | Invoice created (INV-xxxxx). All source requests get `billing_status=Billed`. | [ ] | |

---

## Phase 5: Billing — Verify Invoice Details

| # | Step | Expected Result | Pass/Fail | Notes |
|---|------|-----------------|-----------|-------|
| 5.1 | **Tab: "Invoices"** — The new invoice should appear in the datagrid | Row with INV-xxxxx, Patient name, Total amount, Unpaid status | [ ] | |
| 5.2 | Click **"View"** on the invoice | Invoice Detail modal opens | [ ] | |
| 5.3 | Verify **Invoice Items** section lists: Room & Board charge (with rate × days), Lab tests, Pharmacy drugs | All items appear with descriptions and amounts | [ ] | |
| 5.4 | Verify **Totals** section shows: Total, Discount (if set), Amount Paid (0), Balance (= Total) | Correct arithmetic | [ ] | |
| 5.5 | Click **"Print"** (Invoice pill button in header) | Invoice print page opens in new tab with professional layout | [ ] | |
| 5.6 | Verify printed invoice shows Room & Board charges, lab items, pharmacy items, totals | All sections visible in print view | [ ] | |

---

## Phase 6: Billing — Payment Recording

| # | Step | Expected Result | Pass/Fail | Notes |
|---|------|-----------------|-----------|-------|
| 6.1 | In Invoice Detail modal, click **"Add Payment"** | Payment form appears within the modal | [ ] | |
| 6.2 | Set Amount = full balance, Payment Method = "Cash", select a Budget Source if available | Fields populated | [ ] | |
| 6.3 | Click **"Save Payment"** | Payment created (PAY-xxxxx). Invoice status updates to "Paid". Balance becomes 0. | [ ] | |
| 6.4 | Verify **Payments** section in the modal shows the new payment row | Row with PAY code, amount, method, date visible | [ ] | |
| 6.5 | Click **"Receipt"** (teal button on payment row) | Payment receipt page opens in new tab | [ ] | |
| 6.6 | Verify receipt shows payment details, invoice reference, patient name | Professional receipt layout | [ ] | |
| 6.7 | **Tab: "Payments"** (Tab 3) — payment should appear in the datagrid | PAY-xxxxx, Invoice, Patient, Amount, Method, Date visible | [ ] | |

---

## Phase 7: Invoice Cancellation & Rollback

| # | Step | Expected Result | Pass/Fail | Notes |
|---|------|-----------------|-----------|-------|
| 7.1 | In Invoice Detail modal, click **"Cancel Invoice"** | Confirmation modal appears (amber-themed) | [ ] | |
| 7.2 | Confirm cancellation | Invoice status changes to "Cancelled". Source request billing_statuses revert to "Pending". If discount was applied, totals recalculated. | [ ] | |
| 7.3 | Go to **For Billing tab** — verify the previously billed items reappear as unbilled | Lab, pharmacy, and room charges show again with Pending billing status | [ ] | |

---

## Phase 8: Admission Discharge (Cleanup)

| # | Step | Expected Result | Pass/Fail | Notes |
|---|------|-----------------|-----------|-------|
| 8.1 | Navigate to **Admission** (`/admission`), find the patient in Active Admissions | Patient row visible | [ ] | |
| 8.2 | Click **"Discharge"** | Discharge confirmation modal opens | [ ] | |
| 8.3 | Confirm discharge | Admission status changes to "Discharged". Ward room status reverts to "Available". | [ ] | |

---

## Summary

| Section | Total Steps | Passed | Failed |
|---------|-------------|--------|--------|
| Phase 1: ER Triage | 6 | — | — |
| Phase 2: ER Area | 11 | — | — |
| Phase 3: Admission | 9 | — | — |
| Phase 4: Billing (Invoice) | 10 | — | — |
| Phase 5: Invoice Details | 6 | — | — |
| Phase 6: Payment | 7 | — | — |
| Phase 7: Cancellation | 3 | — | — |
| Phase 8: Discharge | 3 | — | — |
| **Total** | **55** | **0** | **0** |

---

## Bug Reporting Template

If any step fails, record:

```
**Step #:** <number>
**Actual Result:** <what happened>
**Expected Result:** <what should have happened>
**Error Details:** <copy error message or screenshot description>
**Root Cause (if known):** <optional>
```
