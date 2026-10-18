<?php

namespace App\Livewire\Yakap;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\Opd\OpdConsultation;
use App\Models\Yakap\YakapEmpanelment;
use App\Models\Yakap\YakapFpeRecord;
use Livewire\Component;
use Livewire\WithPagination;

class FpeCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'fpe_datetime', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;

    public ?int $empanelment_id = null, $patient_id = null, $consultation_id = null, $physician_id = null;
    public string $fpe_datetime = '', $risk_classification = '';
    public ?int $risk_score = null;
    public string $smoking_status = '', $alcohol_use = '', $diet_type = '', $physical_activity_level = '';
    public string $family_history = '', $past_medical_history = '', $immunization_history = '', $obstetric_history = '';
    public string $height_cm = '', $weight_kg = '', $bmi = '', $waist_circumference_cm = '';
    public string $status = 'Completed', $notes = '';
    public string $empanelment_search = '';

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit(int $id): void
    {
        $r = YakapFpeRecord::findOrFail($id);
        $this->editingId = $r->id;
        $this->empanelment_id = $r->empanelment_id;
        $this->patient_id = $r->patient_id;
        $this->consultation_id = $r->consultation_id;
        $this->physician_id = $r->physician_id;
        $this->fpe_datetime = $r->fpe_datetime?->format('Y-m-d\TH:i') ?? '';
        $this->risk_classification = $r->risk_classification ?? '';
        $this->risk_score = $r->risk_score;
        $this->smoking_status = $r->smoking_status ?? '';
        $this->alcohol_use = $r->alcohol_use ?? '';
        $this->diet_type = $r->diet_type ?? '';
        $this->physical_activity_level = $r->physical_activity_level ?? '';
        $this->family_history = $r->family_history ?? '';
        $this->past_medical_history = $r->past_medical_history ?? '';
        $this->immunization_history = $r->immunization_history ?? '';
        $this->obstetric_history = $r->obstetric_history ?? '';
        $this->height_cm = (string) ($r->height_cm ?? '');
        $this->weight_kg = (string) ($r->weight_kg ?? '');
        $this->bmi = (string) ($r->bmi ?? '');
        $this->waist_circumference_cm = (string) ($r->waist_circumference_cm ?? '');
        $this->status = $r->status;
        $this->notes = $r->notes ?? '';
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();

        $this->validate([
            'empanelment_id' => ['required', 'integer', 'exists:yakap_empanelments,id'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'fpe_datetime' => ['required'],
            'risk_classification' => ['nullable', 'string', 'max:30'],
        ]);

        if ($this->height_cm && $this->weight_kg && !$this->bmi) {
            $h = (float) $this->height_cm / 100;
            $w = (float) $this->weight_kg;
            $this->bmi = $h > 0 ? number_format($w / ($h * $h), 1) : '0.0';
        }

        $data = [
            'tenant_id' => $tid,
            'empanelment_id' => $this->empanelment_id,
            'patient_id' => $this->patient_id,
            'consultation_id' => $this->consultation_id,
            'physician_id' => $this->physician_id,
            'fpe_datetime' => $this->fpe_datetime,
            'risk_classification' => $this->risk_classification ?: null,
            'risk_score' => $this->risk_score,
            'smoking_status' => $this->smoking_status ?: null,
            'alcohol_use' => $this->alcohol_use ?: null,
            'diet_type' => $this->diet_type ?: null,
            'physical_activity_level' => $this->physical_activity_level ?: null,
            'family_history' => $this->family_history ?: null,
            'past_medical_history' => $this->past_medical_history ?: null,
            'immunization_history' => $this->immunization_history ?: null,
            'obstetric_history' => $this->obstetric_history ?: null,
            'height_cm' => $this->height_cm !== '' ? (float) $this->height_cm : null,
            'weight_kg' => $this->weight_kg !== '' ? (float) $this->weight_kg : null,
            'bmi' => $this->bmi !== '' ? (float) $this->bmi : null,
            'waist_circumference_cm' => $this->waist_circumference_cm !== '' ? (float) $this->waist_circumference_cm : null,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ];

        if ($this->isEditing && $this->editingId) {
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            YakapFpeRecord::where('id', $this->editingId)->update($data);
            session()->flash('message', 'FPE record updated successfully.');
        } else {
            $data['created_by'] = $uid;
            $data['created_date'] = now();
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            YakapFpeRecord::create($data);

            // Mark empanelment FPE as completed
            YakapEmpanelment::where('id', $this->empanelment_id)->update([
                'fpe_completed' => true,
                'fpe_completed_at' => now(),
                'risk_classification' => $this->risk_classification ?: null,
                'updated_by' => $uid,
                'updated_date' => now(),
            ]);

            if ($this->consultation_id) {
                OpdConsultation::where('id', $this->consultation_id)->update([
                    'fpe_completed' => true,
                    'risk_classification' => $this->risk_classification ?: null,
                ]);
            }

            session()->flash('message', 'FPE record created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        YakapFpeRecord::where('id', $this->editingId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->showDeleteModal = false;
        $this->editingId = null;
        session()->flash('message', 'FPE record archived successfully.');
    }

    public function updatedEmpanelmentId(): void
    {
        if ($this->empanelment_id) {
            $emp = YakapEmpanelment::with('patient')->find($this->empanelment_id);
            if ($emp) {
                $this->patient_id = $emp->patient_id;
            }
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'empanelment_id', 'patient_id', 'consultation_id', 'physician_id',
            'fpe_datetime', 'risk_classification', 'risk_score', 'smoking_status', 'alcohol_use',
            'diet_type', 'physical_activity_level', 'family_history', 'past_medical_history',
            'immunization_history', 'obstetric_history', 'height_cm', 'weight_kg', 'bmi',
            'waist_circumference_cm', 'notes', 'empanelment_search']);
        $this->status = 'Completed';
    }

    public function render()
    {
        $tid = $this->currentTenantId();
        $query = YakapFpeRecord::with(['empanelment', 'patient', 'consultation', 'physician'])
            ->where('tenant_id', $tid);

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('patient', fn ($pq) => $pq->where('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('patient_code', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('empanelment', fn ($eq) => $eq->where('empanelment_code', 'like', '%' . $this->search . '%'));
            });
        }

        $records = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.yakap.fpe-crud', [
            'records' => $records,
            'empanelments' => $this->empanelment_search
                ? YakapEmpanelment::where('tenant_id', $tid)
                    ->where(function ($q) {
                        $q->where('empanelment_code', 'like', '%' . $this->empanelment_search . '%')
                          ->orWhereHas('patient', fn ($pq) => $pq->where('last_name', 'like', '%' . $this->empanelment_search . '%')
                              ->orWhere('first_name', 'like', '%' . $this->empanelment_search . '%'));
                    })
                    ->orderBy('empanelment_code')->get()
                : collect(),
            'doctors' => HealthCareProfessional::where('tenant_id', $tid)->where('is_active', true)->orderBy('full_name')->get(),
            'consultations' => OpdConsultation::where('tenant_id', $tid)->where('is_yakap', true)->orderBy('consultation_code')->get(),
        ]);
    }
}
