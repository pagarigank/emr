<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EECL Summary — {{ $claim->claim_code }}</title>
    <style>
        @page { margin: 16mm 12mm; }
        body { font-family: 'Courier New', monospace; font-size: 8.5pt; color: #000; line-height: 1.3; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin-bottom: 10px; }
        .header h1 { font-size: 12pt; margin: 0 0 2px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 1px 0; font-size: 7.5pt; }
        .form-title { text-align: center; font-size: 11pt; font-weight: bold; margin: 10px 0 4px; text-transform: uppercase; letter-spacing: 1px; border: 1.5px solid #000; padding: 5px; }
        .form-subtitle { text-align: center; font-size: 7.5pt; margin: 2px 0 8px; color: #333; font-style: italic; }
        .pc-ref { text-align: center; font-size: 7pt; color: #666; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 8pt; }
        th, td { border: 1px solid #000; padding: 2.5px 4px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-weight: bold; font-size: 7pt; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .section-label { font-size: 8pt; font-weight: bold; margin: 8px 0 3px; padding: 2px 4px; background: #e8e8e8; border: 1px solid #000; text-transform: uppercase; }
        .narrative-box { border: 1px solid #000; padding: 4px 6px; min-height: 24px; margin: 2px 0; font-size: 8pt; }
        .items-table th { font-size: 6.5pt; padding: 2px 3px; }
        .items-table td { font-size: 7.5pt; padding: 2px 3px; }
        .items-table tfoot td { font-weight: bold; border-top: 2px solid #000; }
        .amt-col { text-align: right; font-family: 'Courier New', monospace; }
        .triage-badge { display: inline-block; padding: 1px 6px; border: 1px solid #000; font-size: 7pt; font-weight: bold; margin-left: 4px; }
        .triage-1 { background: #fee2e2; }
        .triage-2 { background: #fed7aa; }
        .triage-3 { background: #fef08a; }
        .triage-4 { background: #d9f99d; }
        .triage-5 { background: #e0f2fe; }
        .cert-box { border: 1px solid #000; padding: 6px; margin-top: 10px; font-size: 7.5pt; }
        .cert-box p { margin: 2px 0; }
        .signature-row { margin-top: 18px; display: flex; justify-content: space-between; gap: 12px; }
        .signature-box { text-align: center; width: 45%; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 24px; padding-top: 3px; font-size: 6.5pt; }
        .footer { margin-top: 10px; font-size: 6pt; text-align: center; color: #888; border-top: 1px solid #ccc; padding-top: 4px; }
        .eecl-header { background: #f0f7ff; }
        .total-row td { background: #f8f8f8; font-weight: bold; font-size: 8.5pt; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('philhealth.hospital.name') }}</h1>
        <p>HCI Code: {{ config('philhealth.hospital.code') }} | TIN: {{ config('philhealth.hospital.tin') }}</p>
        <p>{{ config('philhealth.hospital.address') }} | {{ config('philhealth.hospital.contact_no') }} | {{ config('philhealth.hospital.email') }}</p>
    </div>

    <div class="form-title">EECL Summary Form — Annex D</div>
    <div class="form-subtitle">Essential Emergency Care List (EECL) Summary of Services Provided</div>
    <div class="pc-ref">Per PhilHealth Circular No. 2024-0033 (Outpatient Emergency Care Benefit — OECB-FBE)</div>

    @php
        $patient = $claim->patient;
        $erAdmission = $claim->erAdmission;
        $oecbEntries = $erAdmission?->oecbEntries?->where('history', 1) ?? collect();
        $corePresentations = $oecbEntries->pluck('core_presentation')->filter()->implode(', ');
        $totalFixedFees = $oecbEntries->sum('total_amount') ?: $oecbEntries->sum('oecb_fixed_fee');
    @endphp

    {{-- PART I: HCI & Patient Information --}}
    <div class="section-label">Part I — Healthcare Institution & Patient Information</div>
    <table>
        <tr>
            <th style="width:18%">HCI Name</th>
            <td style="width:32%">{{ config('philhealth.hospital.name') }}</td>
            <th style="width:18%">HCI Code</th>
            <td style="width:32%">{{ config('philhealth.hospital.code') }}</td>
        </tr>
        <tr>
            <th>Patient Name</th>
            <td>{{ $patient?->last_name }}, {{ $patient?->first_name }} {{ $patient?->middle_name }} {{ $patient?->suffix }}</td>
            <th>Patient Code</th>
            <td>{{ $patient?->patient_code ?? '—' }}</td>
        </tr>
        <tr>
            <th>PhilHealth PIN</th>
            <td>{{ $claim->philhealthMember?->pin ?? '—' }}</td>
            <th>Membership Type</th>
            <td>{{ $claim->philhealthMember?->membership_type ?? '—' }}</td>
        </tr>
        <tr>
            <th>Date of Birth</th>
            <td>{{ $patient?->date_of_birth?->format('M d, Y') ?? '—' }}</td>
            <th>Sex</th>
            <td>{{ $patient?->gender ?? $claim->gender ?? '—' }}</td>
        </tr>
        <tr>
            <th>Relationship to Member</th>
            <td>{{ $claim->relationship_to_member ?? 'Self' }}</td>
            <th>Patient Type</th>
            <td>{{ $claim->encounter_type ?? 'Outpatient' }}</td>
        </tr>
    </table>

    {{-- PART II: ER Encounter Details --}}
    <div class="section-label">Part II — Emergency Room Encounter Details</div>
    <table>
        <tr>
            <th style="width:18%">ER Admission No.</th>
            <td style="width:32%">{{ $erAdmission?->admission_code ?? '—' }}</td>
            <th style="width:18%">eClaims Reference No.</th>
            <td style="width:32%">{{ $claim->claim_code }}</td>
        </tr>
        <tr>
            <th>Date/Time of Encounter</th>
            <td>{{ $erAdmission?->admission_datetime?->format('M d, Y g:i A') ?? '—' }}</td>
            <th>Date/Time of Disposition</th>
            <td>{{ $erAdmission?->disposition_datetime?->format('M d, Y g:i A') ?? '—' }}</td>
        </tr>
        <tr>
            <th>Attending Physician</th>
            <td>{{ $claim->attendingPhysician?->full_name ?? $erAdmission?->attendingPhysician?->full_name ?? '—' }}</td>
            <th>Disposition</th>
            <td>{{ $erAdmission?->disposition ?? $claim->outcome ?? '—' }}</td>
        </tr>
        <tr>
            <th>Mode of Arrival</th>
            <td>{{ $erAdmission?->mode_of_arrival ?? '—' }}</td>
            <th>Triage Level</th>
            <td>
                @php
                    $triageLevel = $erAdmission?->triageRecord?->acuityLevel?->name ?? null;
                @endphp
                @if($triageLevel)
                    {{ $triageLevel }}
                @else
                    —
                @endif
            </td>
        </tr>
    </table>

    {{-- PART III: Core Presentation & Diagnosis --}}
    <div class="section-label">Part III — Core Presentation & Diagnosis</div>
    <table>
        <tr>
            <th style="width:18%">Core Presentation</th>
            <td colspan="3">
                @if($corePresentations)
                    <strong>{{ $corePresentations }}</strong>
                @else
                    {{ $claim->chief_complaint ?? '—' }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Chief Complaint</th>
            <td colspan="3">{{ app(\App\Services\Philhealth\OecbClaimFormatter::class)->format($claim) ?: '—' }}</td>
        </tr>
        <tr>
            <th>Admitting Diagnosis</th>
            <td>{{ $claim->admitting_diagnosis ?? '—' }}</td>
            <th>ICD-10 Code</th>
            <td>{{ $claim->icd10Code?->code ?? '—' }}</td>
        </tr>
        <tr>
            <th>Discharge Diagnosis</th>
            <td colspan="3">{{ $claim->discharge_diagnosis ?? $claim->admitting_diagnosis ?? '—' }}</td>
        </tr>
        <tr>
            <th>Package Code</th>
            <td><strong>OPER1</strong></td>
            <th>Claim Type</th>
            <td>OECB — Outpatient Emergency Care Benefit</td>
        </tr>
    </table>

    {{-- PART IV: Clinical Information --}}
    <div class="section-label">Part IV — Clinical Information</div>
    <table>
        <tr>
            <th style="width:18%">History of Present Illness</th>
            <td><div class="narrative-box">{{ $claim->history_of_present_illness ?? $erAdmission?->history_of_present_illness ?? '—' }}</div></td>
        </tr>
        <tr>
            <th>Physical Exam Findings</th>
            <td><div class="narrative-box">{{ $claim->physical_exam_findings ?? $erAdmission?->physical_exam_findings ?? '—' }}</div></td>
        </tr>
    </table>

    <table style="margin-top:3px;">
        <tr>
            <th style="width:25%; text-align:center;">Blood Pressure</th>
            <th style="width:25%; text-align:center;">Heart Rate</th>
            <th style="width:25%; text-align:center;">Temperature</th>
            <th style="width:25%; text-align:center;">Respiratory Rate</th>
        </tr>
        <tr>
            <td class="text-center fw-bold">{{ $claim->blood_pressure ?? '—' }}</td>
            <td class="text-center fw-bold">{{ $claim->heart_rate ?? '—' }}</td>
            <td class="text-center fw-bold">{{ $claim->temperature ?? '—' }}</td>
            <td class="text-center fw-bold">{{ $claim->respiratory_rate ?? '—' }}</td>
        </tr>
    </table>

    {{-- PART V: OECB Fee Summary --}}
    <div class="section-label">Part V — OECB Fee Summary (per Core Presentation)</div>
    @if($oecbEntries->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:25%">Core Presentation</th>
                <th style="width:20%">Package Code</th>
                <th style="width:20%; text-align:right;">Fixed Fee</th>
                <th style="width:10%; text-align:center;">Qty</th>
                <th style="width:20%; text-align:right;">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($oecbEntries as $idx => $entry)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $entry->core_presentation ?? '—' }}</td>
                <td class="text-center">{{ $entry->oecb_package_code ?? '—' }}</td>
                <td class="amt-col">&#x20B1;{{ number_format($entry->oecb_fixed_fee ?? 0, 2) }}</td>
                <td class="text-center">{{ number_format($entry->quantity ?? 1, 0) }}</td>
                <td class="amt-col">&#x20B1;{{ number_format($entry->total_amount ?? ($entry->oecb_fixed_fee ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">Total OECB Fixed Fee:</td>
                <td class="amt-col">&#x20B1;{{ number_format($totalFixedFees, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <p style="color:#666;font-style:italic;font-size:7.5pt;">No OECB entries recorded for this encounter.</p>
    @endif

    {{-- PART VI: Services & Items Provided (from claim items) --}}
    @if($claim->items->count() > 0)
    <div class="section-label">Part VI — Services, Medicines, & Supplies Provided</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:12%">Type</th>
                <th style="width:9%">Code</th>
                <th>Description</th>
                <th style="width:5%">Qty</th>
                <th style="width:8%">Unit</th>
                <th style="width:10%">Fixed Fee</th>
                <th style="width:10%">Actual Amt</th>
                <th style="width:10%">Claimed Amt</th>
            </tr>
        </thead>
        <tbody>
            @foreach($claim->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $item->item_type ?? '—' }}</td>
                <td class="text-center">{{ $item->item_code ?? '—' }}</td>
                <td>{{ $item->item_name ?? '—' }}</td>
                <td class="text-right">{{ number_format($item->quantity ?? 1, 2) }}</td>
                <td class="text-center">{{ $item->unit ?? '—' }}</td>
                <td class="amt-col">{{ $item->unit_price ? '&#x20B1;' . number_format($item->unit_price, 2) : '—' }}</td>
                <td class="amt-col">&#x20B1;{{ number_format($item->actual_amount ?? 0, 2) }}</td>
                <td class="amt-col">&#x20B1;{{ number_format($item->claimed_amount ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right">Totals:</td>
                <td class="amt-col"></td>
                <td class="amt-col">&#x20B1;{{ number_format($claim->total_actual_amount ?? 0, 2) }}</td>
                <td class="amt-col">&#x20B1;{{ number_format($claim->total_claimed_amount ?? 0, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    {{-- PART VII: Certification --}}
    <div class="section-label">Part VII — Certification & Attestation</div>
    <div class="cert-box">
        <p>I hereby certify that the above patient was seen and treated at the Emergency Department of
        <strong>{{ config('philhealth.hospital.name') }}</strong> under the Outpatient Emergency Care Benefit (OECB)
        package. All services, medicines, and supplies listed above were medically necessary and actually provided
        to the patient.</p>
        <p style="margin-top:4px;">This summary is submitted in support of the PhilHealth eClaims filing under
        <strong>Package Code: OPER1</strong> in accordance with PhilHealth Circular No. 2024-0033 and its
        implementing guidelines.</p>
    </div>

    <div class="signature-row">
        <div class="signature-box">
            <div class="line">Attending Physician — Signature Over Printed Name</div>
        </div>
        <div class="signature-box">
            <div class="line">Date</div>
        </div>
    </div>

    <div class="signature-row">
        <div class="signature-box">
            <div class="line">HCI Authorized Signatory — Signature Over Printed Name</div>
        </div>
        <div class="signature-box">
            <div class="line">Date</div>
        </div>
    </div>

    <div class="footer">
        This is a computer-generated document. No signature is required unless otherwise indicated.<br>
        {{ config('philhealth.system.name') }} v{{ config('philhealth.system.version') }} — EECL Summary Form (Annex D)<br>
        Generated: {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
