<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $payment->payment_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; padding: 40px; }
        .receipt-box { max-width: 600px; margin: 0 auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 12px; }
        .header { text-align: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #0d9488; }
        .header h1 { font-size: 24px; font-weight: 800; color: #0d9488; letter-spacing: -0.5px; }
        .header p { color: #64748b; font-size: 11px; margin-top: 4px; }
        .receipt-title { text-align: center; font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .info-group label { font-size: 9px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; display: block; margin-bottom: 2px; }
        .info-group p { font-size: 13px; font-weight: 500; color: #1e293b; }
        .info-group .muted { color: #64748b; font-size: 11px; font-weight: 400; font-family: monospace; }
        .amount-box { text-align: center; margin-bottom: 24px; padding: 20px; background: #f0fdfa; border: 2px solid #0d9488; border-radius: 12px; }
        .amount-box .label { font-size: 10px; font-weight: 600; color: #0d9488; text-transform: uppercase; letter-spacing: 1px; }
        .amount-box .amount { font-size: 32px; font-weight: 800; color: #0d9488; font-family: 'Cascadia Code', 'Fira Code', monospace; margin-top: 4px; }
        .amount-box .method { font-size: 11px; color: #64748b; margin-top: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        th { text-align: left; font-size: 9px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        td:last-child { text-align: right; font-family: 'Cascadia Code', 'Fira Code', monospace; }
        .footer { margin-top: 20px; padding-top: 12px; border-top: 1px solid #e2e8f0; text-align: center; color: #94a3b8; font-size: 10px; }
        .no-print { margin-top: 20px; text-align: center; }
        .no-print button { padding: 10px 28px; background: #0d9488; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .no-print button:hover { background: #0f766e; }
        @media print {
            body { padding: 20px; }
            .receipt-box { border: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @php $invoice = $payment->invoice; @endphp
    <div class="receipt-box">
        <div class="header">
            <h1>SITHEMR</h1>
            <p>Hospital Management System</p>
        </div>

        <div class="receipt-title">Official Receipt</div>

        <div class="info-grid">
            <div class="info-group">
                <label>Receipt No.</label>
                <p style="font-family:monospace">{{ $payment->payment_code }}</p>
            </div>
            <div class="info-group">
                <label>Date</label>
                <p>{{ $payment->payment_date?->format('F d, Y') }}</p>
            </div>
            <div class="info-group">
                <label>Patient</label>
                <p>{{ $invoice->patient?->last_name }}, {{ $invoice->patient?->first_name }}</p>
                <span class="muted">{{ $invoice->patient?->patient_code }}</span>
            </div>
            <div class="info-group">
                <label>Invoice</label>
                <p style="font-family:monospace">{{ $invoice->invoice_code }}</p>
            </div>
            @if($payment->reference_no)
            <div class="info-group">
                <label>Reference No.</label>
                <p>{{ $payment->reference_no }}</p>
            </div>
            @endif
            @if($payment->notes)
            <div class="info-group">
                <label>Notes</label>
                <p>{{ $payment->notes }}</p>
            </div>
            @endif
        </div>

        <div class="amount-box">
            <div class="label">Amount Paid</div>
            <div class="amount">₱{{ number_format($payment->amount, 2) }}</div>
            <div class="method">via {{ $payment->payment_method }}</div>
        </div>

        @if($invoice->items->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td><span style="font-size:10px;color:#64748b">[{{ $item->source_type }}]</span> {{ $item->description }}</td>
                    <td>₱{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <div style="margin-bottom:10px;">
            <table>
                <tr><td style="font-weight:600">Invoice Total</td><td style="text-align:right;font-family:monospace;white-space:nowrap;font-weight:600">₱{{ number_format((float) $invoice->total_amount, 2) }}</td></tr>
                @if($invoice->discount_amount > 0)
                <tr><td style="font-weight:600">Discount{{ $invoice->discount_type === 'percentage' ? ' (' . rtrim(rtrim(number_format((float) $invoice->discount_value, 2), '0'), '.') . '%)' : '' }}</td><td style="text-align:right;font-family:monospace;white-space:nowrap;color:#dc2626">-₱{{ number_format((float) $invoice->discount_amount, 2) }}</td></tr>
                <tr><td style="font-weight:600">Net Total</td><td style="text-align:right;font-family:monospace;white-space:nowrap;font-weight:600">₱{{ number_format((float) $invoice->total_amount - (float) $invoice->discount_amount, 2) }}</td></tr>
                @endif
                <tr><td style="font-weight:600">Total Paid</td><td style="text-align:right;font-family:monospace;white-space:nowrap;color:#0d9488">₱{{ number_format((float) $invoice->paid_amount, 2) }}</td></tr>
                <tr><td style="font-weight:700;border-top:2px solid #1e293b;padding-top:8px">Balance</td>
                    <td style="text-align:right;font-family:monospace;white-space:nowrap;font-weight:700;border-top:2px solid #1e293b;padding-top:8px;{{ $invoice->balance > 0 ? 'color:#dc2626' : 'color:#0d9488' }}">₱{{ number_format((float) $invoice->balance, 2) }}</td></tr>
            </table>
        </div>

        <div class="footer">
            <p>This is a computer-generated receipt. No signature required.</p>
            <p style="margin-top:2px">SITHEMR &mdash; Hospital Management System</p>
        </div>

        <div class="no-print">
            <button onclick="window.print()">&#128424; Print Receipt</button>
            <p style="margin-top:8px;color:#94a3b8;font-size:11px">
                <a href="{{ route('billing') }}" style="color:#0d9488">&#8592; Back to Billing</a>
            </p>
        </div>
    </div>
</body>
</html>
