<?php

namespace App\Livewire\Billing;

use App\Models\Billing\FundSource;
use Livewire\Component;
use Livewire\WithPagination;

class FundSourceCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;
    public string $code = '', $name = '', $description = '';
    public string $budget_amount = '0';
    public bool $is_active = true;
    public float $remaining = 0;

    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }
    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }

    protected function rules(): array
    {
        $table = 'fund_sources';
        $u = "unique:{$table},code,NULL,id,history,1";
        if ($this->isEditing && $this->editingId)
            $u = "unique:{$table},code,{$this->editingId},id,history,1";
        return [
            'code'          => ['required', 'string', 'max:30', $u],
            'name'          => ['required', 'string', 'max:200'],
            'description'   => ['nullable', 'string', 'max:500'],
            'budget_amount' => ['required', 'numeric', 'min:0'],
            'is_active'     => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'code' => 'Fund Source Code',
        'name' => 'Fund Source Name',
        'budget_amount' => 'Budget Amount',
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
        $r = FundSource::findOrFail($id);
        $this->editingId = $r->id;
        $this->code = $r->code;
        $this->name = $r->name;
        $this->description = $r->description ?? '';
        $this->budget_amount = (string) ($r->budget_amount ?? 0);
        $this->is_active = $r->is_active;
        $this->remaining = $r->remainingAmount();
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        if ($this->isEditing) {
            $record = FundSource::findOrFail($this->editingId);
            $record->update(array_merge($data, [
                'budget_amount' => $data['budget_amount'],
                'updated_by' => $uid,
                'updated_date' => $now,
            ]));
            $this->dispatch('toast', type: 'success', message: 'Fund source updated successfully.');
        } else {
            FundSource::create(array_merge($data, [
                'tenant_id' => $tid,
                'budget_amount' => $data['budget_amount'],
                'used_amount' => 0,
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]));
            $this->dispatch('toast', type: 'success', message: 'Fund source created successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        FundSource::findOrFail($this->editingId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Fund source archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'description', 'budget_amount']);
        $this->is_active = true;
        $this->remaining = 0;
        $this->resetValidation();
    }

    public function render()
    {
        $items = FundSource::query()
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%')
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.billing.fund-source-crud', compact('items'));
    }
}
