<?php

namespace App\Livewire\Opd;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Livewire\Concerns\ChecksActiveEncounters;
use App\Models\Opd\OpdConsultation;
use App\Models\Opd\OpdClinic;
use App\Models\Opd\OpdConsultationType;
use App\Models\Opd\OpdVisitType;
use App\Models\Opd\OpdReferralSource;
use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\LabTest;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Pharmacy\PharmacyRequestItem;
use App\Models\Supply\Item as SupplyItem;
use App\Models\CsrRequest;
use App\Models\CsrRequestItem;
use App\Models\Radiology\RadiologyExamType;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Radiology\RadiologyRequestItem;
use App\Models\Procedures\Procedure;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Procedures\ProcedureRequestItem;
use App\Models\Department;
use App\Models\Yakap\YakapEmpanelment;
use Illuminate\Support\Facades\DB;

class OpdConsultationCrud extends Component
{
    use WithPagination, ChecksActiveEncounters;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'consultation_datetime';
    public $sortDirection = 'desc';

    // Encounter Create
    public $showCreateModal = false;
    public $patient_id, $opd_clinic_id, $attending_physician_id;
    public $consultation_type_id, $visit_type_id, $referral_source_id;
    public bool $is_yakap = false;
    public ?int $empanelment_id = null;

    // Patient Search
    public string $patient_search = '';
    public bool $skipPatientSearchUpdate = false;
    public ?string $selected_patient_name = null;

    // View States
    public $showConsultationForm = false;
    public $currentConsultationId;
    public $consultation;

    // Vitals (Nursing)
    public $showVitalsModal = false;
    public $vital_bp_systolic, $vital_bp_diastolic, $vital_heart_rate, $vital_temperature;
    public $vital_respiratory_rate, $vital_weight, $vital_height, $vital_o2_saturation;
    public $chief_complaint;

    // SOAP & Treatment
    public $history_of_present_illness, $physical_exam_findings;
    public $assessment, $treatment_plan, $orders_summary;
    public $disposition, $next_follow_up_date;

    // Orders UI
    public $showOrderLabModal = false;
    public $showOrderPharmacyModal = false;
    public $showOrderCsrModal = false;
    public $showOrderRadiologyModal = false;
    public $showOrderProcedureModal = false;

    // Order Selections
    public $orderPriority = 'Routine';
    public $orderNotes = '';
    public string $orderSearch = '';
    public $orderSelection = [];
    public $orderQuantities = [];
    public $orderFrequencies = [];
    public $orderRoutes = [];

    // Medical Certificate Modal
    public bool $showMedCertModal = false;
    public string $medCertType = 'fit_to_work';
    public int $medCertRestDays = 3;
    public string $medCertRemarks = '';
    public string $medCertDiagnosis = '';
    public ?int $medCertConsultationId = null;

    public function openMedCertModal(?int $consultationId = null): void
    {
        $id = $consultationId ?? $this->currentConsultationId;
        $this->medCertConsultationId = $id;
        $consult = OpdConsultation::find($id);
        $this->medCertDiagnosis = $consult?->assessment ?? ($consult?->chief_complaint ?? '');
        $this->showMedCertModal = true;
    }

