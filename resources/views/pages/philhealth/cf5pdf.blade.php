<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CF5/DRG — {{ $claim->claim_code }}</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: 'Courier New', monospace; font-size: 10pt; color: #000; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 14pt; margin: 0 0 4px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 9pt; }
        .title { text-align: center; font-size: 12pt; font-weight: bold; margin: 15px 0; text-transform: uppercase; }
        .subtitle { text-align: center; font-size: 9pt; margin: 4px 0 12px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 9pt; }
        th, td { border: 1px solid #000; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; font-size: 8pt; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .mt-20 { margin-top: 20px; }
        .signature-row { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 45%; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 30px; padding-top: 4px; font-size: 8pt; }
        .bg-yellow { background-color: #fff8dc; }
        .bg-green { background-color: #f0fff0; }
        .bg-red { background-color: #fff0f0; }
        .comparison-row td:last-child { font-weight: bold; }
        .positive { color: #166534; font-weight: bold; }
        .negative { color: #991b1b; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('philhealth.hospital.name') }}</h1>
        <p>TIN: {{ config('philhealth.hospital.tin') }} | Contact: {{ config('philhealth.hospital.contact_no') }}</p>
        <p>{{ config('philhealth.hospital.email') }}</p>
    </div>

    <div class="title">CF5 — Z-Benefit Claim Form</div>
    <div class="subtitle">Summary of Z-Benefit Tranches & Claims</div>

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
            <th>ICD-10 Code:</th>
            <td>{{ $claim->icd10Code?->code ?? '—' }}</td>
            <th>ICD-10 Description:</th>
            <td>{{ $claim->icd10Code?->name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Case Rate Code:</th>
            <td>{{ $claim->caseRate?->rvs_code ?? '—' }}</td>
            <th>Case Rate Description:</th>
            <td>{{ $claim->caseRate?->description ?? '—' }}</td>
        </tr>
        <tr>
            <th>Status:</th>
            <td>{{ $claim->status }}</td>
            <th>Claim Type:</th>
            <td>{{ $claim->claim_type }}</td>
        </tr>
    </table>

    @php
        $zBenefits = $claim->zBenefits;
    @endphp

    @if($zBenefits && $zBenefits->count() > 0)
    <h4 style="margin:16px 0 4px;font-size:10pt;">Z-Benefit Package Claim Details</h4>
    <table>
        <tr>
            <th colspan="5" class="bg-yellow" style="font-size:9pt;">Z-Benefits (CF5) Summary</th>
        </tr>
        <tr>
            <th style="width:20%">Z-Benefit Code</th>
            <th style="width:25%">Pre-Auth Code</th>
            <th style="width:15%" class="text-center">Tranche #</th>
            <th style="width:20%" class="text-right">Tranche Amount</th>
            <th style="width:20%" class="text-center">Status</th>
        </tr>
        @foreach($zBenefits as $zb)
        <tr>
            <td class="fw-bold text-center">{{ $zb->z_benefit_code }}</td>
            <td class="text-center">{{ $zb->pre_authorization_code ?? '—' }}</td>
            <td class="text-center fw-bold">{{ $zb->tranche_number }}</td>
            <td class="text-right fw-bold">&#x20B1;{{ number_format($zb->tranche_amount, 2) }}</td>
            <td class="text-center">Billed</td>
        </tr>
        @endforeach
    </table>
    @else
    <div class="text-center mt-20">
        <p style="font-size:10pt;color:#666;">No Z-Benefit data has been recorded for this claim.</p>
        <p style="font-size:9pt;color:#999;">Use the CF5 / Z-Benefits section in the claim editor to add tranches.</p>
    </div>
    @endif

    @if($claim->items->count() > 0)
    <h4 style="margin:16px 0 4px;font-size:10pt;">Claim Items Summary</h4>
    <table>
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:12%">Type</th>
                <th style="width:10%">Code</th>
                <th>Description</th>
                <th style="width:8%">Qty</th>
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
                <td class="text-right">&#x20B1;{{ number_format($item->actual_amount, 2) }}</td>
                <td class="text-right">&#x20B1;{{ number_format($item->claimed_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="5" class="text-right">Totals:</td>
                <td class="text-right">&#x20B1;{{ number_format($claim->total_actual_amount, 2) }}</td>
                <td class="text-right">&#x20B1;{{ number_format($claim->total_claimed_amount, 2) }}</td>
            </tr>
        </tfoot>
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
        {{ config('philhealth.system.name') }} v{{ config('philhealth.system.version') }} — CF5 Z-Benefit Claim Form
    </p>
</body>
</html>
