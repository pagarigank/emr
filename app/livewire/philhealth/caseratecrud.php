<?php

namespace App\Livewire\Philhealth;

use App\Models\PhilhealthCaseRate;
use Livewire\Component;
use Livewire\WithPagination;

class CaseRateCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'rvs_code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $rvs_code = '', $icd10_code = '', $description = '';
    public string $hf_fee = '0', $pf_fee = '0', $total_rate = '0';
    public bool $is_active = true;

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        return ['rvs_code' => ['nullable', 'string', 'max:10'], 'icd10_code' => ['nullable', 'string', 'max:10'], 'description' => ['required', 'string', 'max:255'], 'hf_fee' => ['required', 'numeric', 'min:0'], 'pf_fee' => ['required', 'numeric', 'min:0'], 'total_rate' => ['required', 'numeric', 'min:0'], 'is_active' => ['boolean']];
    }

    protected array $validationAttributes = ['rvs_code' => 'RVS Code', 'icd10_code' => 'ICD-10 Code', 'description' => 'Description', 'hf_fee' => 'Health Facility Fee', 'pf_fee' => 'Professional Fee', 'total_rate' => 'Total Rate'];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatedHfFee(): void { $this->recalculateTotal(); }
    public function updatedPfFee(): void { $this->recalculateTotal(); }

    private function recalculateTotal(): void
    {
        $hf = floatval($this->hf_fee ?: 0);
        $pf = floatval($this->pf_fee ?: 0);
        $this->total_rate = (string)($hf + $pf);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function openCreate(): void { $this->resetForm(); $this->isEditing = false; $this->showModal = true; }

    public function openEdit(int $id): void
    {
        $r = PhilhealthCaseRate::findOrFail($id);
        $this->editingId = $r->id; $this->rvs_code = $r->rvs_code; $this->icd10_code = $r->icd10_code;
        $this->description = $r->description; $this->hf_fee = (string)$r->hf_fee;
        $this->pf_fee = (string)$r->pf_fee; $this->total_rate = (string)$r->total_rate;
        $this->is_active = $r->is_active; $this->isEditing = true; $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->isEditing) {
            PhilhealthCaseRate::findOrFail($this->editingId)->update(array_merge($data, ['updated_by' => $this->currentUserId(), 'updated_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'Case rate updated successfully.');
        } else {
            PhilhealthCaseRate::create(array_merge($data, ['history' => 1, 'created_by' => $this->currentUserId(), 'created_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'Case rate created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        PhilhealthCaseRate::findOrFail($this->editingId)->update(['history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Case rate archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void { $this->reset(['editingId', 'rvs_code', 'icd10_code', 'description', 'hf_fee', 'pf_fee', 'total_rate']); $this->is_active = true; $this->resetValidation(); }

    public function render()
    {
        $items = PhilhealthCaseRate::query()
            ->when($this->search, fn ($q) => $q->where(fn ($i) => $i->where('description', 'like', '%' . $this->search . '%')->orWhere('rvs_code', 'like', '%' . $this->search . '%')->orWhere('icd10_code', 'like', '%' . $this->search . '%')))
            ->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        return view('livewire.philhealth.case-rate-crud', compact('items'));
    }
}
