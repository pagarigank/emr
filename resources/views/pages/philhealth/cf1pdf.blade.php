<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CF1 — {{ $claim->claim_code }}</title>
    <style>
        @page { margin: 18mm 14mm; }
        body { font-family: 'Courier New', monospace; font-size: 9pt; color: #000; line-height: 1.35; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 13pt; margin: 0 0 3px; text-transform: uppercase; }
        .header p { margin: 1px 0; font-size: 8pt; }
        .title { text-align: center; font-size: 11pt; font-weight: bold; margin: 12px 0; text-transform: uppercase; letter-spacing: 1px; }
        .section-title { font-size: 9pt; font-weight: bold; margin: 14px 0 6px; padding: 3px 6px; background: #f0f0f0; border: 1px solid #000; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 8.5pt; }
        th, td { border: 1px solid #000; padding: 3px 5px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-weight: bold; font-size: 7.5pt; text-transform: uppercase; width: 32%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .mt-10 { margin-top: 10px; }
        .signature-row { margin-top: 24px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 45%; }
        .signature-box .line { border-top: 1px solid #000; margin-top: 28px; padding-top: 3px; font-size: 7pt; }
        .field-empty { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('philhealth.hospital.name') }}</h1>
        <p>HCI Code: {{ config('philhealth.hospital.code') }} | TIN: {{ config('philhealth.hospital.tin') }}</p>
        <p>{{ config('philhealth.hospital.contact_no') }} | {{ config('philhealth.hospital.email') }}</p>
    </div>

    <div class="title">CF1 — Member Registration / Claim Information</div>

    @php
        $member = $claim->philhealthMember;
        $patient = $claim->patient;
    @endphp

    {{-- Part I: Member Information --}}
    <div class="section-title">Part I — Member Information</div>
    <table>
        <tr><th>PhilHealth PIN</th><td>{{ $member?->pin ?? '—' }}</td></tr>
        <tr><th>Member Last Name</th><td>{{ $member?->last_name ?? '—' }}</td></tr>
        <tr><th>Member First Name</th><td>{{ $member?->first_name ?? '—' }}</td></tr>
        <tr><th>Member Middle Name</th><td>{{ $member?->middle_name ?? '—' }}</td></tr>
        <tr><th>Suffix</th><td>{{ $member?->suffix ?? '—' }}</td></tr>
        <tr><th>Date of Birth</th><td>{{ $member?->date_of_birth ? date('M d, Y', strtotime($member->date_of_birth)) : '—' }}</td></tr>
        <tr><th>Sex/Gender</th><td>{{ $member?->gender ?? '—' }}</td></tr>
        <tr><th>Membership Type</th><td>{{ $member?->membership_type ?? '—' }}</td></tr>
        <tr><th>Employer Name</th><td>{{ $member?->employer_name ?? '—' }}</td></tr>
        <tr><th>Employer Address</th><td>{{ $member?->employer_address ?? '—' }}</td></tr>
        <tr><th>Employer Contact No.</th><td>{{ $member?->employer_contact ?? '—' }}</td></tr>
        <tr><th>Contact Number</th><td>{{ $member?->contact_number ?? '—' }}</td></tr>
        <tr><th>Email Address</th><td>{{ $member?->email ?? '—' }}</td></tr>
        <tr><th>Mailing Address</th><td>{{ $member?->mailing_address ?? '—' }}</td></tr>
        <tr><th>Zip Code</th><td>{{ $member?->zip_code ?? '—' }}</td></tr>
    </table>

    {{-- Part II: Patient/Dependent Data --}}
    <div class="section-title">Part II — Patient / Dependent Data</div>
    <table>
        <tr><th>Patient Last Name</th><td>{{ $patient?->last_name ?? '—' }}</td></tr>
        <tr><th>Patient First Name</th><td>{{ $patient?->first_name ?? '—' }}</td></tr>
        <tr><th>Patient Middle Name</th><td>{{ $patient?->middle_name ?? '—' }}</td></tr>
        <tr><th>Suffix</th><td>{{ $patient?->suffix ?? '—' }}</td></tr>
        <tr><th>Date of Birth</th><td>{{ $patient?->date_of_birth ? date('M d, Y', strtotime($patient->date_of_birth)) : '—' }}</td></tr>
        <tr><th>Sex/Gender</th><td>{{ $patient?->gender?->name ?? $patient?->gender_id ?? '—' }}</td></tr>
        <tr><th>Relationship to Member</th><td>{{ $claim->relationship_to_member ?? 'Member (Self)' }}</td></tr>
        <tr><th>Civil Status</th><td>{{ $patient?->civilStatus?->name ?? '—' }}</td></tr>
        <tr><th>Address (Street)</th><td>{{ $patient?->address_street ?? '—' }}</td></tr>
        <tr><th>Barangay</th><td>{{ $patient?->barangay?->name ?? '—' }}</td></tr>
        <tr><th>City/Municipality</th><td>{{ $patient?->city?->name ?? '—' }}</td></tr>
        <tr><th>Province</th><td>{{ $patient?->province?->name ?? '—' }}</td></tr>
        <tr><th>Patient Contact No.</th><td>{{ $patient?->contact_no ?? '—' }}</td></tr>
    </table>

    {{-- Part III: Member Certification --}}
    <div class="section-title">Part III — Member Certification</div>
    <table>
        <tr><th>Member Printed Name</th><td>{{ $member?->full_name ?? $member?->first_name . ' ' . $member?->last_name ?? '—' }}</td></tr>
        <tr><th>Signature Date</th><td>{{ $claim->patient_signature_date?->format('M d, Y') ?? '—' }}</td></tr>
    </table>

    {{-- Part IV: Employer Certification --}}
    <div class="section-title">Part IV — Employer Certification</div>
    <table>
        <tr><th>Employer Name</th><td>{{ $member?->employer_name ?? '—' }}</td></tr>
        <tr><th>Employer Address</th><td>{{ $member?->employer_address ?? '—' }}</td></tr>
        <tr><th>Employer Contact No.</th><td>{{ $member?->employer_contact ?? '—' }}</td></tr>
        <tr><th>Date Signed</th><td>{{ $member?->employer_signature_date ? date('M d, Y', strtotime($member->employer_signature_date)) : '—' }}</td></tr>
    </table>

    <div class="signature-row">
        <div class="signature-box">
            <div class="line">Member / Patient Signature</div>
        </div>
        <div class="signature-box">
            <div class="line">Employer / Authorized Representative Signature</div>
        </div>
    </div>
    <div class="signature-row">
        <div class="signature-box">
            <div class="line">Date Printed: {{ now()->format('M d, Y h:i A') }}</div>
        </div>
        <div class="signature-box">
            <div class="line">Prepared By / Encoder</div>
        </div>
    </div>

    <p style="margin-top:12px;font-size:6.5pt;text-align:center;color:#666;">
        This is a computer-generated document. No signature is required.<br>
        {{ config('philhealth.system.name') }} v{{ config('philhealth.system.version') }} — CF1 Member Registration
    </p>
</body>
</html>
