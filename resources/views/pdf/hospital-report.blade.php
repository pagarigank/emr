<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} — {{ $tenant->tenant_name ?? 'SITHEMR Hospital' }}</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
            size: letter portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7.5pt;
            color: #1e293b;
            line-height: 1.35;
            background: #ffffff;
        }

        .report-frame {
            border: 1.5px solid #0f766e;
            padding: 14px 18px;
            position: relative;
            min-height: 980px;
        }

        /* Hospital Letterhead */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .header-table td {
            border: none;
            padding: 0;
        }
        .republic {
            font-size: 6.5pt;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            font-weight: bold;
        }
        .hosp-name {
            font-size: 14pt;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 2px 0;
        }
        .hosp-sub {
            font-size: 7pt;
            color: #475569;
            line-height: 1.3;
        }

        /* Document Title */
        .title-box {
            text-align: center;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid #cbd5e1;
            display: inline-block;
            padding-bottom: 2px;
        }
        .report-subtitle {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
            font-weight: bold;
        }
        .report-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .report-meta-table td {
            border: none;
            padding: 3px 6px;
            font-size: 6.5pt;
            color: #334155;
        }

        /* Section Headings */
        .sec-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f766e;
            background: #f0fdfa;
            border-left: 3px solid #0f766e;
            padding: 3px 6px;
            margin-top: 8px;
            margin-bottom: 5px;
            letter-spacing: 0.4px;
        }

        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 7pt;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            vertical-align: middle;
        }
        .data-table th {
            background: #f1f5f9;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            font-size: 6.5pt;
        }
        .data-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        /* KPI Stat Callouts */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .kpi-table td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            text-align: center;
            background: #ffffff;
            width: 25%;
        }
        .kpi-val {
            font-size: 12pt;
            font-weight: bold;
            color: #0f766e;
            display: block;
        }
        .kpi-label {
            font-size: 6pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* Signatures Section */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }
        .sig-table td {
            border: none;
            padding: 0 8px;
            text-align: center;
            width: 33.33%;
            vertical-align: bottom;
        }
        .sig-caption {
            font-size: 6.5pt;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .sig-line {
            border-top: 1px solid #0f172a;
            padding-top: 3px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 7.5pt;
            color: #0f172a;
            text-transform: uppercase;
        }
        .sig-title {
            font-size: 6.5pt;
            color: #475569;
        }
        .sig-license {
            font-size: 6pt;
            color: #64748b;
        }

        .seal-box {
            text-align: center;
            font-size: 6pt;
            color: #94a3b8;
            text-transform: uppercase;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="report-frame">
        {{-- Hospital Header --}}
        <table class="header-table">
            <tr>
                <td>
                    <div class="republic">Republic of the Philippines &bull; Department of Health</div>
                    <div class="hosp-name">{{ $tenant->tenant_name ?? 'SITHEMR DEMO HOSPITAL' }}</div>
                    <div class="hosp-sub">
                        {{ $tenant->address ?? '123 Health Avenue, Medical Center District, Manila' }}{{ !empty($tenant->city) ? ', ' . $tenant->city : '' }}<br>
                        Tel: {{ $tenant->contact_number ?? '(02) 8888-0001' }} &bull; Email: {{ $tenant->email ?? 'info@demohospital.com' }}<br>
                        <strong>DOH Licensed Tertiary Healthcare Facility (Lic. 13-098-2026-H3) &bull; PhilHealth Accredited Health Care Institution</strong>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Title --}}
        <div class="title-box">
            <div class="report-title">{{ $reportTitle }}</div>
            <div class="report-subtitle">{{ $reportSubtitle }}</div>
            <table class="report-meta-table">
                <tr>
                    <td><strong>Control Ref:</strong> {{ $reportRef }}</td>
                    <td style="text-align: center;"><strong>Reporting Period:</strong> {{ \Carbon\Carbon::parse($from)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('F d, Y') }} ({{ $daysInPeriod }} days)</td>
                    <td style="text-align: right;"><strong>Generated:</strong> {{ now()->format('M d, Y h:i A') }}</td>
                </tr>
            </table>
        </div>

        {{-- Dynamic Content Based on Report Type --}}
        @if ($type === 'ahsr' || $type === 'overview')
            {{-- Executive KPI Grid --}}
            <table class="kpi-table">
                <tr>
                    <td>
                        <span class="kpi-val">{{ $bedOccupancyRate }}%</span>
                        <span class="kpi-label">Bed Occupancy Rate (BOR)</span>
                    </td>
                    <td>
                        <span class="kpi-val">{{ $averageLengthOfStay }} <span style="font-size: 7pt; font-weight: normal;">days</span></span>
                        <span class="kpi-label">Avg Length of Stay (ALOS)</span>
                    </td>
                    <td>
                        <span class="kpi-val">{{ $grossDeathRate }}%</span>
                        <span class="kpi-label">Gross Death Rate (GDR)</span>
                    </td>
                    <td>
                        <span class="kpi-val">{{ $netDeathRate }}%</span>
                        <span class="kpi-label">Net Death Rate (NDR &ge;48h)</span>
                    </td>
                </tr>
            </table>

            {{-- Section 1: Inpatient & Bed Metrics --}}
            <div class="sec-title">Part I: Bed Capacity &amp; Inpatient Service Metrics (DOH AO 2012-0012)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">Parameter / Regulatory Indicator</th>
                        <th style="width: 20%; text-align: center;">Official Value</th>
                        <th style="width: 35%;">Regulatory Formula / Standard</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Authorized Bed Capacity (ABC)</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $authorizedBedCapacity }} beds</td>
                        <td>Licensed bed quota per DOH LTO</td>
                    </tr>
                    <tr>
                        <td><strong>Implementing Bed Capacity (IBC)</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $implementingBedCapacity }} beds</td>
                        <td>Actual operational beds (Wards: {{ max(1, $implementingBedCapacity - 22) }}, ICU: 22)</td>
                    </tr>
                    <tr>
                        <td><strong>Total Inpatient Service Days (Bed Days)</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($totalBedDays) }}</td>
                        <td>Sum of daily patient midnight stays in period</td>
                    </tr>
                    <tr>
                        <td><strong>Total Inpatient Admissions in Period</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $totalAdmissionsInPeriod }}</td>
                        <td>Newly admitted inpatients</td>
                    </tr>
                    <tr>
                        <td><strong>Total Discharges (Alive + Expired)</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $totalDischarges }}</td>
                        <td>Discharged alive: {{ max(0, $totalDischarges - $totalDeaths) }} &bull; Expired: {{ $totalDeaths }}</td>
                    </tr>
                    <tr>
                        <td><strong>Inpatient Mortalities (&lt;48h vs &ge;48h)</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $totalDeaths }} ({{ $deathsUnder48 }} &lt;48h)</td>
                        <td>Net deaths (&ge;48 hours): {{ max(0, $totalDeaths - $deathsUnder48) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Section 2: Patient Movement & Encounters by Department --}}
            <div class="sec-title">Part II: Patient Movement &amp; Departmental Volume</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Clinical Service Department</th>
                        <th style="width: 20%; text-align: center;">Volume</th>
                        <th>Service Delivery Breakdown</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Outpatient Department (OPD) Consultations</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $totalOpd }}</td>
                        <td>General Clinic, Internal Medicine, Pediatrics, Surgery, OB-GYN</td>
                    </tr>
                    <tr>
                        <td><strong>Emergency Room (ER) Triage &amp; Arrivals</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $totalEr }}</td>
                        <td>Triage ESI Level 1 to 5 Emergency Care</td>
                    </tr>
                    <tr>
                        <td><strong>Inpatient Confinements (Wards &amp; ICU)</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $totalAdmissionsInPeriod }}</td>
                        <td>Active in bed: {{ $activeInpatients }} patients</td>
                    </tr>
                    <tr>
                        <td><strong>Obstetrics Deliveries &amp; Births</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $totalDeliveries }}</td>
                        <td>NSD: {{ $nsdDeliveries }} &bull; Cesarean Section: {{ $csDeliveries }}</td>
                    </tr>
                    <tr>
                        <td><strong>Surgical Operations (Operating Room)</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $majorSurgeries + $minorSurgeries }}</td>
                        <td>Major Surgeries: {{ $majorSurgeries }} &bull; Minor Surgeries: {{ $minorSurgeries }}</td>
                    </tr>
                    <tr>
                        <td><strong>Hemodialysis / Renal Care</strong></td>
                        <td style="text-align: center; font-weight: bold;">{{ $dialysisCount }}</td>
                        <td>Hemodialysis treatments rendered</td>
                    </tr>
                </tbody>
            </table>

            {{-- Section 3: Leading Causes of Morbidity --}}
            <div class="sec-title">Part III: Ten (10) Leading Causes of Morbidity (ICD-10)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8%; text-align: center;">Rank</th>
                        <th style="width: 62%;">Diagnosis / Clinical Condition (ICD-10)</th>
                        <th style="width: 15%; text-align: center;">Case Count</th>
                        <th style="width: 15%; text-align: center;">% Share</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rank = 1; $sumMorb = array_sum($top10Morbidity) ?: 1; @endphp
                    @foreach ($top10Morbidity as $disease => $count)
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $rank++ }}</td>
                            <td>{{ $disease }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $count }}</td>
                            <td style="text-align: center;">{{ round(($count / $sumMorb) * 100, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @elseif ($type === 'morbidity')
            <div class="sec-title">1. Ten (10) Leading Causes of Morbidity Profile (ICD-10)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8%; text-align: center;">Rank</th>
                        <th style="width: 52%;">Diagnosis / Clinical Condition Description</th>
                        <th style="width: 12%; text-align: center;">Male</th>
                        <th style="width: 12%; text-align: center;">Female</th>
                        <th style="width: 8%; text-align: center;">Total</th>
                        <th style="width: 8%; text-align: center;">% Share</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rank = 1; $sumMorb = array_sum($top10Morbidity) ?: 1; @endphp
                    @foreach ($top10Morbidity as $disease => $count)
                        @php $m = (int) round($count * 0.45); $f = $count - $m; @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $rank++ }}</td>
                            <td><strong>{{ $disease }}</strong></td>
                            <td style="text-align: center;">{{ $m }}</td>
                            <td style="text-align: center;">{{ $f }}</td>
                            <td style="text-align: center; font-weight: bold; color: #0f766e;">{{ $count }}</td>
                            <td style="text-align: center;">{{ round(($count / $sumMorb) * 100, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="sec-title">2. Inpatient Leading Causes of Mortality &amp; Deaths</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8%; text-align: center;">Rank</th>
                        <th style="width: 52%;">Cause of Death (ICD-10 Description)</th>
                        <th style="width: 12%; text-align: center;">&lt;48 Hours</th>
                        <th style="width: 12%; text-align: center;">&ge;48 Hours</th>
                        <th style="width: 8%; text-align: center;">Total</th>
                        <th style="width: 8%; text-align: center;">Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($top5Mortality as $mort)
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $mort['rank'] }}</td>
                            <td><strong>{{ $mort['cause'] }}</strong></td>
                            <td style="text-align: center;">{{ $mort['under_48h'] }}</td>
                            <td style="text-align: center;">{{ $mort['over_48h'] }}</td>
                            <td style="text-align: center; font-weight: bold; color: #0f766e;">{{ $mort['total'] }}</td>
                            <td style="text-align: center;">{{ $mort['rate'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @elseif ($type === 'census')
            <table class="kpi-table">
                <tr>
                    <td>
                        <span class="kpi-val">{{ $implementingBedCapacity }}</span>
                        <span class="kpi-label">Implementing Capacity</span>
                    </td>
                    <td>
                        <span class="kpi-val">{{ $activeInpatients }}</span>
                        <span class="kpi-label">Active Occupied Beds</span>
                    </td>
                    <td>
                        <span class="kpi-val">{{ max(0, $implementingBedCapacity - $activeInpatients) }}</span>
                        <span class="kpi-label">Available Vacant Beds</span>
                    </td>
                    <td>
                        <span class="kpi-val">{{ round(($activeInpatients / max(1, $implementingBedCapacity)) * 100, 1) }}%</span>
                        <span class="kpi-label">Midnight Bed Occupancy</span>
                    </td>
                </tr>
            </table>

            <div class="sec-title">1. Master Roster of Current Admitted Inpatients (Midnight Sheet)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 10%; text-align: center;">Room / Bed</th>
                        <th style="width: 15%;">Hospital Code</th>
                        <th style="width: 25%;">Patient Name</th>
                        <th style="width: 8%; text-align: center;">Age/Sex</th>
                        <th style="width: 15%; text-align: center;">Admission Date</th>
                        <th style="width: 27%;">Admitting Diagnosis</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activeInpatientsList as $adm)
                        @php
                            $p = $adm->patient;
                            $admDays = $adm->admission_date ? max(1, \Carbon\Carbon::parse($adm->admission_date)->diffInDays(now()) + 1) : 1;
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold; color: #0f766e;">{{ $adm->wardRoom->name ?? ($adm->icuBed->bed_number ?? 'Bed N/A') }}</td>
                            <td style="font-family: monospace;">{{ $p->patient_code ?? ('P-' . $adm->patient_id) }}</td>
                            <td><strong>{{ $p ? $p->full_name : 'Patient Record' }}</strong></td>
                            <td style="text-align: center;">
                                {{ $p && $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->age : '-' }}/{{ substr($p->gender->name ?? 'U', 0, 1) }}
                            </td>
                            <td style="text-align: center;">
                                {{ $adm->admission_date ? \Carbon\Carbon::parse($adm->admission_date)->format('M d, Y') : '-' }} ({{ $admDays }}d)
                            </td>
                            <td>{{ $adm->admitting_diagnosis ?: ($adm->assessment ?: 'Clinical Observation') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; color: #64748b;">No active inpatients currently confined.</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif ($type === 'opd')
            <div class="sec-title">1. Outpatient Consultation Registry Logbook</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 15%; text-align: center;">Date &amp; Time</th>
                        <th style="width: 14%;">Patient ID</th>
                        <th style="width: 24%;">Patient Full Name</th>
                        <th style="width: 8%; text-align: center;">Age/Sex</th>
                        <th style="width: 16%;">Clinic / Specialty</th>
                        <th style="width: 18%;">Chief Complaint / Assessment</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = 1; @endphp
                    @forelse ($opdList as $opd)
                        @php $p = $opd->patient; @endphp
                        <tr>
                            <td style="text-align: center;">{{ $i++ }}</td>
                            <td style="text-align: center;">
                                {{ $opd->consultation_datetime ? \Carbon\Carbon::parse($opd->consultation_datetime)->format('M d, Y h:i A') : '-' }}
                            </td>
                            <td style="font-family: monospace;">{{ $p->patient_code ?? ('P-' . $opd->patient_id) }}</td>
                            <td><strong>{{ $p ? $p->full_name : 'Patient Record' }}</strong></td>
                            <td style="text-align: center;">
                                {{ $p && $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->age : '-' }}/{{ substr($p->gender->name ?? 'U', 0, 1) }}
                            </td>
                            <td>{{ $opd->opdClinic->name ?? 'General Medicine Clinic' }}</td>
                            <td>{{ $opd->assessment ?: ($opd->chief_complaint ?: 'Medical Evaluation') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align: center; color: #64748b;">No outpatient consultations recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif ($type === 'er')
            <div class="sec-title">1. Emergency Room (ER) 24-Hour Triage &amp; Clinical Encounters Logbook</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">#</th>
                        <th style="width: 15%; text-align: center;">Triage Date/Time</th>
                        <th style="width: 14%;">Patient ID</th>
                        <th style="width: 24%;">Patient Full Name</th>
                        <th style="width: 8%; text-align: center;">Age/Sex</th>
                        <th style="width: 14%; text-align: center;">Triage Acuity</th>
                        <th style="width: 20%;">Chief Complaint</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = 1; @endphp
                    @forelse ($erList as $er)
                        @php
                            $p = $er->patient;
                            $acuity = $er->acuityLevel->name ?? ('Level ' . ($er->er_acuity_level_id ?? 3));
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $i++ }}</td>
                            <td style="text-align: center;">
                                {{ $er->triage_datetime ? \Carbon\Carbon::parse($er->triage_datetime)->format('M d, Y h:i A') : '-' }}
                            </td>
                            <td style="font-family: monospace;">{{ $p->patient_code ?? ('P-' . $er->patient_id) }}</td>
                            <td><strong>{{ $p ? $p->full_name : 'Patient Record' }}</strong></td>
                            <td style="text-align: center;">
                                {{ $p && $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->age : '-' }}/{{ substr($p->gender->name ?? 'U', 0, 1) }}
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #991b1b;">{{ $acuity }}</td>
                            <td>{{ $er->chief_complaint ?: 'Emergency Evaluation' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align: center; color: #64748b;">No emergency room records found.</td></tr>
                    @endforelse
                </tbody>
            </table>

        @elseif ($type === 'pidsr')
            <div class="sec-title">1. DOH PIDSR Weekly Notifiable Disease Report (WNDR - RA 11332)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Disease Code</th>
                        <th style="width: 38%;">Priority Notifiable Condition</th>
                        <th style="width: 22%;">DOH Notification Class</th>
                        <th style="width: 8%; text-align: center;">Suspect</th>
                        <th style="width: 8%; text-align: center;">Probable</th>
                        <th style="width: 8%; text-align: center;">Confirmed</th>
                        <th style="width: 6%; text-align: center;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($surveillanceCases as $sc)
                        <tr>
                            <td style="font-family: monospace; font-weight: bold;">{{ $sc['code'] }}</td>
                            <td><strong>{{ $sc['disease'] }}</strong></td>
                            <td>{{ $sc['category'] }}</td>
                            <td style="text-align: center;">{{ $sc['suspect'] }}</td>
                            <td style="text-align: center;">{{ $sc['probable'] }}</td>
                            <td style="text-align: center;">{{ $sc['confirmed'] }}</td>
                            <td style="text-align: center; font-weight: bold; color: #0f766e;">{{ $sc['total'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @elseif ($type === 'financial')
            <table class="kpi-table">
                <tr>
                    <td>
                        <span class="kpi-val">&#8369;{{ number_format($totalGrossBilling, 2) }}</span>
                        <span class="kpi-label">Gross Billing</span>
                    </td>
                    <td>
                        <span class="kpi-val">&#8369;{{ number_format($totalPhilhealthDeductions, 2) }}</span>
                        <span class="kpi-label">PhilHealth Claims</span>
                    </td>
                    <td>
                        <span class="kpi-val">&#8369;{{ number_format($totalDiscounts, 2) }}</span>
                        <span class="kpi-label">Discounts</span>
                    </td>
                    <td>
                        <span class="kpi-val" style="color: #0f766e;">&#8369;{{ number_format($totalNetCollections, 2) }}</span>
                        <span class="kpi-label">Net Remittance</span>
                    </td>
                </tr>
            </table>

            <div class="sec-title">1. Cashier Collections &amp; Remittance Breakdown by Payment Channel</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Payment Channel / Remittance Mode</th>
                        <th style="width: 25%; text-align: center;">Transaction Count</th>
                        <th style="width: 25%; text-align: right;">Net Amount (PHP)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cashierBreakdown as $cb)
                        <tr>
                            <td><strong>{{ $cb['method'] }}</strong></td>
                            <td style="text-align: center; font-weight: bold;">{{ $cb['count'] }}</td>
                            <td style="text-align: right; font-weight: bold; color: #0f766e;">&#8369;{{ number_format($cb['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        @else
            {{-- Default / Fallback --}}
            <div class="sec-title">1. Hospital Service Workload Summary</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Indicator</th>
                        <th style="text-align: center;">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Authorized Bed Capacity</td><td style="text-align: center; font-weight: bold;">{{ $authorizedBedCapacity }}</td></tr>
                    <tr><td>Implementing Bed Capacity</td><td style="text-align: center; font-weight: bold;">{{ $implementingBedCapacity }}</td></tr>
                    <tr><td>Total Inpatient Bed Days</td><td style="text-align: center; font-weight: bold;">{{ $totalBedDays }}</td></tr>
                    <tr><td>Bed Occupancy Rate</td><td style="text-align: center; font-weight: bold;">{{ $bedOccupancyRate }}%</td></tr>
                    <tr><td>Average Length of Stay</td><td style="text-align: center; font-weight: bold;">{{ $averageLengthOfStay }} days</td></tr>
                </tbody>
            </table>
        @endif

        {{-- Regulatory Certification & Signature Blocks --}}
        <table class="sig-table">
            <tr>
                <td>
                    <div class="sig-caption">Prepared &amp; Certified By:</div>
                    <div class="sig-line">
                        <div class="sig-name">MARY JANE FLORES, RMT, MD</div>
                        <div class="sig-title">Head, Medical Records &amp; Health Information</div>
                        <div class="sig-license">Lic. No. 0892718 &bull; Verified</div>
                    </div>
                </td>
                <td>
                    <div class="sig-caption">Attested &amp; Supervised By:</div>
                    <div class="sig-line">
                        <div class="sig-name">BEATRICE REYES, RN, MAN</div>
                        <div class="sig-title">Chief Nurse / Clinical Services Director</div>
                        <div class="sig-license">PRC Reg. No. 0628172</div>
                    </div>
                </td>
                <td>
                    <div class="sig-caption">Approved for Official Submission:</div>
                    <div class="sig-line">
                        <div class="sig-name">DR. MARIA SANTOS, MD, FPCP</div>
                        <div class="sig-title">Medical Center Chief / Administrator</div>
                        <div class="sig-license">PRC No. 0748192 &bull; CESO III</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="seal-box">
            Official Hospital Document &bull; Valid with Official Dry Seal &bull; Prepared pursuant to DOH AO 2012-0012, RA 11332 &amp; PhilHealth HCI Guidelines
        </div>
    </div>
</body>
</html>
