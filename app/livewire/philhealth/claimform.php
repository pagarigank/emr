<?php

namespace App\Livewire\Philhealth;

use App\Models\Patient;
use App\Models\Icd10Code;
use App\Models\PhilhealthMember;
use App\Models\PhilhealthCaseRate;
use App\Models\PhilhealthClaim;
use App\Models\PhilhealthZBenefit;
use App\Models\Delivery\DeliveryType;
use Livewire\Component;

class ClaimForm extends Component
{
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $claim_code = '';
    public string $status = 'Draft';
    public string $claim_type = 'ACR';

    public ?int $patient_id = null;
    public string $patient_search = '';
    public ?int $philhealth_member_id = null;
    public ?int $icd10_code_id = null;
    public ?int $secondary_icd10_code_id = null;
    public ?int $tertiary_icd10_code_id = null;
    public ?int $case_rate_id = null;
    public ?int $second_case_rate_id = null;

    public string $date_admitted = '';
    public string $time_admitted = '';
    public string $date_discharged = '';
    public string $time_discharged = '';
    public string $encounter_type = 'admission';

    public string $admitting_diagnosis = '';
    public string $discharge_diagnosis = '';

    public string $history_of_present_illness = '';
    public string $pertinent_past_medical_history = '';
    public string $physical_exam_findings = '';
    public string $course_in_ward = '';
    public string $lab_imaging_summary = '';
    public string $chief_complaint = '';

    public string $lmp_date = '';
    public string $edc_date = '';
    public ?int $gravida = null;
    public ?int $para = null;
    public string $delivery_date = '';
    public ?int $delivery_type_id = null;
    public string $newborn_outcome = '';

    public string $outcome = '';
    public string $remarks = '';
    public string $hci_signatory_name = '';

    public ?int $er_admission_id = null;
    public ?int $admission_id = null;

    public array $zBenefits = [];
    public array $filteredCaseRates = [];

