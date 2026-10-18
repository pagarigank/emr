<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consultation Summary — {{ $consultation->consultation_code }}</title>
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

        .summary-container {
            border: 1.5px solid #0f766e;
            padding: 16px 20px;
            position: relative;
        }

        /* Hospital Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f766e;
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
            color: #0f766e;
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
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Title */
        .title-bar {
            text-align: center;
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            padding: 5px;
            margin: 6px 0 10px 0;
        }
        .doc-heading {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f766e;
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
        table.data-table th {
            background: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            color: #475569;
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
        .section-header {
            background: #0f766e;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 6px;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        /* Narrative Box */
        .narrative-box {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 5px 8px;
            font-size: 8pt;
            color: #1e293b;
            margin-bottom: 6px;
            min-height: 28px;
        }

        /* Vitals Grid */
        .vitals-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .vitals-grid td {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            text-align: center;
            padding: 4px 2px;
            width: 12.5%;
        }
        .v-label {
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
        }
        .v-val {
            font-size: 8pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 1px;
        }

        /* Orders Subtable */
        table.order-subtable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            margin-bottom: 6px;
        }
        table.order-subtable th, table.order-subtable td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            font-size: 7.5pt;
        }
        table.order-subtable th {
            background: #f8fafc;
            color: #334155;
            font-weight: bold;
        }

        /* Signature block */
        .sig-container {
            margin-top: 14px;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
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
            width: 240px;
            text-align: center;
            float: right;
        }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            margin-top: 36px;
            padding-top: 3px;
        }
    </style>
