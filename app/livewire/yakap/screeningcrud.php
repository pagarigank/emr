<?php

namespace App\Livewire\Yakap;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\Yakap\YakapEmpanelment;
use App\Models\Yakap\YakapScreening;
use Livewire\Component;
use Livewire\WithPagination;

class ScreeningCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'screening_date', $sortDirection = 'desc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;

    public ?int $empanelment_id = null, $patient_id = null, $performed_by = null;
    public string $screening_type = '', $screening_name = '', $screening_date = '';
    public string $result = '', $result_details = '', $facility_name = '';
    public string $status = 'Pending', $next_screening_due = '', $notes = '';
    public string $empanelment_search = '';

    private array $screeningTypes = [
        'Breast', 'Cervical', 'Colon', 'Prostate', 'Lung', 'Oral'
    ];

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
        $s = YakapScreening::findOrFail($id);
        $this->editingId = $s->id;
        $this->empanelment_id = $s->empanelment_id;
        $this->patient_id = $s->patient_id;
        $this->screening_type = $s->screening_type;
        $this->screening_name = $s->screening_name;
        $this->screening_date = $s->screening_date?->format('Y-m-d') ?? '';
        $this->result = $s->result ?? '';
        $this->result_details = $s->result_details ?? '';
        $this->facility_name = $s->facility_name ?? '';
        $this->performed_by = $s->performed_by;
        $this->status = $s->status;
        $this->next_screening_due = $s->next_screening_due?->format('Y-m-d') ?? '';
        $this->notes = $s->notes ?? '';
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
            'screening_type' => ['required', 'string', 'max:50'],
            'screening_name' => ['required', 'string', 'max:200'],
            'screening_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:30'],
        ]);

        $data = [
            'tenant_id' => $tid,
            'empanelment_id' => $this->empanelment_id,
            'patient_id' => $this->patient_id,
            'screening_type' => $this->screening_type,
            'screening_name' => $this->screening_name,
            'screening_date' => $this->screening_date ?: null,
            'result' => $this->result ?: null,
            'result_details' => $this->result_details ?: null,
            'facility_name' => $this->facility_name ?: null,
            'performed_by' => $this->performed_by,
            'status' => $this->status,
            'next_screening_due' => $this->next_screening_due ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->isEditing && $this->editingId) {
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            YakapScreening::where('id', $this->editingId)->update($data);
            session()->flash('message', 'Screening record updated successfully.');
        } else {
            $data['created_by'] = $uid;
            $data['created_date'] = now();
            $data['updated_by'] = $uid;
            $data['updated_date'] = now();
            YakapScreening::create($data);
            session()->flash('message', 'Screening record created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function updatedScreeningType(): void
    {
        $names = [
            'Breast' => 'Breast Cancer Screening (Clinical Breast Exam / Mammogram)',
            'Cervical' => 'Cervical Cancer Screening (Pap Smear / HPV DNA)',
            'Colon' => 'Colorectal Cancer Screening (FOBT / Colonoscopy)',
            'Prostate' => 'Prostate Cancer Screening (PSA / DRE)',
            'Lung' => 'Lung Cancer Screening (Low-Dose CT)',
            'Oral' => 'Oral Cancer Screening (Visual / Palpation Exam)',
        ];
        $this->screening_name = $names[$this->screening_type] ?? '';
    }

    public function confirmDelete(int $id): void
    {
        $this->editingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        YakapScreening::where('id', $this->editingId)->update([
            'history' => 0,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->showDeleteModal = false;
        $this->editingId = null;
        session()->flash('message', 'Screening record archived successfully.');
    }

    public function updatedEmpanelmentId(): void
    {
        if ($this->empanelment_id) {
            $emp = YakapEmpanelment::with('patient')->find($this->empanelment_id);
            if ($emp) $this->patient_id = $emp->patient_id;
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'empanelment_id', 'patient_id', 'screening_type', 'screening_name',
            'screening_date', 'result', 'result_details', 'facility_name', 'performed_by',
            'next_screening_due', 'notes', 'empanelment_search']);
        $this->status = 'Pending';
    }

    public function render()
    {
        $tid = $this->currentTenantId();
        $query = YakapScreening::with(['empanelment', 'patient', 'performedBy'])->where('tenant_id', $tid);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('screening_type', 'like', '%' . $this->search . '%')
                  ->orWhere('screening_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('patient', fn ($pq) => $pq->where('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->search . '%'));
            });
        }

        $screenings = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.yakap.screening-crud', [
            'screenings' => $screenings,
            'screeningTypes' => $this->screeningTypes,
            'empanelments' => $this->empanelment_search
                ? YakapEmpanelment::where('tenant_id', $tid)
                    ->where(function ($q) {
                        $q->where('empanelment_code', 'like', '%' . $this->empanelment_search . '%')
                          ->orWhereHas('patient', fn ($pq) => $pq->where('last_name', 'like', '%' . $this->empanelment_search . '%'));
                    })->orderBy('empanelment_code')->get()
                : collect(),
            'doctors' => HealthCareProfessional::where('tenant_id', $tid)->where('is_active', true)->orderBy('full_name')->get(),
        ]);
    }
}