    public $patients;
    public $philhealthMembers;
    public $allCaseRates;
    public $deliveryTypes;
    public $icd10Codes;

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:philhealth_claims,claim_code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId) {
            $u = "unique:philhealth_claims,claim_code,{$this->editingId},id,tenant_id,{$tid},history,1";
        }
        return [
            'claim_code' => ['required', 'string', 'max:20', $u],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'philhealth_member_id' => ['nullable', 'integer', 'exists:philhealth_members,id'],
            'icd10_code_id' => ['nullable', 'integer', 'exists:icd_10_codes,id'],
            'secondary_icd10_code_id' => ['nullable', 'integer', 'exists:icd_10_codes,id'],
            'tertiary_icd10_code_id' => ['nullable', 'integer', 'exists:icd_10_codes,id'],
            'case_rate_id' => ['nullable', 'integer', 'exists:philhealth_case_rates,id'],
            'second_case_rate_id' => ['nullable', 'integer', 'exists:philhealth_case_rates,id'],
            'status' => ['required', 'string', 'max:30'],
            'claim_type' => ['required', 'string', 'max:30'],
            'encounter_type' => ['nullable', 'string', 'max:20'],
            'admitting_diagnosis' => ['nullable', 'string'],
            'discharge_diagnosis' => ['nullable', 'string'],
            'date_admitted' => ['nullable', 'date'],
            'time_admitted' => ['nullable'],
            'date_discharged' => ['nullable', 'date'],
            'time_discharged' => ['nullable'],
            'history_of_present_illness' => ['nullable', 'string'],
            'pertinent_past_medical_history' => ['nullable', 'string'],
            'physical_exam_findings' => ['nullable', 'string'],
            'course_in_ward' => ['nullable', 'string'],
            'lab_imaging_summary' => ['nullable', 'string'],
            'chief_complaint' => ['nullable', 'string'],
            'outcome' => ['nullable', 'string', 'max:30'],
            'remarks' => ['nullable', 'string'],
            'hci_signatory_name' => ['nullable', 'string', 'max:150'],
            'er_admission_id' => ['nullable', 'integer'],
            'admission_id' => ['nullable', 'integer'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_type_id' => ['nullable', 'integer'],
            'newborn_outcome' => ['nullable', 'string', 'max:50'],
            'lmp_date' => ['nullable', 'date'],
            'edc_date' => ['nullable', 'date'],
            'gravida' => ['nullable', 'integer', 'min:0'],
            'para' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    public function generateClaimCode(): string
    {
        $prefix = 'PHC-';
        $last = PhilhealthClaim::where('tenant_id', $this->currentTenantId())
            ->where('claim_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('claim_code');
        $num = $last ? (int) substr($last, 4) + 1 : 1;
        return $prefix . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    public function mount(?int $claimId = null): void
    {
    }

    #[\Livewire\Attributes\On('open-claim-form')]
    public function openForm(?int $claimId = null): void
    {
        $this->loadLookups();
        if ($claimId) {
            $this->loadClaim($claimId);
        } else {
            $this->resetForm();
            $this->claim_code = $this->generateClaimCode();
        }
        $this->showModal = true;
    }

    #[\Livewire\Attributes\On('data-pulled')]
    public function receivePulledData(array $data): void
    {
        $this->date_admitted = $data['date_admitted'] ?? $this->date_admitted;
        $this->time_admitted = $data['time_admitted'] ?? $this->time_admitted;
        $this->date_discharged = $data['date_discharged'] ?? $this->date_discharged;
        $this->time_discharged = $data['time_discharged'] ?? $this->time_discharged;
        $this->admitting_diagnosis = $data['admitting_diagnosis'] ?? $this->admitting_diagnosis;
        $this->er_admission_id = $data['er_admission_id'] ?? $this->er_admission_id;
        $this->admission_id = $data['admission_id'] ?? $this->admission_id;
        $this->encounter_type = $data['encounter_type'] ?? $this->encounter_type;
        $this->claim_type = $data['claim_type'] ?? $this->claim_type;
        if (isset($data['chief_complaint'])) {
            $this->chief_complaint = $data['chief_complaint'];
        }
    }

    public function updatedIcd10CodeId(): void
    {
        $this->filterCaseRates();
    }

    private function filterCaseRates(): void
    {
        if (!$this->icd10_code_id || !$this->allCaseRates) {
            $this->filteredCaseRates = [];
            return;
        }
        $icd = Icd10Code::find($this->icd10_code_id);
        if (!$icd) {
            $this->filteredCaseRates = [];
            return;
        }
        $this->filteredCaseRates = $this->allCaseRates
            ->filter(fn($cr) => $cr->icd10_code_id == $this->icd10_code_id || $cr->icd10_code === $icd->code)
            ->values()
            ->toArray();
    }

    public function loadLookups(): void
    {
        $this->icd10Codes = Icd10Code::where('history', 1)->where('is_active', true)->orderBy('code')->get();
        $this->philhealthMembers = PhilhealthMember::where('history', 1)->where('is_active', true)->orderBy('last_name')->get();
        $this->allCaseRates = PhilhealthCaseRate::where('history', 1)->where('is_active', true)->orderBy('description')->get();
        $this->deliveryTypes = DeliveryType::where('history', 1)->where('is_active', true)->orderBy('name')->get();
    }

    public function loadClaim(int $id): void
    {
        $r = PhilhealthClaim::with(['zBenefits'])->findOrFail($id);
        $this->editingId = $r->id;
        $this->isEditing = true;

        $this->claim_code = $r->claim_code;
        $this->patient_id = $r->patient_id;
        $this->patient_search = $r->patient ? "{$r->patient->last_name}, {$r->patient->first_name} ({$r->patient->patient_code})" : '';
        $this->patients = $r->patient ? collect([$r->patient]) : collect();
        $this->philhealth_member_id = $r->philhealth_member_id;
        $this->icd10_code_id = $r->icd10_code_id;
        $this->secondary_icd10_code_id = $r->secondary_icd10_code_id;
        $this->tertiary_icd10_code_id = $r->tertiary_icd10_code_id;
        $this->case_rate_id = $r->case_rate_id;
        $this->second_case_rate_id = $r->second_case_rate_id;
        $this->status = $r->status;
        $this->claim_type = $r->claim_type;
        $this->encounter_type = $r->encounter_type ?? 'admission';
        $this->admitting_diagnosis = $r->admitting_diagnosis ?? '';
        $this->discharge_diagnosis = $r->discharge_diagnosis ?? '';
        $this->date_admitted = $r->date_admitted?->format('Y-m-d') ?? '';
        $this->time_admitted = $r->time_admitted ? substr($r->time_admitted, 0, 5) : '';
        $this->date_discharged = $r->date_discharged?->format('Y-m-d') ?? '';
        $this->time_discharged = $r->time_discharged ? substr($r->time_discharged, 0, 5) : '';
        $this->history_of_present_illness = $r->history_of_present_illness ?? '';
        $this->pertinent_past_medical_history = $r->pertinent_past_medical_history ?? '';
        $this->physical_exam_findings = $r->physical_exam_findings ?? '';
        $this->course_in_ward = $r->course_in_ward ?? '';
        $this->lab_imaging_summary = $r->lab_imaging_summary ?? '';
        $this->chief_complaint = $r->chief_complaint ?? '';
        $this->outcome = $r->outcome ?? '';
        $this->remarks = $r->remarks ?? '';
        $this->hci_signatory_name = $r->hci_signatory_name ?? '';
        $this->er_admission_id = $r->er_admission_id;
        $this->admission_id = $r->admission_id;
        $this->delivery_date = $r->delivery_date?->format('Y-m-d') ?? '';
        $this->delivery_type_id = $r->delivery_type_id;
        $this->newborn_outcome = $r->newborn_outcome ?? '';
        $this->lmp_date = $r->lmp_date?->format('Y-m-d') ?? '';
        $this->edc_date = $r->edc_date?->format('Y-m-d') ?? '';
        $this->gravida = $r->gravida;
        $this->para = $r->para;

        $this->zBenefits = [];
        foreach ($r->zBenefits as $zb) {
            $this->zBenefits[] = [
                'id' => $zb->id,
                'z_benefit_code' => $zb->z_benefit_code,
                'z_benefit_name' => $zb->z_benefit_name ?? '',
                'pre_authorization_code' => $zb->pre_authorization_code ?? '',
                'tranche_number' => $zb->tranche_number,
                'tranche_amount' => (float) $zb->tranche_amount,
            ];
        }

        $this->filterCaseRates();
    }

    public function save(): void
    {
        $data = $this->validate();

        $data['chief_complaint'] = $this->chief_complaint;
        $data['encounter_type'] = $this->encounter_type;
        $data['second_case_rate_id'] = $this->second_case_rate_id;

        if ($this->isEditing) {
            PhilhealthClaim::findOrFail($this->editingId)->update(array_merge($data, [
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]));
            $this->syncZBenefits($this->editingId);
            $claim = PhilhealthClaim::find($this->editingId);
            if ($claim) {
                app(\App\Services\Philhealth\OecbClaimFormatter::class)->saveFormattedChiefComplaint($claim);
            }
            $this->dispatch('toast', type: 'success', message: 'Claim updated successfully.');
        } else {
            $claim = PhilhealthClaim::create(array_merge($data, [
                'tenant_id' => $this->currentTenantId(),
                'history' => 1,
                'is_active' => true,
                'total_actual_amount' => 0,
                'total_claimed_amount' => 0,
                'created_by' => $this->currentUserId(),
                'created_date' => now(),
            ]));
            $this->syncZBenefits($claim->id);
            app(\App\Services\Philhealth\OecbClaimFormatter::class)->saveFormattedChiefComplaint($claim);
            $this->editingId = $claim->id;
            $this->dispatch('toast', type: 'success', message: 'Claim created successfully.');
        }

        $this->dispatch('claim-saved', claimId: $this->editingId);
    }

    private function syncZBenefits(int $claimId): void
    {
        $existingIds = PhilhealthZBenefit::where('philhealth_claim_id', $claimId)->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($this->zBenefits as $zb) {
            $zbData = [
                'tenant_id' => $this->currentTenantId(),
                'z_benefit_code' => $zb['z_benefit_code'],
                'z_benefit_name' => $zb['z_benefit_name'] ?? null,
                'pre_authorization_code' => $zb['pre_authorization_code'] ?? null,
                'tranche_number' => $zb['tranche_number'] ?? 1,
                'tranche_amount' => $zb['tranche_amount'] ?? 0,
                'history' => 1,
            ];

            if (isset($zb['id']) && in_array($zb['id'], $existingIds)) {
                $model = PhilhealthZBenefit::findOrFail($zb['id']);
                $model->update(array_merge($zbData, [
                    'updated_by' => $this->currentUserId(),
                    'updated_date' => now(),
                ]));
                $model->philhealth_claim_id = $claimId;
                $model->save();
                $submittedIds[] = $zb['id'];
            } else {
                $new = PhilhealthZBenefit::create(array_merge($zbData, [
                    'created_by' => $this->currentUserId(),
                    'created_date' => now(),
                ]));
                $new->philhealth_claim_id = $claimId;
                $new->tenant_id = $this->currentTenantId();
                $new->save();
                $submittedIds[] = $new->id;
            }
        }

        $toArchive = array_diff($existingIds, $submittedIds);
        if (!empty($toArchive)) {
            PhilhealthZBenefit::whereIn('id', $toArchive)->update([
                'history' => 0,
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);
        }
    }

    public function addZBenefit(): void
    {
        $this->zBenefits[] = [
            'z_benefit_code' => '',
            'z_benefit_name' => '',
            'pre_authorization_code' => '',
            'tranche_number' => 1,
            'tranche_amount' => 0,
        ];
    }

    public function removeZBenefit(int $index): void
    {
        unset($this->zBenefits[$index]);
        $this->zBenefits = array_values($this->zBenefits);
    }

    public function openPullUp(): void
    {
        if (!$this->patient_id) {
            $this->dispatch('toast', type: 'error', message: 'Select a patient first.');
            return;
        }
        $this->dispatch('open-pull-up', patientId: $this->patient_id);
    }

    public function openItems(): void
    {
        if (!$this->editingId) {
            $this->dispatch('toast', type: 'error', message: 'Save the claim first before adding items.');
            return;
        }
        $this->dispatch('open-items', claimId: $this->editingId);
    }

    public function selectPatient(int $id): void
    {
        $this->patient_id = $id;
        $patient = Patient::find($id);
        if ($patient && $patient->philhealth_member_id) {
            $this->philhealth_member_id = $patient->philhealth_member_id;
        }
        $this->patient_search = $patient ? "{$patient->last_name}, {$patient->first_name} ({$patient->patient_code})" : '';
        $this->patients = $patient ? collect([$patient]) : collect();
    }

    public function updatedPatientSearch(): void
    {
        $this->patients = Patient::where('history', 1)
            ->when($this->patient_search, fn($q) => $q->where(fn($i) => $i
                ->where('last_name', 'like', '%' . $this->patient_search . '%')
                ->orWhere('first_name', 'like', '%' . $this->patient_search . '%')
                ->orWhere('patient_code', 'like', '%' . $this->patient_search . '%')
            ))
            ->orderBy('last_name')->limit(10)->get();
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editingId = null;
        $this->isEditing = false;
        $this->claim_code = '';
        $this->status = 'Draft';
        $this->claim_type = 'ACR';
        $this->patient_id = null;
        $this->patient_search = '';
        $this->philhealth_member_id = null;
        $this->icd10_code_id = null;
        $this->secondary_icd10_code_id = null;
        $this->tertiary_icd10_code_id = null;
        $this->case_rate_id = null;
        $this->second_case_rate_id = null;
        $this->date_admitted = '';
        $this->time_admitted = '';
        $this->date_discharged = '';
        $this->time_discharged = '';
        $this->encounter_type = 'admission';
        $this->admitting_diagnosis = '';
        $this->discharge_diagnosis = '';
        $this->history_of_present_illness = '';
        $this->pertinent_past_medical_history = '';
        $this->physical_exam_findings = '';
        $this->course_in_ward = '';
        $this->lab_imaging_summary = '';
        $this->chief_complaint = '';
        $this->lmp_date = '';
        $this->edc_date = '';
        $this->gravida = null;
        $this->para = null;
        $this->delivery_date = '';
        $this->delivery_type_id = null;
        $this->newborn_outcome = '';
        $this->outcome = '';
        $this->remarks = '';
        $this->hci_signatory_name = '';
        $this->er_admission_id = null;
        $this->admission_id = null;
        $this->zBenefits = [];
        $this->filteredCaseRates = [];
    }

    public function render()
    {
        return view('livewire.philhealth.claim-form');
    }
}
