<?php

namespace App\Livewire\Doctors;

use App\Models\Admission;
use App\Models\Er\ErAdmission;
use App\Models\Opd\OpdConsultation;
use Livewire\Component;

class PatientChartCrud extends Component
{
    public string $type; // 'opd', 'er', 'ward'
    public int $recordId;
    
    // Model state
    public $record;
    public $patient;

    // Form fields for Clinical Notes
    public string $history_of_present_illness = '';
    public string $physical_exam_findings = '';
    public string $assessment = '';
    public string $orders_summary = '';
    public string $treatment_notes = '';
    public ?string $icd_10_code = null;

    // ICD-10 Search
    public string $searchIcd = '';
    public array $icdResults = [];

    // CPOE (Order Entry)
    public bool $showOrderModal = false;
    public string $orderCategory = 'lab';
    public string $orderPriority = 'Routine';
    public string $orderNotes = '';
    public array $selectedOrderItems = [];
    public string $searchOrderQuery = '';
    public array $orderSearchResults = [];

    // Active tab in the chart
    public string $activeTab = 'notes'; // notes, orders, results, records

    // Past encounter drill-down
    public bool $viewingPastEncounter = false;
    public ?array $pastEncounterDetail = null;

    public function mount(string $type, int $id)
    {
        $this->type = $type;
        $this->recordId = $id;

        $this->loadRecord();

        if (!$this->record) {
            abort(404, 'Encounter not found or unauthorized.');
        }

        $this->patient = $this->record->patient;

        // Hydrate clinical notes
        $this->history_of_present_illness = $this->record->history_of_present_illness ?? '';
        $this->physical_exam_findings = $this->record->physical_exam_findings ?? '';
        $this->assessment = $this->record->assessment ?? '';
        $this->orders_summary = $this->record->orders_summary ?? '';
        $this->treatment_notes = $this->record->treatment_notes ?? '';
        
        $this->icd_10_code = $this->record->icd_10_code ?? null;
        if ($this->icd_10_code) {
            $icd = \App\Models\Icd10Code::where('code', $this->icd_10_code)->first();
            if ($icd) {
                $this->searchIcd = $icd->code . ' - ' . $icd->description;
            } else {
                $this->searchIcd = $this->icd_10_code;
            }
        }
    }

