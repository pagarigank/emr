<?php

namespace App\Livewire\Pharmacy;

use App\Models\Patient;
use App\Models\Pharmacy\PharmacyRequest;
use Livewire\Component;

class PatientMedicationRecord extends Component
{
    public string $patient_search = '';
    public array $patientResults = [];
    public ?int $selectedPatientId = null;
    public ?string $selectedPatientName = null;

    public function updatedPatientSearch(): void
    {
        if (strlen($this->patient_search) < 2) {
            $this->patientResults = [];
            return;
        }
        $tid = request()->attributes->get('tenant_id') ?? 1;
        $this->patientResults = Patient::where('tenant_id', $tid)
            ->where(function ($q) {
                $q->where('last_name', 'like', '%' . $this->patient_search . '%')
                    ->orWhere('first_name', 'like', '%' . $this->patient_search . '%')
                    ->orWhere('patient_code', 'like', '%' . $this->patient_search . '%');
            })
            ->limit(10)
            ->get(['id', 'patient_code', 'last_name', 'first_name', 'middle_name'])
            ->toArray();
    }

    public function selectPatient(int $id): void
    {
        $p = Patient::find($id);
        if (!$p) {
            return;
        }
        $this->selectedPatientId = $p->id;
        $this->selectedPatientName = $p->patient_code . ' — ' . $p->last_name . ', ' . $p->first_name;
        $this->patient_search = '';
        $this->patientResults = [];
    }

    public function clearPatient(): void
    {
        $this->selectedPatientId = null;
        $this->selectedPatientName = null;
    }

    public function render()
    {
        $requests = collect();
        if ($this->selectedPatientId) {
            $requests = PharmacyRequest::with(['items.drug', 'requestingDepartment', 'requestingDoctor'])
                ->where('tenant_id', request()->attributes->get('tenant_id') ?? 1)
                ->whereHas('erAdmission', fn($q) => $q->where('patient_id', $this->selectedPatientId))
                ->orderBy('created_date', 'desc')
                ->get();
        }

        return view('livewire.pharmacy.patient-medication-record', compact('requests'));
    }
}
