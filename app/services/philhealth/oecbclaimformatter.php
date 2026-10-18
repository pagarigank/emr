<?php

namespace App\Services\Philhealth;

use App\Models\PhilhealthClaim;
use App\Models\Er\ErAdmission;

class OecbClaimFormatter
{
    public function format(PhilhealthClaim $claim): string
    {
        $oecbAdmission = $this->findOecbAdmission($claim);

        if ($oecbAdmission && $oecbAdmission->oecbEntries->isNotEmpty()) {
            $presentations = $oecbAdmission->oecbEntries->pluck('core_presentation')->filter()->implode(', ');
            $triageLevel = $oecbAdmission->triageRecord?->acuityLevel?->name ?? null;

            $chief = 'Declared Complaint ' . $presentations;
            if ($triageLevel) {
                $chief .= ' [' . $triageLevel . ']';
            }
            return $chief;
        }

        return $claim->chief_complaint ?? '';
    }

    public function saveFormattedChiefComplaint(PhilhealthClaim $claim): void
    {
        $formatted = $this->format($claim);

        if ($formatted !== ($claim->chief_complaint ?? '')) {
            $claim->update([
                'chief_complaint' => $formatted,
                'updated_by' => request()->attributes->get('jwt_user')?->id ?? 1,
                'updated_date' => now(),
            ]);
        }
    }

    public function findOecbAdmission(PhilhealthClaim $claim): ?ErAdmission
    {
        return ErAdmission::with('oecbEntries', 'triageRecord.acuityLevel')
            ->where('patient_id', $claim->patient_id)
            ->where('is_oecb', true)
            ->where('history', 1)
            ->latest('admission_datetime')
            ->first();
    }
}
