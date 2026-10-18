<?php

namespace App\Livewire\Concerns;

use App\Models\Admission;
use App\Models\Delivery\DeliverySchedule;
use App\Models\Er\ErAdmission;
use App\Models\Ob\ObConsultation;
use App\Models\Opd\OpdConsultation;

trait ChecksActiveEncounters
{
    protected function ensureNoActiveEncounter(int $patientId): void
    {
        $tid = $this->currentTenantId();
        $active = [];

        if (ErAdmission::where('tenant_id', $tid)->where('patient_id', $patientId)->where('disposition', 'Pending')->exists()) {
            $active[] = 'ER Admission';
        }

        if (Admission::where('tenant_id', $tid)->where('patient_id', $patientId)->where('disposition', 'Pending')->exists()) {
            $active[] = 'Ward Admission';
        }

        if (OpdConsultation::where('tenant_id', $tid)->where('patient_id', $patientId)->whereNull('disposition')->exists()) {
            $active[] = 'OPD Consultation';
        }

        if (ObConsultation::where('tenant_id', $tid)->where('patient_id', $patientId)->where('disposition', 'Pending')->exists()) {
            $active[] = 'OB Consultation';
        }

        if (DeliverySchedule::where('tenant_id', $tid)->where('patient_id', $patientId)->where('disposition', 'Pending')->exists()) {
            $active[] = 'Delivery Schedule';
        }

        if (!empty($active)) {
            $list = implode(', ', $active);
            throw \Illuminate\Validation\ValidationException::withMessages([
                'patient_id' => "Patient already has active encounter(s): {$list}. Please dispose the existing encounter(s) first."
            ]);
        }
    }
}
