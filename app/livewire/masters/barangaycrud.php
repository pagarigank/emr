<?php

namespace App\Livewire\Masters;

use App\Models\Barangay;
use App\Models\ProvinceCity;
use Livewire\Component;
use Livewire\WithPagination;

class BarangayCrud extends Component
{
    use WithPagination;

    public string $search        = '';
    public string $sortField     = 'code';
    public string $sortDirection = 'asc';
    public int    $perPage       = 10;

    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public bool $isEditing       = false;

    public ?int   $editingId       = null;
    public ?int   $province_city_id = null;
    public string $code            = '';
    public string $name            = '';
    public bool   $is_active       = true;

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $uniqueRule = 'unique:barangays,code,NULL,id,province_city_id,' . ($this->province_city_id ?? 'NULL') . ',history,1';
        if ($this->isEditing && $this->editingId) {
            $uniqueRule = 'unique:barangays,code,' . $this->editingId . ',id,province_city_id,' . ($this->province_city_id ?? 'NULL') . ',history,1';
        }
        return [
            'province_city_id' => ['required', 'integer', 'exists:provinces_cities,id'],
            'code'             => ['required', 'string', 'max:20', $uniqueRule],
            'name'             => ['required', 'string', 'max:100'],
            'is_active'        => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'province_city_id' => 'Province/City',
        'code'             => 'Barangay Code',
        'name'             => 'Barangay Name',
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
        $b = Barangay::findOrFail($id);
        $this->editingId       = $b->id;
        $this->province_city_id = $b->province_city_id;
        $this->code             = $b->code;
        $this->name             = $b->name;
        $this->is_active        = $b->is_active;
        $this->isEditing        = true;
        $this->showModal        = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $provinceCityId = $data['province_city_id'];
        unset($data['province_city_id']);

        if ($this->isEditing) {
            $model = Barangay::findOrFail($this->editingId);
            $model->update(array_merge($data, [
                'updated_by'   => $this->currentUserId(),
                'updated_date' => now(),
            ]));
            $model->province_city_id = $provinceCityId;
            $model->save();
            $this->dispatch('toast', type: 'success', message: 'Barangay updated successfully.');
        } else {
            $model = Barangay::create(array_merge($data, [
                'history'      => 1,
                'created_by'   => $this->currentUserId(),
                'created_date' => now(),
            ]));
            $model->province_city_id = $provinceCityId;
            $model->save();
            $this->dispatch('toast', type: 'success', message: 'Barangay created successfully.');
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
        Barangay::findOrFail($this->editingId)->update([
            'history'      => 0,
            'updated_by'   => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Barangay archived successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal       = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'province_city_id', 'code', 'name']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $barangays = Barangay::query()
            ->with('provinceCity.region')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('barangays.code', 'like', '%' . $this->search . '%')
                          ->orWhere('barangays.name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $provincesCities = ProvinceCity::where('is_active', 1)->orderBy('name')->get();

        return view('livewire.masters.barangay-crud', compact('barangays', 'provincesCities'));
    }
}
