<?php

namespace App\Livewire\Masters;

use App\Models\Gender;
use Livewire\Component;
use Livewire\WithPagination;

class GenderCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;
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
        $unique = 'unique:genders,code,NULL,id,history,1';
        if ($this->isEditing && $this->editingId)
            $unique = 'unique:genders,code,' . $this->editingId . ',id,history,1';
        return [
            'code' => ['required', 'string', 'max:20', $unique],
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'code' => 'Gender Code', 'name' => 'Gender Name',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field)
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function openCreate(): void { $this->resetForm(); $this->isEditing = false; $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $g = Gender::findOrFail($id);
        $this->editingId = $g->id; $this->code = $g->code; $this->name = $g->name;
        $this->is_active = $g->is_active; $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            Gender::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Gender updated successfully.');
        } else {
            Gender::create(array_merge($data, [
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Gender created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Gender::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Gender archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name']); $this->is_active = true; $this->resetValidation();
    }

    public function render()
    {
        $genders = Gender::query()
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.masters.gender-crud', compact('genders'));
    }
}
