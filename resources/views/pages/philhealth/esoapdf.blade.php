<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>eSOA — {{ $claim->claim_code }}</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'Courier New', monospace; font-size: 10pt; color: #000; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 14pt; margin: 0 0 4px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 9pt; }
        .title { text-align: center; font-size: 12pt; font-weight: bold; margin: 15px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 9pt; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; font-size: 8pt; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .mt-20 { margin-top: 20px; }
        .mb-10 { margin-bottom: 10px; }
        .signature-row { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 45%; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 30px; padding-top: 4px; font-size: 8pt; }
        .totals-table td:last-child { text-align: right; font-weight: bold; }
        .no-border td { border: none; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('philhealth.hospital.name') }}</h1>
        <p>TIN: {{ config('philhealth.hospital.tin') }} | Contact: {{ config('philhealth.hospital.contact_no') }}</p>
        <p>{{ config('philhealth.hospital.email') }}</p>
    </div>

    <div class="title">Electronic Statement of Account (eSOA)</div>

    <table>
        <tr>
            <th style="width:25%">e-Claim No:</th>
            <td style="width:25%">{{ $claim->claim_code }}</td>
            <th style="width:25%">Batch Code:</th>
            <td style="width:25%">{{ $claim->claimBatch?->batch_code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Patient Name:</th>
            <td>{{ $claim->patient?->last_name }}, {{ $claim->patient?->first_name }} {{ $claim->patient?->middle_name }}</td>
            <th>Patient Code:</th>
            <td>{{ $claim->patient?->patient_code }}</td>
        </tr>
        <tr>
            <th>PhilHealth PIN:</th>
            <td>{{ $claim->philhealthMember?->pin ?? '—' }}</td>
            <th>Member Type:</th>
            <td>{{ $claim->philhealthMember?->membership_type ?? '—' }}</td>
        </tr>
        <tr>
            <th>Date Admitted:</th>
            <td>{{ $claim->date_admitted?->format('M d, Y') }} {{ $claim->time_admitted ? date('g:i A', strtotime($claim->time_admitted)) : '' }}</td>
            <th>Date Discharged:</th>
            <td>{{ $claim->date_discharged?->format('M d, Y') }} {{ $claim->time_discharged ? date('g:i A', strtotime($claim->time_discharged)) : '' }}</td>
        </tr>
        <tr>
            <th>Admitting Diagnosis:</th>
            <td colspan="3">{{ $claim->admitting_diagnosis ?? '—' }}</td>
        </tr>
        <tr>
            <th>Discharge Diagnosis:</th>
            <td colspan="3">{{ $claim->discharge_diagnosis ?? '—' }}</td>
        </tr>
        <tr>
            <th>ICD-10 Code:</th>
            <td>{{ $claim->icd10Code?->code ?? '—' }}</td>
            <th>Case Rate:</th>
            <td>{{ $claim->caseRate?->rvs_code ?? '—' }}</td>
        </tr>
        <tr>
            <th>Claim Type:</th>
            <td>{{ $claim->claim_type }} @if($claim->claim_type === 'OECB') (OECB) @endif</td>
            <th>Outcome:</th>
            <td>{{ $claim->outcome ?? '—' }}</td>
        </tr>
        <tr>
            <th>Status:</th>
            <td>{{ $claim->status }}</td>
            <th>Claimed Days:</th>
            <td>{{ $claim->date_admitted && $claim->date_discharged ? max(1, $claim->date_admitted->diffInDays($claim->date_discharged) + 1) : 1 }}</td>
        </tr>
        @if($claim->claim_type === 'OECB' && $claim->erAdmission)
        <tr style="background:#f0f9ff;">
            <th colspan="4" style="text-align:center;color:#0369a1;">OECB — ER Encounter: {{ $claim->erAdmission->admission_code ?? '—' }}</th>
        </tr>
        <tr>
            <th>Core Presentation:</th>
            <td>{{ $claim->erAdmission->core_presentation ?? '—' }}</td>
            <th>Package Code:</th>
            <td>{{ $claim->erAdmission->oecb_package_code ?? '—' }}</td>
        </tr>
        @endif
    </table>

    @if($claim->items->count() > 0)
    <h4 style="margin:12px 0 4px;font-size:10pt;">Claim Items</h4>
    <table>
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:12%">Type</th>
                <th style="width:10%">Code</th>
                <th>Description</th>
                <th style="width:8%">Qty</th>
                <th style="width:12%">Unit Price</th>
                <th style="width:12%">Actual Amount</th>
                <th style="width:12%">Claimed Amount</th>
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
    @endif

    @if($claim->caseRate)
    <h4 style="margin:12px 0 4px;font-size:10pt;">Case Rate Breakdown</h4>
    <table>
        <tr>
            <th style="width:40%">Case Rate Code</th>
            <td>{{ $claim->caseRate->rvs_code }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ $claim->caseRate->description }}</td>
        </tr>
        <tr>
            <th>Hospital Fee (HF)</th>
            <td class="text-right">&#x20B1;{{ number_format($claim->caseRate->hf_fee, 2) }}</td>
        </tr>
        <tr>
            <th>Professional Fee (PF)</th>
            <td class="text-right">&#x20B1;{{ number_format($claim->caseRate->pf_fee, 2) }}</td>
        </tr>
        <tr>
            <th>Total Case Rate</th>
            <td class="text-right fw-bold">&#x20B1;{{ number_format($claim->caseRate->total_rate, 2) }}</td>
        </tr>
    </table>
    @endif

    @if($claim->voucher_number || $claim->acknowledgment_code)
    <h4 style="margin:12px 0 4px;font-size:10pt;">PhilHealth Payment / Acknowledgment</h4>
    <table>
        @if($claim->acknowledgment_code)
        <tr>
            <th style="width:40%">Acknowledgment Code</th>
            <td>{{ $claim->acknowledgment_code }}</td>
            <th style="width:30%">Acknowledged Date</th>
            <td>{{ $claim->acknowledgment_date?->format('M d, Y') ?? '—' }}</td>
        </tr>
        @endif
        @if($claim->voucher_number)
        <tr>
            <th>Voucher No.</th>
            <td>{{ $claim->voucher_number }}</td>
            <th>Voucher Date</th>
            <td>{{ $claim->voucher_date?->format('M d, Y') ?? '—' }}</td>
        </tr>
        <tr>
            <th>Voucher Amount</th>
            <td colspan="3" class="fw-bold">&#x20B1;{{ number_format($claim->voucher_amount, 2) }}</td>
        </tr>
        @endif
    </table>
    @endif

    <div class="signature-row">
        <div class="signature-box">
            <div class="line">Prepared By</div>
        </div>
        <div class="signature-box">
            <div class="line">Date Printed: {{ now()->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    <p style="margin-top:15px;font-size:7pt;text-align:center;color:#666;">
        This is a computer-generated document. No signature is required.<br>
        {{ config('philhealth.system.name') }} v{{ config('philhealth.system.version') }} — eSOA
    </p>
</body>
</html>