</head>
<body>
    <div class="summary-container">
        {{-- Hospital Letterhead --}}
        <table class="header-table">
            <tr>
                <td style="width: 70%;">
                    <div class="hosp-title">{{ $tenant->tenant_name ?? 'SITHEMR DEMO HOSPITAL' }}</div>
                    <div class="hosp-sub">
                        {{ $tenant->address ?? '123 Health Avenue, Manila' }}{{ !empty($tenant->city) ? ', ' . $tenant->city : '' }}<br>
                        Tel: {{ $tenant->contact_number ?? '(02) 8888-0001' }} &nbsp;|&nbsp; Email: {{ $tenant->email ?? 'info@sithemr.com' }}
                    </div>
                </td>
                <td style="width: 30%;">
                    <div class="doc-badge">Clinical Record</div>
                    <div style="font-size: 7pt; color: #64748b; text-align: right; margin-top: 2px;">
                        Printed: {{ now()->format('M d, Y h:i A') }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- Title Bar --}}
        <div class="title-bar">
            <div class="doc-heading">Outpatient Clinical Consultation Summary & Abstract</div>
        </div>

        {{-- Demographics & Encounter Details --}}
        <table class="data-table">
            <tr>
                <td style="width: 35%;">
                    <span class="f-label">Patient Full Name</span>
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
                    <span class="f-label">PhilHealth PIN</span>
                    <span class="f-val">{{ $patient->philhealth_pin ?? 'None' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="f-label">Consultation Code</span>
                    <span class="f-val" style="font-family: monospace; color: #0f766e;">{{ $consultation->consultation_code }}</span>
                </td>
                <td>
                    <span class="f-label">Encounter Date</span>
                    <span class="f-val">{{ \Carbon\Carbon::parse($consultation->consultation_datetime ?? now())->format('M d, Y') }}</span>
                </td>
                <td>
                    <span class="f-label">Clinic / Department</span>
                    <span class="f-val">{{ $consultation->opdClinic?->name ?? 'OPD General Clinic' }}</span>
                </td>
                <td>
                    <span class="f-label">Attending Physician</span>
                    <span class="f-val">{{ $consultation->attendingPhysician?->full_name ?? '—' }}</span>
                </td>
            </tr>
        </table>

        {{-- Vital Signs Section --}}
        <div class="section-header">1. Vital Signs at Examination</div>
        <table class="vitals-grid">
            <tr>
                <td>
                    <span class="v-label">Blood Pressure</span>
                    <span class="v-val">{{ $consultation->vital_bp_systolic && $consultation->vital_bp_diastolic ? $consultation->vital_bp_systolic . '/' . $consultation->vital_bp_diastolic : '—' }}</span>
                </td>
                <td>
                    <span class="v-label">Heart Rate</span>
                    <span class="v-val">{{ $consultation->vital_heart_rate ? $consultation->vital_heart_rate . ' bpm' : '—' }}</span>
                </td>
                <td>
                    <span class="v-label">Resp Rate</span>
                    <span class="v-val">{{ $consultation->vital_respiratory_rate ? $consultation->vital_respiratory_rate . ' cpm' : '—' }}</span>
                </td>
                <td>
                    <span class="v-label">Temperature</span>
                    <span class="v-val">{{ $consultation->vital_temperature ? $consultation->vital_temperature . ' °C' : '—' }}</span>
                </td>
                <td>
                    <span class="v-label">SpO2</span>
                    <span class="v-val">{{ $consultation->vital_o2_saturation ? $consultation->vital_o2_saturation . '%' : '—' }}</span>
                </td>
                <td>
                    <span class="v-label">Weight</span>
                    <span class="v-val">{{ $consultation->vital_weight ? $consultation->vital_weight . ' kg' : '—' }}</span>
                </td>
                <td>
                    <span class="v-label">Height</span>
                    <span class="v-val">{{ $consultation->vital_height ? $consultation->vital_height . ' cm' : '—' }}</span>
                </td>
                <td>
                    <span class="v-label">BMI</span>
                    <span class="v-val">
                        @if($consultation->vital_weight && $consultation->vital_height && $consultation->vital_height > 0)
                            {{ number_format($consultation->vital_weight / pow($consultation->vital_height / 100, 2), 1) }}
                        @else
                            —
                        @endif
                    </span>
                </td>
            </tr>
        </table>

        {{-- SOAP Clinical Documentation --}}
        <div class="section-header">2. Subjective & Objective Findings</div>
        <table class="data-table">
            <tr>
                <td style="width: 50%;">
                    <span class="f-label">Chief Complaint</span>
                    <div style="font-weight: 500; margin-top: 2px;">{{ $consultation->chief_complaint ?? 'No complaint specified.' }}</div>
                </td>
                <td style="width: 50%;">
                    <span class="f-label">History of Present Illness (HPI)</span>
                    <div style="margin-top: 2px;">{{ $consultation->history_of_present_illness ?? 'None recorded.' }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="f-label">Physical Examination Findings</span>
                    <div style="margin-top: 2px;">{{ $consultation->physical_exam_findings ?? 'General physical examination unremarkable unless specified.' }}</div>
                </td>
            </tr>
        </table>

        {{-- Assessment & Diagnosis --}}
        <div class="section-header">3. Clinical Assessment & Diagnosis</div>
        <div class="narrative-box" style="background: #f0fdfa; border-left: 3px solid #0f766e;">
            <div style="font-size: 9pt; font-weight: bold; color: #0f172a;">
                {{ $consultation->assessment ?? 'Under evaluation' }}
            </div>
            @if(!empty($consultation->icd_10_code))
                <div style="font-size: 7.5pt; color: #0f766e; font-family: monospace; margin-top: 2px;">
                    ICD-10 Code: <strong>{{ $consultation->icd_10_code }}</strong>
                </div>
            @endif
        </div>

        {{-- Treatment & Orders Plan --}}
        <div class="section-header">4. Management Plan & Doctor's Orders</div>
        <div class="narrative-box">
            <span class="f-label">Treatment Plan / Instructions:</span>
            {{ $consultation->treatment_plan ?? 'Standard supportive care and follow prescribed medications.' }}
        </div>

        {{-- Prescriptions --}}
        @php
            $pharmacyOrders = $consultation->pharmacyRequests()->where('history', 1)->with('items.drug')->get();
            $labOrders = $consultation->labRequests()->where('history', 1)->with('items.labTest')->get();
            $radOrders = $consultation->radiologyRequests()->where('history', 1)->with('items')->get();
        @endphp

        @if($pharmacyOrders->count() > 0)
            <div style="font-weight: bold; font-size: 7.5pt; color: #0f766e; margin-top: 4px;">Prescribed Medications (Rx):</div>
            <table class="order-subtable">
                <thead>
                    <tr>
                        <th style="width: 40%;">Drug / Generic Name</th>
                        <th style="width: 15%;">Dosage</th>
                        <th style="width: 25%;">Sig / Frequency</th>
                        <th style="width: 20%; text-align: right;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pharmacyOrders as $po)
                        @foreach($po->items as $item)
                            <tr>
                                <td><strong>{{ $item->generic_name ?? ($item->drug?->generic_name ?? $item->item_name) }}</strong> {{ $item->brand_name ? '('.$item->brand_name.')' : '' }}</td>
                                <td>{{ $item->dosage ?? ($item->drug?->drug_strength ?? '—') }}</td>
                                <td>{{ $item->frequency ?? 'As directed' }} {{ $item->duration ? 'for '.$item->duration : '' }}</td>
                                <td style="text-align: right;">{{ (int)$item->quantity ?: $item->quantity }} {{ $item->supply_unit_name ?? 'pcs' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Laboratory Tests Ordered --}}
        @if($labOrders->count() > 0)
            <div style="font-weight: bold; font-size: 7.5pt; color: #7c3aed; margin-top: 4px;">Laboratory Orders:</div>
            <table class="order-subtable">
                <thead>
                    <tr>
                        <th style="width: 25%;">Request Code</th>
                        <th style="width: 45%;">Test Name</th>
                        <th style="width: 15%;">Priority</th>
                        <th style="width: 15%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labOrders as $lo)
                        @foreach($lo->items as $item)
                            <tr>
                                <td style="font-family: monospace;">{{ $lo->request_code }}</td>
                                <td>{{ $item->labTest?->name ?? ($item->test_name ?? 'Laboratory Test') }}</td>
                                <td>{{ $lo->priority ?? 'Routine' }}</td>
                                <td style="text-align: center;">{{ $item->status ?? $lo->status }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Radiology Orders --}}
        @if($radOrders->count() > 0)
            <div style="font-weight: bold; font-size: 7.5pt; color: #2563eb; margin-top: 4px;">Diagnostic Imaging & Radiology:</div>
            <table class="order-subtable">
                <thead>
                    <tr>
                        <th style="width: 25%;">Request Code</th>
                        <th style="width: 45%;">Exam Name / Modality</th>
                        <th style="width: 15%;">Priority</th>
                        <th style="width: 15%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($radOrders as $ro)
                        @foreach($ro->items as $item)
                            <tr>
                                <td style="font-family: monospace;">{{ $ro->request_code }}</td>
                                <td>{{ $item->exam_name ?? 'Imaging Exam' }} ({{ $item->modality_name ?? 'Radiology' }})</td>
                                <td>{{ $ro->priority ?? 'Routine' }}</td>
                                <td style="text-align: center;">{{ $item->status ?? $ro->status }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Disposition & Follow-Up --}}
        <div class="section-header">5. Disposition & Follow-Up Instructions</div>
        <table class="data-table">
            <tr>
                <td style="width: 40%;">
                    <span class="f-label">Clinical Disposition</span>
                    <span class="f-val">{{ $consultation->disposition ?? 'Discharged / Treated & Sent Home' }}</span>
                </td>
                <td style="width: 30%;">
                    <span class="f-label">Next Follow-Up Date</span>
                    <span class="f-val">
                        {{ $consultation->next_follow_up_date ? \Carbon\Carbon::parse($consultation->next_follow_up_date)->format('F d, Y') : 'As needed / PRN' }}
                    </span>
                </td>
                <td style="width: 30%;">
                    <span class="f-label">Consultation Status</span>
                    <span class="f-val">{{ $consultation->status ?? 'Completed' }}</span>
                </td>
            </tr>
        </table>

        {{-- Signatures --}}
        <div class="sig-container">
            <table class="sig-table">
                <tr>
                    <td style="width: 50%;">
                        <div style="font-size: 7pt; color: #64748b; line-height: 1.4;">
                            This clinical abstract is generated from the official electronic medical record system of {{ $tenant->tenant_name ?? 'SITHEMR Demo Hospital' }}.<br>
                            Ref ID: {{ $consultation->consultation_code }} &nbsp;|&nbsp; Certified True Copy
                        </div>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <div class="sig-box">
                            <div class="sig-line">
                                <div style="font-size: 9.5pt; font-weight: bold; color: #0f172a;">
                                    {{ $consultation->attendingPhysician?->full_name ?? 'Dr. Attending Physician' }}, M.D.
                                </div>
                                <div style="font-size: 7.5pt; color: #475569;">
                                    {{ $consultation->attendingPhysician?->specialization ?? 'Attending Physician' }}
                                </div>
                                <div style="font-size: 7pt; color: #64748b;">
                                    PRC Lic. No.: {{ $consultation->attendingPhysician?->prc_license ?? '____________' }}
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
