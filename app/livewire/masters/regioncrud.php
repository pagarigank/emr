<?php

namespace App\Livewire\Masters;

use App\Models\Country;
use App\Models\Region;
use Livewire\Component;
use Livewire\WithPagination;

class RegionCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;

    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public bool $isEditing       = false;

    public ?int   $editingId  = null;
    public ?int   $country_id = null;
    public string $code       = '';
    public string $name       = '';
    public bool   $is_active  = true;

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $uniqueRule = 'unique:regions,code,NULL,id,country_id,' . ($this->country_id ?? 'NULL') . ',history,1';
        if ($this->isEditing && $this->editingId) {
            $uniqueRule = 'unique:regions,code,' . $this->editingId . ',id,country_id,' . ($this->country_id ?? 'NULL') . ',history,1';
        }
        return [
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'code'       => ['required', 'string', 'max:20', $uniqueRule],
            'name'       => ['required', 'string', 'max:100'],
            'is_active'  => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'country_id' => 'Country',
        'code'       => 'Region Code',
        'name'       => 'Region Name',
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
        $r = Region::findOrFail($id);
        $this->editingId  = $r->id;
        $this->country_id = $r->country_id;
        $this->code       = $r->code;
        $this->name       = $r->name;
        $this->is_active  = $r->is_active;
        $this->isEditing  = true;
        $this->showModal  = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $countryId = $data['country_id'];
        unset($data['country_id']);

        if ($this->isEditing) {
            $model = Region::findOrFail($this->editingId);
            $model->update(array_merge($data, [
                'updated_by'   => $this->currentUserId(),
                'updated_date' => now(),
            ]));
            $model->country_id = $countryId;
            $model->save();
            $this->dispatch('toast', type: 'success', message: 'Region updated successfully.');
        } else {
            $model = Region::create(array_merge($data, [
                'history'      => 1,
                'created_by'   => $this->currentUserId(),
                'created_date' => now(),
            ]));
            $model->country_id = $countryId;
            $model->save();
            $this->dispatch('toast', type: 'success', message: 'Region created successfully.');
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
        Region::findOrFail($this->editingId)->update([
            'history'      => 0,
            'updated_by'   => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Region archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal       = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'country_id', 'code', 'name']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $regions = Region::query()
            ->with('country')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('regions.code', 'like', '%' . $this->search . '%')
                          ->orWhere('regions.name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $countries = Country::where('is_active', 1)->orderBy('name')->get();

        return view('livewire.masters.region-crud', compact('regions', 'countries'));
    }
}
