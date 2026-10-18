<?php

namespace App\Livewire\Philhealth;

use App\Models\PhilhealthClaim;
use Livewire\Component;
use Livewire\WithPagination;

class ClaimCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_date';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public string $viewMode = 'list';
    public string $activeTab = 'form';
    public ?int $entryClaimId = null;
    public string $entryMode = 'create';

    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->entryMode = 'create';
        $this->entryClaimId = null;
        $this->activeTab = 'form';
        $this->viewMode = 'entry';
        $this->dispatch('open-claim-form');
    }

    public function openEdit(int $id): void
    {
        $claim = PhilhealthClaim::find($id);
        $isDraft = $claim && $claim->status === 'Draft' && !$claim->er_admission_id && !$claim->admission_id && empty($claim->admitting_diagnosis);

        $this->entryMode = 'edit';
        $this->entryClaimId = $id;
        $this->activeTab = $isDraft ? 'pullup' : 'form';
        $this->viewMode = 'entry';
        $this->dispatch($isDraft && $claim?->patient_id ? 'open-pull-up' : 'open-claim-form', patientId: $claim?->patient_id, claimId: $id);
    }

    public function openSubmission(int $id): void
    {
        $this->entryMode = 'edit';
        $this->entryClaimId = $id;
        $this->activeTab = 'submission';
        $this->viewMode = 'entry';
        $this->dispatch('open-claim-submission', claimId: $id);
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;

        match ($tab) {
            'pullup' => $this->dispatch('open-pull-up', patientId: $this->getClaimPatientId()),
            'form' => $this->dispatch('open-claim-form', claimId: $this->entryClaimId),
            'items' => $this->dispatch('open-items', claimId: $this->entryClaimId),
            'submission' => $this->dispatch('open-claim-submission', claimId: $this->entryClaimId),
        };
    }

    private function getClaimPatientId(): ?int
    {
        if (!$this->entryClaimId) return null;
        return PhilhealthClaim::find($this->entryClaimId)?->patient_id;
    }

    public function backToList(): void
    {
        $this->viewMode = 'list';
        $this->activeTab = 'form';
        $this->entryClaimId = null;
        $this->entryMode = 'create';
        $this->refreshPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (!$this->deleteId) return;

        PhilhealthClaim::findOrFail($this->deleteId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        \App\Models\ClaimItem::where('philhealth_claim_id', $this->deleteId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->dispatch('toast', type: 'success', message: 'Claim archived.');
    }

    #[\Livewire\Attributes\On('claim-saved')]
    public function onClaimSaved(): void
    {
        $this->refreshPage();
    }

    #[\Livewire\Attributes\On('claim-status-changed')]
    public function onStatusChanged(): void
    {
        $this->refreshPage();
    }

    private function refreshPage(): void
    {
        $this->viewMode = 'list';
        $this->activeTab = 'form';
        $this->entryClaimId = null;
        $this->entryMode = 'create';
    }

    public function render()
    {
        $items = PhilhealthClaim::with(['patient', 'philhealthMember', 'items'])
            ->when($this->search, fn($q) => $q->where(fn($i) => $i
                ->where('claim_code', 'like', '%' . $this->search . '%')
                ->orWhere('admitting_diagnosis', 'like', '%' . $this->search . '%')
                ->orWhere('discharge_diagnosis', 'like', '%' . $this->search . '%')
            ))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.philhealth.claim-crud', compact('items'));
    }
}
