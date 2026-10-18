<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Prescription — {{ $patient->last_name ?? 'Patient' }}</title>
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
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.35;
            background: #ffffff;
        }

        /* Outer Frame & Aesthetics */
        .rx-container {
            border: 2px solid #0f766e;
            padding: 18px 22px;
            position: relative;
            background: #ffffff;
            min-height: 980px;
        }
        .inner-border {
            border: 0.5px solid #99f6e4;
            position: absolute;
            top: 4px;
            bottom: 4px;
            left: 4px;
            right: 4px;
            pointer-events: none;
        }

        /* Hospital Letterhead */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .hospital-name {
            font-size: 14pt;
            font-weight: bold;
            color: #0f766e;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .hospital-sub {
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
        }
        .doc-badge {
            text-align: right;
            font-size: 8pt;
            color: #0f766e;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .clinic-name {
            font-size: 9pt;
            font-weight: bold;
            color: #047857;
            margin-top: 2px;
        }

        /* Doctor Credentials Banner */
        .doctor-banner {
            background: #f0fdfa;
            border-left: 3px solid #0f766e;
            padding: 6px 10px;
            margin-bottom: 12px;
        }
        .doctor-name {
            font-size: 11pt;
            font-weight: bold;
            color: #0f766e;
        }
        .doctor-specialty {
            font-size: 8.5pt;
            color: #334155;
            font-weight: 600;
        }
        .doctor-licenses {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }

        /* Patient Demographics Table */
        .patient-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            margin-bottom: 14px;
            background: #f8fafc;
        }
        .patient-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 7px;
            font-size: 8pt;
            vertical-align: middle;
        }
        .pt-label {
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
        }
        .pt-value {
            font-size: 8.5pt;
            font-weight: 600;
            color: #0f172a;
        }

        /* Rx Sign Section */
        .rx-section-header {
            margin: 6px 0 10px 0;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 4px;
        }
        .rx-symbol {
            font-family: 'DejaVu Serif', Georgia, serif;
            font-size: 32pt;
            font-weight: bold;
            font-style: italic;
            color: #0f766e;
            display: inline-block;
            line-height: 1;
            vertical-align: middle;
            margin-right: 12px;
        }
        .rx-title {
            display: inline-block;
            vertical-align: middle;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #334155;
        }
        .rx-ref-code {
            float: right;
            font-family: monospace;
            font-size: 8pt;
            color: #64748b;
            margin-top: 10px;
        }

        /* Prescriptions Body */
        .prescriptions-list {
            margin-top: 8px;
            min-height: 480px;
        }
        .rx-item {
            border-bottom: 1px solid #f1f5f9;
            padding: 8px 6px;
            margin-bottom: 6px;
        }
        .rx-item-num {
            display: inline-block;
            width: 20px;
            font-weight: bold;
            color: #0f766e;
            font-size: 9.5pt;
            vertical-align: top;
        }
        .rx-item-details {
            display: inline-block;
            width: 80%;
            vertical-align: top;
        }
        .rx-drug-name {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
        }
        .rx-drug-brand {
            font-size: 9pt;
            color: #475569;
            font-style: italic;
            margin-left: 4px;
        }
        .rx-dosage-form {
            font-size: 8pt;
            color: #64748b;
            font-weight: 500;
        }
        .rx-quantity-dispense {
            display: inline-block;
            width: 15%;
            text-align: right;
            vertical-align: top;
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f766e;
        }
        .rx-sig {
            margin-top: 3px;
            font-size: 8.5pt;
            color: #1e293b;
            padding-left: 20px;
        }
        .rx-sig-label {
            font-weight: bold;
            color: #0f766e;
            font-style: italic;
            font-size: 8pt;
        }
        .rx-notes {
            margin-top: 2px;
            font-size: 7.5pt;
            color: #64748b;
            padding-left: 20px;
            font-style: italic;
        }

        /* Blank lines if no digital meds */
        .blank-rx-line {
            border-bottom: 1px dotted #cbd5e1;
            height: 38px;
            margin-bottom: 6px;
        }

        /* Diagnosis & Instructions */
        .clinical-notes-box {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 4px;
            padding: 6px 10px;
            margin-top: 10px;
            font-size: 8pt;
        }
        .clinical-notes-label {
            font-weight: bold;
            color: #475569;
            font-size: 7pt;
            text-transform: uppercase;
        }

        /* Footer & Signatures */
        .rx-footer {
            margin-top: 16px;
            border-top: 1px solid #cbd5e1;
            padding-top: 12px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .sig-block {
            width: 250px;
            text-align: center;
            float: right;
        }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            margin-top: 42px;
            padding-top: 3px;
        }
        .sig-doctor {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
        }
        .sig-sub {
            font-size: 7pt;
            color: #64748b;
            line-height: 1.3;
        }
        .caution-box {
            font-size: 6.8pt;
            color: #64748b;
            line-height: 1.4;
            max-width: 320px;
        }
        .followup-text {
            font-size: 8pt;
            font-weight: bold;
            color: #0f766e;
            margin-bottom: 4px;
        }
        .watermark {
            position: absolute;
            top: 45%;
            left: 25%;
            font-size: 60pt;
            color: #f1f5f9;
            opacity: 0.4;
            font-weight: bold;
            transform: rotate(-25deg);
            z-index: -1;
            font-family: 'DejaVu Serif', serif;
        }
    </style>