    private function loadRecord()
    {
        $tenantId = request()->attributes->get('tenant_id');
        $doctorId = auth()->user()?->health_care_professional_id;
        $isAdmin = in_array(auth()->user()?->role, ['superadmin', 'tenant_admin']);

        switch ($this->type) {
            case 'opd':
                $this->record = OpdConsultation::with(['patient', 'labRequests.items.labTest.labUnit', 'labRequests.items.labTest.referenceRanges', 'pharmacyRequests', 'radiologyRequests.items.radiologist', 'radiologyRequests.items.images', 'procedureRequests'])->where('tenant_id', $tenantId)->find($this->recordId);
                break;
            case 'er':
                $this->record = ErAdmission::with(['patient', 'labRequests.items.labTest.labUnit', 'labRequests.items.labTest.referenceRanges', 'pharmacyRequests', 'radiologyRequests.items.radiologist', 'radiologyRequests.items.images', 'procedureRequests'])->where('tenant_id', $tenantId)->find($this->recordId);
                break;
            case 'ward':
                $this->record = Admission::with(['patient', 'labRequests.items.labTest.labUnit', 'labRequests.items.labTest.referenceRanges', 'pharmacyRequests', 'radiologyRequests.items.radiologist', 'radiologyRequests.items.images', 'procedureRequests'])->where('tenant_id', $tenantId)->find($this->recordId);
                break;
            default:
                $this->record = null;
        }

        if ($this->record && !$isAdmin && $doctorId) {
            $authorized = match ($this->type) {
                'opd' => $this->record->attending_physician_id === $doctorId,
                'er', 'ward' => in_array($doctorId, [$this->record->attending_physician_id, $this->record->admitting_physician_id]),
                default => false,
            };
            if (!$authorized) {
                $this->record = null;
            }
        }
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function saveNotes()
    {
        if (!$this->record) return;

        $this->record->update([
            'history_of_present_illness' => $this->history_of_present_illness,
            'physical_exam_findings' => $this->physical_exam_findings,
            'assessment' => $this->assessment,
            'icd_10_code' => $this->icd_10_code,
            'orders_summary' => $this->orders_summary,
            'treatment_notes' => $this->treatment_notes,
            'updated_by' => auth()->user()?->id ?? 1,
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Clinical notes saved successfully.');
    }

    public function updatedSearchIcd()
    {
        if (strlen($this->searchIcd) > 1) {
            $this->icdResults = \App\Models\Icd10Code::where(function ($q) {
                    $q->where('code', 'like', '%' . $this->searchIcd . '%')
                      ->orWhere('description', 'like', '%' . $this->searchIcd . '%');
                })
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->icdResults = [];
        }
    }

    public function selectIcd(string $code)
    {
        $this->icd_10_code = $code;
        $icd = \App\Models\Icd10Code::where('code', $code)->first();
        if ($icd) {
            $this->searchIcd = $icd->code . ' - ' . $icd->description;
        }
        $this->icdResults = [];
    }

    public function openOrderModal($category = 'lab')
    {
        $this->orderCategory = $category;
        $this->showOrderModal = true;
        $this->selectedOrderItems = [];
        $this->searchOrderQuery = '';
        $this->orderSearchResults = [];
        $this->orderPriority = 'Routine';
        $this->orderNotes = '';
    }

    public function closeOrderModal()
    {
        $this->showOrderModal = false;
    }

    public function updatedSearchOrderQuery()
    {
        if (strlen($this->searchOrderQuery) < 2) {
            $this->orderSearchResults = [];
            return;
        }

        $tenantId = request()->attributes->get('tenant_id');

        if ($this->orderCategory === 'lab') {
            $this->orderSearchResults = \App\Models\LabTest::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('test_name', 'like', '%' . $this->searchOrderQuery . '%')
                      ->orWhere('code', 'like', '%' . $this->searchOrderQuery . '%');
                })
                ->limit(10)
                ->get()
                ->toArray();
        } elseif ($this->orderCategory === 'rad') {
            $this->orderSearchResults = \App\Models\Radiology\RadiologyExamType::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('exam_name', 'like', '%' . $this->searchOrderQuery . '%')
                      ->orWhere('code', 'like', '%' . $this->searchOrderQuery . '%');
                })
                ->limit(10)
                ->get()
                ->toArray();
        } elseif ($this->orderCategory === 'pharm') {
            $this->orderSearchResults = \App\Models\Pharmacy\Drug::where('tenant_id', $tenantId)->whereHas('batches', function($q) { $q->where('quantity_on_hand', '>', 0)->where('history', 1); })
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('generic_name', 'like', '%' . $this->searchOrderQuery . '%')
                      ->orWhere('brand_name', 'like', '%' . $this->searchOrderQuery . '%');
                })
                ->limit(10)
                ->get()
                ->toArray();
        }
    }

    public function selectOrderItem($id)
    {
        $tenantId = request()->attributes->get('tenant_id');
        
        if ($this->orderCategory === 'lab') {
            $item = \App\Models\LabTest::where('tenant_id', $tenantId)->find($id);
            if ($item) {
                $this->selectedOrderItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'cost' => $item->standard_charge,
                    'quantity' => 1
                ];
            }
        } elseif ($this->orderCategory === 'rad') {
            $item = \App\Models\Radiology\RadiologyExamType::where('tenant_id', $tenantId)->find($id);
            if ($item) {
                $this->selectedOrderItems[] = [
                    'id' => $item->id,
                    'name' => $item->exam_name,
                    'cost' => $item->standard_charge,
                    'quantity' => 1
                ];
            }
        } elseif ($this->orderCategory === 'pharm') {
            $item = \App\Models\Pharmacy\Drug::where('tenant_id', $tenantId)->whereHas('batches', function($q) { $q->where('quantity_on_hand', '>', 0)->where('history', 1); })->find($id);
            if ($item) {
                $this->selectedOrderItems[] = [
                    'id' => $item->id,
                    'name' => $item->generic_name . ($item->brand_name ? ' ('.$item->brand_name.')' : ''),
                    'cost' => $item->unit_price,
                    'quantity' => 1,
                    'dosage' => '',
                    'frequency' => '',
                    'route' => '',
                    'duration' => ''
                ];
            }
        }

        $this->searchOrderQuery = '';
        $this->orderSearchResults = [];
    }

    public function removeOrderItem($index)
    {
        unset($this->selectedOrderItems[$index]);
        $this->selectedOrderItems = array_values($this->selectedOrderItems);
    }

    public function submitOrder()
    {
        $this->validate(['selectedOrderItems' => 'required|array|min:1']);
        
        $tenantId = request()->attributes->get('tenant_id');
        $userId = auth()->user()?->id ?? 1;
        $doctorId = auth()->user()?->health_care_professional_id;

        $totalAmount = collect($this->selectedOrderItems)->sum(function($item) {
            return $item['cost'] * $item['quantity'];
        });

        \Illuminate\Support\Facades\DB::transaction(function() use ($tenantId, $userId, $doctorId, $totalAmount) {
            $opdId = $this->type === 'opd' ? $this->recordId : null;
            $erId = $this->type === 'er' ? $this->recordId : null;
            $wardId = $this->type === 'ward' ? $this->recordId : null;
            $patientId = $this->patient?->id;
            
            if ($this->orderCategory === 'lab') {
                $req = \App\Models\LabRequest::create([
                    'tenant_id' => $tenantId,
                    'opd_consultation_id' => $opdId,
                    'er_admission_id' => $erId,
                    'admission_id' => $wardId,
                    'patient_id' => $patientId,
                    'requesting_doctor_id' => $doctorId,
                    'priority' => $this->orderPriority,
                    'status' => 'Pending',
                    'notes' => $this->orderNotes,
                    'total_amount' => $totalAmount,
                    'created_by' => $userId
                ]);

                $req->update(['request_code' => 'LR-' . str_pad($req->id, 5, '0', STR_PAD_LEFT)]);

                foreach ($this->selectedOrderItems as $item) {
                    \App\Models\LabRequestItem::create([
                        'lab_request_id' => $req->id,
                        'lab_test_id' => $item['id'],
                        'test_name' => $item['name'],
                        'charge_amount' => $item['cost'],
                        'status' => 'Pending',
                        'created_by' => $userId
                    ]);
                }
            } elseif ($this->orderCategory === 'rad') {
                $req = \App\Models\Radiology\RadiologyRequest::create([
                    'tenant_id' => $tenantId,
                    'opd_consultation_id' => $opdId,
                    'er_admission_id' => $erId,
                    'admission_id' => $wardId,
                    'patient_id' => $patientId,
                    'requesting_doctor_id' => $doctorId,
                    'priority' => $this->orderPriority,
                    'status' => 'Pending',
                    'notes' => $this->orderNotes,
                    'total_amount' => $totalAmount,
                    'created_by' => $userId
                ]);

                $req->update(['request_code' => 'RR-' . str_pad($req->id, 5, '0', STR_PAD_LEFT)]);

                foreach ($this->selectedOrderItems as $item) {
                    \App\Models\Radiology\RadiologyRequestItem::create([
                        'radiology_request_id' => $req->id,
                        'exam_type_id' => $item['id'],
                        'exam_name' => $item['name'],
                        'charge_amount' => $item['cost'],
                        'status' => 'Pending',
                        'created_by' => $userId
                    ]);
                }
            } elseif ($this->orderCategory === 'pharm') {
                $req = \App\Models\Pharmacy\PharmacyRequest::create([
                    'tenant_id' => $tenantId,
                    'opd_consultation_id' => $opdId,
                    'er_admission_id' => $erId,
                    'admission_id' => $wardId,
                    'requesting_doctor_id' => $doctorId,
                    'priority' => $this->orderPriority,
                    'status' => 'Pending',
                    'notes' => $this->orderNotes,
                    'total_amount' => $totalAmount,
                    'created_by' => $userId
                ]);

                $req->update(['request_code' => 'PR-' . str_pad($req->id, 5, '0', STR_PAD_LEFT)]);

                foreach ($this->selectedOrderItems as $item) {
                    \App\Models\Pharmacy\PharmacyRequestItem::create([
                        'pharmacy_request_id' => $req->id,
                        'drug_id' => $item['id'],
                        'item_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['cost'],
                        'total_cost' => $item['cost'] * $item['quantity'],
                        'dosage' => $item['dosage'] ?? '',
                        'frequency' => $item['frequency'] ?? '',
                        'route' => $item['route'] ?? '',
                        'duration' => $item['duration'] ?? '',
                        'status' => 'Pending',
                        'created_by' => $userId
                    ]);
                }
            }
        });

        $this->closeOrderModal();
        $this->loadRecord();
        $this->dispatch('toast', type: 'success', message: 'Order submitted successfully.');
    }

    public function getMedicalHistoryProperty()
    {
        if (!$this->patient) return collect([]);

        $history = collect();

        foreach ($this->patient->opdConsultations()->with('attendingPhysician')->get() as $opd) {
            if ($this->type === 'opd' && $this->recordId === $opd->id) continue;
            $history->push([
                'id' => $opd->id,
                'type_key' => 'opd',
                'type' => 'OPD Consultation',
                'date' => \Carbon\Carbon::parse($opd->consultation_datetime)->format('M d, Y h:i A'),
                'physician' => $opd->attendingPhysician?->full_name ?? 'Unknown',
                'diagnosis' => $opd->icd_10_code ?? $opd->assessment,
                'notes' => $opd->history_of_present_illness,
                'disposition' => $opd->disposition,
                'code' => $opd->consultation_code ?? 'OPD#' . $opd->id,
                'color' => 'emerald',
                'timestamp' => \Carbon\Carbon::parse($opd->consultation_datetime)->timestamp,
            ]);
        }

        foreach ($this->patient->erAdmissions()->with('attendingPhysician')->get() as $er) {
            if ($this->type === 'er' && $this->recordId === $er->id) continue;
            $history->push([
                'id' => $er->id,
                'type_key' => 'er',
                'type' => 'ER Treatment',
                'date' => \Carbon\Carbon::parse($er->admission_datetime)->format('M d, Y h:i A'),
                'physician' => $er->attendingPhysician?->full_name ?? 'Unknown',
                'diagnosis' => $er->icd_10_code ?? $er->diagnosis ?? $er->assessment,
                'notes' => $er->history_of_present_illness,
                'disposition' => $er->disposition,
                'code' => $er->admission_code ?? 'ER#' . $er->id,
                'color' => 'rose',
                'timestamp' => \Carbon\Carbon::parse($er->admission_datetime)->timestamp,
            ]);
        }

        foreach ($this->patient->admissions()->with('attendingPhysician', 'wardRoom', 'icuBed')->get() as $adm) {
            if ($this->type === 'ward' && $this->recordId === $adm->id) continue;
            $history->push([
                'id' => $adm->id,
                'type_key' => 'ward',
                'type' => 'Ward Admission',
                'date' => \Carbon\Carbon::parse($adm->admission_date)->format('M d, Y h:i A'),
                'physician' => $adm->attendingPhysician?->full_name ?? 'Unknown',
                'diagnosis' => $adm->icd_10_code ?? $adm->admitting_diagnosis ?? $adm->assessment,
                'notes' => $adm->history_of_present_illness,
                'disposition' => $adm->disposition,
                'code' => $adm->admission_code ?? 'ADM#' . $adm->id,
                'color' => 'indigo',
                'timestamp' => \Carbon\Carbon::parse($adm->admission_date)->timestamp,
            ]);
        }

        foreach ($this->patient->obConsultations()->with('attendingPhysician')->get() as $ob) {
            $history->push([
                'id' => $ob->id,
                'type_key' => 'ob',
                'type' => 'OB/GYN Consultation',
                'date' => \Carbon\Carbon::parse($ob->created_date)->format('M d, Y h:i A'),
                'physician' => $ob->attendingPhysician?->full_name ?? 'Unknown',
                'diagnosis' => $ob->icd_10_code ?? $ob->assessment,
                'notes' => $ob->history_of_present_illness,
                'disposition' => $ob->disposition,
                'code' => $ob->consultation_code ?? 'OB#' . $ob->id,
                'color' => 'purple',
                'timestamp' => \Carbon\Carbon::parse($ob->created_date)->timestamp,
            ]);
        }

        return $history->sortByDesc('timestamp')->values();
    }

    public function viewPastEncounter(string $typeKey, int $id): void
    {
        $this->pastEncounterDetail = null;
        $this->viewingPastEncounter = true;

        $detail = match ($typeKey) {
            'opd' => $this->loadPastOpd($id),
            'er' => $this->loadPastEr($id),
            'ward' => $this->loadPastWard($id),
            'ob' => $this->loadPastOb($id),
            default => null,
        };

        $this->pastEncounterDetail = $detail;
    }

    public function closePastEncounter(): void
    {
        $this->viewingPastEncounter = false;
        $this->pastEncounterDetail = null;
    }

    private function loadPastOpd(int $id): ?array
    {
        $e = \App\Models\Opd\OpdConsultation::withoutGlobalScopes()
            ->with(['patient', 'attendingPhysician', 'opdClinic',
                'labRequests.items.labTest.labUnit', 'labRequests.items.labTest.referenceRanges',
                'pharmacyRequests.items.drug', 'radiologyRequests.items.radiologist',
                'procedureRequests'])
            ->find($id);
        if (!$e) return null;

        $labs = $e->labRequests->flatMap(fn($r) => $r->items->map(fn($i) => [
            'code' => $r->request_code,
            'test' => $i->test_name,
            'result' => $i->result_value ?? $i->result_categorical ?? ($i->result_text ? \Str::limit($i->result_text, 30) : '—'),
            'flag' => $i->flag,
            'range' => $i->reference_range ?? '—',
            'status' => $i->status,
        ]));

        $pharmacy = $e->pharmacyRequests->map(fn($r) => [
            'code' => $r->request_code,
            'items' => $r->items->pluck('drug.drug_name')->implode(', '),
            'status' => $r->status,
        ]);

        $radiology = $e->radiologyRequests->map(fn($r) => [
            'code' => $r->request_code,
            'items' => $r->items->pluck('exam_name')->implode(', '),
            'status' => $r->status,
        ]);

        return [
            'type' => 'OPD Consultation',
            'code' => $e->consultation_code ?? 'OPD#' . $e->id,
            'date' => $e->consultation_datetime?->format('M d, Y h:i A') ?? '—',
            'physician' => $e->attendingPhysician?->full_name ?? '—',
            'fields' => [
                'Clinic' => $e->opdClinic?->name ?? '—',
                'Chief Complaint' => $e->chief_complaint ?? '—',
                'HPI' => $e->history_of_present_illness ?? '—',
                'Physical Exam' => $e->physical_exam_findings ?? '—',
                'Assessment' => $e->assessment ?? '—',
                'ICD-10 Code' => $e->icd_10_code ?? '—',
                'Treatment Plan' => $e->treatment_notes ?? '—',
                'Disposition' => $e->disposition ?? '—',
            ],
            'labs' => $labs,
            'pharmacy' => $pharmacy,
            'radiology' => $radiology,
        ];
    }

    private function loadPastEr(int $id): ?array
    {
        $e = \App\Models\Er\ErAdmission::withoutGlobalScopes()
            ->with(['patient', 'attendingPhysician', 'erBed',
                'labRequests.items.labTest.labUnit', 'labRequests.items.labTest.referenceRanges',
                'pharmacyRequests.items.drug', 'radiologyRequests.items.radiologist',
                'procedureRequests'])
            ->find($id);
        if (!$e) return null;

        $labs = $e->labRequests->flatMap(fn($r) => $r->items->map(fn($i) => [
            'code' => $r->request_code,
            'test' => $i->test_name,
            'result' => $i->result_value ?? $i->result_categorical ?? ($i->result_text ? \Str::limit($i->result_text, 30) : '—'),
            'flag' => $i->flag,
            'range' => $i->reference_range ?? '—',
            'status' => $i->status,
        ]));

        $pharmacy = $e->pharmacyRequests->map(fn($r) => [
            'code' => $r->request_code,
            'items' => $r->items->pluck('drug.drug_name')->implode(', '),
            'status' => $r->status,
        ]);

        $radiology = $e->radiologyRequests->map(fn($r) => [
            'code' => $r->request_code,
            'items' => $r->items->pluck('exam_name')->implode(', '),
            'status' => $r->status,
        ]);

        $colorMap = ['rose' => 'text-rose-600', 'red' => 'text-red-600'];

        return [
            'type' => 'ER Treatment',
            'code' => $e->admission_code ?? 'ER#' . $e->id,
            'date' => $e->admission_datetime?->format('M d, Y h:i A') ?? '—',
            'physician' => $e->attendingPhysician?->full_name ?? '—',
            'fields' => [
                'Chief Complaint' => $e->chief_complaint ?? '—',
                'Diagnosis' => $e->diagnosis ?? '—',
                'Mode of Arrival' => $e->mode_of_arrival ?? '—',
                'ER Bed' => $e->erBed?->name ?? '—',
                'Disposition' => $e->disposition ?? '—',
                'Discharge Date' => $e->disposition_datetime?->format('M d, Y h:i A') ?? '—',
                'Clinical Notes' => $e->clinical_notes ?? '—',
                'Status' => $e->status ?? '—',
            ],
            'labs' => $labs,
            'pharmacy' => $pharmacy,
            'radiology' => $radiology,
        ];
    }

    private function loadPastWard(int $id): ?array
    {
        $e = \App\Models\Admission::withoutGlobalScopes()
            ->with(['patient', 'attendingPhysician', 'wardRoom.ward', 'icuBed.ward',
                'labRequests.items.labTest.labUnit', 'labRequests.items.labTest.referenceRanges',
                'pharmacyRequests.items.drug', 'radiologyRequests.items.radiologist',
                'procedureRequests'])
            ->find($id);
        if (!$e) return null;

        $labs = $e->labRequests->flatMap(fn($r) => $r->items->map(fn($i) => [
            'code' => $r->request_code,
            'test' => $i->test_name,
            'result' => $i->result_value ?? $i->result_categorical ?? ($i->result_text ? \Str::limit($i->result_text, 30) : '—'),
            'flag' => $i->flag,
            'range' => $i->reference_range ?? '—',
            'status' => $i->status,
        ]));

        $pharmacy = $e->pharmacyRequests->map(fn($r) => [
            'code' => $r->request_code,
            'items' => $r->items->pluck('drug.drug_name')->implode(', '),
            'status' => $r->status,
        ]);

        $radiology = $e->radiologyRequests->map(fn($r) => [
            'code' => $r->request_code,
            'items' => $r->items->pluck('exam_name')->implode(', '),
            'status' => $r->status,
        ]);

        $days = $e->admission_date && $e->discharge_date
            ? max(1, (int) ceil($e->admission_date->diffInDays($e->discharge_date))) . ' day(s)'
            : '—';

        return [
            'type' => 'Ward Admission',
            'code' => $e->admission_code ?? 'ADM#' . $e->id,
            'date' => $e->admission_date?->format('M d, Y h:i A') ?? '—',
            'physician' => $e->attendingPhysician?->full_name ?? '—',
            'fields' => [
                'Admitting Diagnosis' => $e->admitting_diagnosis ?? '—',
                'Room' => $e->wardRoom?->name ?? $e->icuBed?->name ?? '—',
                'Ward/ICU' => $e->wardRoom?->ward?->name ?? $e->icuBed?->ward?->name ?? '—',
                'Admission Date' => $e->admission_date?->format('M d, Y') ?? '—',
                'Discharge Date' => $e->discharge_date?->format('M d, Y') ?? '—',
                'Length of Stay' => $days,
                'Discharge Notes' => $e->discharge_notes ?? '—',
                'Status' => $e->status ?? '—',
            ],
            'labs' => $labs,
            'pharmacy' => $pharmacy,
            'radiology' => $radiology,
        ];
    }

    private function loadPastOb(int $id): ?array
    {
        $e = \App\Models\Ob\ObConsultation::withoutGlobalScopes()
            ->with(['patient', 'attendingPhysician', 'visitType'])->find($id);
        if (!$e) return null;

        return [
            'type' => 'OB/GYN Consultation',
            'code' => $e->consultation_code ?? 'OB#' . $e->id,
            'date' => $e->consultation_date?->format('M d, Y h:i A') ?? ($e->created_date?->format('M d, Y h:i A') ?? '—'),
            'physician' => $e->attendingPhysician?->full_name ?? '—',
            'fields' => [
                'Visit Type' => $e->visitType?->name ?? '—',
                'Chief Complaint' => $e->chief_complaint ?? '—',
                'AOG' => $e->aog ?? '—',
                'LMP' => $e->lmp ? \Carbon\Carbon::parse($e->lmp)->format('M d, Y') : '—',
                'EDC' => $e->edc ? \Carbon\Carbon::parse($e->edc)->format('M d, Y') : '—',
                'Gravida/Para' => ($e->gravida ?? '—') . '/' . ($e->para ?? '—'),
                'Assessment' => $e->assessment ?? '—',
                'Treatment Plan' => $e->treatment_notes ?? '—',
                'Disposition' => $e->disposition ?? '—',
            ],
            'labs' => collect([]),
            'pharmacy' => collect([]),
            'radiology' => collect([]),
        ];
    }

    public function render()
    {
        return view('livewire.doctors.patient-chart-crud')->layout('layouts.app');
    }
}
