<?php

namespace App\Livewire\Transfer;

use App\Models\Hospital;
use App\Models\HospitalTransfer;
use App\Models\Patient;
use App\Models\HealthCareProfessional;
use Livewire\Component;
use Livewire\WithPagination;

class HospitalTransferCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_date';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;

    public string $patient_search = '';
    public ?int $patient_id = null;
    public ?int $destination_hospital_id = null;
    public string $source_type = '';
    public ?int $source_id = null;
    public string $transfer_reason = '';
    public string $transfer_diagnosis = '';
    public ?int $transferred_by = null;
    public string $accompanying_personnel = '';
    public string $transport_mode = '';
    public string $condition_on_transfer = '';
    public string $status = 'Pending';
    public string $notes = '';

    public array $sourceTypes = [];

    public function mount(): void
    {
        $this->sourceTypes = [
            '' => 'None',
            'er_admission' => 'ER Admission',
            'admission' => 'Ward Admission',
            'opd_consultation' => 'OPD Consultation',
            'or_schedule' => 'OR Schedule',
            'ob_consultation' => 'OB Consultation',
            'dialysis_session' => 'Dialysis Session',
            'dr_schedule' => 'Delivery Schedule',
        ];
    }

    private function currentUserId(): int
    {
        return auth()->user()?->id ?? 1;
    }

    private function getTenantId(): ?int
    {
        return request()->attributes->get('tenant_id');
    }

    protected function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'destination_hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'source_type' => ['nullable', 'string', 'max:50'],
            'source_id' => ['nullable', 'integer'],
            'transfer_reason' => ['nullable', 'string', 'max:500'],
            'transfer_diagnosis' => ['nullable', 'string', 'max:500'],
            'transferred_by' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'accompanying_personnel' => ['nullable', 'string', 'max:200'],
            'transport_mode' => ['nullable', 'string', 'max:50'],
            'condition_on_transfer' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected array $validationAttributes = [
        'patient_id' => 'Patient',
        'destination_hospital_id' => 'Destination Hospital',
        'source_type' => 'Source Type',
        'transfer_reason' => 'Transfer Reason',
        'transfer_diagnosis' => 'Transfer Diagnosis',
        'transferred_by' => 'Transferred By',
        'transport_mode' => 'Transport Mode',
        'condition_on_transfer' => 'Condition on Transfer',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field)
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->status = 'Pending';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $t = HospitalTransfer::findOrFail($id);
        $this->editingId = $t->id;
        $this->patient_id = $t->patient_id;
        $this->patient_search = $t->patient->full_name ?? '';
        $this->destination_hospital_id = $t->destination_hospital_id;
        $this->source_type = $t->source_type ?? '';
        $this->source_id = $t->source_id;
        $this->transfer_reason = $t->transfer_reason ?? '';
        $this->transfer_diagnosis = $t->transfer_diagnosis ?? '';
        $this->transferred_by = $t->transferred_by;
        $this->accompanying_personnel = $t->accompanying_personnel ?? '';
        $this->transport_mode = $t->transport_mode ?? '';
        $this->condition_on_transfer = $t->condition_on_transfer ?? '';
        $this->status = $t->status;
        $this->notes = $t->notes ?? '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function updatedPatientSearch(): void
    {
        $this->patient_id = null;
    }

    public function selectPatient(int $id): void
    {
        $patient = Patient::find($id);
        if ($patient) {
            $this->patient_id = $patient->id;
            $this->patient_search = $patient->full_name;
        }
    }

    private function generateTransferCode(): string
    {
        $tenantId = $this->getTenantId();
        $last = HospitalTransfer::where('tenant_id', $tenantId)
            ->where('transfer_code', 'like', 'HT-%')
            ->orderBy('id', 'desc')
            ->first();
        $nextId = $last ? ((int) substr($last->transfer_code, 3)) + 1 : 1;
        return 'HT-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    public function save(): void
    {
        $data = $this->validate();
        $tenantId = $this->getTenantId();
        $userId = $this->currentUserId();

        if ($this->isEditing) {
            HospitalTransfer::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $userId, 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Transfer updated successfully.');
        } else {
            $code = $this->generateTransferCode();
            HospitalTransfer::create(array_merge($data, [
                'transfer_code' => $code,
                'tenant_id' => $tenantId,
                'history' => 1,
                'transfer_datetime' => now(),
                'created_by' => $userId, 'created_date' => now(),
                'updated_by' => $userId, 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Transfer created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        HospitalTransfer::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Transfer record archived.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'patient_id', 'patient_search', 'destination_hospital_id',
            'source_type', 'source_id', 'transfer_reason', 'transfer_diagnosis', 'transferred_by',
            'accompanying_personnel', 'transport_mode', 'condition_on_transfer', 'notes']);
        $this->status = 'Pending';
        $this->resetValidation();
    }

    public function render()
    {
        $tenantId = $this->getTenantId();
        $records = HospitalTransfer::with(['patient', 'destinationHospital', 'transferredBy'])
            ->where('tenant_id', $tenantId)
            ->when($this->search, fn ($q) =>
                $q->whereHas('patient', fn ($p) =>
                    $p->where('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('patient_code', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $hospitals = Hospital::where('tenant_id', $tenantId)->where('is_active', true)->orderBy('name')->get();
        $patients = [];
        if (strlen($this->patient_search) >= 2) {
            $patients = Patient::where(function ($q) {
                $q->where('last_name', 'like', '%' . $this->patient_search . '%')
                  ->orWhere('first_name', 'like', '%' . $this->patient_search . '%')
                  ->orWhere('patient_code', 'like', '%' . $this->patient_search . '%');
            })->limit(10)->get();
        }
        $doctors = HealthCareProfessional::where('tenant_id', $tenantId)
            ->where('is_active', true)->orderBy('full_name')->get();

        return view('livewire.transfer.hospital-transfer-crud', compact('records', 'hospitals', 'patients', 'doctors'));
    }
}