</head>
<body>
    <div class="rx-container">
        <div class="inner-border"></div>
        <div class="watermark">℞ PRESCRIPTION</div>

        {{-- Hospital Header --}}
        <table class="header-table">
            <tr>
                <td style="width: 70%;">
                    <div class="hospital-name">{{ $tenant->tenant_name ?? 'SITHEMR DEMO HOSPITAL' }}</div>
                    <div class="hospital-sub">
                        {{ $tenant->address ?? '123 Medical Center Boulevard, Manila' }}{{ !empty($tenant->city) ? ', ' . $tenant->city : '' }}<br>
                        Tel: {{ $tenant->contact_number ?? '(02) 8888-0001' }} | Email: {{ $tenant->email ?? 'info@sithemr.com' }}
                    </div>
                    <div class="clinic-name">
                        {{ $consultation->opdClinic?->name ?? 'Outpatient Care Department' }}
                    </div>
                </td>
                <td style="width: 30%; text-align: right;">
                    <div class="doc-badge">Official Prescription</div>
                    <div style="font-size: 7.5pt; color: #64748b; margin-top: 4px;">
                        Date: <strong>{{ \Carbon\Carbon::parse($consultation->consultation_datetime ?? now())->format('F d, Y') }}</strong>
                    </div>
                    <div style="font-size: 7pt; font-family: monospace; color: #94a3b8; margin-top: 2px;">
                        {{ $consultation->consultation_code ?? 'OPD-RX' }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- Doctor Details --}}
        <div class="doctor-banner">
            <div class="doctor-name">{{ $physician->full_name ?? ($consultation->attendingPhysician?->full_name ?? 'Attending Physician, M.D.') }}</div>
            <div class="doctor-specialty">{{ $physician->specialization ?? ($consultation->attendingPhysician?->specialization ?? 'General Medicine / Outpatient Care') }}</div>
            <div class="doctor-licenses">
                PRC License No.: <strong>{{ $physician->prc_license ?? ($consultation->attendingPhysician?->prc_license ?? '____________') }}</strong>
                &nbsp;|&nbsp;
                PhilHealth Accr.: <strong>{{ $physician->philhealth_accreditation ?? ($consultation->attendingPhysician?->philhealth_accreditation ?? 'PH-____________') }}</strong>
                &nbsp;|&nbsp;
                PTR No.: <strong>PTR-VALIDATED</strong>
                @if(!empty($physician->s2_license) || !empty($consultation->attendingPhysician?->s2_license))
                &nbsp;|&nbsp; S2 Lic.: <strong>{{ $physician->s2_license ?? $consultation->attendingPhysician->s2_license }}</strong>
                @endif
            </div>
        </div>

        {{-- Patient Demographics --}}
        <table class="patient-table">
            <tr>
                <td style="width: 42%;">
                    <span class="pt-label">Patient Name</span>
                    <span class="pt-value">{{ $patient->last_name }}, {{ $patient->first_name }} {{ $patient->middle_name ?? '' }} {{ $patient->suffix ?? '' }}</span>
                </td>
                <td style="width: 18%;">
                    <span class="pt-label">Age / Gender</span>
                    <span class="pt-value">{{ $patient->date_of_birth ? $patient->date_of_birth->age . ' y/o' : '—' }} / {{ $patient->gender?->name ?? '—' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="pt-label">Patient ID</span>
                    <span class="pt-value" style="font-family: monospace;">{{ $patient->patient_code ?? '—' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="pt-label">Consult Date</span>
                    <span class="pt-value">{{ \Carbon\Carbon::parse($consultation->consultation_datetime ?? now())->format('m/d/Y') }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="pt-label">Address</span>
                    <span class="pt-value" style="font-size: 8pt;">{{ $patient->address_street ?? ($patient->house_no ?? '') . ' ' . ($patient->street ?? '') }} {{ $patient->city ?? '' }}</span>
                </td>
                <td>
                    <span class="pt-label">Vitals (BP / HR)</span>
                    <span class="pt-value">
                        {{ $consultation->vital_bp_systolic && $consultation->vital_bp_diastolic ? $consultation->vital_bp_systolic . '/' . $consultation->vital_bp_diastolic . ' mmHg' : '—' }}
                        {{ $consultation->vital_heart_rate ? '| ' . $consultation->vital_heart_rate . ' bpm' : '' }}
                    </span>
                </td>
                <td>
                    <span class="pt-label">Weight</span>
                    <span class="pt-value">{{ $consultation->vital_weight ? $consultation->vital_weight . ' kg' : '—' }}</span>
                </td>
            </tr>
        </table>

        {{-- Rx Header --}}
        <div class="rx-section-header">
            <span class="rx-symbol">℞</span>
            <span class="rx-title">Doctor's Prescription</span>
            <span class="rx-ref-code">Ref: {{ $consultation->consultation_code }}</span>
        </div>

        {{-- Prescriptions List --}}
        <div class="prescriptions-list">
            @php
                // Gather prescribed medicines from consultation's pharmacy requests
                $allMedItems = collect();
                if ($consultation->relationLoaded('pharmacyRequests') || $consultation->pharmacyRequests()->exists()) {
                    foreach ($consultation->pharmacyRequests()->where('history', 1)->with('items.drug')->get() as $pr) {
                        foreach ($pr->items as $item) {
                            $allMedItems->push($item);
                        }
                    }
                }
            @endphp

            @if($allMedItems->count() > 0)
                @foreach($allMedItems as $index => $item)
                    <div class="rx-item">
                        <div class="rx-item-num">{{ $index + 1 }}.</div>
                        <div class="rx-item-details">
                            <span class="rx-drug-name">
                                {{ $item->generic_name ?? ($item->drug?->generic_name ?? $item->item_name) }}
                            </span>
                            @if(!empty($item->brand_name) || !empty($item->drug?->brand_name))
                                <span class="rx-drug-brand">
                                    ({{ $item->brand_name ?? $item->drug?->brand_name }})
                                </span>
                            @endif
                            <div class="rx-dosage-form">
                                {{ $item->dosage ?? ($item->drug?->drug_strength ?? '') }}
                                {{ $item->dosage_form ?? ($item->drug?->dosage_form ?? ($item->supply_unit_name ?? '')) }}
                                @if(!empty($item->route))
                                    • Route: <strong>{{ $item->route }}</strong>
                                @endif
                            </div>
                        </div>
                        <div class="rx-quantity-dispense">
                            # {{ (int)$item->quantity ?: $item->quantity }} {{ $item->supply_unit_name ?? 'pcs' }}
                        </div>

                        {{-- Sig / Directions --}}
                        <div class="rx-sig">
                            <span class="rx-sig-label">Sig:</span>
                            @if(!empty($item->frequency))
                                <strong>{{ $item->frequency }}</strong>
                            @endif
                            @if(!empty($item->duration))
                                for {{ $item->duration }}
                            @endif
                            @if(empty($item->frequency) && empty($item->duration))
                                As directed by physician.
                            @endif
                        </div>

                        @if(!empty($item->notes))
                            <div class="rx-notes">Instructions: {{ $item->notes }}</div>
                        @endif
                    </div>
                @endforeach
            @else
                {{-- If no electronic items yet, provide formatted blank physician lines --}}
                <div style="padding: 10px 0; color: #64748b; font-style: italic; font-size: 8.5pt; margin-bottom: 8px;">
                    (No electronic medications queued for this encounter. Doctor may write prescriptions below:)
                </div>
                @for($i = 1; $i <= 6; $i++)
                    <div class="blank-rx-line"></div>
                @endfor
            @endif
        </div>

        {{-- Clinical Assessment / Instructions Box --}}
        @if(!empty($consultation->assessment) || !empty($consultation->treatment_plan))
            <div class="clinical-notes-box">
                @if(!empty($consultation->assessment))
                    <div>
                        <span class="clinical-notes-label">Clinical Impression / Assessment:</span>
                        <strong>{{ $consultation->assessment }}</strong>
                        @if(!empty($consultation->icd_10_code))
                            <span style="color: #0f766e; font-family: monospace;">({{ $consultation->icd_10_code }})</span>
                        @endif
                    </div>
                @endif
                @if(!empty($consultation->treatment_plan))
                    <div style="margin-top: 3px;">
                        <span class="clinical-notes-label">Special Advice / Dietary Plan:</span>
                        {{ $consultation->treatment_plan }}
                    </div>
                @endif
            </div>
        @endif

        {{-- Footer & Physician Sign-off --}}
        <div class="rx-footer">
            <table class="signature-table">
                <tr>
                    <td style="width: 55%; vertical-align: bottom;">
                        @if(!empty($consultation->next_follow_up_date))
                            <div class="followup-text">
                                Next Follow-up: {{ \Carbon\Carbon::parse($consultation->next_follow_up_date)->format('F d, Y') }}
                            </div>
                        @endif
                        <div class="caution-box">
                            • Generics Law Compliance: Generic substitution allowed unless stamped DAW.<br>
                            • Valid for thirty (30) days from date of issuance unless indicated otherwise.<br>
                            • Please present this prescription slip to licensed pharmacist for dispensing.
                        </div>
                    </td>
                    <td style="width: 45%; text-align: right; vertical-align: bottom;">
                        <div class="sig-block">
                            <div class="sig-line">
                                <div class="sig-doctor">{{ $physician->full_name ?? ($consultation->attendingPhysician?->full_name ?? 'Dr. Attending Physician') }}, M.D.</div>
                                <div class="sig-sub">
                                    PRC License No.: {{ $physician->prc_license ?? ($consultation->attendingPhysician?->prc_license ?? '____________') }}<br>
                                    PTR No.: VALIDATED &nbsp;|&nbsp; S2 No.: {{ $physician->s2_license ?? ($consultation->attendingPhysician?->s2_license ?? 'N/A') }}
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
