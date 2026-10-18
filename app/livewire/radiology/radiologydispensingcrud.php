<?php

namespace App\Livewire\Radiology;

use App\Models\Department;
use App\Models\Radiology\RadiologyExamType;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Radiology\RadiologyRequestItem;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Livewire\Er\ErAdmissionCrud;

class RadiologyDispensingCrud extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '', $sortField = 'created_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $showDetailModal = false, $isEditing = false, $showResultModal = false;
    public ?int $editingId = null, $viewingId = null, $resultItemId = null;

    // Form fields
    public string $request_code = '', $request_notes = '';
    public ?string $requesting_department_id = null, $requesting_person = null;

    // Line items
    public array $lineItems = [];
    public string $total_amount = '0';

    public bool $is_active = true;

    // Result entry fields
    public ?string $result_text = null, $impression = null, $technique = null, $findings = null, $doctor_notes = null;
    public ?int $radiologist_id = null, $performed_by = null;
    public $image_upload = null;
    public array $existingImages = [];

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    protected function rules(): array
    {
        return [
            'requesting_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'requesting_person'        => ['nullable', 'string', 'max:150'],
            'request_notes'            => ['nullable', 'string'],
            'lineItems'                => ['required', 'array', 'min:1'],
            'lineItems.*.exam_type_id' => ['nullable', 'integer', 'exists:radiology_exam_types,id'],
            'lineItems.*.exam_name'    => ['required', 'string', 'max:200'],
            'lineItems.*.charge_amount'=> ['required', 'numeric', 'min:0'],
        ];
    }

    protected array $validationAttributes = [
        'lineItems' => 'Exam Items',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function generateRequestCode(): string
    {
        $tid = $this->currentTenantId();
        $last = RadiologyRequest::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->request_code, 3)) + 1 : 1;
        return 'RR-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->request_code = $this->generateRequestCode();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $r = RadiologyRequest::with('items')->findOrFail($id);
        $this->editingId = $r->id;
        $this->request_code = $r->request_code;
        $this->requesting_department_id = $r->requesting_department_id ? (string) $r->requesting_department_id : null;
        $this->requesting_person = $r->requesting_person;
        $this->request_notes = $r->notes ?? '';
        $this->lineItems = $r->items->map(fn ($i) => [
            'exam_type_id' => $i->exam_type_id,
            'exam_name' => $i->exam_name,
            'modality_name' => $i->modality_name,
            'charge_amount' => (string) $i->charge_amount,
        ])->toArray();
        $this->recalculateTotal();
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function viewDetail(int $id): void
    {
        $this->viewingId = $id;
        $this->showDetailModal = true;
    }

    public function addExamType(int $examTypeId): void
    {
        $exam = RadiologyExamType::with('modality')->find($examTypeId);
        if (!$exam) return;
        $this->lineItems[] = [
            'exam_type_id' => $exam->id,
            'exam_name' => $exam->name,
            'modality_name' => $exam->modality?->name ?? '',
            'charge_amount' => (string) $exam->standard_charge,
        ];
        $this->recalculateTotal();
    }

    public function removeLineItem(int $index): void
    {
        unset($this->lineItems[$index]);
        $this->lineItems = array_values($this->lineItems);
        $this->recalculateTotal();
    }

    public function recalculateTotal(): void
    {
        $total = 0;
        foreach ($this->lineItems as $item) {
            $total += (float) ($item['charge_amount'] ?? 0);
        }
        $this->total_amount = number_format($total, 2, '.', '');
    }

    public function updatedLineItems(): void
    {
        $this->recalculateTotal();
    }

    public function save(): void
    {
        $data = $this->validate();
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        if ($this->isEditing) {
            $request = RadiologyRequest::findOrFail($this->editingId);
            $request->update([
                'requesting_department_id' => $data['requesting_department_id'],
                'requesting_person'       => $data['requesting_person'],
                'notes'                   => $data['request_notes'],
                'total_amount'            => $this->total_amount,
                'updated_by'              => $uid,
                'updated_date'            => $now,
            ]);
            $request->items()->delete();
            foreach ($data['lineItems'] as $li) {
                $request->items()->create([
                    'exam_type_id'   => $li['exam_type_id'],
                    'exam_name'      => $li['exam_name'],
                    'modality_name'  => $li['modality_name'] ?? '',
                    'charge_amount'  => $li['charge_amount'],
                    'status'         => 'Pending',
                    'is_active'      => true,
                    'history'        => 1,
                    'created_by'     => $uid,
                    'created_date'   => $now,
                    'updated_by'     => $uid,
                    'updated_date'   => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Radiology request updated successfully.');
            if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
            if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
            if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
            if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
            if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
            if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        } else {
            $request = RadiologyRequest::create([
                'tenant_id'                => $tid,
                'request_code'             => $this->request_code,
                'requesting_department_id'  => $data['requesting_department_id'],
                'requesting_person'        => $data['requesting_person'],
                'status'                   => 'Pending',
                'billing_status'           => 'Pending',
                'total_amount'             => $this->total_amount,
                'notes'                    => $data['request_notes'],
                'is_active'                => true,
                'history'                  => 1,
                'created_by'               => $uid,
                'created_date'             => $now,
                'updated_by'               => $uid,
                'updated_date'             => $now,
            ]);
            foreach ($data['lineItems'] as $li) {
                RadiologyRequestItem::create([
                    'radiology_request_id' => $request->id,
                    'exam_type_id'         => $li['exam_type_id'],
                    'exam_name'            => $li['exam_name'],
                    'modality_name'        => $li['modality_name'] ?? '',
                    'charge_amount'        => $li['charge_amount'],
                    'status'               => 'Pending',
                    'is_active'            => true,
                    'history'              => 1,
                    'created_by'           => $uid,
                    'created_date'         => $now,
                    'updated_by'           => $uid,
                    'updated_date'         => $now,
                ]);
            }
            $this->dispatch('toast', type: 'success', message: 'Radiology request created successfully.');
            if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
            if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
            if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
            if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
            if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
            if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        }
        $this->closeModal();
    }

    public function updateBillingStatus(int $id, string $status): void
    {
        $valid = ['Billed', 'Exempt', 'Credited', 'Pending'];
        if (!in_array($status, $valid)) return;

        $uid = $this->currentUserId();
        $request = RadiologyRequest::findOrFail($id);
        $request->update([
            'billing_status' => $status,
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: "Radiology billing status updated to {$status}.");
        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
    }

    public function updateItemStatus(int $itemId, string $status): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $item = RadiologyRequestItem::findOrFail($itemId);
        $item->update([
            'status' => $status,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $allStatuses = $item->radiologyRequest->items->pluck('status')->unique()->values();
        $parentStatus = match (true) {
            $allStatuses->every(fn ($s) => $s === 'Complete') => 'Complete',
            $allStatuses->contains('Approved') || $allStatuses->contains('Partial') => 'Partial',
            $allStatuses->contains('Cancelled') && $allStatuses->count() === 1 => 'Cancelled',
            default => 'Pending',
        };
        $updateData = ['status' => $parentStatus, 'updated_by' => $uid, 'updated_date' => $now];

        // Auto-bill when item is approved — charge is captured even if result entry happens later
        if ($status === 'Approved' && $item->radiologyRequest->billing_status === 'Pending') {
            $updateData['billing_status'] = 'Billed';
        }

        $item->radiologyRequest->update($updateData);

        if ($parentStatus === 'Complete') {
            \App\Models\Radiology\RadiologyRequest::postChargeToLedger($item->radiologyRequest, (float) $item->radiologyRequest->total_amount, $uid);
        }

        $this->dispatch('toast', type: 'success', message: "Radiology item status updated to {$status}.");
        $this->dispatch('$refresh');
    }

    public function completeAllItems(int $requestId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $request = RadiologyRequest::findOrFail($requestId);
        $request->items()->update(['status' => 'Complete', 'updated_by' => $uid, 'updated_date' => $now]);

        $updateData = ['status' => 'Complete', 'updated_by' => $uid, 'updated_date' => $now];
        if ($request->billing_status === 'Pending') {
            $updateData['billing_status'] = 'Billed';
        }
        $request->update($updateData);

        \App\Models\Radiology\RadiologyRequest::postChargeToLedger($request, (float) $request->total_amount, $uid);

        $this->dispatch('toast', type: 'success', message: 'All radiology items marked as Complete.');
        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        if ($request->opd_consultation_id) $this->dispatch('refreshOpdOrders')->to(\App\Livewire\Opd\OpdConsultationCrud::class);
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        $uid = $this->currentUserId();
        $request = RadiologyRequest::findOrFail($this->editingId);
        $erId = $request->er_admission_id;
        $dsId = $request->dialysis_session_id;
        $orId = $request->or_schedule_id;
        $admissionId = $request->admission_id;
        $obId = $request->ob_consultation_id;
        $drId = $request->dr_schedule_id;
        $request->update([
            'status' => 'Cancelled',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        RadiologyRequestItem::where('radiology_request_id', $this->editingId)->update([
            'status' => 'Cancelled',
            'updated_by' => $uid,
            'updated_date' => now(),
        ]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Radiology request cancelled successfully.');
        if ($erId) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($dsId) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($orId) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($admissionId) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($obId) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($drId) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showDetailModal = false;
        $this->showResultModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'viewingId', 'resultItemId', 'request_code', 'requesting_department_id',
            'requesting_person', 'request_notes', 'lineItems',
            'result_text', 'impression', 'technique', 'findings', 'doctor_notes', 'radiologist_id', 'performed_by', 'image_upload', 'existingImages'
        ]);
        $this->total_amount = '0';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function openResultModal(int $itemId): void
    {
        $item = RadiologyRequestItem::with('images')->findOrFail($itemId);
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

        $item = RadiologyRequestItem::with('images')->findOrFail($this->resultItemId);
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
            \App\Models\Radiology\RadiologyResultImage::create([
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

        $request = $item->radiologyRequest;
        if ($request->er_admission_id) $this->dispatch('refreshErOrders')->to(ErAdmissionCrud::class);
        if ($request->dialysis_session_id) $this->dispatch('refreshDsOrders')->to(\App\Livewire\RenalCare\DialysisSessionCrud::class);
        if ($request->or_schedule_id) $this->dispatch('refreshOrOrders')->to(\App\Livewire\Or\OrScheduleCrud::class);
        if ($request->admission_id) $this->dispatch('refreshAdmissionOrders')->to(\App\Livewire\Admission\AdmissionCrud::class);
        if ($request->ob_consultation_id) $this->dispatch('refreshObOrders')->to(\App\Livewire\Ob\ObConsultationCrud::class);
        if ($request->dr_schedule_id) $this->dispatch('refreshDrOrders')->to(\App\Livewire\Delivery\DeliveryScheduleCrud::class);
        if ($request->opd_consultation_id) $this->dispatch('refreshOpdOrders')->to(\App\Livewire\Opd\OpdConsultationCrud::class);
        
        if ($this->showDetailModal && $this->viewingId) {
            $this->detailRequest = RadiologyRequest::with(['requestingDepartment', 'items.examType.modality', 'items.examType.bodyPart', 'patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])->find($this->viewingId);
        }
    }

    public function render()
    {
        $items = RadiologyRequest::query()
            ->with(['requestingDepartment', 'items', 'patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'drSchedule.patient', 'obConsultation.patient'])
            ->where('tenant_id', $this->currentTenantId())
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('request_code', 'like', '%' . $this->search . '%')
                          ->orWhere('requesting_person', 'like', '%' . $this->search . '%')
                          ->orWhereHas('requestingDepartment', fn ($d) =>
                              $d->where('dept_name', 'like', '%' . $this->search . '%')
                                ->orWhere('dept_code', 'like', '%' . $this->search . '%')
                          )
                          ->orWhereHas('patient', fn ($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('patient_code', 'like', '%' . $this->search . '%')
                          )
                          ->orWhereHas('erAdmission.patient', fn($p) => 
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                          )
                          ->orWhereHas('opdConsultation.patient', fn($p) => 
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                          )
                           ->orWhereHas('admission.patient', fn($p) => 
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                           ->orWhereHas('dialysisSession.patient', fn($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                           ->orWhereHas('orSchedule.patient', fn($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                           ->orWhereHas('drSchedule.patient', fn($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                           ->orWhereHas('obConsultation.patient', fn($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                           )
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $departments = Department::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->where('history', 1)
            ->orderBy('dept_name')
            ->get(['id', 'dept_code', 'dept_name']);

        $examTypes = RadiologyExamType::query()
            ->with('modality')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'modality_id', 'standard_charge']);
            
        $radiologists = \App\Models\HealthCareProfessional::query()
            ->where('tenant_id', $this->currentTenantId())
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'professional_code']);

        $technologists = User::whereIn('role', ['admin', 'superadmin', 'staff'])
            ->orderBy('full_name')->get(['id', 'full_name']);

        $detailRequest = null;
        if ($this->showDetailModal && $this->viewingId) {
            $detailRequest = RadiologyRequest::with(['requestingDepartment', 'items.examType.modality', 'items.examType.bodyPart', 'patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient'])
                ->find($this->viewingId);
        }

        return view('livewire.radiology.radiology-dispensing-crud', compact(
            'items', 'departments', 'examTypes', 'detailRequest', 'radiologists'
        ));
    }
}
