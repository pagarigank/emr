<?php

namespace App\Livewire\Masters;

use App\Models\Nationality;
use Livewire\Component;
use Livewire\WithPagination;

class NationalityCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '';
    public bool $is_active = true;

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $u = 'unique:nationalities,code,NULL,id,history,1';
        if ($this->isEditing && $this->editingId) $u = 'unique:nationalities,code,' . $this->editingId . ',id,history,1';
        return ['code' => ['required', 'string', 'max:20', $u], 'name' => ['required', 'string', 'max:100'], 'is_active' => ['boolean']];
    }

    protected array $validationAttributes = ['code' => 'Nationality Code', 'name' => 'Nationality Name'];

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
        $r = Nationality::findOrFail($id);
        $this->editingId = $r->id; $this->code = $r->code; $this->name = $r->name;
        $this->is_active = $r->is_active; $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            Nationality::findOrFail($this->editingId)->update(array_merge($data, ['updated_by' => $this->currentUserId(), 'updated_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'Nationality updated successfully.');
        } else {
            Nationality::create(array_merge($data, ['history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'Nationality created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Nationality::findOrFail($this->editingId)->update(['history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Nationality archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void { $this->reset(['editingId', 'code', 'name']); $this->is_active = true; $this->resetValidation(); }

    public function render()
    {
        $items = Nationality::query()
            ->when($this->search, fn ($q) => $q->where(fn ($i) => $i->where('code', 'like', '%' . $this->search . '%')->orWhere('name', 'like', '%' . $this->search . '%')))
            ->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        return view('livewire.masters.nationality-crud', compact('items'));
    }
}
