<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Result Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1a202c; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #2b6cb0; }
        .header h1 { font-size: 18px; color: #2b6cb0; margin-bottom: 3px; }
        .header p { font-size: 11px; color: #4a5568; line-height: 1.5; }
        .header .report-title { font-size: 14px; font-weight: 700; color: #2d3748; margin-top: 5px; }
        .info-grid { display: flex; gap: 30px; margin-bottom: 15px; padding: 12px; background: #f7fafc; border-radius: 6px; border: 1px solid #e2e8f0; }
        .info-grid .col { flex: 1; }
        .info-grid .label { font-size: 9px; font-weight: 700; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-grid .value { font-size: 12px; font-weight: 600; color: #2d3748; margin-top: 1px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #ebf8ff; color: #2b6cb0; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 10px; text-align: left; border: 1px solid #bee3f8; }
        td { padding: 7px 10px; border: 1px solid #e2e8f0; font-size: 11px; }
        tr:nth-child(even) { background: #f7fafc; }
        .flag-H { color: #e53e3e; font-weight: 700; }
        .flag-L { color: #d69e2e; font-weight: 700; }
        .flag-N { color: #38a169; font-weight: 700; }
        .flag-Critical { color: #9b2c2c; font-weight: 700; }
        .flag-Abnormal { color: #e53e3e; font-weight: 700; }
        .signatures { display: flex; justify-content: space-between; margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; }
        .signatures .sig-block { text-align: center; flex: 1; }
        .signatures .sig-line { width: 80%; margin: 30px auto 5px; border-top: 1px solid #4a5568; }
        .signatures .sig-label { font-size: 9px; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; }
        .signatures .sig-name { font-size: 11px; font-weight: 600; color: #2d3748; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #a0aec0; }
        .no-print { margin-bottom: 15px; }
        .no-print button { padding: 8px 20px; background: #2b6cb0; color: #fff; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; }
        .no-print button:hover { background: #2c5282; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Print Report</button>
    </div>

    <div class="header">
        <h1>{{ $item->labRequest?->tenant?->name ?? 'HOSPITAL NAME' }}</h1>
        <p>{{ $item->labRequest?->tenant?->address ?? 'Address' }}<br>
        {{ $item->labRequest?->tenant?->contact_number ?? 'Contact Number' }}</p>
        <div class="report-title">Laboratory Result Report</div>
    </div>

    <div class="info-grid">
        <div class="col">
            <div class="label">Patient Name</div>
            <div class="value">{{ $item->labRequest?->patient_name ?? 'N/A' }}</div>
            <div class="label" style="margin-top:6px;">MRN</div>
            <div class="value">{{ $item->labRequest?->patient?->patient_code ?? 'N/A' }}</div>
        </div>
        <div class="col">
            <div class="label">Age / Gender</div>
            <div class="value">{{ $item->labRequest?->patient?->date_of_birth ? \Carbon\Carbon::parse($item->labRequest->patient->date_of_birth)->age . ' yrs' : 'N/A' }} / {{ $item->labRequest?->patient?->gender?->name ?? 'N/A' }}</div>
            <div class="label" style="margin-top:6px;">Date of Birth</div>
            <div class="value">{{ $item->labRequest?->patient?->date_of_birth ? \Carbon\Carbon::parse($item->labRequest->patient->date_of_birth)->format('M d, Y') : 'N/A' }}</div>
        </div>
        <div class="col">
            <div class="label">Requesting Physician</div>
            <div class="value">{{ $item->labRequest?->requestingDoctor?->full_name ?? 'N/A' }}</div>
            <div class="label" style="margin-top:6px;">Specimen / Result Date</div>
            <div class="value">{{ $item->result_date ? $item->result_date->format('M d, Y h:i A') : 'N/A' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Test Name</th>
                <th>Result</th>
                <th>Flag</th>
                <th>Reference Range</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight:600;">{{ $item->test_name }}</td>
                <td class="flag-{{ $item->flag ?? 'N' }}">{{ $item->result_value ?? $item->result_categorical ?? $item->result_text ?? '--' }}</td>
                <td class="flag-{{ $item->flag ?? 'N' }}">{{ $item->flag ?? '--' }}</td>
                <td>{{ $item->reference_range ?? '--' }}</td>
                <td>{{ $item->labTest?->labUnit?->name ?? '--' }}</td>
            </tr>
        </tbody>
    </table>

    @if($item->result_notes)
    <div style="margin-bottom:10px;padding:10px;background:#fffbeb;border-radius:4px;border:1px solid #fef3c7;">
        <div style="font-size:9px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:0.5px;">MedTech Notes</div>
        <div style="font-size:11px;color:#78350f;margin-top:2px;">{{ $item->result_notes }}</div>
    </div>
    @endif

    <div class="signatures">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Performed By</div>
            <div class="sig-name">{{ $item->performedBy?->full_name ?? '____________________' }}</div>
            <div style="font-size:9px;color:#718096;">RMT License: {{ $item->performedBy?->license_number ?? '__________' }}</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Verified By</div>
            <div class="sig-name">{{ $item->verifiedBy?->full_name ?? '____________________' }}</div>
            <div style="font-size:9px;color:#718096;">RMT License: {{ $item->verifiedBy?->license_number ?? '__________' }}</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Validated By</div>
            <div class="sig-name">{{ $item->validatedBy?->full_name ?? '____________________' }}</div>
            <div style="font-size:9px;color:#718096;">Pathologist</div>
        </div>
    </div>

    <div class="footer">
        This report is electronically generated and is valid without signature per DOH AO 2021-0037.
    </div>
</body>
</html>
