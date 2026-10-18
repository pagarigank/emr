<?php

namespace App\Livewire\Masters;

use App\Models\Country;
use Livewire\Component;
use Livewire\WithPagination;

class CountryCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;

    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public bool $isEditing       = false;

    public ?int   $editingId = null;
    public string $code      = '';
    public string $name      = '';
    public bool   $is_active = true;

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $uniqueRule = 'unique:countries,code,NULL,id,history,1';
        if ($this->isEditing && $this->editingId) {
            $uniqueRule = 'unique:countries,code,' . $this->editingId . ',id,history,1';
        }
        return [
            'code'      => ['required', 'string', 'max:10', $uniqueRule],
            'name'      => ['required', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'code' => 'Country Code',
        'name' => 'Country Name',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $c = Country::findOrFail($id);
        $this->editingId = $c->id;
        $this->code      = $c->code;
        $this->name      = $c->name;
        $this->is_active = $c->is_active;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->isEditing) {
            Country::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by'   => $this->currentUserId(),
                'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Country updated successfully.');
        } else {
            Country::create(array_merge($data, [
                'history'      => 1,
                'created_by'   => $this->currentUserId(),
                'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Country created successfully.');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId       = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Country::findOrFail($this->editingId)->update([
            'history'      => 0,
            'updated_by'   => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Country archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal       = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $countries = Country::query()
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.masters.country-crud', compact('countries'));
    }
}
