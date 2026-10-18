<?php

namespace App\Livewire\Lab;

use App\Models\ReferenceRange;
use App\Models\LabTest;
use Livewire\Component;
use Livewire\WithPagination;

class ReferenceRangeCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'lab_test_id', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public ?int $lab_test_id = null;
    public string $gender = 'Any', $normal_values_text = '', $interpretation = '';
    public ?float $age_min_years = null, $age_max_years = null, $min_value = null, $max_value = null;
    public bool $is_active = true;

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        return [
            'lab_test_id'        => ['required', 'integer', 'exists:lab_tests,id'],
            'gender'             => ['required', 'string', 'in:Any,Male,Female'],
            'age_min_years'      => ['nullable', 'numeric', 'min:0'],
            'age_max_years'      => ['nullable', 'numeric', 'min:0'],
            'min_value'          => ['nullable', 'numeric'],
            'max_value'          => ['nullable', 'numeric'],
            'normal_values_text' => ['nullable', 'string', 'max:255'],
            'interpretation'     => ['nullable', 'string', 'max:255'],
            'is_active'          => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'lab_test_id' => 'Lab Test',
    ];

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
        $r = ReferenceRange::findOrFail($id);
        $this->editingId = $r->id; $this->lab_test_id = $r->lab_test_id;
        $this->gender = $r->gender; $this->age_min_years = $r->age_min_years;
        $this->age_max_years = $r->age_max_years; $this->min_value = $r->min_value;
        $this->max_value = $r->max_value; $this->normal_values_text = $r->normal_values_text ?? '';
        $this->interpretation = $r->interpretation ?? ''; $this->is_active = $r->is_active;
        $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            ReferenceRange::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(), 'updated_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Reference range updated successfully.');
        } else {
            ReferenceRange::create(array_merge($data, [
                'history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now(),
            ]));
            $this->dispatch('toast', type: 'success', message: 'Reference range created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        ReferenceRange::findOrFail($this->editingId)->update([
            'history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Reference range archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'lab_test_id', 'gender', 'age_min_years', 'age_max_years', 'min_value', 'max_value', 'normal_values_text', 'interpretation']);
        $this->is_active = true; $this->gender = 'Any'; $this->resetValidation();
    }

    public function render()
    {
        $items = ReferenceRange::query()
            ->select('reference_ranges.*')
            ->join('lab_tests', 'lab_tests.id', '=', 'reference_ranges.lab_test_id')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('lab_tests.name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $labTests = LabTest::orderBy('name')->get();

        return view('livewire.lab.reference-range-crud', compact('items', 'labTests'));
    }
}
