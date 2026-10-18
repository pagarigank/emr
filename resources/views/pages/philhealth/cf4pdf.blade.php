<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CF4 — {{ $claim->claim_code }}</title>
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: 'Courier New', monospace; font-size: 9pt; color: #000; line-height: 1.35; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 13pt; margin: 0 0 3px; text-transform: uppercase; }
        .header p { margin: 1px 0; font-size: 8pt; }
        .title { text-align: center; font-size: 11pt; font-weight: bold; margin: 12px 0; text-transform: uppercase; letter-spacing: 1px; }
        .subtitle { text-align: center; font-size: 8pt; margin: 2px 0 10px; color: #444; }
        .section-title { font-size: 9pt; font-weight: bold; margin: 14px 0 6px; padding: 3px 6px; background: #f0f0f0; border: 1px solid #000; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 8.5pt; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-weight: bold; font-size: 7.5pt; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .mt-10 { margin-top: 10px; }
        .vitals-grid { display: flex; gap: 4px; }
        .vitals-item { flex: 1; border: 1px solid #000; padding: 3px 5px; text-align: center; font-size: 8pt; }
        .vitals-item strong { display: block; font-size: 6.5pt; text-transform: uppercase; color: #555; }
        .narrative-box { border: 1px solid #000; padding: 6px 8px; min-height: 36px; margin: 4px 0; font-size: 8.5pt; }
        .signature-row { margin-top: 24px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 45%; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 28px; padding-top: 3px; font-size: 7pt; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('philhealth.hospital.name') }}</h1>
        <p>HCI Code: {{ config('philhealth.hospital.code') }} | TIN: {{ config('philhealth.hospital.tin') }}</p>
        <p>{{ config('philhealth.hospital.contact_no') }} | {{ config('philhealth.hospital.email') }}</p>
    </div>

    <div class="title">CF4 — Clinical Abstract (Extended)</div>
    <div class="subtitle">Comprehensive Clinical Data Capture for e-Claims Submission</div>

    @php
        $patient = $claim->patient;
    @endphp

    {{-- Part I: HCI & Patient Information --}}
    <div class="section-title">Part I — HCI & Patient Information</div>
    <table>
        <tr><th style="width:20%">HCI Name</th><td style="width:30%">{{ config('philhealth.hospital.name') }}</td><th style="width:20%">HCI Code</th><td style="width:30%">{{ config('philhealth.hospital.code') }}</td></tr>
        <tr><th>Patient Name</th><td>{{ $patient?->last_name }}, {{ $patient?->first_name }} {{ $patient?->middle_name }} {{ $patient?->suffix }}</td><th>Patient Code</th><td>{{ $patient?->patient_code ?? '—' }}</td></tr>
        <tr><th>PhilHealth PIN</th><td>{{ $claim->philhealthMember?->pin ?? '—' }}</td><th>Relationship to Member</th><td>{{ $claim->relationship_to_member ?? 'Self' }}</td></tr>
        <tr><th>Claim No.</th><td>{{ $claim->claim_code }}</td><th>Claim Type</th><td>{{ $claim->claim_type ?? '—' }}</td></tr>
        <tr><th>Date Admitted</th><td>{{ $claim->date_admitted?->format('M d, Y') ?? '—' }}</td><th>Time Admitted</th><td>{{ $claim->time_admitted ? date('g:i A', strtotime($claim->time_admitted)) : '—' }}</td></tr>
        <tr><th>Date Discharged</th><td>{{ $claim->date_discharged?->format('M d, Y') ?? '—' }}</td><th>Time Discharged</th><td>{{ $claim->time_discharged ? date('g:i A', strtotime($claim->time_discharged)) : '—' }}</td></tr>
        <tr><th>Admitting Diagnosis</th><td colspan="3">{{ $claim->admitting_diagnosis ?? '—' }}</td></tr>
        <tr><th>Discharge Diagnosis</th><td colspan="3">{{ $claim->discharge_diagnosis ?? '—' }}</td></tr>
        <tr><th>ICD-10 (Primary)</th><td>{{ $claim->icd10Code?->code }} — {{ $claim->icd10Code?->name ?? '—' }}</td><th>Case Rate</th><td>{{ $claim->caseRate?->rvs_code ?? '—' }}</td></tr>
        <tr><th>ICD-10 (Secondary)</th><td colspan="3">{{ $claim->secondaryIcd10Code?->code ? $claim->secondaryIcd10Code->code . ' — ' . $claim->secondaryIcd10Code->name : '—' }}</td></tr>
        <tr><th>ICD-10 (Tertiary)</th><td colspan="3">{{ $claim->tertiaryIcd10Code?->code ? $claim->tertiaryIcd10Code->code . ' — ' . $claim->tertiaryIcd10Code->name : '—' }}</td></tr>
        @if($claim->claim_type === 'OECB' && $claim->erAdmission)
        <tr><th colspan="4" style="background:#e0f2fe;color:#0369a1;text-align:center;">OECB — Outpatient Emergency Care Benefit</th></tr>
        <tr><th>Core Presentation</th><td>{{ $claim->erAdmission->core_presentation ?? '—' }}</td><th>Package Code</th><td>{{ $claim->erAdmission->oecb_package_code ?? '—' }}</td></tr>
        <tr><th>Fixed Fee</th><td>&#x20B1;{{ number_format($claim->erAdmission->oecb_fixed_fee ?? 0, 2) }}</td><th>ER Encounter</th><td>{{ $claim->erAdmission->admission_code ?? '—' }}</td></tr>
        @endif
    </table>

    {{-- Part II: Reason for Admission --}}
    <div class="section-title">Part II — Reason for Admission</div>
    <table>
        <tr><th style="width:18%">Chief Complaint</th><td>{{ app(\App\Services\Philhealth\OecbClaimFormatter::class)->format($claim) ?: '—' }}</td></tr>
        <tr><th>History of Present Illness</th><td><div class="narrative-box">{{ $claim->history_of_present_illness ?? '—' }}</div></td></tr>
        <tr><th>Pertinent Past Medical History</th><td><div class="narrative-box">{{ $claim->pertinent_past_medical_history ?? '—' }}</div></td></tr>
        <tr><th>Review of Systems</th><td><div class="narrative-box">{{ $claim->review_of_systems ?? '—' }}</div></td></tr>
    </table>

    {{-- Part III: Physical Examination --}}
    <div class="section-title">Part III — Physical Examination</div>
    <table>
        <tr><th style="width:18%">Physical Exam Findings</th><td><div class="narrative-box">{{ $claim->physical_exam_findings ?? '—' }}</div></td></tr>
    </table>

    <div class="vitals-grid">
        <div class="vitals-item"><strong>Blood Pressure</strong>{{ $claim->blood_pressure ?? '—' }}</div>
        <div class="vitals-item"><strong>Heart Rate</strong>{{ $claim->heart_rate ?? '—' }}</div>
        <div class="vitals-item"><strong>Temperature</strong>{{ $claim->temperature ?? '—' }}</div>
        <div class="vitals-item"><strong>Respiratory Rate</strong>{{ $claim->respiratory_rate ?? '—' }}</div>
    </div>

    {{-- Part IV: Course in the Ward --}}
    <div class="section-title">Part IV — Course in the Ward</div>
    <table>
        <tr><th style="width:18%">Course in the Ward</th><td><div class="narrative-box">{{ $claim->course_in_ward ?? '—' }}</div></td></tr>
        @if($claim->lab_imaging_summary)
        <tr><th>Significant Lab/Imaging</th><td><div class="narrative-box">{{ $claim->lab_imaging_summary }}</div></td></tr>
        @endif
    </table>

    {{-- Part V: Drugs & Medicines / Procedures --}}
    <div class="section-title">Part V — Drugs, Medicines, & Procedures (Claim Items)</div>
    @if($claim->items->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:11%">Type</th>
                <th style="width:9%">Code</th>
                <th>Description</th>
                <th style="width:6%">Qty</th>
                <th style="width:10%">Unit Price</th>
                <th style="width:11%">Actual Amount</th>
                <th style="width:11%">Claimed Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($claim->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $item->item_type }}</td>
                <td class="text-center">{{ $item->item_code ?? '—' }}</td>
                <td>{{ $item->item_name }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">&#x20B1;{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">&#x20B1;{{ number_format($item->actual_amount, 2) }}</td>
                <td class="text-right">&#x20B1;{{ number_format($item->claimed_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="6" class="text-right">Totals:</td>
                <td class="text-right">&#x20B1;{{ number_format($claim->total_actual_amount, 2) }}</td>
                <td class="text-right">&#x20B1;{{ number_format($claim->total_claimed_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <p style="color:#999;font-style:italic;">No claim items recorded.</p>
    @endif

    {{-- Part VI: Outcome & Certification --}}
    <div class="section-title">Part VI — Outcome & Certification</div>
    <table>
        <tr><th style="width:25%">Outcome</th><td><span class="fw-bold">{{ $claim->outcome ?? '—' }}</span></td></tr>
        <tr><th>Attending Physician</th><td>{{ $claim->attendingPhysician?->full_name ?? '—' }}</td></tr>
        <tr><th>Referring Physician</th><td>{{ $claim->referringPhysician?->full_name ?? '—' }}</td></tr>
        <tr><th>HCI Authorized Signatory</th><td>{{ $claim->hci_signatory_name ?? '—' }}</td></tr>
        <tr><th>Patient/Member Signature Date</th><td>{{ $claim->patient_signature_date?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>Physician Signature Date</th><td>{{ $claim->physician_signature_date?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>HCI Signature Date</th><td>{{ $claim->hci_signature_date?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>Status</th><td><span class="fw-bold">{{ $claim->status }}</span></td></tr>
    </table>

    <div class="signature-row">
        <div class="signature-box">
            <div class="line">Attending Physician Signature</div>
        </div>
        <div class="signature-box">
            <div class="line">Patient / Member Signature</div>
        </div>
    </div>
    <div class="signature-row">
        <div class="signature-box">
            <div class="line">HCI Authorized Representative Signature</div>
        </div>
        <div class="signature-box">
            <div class="line">Date Printed: {{ now()->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    <p style="margin-top:12px;font-size:6.5pt;text-align:center;color:#666;">
        This is a computer-generated document. No signature is required.<br>
        {{ config('philhealth.system.name') }} v{{ config('philhealth.system.version') }} — CF4 Clinical Abstract
    </p>
</body>
</html>
