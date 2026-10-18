<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CF2 — {{ $claim->claim_code }}</title>
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: 'Courier New', monospace; font-size: 9pt; color: #000; line-height: 1.35; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 13pt; margin: 0 0 3px; text-transform: uppercase; }
        .header p { margin: 1px 0; font-size: 8pt; }
        .title { text-align: center; font-size: 11pt; font-weight: bold; margin: 12px 0; text-transform: uppercase; letter-spacing: 1px; }
        .section-title { font-size: 9pt; font-weight: bold; margin: 14px 0 6px; padding: 3px 6px; background: #f0f0f0; border: 1px solid #000; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 8.5pt; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-weight: bold; font-size: 7.5pt; text-transform: uppercase; width: 30%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .mt-10 { margin-top: 10px; }
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

    <div class="title">CF2 — Health Care Institution Report</div>

    @php
        $patient = $claim->patient;
    @endphp

    {{-- Part I: HCI Information --}}
    <div class="section-title">Part I — HCI Information</div>
    <table>
        <tr><th>HCI Name</th><td>{{ config('philhealth.hospital.name') }}</td></tr>
        <tr><th>HCI Code (PhilHealth)</th><td>{{ config('philhealth.hospital.code') }}</td></tr>
        <tr><th>HCI Address</th><td>{{ config('philhealth.hospital.name') }}, {{ config('philhealth.hospital.contact_no') }}</td></tr>
        <tr><th>Accreditation No.</th><td>{{ config('philhealth.hospital.code') }}</td></tr>
        <tr><th>Contact No.</th><td>{{ config('philhealth.hospital.contact_no') }}</td></tr>
        <tr><th>Email</th><td>{{ config('philhealth.hospital.email') }}</td></tr>
    </table>

    {{-- Part II: Patient Confinement Details --}}
    <div class="section-title">Part II — Patient Confinement Details</div>
    <table>
        <tr><th>Patient Name</th><td>{{ $patient?->last_name }}, {{ $patient?->first_name }} {{ $patient?->middle_name }} {{ $patient?->suffix }}</td></tr>
        <tr><th>PhilHealth PIN</th><td>{{ $claim->philhealthMember?->pin ?? '—' }}</td></tr>
        <tr><th>Date Admitted</th><td>{{ $claim->date_admitted?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>Time Admitted</th><td>{{ $claim->time_admitted ? date('g:i A', strtotime($claim->time_admitted)) : '—' }}</td></tr>
        <tr><th>Date Discharged</th><td>{{ $claim->date_discharged?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>Time Discharged</th><td>{{ $claim->time_discharged ? date('g:i A', strtotime($claim->time_discharged)) : '—' }}</td></tr>
        <tr><th>Admitting Diagnosis</th><td>{{ $claim->admitting_diagnosis ?? '—' }}</td></tr>
        <tr><th>Discharge / Final Diagnosis</th><td>{{ $claim->discharge_diagnosis ?? '—' }}</td></tr>
        <tr><th>ICD-10 Code (Primary)</th><td>{{ $claim->icd10Code?->code }} — {{ $claim->icd10Code?->name ?? '—' }}</td></tr>
        <tr><th>ICD-10 Code (Secondary)</th><td>{{ $claim->secondaryIcd10Code?->code ? $claim->secondaryIcd10Code->code . ' — ' . $claim->secondaryIcd10Code->name : '—' }}</td></tr>
        <tr><th>ICD-10 Code (Tertiary)</th><td>{{ $claim->tertiaryIcd10Code?->code ? $claim->tertiaryIcd10Code->code . ' — ' . $claim->tertiaryIcd10Code->name : '—' }}</td></tr>
        <tr><th>Case Rate Package</th><td>{{ $claim->caseRate?->rvs_code }} — {{ $claim->caseRate?->description ?? '—' }}</td></tr>
        <tr><th>Claim Type</th><td>{{ $claim->claim_type ?? '—' }}</td></tr>
        <tr><th>Outcome</th><td>{{ $claim->outcome ?? '—' }}</td></tr>
    </table>

    {{-- Part III: Claim Items --}}
    <div class="section-title">Part III — Claim Items / Services Rendered</div>
    @if($claim->items->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:11%">Type</th>
                <th style="width:10%">RVS Code</th>
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

    {{-- Part IV: HCI Certification --}}
    <div class="section-title">Part IV — HCI Certification</div>
    <table>
        <tr><th>Attending Physician</th><td>{{ $claim->attendingPhysician?->full_name ?? '—' }}</td></tr>
        <tr><th>Referring Physician</th><td>{{ $claim->referringPhysician?->full_name ?? '—' }}</td></tr>
        <tr><th>Physician Signature Date</th><td>{{ $claim->physician_signature_date?->format('M d, Y') ?? '—' }}</td></tr>
        <tr><th>HCI Authorized Signatory</th><td>{{ $claim->hci_signatory_name ?? '—' }}</td></tr>
        <tr><th>HCI Signature Date</th><td>{{ $claim->hci_signature_date?->format('M d, Y') ?? '—' }}</td></tr>
    </table>

    <div class="signature-row">
        <div class="signature-box">
            <div class="line">Attending Physician Signature</div>
        </div>
        <div class="signature-box">
            <div class="line">HCI Authorized Representative Signature</div>
        </div>
    </div>
    <div class="signature-row">
        <div class="signature-box">
            <div class="line">Date Printed: {{ now()->format('M d, Y h:i A') }}</div>
        </div>
        <div class="signature-box">
            <div class="line">Prepared By / Encoder</div>
        </div>
    </div>

    <p style="margin-top:12px;font-size:6.5pt;text-align:center;color:#666;">
        This is a computer-generated document. No signature is required.<br>
        {{ config('philhealth.system.name') }} v{{ config('philhealth.system.version') }} — CF2 HCI Report
    </p>
</body>
</html>