    public function closeMedCertModal(): void
    {
        $this->showMedCertModal = false;
    }

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

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
        $this->reset(['patient_id', 'opd_clinic_id', 'attending_physician_id', 'consultation_type_id', 'visit_type_id', 'referral_source_id', 'empanelment_id', 'patient_search', 'selected_patient_name']);
        $this->is_yakap = false;
        $this->showCreateModal = true;
    }

    public function updatedPatientSearch()
    {
        if (!$this->skipPatientSearchUpdate) {
            $this->patient_id = null;
            $this->selected_patient_name = null;
        }
        $this->skipPatientSearchUpdate = false;
    }

    public function selectPatient($id, $name)
    {
        $this->patient_id = $id;
        $this->selected_patient_name = $name;
        $this->skipPatientSearchUpdate = true;
        $this->patient_search = '';
    }

    private function generateConsultationCode(int $tid): string
    {
        $last = OpdConsultation::where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        if (!$last || !$last->consultation_code) return 'OPD-00001';
        $num = (int)str_replace('OPD-', '', $last->consultation_code);
        return 'OPD-' . str_pad($num + 1, 5, '0', STR_PAD_LEFT);
    }

    public function saveEncounter(): void
    {
        $this->validate([
            'patient_id' => 'required|exists:patients,id',
            'opd_clinic_id' => 'required|exists:opd_clinics,id',
            'attending_physician_id' => 'required|exists:health_care_professionals,id',
            'consultation_type_id' => 'required|exists:opd_consultation_types,id',
            'visit_type_id' => 'required|exists:opd_visit_types,id',
        ]);

        $this->ensureNoActiveEncounter($this->patient_id);

        $tid = $this->currentTenantId();
        OpdConsultation::create([
            'tenant_id' => $tid,
            'patient_id' => $this->patient_id,
            'consultation_code' => $this->generateConsultationCode($tid),
            'opd_clinic_id' => $this->opd_clinic_id,
            'attending_physician_id' => $this->attending_physician_id,
            'consultation_type_id' => $this->consultation_type_id,
            'visit_type_id' => $this->visit_type_id,
            'referral_source_id' => $this->referral_source_id,
            'consultation_datetime' => now(),
            'status' => 'Pending',
            'is_yakap' => $this->is_yakap,
            'empanelment_id' => $this->is_yakap ? $this->empanelment_id : null,
            'created_by' => $this->currentUserId(),
            'created_date' => now(),
        ]);

        $this->showCreateModal = false;
        session()->flash('message', 'OPD Encounter created successfully.');
    }

    public function openVitals(int $id): void
    {
        $c = OpdConsultation::findOrFail($id);
        $this->currentConsultationId = $c->id;
        $this->vital_bp_systolic = $c->vital_bp_systolic;
        $this->vital_bp_diastolic = $c->vital_bp_diastolic;
        $this->vital_heart_rate = $c->vital_heart_rate;
        $this->vital_temperature = $c->vital_temperature;
        $this->vital_respiratory_rate = $c->vital_respiratory_rate;
        $this->vital_weight = $c->vital_weight;
        $this->vital_height = $c->vital_height;
        $this->vital_o2_saturation = $c->vital_o2_saturation;
        $this->chief_complaint = $c->chief_complaint;
        $this->showVitalsModal = true;
    }

    public function saveVitals(): void
    {
        $c = OpdConsultation::findOrFail($this->currentConsultationId);
        $c->update([
            'vital_bp_systolic' => $this->vital_bp_systolic,
            'vital_bp_diastolic' => $this->vital_bp_diastolic,
            'vital_heart_rate' => $this->vital_heart_rate,
            'vital_temperature' => $this->vital_temperature,
            'vital_respiratory_rate' => $this->vital_respiratory_rate,
            'vital_weight' => $this->vital_weight,
            'vital_height' => $this->vital_height,
            'vital_o2_saturation' => $this->vital_o2_saturation,
            'chief_complaint' => $this->chief_complaint,
            'status' => $c->status === 'Pending' ? 'In Progress' : $c->status,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->showVitalsModal = false;
        session()->flash('message', 'Vitals saved successfully.');
    }

    public function treatConsultation(int $id): void
    {
        $this->consultation = OpdConsultation::with(['patient', 'opdClinic', 'attendingPhysician', 'labRequests.items.labTest', 'pharmacyRequests.items.drug', 'csrRequests.items.item', 'radiologyRequests.items.examType', 'procedureRequests.items.procedure'])->findOrFail($id);
        $this->currentConsultationId = $id;
        $this->history_of_present_illness = $this->consultation->history_of_present_illness;
        $this->physical_exam_findings = $this->consultation->physical_exam_findings;
        $this->assessment = $this->consultation->assessment;
        $this->treatment_plan = $this->consultation->treatment_plan;
        $this->orders_summary = $this->consultation->orders_summary;
        $this->disposition = $this->consultation->disposition;
        $this->next_follow_up_date = $this->consultation->next_follow_up_date;
        $this->showConsultationForm = true;
    }

    public function closeConsultation(): void
    {
        $this->showConsultationForm = false;
        $this->consultation = null;
    }

    #[On('refreshOpdOrders')]
    public function refreshOpdOrders(): void
    {
        if ($this->currentConsultationId && $this->showConsultationForm) {
            $this->treatConsultation($this->currentConsultationId);
        }
    }

    public function saveConsultation(): void
    {
        $this->consultation->update([
            'history_of_present_illness' => $this->history_of_present_illness,
            'physical_exam_findings' => $this->physical_exam_findings,
            'assessment' => $this->assessment,
            'treatment_plan' => $this->treatment_plan,
            'orders_summary' => $this->orders_summary,
            'disposition' => $this->disposition,
            'next_follow_up_date' => $this->next_follow_up_date,
            'status' => $this->disposition ? 'Completed' : 'In Progress',
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        session()->flash('message', 'Consultation notes saved.');
        $this->treatConsultation($this->currentConsultationId); // refresh
    }

    // Modal Triggers
    public function openOrderModal(string $type): void
    {
        $this->reset(['orderPriority', 'orderNotes', 'orderSearch', 'orderSelection', 'orderQuantities', 'orderFrequencies', 'orderRoutes']);
        if($type === 'Lab') $this->showOrderLabModal = true;
        if($type === 'Pharmacy') $this->showOrderPharmacyModal = true;
        if($type === 'CSR') $this->showOrderCsrModal = true;
        if($type === 'Radiology') $this->showOrderRadiologyModal = true;
        if($type === 'Procedure') $this->showOrderProcedureModal = true;
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showVitalsModal = false;
        $this->showOrderLabModal = false;
        $this->showOrderPharmacyModal = false;
        $this->showOrderCsrModal = false;
        $this->showOrderRadiologyModal = false;
        $this->showOrderProcedureModal = false;
        $this->orderSearch = '';
    }

    // Orders Placement Logic
    public function placeLabOrder(): void
    {
        $this->validate(['orderSelection' => 'required|array|min:1']);
        $tid = $this->currentTenantId();
        
        $reqCode = 'LR-' . str_pad((LabRequest::where('tenant_id', $tid)->count() + 1), 5, '0', STR_PAD_LEFT);
        
        $dept = Department::where('dept_name', 'Laboratory')->first();
        
        DB::transaction(function() use ($tid, $reqCode, $dept) {
            $req = LabRequest::create([
                'tenant_id' => $tid,
                'opd_consultation_id' => $this->currentConsultationId,
                'patient_id' => $this->consultation->patient_id,
                'request_code' => $reqCode,
                'requesting_department_id' => $this->consultation->opdClinic->department_id ?? null,
                'requesting_doctor_id' => auth()->user()?->health_care_professional_id,
                'priority' => $this->orderPriority,
                'status' => 'Pending',
                'request_notes' => $this->orderNotes,
                'created_by' => $this->currentUserId(),
                'updated_by' => $this->currentUserId(),
            ]);
            
            $total = 0;
            $uid = $this->currentUserId();
            foreach($this->orderSelection as $testId) {
                $test = LabTest::find($testId);
                if(!$test) continue;
                $cost = (float)($test->standard_charge ?? 0);
                LabRequestItem::create([
                    'lab_request_id' => $req->id,
                    'item_type' => 'Test',
                    'lab_test_id' => $test->id,
                    'test_name' => $test->name,
                    'charge_amount' => $cost,
                    'created_by' => $uid,
                    'updated_by' => $uid,
                ]);
                $total += $cost;
            }
            $req->update(['total_charge' => $total]);
        });
        
        $this->closeModal();
        $this->treatConsultation($this->currentConsultationId);
        session()->flash('message', "Laboratory request {$reqCode} placed successfully.");
    }

    public function placePharmacyOrder(): void
    {
        $this->validate(['orderSelection' => 'required|array|min:1']);
        $tid = $this->currentTenantId();
        
        $reqCode = 'PR-' . str_pad((PharmacyRequest::where('tenant_id', $tid)->count() + 1), 5, '0', STR_PAD_LEFT);
        
        DB::transaction(function() use ($tid, $reqCode) {
            $req = PharmacyRequest::create([
                'tenant_id' => $tid,
                'opd_consultation_id' => $this->currentConsultationId,
                'request_code' => $reqCode,
                'requesting_department_id' => $this->consultation->opdClinic->department_id ?? null,
                'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
                'requesting_doctor_id' => auth()->user()?->health_care_professional_id,
                'priority' => $this->orderPriority,
                'status' => 'Pending',
                'notes' => $this->orderNotes,
                'created_by' => $this->currentUserId(),
                'updated_by' => $this->currentUserId(),
            ]);
            
            $total = 0;
            foreach($this->orderSelection as $drugId) {
                $drug = Drug::find($drugId);
                if(!$drug) continue;
                
                $qty = (float)($this->orderQuantities[$drugId] ?? 1);
                $cost = (float)($drug->unit_cost ?? 0); // Assuming drug has unit cost or handled by Pharmacy
                $lineTotal = $qty * $cost;
                
                PharmacyRequestItem::create([
                    'pharmacy_request_id' => $req->id,
                    'drug_id' => $drug->id,
                    'item_name' => $drug->generic_name . ' ' . $drug->drug_strength,
                    'generic_name' => $drug->generic_name,
                    'brand_name' => $drug->brand_name,
                    'dosage_form' => $drug->dosage_form,
                    'philhealth_item_code' => $drug->philhealth_item_code,
                    'quantity' => $qty,
                    'frequency' => $this->orderFrequencies[$drugId] ?? null,
                    'route' => $this->orderRoutes[$drugId] ?? $drug->route,
                    'unit_cost' => $cost,
                    'total_cost' => $lineTotal,
                    'created_by' => $this->currentUserId(),
                    'updated_by' => $this->currentUserId(),
                ]);
                $total += $lineTotal;
            }
            $req->update(['total_amount' => $total]);
        });
        
        $this->closeModal();
        $this->treatConsultation($this->currentConsultationId);
        session()->flash('message', "Pharmacy order {$reqCode} placed successfully.");
    }

    public function placeCsrOrder(): void
    {
        $this->validate(['orderSelection' => 'required|array|min:1']);
        $tid = $this->currentTenantId();
        
        $reqCode = 'CR-' . str_pad((CsrRequest::where('tenant_id', $tid)->count() + 1), 5, '0', STR_PAD_LEFT);
        
        DB::transaction(function() use ($tid, $reqCode) {
            $req = CsrRequest::create([
                'tenant_id' => $tid,
                'opd_consultation_id' => $this->currentConsultationId,
                'patient_id' => $this->consultation->patient_id,
                'request_code' => $reqCode,
                'requesting_department_id' => $this->consultation->opdClinic->department_id ?? null,
                'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
                'status' => 'Pending',
                'notes' => $this->orderNotes,
                'created_by' => $this->currentUserId(),
                'updated_by' => $this->currentUserId(),
            ]);
            
            $total = 0;
            foreach($this->orderSelection as $itemId) {
                $item = SupplyItem::find($itemId);
                if(!$item) continue;
                
                $qty = (int)($this->orderQuantities[$itemId] ?? 1);
                $cost = (float)($item->unit_cost ?? 0) * $qty;
                
                CsrRequestItem::create([
                    'csr_request_id' => $req->id,
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_code' => $item->code,
                    'supply_unit_name' => $item->unit?->name ?? '',
                    'quantity' => $qty,
                    'unit_cost' => $item->unit_cost ?? 0,
                    'total_cost' => $cost,
                    'status' => 'Pending',
                    'created_by' => $this->currentUserId(),
                    'updated_by' => $this->currentUserId(),
                ]);
                $total += $cost;
            }
            $req->update(['total_amount' => $total]);
        });
        
        $this->closeModal();
        $this->treatConsultation($this->currentConsultationId);
        session()->flash('message', "CSR supply order {$reqCode} placed successfully.");
    }

    public function placeRadiologyOrder(): void
    {
        $this->validate(['orderSelection' => 'required|array|min:1']);
        $tid = $this->currentTenantId();
        
        $reqCode = 'RAD-' . str_pad((RadiologyRequest::where('tenant_id', $tid)->count() + 1), 5, '0', STR_PAD_LEFT);
        
        DB::transaction(function() use ($tid, $reqCode) {
            $req = RadiologyRequest::create([
                'tenant_id' => $tid,
                'opd_consultation_id' => $this->currentConsultationId,
                'patient_id' => $this->consultation->patient_id,
                'request_code' => $reqCode,
                'requesting_department_id' => $this->consultation->opdClinic->department_id ?? null,
                'requesting_person' => auth()->user()?->full_name ?? session('jwt_user.full_name', 'System'),
                'requesting_doctor_id' => auth()->user()?->health_care_professional_id,
                'status' => 'Pending',
                'notes' => $this->orderNotes,
                'created_by' => $this->currentUserId(),
                'updated_by' => $this->currentUserId(),
            ]);
            
            $total = 0;
            foreach($this->orderSelection as $examId) {
                $exam = RadiologyExamType::find($examId);
                if(!$exam) continue;
                
                $charge = (float)($exam->standard_charge ?? 0);
                
                RadiologyRequestItem::create([
                    'radiology_request_id' => $req->id,
                    'exam_type_id' => $exam->id,
                    'exam_name' => $exam->name,
                    'modality_name' => $exam->modality?->name ?? '',
                    'charge_amount' => $charge,
                    'status' => 'Pending',
                    'created_by' => $this->currentUserId(),
                    'updated_by' => $this->currentUserId(),
                ]);
                $total += $charge;
            }
            $req->update(['total_amount' => $total]);
        });
        
        $this->closeModal();
        $this->treatConsultation($this->currentConsultationId);
        session()->flash('message', "Radiology request {$reqCode} placed successfully.");
    }

    public function placeProcedureOrder(): void
    {
        $this->validate(['orderSelection' => 'required|array|min:1']);
        $tid = $this->currentTenantId();
        
        $reqCode = 'PROC-' . str_pad((ProcedureRequest::where('tenant_id', $tid)->count() + 1), 5, '0', STR_PAD_LEFT);
        
        DB::transaction(function() use ($tid, $reqCode) {
            $req = ProcedureRequest::create([
                'tenant_id' => $tid,
                'opd_consultation_id' => $this->currentConsultationId,
                'request_code' => $reqCode,
                'requesting_department_id' => $this->consultation->opdClinic->department_id ?? null,
                'requesting_doctor_id' => auth()->user()?->health_care_professional_id,
                'status' => 'Pending',
                'notes' => $this->orderNotes,
                'created_by' => $this->currentUserId(),
                'updated_by' => $this->currentUserId(),
            ]);
            
            $total = 0;
            foreach($this->orderSelection as $procId) {
                $proc = Procedure::find($procId);
                if(!$proc) continue;
                
                $qty = (int)($this->orderQuantities[$procId] ?? 1);
                $charge = (float)($proc->standard_charge ?? 0) * $qty;
                
                ProcedureRequestItem::create([
                    'procedure_request_id' => $req->id,
                    'procedure_id' => $proc->id,
                    'procedure_name' => $proc->name,
                    'category_name' => $proc->category?->name ?? '',
                    'quantity' => $qty,
                    'unit_charge' => $proc->standard_charge ?? 0,
                    'total_charge' => $charge,
                    'status' => 'Pending',
                    'created_by' => $this->currentUserId(),
                    'updated_by' => $this->currentUserId(),
                ]);
                $total += $charge;
            }
            $req->update(['total_amount' => $total]);
        });
        
        $this->closeModal();
        $this->treatConsultation($this->currentConsultationId);
        session()->flash('message', "Procedure order {$reqCode} placed successfully.");
    }

    public function render()
    {
        $tid = $this->currentTenantId();

        if ($this->showConsultationForm && $this->consultation) {
            return view('livewire.opd.opd-consultation-crud', [
                'consultations' => collect(),
                'patients' => [],
                'clinics' => [],
                'doctors' => [],
                'consultationTypes' => [],
                'visitTypes' => [],
                'referralSources' => [],
                'labTests' => $this->showOrderLabModal ? LabTest::where('tenant_id', $tid)
                    ->where('is_active', true)
                    ->when($this->orderSearch, function($q) {
                        $term = '%' . $this->orderSearch . '%';
                        $q->where(function($sub) use ($term) {
                            $sub->where('name', 'like', $term)
                                ->orWhere('test_code', 'like', $term);
                        });
                    })
                    ->orderBy('name')->get() : collect(),
                'drugs' => $this->showOrderPharmacyModal ? Drug::where('tenant_id', $tid)
                    ->where('is_active', true)
                    ->whereHas('batches', function ($q) {
                        $q->where('quantity_on_hand', '>', 0)
                          ->where('expiry_date', '>=', now()->toDateString())
                          ->where('history', 1);
                    })
                    ->when($this->orderSearch, function($q) {
                        $term = '%' . $this->orderSearch . '%';
                        $q->where(function($sub) use ($term) {
                            $sub->where('generic_name', 'like', $term)
                                ->orWhere('brand_name', 'like', $term)
                                ->orWhere('drug_code', 'like', $term);
                        });
                    })
                    ->orderBy('generic_name')->get() : collect(),
                'csrItems' => $this->showOrderCsrModal ? SupplyItem::with('unit')
                    ->where('tenant_id', $tid)
                    ->where('is_active', true)
                    ->whereHas('batches', function ($q) {
                        $q->where('quantity_on_hand', '>', 0)
                          ->where('expiry_date', '>=', now()->toDateString())
                          ->where('history', 1)
                          ->where('is_active', 1);
                    })
                    ->when($this->orderSearch, function($q) {
                        $term = '%' . $this->orderSearch . '%';
                        $q->where(function($sub) use ($term) {
                            $sub->where('name', 'like', $term)
                                ->orWhere('code', 'like', $term);
                        });
                    })
                    ->orderBy('name')->get() : collect(),
                'radiologyExams' => $this->showOrderRadiologyModal ? RadiologyExamType::with('modality')
                    ->where('tenant_id', $tid)
                    ->where('is_active', true)
                    ->when($this->orderSearch, function($q) {
                        $term = '%' . $this->orderSearch . '%';
                        $q->where(function($sub) use ($term) {
                            $sub->where('name', 'like', $term)
                                ->orWhere('code', 'like', $term)
                                ->orWhereHas('modality', fn($m) => $m->where('name', 'like', $term));
                        });
                    })
                    ->orderBy('name')->get() : collect(),
                'procedures' => $this->showOrderProcedureModal ? Procedure::with('category')
                    ->where('tenant_id', $tid)
                    ->where('is_active', true)
                    ->when($this->orderSearch, function($q) {
                        $term = '%' . $this->orderSearch . '%';
                        $q->where(function($sub) use ($term) {
                            $sub->where('name', 'like', $term)
                                ->orWhere('code', 'like', $term);
                        });
                    })
                    ->orderBy('name')->get() : collect(),
            ])->layout('components.layouts.app');
        }

        $query = OpdConsultation::with(['patient', 'opdClinic', 'attendingPhysician', 'empanelment'])
            ->where('tenant_id', $tid)
            ->where('history', 1);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('consultation_code', 'like', '%'.$this->search.'%')
                  ->orWhereHas('patient', function($p) {
                      $p->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                  });
            });
        }

        $query->orderBy($this->sortField, $this->sortDirection);
        $consultations = $query->paginate($this->perPage);

        $patients = collect();
        if ($this->patient_search && !$this->patient_id) {
            $patients = Patient::where('tenant_id', $tid)
                ->where('history', 1)
                ->where(function($q) {
                    $q->where('first_name', 'like', '%'.$this->patient_search.'%')
                      ->orWhere('last_name', 'like', '%'.$this->patient_search.'%')
                      ->orWhere('patient_code', 'like', '%'.$this->patient_search.'%');
                })
                ->orderBy('last_name')
                ->limit(15)
                ->get();
        }

        return view('livewire.opd.opd-consultation-crud', [
            'consultations' => $consultations,
            'patients' => $patients,
            'clinics' => OpdClinic::where('is_active', true)->orderBy('name')->get(),
            'doctors' => HealthCareProfessional::where('tenant_id', $tid)->where('is_active', true)->orderBy('full_name')->get(),
            'consultationTypes' => OpdConsultationType::where('is_active', true)->orderBy('name')->get(),
            'visitTypes' => OpdVisitType::where('is_active', true)->orderBy('name')->get(),
            'referralSources' => OpdReferralSource::where('is_active', true)->orderBy('name')->get(),
            'yakapEmpanelments' => $this->is_yakap
                ? YakapEmpanelment::with('patient')
                    ->where('tenant_id', $tid)
                    ->where('status', 'Active')
                    ->where('history', 1)
                    ->orderBy('empanelment_code')
                    ->get()
                : collect(),
        ])->layout('components.layouts.app');
    }
}
