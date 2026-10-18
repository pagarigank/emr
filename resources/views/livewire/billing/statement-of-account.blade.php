<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Account — {{ $patient->last_name }}, {{ $patient->first_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; padding: 24px; }
        @media print {
            @page { size: letter portrait; margin: 12mm; }
            body { padding: 0; background: #fff; }
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; font-size: 10px; vertical-align: top; }
        th { background: #f3f4f6; font-weight: 700; text-transform: uppercase; font-size: 8px; letter-spacing: 0.5px; color: #555; }
        .header-table { border: none; margin-bottom: 12px; }
        .header-table td { border: none; padding: 0; vertical-align: top; }
        .header-table .hospital-name { font-size: 20px; font-weight: 800; color: #065f46; }
        .header-table .hospital-info { font-size: 9px; color: #777; line-height: 1.5; }
        .header-table .doc-title { font-size: 16px; font-weight: 700; color: #065f46; text-align: right; text-transform: uppercase; letter-spacing: 1px; }
        .header-table .doc-date { font-size: 9px; color: #777; text-align: right; }
        .divider { border: none; border-top: 3px solid #065f46; margin: 8px 0 14px 0; }
        .patient-table { background: #f0fdf4; margin-bottom: 14px; }
        .patient-table td { border: 1px solid #bbf7d0; padding: 5px 10px; font-size: 10px; }
        .patient-table .label { font-size: 8px; text-transform: uppercase; color: #888; font-weight: 700; }
        .patient-table .value { font-size: 11px; font-weight: 600; color: #1a1a1a; }
        .summary-table { margin-bottom: 14px; }
        .summary-table td { border: 1px solid #ddd; padding: 8px 12px; text-align: center; }
        .summary-table .s-label { font-size: 8px; text-transform: uppercase; color: #888; font-weight: 700; }
        .summary-table .s-value { font-size: 16px; font-weight: 700; margin-top: 2px; }
        .s-charges { color: #dc2626; }
        .s-payments { color: #059669; }
        .s-balance { color: #d97706; }
        .ledger-table th { background: #f9fafb; font-size: 8px; }
        .ledger-table td { font-size: 10px; }
        .ledger-table .text-right { text-align: right; }
        .ledger-table .text-center { text-align: center; }
        .ledger-table .text-red { color: #dc2626; font-weight: 600; }
        .ledger-table .text-green { color: #059669; font-weight: 600; }
        .ledger-table .text-bold { font-weight: 700; }
        .ledger-table tfoot td { background: #f9fafb; font-weight: 700; border-top: 2px solid #065f46; }
        .source-group { margin-bottom: 14px; page-break-inside: avoid; }
        .source-header-table { margin-bottom: 0; }
        .source-header-table td { background: #065f46; color: #fff; padding: 6px 10px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .source-header-table .source-total { text-align: right; font-size: 11px; }
        .source-items th { background: #f0fdf4; font-size: 8px; }
        .source-items td { font-size: 10px; }
        .source-items tfoot td { background: #f0fdf4; font-weight: 700; border-top: 2px solid #065f46; }
        .footer-table { border: none; margin-top: 24px; }
        .footer-table td { border: none; padding: 0; vertical-align: bottom; }
        .footer-table .note { font-size: 8px; color: #aaa; }
        .footer-table .sig-line { border-top: 1px solid #333; width: 180px; margin-top: 36px; padding-top: 4px; font-size: 9px; color: #666; text-align: center; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td style="width:60%">
                <div class="hospital-name">{{ $tenant->tenant_name }}</div>
                <div class="hospital-info">
                    {{ $tenant->address }}{{ $tenant->city ? ', ' . $tenant->city : '' }}<br>
                    @if($tenant->contact_number)Tel: {{ $tenant->contact_number }}<br>@endif
                </div>
            </td>
            <td style="width:40%">
                <div class="doc-title">Statement of Account</div>
                <div class="doc-date">Printed: {{ $printedAt }}</div>
            </td>
        </tr>
    </table>
    <hr class="divider">

    {{-- PATIENT INFO --}}
    <table class="patient-table">
        <tr>
            <td style="width:40%">
                <div class="label">Patient Name</div>
                <div class="value">{{ $patient->last_name }}, {{ $patient->first_name }} {{ $patient->middle_name ?? '' }}</div>
            </td>
            <td style="width:30%">
                <div class="label">Patient Code</div>
                <div class="value">{{ $patient->patient_code ?? '—' }}</div>
            </td>
            <td style="width:30%">
                <div class="label">Contact</div>
                <div class="value">{{ $patient->contact_number ?? $patient->phone ?? '—' }}</div>
            </td>
        </tr>
        @if(!empty($activeAdmission))
        <tr>
            <td>
                <div class="label">Inpatient Encounter &bull; Room / Bed</div>
                <div class="value">{{ $activeAdmission->admission_code }} &mdash; {{ $roomName ?? 'Ward' }}</div>
            </td>
            <td>
                <div class="label">Admission Date</div>
                <div class="value">{{ $activeAdmission->admission_date?->format('M d, Y h:i A') ?? '—' }}</div>
            </td>
            <td>
                <div class="label">Days Stayed (to date)</div>
                <div class="value" style="color: #065f46; font-weight: 700;">{{ $daysStayed ?? $activeAdmission->days_stayed }} {{ ($daysStayed ?? $activeAdmission->days_stayed) === 1 ? 'day' : 'days' }} (to date)</div>
            </td>
        </tr>
        @endif
    </table>

    {{-- SUMMARY CARDS --}}
    <table class="summary-table">
        <tr>
            <td style="width:33%">
                <div class="s-label">Total Charges</div>
                <div class="s-value s-charges">PHP {{ number_format($totalCharges, 2) }}</div>
            </td>
            <td style="width:33%">
                <div class="s-label">Total Payments</div>
                <div class="s-value s-payments">PHP {{ number_format($totalPayments, 2) }}</div>
            </td>
            <td style="width:34%">
                <div class="s-label">Outstanding Balance</div>
                <div class="s-value s-balance">PHP {{ number_format($balance, 2) }}</div>
            </td>
        </tr>
    </table>

    @if(!empty($activeAdmission) && ($accruedRoomCharges ?? 0) > 0)
    <div style="background: #f0fdf4; border: 1.5px solid #059669; padding: 6px 10px; margin-bottom: 14px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; padding: 0;">
                    <strong style="color: #065f46; font-size: 10px;">Active Inpatient Stay — Accrued Room &amp; Board (Unbilled to Date):</strong><br>
                    <span style="color: #334155; font-size: 9px;">{{ $roomName }} &bull; {{ $daysStayed }} day(s) stayed to date @ {{ $roomRate }}</span>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: middle;">
                    <span style="font-size: 7.5px; text-transform: uppercase; color: #065f46; font-weight: bold; display: block;">Accrued Room Total</span>
                    <strong style="font-size: 12px; color: #065f46;">PHP {{ number_format($accruedRoomCharges, 2) }}</strong>
                </td>
            </tr>
        </table>
    </div>
    @endif

    @if($mode === 'summary')
        {{-- SUMMARY MODE --}}
        @forelse($summary as $group)
            <div class="source-group">
                <table class="source-header-table" style="width:100%">
                    <tr>
                        <td>{{ $group['source_type'] }}</td>
                        <td class="source-total">PHP {{ number_format($group['total'], 2) }} &nbsp;({{ $group['count'] }} {{ $group['count'] === 1 ? 'entry' : 'entries' }})</td>
                    </tr>
                </table>
                <table class="ledger-table source-items" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:15%">Date</th>
                            <th style="width:20%">Reference</th>
                            <th>Description</th>
                            <th style="width:15%" class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group['items'] as $item)
                        <tr>
                            <td>{{ $item['date'] }}</td>
                            <td>{{ $item['source_code'] }}</td>
                            <td>{{ $item['description'] }}</td>
                            <td class="text-right text-red">PHP {{ number_format($item['debit'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">Subtotal — {{ $group['source_type'] }}</td>
                            <td class="text-right text-red">PHP {{ number_format($group['total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @empty
            <p style="text-align:center; color:#aaa; padding:20px;">No charge entries found.</p>
        @endforelse

    @else
        {{-- DETAILED MODE --}}
        <table class="ledger-table" style="width:100%">
            <thead>
                <tr>
                    <th style="width:12%">Date</th>
                    <th style="width:9%">Type</th>
                    <th style="width:11%">Source</th>
                    <th style="width:14%">Reference</th>
                    <th>Description</th>
                    <th style="width:11%" class="text-right">Debit</th>
                    <th style="width:11%" class="text-right">Credit</th>
                    <th style="width:12%" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ledger as $entry)
                <tr>
                    <td>{{ $entry['date'] }}</td>
                    <td>
                        @if($entry['entry_type'] === 'charge')
                            <span class="text-red">Charge</span>
                        @elseif($entry['entry_type'] === 'payment')
                            <span class="text-green">Payment</span>
                        @else
                            {{ ucfirst($entry['entry_type']) }}
                        @endif
                    </td>
                    <td>{{ $entry['source_type'] }}</td>
                    <td>{{ $entry['source_code'] }}</td>
                    <td>{{ $entry['description'] }}</td>
                    <td class="text-right {{ $entry['debit'] > 0 ? 'text-red' : '' }}">
                        {{ $entry['debit'] > 0 ? 'PHP ' . number_format($entry['debit'], 2) : '—' }}
                    </td>
                    <td class="text-right {{ $entry['credit'] > 0 ? 'text-green' : '' }}">
                        {{ $entry['credit'] > 0 ? 'PHP ' . number_format($entry['credit'], 2) : '—' }}
                    </td>
                    <td class="text-right text-bold">PHP {{ number_format($entry['balance'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">Totals</td>
                    <td class="text-right text-red">PHP {{ number_format($totalCharges, 2) }}</td>
                    <td class="text-right text-green">PHP {{ number_format($totalPayments, 2) }}</td>
                    <td class="text-right text-bold">PHP {{ number_format($balance, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- FOOTER --}}
    <table class="footer-table" style="width:100%">
        <tr>
            <td style="width:60%">
                <div class="note">This statement is computer-generated and is valid without signature.<br>For questions or concerns, please contact the billing department.</div>
            </td>
            <td style="width:40%; text-align:center;">
                <div class="sig-line">Authorized Signature</div>
            </td>
        </tr>
    </table>

</body>
</html>
