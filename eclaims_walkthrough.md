# PhilHealth e-Claims — Manual Test Walkthrough

## Prerequisites

| Requirement | Check |
|-------------|-------|
| App running (`php artisan serve`) | `http://127.0.0.1:8000` |
| Logged in as `tenant_admin` or `superadmin` | `admin@demohospital.com` / `Admin@1234` |
| PhilHealth tables migrated | `php artisan migrate --force` |
| At least 1 Patient with PhilHealth Member link | See **Patients** module |
| At least 1 ICD-10 code seeded | Pre-seeded |
| At least 1 Case Rate seeded | Pre-seeded |

---

## 1. Claim CRUD

### Create a Claim
1. Navigate to **PhilHealth → Claims**
2. Click **New Claim**
3. Fill in:
   - **Patient**: Search and select a patient
   - **PhilHealth Member**: Auto-populated if patient has one
   - **Date/Time Admitted & Discharged**: Fill in
   - **Admitting / Discharge Diagnosis**: Type free text
   - **ICD-10 Code**: Select from dropdown
   - **Case Rate**: Select from dropdown
4. Fill **CF4 Clinical Data** (Section B):
   - History of Present Illness
   - Past Medical History
   - Physical Exam Findings
   - Course in Ward
5. Add **Claim Items** (Section C):
   - Click **Add Item**
   - Select type (Drug/Medicine, Laboratory, etc.)
   - Enter item code (RVS), name, quantity, unit price
   - Actual and Claimed amounts auto-total
   - Add 2-3 items
6. Set **Outcome** (Improved, Recovered, etc.) and **Status** (Draft / For Submission)
7. Click **Create Claim**

**Expected**: Toast "Claim created successfully." Modal closes. New row appears in datatable.

### Edit a Claim
- Click the **Edit** (pencil) icon on a Draft/For Submission claim
- Modify fields (add/remove items, change diagnosis, etc.)
- Click **Update Claim**

**Expected**: Toast "Claim updated successfully." Datatable refreshes.

### Archive (Delete) a Claim
- Click the **Archive** (trash) icon
- Confirm in the modal

**Expected**: Toast "Claim archived successfully." Row disappears from grid.

### View Details
- Click the **View** (eye) icon on any claim
- Detail modal shows: patient info, member, ICD-10, case rate, admission/discharge, clinical data, items table with totals, ECS/Cloud/Acknowledgment/Voucher status chips

---

## 2. Pagination & Search

