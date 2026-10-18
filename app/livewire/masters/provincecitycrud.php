<?php

namespace App\Livewire\Masters;

use App\Models\ProvinceCity;
use App\Models\Region;
use Livewire\Component;
use Livewire\WithPagination;

class ProvinceCityCrud extends Component
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
    public ?int   $region_id  = null;
    public ?int   $parent_id  = null;
    public string $code       = '';
    public string $name       = '';
    public string $type       = 'province';
    public bool   $is_active  = true;

    public array $availableProvinces = [];

    public function mount(string $type = 'province')
    {
        $this->type = in_array(strtolower($type), ['province', 'city']) ? strtolower($type) : 'province';
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $uniqueRule = 'unique:provinces_cities,code,NULL,id,region_id,' . ($this->region_id ?? 'NULL') . ',history,1';
        if ($this->isEditing && $this->editingId) {
            $uniqueRule = 'unique:provinces_cities,code,' . $this->editingId . ',id,region_id,' . ($this->region_id ?? 'NULL') . ',history,1';
        }

        $rules = [
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'code'      => ['required', 'string', 'max:20', $uniqueRule],
            'name'      => ['required', 'string', 'max:100'],
            'type'      => ['required', 'string', 'in:province,city'],
            'is_active' => ['boolean'],
        ];

        if ($this->type === 'city') {
            $rules['parent_id'] = ['required', 'integer', 'exists:provinces_cities,id'];
        } else {
            $rules['parent_id'] = ['nullable'];
        }

        return $rules;
    }

    protected array $validationAttributes = [
        'region_id' => 'Region',
        'parent_id' => 'Province',
        'code'      => 'Code',
        'name'      => 'Name',
        'type'      => 'Type',
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

    public function updatedRegionId($value)
    {
        $this->parent_id = null;
        if ($value && $this->type === 'city') {
            $this->availableProvinces = ProvinceCity::where('region_id', $value)->where('type', 'province')->orderBy('name')->get()->toArray();
        } else {
            $this->availableProvinces = [];
        }
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $pc = ProvinceCity::findOrFail($id);
        $this->editingId = $pc->id;
        $this->region_id = $pc->region_id;
        $this->parent_id = $pc->parent_id;
        $this->code      = $pc->code;
        $this->name      = $pc->name;
        $this->type      = strtolower($pc->type);
        $this->is_active = $pc->is_active;

        if ($this->region_id && $this->type === 'city') {
            $this->availableProvinces = ProvinceCity::where('region_id', $this->region_id)->where('type', 'province')->orderBy('name')->get()->toArray();
        } else {
            $this->availableProvinces = [];
        }

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $regionId = $data['region_id'];
        $parentId = $data['parent_id'] ?? null;
        unset($data['region_id'], $data['parent_id']);

        if ($this->isEditing) {
            $model = ProvinceCity::findOrFail($this->editingId);
            $model->update(array_merge($data, [
                'updated_by'   => $this->currentUserId(),
                'updated_date' => now(),
            ]));
            $model->region_id = $regionId;
            $model->parent_id = $parentId;
            $model->save();
            $this->dispatch('toast', type: 'success', message: ucfirst($this->type) . ' updated successfully.');
        } else {
            $model = ProvinceCity::create(array_merge($data, [
                'history'      => 1,
                'created_by'   => $this->currentUserId(),
                'created_date' => now(),
            ]));
            $model->region_id = $regionId;
            $model->parent_id = $parentId;
            $model->save();
            $this->dispatch('toast', type: 'success', message: ucfirst($this->type) . ' created successfully.');
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
        ProvinceCity::findOrFail($this->editingId)->update([
            'history'      => 0,
            'updated_by'   => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: ucfirst($this->type) . ' archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal       = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'region_id', 'parent_id', 'code', 'name', 'availableProvinces']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $provincesCities = ProvinceCity::query()
            ->with(['region', 'parent'])
            ->where('type', $this->type)
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('provinces_cities.code', 'like', '%' . $this->search . '%')
                          ->orWhere('provinces_cities.name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $regions = Region::where('is_active', 1)->orderBy('name')->get();

        return view('livewire.masters.province-city-crud', compact('provincesCities', 'regions'));
    }
}
