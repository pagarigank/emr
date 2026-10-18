<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CF3 — {{ $claim->claim_code }}</title>
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
        th { background: #f0f0f0; font-weight: bold; font-size: 7.5pt; text-transform: uppercase; width: 28%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .mt-10 { margin-top: 10px; }
        .vitals-grid { display: flex; gap: 4px; }
        .vitals-item { flex: 1; border: 1px solid #000; padding: 3px 5px; text-align: center; font-size: 8pt; }
        .vitals-item strong { display: block; font-size: 6.5pt; text-transform: uppercase; color: #555; }
        .signature-row { margin-top: 24px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 45%; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 28px; padding-top: 3px; font-size: 7pt; }
        .field-empty { color: #999; font-style: italic; }
        .narrative-box { border: 1px solid #000; padding: 6px 8px; min-height: 40px; margin: 4px 0; font-size: 8.5pt; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('philhealth.hospital.name') }}</h1>
        <p>HCI Code: {{ config('philhealth.hospital.code') }} | TIN: {{ config('philhealth.hospital.tin') }}</p>
        <p>{{ config('philhealth.hospital.contact_no') }} | {{ config('philhealth.hospital.email') }}</p>
    </div>

    <div class="title">CF3 — Clinical Record</div>
    <div class="subtitle">(Required for Level 1 facilities, catastrophic cases, maternity, ER, &lt;24hr stays, day surgeries)</div>

    @php
        $patient = $claim->patient;
    @endphp

    <table>
        <tr><th style="width:15%">Claim No:</th><td style="width:35%">{{ $claim->claim_code }}</td><th style="width:15%">Patient:</th><td style="width:35%">{{ $patient?->last_name }}, {{ $patient?->first_name }} {{ $patient?->middle_name }}</td></tr>
        <tr><th>Date Admitted:</th><td>{{ $claim->date_admitted?->format('M d, Y') ?? '—' }}</td><th>Date Discharged:</th><td>{{ $claim->date_discharged?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>Attending Physician:</th><td colspan="3">{{ $claim->attendingPhysician?->full_name ?? '—' }}</td></tr>
    </table>

    {{-- Part I: Clinical Abstract --}}
    <div class="section-title">Part I — Clinical Abstract</div>
    <table>
        <tr><th>Chief Complaint / Reason for Admission</th><td>{{ app(\App\Services\Philhealth\OecbClaimFormatter::class)->format($claim) ?: '—' }}</td></tr>
        <tr><th>History of Present Illness</th><td><div class="narrative-box">{{ $claim->history_of_present_illness ?? '—' }}</div></td></tr>
        <tr><th>Review of Systems</th><td><div class="narrative-box">{{ $claim->review_of_systems ?? '—' }}</div></td></tr>
        <tr><th>Past Medical History</th><td><div class="narrative-box">{{ $claim->pertinent_past_medical_history ?? '—' }}</div></td></tr>
        <tr><th>Physical Exam Findings</th><td><div class="narrative-box">{{ $claim->physical_exam_findings ?? '—' }}</div></td></tr>
    </table>

    <div class="vitals-grid">
        <div class="vitals-item"><strong>Blood Pressure</strong>{{ $claim->blood_pressure ?? '—' }}</div>
        <div class="vitals-item"><strong>Heart Rate</strong>{{ $claim->heart_rate ?? '—' }}</div>
        <div class="vitals-item"><strong>Temperature</strong>{{ $claim->temperature ?? '—' }}</div>
        <div class="vitals-item"><strong>Respiratory Rate</strong>{{ $claim->respiratory_rate ?? '—' }}</div>
    </div>

    {{-- Part II: Course in the Ward --}}
    <div class="section-title">Part II — Course in the Ward</div>
    <table>
        <tr><th style="width:20%">Course in the Ward</th><td><div class="narrative-box">{{ $claim->course_in_ward ?? '—' }}</div></td></tr>
        @if($claim->lab_imaging_summary)
        <tr><th>Lab/Imaging Results</th><td><div class="narrative-box">{{ $claim->lab_imaging_summary }}</div></td></tr>
        @endif
        <tr><th>Outcome</th><td><span class="fw-bold">{{ $claim->outcome ?? '—' }}</span></td></tr>
    </table>

    {{-- Part III: Maternity Care Package (OB-specific) --}}
    @if($claim->lmp_date || $claim->edc_date || $claim->gravida || $claim->para || $claim->delivery_date)
    <div class="section-title">Part III — Maternity Care Package (OB Data)</div>
    <table>
        <tr><th>LMP (Last Menstrual Period)</th><td>{{ $claim->lmp_date?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>EDC (Expected Date of Confinement)</th><td>{{ $claim->edc_date?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>Gravida</th><td>{{ $claim->gravida ?? '—' }}</td></tr>
        <tr><th>Para</th><td>{{ $claim->para ?? '—' }}</td></tr>
        <tr><th>Delivery Date</th><td>{{ $claim->delivery_date?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>Delivery Type</th><td>{{ $claim->deliveryType?->name ?? '—' }}</td></tr>
        <tr><th>Newborn Outcome</th><td>{{ $claim->newborn_outcome ?? '—' }}</td></tr>
    </table>
    @endif

    {{-- Part IV: Clinical Impression --}}
    <div class="section-title">Part IV — Clinical Impression</div>
    <table>
        <tr><th>Admitting Diagnosis</th><td>{{ $claim->admitting_diagnosis ?? '—' }}</td></tr>
        <tr><th>Discharge Diagnosis</th><td>{{ $claim->discharge_diagnosis ?? '—' }}</td></tr>
        <tr><th>ICD-10 Code</th><td>{{ $claim->icd10Code?->code }} — {{ $claim->icd10Code?->name ?? '—' }}</td></tr>
        <tr><th>Attending Physician</th><td>{{ $claim->attendingPhysician?->full_name ?? '—' }}</td></tr>
        <tr><th>Physician Signature Date</th><td>{{ $claim->physician_signature_date?->format('M d, Y') ?? '—' }}</td></tr>
    </table>

    <div class="signature-row">
        <div class="signature-box">
            <div class="line">Attending Physician Signature</div>
        </div>
        <div class="signature-box">
            <div class="line">Date Printed: {{ now()->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    <p style="margin-top:12px;font-size:6.5pt;text-align:center;color:#666;">
        This is a computer-generated document. No signature is required.<br>
        {{ config('philhealth.system.name') }} v{{ config('philhealth.system.version') }} — CF3 Clinical Record
    </p>
</body>
</html>
