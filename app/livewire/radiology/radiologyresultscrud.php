<?php

namespace App\Livewire\Radiology;

use App\Models\HealthCareProfessional;
use App\Models\Radiology\RadiologyRequestItem;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class RadiologyResultsCrud extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '', $sortField = 'result_date', $sortDirection = 'desc', $filterStatus = '';
    public int $perPage = 10;

    public bool $showDetailModal = false, $showResultModal = false;
    public ?int $viewingId = null, $resultItemId = null;

    // Result entry fields
    public ?string $result_text = null, $impression = null, $technique = null, $findings = null, $doctor_notes = null;
    public ?int $radiologist_id = null, $performed_by = null;
    public $image_upload = null;
    public array $existingImages = [];
    public array $uploadedImages = [];
    public bool $removingImage = false;

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function viewDetail(int $id): void
    {
        $this->viewingId = $id;
        $this->showDetailModal = true;
    }

    public function openResultModal(int $itemId): void
    {
        $item = RadiologyRequestItem::with(['images', 'radiologist', 'performedBy'])->findOrFail($itemId);
        $this->resultItemId = $item->id;
        $this->result_text = $item->result_text;
        $this->impression = $item->impression;
        $this->technique = $item->technique;
        $this->findings = $item->findings;
        $this->doctor_notes = $item->doctor_notes;
        $this->radiologist_id = $item->radiologist_id;
        $this->performed_by = $item->performed_by;
        $this->existingImages = $item->images->toArray();
        $this->image_upload = null;
        $this->showResultModal = true;
    }

    public function removeImage(int $imageId): void
    {
        $img = \App\Models\Radiology\RadiologyResultImage::find($imageId);
        if ($img && $img->radiology_request_item_id === $this->resultItemId) {
            $storageRoot = realpath(public_path('storage')) ?: public_path('storage');
            $path = public_path('storage/' . ltrim($img->path, '/'));
            $realPath = realpath($path);
            if ($realPath && $storageRoot && str_starts_with($realPath, $storageRoot) && file_exists($realPath)) {
                @unlink($realPath);
            }
            $img->update(['history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
            $item = RadiologyRequestItem::with('images')->find($this->resultItemId);
            $this->existingImages = $item?->images->toArray() ?? [];
        }
    }

    public function saveResult(): void
    {
        $this->validate([
            'result_text' => 'nullable|string',
            'impression' => 'nullable|string',
            'technique' => 'nullable|string',
            'findings' => 'nullable|string',
            'doctor_notes' => 'nullable|string',
            'radiologist_id' => 'nullable|integer|exists:health_care_professionals,id',
            'performed_by' => 'nullable|integer|exists:users,id',
            'image_upload' => 'nullable|image|max:20480',
        ]);

        $item = RadiologyRequestItem::findOrFail($this->resultItemId);
        $uid = $this->currentUserId();

        $item->update([
            'result_text' => $this->result_text,
            'impression' => $this->impression,
            'technique' => $this->technique,
            'findings' => $this->findings,
            'doctor_notes' => $this->doctor_notes,
            'radiologist_id' => $this->radiologist_id,
            'performed_by' => $this->performed_by,
            'status' => 'Complete',
            'result_date' => $item->result_date ?? now(),
            'performed_at' => $item->performed_at ?? now(),
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);

        if ($this->image_upload) {
            $path = $this->image_upload->store('radiology/results', 'public');
            $img = \App\Models\Radiology\RadiologyResultImage::create([
                'radiology_request_item_id' => $item->id,
                'filename' => $this->image_upload->hashName(),
                'original_name' => $this->image_upload->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $this->image_upload->getMimeType(),
                'file_size' => $this->image_upload->getSize(),
                'is_primary' => $item->images()->count() === 0,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => now(),
                'updated_by' => $uid,
                'updated_date' => now(),
            ]);
        }

        // Update parent request status
        $allStatuses = $item->radiologyRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Approved') || $allStatuses->contains('Partial') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $updateData = ['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => now()];
        $item->radiologyRequest->update($updateData);

        if ($parentStatus === 'Complete') {
            \App\Models\Radiology\RadiologyRequest::postChargeToLedger($item->radiologyRequest, (float) $item->radiologyRequest->total_amount, $uid);
        }

        $this->showResultModal = false;
        $this->dispatch('toast', type: 'success', message: 'Radiology result saved successfully.');
    }

    public function closeModal(): void
    {
        $this->showDetailModal = false;
        $this->showResultModal = false;
        $this->reset([
            'viewingId', 'resultItemId', 'result_text', 'impression', 'technique',
            'findings', 'doctor_notes', 'radiologist_id', 'performed_by',
            'image_upload', 'existingImages',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $query = RadiologyRequestItem::with([
            'radiologyRequest.requestingDepartment',
            'radiologyRequest.patient',
            'radiologyRequest.erAdmission.patient',
            'radiologyRequest.opdConsultation.patient',
            'radiologyRequest.admission.patient',
            'radiologist',
            'performedBy',
            'examType',
        ])->whereHas('radiologyRequest', fn ($q) =>
            $q->where('tenant_id', $this->currentTenantId())
        );

        if ($this->filterStatus === 'pending') {
            $query->whereIn('status', ['Pending', 'Approved', 'Partial']);
        } elseif ($this->filterStatus === 'completed') {
            $query->where('status', 'Complete');
        }

        if ($this->search) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('exam_name', 'like', "%{$s}%")
                  ->orWhere('result_text', 'like', "%{$s}%")
                  ->orWhere('impression', 'like', "%{$s}%")
                  ->orWhereHas('radiologyRequest', fn ($r) =>
                      $r->where('request_code', 'like', "%{$s}%")
                  );
            });
        }

        $items = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        $detailItem = null;
        if ($this->showDetailModal && $this->viewingId) {
            $detailItem = RadiologyRequestItem::with([
                'radiologyRequest.requestingDepartment',
                'radiologyRequest.patient',
                'radiologyRequest.erAdmission.patient',
                'radiologyRequest.opdConsultation.patient',
                'radiologyRequest.admission.patient',
                'radiologist',
                'performedBy',
                'examType.modality',
                'images',
            ])->find($this->viewingId);
        }

        $radiologists = HealthCareProfessional::where('tenant_id', $this->currentTenantId())
            ->orderBy('full_name')->get(['id', 'full_name', 'professional_code']);

        $technologists = User::whereIn('role', ['admin', 'superadmin', 'staff'])
            ->orderBy('full_name')->get(['id', 'full_name']);

        return view('livewire.radiology.radiology-results-crud', compact(
            'items', 'detailItem', 'radiologists', 'technologists'
        ));
    }
}