- **Search**: Type a claim code or diagnosis text in the search box. Results filter as you type (300ms debounce).
- **Rows**: Change the per-page dropdown (10/25/50).
- **Sort**: Click column headers (Claim #, Patient, Status, etc.) to toggle ascending/descending.
- **Pagination**: `$items->links()` renders at the bottom of the table.

---

## 3. Check Eligibility (CEWS)

1. Open a claim's detail modal
2. Click **Check Eligibility**
3. Eligibility modal appears with result:
   - If no member linked: "No PhilHealth member linked to this claim."
   - If member exists: SOAP response or fallback simulated result
   - **Fallback mode**: Since no real credentials, the service returns simulated eligibility

---

## 4. ECS Upload

1. Open a claim's detail modal (status must be Submitted/Acknowledged)
2. Click **ECS** button
3. Modal opens with "Upload to ECS" action
4. Click the upload button

**Expected**: Toast "Claim uploaded to ECS." Tracking number shown. `ECS` badge appears on claim row. Claim status unchanged.

---

## 5. Cloud Storage Upload

1. Open a claim's detail modal (status must be Submitted/Acknowledged)
2. Click **Cloud** button (appears after ECS upload)
3. Modal opens with upload action
4. Click upload

**Expected**: Toast "Claim uploaded to Cloud Storage." `CLOUD` badge appears on claim row.

---

## 6. Acknowledgment

1. Open a claim's detail modal (status must be Submitted/Acknowledged)
2. Click **Acknowledge** button
3. Enter acknowledgment code (e.g. `ACK-2025-001`)
4. Submit

**Expected**: Toast "Claim acknowledged successfully." Status changes to `Acknowledged`.
- Email notification sent (check `storage/logs/laravel.log` for "ClaimStatusNotification" log line)

---

## 7. Voucher / Payment

1. Open an Acknowledged claim's detail modal
2. Click **Voucher** button
3. Enter voucher number (e.g. `VCH-001`) and amount (e.g. `35000.00`)
4. Submit

**Expected**: Toast "Voucher recorded. Claim marked as Paid." Status changes to `Paid`. `V` badge appears on claim row.
- Email notification sent (check log for paid notification)

---

## 8. Deny / Rejection

1. Open a non-Paid/non-Denied claim's detail modal
2. Click **Deny** button
3. Enter a reason (e.g. "Incomplete documentation")
4. Submit

**Expected**: Toast "Claim marked as Denied." Status changes to `Denied`.
- Email notification sent (check log for denied notification)

### Record Formal Rejection
1. Open a Denied claim's detail modal
2. Click **Record Rejection** (if available in detail modal — currently via separate button)
3. Enter rejection code, reason, and date
4. Submit

**Expected**: Rejection recorded. Claim rejection history is created.

---

## 9. CF5 / DRG Calculation

1. Open any claim's detail modal
2. Click **CF5/DRG** button
3. DRG calculation runs:
   - Maps ICD-10 code prefix to DRG code/weight
   - Calculates: total DRG = base_rate (₱35,000) × drg_weight
   - Splits: HF = 60%, PF = 40%
   - Compares: DRG amount vs actual case rate
   - Shows break-even: gain (green) or loss (red)
4. Click **Print CF5** to open the print-optimized CF5 form

**Expected**: CF5 modal shows:
- DRG classification table (code, description, weight, base rate)
- Financial comparison table
- Break-even analysis with weight variance %

---

## 10. QR Code

1. Open a claim's detail modal
2. Click **QR** button
3. Modal shows QR code SVG image (eGov Super App URL with claim data)

**Expected**: QR code displays. Scan with eGov Super App (when live) to view claim.

---

## 11. Status Polling

### Manual Poll
1. Open a Submitted/Acknowledged claim's detail modal
2. Click **Poll Status**
3. Result message appears (simulated or from SOAP)

### Poll History
1. Click **History** (next to Poll Status)
2. Modal lists all previous polls with: timestamp, source (manual/auto), status before/after, response data

---

## 12. Resubmission

### Claim-Level
1. Open a Denied claim's detail modal
2. Click **Resubmit** (if available)
3. Latest pending rejection is marked as "Resubmitted"
4. Claim status resets to "For Submission", acknowledgment code cleared

### Batch-Level
1. Navigate to **Batch CRUD**
2. Open a Submitted/Acknowledged batch's detail modal
3. Click **Resubmit Denied**
4. Confirmation modal explains: all Denied claims in batch will be reset to For Submission
5. Confirm

**Expected**: All denied claims in batch resubmitted. Email notifications sent per claim.

---

## 13. Batch CRUD

### Create a Batch
1. Navigate to **PhilHealth → Batches**
2. Click **New Batch**
3. Batch code auto-generates. Add claims to the batch.
4. Save

### Batch Actions
- **Upload to ECS**: Uploads all claims in batch as single XML transmittal
- **Upload to Cloud**: Uploads batch folder to PhilHealth Cloud
- **Acknowledge Batch**: Enter batch-level acknowledgment code
- **Resubmit Denied**: Resubmits all denied claims

### ECS Upload (Batch)
1. Open batch detail
2. Click **ECS Upload**
3. Tracks batch-level ECS tracking number

### Cloud Upload (Batch)
1. Open batch detail
2. Click **Cloud Upload**
3. Creates cloud folder per batch

---

## 14. Reports

1. Navigate to **PhilHealth → Reports**
2. Set date range (default: this month)
3. Summary cards load:
   - Total Claims, Total Claimed, Total Paid, Total Actual
   - Approval Rate %, Collection Rate %
4. Status Breakdown: bar chart with counts per status
5. Aging: buckets (0-30, 31-60, 61-90, 90+ days)
6. Monthly Trend: 12-month table with columns: Month, Claims, Actual, Claimed, Paid, Collection %

### CSV Export
- Click **CSV** button → downloads `philhealth-reports-YYYY-MM-DD.csv`
- Open in Excel/Google Sheets to verify columns

### Excel Export
- Click **Excel** button → downloads `philhealth-reports-YYYY-MM-DD.xlsx`
- File has styled headers (dark blue), auto-sized columns, 4 sections:
  - Summary metrics
  - Status breakdown
  - Aging buckets
  - Monthly trend with collection %

---

## 15. Dashboard Widget

1. Navigate to **Home/Dashboard**
2. PhilHealth section shows 8 stat cards:
   - **Total Claims**: Count of all claims
   - **For Submission**: Count of pending claims
   - **Submitted / Ack'd**: Count in pipeline
   - **Approval Rate %**: Paid / (Paid + Denied)
   - **Paid**: Count of paid claims (green)
   - **Denied**: Count of denied claims (red)
   - **This Month Claimed**: Total claimed ₱
   - **This Month Collected**: Total paid ₱ with collection %

---

## 16. PDF Downloads

### eSOA PDF
1. Open any claim's detail modal
2. Click **eSOA** button
3. Downloads `esoapdf-{id}.pdf` via DomPDF
4. Verify: hospital header, patient info, ICD-10, case rate, itemized items, totals, signature block

### CF5 PDF
1. Open CF5 modal on any claim
2. Click **Print CF5** link
3. Downloads `cf5-{id}.pdf` via DomPDF
4. Verify: DRG classification, financial comparison, break-even analysis, itemized items

### XML Download
1. Open any claim's detail modal
2. Click **Download XML**
3. Downloads `{claim_code}.xml`
4. Verify XML structure: `<eCLAIMS>`, `<eTRANSMITTAL>`, `<CLAIM>`, `<ICDCODES>`, `<PACKAGE>`

---

## 17. Email Notifications (Log-Based)

Since `MAIL_MAILER=log` in `.env`, emails are written to `storage/logs/laravel.log` instead of being sent.

To verify:
```bash
grep "ClaimStatusNotification" storage/logs/laravel.log
```

Expected log lines:
- Acknowledged: `Subject: Claim Acknowledged — {claim_code}`
- Paid: `Subject: Claim Paid — {claim_code} — VCH-xxx`
- Denied: `Subject: Claim Denied — {claim_code}`
- Resubmitted: `Subject: Claim Resubmitted — {claim_code}`

To switch to real SMTP, set in `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
```

---

## 18. Scheduler (Artisan Command)

The daily poll command can be run manually:
```bash
php artisan philhealth:poll-claims
```

Options:
- `--batch=5` — poll all claims in batch ID 5
- `--claim=12` — poll a single claim ID 12

Logs written to `storage/logs/philhealth-poll.log`.

The scheduler is configured in `routes/console.php` to run daily at 02:00.
On production, add cron entry:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 19. Claim Status State Machine

```
                        ┌──────────┐
                        │  Draft   │
                        └────┬─────┘
                             │
                        ┌────▼─────────┐
                        │ For Submission│
                        └────┬─────────┘
                             │
                        ┌────▼─────┐
                        │ Submitted │
                        └────┬─────┘
                             │
                   ┌─────────▼──────────┐
                   │   ECS Upload       │
                   └─────────┬──────────┘
                             │
                   ┌─────────▼──────────┐
                   │  Cloud Upload      │
                   └─────────┬──────────┘
                             │
                   ┌─────────▼──────────┐
                   │   Acknowledged     │
                   └─────────┬──────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
         ┌────▼─────┐  ┌────▼─────┐   ┌────▼──────────┐
         │   Paid   │  │  Denied  │   │  Poll Status  │
         └──────────┘  └────┬─────┘   │  (no change)  │
                            │         └───────────────┘
                            │
                      ┌─────▼──────────┐
                      │  Resubmitted   │
                      └─────┬──────────┘
                            │
                      ┌─────▼──────────────┐
                      │  For Submission     │
                      │  (loop back)        │
                      └────────────────────┘
```

---

## 20. Test Checklist

| # | Test Case | Status |
|---|-----------|--------|
| 1 | Create claim with all CF4 clinical fields | `☐` |
| 2 | Add 3+ claim items of different types | `☐` |
| 3 | Edit claim (change diagnosis, add/remove items) | `☐` |
| 4 | Archive claim (soft delete) | `☐` |
| 5 | View claim detail modal | `☐` |
| 6 | Search by claim code | `☐` |
| 7 | Search by diagnosis text | `☐` |
| 8 | Sort by each column (asc/desc) | `☐` |
| 9 | Change per-page rows (10/25/50) | `☐` |
| 10 | Check Eligibility (fallback/simulated) | `☐` |
| 11 | ECS Upload (simulated) | `☐` |
| 12 | Cloud Storage Upload (simulated) | `☐` |
| 13 | Acknowledgment with code | `☐` |
| 14 | Record Voucher (number + amount) | `☐` |
| 15 | Deny claim with reason | `☐` |
| 16 | Record Rejection (code, reason, date) | `☐` |
| 17 | CF5/DRG calculation (check against ICD-10) | `☐` |
| 18 | Print CF5 form | `☐` |
| 19 | QR code generation | `☐` |
| 20 | Manual status poll | `☐` |
| 21 | Poll history view | `☐` |
| 22 | Resubmit denied claim | `☐` |
| 23 | Create batch + add claims | `☐` |
| 24 | Batch ECS upload | `☐` |
| 25 | Batch Cloud upload | `☐` |
| 26 | Batch acknowledgment | `☐` |
| 27 | Batch resubmit denied | `☐` |
| 28 | Reports page loads with date range | `☐` |
| 29 | Reports CSV export | `☐` |
| 30 | Reports Excel export | `☐` |
| 31 | Dashboard shows PhilHealth stats | `☐` |
| 32 | eSOA PDF download | `☐` |
| 33 | CF5 PDF download | `☐` |
| 34 | XML download (valid XML structure) | `☐` |
| 35 | Email notification logged on acknowledge | `☐` |
| 36 | Email notification logged on paid | `☐` |
| 37 | Email notification logged on denied | `☐` |
| 38 | Email notification logged on resubmit | `☐` |
| 39 | `philhealth:poll-claims` artisan command | `☐` |
| 40 | Scheduler config in `routes/console.php` | `☐` |
| 41 | Pagination: second page loads correctly | `☐` |
| 42 | Claim status state machine transitions | `☐` |
