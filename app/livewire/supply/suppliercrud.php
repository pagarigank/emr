<?php

namespace App\Livewire\Supply;

use App\Models\Supply\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $contact_person = '', $contact_number = '', $email = '', $address = '', $tin = '', $payment_terms = '';
    public bool $is_active = true;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:suppliers,code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:suppliers,code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'code'           => ['required', 'string', 'max:20', $u],
            'name'           => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email'          => ['nullable', 'email', 'max:100'],
            'address'        => ['nullable', 'string', 'max:255'],
            'tin'            => ['nullable', 'string', 'max:30'],
            'payment_terms'  => ['nullable', 'string', 'max:100'],
            'is_active'      => ['boolean'],
        ];
    }

    protected array $validationAttributes = ['code' => 'Supplier Code', 'name' => 'Supplier Name'];

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
        $r = Supplier::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->contact_person = $r->contact_person ?? ''; $this->contact_number = $r->contact_number ?? '';
        $this->email = $r->email ?? ''; $this->address = $r->address ?? '';
        $this->tin = $r->tin ?? ''; $this->payment_terms = $r->payment_terms ?? '';
        $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            Supplier::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Supplier updated successfully.');
        } else {
            Supplier::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
                'updated_by' => $this->currentUserId(), 'updated_date' => null,
            ]));
            $this->dispatch('toast', type: 'success', message: 'Supplier created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Supplier::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Supplier archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'contact_person', 'contact_number', 'email', 'address', 'tin', 'payment_terms']);
        $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $items = Supplier::query()
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                          ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.supply.supplier-crud', compact('items'));
    }
}
