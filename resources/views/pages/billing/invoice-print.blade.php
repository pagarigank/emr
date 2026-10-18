<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; padding: 40px; }
        .invoice-box { max-width: 800px; margin: 0 auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 12px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #059669; }
        .header-left h1 { font-size: 26px; font-weight: 800; color: #059669; letter-spacing: -0.5px; }
        .header-left p { color: #64748b; font-size: 11px; margin-top: 4px; }
        .header-right { text-align: right; }
        .header-right h2 { font-size: 20px; font-weight: 700; color: #1e293b; }
        .header-right .status { display: inline-block; margin-top: 6px; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-Unpaid { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .status-Partial { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .status-Paid { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .status-Cancelled { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-group label { font-size: 9px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; display: block; margin-bottom: 3px; }
        .info-group p { font-size: 13px; font-weight: 500; color: #1e293b; }
        .info-group .muted { color: #64748b; font-size: 11px; font-weight: 400; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; table-layout: fixed; }
        thead th { background: #f1f5f9; padding: 10px 12px; text-align: left; font-size: 10px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
        thead th:last-child { text-align: right; }
        thead th:nth-child(3) { text-align: right; }
        thead th:nth-child(4) { text-align: right; }
        tbody td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
        tbody td.number, tbody td:last-child, tbody td:nth-child(3), tbody td:nth-child(4) { text-align: right; font-weight: 600; font-family: 'Cascadia Code', 'Fira Code', monospace; white-space: nowrap; }
        td.price { text-align: right; font-family: 'Cascadia Code', 'Fira Code', monospace; white-space: nowrap; font-weight: 600; }
        .source-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; }
        .source-Lab { background: #f3e8ff; color: #6b21a8; }
        .source-Pharmacy { background: #d1fae5; color: #065f46; }
        .source-CSR { background: #fef3c7; color: #92400e; }
        .source-Radiology { background: #e0e7ff; color: #3730a3; }
        .source-Procedure { background: #fce7f3; color: #9d174d; }
        .source-OECB { background: #e0f2fe; color: #0369a1; }
        .source-Admission { background: #ccfbf1; color: #0f766e; }
        .source-Manual, .source-Adjustment { background: #e0f2fe; color: #0369a1; }
        .group-header td { background: #f8fafc; padding: 10px 12px; font-size: 11px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
        .group-header td .group-count { font-weight: 400; color: #94a3b8; font-size: 10px; margin-left: 6px; }
        .group-subtotal td { background: #f1f5f9; padding: 8px 12px; font-size: 11px; font-weight: 600; color: #475569; border-bottom: 2px solid #e2e8f0; }
        .group-subtotal td:last-child { text-align: right; font-family: 'Cascadia Code', 'Fira Code', monospace; color: #1e293b; }
        .summary-row td { padding: 10px 12px; font-size: 12px; border-bottom: 1px solid #e2e8f0; }
        .summary-row td:last-child { text-align: right; font-family: 'Cascadia Code', 'Fira Code', monospace; font-weight: 600; }
        .summary-total td { padding: 10px 12px; font-size: 12px; font-weight: 700; background: #f8fafc; border-top: 2px solid #1e293b; }
        .summary-total td:last-child { text-align: right; font-family: 'Cascadia Code', 'Fira Code', monospace; color: #059669; }
        .totals { margin-top: 16px; margin-bottom: 30px; }
        .totals table { width: 280px; margin-left: auto; }
        .totals td { padding: 5px 12px; font-size: 12px; border: none; }
        .totals td:last-child { text-align: right; font-family: 'Cascadia Code', 'Fira Code', monospace; font-weight: 600; }
        .totals .total-row td { padding-top: 10px; border-top: 2px solid #1e293b; font-size: 14px; font-weight: 700; }
        .totals .total-row td:last-child { color: #059669; }
        .payments-section h3 { font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
        .footer { margin-top: 30px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 10px; }
        .no-print { margin-top: 20px; text-align: center; }
        .no-print button { padding: 10px 28px; background: #059669; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .no-print button:hover { background: #047857; }
        .mode-selector { display: flex; justify-content: center; gap: 0; margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; width: fit-content; margin-left: auto; margin-right: auto; }
        .mode-selector a { padding: 8px 24px; font-size: 12px; font-weight: 600; text-decoration: none; color: #64748b; background: #f8fafc; border-right: 1px solid #e2e8f0; transition: all 0.15s; cursor: pointer; }
        .mode-selector a:last-child { border-right: none; }
        .mode-selector a:hover { background: #f1f5f9; color: #334155; }
        .mode-selector a.active { background: #059669; color: #fff; }
        .mode-label { text-align: center; font-size: 10px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
        @media print {
            body { padding: 20px; }
            .invoice-box { border: none; padding: 0; }
            .no-print { display: none; }
            .header { border-bottom-color: #059669; }
        }
    </style>
</head>
<body>
    @php
        $mode = request('mode', 'detail');
        $manualItems = $invoice->manualCharges->where('history', 1)->map(fn($mc) => (object)[
            'source_type' => $mc->charge_type ?: 'Adjustment',
            'description' => $mc->description,
            'source_request_code' => null,
            'quantity' => $mc->quantity,
            'unit_price' => $mc->unit_price,
            'total_price' => $mc->total_price,
            'is_manual' => true,
        ]);
        $items = $invoice->items->toBase()->concat($manualItems);
        $grouped = $items->groupBy('source_type');
        $groupOrder = ['Lab', 'Pharmacy', 'CSR', 'Radiology', 'Procedure', 'OECB', 'Admission', 'Miscellaneous', 'Administrative', 'Supply', 'Professional Fee', 'Transport', 'Equipment', 'Room Differential', 'Others'];
        $sortedGroups = $grouped->sortBy(function($items, $key) use ($groupOrder) {
            $idx = array_search($key, $groupOrder);
            return $idx !== false ? $idx : 999;
        });
    @endphp

    <div class="invoice-box">
        {{-- Header --}}
        <div class="header">
            <div class="header-left">
                <h1>SITHEMR</h1>
                <p>Hospital Management System</p>
            </div>
            <div class="header-right">
                <h2>{{ $invoice->invoice_code }}</h2>
                <span class="status status-{{ $invoice->status }}">{{ $invoice->status }}</span>
            </div>
        </div>

        {{-- Mode Selector --}}
        <div class="no-print">
            <p class="mode-label">Print Mode</p>
            <div class="mode-selector">
                <a href="?mode=summary" class="{{ $mode === 'summary' ? 'active' : '' }}" onclick="switchMode('summary')">Summary</a>
                <a href="?mode=detail" class="{{ $mode === 'detail' ? 'active' : '' }}" onclick="switchMode('detail')">Detail</a>
            </div>
        </div>

        {{-- Info --}}
        <div class="info-grid">
            <div class="info-group">
                <label>Patient</label>
                <p>{{ $invoice->patient?->last_name }}, {{ $invoice->patient?->first_name }}</p>
                <span class="muted">{{ $invoice->patient?->patient_code }}</span>
            </div>
            <div class="info-group">
                <label>Invoice Date</label>
                <p>{{ $invoice->invoice_date?->format('F d, Y') }}</p>
            </div>
            @if($invoice->erAdmission)
            <div class="info-group">
                <label>ER Admission</label>
                <p>#{{ $invoice->er_admission_id }}</p>
            </div>
            @endif
            @if($invoice->notes)
            <div class="info-group">
                <label>Notes</label>
                <p>{{ $invoice->notes }}</p>
            </div>
            @endif
        </div>

        {{-- LINE ITEMS --}}
        @if($mode === 'detail')
            {{-- DETAIL MODE: Grouped by type, each item shown --}}
            <table>
                <thead>
                    <tr>
                        <th style="width:90px">Source</th>
                        <th>Description</th>
                        <th style="width:60px">Qty</th>
                        <th style="width:110px">Unit Price</th>
                        <th style="width:110px">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sortedGroups as $sourceType => $groupItems)
                    <tr class="group-header">
                        <td colspan="5">
                            <span class="source-badge source-{{ $sourceType }}">{{ $sourceType }}</span>
                            <span class="group-count">({{ $groupItems->count() }} item{{ $groupItems->count() > 1 ? 's' : '' }})</span>
                        </td>
                    </tr>
                    @foreach($groupItems as $item)
                    <tr>
                        <td></td>
                        <td>
                            {{ $item->description }}
                            @if($item->source_request_code)
                            <br><span style="font-size:10px;color:#94a3b8;font-family:monospace">{{ $item->source_request_code }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($item->quantity, $item->is_manual ?? false ? 2 : 0) }}</td>
                        <td class="price">₱{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="price">₱{{ number_format((float) $item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="group-subtotal">
                        <td colspan="4">{{ $sourceType }} Subtotal</td>
                        <td class="price">₱{{ number_format((float) $groupItems->sum('total_price'), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        @else
            {{-- SUMMARY MODE: Grouped by type, count + total only --}}
            <table>
                <thead>
                    <tr>
                        <th style="width:90px">Source</th>
                        <th>Charge Type</th>
                        <th style="width:60px">Items</th>
                        <th style="width:110px">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @foreach($sortedGroups as $sourceType => $groupItems)
                    @php
                        $groupTotal = $groupItems->sum('total_price');
                        $grandTotal += $groupTotal;
                    @endphp
                    <tr class="summary-row">
                        <td><span class="source-badge source-{{ $sourceType }}">{{ $sourceType }}</span></td>
                        <td style="font-weight:500;color:#334155">{{ $sourceType }} Charges</td>
                        <td style="text-align:center;font-family:monospace">{{ $groupItems->count() }}</td>
                        <td class="price">₱{{ number_format((float) $groupTotal, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="summary-total">
                        <td colspan="3" style="text-align:right;padding-right:12px">Total ({{ $items->count() }} items)</td>
                        <td class="price">₱{{ number_format((float) $grandTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        {{-- Totals --}}
        <div class="totals">
            <table>
                <tr><td>Subtotal</td><td class="price">₱{{ number_format((float) $invoice->total_amount, 2) }}</td></tr>
                @if($invoice->discount_amount > 0)
                <tr><td>Discount{{ $invoice->discount_type === 'percentage' ? ' (' . rtrim(rtrim(number_format((float) $invoice->discount_value, 2), '0'), '.') . '%)' : '' }}</td><td class="price" style="color:#dc2626">-₱{{ number_format((float) $invoice->discount_amount, 2) }}</td></tr>
                <tr><td style="font-weight:600">Net Total</td><td class="price" style="font-weight:600">₱{{ number_format((float) $invoice->total_amount - (float) $invoice->discount_amount, 2) }}</td></tr>
                @endif
                <tr><td>Total Paid</td><td class="price" style="color:#059669">₱{{ number_format((float) $invoice->paid_amount, 2) }}</td></tr>
                <tr class="total-row"><td>Balance Due</td><td class="price">₱{{ number_format((float) $invoice->balance, 2) }}</td></tr>
            </table>
        </div>

        {{-- Payments --}}
        @if($invoice->payments->count() > 0)
        <div class="payments-section">
            <h3>Payment History</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width:120px">Code</th>
                        <th style="width:100px">Date</th>
                        <th style="width:110px">Method</th>
                        <th>Reference</th>
                        <th style="width:120px">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $pay)
                    <tr>
                        <td style="font-family:monospace;font-size:11px">{{ $pay->payment_code }}</td>
                        <td>{{ $pay->payment_date?->format('Y-m-d') }}</td>
                        <td>{{ $pay->payment_method }}</td>
                        <td style="font-size:11px;color:#64748b">{{ $pay->reference_no ?? '—' }}</td>
                        <td class="price">₱{{ number_format((float) $pay->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>This is a computer-generated invoice. No signature required.</p>
            <p style="margin-top:2px">SITHEMR &mdash; Hospital Management System</p>
        </div>

        {{-- Print Button --}}
        <div class="no-print">
            <button onclick="window.print()">&#128424; Print Invoice</button>
            <p style="margin-top:8px;color:#94a3b8;font-size:11px">
                <a href="{{ route('billing') }}" style="color:#059669">&#8592; Back to Billing</a>
            </p>
        </div>
    </div>

    <script>
        function switchMode(mode) {
            event.preventDefault();
            var url = new URL(window.location);
            url.searchParams.set('mode', mode);
            window.location = url;
        }
    </script>
</body>
</html>
