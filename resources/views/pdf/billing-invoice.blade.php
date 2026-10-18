<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing Statement & Invoice — {{ $invoice->invoice_code }}</title>
    <style>
        @page {
            margin: 14mm 16mm 14mm 16mm;
            size: letter portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.35;
            background: #ffffff;
        }

        .invoice-box {
            border: 1.5px solid #059669;
            padding: 18px 22px;
            position: relative;
        }

        /* Hospital Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #059669;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .hosp-title {
            font-size: 14pt;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .hosp-sub {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }
        .doc-badge {
            text-align: right;
            font-size: 12pt;
            font-weight: bold;
            color: #065f46;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Demographics */
        table.meta-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            margin-bottom: 12px;
        }
        table.meta-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 8px;
            font-size: 8pt;
        }
        .m-label {
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
        }
        .m-val {
            font-weight: 600;
            color: #0f172a;
        }

        /* Line Items */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        table.items-table th, table.items-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 8pt;
        }
        table.items-table th {
            background: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            color: #475569;
        }
        .group-header td {
            background: #ecfdf5;
            color: #065f46;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
        }

        /* Totals */
        .totals-table {
            width: 280px;
            border-collapse: collapse;
            float: right;
            margin-bottom: 16px;
        }
        .totals-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 8px;
            font-size: 8.5pt;
        }
        .total-highlight {
            background: #065f46;
            color: #ffffff;
            font-weight: bold;
            font-size: 9.5pt;
        }

        /* Clearfix */
        .clearfix {
            clear: both;
        }

        /* Payments Table */
        table.payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }
        table.payments-table th, table.payments-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 7.5pt;
        }
        table.payments-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6.5pt;
        }

        /* Signatures */
        .sig-container {
            margin-top: 18px;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sig-table td {
            border: none;
            padding: 0;
            vertical-align: bottom;
        }
        .sig-box {
            width: 220px;
            text-align: center;
        }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            margin-top: 36px;
            padding-top: 3px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        {{-- Hospital Header --}}
        <table class="header-table">
            <tr>
                <td style="width: 65%;">
                    <div class="hosp-title">{{ $tenant->tenant_name ?? 'SITHEMR DEMO HOSPITAL' }}</div>
                    <div class="hosp-sub">
                        {{ $tenant->address ?? '123 Health Avenue, Manila' }}{{ !empty($tenant->city) ? ', ' . $tenant->city : '' }}<br>
                        Tel: {{ $tenant->contact_number ?? '(02) 8888-0001' }} &nbsp;|&nbsp; TIN: 000-123-456-000<br>
                        Billing & Cashiering Services
                    </div>
                </td>
                <td style="width: 35%;">
                    <div class="doc-badge">Billing Invoice</div>
                    <div style="font-size: 8pt; color: #475569; text-align: right; margin-top: 3px;">
                        Invoice No.: <strong style="font-family: monospace; color: #065f46;">{{ $invoice->invoice_code }}</strong><br>
                        Date: <strong>{{ \Carbon\Carbon::parse($invoice->invoice_date ?? $invoice->created_date)->format('M d, Y') }}</strong><br>
                        Status: <strong style="color: {{ $invoice->status === 'Paid' ? '#059669' : '#dc2626' }};">{{ strtoupper($invoice->status) }}</strong>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Meta Table --}}
        <table class="meta-table">
            <tr>
                <td style="width: 40%;">
                    <span class="m-label">Patient Name</span>
                    <span class="m-val">{{ $invoice->patient?->last_name }}, {{ $invoice->patient?->first_name }} {{ $invoice->patient?->middle_name ?? '' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="m-label">Patient Code</span>
                    <span class="m-val" style="font-family: monospace;">{{ $invoice->patient?->patient_code ?? '—' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="m-label">Age / Gender</span>
                    <span class="m-val">{{ $invoice->patient?->date_of_birth ? $invoice->patient?->date_of_birth->age . ' y/o' : '—' }} / {{ $invoice->patient?->gender?->name ?? '—' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="m-label">PhilHealth PIN</span>
                    <span class="m-val">{{ $invoice->patient?->philhealth_pin ?? 'None' }}</span>
                </td>
            </tr>
        </table>

        {{-- Line Items Grouped --}}
        @php
            $manualPdfItems = ($invoice->manualCharges ?? collect())->where('history', 1)->map(fn($mc) => (object)[
                'item_type' => $mc->charge_type ?: 'Adjustment',
                'description' => $mc->description,
                'unit_price' => $mc->unit_price,
                'quantity' => $mc->quantity,
                'total_price' => $mc->total_price,
            ]);
            $allPdfItems = $invoice->items->toBase()->concat($manualPdfItems);
            $groupedItems = $allPdfItems->groupBy(fn($i) => $i->item_type ?? 'Miscellaneous');
        @endphp

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Item Description / Service</th>
                    <th style="width: 15%; text-align: right;">Unit Price</th>
                    <th style="width: 15%; text-align: right;">Quantity</th>
                    <th style="width: 25%; text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedItems as $type => $items)
                    <tr class="group-header">
                        <td colspan="4">{{ $type }} ({{ count($items) }} items)</td>
                    </tr>
                    @foreach($items as $it)
                        <tr>
                            <td>{{ $it->description ?? $it->item_name }}</td>
                            <td style="text-align: right;">₱{{ number_format((float)($it->unit_price ?? 0), 2) }}</td>
                            <td style="text-align: right;">{{ (int)$it->quantity ?: $it->quantity }}</td>
                            <td style="text-align: right; font-weight: 600;">₱{{ number_format((float)($it->total_price ?? $it->total_amount ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 12px;">No items charged on this invoice.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Totals Summary --}}
        <table class="totals-table">
            <tr>
                <td style="font-weight: bold; color: #475569;">Total Gross Charges:</td>
                <td style="text-align: right; font-weight: bold;">₱{{ number_format((float)($invoice->total_amount ?? 0), 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
                <tr>
                    <td style="color: #059669;">Discounts / PhilHealth:</td>
                    <td style="text-align: right; color: #059669;">-₱{{ number_format((float)($invoice->discount_amount ?? 0), 2) }}</td>
                </tr>
            @endif
            <tr>
                <td style="font-weight: bold; color: #059669;">Total Payments:</td>
                <td style="text-align: right; font-weight: bold; color: #059669;">₱{{ number_format((float)($invoice->paid_amount ?? 0), 2) }}</td>
            </tr>
            <tr class="total-highlight">
                <td>Net Balance Due:</td>
                <td style="text-align: right;">₱{{ number_format((float)max(0, ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0) - ($invoice->discount_amount ?? 0)), 2) }}</td>
            </tr>
        </table>
        <div class="clearfix"></div>

        {{-- Payments List if Any --}}
        @if($invoice->payments && count($invoice->payments) > 0)
            <div style="font-weight: bold; font-size: 7.5pt; text-transform: uppercase; color: #475569; margin-top: 8px;">
                Payments Received:
            </div>
            <table class="payments-table">
                <thead>
                    <tr>
                        <th>Receipt No.</th>
                        <th>Payment Date</th>
                        <th>Payment Method</th>
                        <th>Reference</th>
                        <th style="text-align: right;">Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $pmt)
                        <tr>
                            <td style="font-family: monospace;">{{ $pmt->payment_code }}</td>
                            <td>{{ \Carbon\Carbon::parse($pmt->payment_date)->format('M d, Y h:i A') }}</td>
                            <td>{{ $pmt->payment_method }}</td>
                            <td>{{ $pmt->reference_no ?? '—' }}</td>
                            <td style="text-align: right; font-weight: bold; color: #059669;">₱{{ number_format($pmt->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Signatures --}}
        <div class="sig-container">
            <table class="sig-table">
                <tr>
                    <td style="width: 50%;">
                        <div style="font-size: 7pt; color: #64748b;">
                            Official Hospital Billing Statement.<br>
                            Generated at: {{ now()->format('M d, Y h:i A') }}
                        </div>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <div class="sig-box" style="float: right;">
                            <div class="sig-line">
                                <div style="font-size: 8.5pt; font-weight: bold; color: #0f172a;">Billing Officer / Cashier</div>
                                <div style="font-size: 7pt; color: #64748b;">Authorized Hospital Representative</div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
