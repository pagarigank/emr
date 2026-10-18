<?php

namespace App\Livewire\Philhealth;

use App\Models\Department;
use App\Models\HealthCareProfessional;
use Livewire\Component;
use Livewire\WithPagination;

class HcpCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'professional_code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $professional_code = '', $professional_type = 'Doctor', $full_name = '', $last_name = '', $first_name = '', $middle_name = '', $suffix = '', $specialization = '', $prc_license = '', $philhealth_accreditation = '', $contact_number = '', $email = '', $tin = '';
    public ?string $date_of_birth = null;
    public ?int $department_id = null;
    public bool $is_active = true;

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:health_care_professionals,professional_code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId) $u = "unique:health_care_professionals,professional_code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'professional_code' => ['required', 'string', 'max:30', $u],
            'professional_type' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'middle_name' => ['nullable', 'string', 'max:50'],
            'suffix' => ['nullable', 'string', 'max:10'],
            'date_of_birth' => ['nullable', 'date'],
            'tin' => ['nullable', 'string', 'max:30'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'prc_license' => ['nullable', 'string', 'max:50'],
            'philhealth_accreditation' => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'professional_code' => 'Professional Code',
        'professional_type' => 'Type',
        'last_name' => 'Last Name',
        'first_name' => 'First Name',
        'middle_name' => 'Middle Name',
        'suffix' => 'Suffix',
        'date_of_birth' => 'Date of Birth',
        'tin' => 'TIN',
        'specialization' => 'Specialization',
        'prc_license' => 'PRC License',
        'philhealth_accreditation' => 'PhilHealth Accreditation',
        'department_id' => 'Department',
        'contact_number' => 'Contact Number',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function openCreate(): void { $this->resetForm(); $this->isEditing = false; $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $r = HealthCareProfessional::with('department')->findOrFail($id);
        $this->editingId = $r->id;
        $this->professional_code = $r->professional_code;
        $this->professional_type = $r->professional_type;
        $this->full_name = $r->full_name;
        $this->last_name = $r->last_name ?? '';
        $this->first_name = $r->first_name ?? '';
        $this->middle_name = $r->middle_name ?? '';
        $this->suffix = $r->suffix ?? '';
        $this->date_of_birth = $r->date_of_birth ? $r->date_of_birth->format('Y-m-d') : null;
        $this->tin = $r->tin ?? '';
        $this->specialization = $r->specialization;
        $this->prc_license = $r->prc_license;
        $this->philhealth_accreditation = $r->philhealth_accreditation;
        $this->department_id = $r->department_id;
        $this->contact_number = $r->contact_number;
        $this->email = $r->email;
        $this->is_active = $r->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        
        $parts = array_filter([$data['last_name'] . ',', $data['first_name'], $data['middle_name'], $data['suffix']]);
        $data['full_name'] = implode(' ', $parts);
        
        if ($this->isEditing) {
            HealthCareProfessional::findOrFail($this->editingId)->update(array_merge($data, ['updated_by' => $this->currentUserId(), 'updated_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'Professional updated successfully.');
        } else {
            HealthCareProfessional::create(array_merge($data, ['tenant_id' => $this->currentTenantId(), 'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'Professional created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        HealthCareProfessional::findOrFail($this->editingId)->update(['history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Professional archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'professional_code', 'full_name', 'last_name', 'first_name', 'middle_name', 'suffix', 'date_of_birth', 'tin', 'specialization', 'prc_license',
            'philhealth_accreditation', 'department_id', 'contact_number', 'email',
        ]);
        $this->professional_type = 'Doctor';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $items = HealthCareProfessional::query()
            ->with('department')
            ->when($this->search, fn ($q) => $q->where(fn ($i) => $i
                ->where('professional_code', 'like', '%' . $this->search . '%')
                ->orWhere('full_name', 'like', '%' . $this->search . '%')
                ->orWhere('specialization', 'like', '%' . $this->search . '%')
                ->orWhere('prc_license', 'like', '%' . $this->search . '%')
                ->orWhere('professional_type', 'like', '%' . $this->search . '%')
            ))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $departments = Department::where('is_active', 1)->orderBy('dept_name')->get();

        return view('livewire.philhealth.hcp-crud', compact('items', 'departments'));
    }
}
