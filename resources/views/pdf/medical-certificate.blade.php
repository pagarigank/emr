<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $certificateTitle }} — {{ $patient->last_name ?? 'Patient' }}</title>
    <style>
        @page {
            margin: 18mm 20mm 18mm 20mm;
            size: letter portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            line-height: 1.5;
            background: #ffffff;
        }

        /* Certificate Frame */
        .cert-border {
            border: 2px solid #0f3b4c;
            padding: 24px 28px;
            position: relative;
            min-height: 960px;
        }
        .cert-inner-line {
            border: 0.5px solid #0d9488;
            position: absolute;
            top: 5px;
            bottom: 5px;
            left: 5px;
            right: 5px;
            pointer-events: none;
        }

        /* Hospital Letterhead */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            margin-bottom: 12px;
        }
        .header-table td {
            border: none;
            padding: 0;
        }
        .hospital-name {
            font-size: 16pt;
            font-weight: bold;
            color: #0f3b4c;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .hospital-details {
            font-size: 8pt;
            color: #475569;
            margin-top: 3px;
            line-height: 1.35;
        }
        .header-divider-thick {
            height: 2px;
            background: #0f3b4c;
            margin-top: 10px;
            margin-bottom: 2px;
        }
        .header-divider-thin {
            height: 0.5px;
            background: #0d9488;
            margin-bottom: 16px;
        }

        /* Document Title */
        .title-container {
            text-align: center;
            margin-bottom: 22px;
        }
        .cert-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0f3b4c;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 1px solid #cbd5e1;
            display: inline-block;
            padding-bottom: 4px;
        }
        .cert-ref {
            font-size: 7.5pt;
            font-family: monospace;
            color: #64748b;
            margin-top: 4px;
        }
        .cert-date {
            text-align: right;
            font-size: 9pt;
            color: #334155;
            margin-bottom: 18px;
            font-weight: 500;
        }

        /* Salutation */
        .salutation {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Certificate Body */
        .cert-body {
            font-size: 10pt;
            text-align: justify;
            color: #1e293b;
            margin-bottom: 18px;
            line-height: 1.7;
        }
        .cert-body p {
            margin-bottom: 14px;
            text-indent: 28px;
        }

        /* Patient Info Highlight Box */
        .patient-box {
            width: 100%;
            border-collapse: collapse;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            margin: 14px 0 18px 0;
        }
        .patient-box td {
            border: 1px solid #e2e8f0;
            padding: 5px 8px;
            font-size: 8.5pt;
        }
        .pb-label {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
        }
        .pb-val {
            font-weight: 600;
            color: #0f172a;
        }

        /* Diagnosis Card */
        .diagnosis-card {
            border-left: 3.5px solid #0f766e;
            background: #f0fdfa;
            padding: 10px 14px;
            margin: 14px 0 18px 0;
        }
        .diag-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f766e;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .diag-text {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
        }

        /* Recommendation Box */
        .recommendation-box {
            border-left: 3.5px solid #0284c7;
            background: #f0f9ff;
            padding: 10px 14px;
            margin: 14px 0 18px 0;
            font-size: 9.5pt;
        }
        .rec-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0284c7;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        /* Encounter Breakdown Table (for Abstract/Admission) */
        .encounter-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 16px 0;
            font-size: 8.5pt;
        }
        .encounter-table th, .encounter-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
        }
        .encounter-table th {
            background: #f1f5f9;
            font-weight: bold;
            text-align: left;
            font-size: 7.5pt;
            text-transform: uppercase;
            color: #475569;
        }

        /* Legal / Disclaimer Notice */
        .disclaimer-box {
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            margin-top: 24px;
            margin-bottom: 24px;
            font-size: 7pt;
            color: #64748b;
            line-height: 1.4;
            font-style: italic;
        }

        /* Signatures Section */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: bottom;
        }
        .seal-box {
            width: 140px;
            height: 80px;
            border: 1.5px dashed #94a3b8;
            border-radius: 6px;
            text-align: center;
            padding-top: 28px;
            font-size: 7.5pt;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sig-block {
            width: 260px;
            text-align: center;
            float: right;
        }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            padding-top: 4px;
        }
        .doc-name {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
        }
        .doc-spec {
            font-size: 8pt;
            color: #334155;
            font-weight: 500;
        }
        .doc-creds {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.3;
        }

        /* Watermark */
        .bg-watermark {
            position: absolute;
            top: 42%;
            left: 18%;
            font-size: 64pt;
            color: #f8fafc;
            opacity: 0.5;
            font-weight: bold;
            transform: rotate(-25deg);
            z-index: -1;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="cert-border">
        <div class="cert-inner-line"></div>
        <div class="bg-watermark">OFFICIAL</div>

        {{-- Hospital Header --}}
        <table class="header-table">
            <tr>
                <td>
                    <div class="hospital-name">{{ $tenant->tenant_name ?? 'SITHEMR DEMO HOSPITAL' }}</div>
                    <div class="hospital-details">
                        {{ $tenant->address ?? '123 Health Avenue, Medical Center District, Manila' }}{{ !empty($tenant->city) ? ', ' . $tenant->city : '' }}<br>
                        Tel: {{ $tenant->contact_number ?? '(02) 8888-0001' }} &nbsp;•&nbsp; Email: {{ $tenant->email ?? 'records@demohospital.com' }}<br>
                        DOH Licensed Healthcare Facility &nbsp;|&nbsp; PhilHealth Accredited Tertiary Hospital
                    </div>
                </td>
            </tr>
        </table>
        <div class="header-divider-thick"></div>
        <div class="header-divider-thin"></div>

        {{-- Document Title --}}
        <div class="title-container">
            <div class="cert-title">{{ $certificateTitle }}</div>
            <div class="cert-ref">Control No.: {{ $controlCode ?? ('MC-' . date('Y') . '-' . str_pad($patient->id ?? 1, 5, '0', STR_PAD_LEFT)) }}</div>
        </div>

        @php
            $formattedIssueDate = date('F d, Y');
            if (!empty($issueDate)) {
                try {
                    $formattedIssueDate = \Carbon\Carbon::parse($issueDate)->format('F d, Y');
                } catch (\Throwable) {}
            }

            $formattedEncounterDate = '—';
            if (!empty($encounterDate) && $encounterDate !== '—') {
                try {
                    $formattedEncounterDate = \Carbon\Carbon::parse($encounterDate)->format('F d, Y');
                } catch (\Throwable) {
                    $formattedEncounterDate = (string) $encounterDate;
                }
            }

            $patientAge = '—';
            $patientDob = '—';
            if (!empty($patient->date_of_birth)) {
                try {
                    $dob = $patient->date_of_birth instanceof \DateTimeInterface
                        ? $patient->date_of_birth
                        : \Carbon\Carbon::parse($patient->date_of_birth);
                    $patientAge = $dob->age;
                    $patientDob = $dob->format('M d, Y');
                } catch (\Throwable) {}
            }
        @endphp

        {{-- Date of Issuance --}}
        <div class="cert-date">
            Date Issued: <strong>{{ $formattedIssueDate }}</strong>
        </div>

        {{-- Salutation --}}
        <div class="salutation">TO WHOM IT MAY CONCERN:</div>

        {{-- Opening Statement --}}
        <div class="cert-body">
            <p>
                This is to certify that <strong>{{ $patient->last_name }}, {{ $patient->first_name }} {{ $patient->middle_name ?? '' }} {{ $patient->suffix ?? '' }}</strong>,
                <strong>{{ $patientAge }}</strong> years of age,
                <strong>{{ $patient->gender?->name ?? '—' }}</strong>,
                residing at <strong>{{ $patient->address_street ?? ($patient->house_no ?? '') . ' ' . ($patient->street ?? '') }} {{ $patient->city ?? 'Manila, Philippines' }}</strong>,
                was examined and treated at this institution on
                <strong>{{ $formattedEncounterDate }}</strong>
                under the professional medical care of <strong>{{ $attendingDoctor ?? 'Dr. Attending Physician' }}</strong>.
            </p>
        </div>

        {{-- Patient Demographics Grid --}}
        <table class="patient-box">
            <tr>
                <td style="width: 25%;">
                    <span class="pb-label">Hospital ID / Code</span>
                    <span class="pb-val" style="font-family: monospace;">{{ $patient->patient_code ?? '—' }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="pb-label">Date of Birth</span>
                    <span class="pb-val">{{ $patientDob }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="pb-label">Encounter Ref</span>
                    <span class="pb-val" style="font-family: monospace;">{{ $encounterCode ?? 'OPD' }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="pb-label">PhilHealth PIN</span>
                    <span class="pb-val">{{ $patient->philhealth_pin ?? 'N/A' }}</span>
                </td>
            </tr>
            @if(isset($vitals) && !empty($vitals))
            <tr>
                <td colspan="4">
                    <span class="pb-label">Vital Signs upon Examination</span>
                    <span class="pb-val" style="font-size: 8pt;">
                        BP: {{ $vitals['bp'] ?? '—' }} mmHg &nbsp;|&nbsp;
                        Heart Rate: {{ $vitals['hr'] ?? '—' }} bpm &nbsp;|&nbsp;
                        Temp: {{ $vitals['temp'] ?? '—' }} °C &nbsp;|&nbsp;
                        RR: {{ $vitals['rr'] ?? '—' }} cpm &nbsp;|&nbsp;
                        SpO2: {{ $vitals['spo2'] ?? '—' }}% &nbsp;|&nbsp;
                        Weight: {{ $vitals['weight'] ?? '—' }} kg
                    </span>
                </td>
            </tr>
            @endif
        </table>

        {{-- Diagnosis Card --}}
        <div class="diagnosis-card">
            <div class="diag-title">Diagnosis / Clinical Impression:</div>
            <div class="diag-text">
                {{ $diagnosis ?? 'Clinically Evaluated' }}
                @if(!empty($icd10Code))
                    <span style="font-size: 8.5pt; font-family: monospace; color: #0f766e; font-weight: normal;">(ICD-10: {{ $icd10Code }})</span>
                @endif
            </div>
        </div>

        {{-- Specific Recommendation / Finding by Certificate Type --}}
        @if($certType === 'fit_to_work')
            <div class="recommendation-box">
                <div class="rec-title">Medical Fitness Evaluation:</div>
                <div style="font-size: 9.5pt; color: #0f172a; line-height: 1.5;">
                    The above-named patient has been thoroughly examined and clinically evaluated. The patient has recovered satisfactorily, exhibits no signs or symptoms of contagious or incapacitating medical conditions, and is hereby cleared and pronounced <strong>PHYSICALLY AND MEDICALLY FIT TO RESUME WORK / DUTIES / SCHOOL ACTIVITIES</strong> without restrictions.
                </div>
            </div>
        @elseif($certType === 'illness' || $certType === 'sick_leave')
            <div class="recommendation-box">
                <div class="rec-title">Physician's Recommendation & Medical Leave:</div>
                <div style="font-size: 9.5pt; color: #0f172a; line-height: 1.5;">
                    Due to the medical condition diagnosed above, the patient has been prescribed appropriate medications and is advised to observe <strong>REST FOR {{ $restDays ?? 3 }} CALENDAR DAY(S)</strong>
                    @if(!empty($dateStart) && !empty($dateEnd))
                        from <strong>{{ \Carbon\Carbon::parse($dateStart)->format('F d, Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($dateEnd)->format('F d, Y') }}</strong>
                    @else
                        effective immediately
                    @endif
                    to facilitate complete clinical recovery.
                </div>
            </div>
        @elseif($certType === 'admission' || $certType === 'discharge')
            <div class="recommendation-box">
                <div class="rec-title">Confinement & Discharge Summary:</div>
                <div style="font-size: 9pt; color: #0f172a; line-height: 1.5;">
                    Patient was admitted on <strong>{{ $encounterData['Date/Time'] ?? '—' }}</strong> and managed accordingly.
                    @if(!empty($encounterData['Discharge Date']))
                        Discharged on <strong>{{ $encounterData['Discharge Date'] }}</strong> in stable/improved condition.
                    @endif
                </div>
            </div>
        @else
            @if(!empty($recommendation))
            <div class="recommendation-box">
                <div class="rec-title">Physician's Remarks & Recommendations:</div>
                <div style="font-size: 9.5pt; color: #0f172a; line-height: 1.5;">
                    {{ $recommendation }}
                </div>
            </div>
            @endif
        @endif

        {{-- Diagnostic or Encounter details if provided --}}
        @if(isset($encounterData) && count($encounterData) > 0)
            <table class="encounter-table">
                <thead>
                    <tr>
                        <th colspan="2">Encounter Particulars</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($encounterData as $label => $val)
                        <tr>
                            <td style="width: 35%; font-weight: bold; color: #475569;">{{ $label }}</td>
                            <td style="width: 65%;">{{ $val }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Orders / Services Rendered Summary if provided --}}
        @if(isset($orders) && count($orders) > 0)
            <table class="encounter-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Service Type</th>
                        <th style="width: 25%;">Request Code</th>
                        <th style="width: 35%;">Item(s)</th>
                        <th style="width: 15%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $ord)
                        <tr>
                            <td>{{ $ord['type'] }}</td>
                            <td style="font-family: monospace;">{{ $ord['code'] }}</td>
                            <td>{{ $ord['items'] }}</td>
                            <td style="text-align: center;">{{ $ord['status'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Legal Disclaimer --}}
        <div class="disclaimer-box">
            IMPORTANT LEGAL NOTICE: This medical certificate is issued upon the request of the patient for legitimate verification, employment, or academic purposes. This document is NOT VALID FOR MEDICO-LEGAL PURPOSES or judicial court proceedings unless signed under oath and notarized. Any erasure, modification, or unauthorized reproduction voids this certification.
        </div>

        {{-- Signatures Section --}}
        <table class="signature-table">
            <tr>
                <td style="width: 45%;">
                    <div class="seal-box">
                        OFFICIAL HOSPITAL<br>SEAL / STAMP
                    </div>
                    <div style="font-size: 6.5pt; color: #94a3b8; margin-top: 4px;">
                        Verified by Medical Records Section
                    </div>
                </td>
                <td style="width: 55%; text-align: right;">
                    <div class="sig-block">
                        <div class="sig-line">
                            <div class="doc-name">{{ $attendingDoctor ?? 'Dr. Attending Physician' }}, M.D.</div>
                            <div class="doc-spec">{{ $doctorSpecialty ?? 'Attending Medical Specialist' }}</div>
                            <div class="doc-creds">
                                PRC License No.: <strong>{{ $doctorPrc ?? '____________' }}</strong><br>
                                PTR No.: <strong>PTR-VALIDATED</strong> &nbsp;|&nbsp; S2 No.: <strong>{{ $doctorS2 ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
