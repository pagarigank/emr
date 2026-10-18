<?php

namespace App\Livewire\Yakap;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\Opd\OpdClinic;
use App\Models\Opd\OpdConsultation;
use App\Models\Yakap\YakapEmpanelment;
use Livewire\Component;
use Livewire\WithPagination;

class EmpanelmentCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'empanelment_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;

    public string $empanelment_code = '', $empanelment_year = '', $empanelment_date = '', $yes_signed_date = '', $yes_reference_no = '';
    public string $status = 'Active', $risk_classification = '';
    public ?int $patient_id = null, $philhealth_member_id = null, $primary_care_provider_id = null, $opd_clinic_id = null;
    public string $patient_search = '';
    public bool $is_active = true;

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
        $e = YakapEmpanelment::findOrFail($id);
        $this->editingId = $e->id;
        $this->empanelment_code = $e->empanelment_code;
        $this->patient_id = $e->patient_id;
        $this->philhealth_member_id = $e->philhealth_member_id;
        $this->empanelment_year = (string) $e->empanelment_year;
        $this->status = $e->status;
        $this->empanelment_date = $e->empanelment_date?->format('Y-m-d') ?? '';
        $this->yes_signed_date = $e->yes_signed_date?->format('Y-m-d') ?? '';
        $this->yes_reference_no = $e->yes_reference_no ?? '';
        $this->primary_care_provider_id = $e->primary_care_provider_id;
        $this->opd_clinic_id = $e->opd_clinic_id;
        $this->risk_classification = $e->risk_classification ?? '';
        $this->is_active = $e->is_active;
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();

        $this->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'empanelment_year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'empanelment_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:30'],
            'primary_care_provider_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'opd_clinic_id' => ['nullable', 'integer', 'exists:opd_clinics,id'],
        ]);

        $data = [
            'tenant_id' => $tid,
            'patient_id' => $this->patient_id,
            'philhealth_member_id' => $this->philhealth_member_id,
            'empanelment_year' => (int) $this->empanelment_year,
            'status' => $this->status,
            'empanelment_date' => $this->empanelment_date,
            'yes_signed_date' => $this->yes_signed_date ?: null,
            'yes_reference_no' => $this->yes_reference_no ?: null,
            'primary_care_provider_id' => $this->primary_care_provider_id,
            'opd_clinic_id' => $this->opd_clinic_id,
            'risk_classification' => $this->risk_classification ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditing && $this->editingId) {
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            YakapEmpanelment::where('id', $this->editingId)->update($data);
            session()->flash('message', 'Empanelment updated successfully.');
        } else {
            $data['empanelment_code'] = $this->generateEmpanelmentCode($tid);
            $data['created_by'] = $uid;
            $data['created_date'] = now();
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            YakapEmpanelment::create($data);
            session()->flash('message', 'Empanelment created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function generateEmpanelmentCode(int $tid): string
    {
        $last = YakapEmpanelment::where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->empanelment_code, 4)) + 1 : 1;
        return 'YAK-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        YakapEmpanelment::where('id', $this->editingId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->showDeleteModal = false;
        $this->editingId = null;
        session()->flash('message', 'Empanelment archived successfully.');
    }

    public function markFpeCompleted(int $id): void
    {
        $e = YakapEmpanelment::findOrFail($id);
        $e->update([
            'fpe_completed' => true,
            'fpe_completed_at' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        session()->flash('message', 'FPE marked as completed.');
    }

    public function updatedPatientId(): void
    {
        if ($this->patient_id) {
            $patient = Patient::with('philhealthMember')->find($this->patient_id);
            if ($patient && $patient->philhealthMember) {
                $this->philhealth_member_id = $patient->philhealthMember->id;
            }
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'empanelment_code', 'patient_id', 'philhealth_member_id',
            'empanelment_year', 'status', 'empanelment_date', 'yes_signed_date',
            'yes_reference_no', 'primary_care_provider_id', 'opd_clinic_id',
            'risk_classification', 'is_active', 'patient_search']);
        $this->status = 'Active';
        $this->is_active = true;
    }

    public function render()
    {
        $tid = $this->currentTenantId();
        $query = YakapEmpanelment::with(['patient', 'philhealthMember', 'primaryCareProvider', 'opdClinic'])
            ->where('tenant_id', $tid);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('empanelment_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('patient', fn ($pq) => $pq->where('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('patient_code', 'like', '%' . $this->search . '%'));
            });
        }

        $empanelments = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.yakap.empanelment-crud', [
            'empanelments' => $empanelments,
            'patients' => $this->patient_search
                ? Patient::where('tenant_id', $tid)->where(function ($q) {
                    $q->where('last_name', 'like', '%' . $this->patient_search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->patient_search . '%')
                      ->orWhere('patient_code', 'like', '%' . $this->patient_search . '%');
                })->orderBy('last_name')->get()
                : collect(),
            'doctors' => HealthCareProfessional::where('tenant_id', $tid)->where('is_active', true)->orderBy('full_name')->get(),
            'clinics' => OpdClinic::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
