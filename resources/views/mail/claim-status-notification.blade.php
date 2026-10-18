@php $isPositive = in_array($changeType, ['acknowledged', 'paid']); @endphp
<x-mail::message>
# PhilHealth Claim Status Update

**Claim:** {{ $claim->claim_code }}
**Patient:** {{ $claim->patient?->last_name }}, {{ $claim->patient?->first_name }}
**Status:** {{ $claim->status }}

<x-mail::panel>
@switch($changeType)
@case('acknowledged')
✅ **Claim Acknowledged**
Acknowledgment Code: {{ $details['acknowledgment_code'] ?? $claim->acknowledgment_code }}
@break
@case('paid')
💰 **Payment Received**
Voucher: {{ $details['voucher_number'] ?? $claim->voucher_number }}
Amount: ₱{{ number_format($details['voucher_amount'] ?? $claim->voucher_amount, 2) }}
@break
@case('denied')
❌ **Claim Denied**
Reason: {{ $details['denial_reason'] ?? 'No reason provided.' }}
@break
@case('resubmitted')
🔄 **Claim Resubmitted**
Status has been reset to For Submission.
@break
@endswitch
</x-mail::panel>

<x-mail::table>
| Field | Value |
|-------|-------|
| Claim Code | {{ $claim->claim_code }} |
| Patient | {{ $claim->patient?->last_name }}, {{ $claim->patient?->first_name }} |
| ICD-10 Code | {{ $claim->icd10Code?->code ?? '—' }} |
| Total Claimed | ₱{{ number_format($claim->total_claimed_amount, 2) }} |
| Status | {{ $claim->status }} |
</x-mail::table>

<x-mail::button :url="route('philhealth.claims')">
View in SITHEMR
</x-mail::button>

— {{ config('philhealth.system.name') }} v{{ config('philhealth.system.version') }}
</x-mail::message>
