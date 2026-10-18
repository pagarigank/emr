<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encounter Medications Report — {{ $request->request_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1a202c; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #059669; }
        .header h1 { font-size: 18px; color: #059669; margin-bottom: 3px; }
        .header p { font-size: 11px; color: #4a5568; line-height: 1.5; }
        .header .report-title { font-size: 14px; font-weight: 700; color: #2d3748; margin-top: 5px; }
        .info-grid { display: flex; gap: 30px; margin-bottom: 15px; padding: 12px; background: #f0fdf4; border-radius: 6px; border: 1px solid #bbf7d0; }
        .info-grid .col { flex: 1; }
        .info-grid .label { font-size: 9px; font-weight: 700; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-grid .value { font-size: 12px; font-weight: 600; color: #2d3748; margin-top: 1px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #d1fae5; color: #065f46; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 10px; text-align: left; border: 1px solid #a7f3d0; }
        td { padding: 7px 10px; border: 1px solid #e2e8f0; font-size: 11px; }
        tr:nth-child(even) { background: #f0fdf4; }
        .status-complete { color: #059669; font-weight: 700; }
        .status-partial { color: #d97706; font-weight: 700; }
        .status-pending { color: #6b7280; font-weight: 700; }
        .status-cancelled { color: #dc2626; font-weight: 700; text-decoration: line-through; }
        .total-row { background: #ecfdf5 !important; font-weight: 700; }
        .total-row td { border-top: 2px solid #059669; }
        .request-group { margin-bottom: 20px; page-break-inside: avoid; }
        .request-group-header { display: flex; gap: 15px; padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; margin-bottom: 8px; font-size: 11px; }
        .request-group-header .rg-label { font-size: 9px; font-weight: 700; color: #718096; text-transform: uppercase; }
        .request-group-header .rg-value { font-weight: 600; color: #2d3748; }
        .signatures { display: flex; justify-content: space-between; margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; }
        .signatures .sig-block { text-align: center; flex: 1; }
        .signatures .sig-line { width: 80%; margin: 30px auto 5px; border-top: 1px solid #4a5568; }
        .signatures .sig-label { font-size: 9px; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; }
        .signatures .sig-name { font-size: 11px; font-weight: 600; color: #2d3748; }
        .footer { text-align: center; margin-top: 20px; font-size: 9px; color: #a0aec0; }
        .no-print { margin-bottom: 15px; }
        .no-print button { padding: 8px 20px; background: #059669; color: #fff; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; }
        .no-print button:hover { background: #047857; }
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
        <h1>{{ $tenant->name ?? 'HOSPITAL NAME' }}</h1>
        <p>{{ $tenant->address ?? 'Address' }}<br>
        {{ $tenant->contact_number ?? 'Contact Number' }}</p>
        <div class="report-title">Encounter Medications Report</div>
    </div>

    <div class="info-grid">
        <div class="col">
            <div class="label">Patient Name</div>
            <div class="value">{{ $request->patient_name ?? 'N/A' }}</div>
            <div class="label" style="margin-top:6px;">MRN</div>
            <div class="value">{{ $request->patient?->patient_code ?? 'N/A' }}</div>
        </div>
        <div class="col">
            <div class="label">Age / Gender</div>
            <div class="value">{{ $request->patient?->date_of_birth ? \Carbon\Carbon::parse($request->patient->date_of_birth)->age . ' yrs' : 'N/A' }} / {{ $request->patient?->gender?->name ?? 'N/A' }}</div>
            <div class="label" style="margin-top:6px;">Date of Birth</div>
            <div class="value">{{ $request->patient?->date_of_birth ? \Carbon\Carbon::parse($request->patient->date_of_birth)->format('M d, Y') : 'N/A' }}</div>
        </div>
        <div class="col">
            <div class="label">Requesting Doctor</div>
            <div class="value">{{ $request->requestingDoctor?->full_name ?? 'N/A' }}</div>
            <div class="label" style="margin-top:6px;">Department</div>
            <div class="value">{{ $request->requestingDepartment?->dept_name ?? 'N/A' }}</div>
        </div>
        <div class="col">
            <div class="label">Total Requests in Encounter</div>
            <div class="value">{{ $encounterRequests->count() }}</div>
            <div class="label" style="margin-top:6px;">Primary Request Code</div>
            <div class="value">{{ $request->request_code }}</div>
        </div>
    </div>

    @foreach($encounterRequests as $req)
    <div class="request-group">
        <div class="request-group-header">
            <div>
                <div class="rg-label">Request Code</div>
                <div class="rg-value">{{ $req->request_code }}</div>
            </div>
            <div>
                <div class="rg-label">Status</div>
                <div class="rg-value">{{ $req->status }}</div>
            </div>
            <div>
                <div class="rg-label">Billing</div>
                <div class="rg-value">{{ $req->billing_status }}</div>
            </div>
            <div>
                <div class="rg-label">Priority</div>
                <div class="rg-value">{{ $req->priority }}</div>
            </div>
            <div>
                <div class="rg-label">Department</div>
                <div class="rg-value">{{ $req->requestingDepartment?->dept_name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="rg-label">Date</div>
                <div class="rg-value">{{ $req->created_date?->format('M d, Y h:i A') ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="rg-label">Requesting Doctor</div>
                <div class="rg-value">{{ $req->requestingDoctor?->full_name ?? 'N/A' }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:25%">Medication</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Route</th>
                    <th>Duration</th>
                    <th style="text-align:right">Qty Ordered</th>
                    <th style="text-align:right">Qty Given</th>
                    <th>Batch No</th>
                    <th style="text-align:right">Unit Cost</th>
                    <th style="text-align:right">Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($req->items as $item)
                @php $statusClass = match($item->status) { 'Complete' => 'status-complete', 'Partial' => 'status-partial', 'Cancelled' => 'status-cancelled', default => 'status-pending' }; @endphp
                <tr>
                    <td style="font-weight:600;">
                        {{ $item->item_name }}
                        @if($item->drug?->drug_name && $item->drug->drug_name !== $item->item_name)
                            <br><span style="font-size:10px;color:#6b7280;font-weight:400;">{{ $item->drug->drug_name }}</span>
                        @endif
                    </td>
                    <td>{{ $item->dosage ?? '—' }}</td>
                    <td>{{ $item->frequency ?? '—' }}</td>
                    <td>{{ $item->route ?? '—' }}</td>
                    <td>{{ $item->duration ?? '—' }}</td>
                    <td style="text-align:right;font-family:monospace;">{{ $item->quantity }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:600;">{{ $item->given_quantity }}</td>
                    <td style="font-family:monospace;font-size:10px;">{{ $item->drugBatch?->batch_no ?? '—' }}</td>
                    <td style="text-align:right;font-family:monospace;">&#8369;{{ number_format($item->unit_cost, 2) }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:600;">&#8369;{{ number_format($item->total_cost, 2) }}</td>
                    <td class="{{ $statusClass }}">{{ $item->status }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align:center;color:#9ca3af;padding:20px;">No medication items found.</td>
                </tr>
                @endforelse
                @if($req->items->count() > 0)
                <tr class="total-row">
                    <td colspan="5" style="text-align:right;font-weight:700;">Subtotal</td>
                    <td style="text-align:right;font-family:monospace;font-weight:700;">{{ $req->items->sum('quantity') }}</td>
                    <td style="text-align:right;font-family:monospace;font-weight:700;">{{ $req->items->sum('given_quantity') }}</td>
                    <td></td>
                    <td></td>
                    <td style="text-align:right;font-family:monospace;font-weight:700;">&#8369;{{ number_format($req->items->sum('total_cost'), 2) }}</td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>
        @if($req->notes)
        <div style="margin-bottom:10px;padding:8px;background:#fffbeb;border-radius:4px;border:1px solid #fef3c7;">
            <span style="font-size:9px;font-weight:700;color:#92400e;text-transform:uppercase;">Notes:</span>
            <span style="font-size:11px;color:#78350f;">{{ $req->notes }}</span>
        </div>
        @endif
    </div>
    @endforeach

    {{-- Grand Total --}}
    @php
        $allItems = $encounterRequests->flatMap(fn($r) => $r->items);
        $grandQty = $allItems->sum('quantity');
        $grandGiven = $allItems->sum('given_quantity');
        $grandTotal = $allItems->sum('total_cost');
    @endphp
    @if($encounterRequests->count() > 1)
    <table>
        <thead>
            <tr>
                <th style="width:25%">GRAND TOTAL</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Route</th>
                <th>Duration</th>
                <th style="text-align:right">Qty Ordered</th>
                <th style="text-align:right">Qty Given</th>
                <th>Batch No</th>
                <th style="text-align:right">Unit Cost</th>
                <th style="text-align:right">Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr class="total-row">
                <td style="font-weight:700;">{{ $encounterRequests->count() }} requests</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align:right;font-family:monospace;font-weight:700;">{{ $grandQty }}</td>
                <td style="text-align:right;font-family:monospace;font-weight:700;">{{ $grandGiven }}</td>
                <td></td>
                <td></td>
                <td style="text-align:right;font-family:monospace;font-weight:700;">&#8369;{{ number_format($grandTotal, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="signatures">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Dispensed By</div>
            <div class="sig-name">____________________</div>
            <div style="font-size:9px;color:#718096;">Pharmacist</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Received By</div>
            <div class="sig-name">{{ $request->patient_name ?? '____________________' }}</div>
            <div style="font-size:9px;color:#718096;">Patient / Authorized Representative</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Verified By</div>
            <div class="sig-name">____________________</div>
            <div style="font-size:9px;color:#718096;">Head Pharmacist</div>
        </div>
    </div>

    <div class="footer">
        This report is electronically generated. Printed on {{ now()->format('M d, Y h:i A') }}.
    </div>
</body>
</html>
