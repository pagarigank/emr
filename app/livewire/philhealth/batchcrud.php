<?php

namespace App\Livewire\Philhealth;

use App\Models\ClaimBatch;
use App\Models\PhilhealthClaim;
use App\Services\Philhealth\EclaimsService;
use App\Services\Philhealth\EcsService;
use App\Services\Philhealth\CloudStorageService;
use App\Services\Philhealth\AcknowledgmentService;
use App\Services\Philhealth\XmlBuilder;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BatchCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'created_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $showSubmitModal = false, $showClaimSelectModal = false, $showDetailModal = false, $isEditing = false;
    public bool $showEcsUploadModal = false, $showCloudUploadModal = false, $showAcknowledgmentModal = false, $showResubmitBatchModal = false;
    public ?int $editingId = null, $selectedBatchId = null;

    public string $batch_code = '', $status = 'Draft', $remarks = '';
    public string $date_from = '', $date_to = '';
    public array $selectedClaimIds = [];

    public string $ecsResultMessage = '', $cloudResultMessage = '';
    public string $acknowledgment_code = '';

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:claim_batches,batch_code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId) $u = "unique:claim_batches,batch_code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'batch_code' => ['required', 'string', 'max:20', $u],
            'status' => ['required', 'string', 'max:30'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function generateBatchCode(): string
    {
        $prefix = 'BCH-';
        $last = ClaimBatch::where('tenant_id', $this->currentTenantId())
            ->where('batch_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('batch_code');
        $num = $last ? (int) substr($last, 4) + 1 : 1;
        return $prefix . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->batch_code = $this->generateBatchCode();
        $this->date_from = now()->startOfMonth()->format('Y-m-d');
        $this->date_to = now()->format('Y-m-d');
        $this->isEditing = false;
        $this->showClaimSelectModal = true;
    }

    public function openEdit(int $id): void
    {
        $r = ClaimBatch::findOrFail($id);
        $this->editingId = $r->id;
        $this->batch_code = $r->batch_code;
        $this->status = $r->status;
        $this->remarks = $r->remarks ?? '';
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function openDetail(int $id): void
    {
        $this->selectedBatchId = $id;
        $this->showDetailModal = true;
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }
    public function confirmSubmit(int $id): void { $this->editingId = $id; $this->showSubmitModal = true; }

    public function openEcsUpload(int $id): void
    {
        $this->editingId = $id;
        $this->ecsResultMessage = '';
        $this->showEcsUploadModal = true;
    }

    public function openCloudUpload(int $id): void
    {
        $this->editingId = $id;
        $this->cloudResultMessage = '';
        $this->showCloudUploadModal = true;
    }

    public function openAcknowledgment(int $id): void
    {
        $this->editingId = $id;
        $this->acknowledgment_code = '';
        $this->showAcknowledgmentModal = true;
    }

    public function selectAllClaims(): void
    {
        $claims = $this->getAvailableClaims();
        $this->selectedClaimIds = $claims->pluck('id')->map(fn ($id) => (int) $id)->toArray();
    }

    public function deselectAllClaims(): void
    {
        $this->selectedClaimIds = [];
    }

    public function createBatch(): void
    {
        $this->validate([
            'batch_code' => ['required', 'string', 'max:20'],
            'selectedClaimIds' => ['required', 'array', 'min:1'],
        ]);

        $claims = PhilhealthClaim::whereIn('id', $this->selectedClaimIds)->get();
        $totalAmount = $claims->sum('total_claimed_amount');

        $batch = ClaimBatch::create([
            'tenant_id' => $this->currentTenantId(),
            'batch_code' => $this->batch_code,
            'status' => 'Draft',
            'total_claims' => $claims->count(),
            'total_amount' => $totalAmount,
            'remarks' => $this->remarks,
            'history' => 1,
            'created_by' => $this->currentUserId(),
            'created_date' => now(),
        ]);

        PhilhealthClaim::whereIn('id', $this->selectedClaimIds)->update([
            'claim_batch_id' => $batch->id,
            'status' => 'For Submission',
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: "Batch {$batch->batch_code} created with {$claims->count()} claims.");
        $this->closeModal();
    }

    public function submitBatch(): void
    {
        $batch = ClaimBatch::findOrFail($this->editingId);

        PhilhealthClaim::where('claim_batch_id', $batch->id)->update([
            'status' => 'Submitted',
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $batch->update([
            'status' => 'Submitted',
            'submission_date' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: "Batch {$batch->batch_code} submitted successfully.");
        $this->closeModal();
    }

    public function uploadBatchToEcs(): void
    {
        $batch = ClaimBatch::findOrFail($this->editingId);

        try {
            $service = app(EcsService::class);
            $result = $service->uploadBatch($batch);

            if ($result['success']) {
                $batch->update([
                    'ecs_tracking_no' => $result['tracking_no'],
                    'ecs_uploaded_at' => now(),
                    'updated_by' => $this->currentUserId(),
                    'updated_date' => now(),
                ]);
                $this->ecsResultMessage = 'ECS upload successful. Tracking No: ' . $result['tracking_no'];
                $this->dispatch('toast', type: 'success', message: 'Batch uploaded to ECS successfully.');
            } else {
                $this->ecsResultMessage = 'ECS upload failed: ' . $result['message'];
                $this->dispatch('toast', type: 'error', message: 'ECS upload failed.');
            }
        } catch (\Throwable $e) {
            $this->ecsResultMessage = 'ECS upload error: ' . $e->getMessage();
            $this->dispatch('toast', type: 'error', message: 'ECS upload error.');
        }
    }

    public function uploadBatchToCloud(): void
    {
        $batch = ClaimBatch::findOrFail($this->editingId);

        try {
            $cloud = app(CloudStorageService::class);
            $builder = app(XmlBuilder::class);
            $xml = $builder->buildBatchXml($batch);

            $folderName = $batch->batch_code;

            $cloud->createFolder($folderName);

            $result = $cloud->uploadFile($folderName, $batch->batch_code . '.xml', $xml);

            if ($result['success']) {
                $batch->update([
                    'cloud_folder' => $folderName,
                    'cloud_uploaded_at' => now(),
                    'updated_by' => $this->currentUserId(),
                    'updated_date' => now(),
                ]);
                $this->cloudResultMessage = "Cloud upload successful. Folder: {$folderName}";
                $this->dispatch('toast', type: 'success', message: 'Batch uploaded to PhilHealth Cloud Storage.');
            } else {
                $this->cloudResultMessage = 'Cloud upload failed: ' . $result['message'];
                $this->dispatch('toast', type: 'error', message: 'Cloud upload failed.');
            }
        } catch (\Throwable $e) {
            $this->cloudResultMessage = 'Cloud upload error: ' . $e->getMessage();
            $this->dispatch('toast', type: 'error', message: 'Cloud upload error.');
        }
    }

    public function acknowledgeBatch(): void
    {
        $this->validate(['acknowledgment_code' => ['required', 'string', 'max:50']]);

        $batch = ClaimBatch::findOrFail($this->editingId);

        try {
            $service = app(AcknowledgmentService::class);
            $service->acknowledgeBatch($batch, $this->acknowledgment_code);

            $this->dispatch('toast', type: 'success', message: 'Batch acknowledged successfully.');
            $this->closeModal();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Acknowledgment error: ' . $e->getMessage());
        }
    }

    public function delete(): void
    {
        $batch = ClaimBatch::findOrFail($this->editingId);

        PhilhealthClaim::where('claim_batch_id', $batch->id)->update([
            'claim_batch_id' => null,
            'status' => 'Draft',
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $batch->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Batch archived successfully.');
        $this->closeModal();
    }

    public function downloadBatchXml(int $id): StreamedResponse
    {
        $batch = ClaimBatch::findOrFail($id);
        $builder = app(XmlBuilder::class);
        $xml = $builder->buildBatchXml($batch);

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, $batch->batch_code . '.xml', [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function downloadClaimXml(int $claimId): StreamedResponse
    {
        $claim = PhilhealthClaim::findOrFail($claimId);
        $builder = app(XmlBuilder::class);
        $xml = $builder->buildClaimXml($claim);

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, $claim->claim_code . '.xml', [
            'Content-Type' => 'application/xml',
        ]);
    }

    public function openResubmitBatch(int $id): void
    {
        $this->editingId = $id;
        $this->showResubmitBatchModal = true;
    }

    public function resubmitBatch(): void
    {
        $batch = ClaimBatch::findOrFail($this->editingId);
        $deniedClaims = PhilhealthClaim::where('claim_batch_id', $batch->id)
            ->where('status', 'Denied')
            ->get();

        if ($deniedClaims->isEmpty()) {
            $this->dispatch('toast', type: 'info', message: 'No denied claims found in this batch.');
            $this->closeModal();
            return;
        }

        $count = 0;
        foreach ($deniedClaims as $claim) {
            $latestRejection = \App\Models\ClaimRejection::where('philhealth_claim_id', $claim->id)
                ->where('resubmission_status', 'Pending')
                ->latest('id')
                ->first();

            if ($latestRejection) {
                $latestRejection->update([
                    'resubmission_status' => 'Resubmitted',
                    'resubmitted_at' => now(),
                    'updated_by' => $this->currentUserId(),
                    'updated_date' => now(),
                ]);
            }

            $claim->update([
                'status' => 'For Submission',
                'acknowledgment_code' => null,
                'acknowledgment_date' => null,
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);

            try {
                \Illuminate\Support\Facades\Mail::to(config('philhealth.hospital.email', config('mail.from.address')))
                    ->send(new \App\Mail\ClaimStatusNotification($claim->fresh(), 'resubmitted'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Resubmit notification failed: ' . $e->getMessage());
            }

            $count++;
        }

        $this->dispatch('toast', type: 'success', message: "{$count} denied claim(s) resubmitted from batch {$batch->batch_code}.");
        $this->closeModal();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showSubmitModal = false;
        $this->showClaimSelectModal = false;
        $this->showDetailModal = false;
        $this->showEcsUploadModal = false;
        $this->showCloudUploadModal = false;
        $this->showAcknowledgmentModal = false;
        $this->showResubmitBatchModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'batch_code', 'status', 'remarks', 'date_from', 'date_to', 'selectedClaimIds', 'selectedBatchId', 'ecsResultMessage', 'cloudResultMessage', 'acknowledgment_code']);
        $this->status = 'Draft';
        $this->resetValidation();
    }

    protected function getAvailableClaims()
    {
        return PhilhealthClaim::whereNull('claim_batch_id')
            ->whereIn('status', ['Draft', 'For Submission'])
            ->where('tenant_id', $this->currentTenantId())
            ->where('history', 1)
            ->when($this->date_from, fn ($q) => $q->whereDate('created_date', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('created_date', '<=', $this->date_to))
            ->when($this->search, fn ($q) => $q->where(fn ($i) => $i
                ->where('claim_code', 'like', '%' . $this->search . '%')
                ->orWhere('admitting_diagnosis', 'like', '%' . $this->search . '%')
            ))
            ->orderBy('created_date', 'desc')
            ->get();
    }

    public function render()
    {
        $items = ClaimBatch::withCount('claims')
            ->when($this->search, fn ($q) => $q->where('batch_code', 'like', '%' . $this->search . '%'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $availableClaims = $this->showClaimSelectModal ? $this->getAvailableClaims() : collect();
        $detailBatch = null;
        if ($this->showDetailModal && $this->selectedBatchId) {
            $detailBatch = ClaimBatch::with(['claims.patient', 'claims.philhealthMember', 'claims.icd10Code', 'claims.caseRate'])
                ->find($this->selectedBatchId);
        }

        return view('livewire.philhealth.batch-crud', compact('items', 'availableClaims', 'detailBatch'));
    }
}
