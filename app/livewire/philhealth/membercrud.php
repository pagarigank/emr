<?php

namespace App\Livewire\Philhealth;

use App\Models\PhilhealthMember;
use Livewire\Component;
use Livewire\WithPagination;

class MemberCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'pin', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $pin = '', $last_name = '', $first_name = '', $middle_name = '', $suffix = '',
        $date_of_birth = '', $gender = '', $membership_type = '', $contact_number = '', $email = '',
        $mailing_address = '', $zip_code = '';
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
        $u = "unique:philhealth_members,pin,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId) $u = "unique:philhealth_members,pin,{$this->editingId},id,tenant_id,{$tid},history,1";
        return ['pin' => ['required', 'string', 'max:30', $u], 'last_name' => ['required', 'string', 'max:60'], 'first_name' => ['required', 'string', 'max:60'], 'middle_name' => ['nullable', 'string', 'max:60'], 'suffix' => ['nullable', 'string', 'max:20'], 'date_of_birth' => ['nullable', 'date'], 'gender' => ['nullable', 'string', 'max:10'], 'membership_type' => ['nullable', 'string', 'max:50'], 'contact_number' => ['nullable', 'string', 'max:30'], 'email' => ['nullable', 'string', 'max:100'], 'mailing_address' => ['nullable', 'string', 'max:255'], 'zip_code' => ['nullable', 'string', 'max:10'], 'is_active' => ['boolean']];
    }

    protected array $validationAttributes = ['pin' => 'PIN', 'last_name' => 'Last Name', 'first_name' => 'First Name', 'middle_name' => 'Middle Name', 'suffix' => 'Suffix', 'date_of_birth' => 'Date of Birth', 'gender' => 'Gender', 'membership_type' => 'Membership Type', 'contact_number' => 'Contact Number', 'mailing_address' => 'Mailing Address', 'zip_code' => 'Zip Code'];

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
        $r = PhilhealthMember::findOrFail($id);
        $this->editingId = $r->id; $this->pin = $r->pin; $this->last_name = $r->last_name;
        $this->first_name = $r->first_name; $this->middle_name = $r->middle_name; $this->suffix = $r->suffix;
        $this->date_of_birth = $r->date_of_birth ? $r->date_of_birth->format('Y-m-d') : '';
        $this->gender = $r->gender; $this->membership_type = $r->membership_type;
        $this->contact_number = $r->contact_number; $this->email = $r->email;
        $this->mailing_address = $r->mailing_address; $this->zip_code = $r->zip_code;
        $this->is_active = $r->is_active; $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            PhilhealthMember::findOrFail($this->editingId)->update(array_merge($data, ['updated_by' => $this->currentUserId(), 'updated_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'PhilHealth member updated successfully.');
        } else {
            PhilhealthMember::create(array_merge($data, ['tenant_id' => $this->currentTenantId(), 'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'PhilHealth member created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        PhilhealthMember::findOrFail($this->editingId)->update(['history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'PhilHealth member archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void { $this->reset(['editingId', 'pin', 'last_name', 'first_name', 'middle_name', 'suffix', 'date_of_birth', 'gender', 'membership_type', 'contact_number', 'email', 'mailing_address', 'zip_code']); $this->is_active = true; $this->resetValidation(); }

    public function render()
    {
        $items = PhilhealthMember::query()
            ->when($this->search, fn ($q) => $q->where(fn ($i) => $i->where('pin', 'like', '%' . $this->search . '%')->orWhere('last_name', 'like', '%' . $this->search . '%')->orWhere('first_name', 'like', '%' . $this->search . '%')))
            ->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        return view('livewire.philhealth.member-crud', compact('items'));
    }
}
