<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CF5 — DRG Shadow Billing Statement — {{ $claim->claim_code }}</title>
    <style>
        @page { margin: 12mm 12mm 15mm 12mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 8.5pt; color: #1e293b; line-height: 1.35; }
        .header { text-align: center; border-bottom: 2px solid #0284c7; padding-bottom: 8px; margin-bottom: 10px; }
        .header .rep { font-size: 8pt; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; }
        .header .corp { font-size: 9.5pt; font-weight: bold; color: #0369a1; text-transform: uppercase; margin: 1px 0; }
        .header h1 { font-size: 13pt; margin: 3px 0 2px; text-transform: uppercase; color: #0f172a; font-weight: 800; letter-spacing: 0.5px; }
        .header .meta { margin: 1px 0; font-size: 8pt; color: #64748b; }
        .header .sub-badge { display: inline-block; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-size: 7.5pt; font-weight: 600; margin-top: 3px; }
        
        .section-title { font-size: 8.5pt; font-weight: bold; background: #f1f5f9; color: #0f172a; padding: 4px 6px; border-left: 3px solid #0284c7; margin: 8px 0 4px; text-transform: uppercase; letter-spacing: 0.3px; }
        
        table { width: 100%; border-collapse: collapse; margin: 4px 0 8px; font-size: 8pt; }
        th, td { border: 1px solid #cbd5e1; padding: 3.5px 5px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-weight: 600; color: #334155; font-size: 7.5pt; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        
        .kpi-container { width: 100%; margin: 6px 0 10px; }
        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 6px; }
        .kpi-table td { border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 8px; background: #ffffff; }
        .kpi-label { font-size: 7pt; text-transform: uppercase; color: #64748b; font-weight: 600; margin-bottom: 2px; }
        .kpi-val { font-size: 11pt; font-weight: bold; color: #0f172a; font-family: 'Courier New', monospace; }
        .kpi-sub { font-size: 6.5pt; color: #94a3b8; }
        
        .bg-sky { background-color: #f0f9ff; }
        .bg-emerald { background-color: #ecfdf5; }
        .bg-rose { background-color: #fff1f2; }
        .bg-amber { background-color: #fffbeb; }
        
        .badge-surplus { background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 7.5pt; display: inline-block; }
        .badge-deficit { background: #fee2e2; color: #b91c1c; padding: 2px 6px; border-radius: 3px; font-weight: bold; font-size: 7.5pt; display: inline-block; }
        
        .notice-box { background: #eff6ff; border: 1px dashed #3b82f6; border-radius: 4px; padding: 6px 8px; font-size: 7.5pt; color: #1e40af; margin: 6px 0 8px; line-height: 1.35; }
        
        .signature-table { width: 100%; margin-top: 18px; border: none; }
        .signature-table td { border: none; padding: 4px 12px; width: 33.33%; text-align: center; vertical-align: top; }
        .sig-line { border-top: 1px solid #0f172a; margin-top: 28px; padding-top: 3px; font-weight: bold; font-size: 7.5pt; }
        .sig-sub { font-size: 6.5pt; color: #64748b; }
        
        .footer { margin-top: 10px; font-size: 6.5pt; text-align: center; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 4px; }
    </style>
</head>
<body>
    @php
        $shadow = $claim->cf5ShadowBilling->first();
        if (!$shadow) {
            try {
                $shadow = app(\App\Services\Philhealth\Cf5ShadowBillingService::class)->calculate($claim);
            } catch (\Throwable $e) {
                $shadow = null;
            }
        }
        $drgWeight = $shadow?->drg_weight ?? 1.0;
        $baseRate = $shadow?->base_rate ?? 35000.00;
        $totalDrg = $shadow?->total_drg_amount ?? ($drgWeight * $baseRate);
        $hfAmount = $shadow?->hf_amount ?? ($totalDrg * 0.60);
        $pfAmount = $shadow?->pf_amount ?? ($totalDrg * 0.40);
        $acrAmount = $shadow?->actual_case_rate_amount ?? (float)($claim->caseRate?->total_rate ?? $claim->total_claimed_amount);
        $variance = $shadow?->difference_amount ?? ($totalDrg - $acrAmount);
        $variancePercent = $acrAmount > 0 ? (($variance / $acrAmount) * 100) : 0;
        $acrHf = $claim->caseRate ? (float)$claim->caseRate->hf_fee : ($acrAmount * 0.60);
        $acrPf = $claim->caseRate ? (float)$claim->caseRate->pf_fee : ($acrAmount * 0.40);
    @endphp

    <div class="header">
        <div class="rep">Republic of the Philippines</div>
        <div class="corp">Philippine Health Insurance Corporation</div>
        <h1>Claim Form 5 (CF5) — DRG Shadow Billing Statement</h1>
        <div class="meta">
            {{ config('philhealth.hospital.name') }} &bull; 
            HCI Accreditation No: {{ config('philhealth.hospital.code') }} &bull; 
            TIN: {{ config('philhealth.hospital.tin') }}
        </div>
        <div class="sub-badge">Transition to Diagnosis-Related Groups (DRG) &bull; Circular No. 2024-0006 & 2026-0012</div>
    </div>

    {{-- 1. IDENTIFYING INFORMATION --}}
    <div class="section-title">Part I &mdash; Claim & Patient Profile</div>
    <table>
        <tr>
            <th style="width:16%">Claim Code:</th>
            <td style="width:34%" class="fw-bold font-mono">{{ $claim->claim_code }}</td>
            <th style="width:16%">Encounter Type:</th>
            <td style="width:34%">{{ ucfirst($claim->encounter_type ?? 'Inpatient') }} ({{ $claim->claim_type }})</td>
        </tr>
        <tr>
            <th>Patient Name:</th>
            <td class="fw-bold">{{ $claim->patient?->last_name }}, {{ $claim->patient?->first_name }} {{ $claim->patient?->middle_name }} {{ $claim->patient?->suffix }}</td>
            <th>Patient Code:</th>
            <td class="font-mono">{{ $claim->patient?->patient_code ?? '—' }}</td>
        </tr>
        <tr>
            <th>PhilHealth PIN:</th>
            <td class="font-mono fw-bold">{{ $claim->philhealthMember?->pin ?? '—' }}</td>
            <th>Membership Type:</th>
            <td>{{ $claim->philhealthMember?->membership_type ?? 'Standard' }}</td>
        </tr>
        <tr>
            <th>Date Admitted:</th>
            <td>{{ $claim->date_admitted?->format('M d, Y') ?? '—' }} {{ $claim->time_admitted ? substr($claim->time_admitted, 0, 5) : '' }}</td>
            <th>Date Discharged:</th>
            <td>{{ $claim->date_discharged?->format('M d, Y') ?? '—' }} {{ $claim->time_discharged ? substr($claim->time_discharged, 0, 5) : '' }}</td>
        </tr>
        <tr>
            <th>Attending Physician:</th>
            <td>
                {{ $claim->attendingPhysician ? $claim->attendingPhysician->last_name . ', ' . $claim->attendingPhysician->first_name : 'Attending Physician' }}
                @if($claim->attendingPhysician?->license_number)
                    <span class="font-mono text-gray-500">(Lic. {{ $claim->attendingPhysician->license_number }})</span>
                @endif
            </td>
            <th>Discharge Outcome:</th>
            <td class="fw-bold">{{ $claim->outcome ?? 'Recovered' }}</td>
        </tr>
    </table>

    {{-- 2. CLINICAL & CODING ABSTRACT --}}
    <div class="section-title">Part II &mdash; Clinical & Diagnostic Classification</div>
    <table>
        <tr>
            <th style="width:20%">Admitting Diagnosis:</th>
            <td colspan="3">{{ $claim->admitting_diagnosis ?: 'None stated' }}</td>
        </tr>
        <tr>
            <th>Discharge Diagnosis:</th>
            <td colspan="3" class="fw-bold">{{ $claim->discharge_diagnosis ?: ($claim->icd10Code?->name ?? 'Clinical assessment pending') }}</td>
        </tr>
        <tr>
            <th style="width:20%">Principal ICD-10 Code:</th>
            <td style="width:30%" class="fw-bold font-mono">{{ $claim->icd10Code?->code ?? '—' }}</td>
            <th style="width:20%">ICD-10 Description:</th>
            <td style="width:30%">{{ $claim->icd10Code?->name ?? '—' }}</td>
        </tr>
        @if($claim->secondaryIcd10Code || $claim->tertiaryIcd10Code)
        <tr>
            <th>Secondary / Comorbid:</th>
            <td colspan="3">
                @if($claim->secondaryIcd10Code)
                    <span class="font-mono fw-bold">[{{ $claim->secondaryIcd10Code->code }}]</span> {{ $claim->secondaryIcd10Code->name }}
                @endif
                @if($claim->tertiaryIcd10Code)
                    &bull; <span class="font-mono fw-bold">[{{ $claim->tertiaryIcd10Code->code }}]</span> {{ $claim->tertiaryIcd10Code->name }}
                @endif
            </td>
        </tr>
        @endif
    </table>

    {{-- 3. DRG SHADOW BILLING COMPUTATION (CIRCULAR 2026-0012) --}}
    <div class="section-title">Part III &mdash; DRG Shadow Billing Calculation (Circular No. 2024-0006 & 2026-0012)</div>
    
    <table class="kpi-container" style="border:none;margin-bottom:6px;">
        <tr>
            <td style="width:25%;border:1px solid #bae6fd;background:#f0f9ff;padding:6px;border-radius:4px;">
                <div class="kpi-label" style="color:#0284c7;">Assigned DRG Code</div>
                <div class="kpi-val" style="color:#0369a1;">{{ $shadow?->drg_code ?? 'DRG-999' }}</div>
                <div class="kpi-sub">{{ Str::limit($shadow?->drg_description ?? 'Unclassified DRG Condition', 32) }}</div>
            </td>
            <td style="width:25%;border:1px solid #cbd5e1;background:#ffffff;padding:6px;border-radius:4px;">
                <div class="kpi-label">DRG Relative Weight (RW)</div>
                <div class="kpi-val">{{ number_format($drgWeight, 4) }}</div>
                <div class="kpi-sub">Clinical Case-Mix Index</div>
            </td>
            <td style="width:25%;border:1px solid #cbd5e1;background:#ffffff;padding:6px;border-radius:4px;">
                <div class="kpi-label">Base Rate (National)</div>
                <div class="kpi-val">&#x20B1;{{ number_format($baseRate, 2) }}</div>
                <div class="kpi-sub">Level 1-3 Standard Base</div>
            </td>
            <td style="width:25%;border:1px solid #bbf7d0;background:#f0fdf4;padding:6px;border-radius:4px;">
                <div class="kpi-label" style="color:#16a34a;">Total DRG Reimbursement</div>
                <div class="kpi-val" style="color:#15803d;">&#x20B1;{{ number_format($totalDrg, 2) }}</div>
                <div class="kpi-sub">Formula: Base Rate &times; RW</div>
            </td>
        </tr>
    </table>

    {{-- STATUTORY 60/40 BREAKDOWN AND ACR COMPARISON --}}
    <table>
        <thead>
            <tr>
                <th style="width:28%">Benefit Allocation</th>
                <th style="width:18%" class="text-right">DRG Rate (Shadow)</th>
                <th style="width:18%" class="text-right">ACR Baseline Rate</th>
                <th style="width:18%" class="text-right">Variance (&#x20B1;)</th>
                <th style="width:18%" class="text-center">Status / Variance %</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Health Care Facility Fee (60%)</strong><br><span style="font-size:7pt;color:#64748b;">Bundles room, board, routine drugs, labs, supplies</span></td>
                <td class="text-right font-mono fw-bold">&#x20B1;{{ number_format($hfAmount, 2) }}</td>
                <td class="text-right font-mono">&#x20B1;{{ number_format($acrHf, 2) }}</td>
                <td class="text-right font-mono {{ ($hfAmount - $acrHf) >= 0 ? 'text-green-700 fw-bold' : 'text-red-700 fw-bold' }}">
                    {{ ($hfAmount - $acrHf) >= 0 ? '+' : '' }}&#x20B1;{{ number_format($hfAmount - $acrHf, 2) }}
                </td>
                <td class="text-center font-mono">
                    {{ $acrHf > 0 ? number_format((($hfAmount - $acrHf) / $acrHf) * 100, 1) . '%' : 'N/A' }}
                </td>
            </tr>
            <tr>
                <td><strong>Professional Fee (40%)</strong><br><span style="font-size:7pt;color:#64748b;">Attending, co-attending, and surgeon fees</span></td>
                <td class="text-right font-mono fw-bold">&#x20B1;{{ number_format($pfAmount, 2) }}</td>
                <td class="text-right font-mono">&#x20B1;{{ number_format($acrPf, 2) }}</td>
                <td class="text-right font-mono {{ ($pfAmount - $acrPf) >= 0 ? 'text-green-700 fw-bold' : 'text-red-700 fw-bold' }}">
                    {{ ($pfAmount - $acrPf) >= 0 ? '+' : '' }}&#x20B1;{{ number_format($pfAmount - $acrPf, 2) }}
                </td>
                <td class="text-center font-mono">
                    {{ $acrPf > 0 ? number_format((($pfAmount - $acrPf) / $acrPf) * 100, 1) . '%' : 'N/A' }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;font-size:8.5pt;">
                <td class="fw-bold">Total Benefit Reimbursement</td>
                <td class="text-right font-mono fw-bold">&#x20B1;{{ number_format($totalDrg, 2) }}</td>
                <td class="text-right font-mono fw-bold">&#x20B1;{{ number_format($acrAmount, 2) }}</td>
                <td class="text-right font-mono fw-bold" style="color:{{ $variance >= 0 ? '#15803d' : '#b91c1c' }};">
                    {{ $variance >= 0 ? '+' : '' }}&#x20B1;{{ number_format($variance, 2) }}
                </td>
                <td class="text-center">
                    @if($variance >= 0)
                        <span class="badge-surplus">+{{ number_format($variancePercent, 1) }}% Surplus</span>
                    @else
                        <span class="badge-deficit">{{ number_format($variancePercent, 1) }}% Deficit</span>
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- PHARMACY DRG BUNDLING & ESOA NOTICE --}}
    <div class="notice-box">
        <strong>Official Notice on Pharmacy & DRG Bundling:</strong> Under the PhilHealth DRG Shadow Billing System (Circular No. 2024-0006 & 2026-0012), all inpatient routine medicines, intravenous fluids, supplies, and routine diagnostic examinations are <strong>bundled</strong> into the Health Care Facility Fee. Line-item charges are reflected in the Electronic Statement of Account (eSOA) and Claim Form 4 (CF4) for relative weight calibration, audit verification, and compliance monitoring.
    </div>

    {{-- 4. ITEMIZED CHARGES / eSOA SUMMARY BREAKDOWN --}}
    @if($claim->items->count() > 0)
    <div class="section-title">Part IV &mdash; Itemized Electronic Statement of Account (eSOA) Summary</div>
    <table>
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:16%">Category</th>
                <th style="width:14%">Item Code</th>
                <th>Description / Particulars</th>
                <th style="width:8%" class="text-right">Qty</th>
                <th style="width:13%" class="text-right">Unit Price</th>
                <th style="width:14%" class="text-right">Actual Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($claim->items->take(12) as $idx => $item)
            <tr>
                <td class="text-center font-mono">{{ $idx + 1 }}</td>
                <td>{{ $item->item_type }}</td>
                <td class="font-mono text-center">{{ $item->item_code ?? '—' }}</td>
                <td>{{ Str::limit($item->item_name, 40) }}</td>
                <td class="text-right font-mono">{{ number_format($item->quantity, 1) }}</td>
                <td class="text-right font-mono">&#x20B1;{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right font-mono">&#x20B1;{{ number_format($item->actual_amount, 2) }}</td>
            </tr>
            @endforeach
            @if($claim->items->count() > 12)
            <tr>
                <td colspan="7" class="text-center" style="font-size:7pt;color:#64748b;background:#f8fafc;">
                    &bull; Displaying top 12 of {{ $claim->items->count() }} items &bull; Complete itemized audit available in full eSOA XML export &bull;
                </td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background:#f1f5f9;font-weight:bold;">
                <td colspan="6" class="text-right">Total Hospital Actual Charges:</td>
                <td class="text-right font-mono">&#x20B1;{{ number_format($claim->total_actual_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- CONDITIONAL Z-BENEFIT TRANCHE SCHEDULE --}}
    @if($claim->zBenefits && $claim->zBenefits->count() > 0)
    <div class="section-title">Special &mdash; Z-Benefit Package Tranche Schedule</div>
    <table>
        <thead>
            <tr>
                <th style="width:20%">Z-Benefit Code</th>
                <th style="width:25%">Pre-Authorization No.</th>
                <th style="width:15%" class="text-center">Tranche No.</th>
                <th style="width:20%" class="text-right">Tranche Amount</th>
                <th style="width:20%" class="text-center">Billing Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($claim->zBenefits as $zb)
            <tr>
                <td class="fw-bold font-mono">{{ $zb->z_benefit_code }}</td>
                <td class="font-mono text-center">{{ $zb->pre_authorization_code ?? '—' }}</td>
                <td class="text-center fw-bold">{{ $zb->tranche_number }}</td>
                <td class="text-right font-mono fw-bold">&#x20B1;{{ number_format($zb->tranche_amount, 2) }}</td>
                <td class="text-center text-green-700 fw-bold">Active Tranche</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- CERTIFICATION AND SIGNATURES --}}
    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-line">
                    {{ $claim->attendingPhysician ? $claim->attendingPhysician->last_name . ', ' . $claim->attendingPhysician->first_name : 'ATTENDING PHYSICIAN' }}
                </div>
                <div class="sig-sub">Attending Physician (CF2 / CF4)<br>Lic. No: {{ $claim->attendingPhysician?->license_number ?? '__________' }}</div>
            </td>
            <td>
                <div class="sig-line">{{ config('philhealth.hospital.claims_officer', 'MEDICAL CLAIMS OFFICER') }}</div>
                <div class="sig-sub">Medical Claims / DRG Shadow Billing Officer<br>Verification & Coding Certification</div>
            </td>
            <td>
                <div class="sig-line">{{ $claim->hci_signatory_name ?: config('philhealth.hospital.medical_director', 'MEDICAL DIRECTOR / HCI HEAD') }}</div>
                <div class="sig-sub">Authorized HCI Representative (CF2)<br>Hospital Facility Administrator</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Claim Form 5 (CF5) DRG Shadow Billing Statement &bull; {{ config('philhealth.system.name') }} v{{ config('philhealth.system.version') }} &bull; Generated: {{ now()->format('M d, Y h:i A') }} &bull; Page 1 of 1
    </div>
</body>
</html>
