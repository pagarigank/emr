<?php

namespace App\Livewire\Yakap;

use App\Models\Patient;
use App\Models\Yakap\YakapEmpanelment;
use App\Models\Yakap\YakapCapitationPayment;
use Livewire\Component;
use Livewire\WithPagination;

class CapitationCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'payment_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;

    public ?int $empanelment_id = null, $patient_id = null;
    public int $tranche = 1;
    public string $amount = '0.00', $payment_code = '', $payment_date = '';
    public string $status = 'Pending', $philhealth_reference = '', $notes = '';
    public string $empanelment_search = '';

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit(int $id): void
    {
        $p = YakapCapitationPayment::findOrFail($id);
        $this->editingId = $p->id;
        $this->empanelment_id = $p->empanelment_id;
        $this->patient_id = $p->patient_id;
        $this->tranche = $p->tranche;
        $this->amount = number_format($p->amount, 2, '.', '');
        $this->payment_code = $p->payment_code ?? '';
        $this->payment_date = $p->payment_date?->format('Y-m-d') ?? '';
        $this->status = $p->status;
        $this->philhealth_reference = $p->philhealth_reference ?? '';
        $this->notes = $p->notes ?? '';
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save(): void
    {
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();

        $this->validate([
            'empanelment_id' => ['required', 'integer', 'exists:yakap_empanelments,id'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'tranche' => ['required', 'integer', 'in:1,2'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:30'],
        ]);

        $data = [
            'tenant_id' => $tid,
            'empanelment_id' => $this->empanelment_id,
            'patient_id' => $this->patient_id,
            'tranche' => $this->tranche,
            'amount' => (float) $this->amount,
            'payment_code' => $this->payment_code ?: null,
            'payment_date' => $this->payment_date ?: null,
            'status' => $this->status,
            'philhealth_reference' => $this->philhealth_reference ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->isEditing && $this->editingId) {
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            YakapCapitationPayment::where('id', $this->editingId)->update($data);
            session()->flash('message', 'Capitation payment updated successfully.');
        } else {
            $data['created_by'] = $uid;
            $data['created_date'] = now();
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            YakapCapitationPayment::create($data);
            session()->flash('message', 'Capitation payment created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function updatedEmpanelmentId(): void
    {
        if ($this->empanelment_id) {
            $emp = YakapEmpanelment::find($this->empanelment_id);
            if ($emp) $this->patient_id = $emp->patient_id;
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        YakapCapitationPayment::where('id', $this->editingId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->showDeleteModal = false;
        $this->editingId = null;
        session()->flash('message', 'Capitation payment archived successfully.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'empanelment_id', 'patient_id', 'amount', 'payment_code',
            'payment_date', 'philhealth_reference', 'notes', 'empanelment_search']);
        $this->tranche = 1;
        $this->status = 'Pending';
    }

    public function render()
    {
        $tid = $this->currentTenantId();
        $query = YakapCapitationPayment::with(['empanelment', 'patient'])->where('tenant_id', $tid);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('payment_code', 'like', '%' . $this->search . '%')
                  ->orWhere('philhealth_reference', 'like', '%' . $this->search . '%')
                  ->orWhereHas('patient', fn ($pq) => $pq->where('last_name', 'like', '%' . $this->search . '%'));
            });
        }

        $payments = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.yakap.capitation-crud', [
            'payments' => $payments,
            'empanelments' => $this->empanelment_search
                ? YakapEmpanelment::where('tenant_id', $tid)
                    ->where(function ($q) {
                        $q->where('empanelment_code', 'like', '%' . $this->empanelment_search . '%')
                          ->orWhereHas('patient', fn ($pq) => $pq->where('last_name', 'like', '%' . $this->empanelment_search . '%'));
                    })->orderBy('empanelment_code')->get()
                : collect(),
        ]);
    }
}
