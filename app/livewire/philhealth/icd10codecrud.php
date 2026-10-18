<?php

namespace App\Livewire\Philhealth;

use App\Models\Icd10Code;
use Livewire\Component;
use Livewire\WithPagination;

class Icd10CodeCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $category = '';
    public bool $is_active = true;

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $u = 'unique:icd10_codes,code,NULL,id,history,1';
        if ($this->isEditing && $this->editingId) $u = 'unique:icd10_codes,code,' . $this->editingId . ',id,history,1';
        return ['code' => ['required', 'string', 'max:10', $u], 'name' => ['required', 'string', 'max:255'], 'category' => ['nullable', 'string', 'max:100'], 'is_active' => ['boolean']];
    }

    protected array $validationAttributes = ['code' => 'ICD-10 Code', 'name' => 'Diagnosis Description', 'category' => 'Category'];

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
        $r = Icd10Code::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name; $this->category = $r->category;
        $this->is_active = $r->is_active; $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            Icd10Code::findOrFail($this->editingId)->update(array_merge($data, ['updated_by' => $this->currentUserId(), 'updated_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'ICD-10 code updated successfully.');
        } else {
            Icd10Code::create(array_merge($data, ['history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'ICD-10 code created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Icd10Code::findOrFail($this->editingId)->update(['history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'ICD-10 code archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void { $this->reset(['editingId', 'code', 'name', 'category']); $this->is_active = true; $this->resetValidation(); }

    public function render()
    {
        $items = Icd10Code::query()
            ->when($this->search, fn ($q) => $q->where(fn ($i) => $i->where('code', 'like', '%' . $this->search . '%')->orWhere('name', 'like', '%' . $this->search . '%')->orWhere('category', 'like', '%' . $this->search . '%')))
            ->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        return view('livewire.philhealth.icd10-code-crud', compact('items'));
    }
}
