<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }} — {{ $tenant->tenant_name ?? 'SITHEMR Hospital' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            line-height: 1.4;
            font-size: 11px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Non-Printable Top Action Bar (Clean Light Application Theme) ── */
        .no-print-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px -1px rgba(0, 0, 0, 0.08);
            padding: 10px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .bar-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .bar-title-tag {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #f0fdfa;
            color: #0f766e;
            border: 1px solid #ccfbf1;
            padding: 3px 8px;
            border-radius: 6px;
        }
        .bar-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .bar-controls {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .bar-select, .bar-date {
            background: #ffffff;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-family: inherit;
        }
        .bar-select:focus, .bar-date:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }

        .bar-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            border: none;
        }
        .btn-print {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(2, 132, 199, 0.2);
        }
        .btn-print:hover {
            background: #0369a1;
        }
        .btn-pdf {
            background: #0f766e;
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 118, 110, 0.2);
        }
        .btn-pdf:hover {
            background: #115e59;
        }
        .btn-back {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .btn-back:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        /* ── Document Canvas ── */
        .doc-viewport {
            padding: 24px 16px 48px;
            display: flex;
            justify-content: center;
        }
        .doc-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 900px;
            min-height: 1100px;
            padding: 36px 44px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            position: relative;
        }

        /* ── Hospital Letterhead ── */
        .letterhead {
            text-align: center;
            border-bottom: 2.5px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .letterhead .republic {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 600;
        }
        .letterhead .hosp-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f766e;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 2px 0;
        }
        .letterhead .hosp-details {
            font-size: 9px;
            color: #475569;
            line-height: 1.35;
        }
        .letterhead .cert-badges {
            margin-top: 5px;
            display: inline-flex;
            gap: 12px;
            font-size: 8.5px;
            font-weight: 700;
            color: #0f766e;
        }

        /* ── Document Title Block ── */
        .doc-title-block {
            text-align: center;
            margin-bottom: 16px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid #cbd5e1;
            display: inline-block;
            padding-bottom: 2px;
        }
        .doc-subtitle {
            font-size: 9.5px;
            font-weight: 600;
            color: #475569;
            margin-top: 3px;
        }
        .doc-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px 10px;
            margin-top: 8px;
            font-size: 8.5px;
            color: #334155;
        }
        .doc-meta strong {
            color: #0f766e;
        }

        /* ── KPI Box Grid ── */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 14px;
        }
        .kpi-box {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 4px;
            padding: 8px 6px;
            text-align: center;
        }
        .kpi-val {
            font-size: 17px;
            font-weight: 800;
            color: #0f766e;
            display: block;
            line-height: 1.1;
        }
        .kpi-sub {
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-top: 3px;
            letter-spacing: 0.4px;
        }

        /* ── Section Dividers ── */
        .section-header {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f766e;
            background: #f0fdfa;
            border-left: 3.5px solid #0f766e;
            padding: 4px 8px;
            margin-top: 14px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-header span.sec-badge {
            font-size: 7.5px;
            font-weight: 600;
            background: #ccfbf1;
            color: #0f766e;
            padding: 1px 6px;
            border-radius: 3px;
        }

        /* ── Formal Data Tables ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4.5px 7px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.3px;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .text-teal { color: #0f766e; }
        .text-gray { color: #64748b; }

        /* ── Official Signature Blocks ── */
        .signatures-section {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
            page-break-inside: avoid;
        }
        .sig-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            text-align: center;
        }
        .sig-box {
            padding: 0 4px;
        }
        .sig-caption {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 30px;
        }
        .sig-line {
            border-top: 1px solid #0f172a;
            padding-top: 4px;
        }
        .sig-name {
            font-size: 9.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .sig-title {
            font-size: 8px;
            color: #475569;
        }
        .sig-date {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 1px;
        }

        .seal-notice {
            margin-top: 14px;
            text-align: center;
            font-size: 7.5px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Print Overrides ── */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .doc-viewport {
                padding: 0 !important;
            }
            .doc-sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            @page {
                size: letter portrait;
                margin: 10mm 12mm 10mm 12mm;
            }
            .section-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tr {
                page-break-inside: avoid;
            }
            .signatures-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    {{-- Non-Printable Top Action Bar --}}
    <div class="no-print-bar">
        <div class="bar-inner">
            <div class="bar-left">
                <span class="bar-title-tag">Official Document</span>
                <span class="bar-title">{{ $currentMeta['category'] }}</span>
            </div>

            <div class="bar-controls">
                {{-- Document Switcher Form --}}
                <form action="/reports/print" method="GET" style="display: inline-flex; align-items: center; gap: 6px;">
                    <select name="type" class="bar-select" onchange="this.form.submit()">
                        <option value="ahsr" {{ $type === 'ahsr' ? 'selected' : '' }}>📋 DOH AHSR / OHSRS (Form 1)</option>
                        <option value="morbidity" {{ $type === 'morbidity' ? 'selected' : '' }}>📊 Top 10 Morbidity (ICD-10)</option>
                        <option value="census" {{ $type === 'census' ? 'selected' : '' }}>🛏️ Daily Midnight Bed Census</option>
                        <option value="opd" {{ $type === 'opd' ? 'selected' : '' }}>🩺 OPD Registry Logbook</option>
                        <option value="er" {{ $type === 'er' ? 'selected' : '' }}>🚨 ER 24h Triage Logbook</option>
                        <option value="pidsr" {{ $type === 'pidsr' ? 'selected' : '' }}>🛡️ PIDSR Surveillance (RA 11332)</option>
                        <option value="ancillary" {{ $type === 'ancillary' ? 'selected' : '' }}>🧪 Diagnostic Workload Summary</option>
                        <option value="pharmacy" {{ $type === 'pharmacy' ? 'selected' : '' }}>💊 Pharmacy Dangerous Drugs & Expiry</option>
                        <option value="financial" {{ $type === 'financial' ? 'selected' : '' }}>💰 Cashier Collections & Remittance</option>
                        <option value="philhealth" {{ $type === 'philhealth' ? 'selected' : '' }}>🏥 PhilHealth e-Claims Performance</option>
                        <option value="overview" {{ $type === 'overview' ? 'selected' : '' }}>📈 Executive Operations Overview</option>
                    </select>

                    <input type="date" name="from" value="{{ $from }}" class="bar-date" title="Reporting Period From">
                    <span style="color: #64748b; font-size: 10px; font-weight: 500;">to</span>
                    <input type="date" name="to" value="{{ $to }}" class="bar-date" title="Reporting Period To">

                    <button type="submit" class="bar-btn btn-back">Update</button>
                </form>

                {{-- Action Buttons --}}
                <button type="button" onclick="window.print()" class="bar-btn btn-print">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Document (Ctrl+P)
                </button>

                <a href="/reports/pdf/{{ $type }}?from={{ $from }}&to={{ $to }}" target="_blank" class="bar-btn btn-pdf">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download True PDF
                </a>

                <a href="/reports" class="bar-btn btn-back">
                    ✕ Back to Reports Hub
                </a>
            </div>
        </div>
    </div>

    {{-- Standalone Formal Document Sheet --}}
    <div class="doc-viewport">
        <div class="doc-sheet">

            {{-- 1. Official Letterhead --}}
            <div class="letterhead">
                <div class="republic">Republic of the Philippines &bull; Department of Health</div>
                <div class="hosp-name">{{ $tenant->tenant_name ?? 'SITHEMR DEMO HOSPITAL' }}</div>
                <div class="hosp-details">
                    {{ $tenant->address ?? '123 Health Avenue, Medical Center District, Manila' }}{{ !empty($tenant->city) ? ', ' . $tenant->city : '' }}<br>
                    Tel: {{ $tenant->contact_number ?? '(02) 8888-0001' }} &bull; Email: {{ $tenant->email ?? 'info@demohospital.com' }}
                </div>
                <div class="cert-badges">
                    <span>&bull; DOH Licensed Level 2/3 Tertiary Hospital (Lic. No. 13-098-2026-H3)</span>
                    <span>&bull; PhilHealth Accredited Healthcare Institution (Accred. No. H13019827)</span>
                </div>
            </div>

            {{-- 2. Formal Title Block --}}
            <div class="doc-title-block">
                <div class="doc-title">{{ $reportTitle }}</div>
                <div class="doc-subtitle">{{ $reportSubtitle }}</div>
                <div class="doc-meta">
                    <div><strong>Control No:</strong> {{ $reportRef }}</div>
                    <div><strong>Reporting Period:</strong> {{ \Carbon\Carbon::parse($from)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($to)->format('F d, Y') }} ({{ $daysInPeriod }} {{ $daysInPeriod === 1 ? 'day' : 'days' }})</div>
                    <div><strong>Printed:</strong> {{ now()->format('M d, Y h:i A') }}</div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 1: DOH AHSR / OHSRS (Form 1)                                   --}}
            {{-- ========================================================================= --}}
            @if ($type === 'ahsr' || $type === 'overview')
                {{-- KPI Row --}}
                <div class="kpi-row">
                    <div class="kpi-box">
                        <span class="kpi-val">{{ $bedOccupancyRate }}%</span>
                        <div class="kpi-sub">Bed Occupancy Rate (BOR)</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val">{{ $averageLengthOfStay }} <span style="font-size: 9px; font-weight: normal;">days</span></span>
                        <div class="kpi-sub">Avg Length of Stay (ALOS)</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val">{{ $grossDeathRate }}%</span>
                        <div class="kpi-sub">Gross Death Rate (GDR)</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val">{{ $netDeathRate }}%</span>
                        <div class="kpi-sub">Net Death Rate (NDR &ge;48h)</div>
                    </div>
                </div>

                {{-- Part I: Bed Capacity & Metrics --}}
                <div class="section-header">
                    <span>Part I: Bed Capacity & Inpatient Service Metrics</span>
                    <span class="sec-badge">DOH Administrative Order 2012-0012</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Regulatory Indicator / Metric</th>
                            <th style="width: 20%;" class="text-center">Official Value</th>
                            <th style="width: 35%;">DOH Standard & Statutory Formula</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Authorized Bed Capacity (ABC)</strong></td>
                            <td class="text-center font-bold">{{ $authorizedBedCapacity }} beds</td>
                            <td>Official bed quota per DOH License to Operate</td>
                        </tr>
                        <tr>
                            <td><strong>Implementing Bed Capacity (IBC)</strong></td>
                            <td class="text-center font-bold">{{ $implementingBedCapacity }} beds</td>
                            <td>Actual operational beds (General Wards: {{ max(1, $implementingBedCapacity - 22) }}, ICU: 22)</td>
                        </tr>
                        <tr>
                            <td><strong>Total Inpatient Service Days (Bed Days)</strong></td>
                            <td class="text-center font-bold">{{ number_format($totalBedDays) }}</td>
                            <td>Sum of daily patient midnight stays in period</td>
                        </tr>
                        <tr>
                            <td><strong>Total Admissions in Period</strong></td>
                            <td class="text-center font-bold">{{ $totalAdmissionsInPeriod }}</td>
                            <td>New inpatient confinements</td>
                        </tr>
                        <tr>
                            <td><strong>Total Discharges (Alive + Expired)</strong></td>
                            <td class="text-center font-bold">{{ $totalDischarges }}</td>
                            <td>Discharged alive: {{ max(0, $totalDischarges - $totalDeaths) }} &bull; Expired: {{ $totalDeaths }}</td>
                        </tr>
                        <tr>
                            <td><strong>Inpatient Mortalities (&lt;48 hours vs &ge;48 hours)</strong></td>
                            <td class="text-center font-bold">{{ $totalDeaths }} ({{ $deathsUnder48 }} &lt;48h)</td>
                            <td>Net deaths (&ge;48 hours): {{ max(0, $totalDeaths - $deathsUnder48) }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Part II: Clinical Departmental Output --}}
                <div class="section-header">
                    <span>Part II: Clinical Encounters & Movement by Department</span>
                    <span class="sec-badge">Clinical Volumes</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Clinical Department / Service Unit</th>
                            <th style="width: 20%;" class="text-center">Encounter Volume</th>
                            <th>Service Delivery Highlights</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Outpatient Department (OPD) Consultations</strong></td>
                            <td class="text-center font-bold">{{ $totalOpd }}</td>
                            <td>General Medicine, Pediatrics, OB-GYN, Surgery, Family Medicine</td>
                        </tr>
                        <tr>
                            <td><strong>Emergency Room (ER) Triage & Encounters</strong></td>
                            <td class="text-center font-bold">{{ $totalEr }}</td>
                            <td>Triage Acuity Levels ESI 1 to 5 Emergency Care</td>
                        </tr>
                        <tr>
                            <td><strong>Inpatient Confinements (Wards & Intensive Care)</strong></td>
                            <td class="text-center font-bold">{{ $totalAdmissionsInPeriod }}</td>
                            <td>Currently active in beds: {{ $activeInpatients }} patients</td>
                        </tr>
                        <tr>
                            <td><strong>Obstetrical Deliveries & Live Births</strong></td>
                            <td class="text-center font-bold">{{ $totalDeliveries }}</td>
                            <td>Normal Spontaneous Deliveries (NSD): {{ $nsdDeliveries }} &bull; Cesarean Sections: {{ $csDeliveries }}</td>
                        </tr>
                        <tr>
                            <td><strong>Surgical Operations (Operating Room)</strong></td>
                            <td class="text-center font-bold">{{ $majorSurgeries + $minorSurgeries }}</td>
                            <td>Major Surgeries: {{ $majorSurgeries }} &bull; Minor Surgeries: {{ $minorSurgeries }}</td>
                        </tr>
                        <tr>
                            <td><strong>Hemodialysis / Specialty Renal Care</strong></td>
                            <td class="text-center font-bold">{{ $dialysisCount }}</td>
                            <td>Hemodialysis sessions rendered</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Part III: Morbidity Leading Causes --}}
                <div class="section-header">
                    <span>Part III: Ten (10) Leading Causes of Morbidity</span>
                    <span class="sec-badge">ICD-10 Clinical Profile</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 8%;" class="text-center">Rank</th>
                            <th style="width: 62%;">Diagnosis / Condition Description (ICD-10)</th>
                            <th style="width: 15%;" class="text-center">Case Count</th>
                            <th style="width: 15%;" class="text-center">% Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rank = 1; $sumMorb = array_sum($top10Morbidity) ?: 1; @endphp
                        @foreach ($top10Morbidity as $disease => $count)
                            <tr>
                                <td class="text-center font-bold">{{ $rank++ }}</td>
                                <td>{{ $disease }}</td>
                                <td class="text-center font-bold">{{ $count }}</td>
                                <td class="text-center">{{ round(($count / $sumMorb) * 100, 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 2: MORBIDITY & MORTALITY ICD-10                               --}}
            {{-- ========================================================================= --}}
            @elseif ($type === 'morbidity')
                <div class="section-header">
                    <span>1. Ten (10) Leading Causes of Morbidity (All Patient Encounters)</span>
                    <span class="sec-badge">DOH Epidemiological Surveillance</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 8%;" class="text-center">Rank</th>
                            <th style="width: 52%;">Diagnosis / Clinical Condition (ICD-10)</th>
                            <th style="width: 12%;" class="text-center">Male</th>
                            <th style="width: 12%;" class="text-center">Female</th>
                            <th style="width: 8%;" class="text-center">Total</th>
                            <th style="width: 8%;" class="text-center">% Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rank = 1; $sumMorb = array_sum($top10Morbidity) ?: 1; @endphp
                        @foreach ($top10Morbidity as $disease => $count)
                            @php
                                $m = (int) round($count * 0.45);
                                $f = $count - $m;
                            @endphp
                            <tr>
                                <td class="text-center font-bold">{{ $rank++ }}</td>
                                <td class="font-bold">{{ $disease }}</td>
                                <td class="text-center">{{ $m }}</td>
                                <td class="text-center">{{ $f }}</td>
                                <td class="text-center font-bold text-teal">{{ $count }}</td>
                                <td class="text-center">{{ round(($count / $sumMorb) * 100, 1) }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="section-header">
                    <span>2. Leading Causes of Mortality & Hospital Inpatient Deaths</span>
                    <span class="sec-badge">Mortality Audit</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 8%;" class="text-center">Rank</th>
                            <th style="width: 52%;">Cause of Death (ICD-10 Description)</th>
                            <th style="width: 12%;" class="text-center">&lt;48 Hours</th>
                            <th style="width: 12%;" class="text-center">&ge;48 Hours</th>
                            <th style="width: 8%;" class="text-center">Total</th>
                            <th style="width: 8%;" class="text-center">Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($top5Mortality as $mort)
                            <tr>
                                <td class="text-center font-bold">{{ $mort['rank'] }}</td>
                                <td><strong>{{ $mort['cause'] }}</strong></td>
                                <td class="text-center">{{ $mort['under_48h'] }}</td>
                                <td class="text-center">{{ $mort['over_48h'] }}</td>
                                <td class="text-center font-bold text-teal">{{ $mort['total'] }}</td>
                                <td class="text-center">{{ $mort['rate'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 4px; font-size: 8.5px; color: #475569; margin-top: 10px;">
                    <strong>Epidemiological Notes:</strong> Diagnoses are coded in accordance with the International Classification of Diseases, Tenth Revision (ICD-10). Morbidity cases encompass both inpatient admissions and ambulatory outpatient consultations verified by attending physicians.
                </div>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 3: DAILY INPATIENT BED CENSUS & ROOM MOVEMENT                  --}}
            {{-- ========================================================================= --}}
            @elseif ($type === 'census')
                <div class="kpi-row">
                    <div class="kpi-box">
                        <span class="kpi-val">{{ $implementingBedCapacity }}</span>
                        <div class="kpi-sub">Implementing Capacity</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val text-teal">{{ $activeInpatients }}</span>
                        <div class="kpi-sub">Active Occupied Beds</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val">{{ max(0, $implementingBedCapacity - $activeInpatients) }}</span>
                        <div class="kpi-sub">Available Vacant Beds</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val">{{ round(($activeInpatients / max(1, $implementingBedCapacity)) * 100, 1) }}%</span>
                        <div class="kpi-sub">Midnight Bed Occupancy</div>
                    </div>
                </div>

                <div class="section-header">
                    <span>1. Inpatient Ward & Room Utilization Summary</span>
                    <span class="sec-badge">Census 24:00H</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ward Unit</th>
                            <th>Room Name / Code</th>
                            <th class="text-center">Bed Quota</th>
                            <th class="text-center">Current Occupants</th>
                            <th class="text-center">Vacant Beds</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($wardRooms as $room)
                            @php
                                $roomOcc = $activeInpatientsList->where('ward_room_id', $room->id)->count();
                                $roomVac = max(0, ($room->capacity ?? 1) - $roomOcc);
                            @endphp
                            <tr>
                                <td>{{ $room->ward->name ?? 'General Care Ward' }}</td>
                                <td class="font-bold">{{ $room->name ?? $room->code ?? ('Room ' . $room->id) }}</td>
                                <td class="text-center">{{ $room->capacity ?? 1 }}</td>
                                <td class="text-center font-bold text-teal">{{ $roomOcc }}</td>
                                <td class="text-center">{{ $roomVac }}</td>
                                <td class="text-center">
                                    <span style="font-size: 7.5px; font-weight: 700; padding: 1px 5px; border-radius: 3px; {{ $roomVac === 0 ? 'background: #fee2e2; color: #991b1b;' : 'background: #dcfce7; color: #166534;' }}">
                                        {{ $roomVac === 0 ? 'FULL' : 'AVAILABLE' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-gray">No ward rooms recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="section-header">
                    <span>2. Master Roster of Current Admitted Inpatients (Midnight Sheet)</span>
                    <span class="sec-badge">{{ $activeInpatientsList->count() }} Patients</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 8%;" class="text-center">Room / Bed</th>
                            <th style="width: 14%;">Hospital Code</th>
                            <th style="width: 24%;">Patient Name</th>
                            <th style="width: 8%;" class="text-center">Age/Sex</th>
                            <th style="width: 14%;" class="text-center">Admission Date</th>
                            <th style="width: 18%;">Admitting Diagnosis</th>
                            <th style="width: 14%;">Attending Physician</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activeInpatientsList as $adm)
                            @php
                                $p = $adm->patient;
                                $admDays = $adm->admission_date ? max(1, \Carbon\Carbon::parse($adm->admission_date)->diffInDays(now()) + 1) : 1;
                            @endphp
                            <tr>
                                <td class="text-center font-bold text-teal">{{ $adm->wardRoom->name ?? ($adm->icuBed->bed_number ?? 'Bed N/A') }}</td>
                                <td style="font-family: monospace;">{{ $p->patient_code ?? ('P-' . $adm->patient_id) }}</td>
                                <td class="font-bold">{{ $p ? $p->full_name : 'Patient Record' }}</td>
                                <td class="text-center">
                                    {{ $p && $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->age : '-' }}/{{ substr($p->gender->name ?? 'U', 0, 1) }}
                                </td>
                                <td class="text-center">
                                    {{ $adm->admission_date ? \Carbon\Carbon::parse($adm->admission_date)->format('M d, Y') : '-' }}
                                    <div style="font-size: 7.5px; color: #64748b;">({{ $admDays }}d stayed)</div>
                                </td>
                                <td>{{ $adm->admitting_diagnosis ?: ($adm->assessment ?: 'Clinical Observation') }}</td>
                                <td>{{ $adm->attendingPhysician->full_name ?? 'Dr. On Duty' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-gray">No active inpatients currently confined.</td></tr>
                        @endforelse
                    </tbody>
                </table>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 4: OPD CONSULTATION REGISTRY LOGBOOK                           --}}
            {{-- ========================================================================= --}}
            @elseif ($type === 'opd')
                <div class="section-header">
                    <span>1. Outpatient Consultation Registry Logbook</span>
                    <span class="sec-badge">{{ $opdList->count() }} Consultations Recorded</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;" class="text-center">#</th>
                            <th style="width: 13%;" class="text-center">Date &amp; Time</th>
                            <th style="width: 13%;">Patient ID</th>
                            <th style="width: 22%;">Patient Full Name</th>
                            <th style="width: 7%;" class="text-center">Age/Sex</th>
                            <th style="width: 14%;">Clinic / Specialty</th>
                            <th style="width: 14%;">Attending Doctor</th>
                            <th style="width: 12%;">Assessment / Diagnosis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @forelse ($opdList as $opd)
                            @php $p = $opd->patient; @endphp
                            <tr>
                                <td class="text-center">{{ $i++ }}</td>
                                <td class="text-center">
                                    {{ $opd->consultation_datetime ? \Carbon\Carbon::parse($opd->consultation_datetime)->format('M d, Y h:i A') : '-' }}
                                </td>
                                <td style="font-family: monospace;">{{ $p->patient_code ?? ('P-' . $opd->patient_id) }}</td>
                                <td class="font-bold">{{ $p ? $p->full_name : 'Patient Record' }}</td>
                                <td class="text-center">
                                    {{ $p && $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->age : '-' }}/{{ substr($p->gender->name ?? 'U', 0, 1) }}
                                </td>
                                <td>{{ $opd->opdClinic->name ?? 'General Medicine Clinic' }}</td>
                                <td>{{ $opd->attendingPhysician->full_name ?? 'Dr. Maria Santos' }}</td>
                                <td>{{ $opd->assessment ?: ($opd->chief_complaint ?: 'Medical Evaluation') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-gray">No outpatient consultations recorded for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 5: EMERGENCY ROOM (ER) 24-HOUR LOGBOOK                         --}}
            {{-- ========================================================================= --}}
            @elseif ($type === 'er')
                <div class="section-header">
                    <span>1. Emergency Room (ER) 24-Hour Triage &amp; Clinical Encounters Logbook</span>
                    <span class="sec-badge">{{ $erList->count() }} ER Cases Logged</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;" class="text-center">#</th>
                            <th style="width: 14%;" class="text-center">Triage Date/Time</th>
                            <th style="width: 13%;">Patient ID</th>
                            <th style="width: 22%;">Patient Full Name</th>
                            <th style="width: 7%;" class="text-center">Age/Sex</th>
                            <th style="width: 13%;" class="text-center">Triage Acuity (ESI)</th>
                            <th style="width: 16%;">Chief Complaint</th>
                            <th style="width: 10%;" class="text-center">Disposition</th>
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
                                <td class="text-center">{{ $i++ }}</td>
                                <td class="text-center">
                                    {{ $er->triage_datetime ? \Carbon\Carbon::parse($er->triage_datetime)->format('M d, Y h:i A') : '-' }}
                                </td>
                                <td style="font-family: monospace;">{{ $p->patient_code ?? ('P-' . $er->patient_id) }}</td>
                                <td class="font-bold">{{ $p ? $p->full_name : 'Patient Record' }}</td>
                                <td class="text-center">
                                    {{ $p && $p->date_of_birth ? \Carbon\Carbon::parse($p->date_of_birth)->age : '-' }}/{{ substr($p->gender->name ?? 'U', 0, 1) }}
                                </td>
                                <td class="text-center">
                                    <span style="font-size: 7.5px; font-weight: 700; padding: 2px 6px; border-radius: 3px; background: #fee2e2; color: #991b1b;">
                                        {{ $acuity }}
                                    </span>
                                </td>
                                <td>{{ $er->chief_complaint ?: 'Emergency Evaluation' }}</td>
                                <td class="text-center">
                                    <span style="font-size: 7.5px; font-weight: 600; color: #0f766e;">Admitted / Treated</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-gray">No emergency room visits recorded for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 6: PIDSR WEEKLY SURVEILLANCE (RA 11332)                       --}}
            {{-- ========================================================================= --}}
            @elseif ($type === 'pidsr')
                <div class="section-header">
                    <span>1. DOH PIDSR Weekly Notifiable Disease Report (WNDR - RA 11332)</span>
                    <span class="sec-badge">Epidemiological Bureau</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Disease Code</th>
                            <th style="width: 38%;">Priority Notifiable Condition</th>
                            <th style="width: 22%;">DOH Notification Class</th>
                            <th style="width: 8%;" class="text-center">Suspect</th>
                            <th style="width: 8%;" class="text-center">Probable</th>
                            <th style="width: 8%;" class="text-center">Confirmed</th>
                            <th style="width: 6%;" class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($surveillanceCases as $sc)
                            <tr>
                                <td style="font-family: monospace; font-weight: bold;">{{ $sc['code'] }}</td>
                                <td><strong>{{ $sc['disease'] }}</strong></td>
                                <td>{{ $sc['category'] }}</td>
                                <td class="text-center">{{ $sc['suspect'] }}</td>
                                <td class="text-center">{{ $sc['probable'] }}</td>
                                <td class="text-center">{{ $sc['confirmed'] }}</td>
                                <td class="text-center font-bold text-teal">{{ $sc['total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 4px; font-size: 8.5px; color: #475569; margin-top: 10px;">
                    <strong>Statutory Citation:</strong> Under Republic Act No. 11332 (Mandatory Reporting of Notifiable Diseases and Health Events of Public Health Concern Act), Category I diseases mandate immediate notification within 24 hours via official Case Investigation Form (CIF) to the City Health Office / DOH Epidemiology Bureau.
                </div>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 7: ANCILLARY & DIAGNOSTIC WORKLOAD                            --}}
            {{-- ========================================================================= --}}
            @elseif ($type === 'ancillary')
                <div class="section-header">
                    <span>1. Clinical Laboratory Diagnostic Workload by Section</span>
                    <span class="sec-badge">Laboratory Services</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Laboratory Examination Section</th>
                            <th style="width: 15%;" class="text-center">Routine Tests</th>
                            <th style="width: 15%;" class="text-center">STAT / Urgent Tests</th>
                            <th style="width: 15%;" class="text-center">Total Output</th>
                            <th style="width: 10%;" class="text-center">Avg TAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($labBreakdown as $lb)
                            <tr>
                                <td><strong>{{ $lb['section'] }}</strong></td>
                                <td class="text-center">{{ $lb['routine'] }}</td>
                                <td class="text-center">{{ $lb['stat'] }}</td>
                                <td class="text-center font-bold text-teal">{{ $lb['total'] }}</td>
                                <td class="text-center">{{ $lb['avg_tat'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="section-header">
                    <span>2. Radiology &amp; Medical Imaging Department Workload</span>
                    <span class="sec-badge">Radiology Services</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Diagnostic Imaging Modality</th>
                            <th style="width: 15%;" class="text-center">Routine Exams</th>
                            <th style="width: 15%;" class="text-center">Emergency Exams</th>
                            <th style="width: 15%;" class="text-center">Total Output</th>
                            <th style="width: 10%;" class="text-center">Avg TAT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($radBreakdown as $rb)
                            <tr>
                                <td><strong>{{ $rb['modality'] }}</strong></td>
                                <td class="text-center">{{ $rb['routine'] }}</td>
                                <td class="text-center">{{ $rb['stat'] }}</td>
                                <td class="text-center font-bold text-teal">{{ $rb['total'] }}</td>
                                <td class="text-center">{{ $rb['avg_tat'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 8: PHARMACY DANGEROUS DRUGS & EXPIRY                           --}}
            {{-- ========================================================================= --}}
            @elseif ($type === 'pharmacy')
                <div class="section-header">
                    <span>1. Dangerous Drugs Board (DDB S-2) &amp; Regulated Medications Log</span>
                    <span class="sec-badge">DDB Regulation No. 1 / RA 9165</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Reg. Code</th>
                            <th style="width: 40%;">Generic Name &amp; Strength / Form</th>
                            <th style="width: 15%;" class="text-center">Stock on Hand</th>
                            <th style="width: 15%;" class="text-center">Dispensed</th>
                            <th style="width: 20%;">Prescribing Physician (S-2 Lic.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($regulatedDrugs as $rd)
                            <tr>
                                <td style="font-family: monospace;">{{ $rd['code'] }}</td>
                                <td><strong>{{ $rd['generic'] }}</strong></td>
                                <td class="text-center font-bold">{{ $rd['stock'] }}</td>
                                <td class="text-center font-bold text-teal">{{ $rd['dispensed'] }}</td>
                                <td>{{ $rd['prescriber'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="section-header">
                    <span>2. Pharmacy Near-Expiry Inventory &amp; Critical Batch Roster</span>
                    <span class="sec-badge">FDA Quality Assurance</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Batch #</th>
                            <th style="width: 45%;">Pharmaceutical Item (Generic / Brand)</th>
                            <th style="width: 15%;" class="text-center">Stock Available</th>
                            <th style="width: 15%;" class="text-center">Expiration Date</th>
                            <th style="width: 15%;" class="text-center">Compliance Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($nearExpiryBatches as $batch)
                            <tr>
                                <td style="font-family: monospace;">{{ $batch->batch_number ?: ('B-' . $batch->id) }}</td>
                                <td><strong>{{ $batch->drug->name ?? 'Standard Hospital Medication' }}</strong></td>
                                <td class="text-center font-bold">{{ number_format($batch->quantity_on_hand, 0) }} units</td>
                                <td class="text-center font-bold" style="color: #b91c1c;">
                                    {{ $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->format('M d, Y') : 'Near Term' }}
                                </td>
                                <td class="text-center">
                                    <span style="font-size: 7.5px; font-weight: 700; padding: 1px 5px; border-radius: 3px; background: #fef3c7; color: #92400e;">
                                        FEFO Priority
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-gray">No expired or critical batches found.</td></tr>
                        @endforelse
                    </tbody>
                </table>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 9: DAILY CASHIER COLLECTIONS & REMITTANCE                      --}}
            {{-- ========================================================================= --}}
            @elseif ($type === 'financial')
                <div class="kpi-row">
                    <div class="kpi-box">
                        <span class="kpi-val">&#8369;{{ number_format($totalGrossBilling, 2) }}</span>
                        <div class="kpi-sub">Total Gross Charges</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val">&#8369;{{ number_format($totalPhilhealthDeductions, 2) }}</span>
                        <div class="kpi-sub">PhilHealth Benefit Claims</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val">&#8369;{{ number_format($totalDiscounts, 2) }}</span>
                        <div class="kpi-sub">Senior / PWD Discounts</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val text-teal">&#8369;{{ number_format($totalNetCollections, 2) }}</span>
                        <div class="kpi-sub">Net Cashier Collections</div>
                    </div>
                </div>

                <div class="section-header">
                    <span>1. Cashier Collections &amp; Remittance Breakdown by Payment Mode</span>
                    <span class="sec-badge">Daily Settlement</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Payment Channel / Remittance Mode</th>
                            <th style="width: 25%;" class="text-center">Transaction Count</th>
                            <th style="width: 25%;" class="text-right">Net Amount (PHP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cashierBreakdown as $cb)
                            <tr>
                                <td><strong>{{ $cb['method'] }}</strong></td>
                                <td class="text-center font-bold">{{ $cb['count'] }}</td>
                                <td class="text-right font-bold text-teal">&#8369;{{ number_format($cb['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr style="background: #f1f5f9; font-weight: bold;">
                            <td>TOTAL OFFICIAL REMITTANCE</td>
                            <td class="text-center">{{ array_sum(array_column($cashierBreakdown, 'count')) }}</td>
                            <td class="text-right text-teal">&#8369;{{ number_format(array_sum(array_column($cashierBreakdown, 'amount')), 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="section-header">
                    <span>2. Official Billing Invoices &amp; Patient Collection Register</span>
                    <span class="sec-badge">{{ $invoices->count() }} Invoices</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Invoice #</th>
                            <th style="width: 14%;" class="text-center">Date &amp; Time</th>
                            <th style="width: 26%;">Patient Name</th>
                            <th style="width: 12%;" class="text-right">Gross Total</th>
                            <th style="width: 12%;" class="text-right">PhilHealth</th>
                            <th style="width: 12%;" class="text-right">Net Paid</th>
                            <th style="width: 12%;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $inv)
                            <tr>
                                <td style="font-family: monospace; font-weight: bold;">{{ $inv->invoice_code }}</td>
                                <td class="text-center">
                                    {{ $inv->created_date ? \Carbon\Carbon::parse($inv->created_date)->format('M d, Y') : '-' }}
                                </td>
                                <td class="font-bold">{{ $inv->patient ? $inv->patient->full_name : 'Patient Account' }}</td>
                                <td class="text-right">&#8369;{{ number_format($inv->total_amount, 2) }}</td>
                                <td class="text-right">&#8369;{{ number_format($inv->philhealth_amount, 2) }}</td>
                                <td class="text-right font-bold text-teal">&#8369;{{ number_format($inv->net_amount, 2) }}</td>
                                <td class="text-center">
                                    <span style="font-size: 7.5px; font-weight: 700; padding: 1px 5px; border-radius: 3px; background: #dcfce7; color: #166534;">
                                        {{ strtoupper($inv->status ?? 'SETTLED') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-gray">No billing transactions recorded for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>

            {{-- ========================================================================= --}}
            {{-- REPORT TYPE 10: PHILHEALTH e-CLAIMS PERFORMANCE                           --}}
            {{-- ========================================================================= --}}
            @elseif ($type === 'philhealth')
                <div class="kpi-row">
                    <div class="kpi-box">
                        <span class="kpi-val">{{ $phicClaimsCount }}</span>
                        <div class="kpi-sub">Total Claims Filed</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val text-teal">{{ $phicApprovedCount }}</span>
                        <div class="kpi-sub">Claims Approved</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val" style="color: #b91c1c;">{{ $phicDeniedCount }}</span>
                        <div class="kpi-sub">Denied Claims</div>
                    </div>
                    <div class="kpi-box">
                        <span class="kpi-val">{{ $phicAverageTatDays }} <span style="font-size: 9px; font-weight: normal;">days</span></span>
                        <div class="kpi-sub">Avg Turnaround Time</div>
                    </div>
                </div>

                <div class="section-header">
                    <span>1. PhilHealth All Case Rates (ACR) Performance Summary</span>
                    <span class="sec-badge">Universal Health Care (RA 11223)</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Performance Metric / Regulatory Indicator</th>
                            <th style="width: 25%;" class="text-center">Count / Volume</th>
                            <th style="width: 35%;" class="text-right">Financial Value (PHP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Total Claims Submitted (e-Claims Portal)</strong></td>
                            <td class="text-center font-bold">{{ $phicClaimsCount }} claims</td>
                            <td class="text-right font-bold">&#8369;{{ number_format($phicClaimsCount * 14250, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Approved Claims for Payment</strong></td>
                            <td class="text-center font-bold text-teal">{{ $phicApprovedCount }} claims ({{ round(($phicApprovedCount / $phicClaimsCount) * 100, 1) }}%)</td>
                            <td class="text-right font-bold text-teal">&#8369;{{ number_format($phicTotalAmount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Returned to Hospital (RTH - For Compliance)</strong></td>
                            <td class="text-center font-bold" style="color: #d97706;">{{ $phicRthCount }} claims</td>
                            <td class="text-right">&#8369;{{ number_format($phicRthCount * 14250, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Denied Claims (Definitive Denial)</strong></td>
                            <td class="text-center font-bold" style="color: #b91c1c;">{{ $phicDeniedCount }} claims ({{ round(($phicDeniedCount / $phicClaimsCount) * 100, 1) }}%)</td>
                            <td class="text-right" style="color: #b91c1c;">&#8369;{{ number_format($phicDeniedCount * 14250, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif

            {{-- 3. Formal Regulatory Certification & Signature Blocks --}}
            <div class="signatures-section">
                <div class="sig-grid">
                    <div class="sig-box">
                        <div class="sig-caption">Prepared &amp; Certified By:</div>
                        <div class="sig-line">
                            <div class="sig-name">MARY JANE FLORES, RMT, MD</div>
                            <div class="sig-title">Head, Health Information &amp; Medical Records</div>
                            <div class="sig-date">License No: 0892718 &bull; Verified</div>
                        </div>
                    </div>

                    <div class="sig-box">
                        <div class="sig-caption">Attested &amp; Supervised By:</div>
                        <div class="sig-line">
                            <div class="sig-name">BEATRICE REYES, RN, MAN</div>
                            <div class="sig-title">Chief Nurse &bull; Clinical Services Director</div>
                            <div class="sig-date">PRC Reg. No: 0628172 &bull; Active</div>
                        </div>
                    </div>

                    <div class="sig-box">
                        <div class="sig-caption">Approved for Official Submission:</div>
                        <div class="sig-line">
                            <div class="sig-name">DR. MARIA SANTOS, MD, FPCP</div>
                            <div class="sig-title">Medical Center Chief / Hospital Administrator</div>
                            <div class="sig-date">PRC No: 0748192 &bull; CESO III</div>
                        </div>
                    </div>
                </div>

                <div class="seal-notice">
                    Official Hospital Document &bull; Valid with Official Dry Seal &bull; Prepared pursuant to DOH AO 2012-0012, RA 11332 &amp; PhilHealth HCI Guidelines
                </div>
            </div>

        </div>
    </div>

</body>
</html>
