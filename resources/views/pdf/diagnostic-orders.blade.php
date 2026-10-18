<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Diagnostic Orders — {{ $consultation->consultation_code }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
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

        .order-container {
            border: 1.5px solid #334155;
            padding: 16px 20px;
            position: relative;
        }

        /* Hospital Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #334155;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .hosp-title {
            font-size: 13pt;
            font-weight: bold;
            color: #1e293b;
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
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Title */
        .title-bar {
            text-align: center;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px;
            margin: 6px 0 10px 0;
        }
        .doc-heading {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 3.5px 6px;
            font-size: 8pt;
            vertical-align: top;
        }
        .f-label {
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
        }
        .f-val {
            font-weight: 600;
            color: #0f172a;
        }

        /* Section Headers */
        .sec-head {
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 8px;
            color: #ffffff;
            margin-top: 10px;
            margin-bottom: 2px;
        }
        .sec-lab { background: #6b21a8; }
        .sec-rad { background: #1e40af; }
        .sec-med { background: #065f46; }
        .sec-csr { background: #92400e; }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.items-table th, table.items-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 8pt;
        }
        table.items-table th {
            background: #f8fafc;
            font-weight: bold;
            text-align: left;
            font-size: 7pt;
            text-transform: uppercase;
            color: #475569;
        }

        /* Signatures */
        .sig-container {
            margin-top: 16px;
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
    <div class="order-container">
        {{-- Hospital Header --}}
        <table class="header-table">
            <tr>
                <td style="width: 70%;">
                    <div class="hosp-title">{{ $tenant->tenant_name ?? 'SITHEMR DEMO HOSPITAL' }}</div>
                    <div class="hosp-sub">
                        {{ $tenant->address ?? '123 Health Avenue, Manila' }}{{ !empty($tenant->city) ? ', ' . $tenant->city : '' }}<br>
                        Tel: {{ $tenant->contact_number ?? '(02) 8888-0001' }} &nbsp;|&nbsp; OPD Clinic: {{ $consultation->opdClinic?->name ?? 'General Outpatient Clinic' }}
                    </div>
                </td>
                <td style="width: 30%;">
                    <div class="doc-badge">Diagnostic Order</div>
                    <div style="font-size: 7.5pt; color: #64748b; text-align: right; margin-top: 2px;">
                        Date: <strong>{{ \Carbon\Carbon::parse($consultation->consultation_datetime ?? now())->format('M d, Y') }}</strong>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Title Bar --}}
        <div class="title-bar">
            <div class="doc-heading">Diagnostic & Clinical Order Requisition</div>
        </div>

        {{-- Patient Info --}}
        <table class="data-table">
            <tr>
                <td style="width: 35%;">
                    <span class="f-label">Patient Name</span>
                    <span class="f-val">{{ $patient->last_name }}, {{ $patient->first_name }} {{ $patient->middle_name ?? '' }} {{ $patient->suffix ?? '' }}</span>
                </td>
                <td style="width: 15%;">
                    <span class="f-label">Age / Gender</span>
                    <span class="f-val">{{ $patient->date_of_birth ? $patient->date_of_birth->age . ' y/o' : '—' }} / {{ $patient->gender?->name ?? '—' }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="f-label">Patient Code</span>
                    <span class="f-val" style="font-family: monospace;">{{ $patient->patient_code ?? '—' }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="f-label">Encounter Code</span>
                    <span class="f-val" style="font-family: monospace; color: #0f766e;">{{ $consultation->consultation_code }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="f-label">Clinical Impression / Assessment</span>
                    <span class="f-val">{{ $consultation->assessment ?? ($consultation->chief_complaint ?? 'Clinical Investigation') }}</span>
                    @if(!empty($consultation->icd_10_code))
                        <span style="font-family: monospace; color: #0f766e;">({{ $consultation->icd_10_code }})</span>
                    @endif
                </td>
                <td colspan="2">
                    <span class="f-label">Ordering Physician</span>
                    <span class="f-val">{{ $consultation->attendingPhysician?->full_name ?? 'Dr. Attending Physician' }}</span>
                    <span style="font-size: 7pt; color: #64748b;">(PRC: {{ $consultation->attendingPhysician?->prc_license ?? '____________' }})</span>
                </td>
            </tr>
        </table>

        @php
            $labOrders = $consultation->labRequests()->where('history', 1)->with('items.labTest')->get();
            $radOrders = $consultation->radiologyRequests()->where('history', 1)->with('items')->get();
            $pharmaOrders = $consultation->pharmacyRequests()->where('history', 1)->with('items.drug')->get();
            $csrOrders = $consultation->csrRequests()->where('history', 1)->with('items.item')->get();
        @endphp

        {{-- Section 1: Laboratory Orders --}}
        @if($labOrders->count() > 0)
            <div class="sec-head sec-lab">1. Laboratory Requisition</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Order Code</th>
                        <th style="width: 42%;">Test Name / Panel</th>
                        <th style="width: 20%;">Specimen / Section</th>
                        <th style="width: 10%;">Priority</th>
                        <th style="width: 10%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labOrders as $lo)
                        @foreach($lo->items as $item)
                            <tr>
                                <td style="font-family: monospace;">{{ $lo->request_code }}</td>
                                <td><strong>{{ $item->labTest?->name ?? ($item->test_name ?? 'Lab Test') }}</strong></td>
                                <td>{{ $item->labTest?->specimenType?->name ?? ($item->labTest?->section?->name ?? 'Clinical Lab') }}</td>
                                <td>{{ $lo->priority ?? 'Routine' }}</td>
                                <td style="text-align: center;">{{ $item->status ?? $lo->status }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Section 2: Radiology / Imaging Orders --}}
        @if($radOrders->count() > 0)
            <div class="sec-head sec-rad">2. Radiology & Diagnostic Imaging Requisition</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Order Code</th>
                        <th style="width: 42%;">Examination Requested</th>
                        <th style="width: 20%;">Modality</th>
                        <th style="width: 10%;">Priority</th>
                        <th style="width: 10%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($radOrders as $ro)
                        @foreach($ro->items as $item)
                            <tr>
                                <td style="font-family: monospace;">{{ $ro->request_code }}</td>
                                <td><strong>{{ $item->exam_name ?? 'Diagnostic Imaging' }}</strong></td>
                                <td>{{ $item->modality_name ?? 'Radiology' }}</td>
                                <td>{{ $ro->priority ?? 'Routine' }}</td>
                                <td style="text-align: center;">{{ $item->status ?? $ro->status }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Section 3: Supplies & CSR Orders --}}
        @if($csrOrders->count() > 0)
            <div class="sec-head sec-csr">3. CSR Supplies Requisition</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 20%;">Order Code</th>
                        <th style="width: 50%;">Item Description</th>
                        <th style="width: 15%; text-align: right;">Quantity</th>
                        <th style="width: 15%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($csrOrders as $co)
                        @foreach($co->items as $item)
                            <tr>
                                <td style="font-family: monospace;">{{ $co->request_code }}</td>
                                <td><strong>{{ $item->item?->name ?? ($item->item_name ?? 'Supply Item') }}</strong></td>
                                <td style="text-align: right;">{{ (int)$item->quantity ?: $item->quantity }}</td>
                                <td style="text-align: center;">{{ $item->status ?? $co->status }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($labOrders->count() === 0 && $radOrders->count() === 0 && $csrOrders->count() === 0)
            <div style="padding: 24px; text-align: center; color: #64748b; font-style: italic; border: 1px dashed #cbd5e1; margin-top: 10px;">
                No diagnostic orders (laboratory or imaging) recorded for this encounter yet.
            </div>
        @endif

        {{-- Signatures --}}
        <div class="sig-container">
            <table class="sig-table">
                <tr>
                    <td style="width: 50%;">
                        <div class="sig-box" style="float: left;">
                            <div class="sig-line">
                                <div style="font-size: 8.5pt; font-weight: bold; color: #0f172a;">Receiving Unit / Specimen Collector</div>
                                <div style="font-size: 7pt; color: #64748b;">Signature over Printed Name & Date/Time</div>
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="sig-box" style="float: right;">
                            <div class="sig-line">
                                <div style="font-size: 9pt; font-weight: bold; color: #0f172a;">
                                    {{ $consultation->attendingPhysician?->full_name ?? 'Dr. Attending Physician' }}, M.D.
                                </div>
                                <div style="font-size: 7.5pt; color: #475569;">
                                    Ordering Physician (PRC: {{ $consultation->attendingPhysician?->prc_license ?? '____________' }})
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
