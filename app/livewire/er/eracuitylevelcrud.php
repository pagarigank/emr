<?php

namespace App\Livewire\Er;

use App\Models\ER\ErAcuityLevel;
use Livewire\Component;
use Livewire\WithPagination;

class ErAcuityLevelCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    
    public string $code = '', $name = '', $color_code = '', $description = '';
    public bool $is_active = true;

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $unique = 'unique:er_acuity_levels,code,NULL,id,history,1';
        if ($this->isEditing && $this->editingId)
            $unique = 'unique:er_acuity_levels,code,' . $this->editingId . ',id,history,1';
            
        return [
            'code' => ['required', 'string', 'max:20', $unique],
            'name' => ['required', 'string', 'max:100'],
            'color_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'code' => 'Acuity Code', 'name' => 'Acuity Name',
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
        $al = ErAcuityLevel::findOrFail($id);
        $this->editingId = $al->id; 
        $this->code = $al->code; 
        $this->name = $al->name;
        $this->color_code = $al->color_code ?? '';
        $this->description = $al->description ?? '';
        $this->is_active = $al->is_active; 
        $this->isEditing = true; 
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            ErAcuityLevel::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'ER Acuity Level updated successfully.');
        } else {
            ErAcuityLevel::create(array_merge($data, [
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'ER Acuity Level created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        ErAcuityLevel::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'ER Acuity Level archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'color_code', 'description']); 
        $this->is_active = true; 
        $this->resetValidation();
    }

    public function render()
    {
        $levels = ErAcuityLevel::query()
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.er.er-acuity-level-crud', compact('levels'));
    }
}
