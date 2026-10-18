<?php

namespace App\Livewire\Billing;

use App\Models\Admission;
use App\Models\AdmissionRoomHistory;
use App\Models\Billing\BillingInvoice;
use App\Models\Billing\BillingInvoiceItem;
use App\Models\Billing\BillingManualCharge;
use App\Models\Billing\BillingPayment;
use App\Models\Billing\FundSource;
use App\Models\Billing\HealthInsuranceProvider;
use App\Models\Billing\PatientAccountEntry;
use App\Models\CsrRequest;
use App\Models\Er\ErAdmission;
use App\Models\LabRequest;
use App\Models\Patient;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Ob\ObConsultation;
use App\Models\Opd\OpdConsultation;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Radiology\RadiologyRequest;
use Livewire\Component;
use Livewire\WithPagination;

class BillingCrud extends Component
{
    use WithPagination;

    public int $activeTab = 1;
    public string $search = '', $sortField = 'created_date', $sortDirection = 'desc';
    public int $perPage = 10;

    // Invoice / Statement Modal
    public bool $showInvoiceModal = false, $showInvoiceDetailModal = false, $showPaymentModal = false;
    public ?int $editingInvoiceId = null, $viewingInvoiceId = null;
    public string $invoice_code = '', $invoice_date = '';
    public ?string $patient_search = null, $patient_id = null, $admission_id = null, $er_admission_id = null, $opd_consultation_id = null, $dr_schedule_id = null, $ob_consultation_id = null;
    public string $invoice_notes = '';
    public string $discount_type = '';
    public string $discount_value = '0';
    public float $discount_amount = 0;
    public array $selectedServiceItems = [];
    public bool $skipPatientSearchUpdate = false;

    // Payment Modal
    public ?int $paymentPatientId = null;
    public ?int $paymentInvoiceId = null;
    public string $payment_code = '', $payment_date = '', $payment_amount = '0', $payment_method = 'Cash', $payment_reference = '', $payment_notes = '';
    public $fund_sources = [];
    public $insurance_providers = [];
    public ?string $payment_fund_source_id = null;
    public ?string $payment_insurance_provider_id = null;

    // Detail Modal Discount Edit
    public string $detail_discount_type = '';
    public string $detail_discount_value = '0';
    public float $detail_discount_amount = 0;

    // Manual Charge (inside detail modal — post-statement)
    public bool $showManualChargeForm = false;
    public string $manualChargeType = 'Miscellaneous', $manualChargeDesc = '', $manualChargeQty = '1', $manualChargePrice = '0', $manualChargeNotes = '';

    // Pre-statement Manual Charge (per patient on Patient Accounts tab)
    public bool $showPatientManualChargeForm = false;
    public ?int $manualChargePatientId = null;

    // Patient Ledger Modal
    public bool $showLedgerModal = false;
    public ?int $viewingPatientId = null;
    public string $viewingPatientName = '';
    public array $viewingLedger = [];
    public float $viewingPatientBalance = 0;
    public string $viewingPatientCode = '';
    public string $viewingPatientDob = '';
    public string $viewingPatientGender = '';
    public string $viewingPatientContact = '';
    public string $viewingPatientAddress = '';
    public string $viewingPatientAdmissionDate = '';
    public string $viewingPatientEncounterType = '';
    public string $viewingPatientEncounterCode = '';
    public string $viewingPatientRoom = '';
    public string $viewingPatientPhysician = '';
    public ?int $viewingPatientDaysStayed = null;
    public ?string $viewingPatientRoomRate = null;
    public ?float $viewingPatientAccruedRoomCharge = null;
    public bool $viewingPatientIsAdmitted = false;
    public array $selectedLedgerEntries = [];
    public string $ledgerConfirmAction = '';
    public ?int $discount_patient_id = null;
    public bool $showDiscountModal = false;
    public string $ledger_discount_type = 'percentage';
    public string $ledger_discount_value = '';
    public float $ledger_discount_computed = 0;
    public string $ledger_discount_notes = '';

    // Patient Charges Modal (legacy — for unbilled requests view)
    public bool $showChargesModal = false;
    public array $viewingPatientCharges = [];
    public ?int $chargesModalPatientId = null;

    // Statement of Account PDF Modal
    public bool $showStatementModal = false;
    public string $statementMode = 'detailed';
    public string $statementUrl = '';

    private function currentTenantId(): int { return request()->attributes->get('tenant_id') ?? 1; }
    private function currentUserId(): int { return request()->attributes->get('jwt_user')?->id ?? 1; }

    public function mount(): void
    {
        $this->invoice_date = now()->format('Y-m-d');
        $this->payment_date = now()->format('Y-m-d');
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function updatedDiscountType(): void { $this->computeDiscount(); }
    public function updatedDiscountValue(): void { $this->computeDiscount(); }

    private function computeDiscount(): void
    {
        $serviceTotal = collect($this->unbilled_requests)
            ->whereIn('id', $this->selectedServiceItems)
            ->sum('total_amount');

        $manualTotal = 0;
        if ($this->patient_id) {
            $stagingCharges = $this->stagingManualCharges()->get((int) $this->patient_id, collect());
            $manualTotal = (float) $stagingCharges->sum('total_price');
        }
        $total = $serviceTotal + $manualTotal;

        if ($this->discount_type === 'percentage' && (float) $this->discount_value > 0) {
            $this->discount_amount = round($total * (float) $this->discount_value / 100, 2);
        } elseif ($this->discount_type === 'fixed' && (float) $this->discount_value > 0) {
            $this->discount_amount = min((float) $this->discount_value, $total);
        } else {
            $this->discount_amount = 0;
        }
    }

    public function updatedDetailDiscountType(): void { $this->computeDetailDiscount(); }
    public function updatedDetailDiscountValue(): void { $this->computeDetailDiscount(); }

    private function computeDetailDiscount(): void
    {
        $invoice = $this->viewingInvoiceId ? BillingInvoice::find($this->viewingInvoiceId) : null;
        $total = $invoice?->total_amount ?? 0;

        if ($this->detail_discount_type === 'percentage' && (float) $this->detail_discount_value > 0) {
            $this->detail_discount_amount = round($total * (float) $this->detail_discount_value / 100, 2);
        } elseif ($this->detail_discount_type === 'fixed' && (float) $this->detail_discount_value > 0) {
            $this->detail_discount_amount = min((float) $this->detail_discount_value, $total);
        } else {
            $this->detail_discount_amount = 0;
        }
    }

    public function saveInvoiceDiscount(): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $invoice = BillingInvoice::findOrFail($this->viewingInvoiceId);

        $this->computeDetailDiscount();

        $newBalance = $invoice->total_amount - $this->detail_discount_amount - $invoice->paid_amount;
        $newStatus = $newBalance <= 0 ? 'Paid' : ($invoice->paid_amount > 0 ? 'Partial' : 'Unpaid');
        if ($newBalance < 0) $newBalance = 0;

        $invoice->update([
            'discount_type' => $this->detail_discount_type ?: null,
            'discount_value' => $this->detail_discount_value,
            'discount_amount' => $this->detail_discount_amount,
            'balance' => $newBalance,
            'status' => $newStatus,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Discount saved.');
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function setTab(int $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function generateInvoiceCode(): string
    {
        $tid = $this->currentTenantId();
        $last = BillingInvoice::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->invoice_code, 4)) + 1 : 1;
        return 'INV-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function generatePaymentCode(): string
    {
        $tid = $this->currentTenantId();
        $last = BillingPayment::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
        $num = $last ? ((int) substr($last->payment_code, 4)) + 1 : 1;
        return 'PAY-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ── Unbilled Requests (Legacy — kept for invoice creation backward compat) ──

    private function stagingManualCharges(): \Illuminate\Database\Eloquent\Collection
    {
        return BillingManualCharge::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->whereNull('billing_invoice_id')
            ->where('is_active', true)
            ->where('history', 1)
            ->get()
            ->groupBy('patient_id');
    }

    public array $unbilledRequests = [];

    public function getUnbilledRequestsProperty(): array
    {
        $tid = $this->currentTenantId();
        $results = [];

        $labReqs = LabRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('history', 1)
            ->where('status', 'Completed')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['patient', 'requestingDepartment', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'obConsultation.patient', 'drSchedule.patient'])
            ->get()
            ->map(fn ($r) => [
                'id' => 'lab_' . $r->id,
                'source_type' => 'Lab',
                'source_id' => $r->id,
                'request_code' => $r->request_code,
                'patient_name' => $r->patient_name,
                'patient_id' => $r->patient_id ?? $r->erAdmission?->patient_id ?? $r->opdConsultation?->patient_id ?? $r->admission?->patient_id ?? $r->dialysisSession?->patient_id ?? $r->orSchedule?->patient_id ?? $r->obConsultation?->patient_id ?? $r->drSchedule?->patient_id,
                'department' => $r->requestingDepartment?->dept_name ?? '—',
                'total_amount' => (float) $r->total_charge,
                'date' => $r->created_date?->format('Y-m-d') ?? '—',
                'er_admission_id' => $r->er_admission_id,
                'opd_consultation_id' => $r->opd_consultation_id,
                'admission_id' => $r->admission_id,
                'dialysis_session_id' => $r->dialysis_session_id,
                'or_schedule_id' => $r->or_schedule_id,
                'ob_consultation_id' => $r->ob_consultation_id,
                'dr_schedule_id' => $r->dr_schedule_id,
            ]);

        $pharmReqs = PharmacyRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('history', 1)
            ->where('status', 'Complete')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->whereHas('items', fn ($q) => $q->where('history', 1))
            ->with(['erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'obConsultation.patient', 'drSchedule.patient'])
            ->get()
            ->map(fn ($r) => [
                'id' => 'pharm_' . $r->id,
                'source_type' => 'Pharmacy',
                'source_id' => $r->id,
                'request_code' => $r->request_code,
                'patient_name' => $r->patient_name,
                'patient_id' => $r->erAdmission?->patient_id ?? $r->opdConsultation?->patient_id ?? $r->admission?->patient_id ?? $r->dialysisSession?->patient_id ?? $r->orSchedule?->patient_id ?? $r->obConsultation?->patient_id ?? $r->drSchedule?->patient_id,
                'department' => $r->requestingDepartment?->dept_name ?? '—',
                'total_amount' => (float) $r->total_amount,
                'date' => $r->created_date?->format('Y-m-d') ?? '—',
                'er_admission_id' => $r->er_admission_id,
                'opd_consultation_id' => $r->opd_consultation_id,
                'admission_id' => $r->admission_id,
                'dialysis_session_id' => $r->dialysis_session_id,
                'or_schedule_id' => $r->or_schedule_id,
                'ob_consultation_id' => $r->ob_consultation_id,
                'dr_schedule_id' => $r->dr_schedule_id,
            ]);

        $csrReqs = CsrRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('history', 1)
            ->where('status', 'Complete')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->whereHas('items', fn ($q) => $q->where('history', 1))
            ->with(['patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'obConsultation.patient', 'drSchedule.patient'])
            ->get()
            ->map(fn ($r) => [
                'id' => 'csr_' . $r->id,
                'source_type' => 'CSR',
                'source_id' => $r->id,
                'request_code' => $r->request_code,
                'patient_name' => $r->patient_name,
                'patient_id' => $r->patient_id ?? $r->erAdmission?->patient_id ?? $r->opdConsultation?->patient_id ?? $r->admission?->patient_id ?? $r->dialysisSession?->patient_id ?? $r->orSchedule?->patient_id ?? $r->obConsultation?->patient_id ?? $r->drSchedule?->patient_id,
                'department' => $r->requestingDepartment?->dept_name ?? '—',
                'total_amount' => (float) $r->total_amount,
                'date' => $r->created_date?->format('Y-m-d') ?? '—',
                'er_admission_id' => $r->er_admission_id,
                'opd_consultation_id' => $r->opd_consultation_id,
                'admission_id' => $r->admission_id,
                'dialysis_session_id' => $r->dialysis_session_id,
                'or_schedule_id' => $r->or_schedule_id,
                'ob_consultation_id' => $r->ob_consultation_id,
                'dr_schedule_id' => $r->dr_schedule_id,
            ]);

        $radReqs = RadiologyRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('history', 1)
            ->where('status', 'Complete')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->whereHas('items', fn ($q) => $q->where('history', 1))
            ->with(['patient', 'erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'obConsultation.patient', 'drSchedule.patient'])
            ->get()
            ->map(fn ($r) => [
                'id' => 'rad_' . $r->id,
                'source_type' => 'Radiology',
                'source_id' => $r->id,
                'request_code' => $r->request_code,
                'patient_name' => $r->patient_name,
                'patient_id' => $r->patient_id ?? $r->erAdmission?->patient_id ?? $r->opdConsultation?->patient_id ?? $r->admission?->patient_id ?? $r->dialysisSession?->patient_id ?? $r->orSchedule?->patient_id ?? $r->obConsultation?->patient_id ?? $r->drSchedule?->patient_id,
                'department' => $r->requestingDepartment?->dept_name ?? '—',
                'total_amount' => (float) $r->total_amount,
                'date' => $r->created_date?->format('Y-m-d') ?? '—',
                'er_admission_id' => $r->er_admission_id,
                'opd_consultation_id' => $r->opd_consultation_id,
                'admission_id' => $r->admission_id,
                'dialysis_session_id' => $r->dialysis_session_id,
                'or_schedule_id' => $r->or_schedule_id,
                'ob_consultation_id' => $r->ob_consultation_id,
                'dr_schedule_id' => $r->dr_schedule_id,
            ]);

        $oecbReqs = ErAdmission::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('history', 1)
            ->where('is_oecb', true)
            ->where('status', 'Completed')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['patient', 'oecbEntries'])
            ->get()
            ->flatMap(fn ($r) => $r->oecbEntries->map(fn ($e) => [
                'id' => 'oecb_' . $e->id,
                'source_type' => 'OECB',
                'source_id' => $e->id,
                'request_code' => $r->admission_code,
                'patient_name' => $r->patient?->last_name . ', ' . $r->patient?->first_name,
                'patient_id' => $r->patient_id,
                'department' => 'ER',
                'total_amount' => (float) ($e->oecb_fixed_fee ?? 0),
                'date' => $r->disposition_datetime?->format('Y-m-d') ?? $r->created_date?->format('Y-m-d') ?? '—',
                'er_admission_id' => $r->id,
                'opd_consultation_id' => null,
                'admission_id' => null,
            ]));

        $admReqs = collect();

        $pendingAdmissions = Admission::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('history', 1)
            ->whereIn('status', ['Admitted', 'Discharged'])
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['patient', 'wardRoom', 'icuBed'])
            ->get();

        foreach ($pendingAdmissions as $r) {
            $histories = AdmissionRoomHistory::where('admission_id', $r->id)
                ->where('history', 1)
                ->orderBy('assigned_at')
                ->get();

            if ($histories->isEmpty()) {
                $days = $r->days_stayed;
                $rate = (float) ($r->wardRoom?->room_rate ?? $r->icuBed?->room_rate ?? 0);
                $roomName = $r->wardRoom?->name ?? $r->icuBed?->name ?? 'Room & Board';
                $total = (float) ($days * $rate);
                $admReqs->push([
                    'id' => 'adm_' . $r->id,
                    'source_type' => 'Admission',
                    'source_id' => $r->id,
                    'request_code' => $r->admission_code,
                    'patient_name' => $r->patient?->last_name . ', ' . $r->patient?->first_name,
                    'patient_id' => $r->patient_id,
                    'department' => 'Ward/ICU',
                    'total_amount' => $total,
                    'date' => ($r->discharge_date ?? $r->created_date)?->format('Y-m-d') ?? '—',
                    'er_admission_id' => $r->er_admission_id,
                    'opd_consultation_id' => null,
                    'admission_id' => $r->id,
                    'room_name' => $roomName,
                    'days' => $days,
                    'rate' => $rate,
                ]);
            } else {
                foreach ($histories as $h) {
                    $days = $h->days_stayed;
                    $rate = (float) $h->room_rate;
                    $total = (float) $h->total_charge;
                    $admReqs->push([
                        'id' => 'adm_' . $r->id . '_hist_' . $h->id,
                        'source_type' => 'Admission',
                        'source_id' => $r->id,
                        'request_code' => $r->admission_code,
                        'patient_name' => $r->patient?->last_name . ', ' . $r->patient?->first_name,
                        'patient_id' => $r->patient_id,
                        'department' => 'Ward/ICU',
                        'total_amount' => $total,
                        'date' => $h->assigned_at?->format('Y-m-d') ?? ($r->admission_date?->format('Y-m-d') ?? '—'),
                        'er_admission_id' => $r->er_admission_id,
                        'opd_consultation_id' => null,
                        'admission_id' => $r->id,
                        'room_name' => ($h->room_name ?? 'Unknown') . ($h->ward_name ? ' (' . $h->ward_name . ')' : ''),
                        'days' => $days,
                        'rate' => $rate,
                        'room_history_id' => $h->id,
                    ]);
                }
            }
        }

        $procReqs = ProcedureRequest::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('history', 1)
            ->where('status', 'Complete')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->whereHas('items', fn ($q) => $q->where('history', 1))
            ->with(['erAdmission.patient', 'opdConsultation.patient', 'admission.patient', 'dialysisSession.patient', 'orSchedule.patient', 'obConsultation.patient', 'drSchedule.patient'])
            ->get()
            ->map(fn ($r) => [
                'id' => 'proc_' . $r->id,
                'source_type' => 'Procedure',
                'source_id' => $r->id,
                'request_code' => $r->request_code,
                'patient_name' => $r->patient_name,
                'patient_id' => $r->patient_id ?? $r->erAdmission?->patient_id ?? $r->opdConsultation?->patient_id ?? $r->admission?->patient_id ?? $r->dialysisSession?->patient_id ?? $r->orSchedule?->patient_id ?? $r->obConsultation?->patient_id ?? $r->drSchedule?->patient_id,
                'department' => $r->requestingDepartment?->dept_name ?? '—',
                'total_amount' => (float) $r->total_amount,
                'date' => $r->created_date?->format('Y-m-d') ?? '—',
                'er_admission_id' => $r->er_admission_id,
                'opd_consultation_id' => $r->opd_consultation_id,
                'admission_id' => $r->admission_id,
                'dialysis_session_id' => $r->dialysis_session_id,
                'or_schedule_id' => $r->or_schedule_id,
                'ob_consultation_id' => $r->ob_consultation_id,
                'dr_schedule_id' => $r->dr_schedule_id,
            ]);

        $results = array_merge(
            $labReqs->toArray(),
            $pharmReqs->toArray(),
            $csrReqs->toArray(),
            $radReqs->toArray(),
            $procReqs->toArray(),
            $oecbReqs->toArray(),
            $admReqs->toArray()
        );

        usort($results, fn ($a, $b) => strcmp($b['date'], $a['date']));

        if ($this->search) {
            $results = array_filter($results, fn ($r) =>
                stripos($r['request_code'], $this->search) !== false
                || stripos($r['source_type'], $this->search) !== false
                || stripos($r['department'], $this->search) !== false
                || stripos($r['patient_name'], $this->search) !== false
            );
        }

        return $results;
    }

    public function getUnbilledPatientsProperty(): array
    {
        $grouped = [];
        foreach ($this->unbilled_requests as $req) {
            $pid = $req['patient_id'] ?? 0;
            if (!$pid) continue;
            $name = $req['patient_name'] ?? 'Unknown';
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = [
                    'patient_id' => $pid,
                    'patient_name' => $name,
                    'total_amount' => 0,
                    'item_count' => 0,
                    'items' => [],
                    'manual_charges' => [],
                    'manual_total' => 0,
                ];
            }
            $grouped[$pid]['total_amount'] += $req['total_amount'];
            $grouped[$pid]['item_count']++;
            $grouped[$pid]['items'][] = $req;
        }

        $stagingCharges = $this->stagingManualCharges();
        foreach ($stagingCharges as $pid => $charges) {
            if (empty($charges)) continue;
            if (!isset($grouped[$pid])) {
                $patient = Patient::where('id', $pid)->where('tenant_id', $this->currentTenantId())->first();
                if (!$patient) continue;
                $grouped[$pid] = [
                    'patient_id' => $pid,
                    'patient_name' => $patient->last_name . ', ' . $patient->first_name,
                    'total_amount' => 0,
                    'item_count' => 0,
                    'items' => [],
                    'manual_charges' => [],
                    'manual_total' => 0,
                ];
            }
            $grouped[$pid]['manual_charges'] = $charges->values();
            $grouped[$pid]['manual_total'] = (float) $charges->sum('total_price');
            $grouped[$pid]['total_amount'] += $grouped[$pid]['manual_total'];
            $grouped[$pid]['item_count'] += $charges->count();
        }

        usort($grouped, fn ($a, $b) => strcmp($a['patient_name'], $b['patient_name']));
        return array_values($grouped);
    }

    // ── Patient Accounts (Ledger) ─────────────────────────────────────────

    public function getPatientAccountsProperty(): array
    {
        $tid = $this->currentTenantId();

        // Get all patients with ledger entries
        $entries = PatientAccountEntry::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('is_active', true)
            ->where('history', 1)
            ->get();

        // Group by patient
        $grouped = [];
        foreach ($entries as $entry) {
            $pid = $entry->patient_id;
            if (!isset($grouped[$pid])) {
                $patient = $entry->patient;
                $grouped[$pid] = [
                    'patient_id' => $pid,
                    'patient_name' => $patient ? $patient->last_name . ', ' . $patient->first_name : 'Unknown',
                    'patient_code' => $patient->patient_code ?? '',
                    'total_charges' => 0,
                    'total_payments' => 0,
                    'balance' => 0,
                    'entry_count' => 0,
                    'last_entry_date' => null,
                ];
            }
            $grouped[$pid]['total_charges'] += (float) $entry->debit;
            $grouped[$pid]['total_payments'] += (float) $entry->credit;
            $grouped[$pid]['entry_count']++;
            if (!$grouped[$pid]['last_entry_date'] || ($entry->created_date && $entry->created_date->gt($grouped[$pid]['last_entry_date']))) {
                $grouped[$pid]['last_entry_date'] = $entry->created_date;
            }
        }

        // Calculate balance from ledger
        foreach ($grouped as &$row) {
            $row['balance'] = $row['total_charges'] - $row['total_payments'];
            $row['balance'] = max(0, $row['balance']);
        }
        unset($row);

        // Also include patients with unbilled requests (not yet in ledger)
        foreach ($this->unbilled_requests as $req) {
            $pid = $req['patient_id'] ?? 0;
            if (!$pid || isset($grouped[$pid])) continue;
            $patient = Patient::where('id', $pid)->where('tenant_id', $tid)->first();
            if (!$patient) continue;
            $grouped[$pid] = [
                'patient_id' => $pid,
                'patient_name' => $patient->last_name . ', ' . $patient->first_name,
                'patient_code' => $patient->patient_code ?? '',
                'total_charges' => 0,
                'total_payments' => 0,
                'balance' => 0,
                'entry_count' => 0,
                'last_entry_date' => null,
                'has_unbilled' => true,
                'unbilled_amount' => 0,
            ];
        }

        // Add unbilled amounts to patients who have pending requests
        foreach ($this->unbilled_requests as $req) {
            $pid = $req['patient_id'] ?? 0;
            if (!$pid || !isset($grouped[$pid])) continue;
            if (!isset($grouped[$pid]['unbilled_amount'])) $grouped[$pid]['unbilled_amount'] = 0;
            $grouped[$pid]['unbilled_amount'] += $req['total_amount'];
            $grouped[$pid]['has_unbilled'] = true;
        }

        // Attach active admission status and auto-computed days stayed to date
        $patientIds = array_keys($grouped);
        if (!empty($patientIds)) {
            $activeAdmissions = Admission::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('history', 1)
                ->whereIn('patient_id', $patientIds)
                ->where('status', 'Admitted')
                ->with(['wardRoom', 'icuBed', 'roomHistories'])
                ->get()
                ->keyBy('patient_id');

            foreach ($grouped as $pid => &$row) {
                if (isset($activeAdmissions[$pid])) {
                    $adm = $activeAdmissions[$pid];
                    $row['is_admitted'] = true;
                    $row['admission_id'] = $adm->id;
                    $row['admission_code'] = $adm->admission_code;
                    $row['days_stayed'] = $adm->days_stayed;
                    $row['admission_date'] = $adm->admission_date?->format('M d, Y');
                    $activeHist = $adm->roomHistories->firstWhere('is_active', true);
                    $row['room_name'] = $activeHist?->room_name ?? $adm->wardRoom?->name ?? $adm->icuBed?->name ?? 'Ward Room';
                } else {
                    $row['is_admitted'] = false;
                    $row['days_stayed'] = null;
                    $row['room_name'] = null;
                }
            }
            unset($row);
        }

        // Filter by search
        if ($this->search) {
            $grouped = array_filter($grouped, fn ($row) =>
                stripos($row['patient_name'], $this->search) !== false
                || stripos($row['patient_code'], $this->search) !== false
            );
        }

        // Sort by name
        usort($grouped, fn ($a, $b) => strcmp($a['patient_name'], $b['patient_name']));

        return array_values($grouped);
    }

    // ── Post charges to ledger (called from createInvoice) ────────────────

    private function postChargesToLedger(int $patientId, array $itemsToCreate, float $manualTotal, int $invoiceId): void
    {
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        foreach ($itemsToCreate as $itm) {
            $existingEntry = PatientAccountEntry::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('source_type', $itm['source_type'])
                ->where('source_id', $itm['source_id'])
                ->where('entry_type', 'charge')
                ->first();

            if ($existingEntry) {
                $existingEntry->update([
                    'billing_invoice_id' => $invoiceId,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
                continue;
            }

            // Determine encounter scope from source type
            $encounterColumn = match ($itm['source_type']) {
                'Lab', 'Pharmacy', 'CSR', 'Radiology', 'Procedure' => $itm['encounter_type'] ?? null,
                'OECB' => 'er_admission_id',
                'Admission' => 'admission_id',
                default => null,
            };

            PatientAccountEntry::postCharge([
                'tenant_id' => $tid,
                'patient_id' => $patientId,
                'admission_id' => $itm['admission_id'] ?? null,
                'er_admission_id' => $itm['er_admission_id'] ?? null,
                'opd_consultation_id' => $itm['opd_consultation_id'] ?? null,
                'or_schedule_id' => $itm['or_schedule_id'] ?? null,
                'dr_schedule_id' => $itm['dr_schedule_id'] ?? null,
                'ob_consultation_id' => $itm['ob_consultation_id'] ?? null,
                'dialysis_session_id' => $itm['dialysis_session_id'] ?? null,
                'entry_type' => 'charge',
                'source_type' => $itm['source_type'],
                'source_id' => $itm['source_id'],
                'source_code' => $itm['source_request_code'],
                'debit' => $itm['total_price'],
                'credit' => 0,
                'description' => $itm['description'],
                'billing_invoice_id' => $invoiceId,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);
        }

        // Post staging manual charges to ledger
        if ($manualTotal > 0) {
            $stagingCharges = BillingManualCharge::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('patient_id', $patientId)
                ->whereNull('billing_invoice_id')
                ->where('is_active', true)
                ->where('history', 1)
                ->get();

            foreach ($stagingCharges as $mc) {
                PatientAccountEntry::postCharge([
                    'tenant_id' => $tid,
                    'patient_id' => $patientId,
                    'entry_type' => 'charge',
                    'source_type' => 'Manual',
                    'source_id' => $mc->id,
                    'source_code' => $mc->charge_type,
                    'debit' => $mc->total_price,
                    'credit' => 0,
                    'description' => $mc->description,
                    'billing_invoice_id' => $invoiceId,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
        }
    }

    // ── Patient Charges Modal (legacy unbilled view) ──────────────────────

    public function viewPatientCharges(int $patientId): void
    {
        $patient = collect($this->unbilled_patients)->firstWhere('patient_id', $patientId);
        if (!$patient) {
            $this->dispatch('toast', type: 'error', message: 'Patient not found.');
            return;
        }
        $this->viewingPatientName = $patient['patient_name'];
        $this->chargesModalPatientId = $patientId;
        $this->refreshViewingCharges($patientId, $patient['items']);
        $this->showChargesModal = true;
    }

    private function refreshViewingCharges(int $patientId, array $serviceItems = []): void
    {
        $this->viewingPatientCharges = $serviceItems;

        $manualCharges = BillingManualCharge::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->where('patient_id', $patientId)
            ->whereNull('billing_invoice_id')
            ->where('is_active', true)
            ->where('history', 1)
            ->get();

        foreach ($manualCharges as $mc) {
            $this->viewingPatientCharges[] = [
                'id' => 'manual_' . $mc->id,
                'source_type' => 'Manual',
                'source_id' => $mc->id,
                'request_code' => $mc->charge_type,
                'patient_name' => $this->viewingPatientName,
                'patient_id' => $patientId,
                'department' => '—',
                'total_amount' => (float) $mc->total_price,
                'date' => $mc->created_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'is_manual' => true,
                'description' => $mc->description,
                'quantity' => (float) $mc->quantity,
                'unit_price' => (float) $mc->unit_price,
                'charge_type' => $mc->charge_type,
            ];
        }
    }

    public function closeChargesModal(): void
    {
        $this->showChargesModal = false;
        $this->viewingPatientCharges = [];
        $this->viewingPatientName = '';
        $this->chargesModalPatientId = null;
    }

    // ── Patient Ledger Modal ──────────────────────────────────────────────

    public function viewPatientLedger(int $patientId): void
    {
        $tid = $this->currentTenantId();
        $patient = Patient::where('id', $patientId)->where('tenant_id', $tid)->first();

        if (!$patient) {
            $this->dispatch('toast', type: 'error', message: 'Patient not found.');
            return;
        }

        $this->viewingPatientId = $patientId;
        $this->viewingPatientName = $patient->last_name . ', ' . $patient->first_name;

        // Patient demographics
        $this->viewingPatientCode = $patient->patient_code ?? '—';
        $this->viewingPatientDob = $patient->date_of_birth?->format('M d, Y') ?? '—';
        $this->viewingPatientGender = $patient->gender?->description ?? '—';
        $this->viewingPatientContact = $patient->phone ?? $patient->email ?? '—';
        $this->viewingPatientAddress = collect([$patient->address_line1, $patient->barangay?->name, $patient->city?->name])->filter()->implode(', ') ?: '—';

        // Find most recent encounter across all encounter types
        $latestEncounter = null;
        $encounterType = '';
        $encounterDate = '';
        $encounterCode = '';
        $encounterPhysician = '';
        $encounterRoom = '';

        // ER Admissions
        $latestEr = $patient->erAdmissions()->latest('admission_datetime')->first();
        if ($latestEr) {
            $latestEncounter = $latestEr;
            $encounterType = 'ER';
            $encounterDate = $latestEr->admission_datetime?->format('M d, Y H:i') ?? '';
            $encounterCode = $latestEr->er_number ?? $latestEr->id;
            $encounterPhysician = $latestEr->attendingPhysician?->full_name ?? '';
            $encounterRoom = $latestEr->erBed?->code ?? '';
        }

        // Admissions (inpatient)
        $latestAdmission = $patient->admissions()->withoutGlobalScopes()->where('history', 1)->latest('admission_date')->with(['attendingPhysician', 'wardRoom', 'icuBed', 'roomHistories'])->first();
        if ($latestAdmission && (!$latestEncounter || $latestAdmission->admission_date?->gt($latestEncounter->admission_datetime ?? $latestEncounter->created_date ?? now()->subYears(10)))) {
            $latestEncounter = $latestAdmission;
            $encounterType = 'Admission';
            $encounterDate = $latestAdmission->admission_date?->format('M d, Y H:i') ?? '';
            $encounterCode = $latestAdmission->admission_code ?? ('ADM-' . $latestAdmission->id);
            $encounterPhysician = $latestAdmission->attendingPhysician?->full_name ?? '';
            $activeHist = $latestAdmission->roomHistories->firstWhere('is_active', true);
            $encounterRoom = $activeHist?->room_name ?? ($latestAdmission->wardRoom?->name ?? $latestAdmission->icuBed?->name ?? '');
            if ($activeHist && $activeHist->ward_name) {
                $encounterRoom .= ' (' . $activeHist->ward_name . ')';
            }
        }

        // OPD Consultations
        $latestOpd = $patient->opdConsultations()->latest('consultation_datetime')->first();
        if ($latestOpd && (!$latestEncounter || $latestOpd->consultation_datetime?->gt($latestEncounter->admission_datetime ?? $latestEncounter->admission_date ?? $latestEncounter->created_date ?? now()->subYears(10)))) {
            $latestEncounter = $latestOpd;
            $encounterType = 'OPD';
            $encounterDate = $latestOpd->consultation_datetime?->format('M d, Y H:i') ?? '';
            $encounterCode = $latestOpd->opd_number ?? $latestOpd->id;
            $encounterPhysician = $latestOpd->attendingPhysician?->full_name ?? '';
            $encounterRoom = $latestOpd->opdClinic?->name ?? '';
        }

        // OB Consultations
        $latestOb = $patient->obConsultations()->latest('created_date')->first();
        if ($latestOb && (!$latestEncounter || $latestOb->created_date?->gt($latestEncounter->admission_datetime ?? $latestEncounter->admission_date ?? $latestEncounter->consultation_datetime ?? now()->subYears(10)))) {
            $latestEncounter = $latestOb;
            $encounterType = 'OB';
            $encounterDate = $latestOb->created_date?->format('M d, Y H:i') ?? '';
            $encounterCode = $latestOb->ob_number ?? $latestOb->id;
            $encounterPhysician = $latestOb->attendingPhysician?->full_name ?? '';
            $encounterRoom = '';
        }

        $this->viewingPatientEncounterType = $encounterType ?: '—';
        $this->viewingPatientEncounterCode = $encounterCode ? (string) $encounterCode : '—';
        $this->viewingPatientAdmissionDate = $encounterDate ?: '—';
        $this->viewingPatientPhysician = $encounterPhysician ?: '—';
        $this->viewingPatientRoom = $encounterRoom ?: '—';

        // Auto-compute inpatient days stayed and accrued room charges if admission exists
        if ($latestAdmission) {
            $this->viewingPatientIsAdmitted = ($latestAdmission->status === 'Admitted' || !$latestAdmission->discharge_date);
            $this->viewingPatientDaysStayed = $latestAdmission->days_stayed;
            $this->viewingPatientAccruedRoomCharge = $latestAdmission->computeAccruedRoomCharges();
            $activeHist = $latestAdmission->roomHistories->firstWhere('is_active', true);
            $rate = (float) ($activeHist?->room_rate ?? $latestAdmission->wardRoom?->room_rate ?? $latestAdmission->icuBed?->room_rate ?? 0);
            $this->viewingPatientRoomRate = '₱' . number_format($rate, 2) . '/day';
        } else {
            $this->viewingPatientIsAdmitted = false;
            $this->viewingPatientDaysStayed = null;
            $this->viewingPatientAccruedRoomCharge = null;
            $this->viewingPatientRoomRate = null;
        }

        // Load ledger entries
        $entries = PatientAccountEntry::getLedger($tid, $patientId);
        $this->viewingLedger = $entries->map(fn ($e) => [
            'id' => $e->id,
            'date' => $e->created_date?->format('Y-m-d H:i') ?? '—',
            'entry_type' => $e->entry_type,
            'source_type' => $e->source_type,
            'source_code' => $e->source_code ?? '—',
            'description' => $e->description ?? '—',
            'debit' => (float) $e->debit,
            'credit' => (float) $e->credit,
            'balance' => (float) $e->balance,
            'invoice_id' => $e->billing_invoice_id,
        ])->toArray();

        $this->viewingPatientBalance = PatientAccountEntry::getPatientBalance($tid, $patientId);
        $this->showLedgerModal = true;
    }

    public function closeLedgerModal(): void
    {
        $this->showLedgerModal = false;
        $this->viewingPatientId = null;
        $this->viewingPatientName = '';
        $this->viewingLedger = [];
        $this->viewingPatientBalance = 0;
        $this->viewingPatientCode = '';
        $this->viewingPatientDob = '';
        $this->viewingPatientGender = '';
        $this->viewingPatientContact = '';
        $this->viewingPatientAddress = '';
        $this->viewingPatientAdmissionDate = '';
        $this->viewingPatientEncounterType = '';
        $this->viewingPatientEncounterCode = '';
        $this->viewingPatientRoom = '';
        $this->viewingPatientPhysician = '';
        $this->viewingPatientIsAdmitted = false;
        $this->viewingPatientDaysStayed = null;
        $this->viewingPatientAccruedRoomCharge = null;
        $this->viewingPatientRoomRate = null;
        $this->selectedLedgerEntries = [];
        $this->ledgerConfirmAction = '';
    }

    public function toggleLedgerEntry(int $entryId): void
    {
        if (in_array($entryId, $this->selectedLedgerEntries)) {
            $this->selectedLedgerEntries = array_values(array_diff($this->selectedLedgerEntries, [$entryId]));
        } else {
            $this->selectedLedgerEntries[] = $entryId;
        }
    }

    public function toggleSelectAllLedger(): void
    {
        if (count($this->selectedLedgerEntries) === count($this->viewingLedger)) {
            $this->selectedLedgerEntries = [];
        } else {
            $this->selectedLedgerEntries = array_column($this->viewingLedger, 'id');
        }
    }

    public function cancelSelectedEntries(): void
    {
        if (empty($this->selectedLedgerEntries)) {
            $this->dispatch('toast', type: 'error', message: 'No entries selected.');
            return;
        }

        $uid = $this->currentUserId();
        $tid = $this->currentTenantId();
        $now = now();

        $entries = PatientAccountEntry::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->whereIn('id', $this->selectedLedgerEntries)
            ->get();

        $cancelledCount = 0;
        foreach ($entries as $entry) {
            if ($entry->entry_type === 'payment') {
                continue; // Skip payments — use revokeSelectedPayments instead
            }

            $entry->update(['history' => 0, 'updated_by' => $uid, 'updated_date' => $now]);
            $cancelledCount++;

            // Deactivate linked billing payment if exists
            if ($entry->billing_invoice_id) {
                BillingPayment::withoutGlobalScopes()
                    ->where('billing_invoice_id', $entry->billing_invoice_id)
                    ->where('patient_id', $this->viewingPatientId)
                    ->update(['history' => 0, 'updated_by' => $uid, 'updated_date' => $now]);
            }
        }

        if ($cancelledCount > 0) {
            $this->recalculatePatientBalance($tid, $this->viewingPatientId);
            $this->refreshLedger();
            $this->selectedLedgerEntries = [];
            $this->ledgerConfirmAction = '';
            $this->dispatch('toast', type: 'success', message: "{$cancelledCount} charge(s) cancelled.");
        } else {
            $this->dispatch('toast', type: 'warning', message: 'No charge entries were selected. Use "Revoke Payment" for payment entries.');
        }
    }

    public function revokeSelectedPayments(): void
    {
        if (empty($this->selectedLedgerEntries)) {
            $this->dispatch('toast', type: 'error', message: 'No entries selected.');
            return;
        }

        $uid = $this->currentUserId();
        $tid = $this->currentTenantId();
        $now = now();

        $entries = PatientAccountEntry::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->whereIn('id', $this->selectedLedgerEntries)
            ->get();

        $revokedCount = 0;
        foreach ($entries as $entry) {
            if ($entry->entry_type !== 'payment') {
                continue; // Skip non-payment entries
            }

            $entry->update(['history' => 0, 'updated_by' => $uid, 'updated_date' => $now]);
            $revokedCount++;

            // Find and deactivate the corresponding BillingPayment record
            $payment = BillingPayment::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('patient_id', $this->viewingPatientId)
                ->where(fn ($q) => $q->where('payment_code', $entry->source_code)->orWhere('reference_no', $entry->source_code))
                ->first();

            if ($payment) {
                $payment->update(['history' => 0, 'updated_by' => $uid, 'updated_date' => $now]);

                // Restore fund source budget
                if ($payment->fund_source_id && $payment->fundSource) {
                    $payment->fundSource->restoreBudget((float) $payment->amount);
                }
            }
        }

        if ($revokedCount > 0) {
            $this->recalculatePatientBalance($tid, $this->viewingPatientId);
            $this->refreshLedger();
            $this->selectedLedgerEntries = [];
            $this->ledgerConfirmAction = '';
            $this->dispatch('toast', type: 'success', message: "{$revokedCount} payment(s) revoked.");
        } else {
            $this->dispatch('toast', type: 'warning', message: 'No payment entries were selected. Use "Cancel Charges" for charge entries.');
        }
    }

    private function recalculatePatientBalance(int $tenantId, int $patientId): void
    {
        $activeEntries = PatientAccountEntry::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('patient_id', $patientId)
            ->where('history', 1)
            ->orderBy('id')
            ->get();

        $runningBalance = 0;
        foreach ($activeEntries as $entry) {
            $runningBalance = $runningBalance + $entry->debit - $entry->credit;
            $newBalance = max(0, $runningBalance);
            if ((float) $entry->balance !== $newBalance) {
                $entry->update(['balance' => $newBalance]);
            }
        }
    }

    private function refreshLedger(): void
    {
        if (!$this->viewingPatientId) return;

        $tid = $this->currentTenantId();
        $entries = PatientAccountEntry::getLedger($tid, $this->viewingPatientId);
        $this->viewingLedger = $entries->map(fn ($e) => [
            'id' => $e->id,
            'date' => $e->created_date?->format('Y-m-d H:i') ?? '—',
            'entry_type' => $e->entry_type,
            'source_type' => $e->source_type,
            'source_code' => $e->source_code ?? '—',
            'description' => $e->description ?? '—',
            'debit' => (float) $e->debit,
            'credit' => (float) $e->credit,
            'balance' => (float) $e->balance,
            'invoice_id' => $e->billing_invoice_id,
        ])->toArray();

        $this->viewingPatientBalance = PatientAccountEntry::getPatientBalance($tid, $this->viewingPatientId);
    }

    public function printStatement(): void
    {
        if (!$this->viewingPatientId) return;

        $mode = in_array($this->statementMode, ['summary', 'detailed']) ? $this->statementMode : 'detailed';
        $token = session('jwt_token', '');
        $url = '/billing/statement/' . $this->viewingPatientId . '/' . $mode . ($token ? '?token=' . urlencode($token) : '');

        $this->statementMode = $mode;
        $this->statementUrl = $url;
        $this->showStatementModal = true;
    }

    public function closeStatementModal(): void
    {
        $this->showStatementModal = false;
        $this->statementMode = 'detailed';
        $this->statementUrl = '';
    }

    // ── Ledger Discount ──────────────────────────────────────────────────

    public function openDiscountModal(int $patientId): void
    {
        $this->discount_patient_id = $patientId;
        $this->ledger_discount_type = 'percentage';
        $this->ledger_discount_value = '';
        $this->ledger_discount_computed = 0;
        $this->ledger_discount_notes = '';
        $this->showDiscountModal = true;
    }

    public function closeDiscountModal(): void
    {
        $this->showDiscountModal = false;
        $this->reset(['discount_patient_id', 'ledger_discount_type', 'ledger_discount_value', 'ledger_discount_computed', 'ledger_discount_notes']);
    }

    public function updatedLedgerDiscountValue(): void
    {
        $tid = $this->currentTenantId();
        $currentBalance = PatientAccountEntry::getPatientBalance($tid, $this->discount_patient_id);
        $val = (float) $this->ledger_discount_value;

        if ($this->ledger_discount_type === 'percentage' && $val > 0) {
            $this->ledger_discount_computed = round($currentBalance * $val / 100, 2);
        } elseif ($this->ledger_discount_type === 'fixed' && $val > 0) {
            $this->ledger_discount_computed = min($val, $currentBalance);
        } else {
            $this->ledger_discount_computed = 0;
        }
    }

    public function saveDiscountEntry(): void
    {
        $this->validate([
            'ledger_discount_value' => ['required', 'numeric', 'min:0.01'],
            'ledger_discount_notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($this->ledger_discount_computed <= 0) {
            $this->dispatch('toast', type: 'error', message: 'Discount amount must be greater than zero.');
            return;
        }

        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        $now = now();

        PatientAccountEntry::postAdjustment([
            'tenant_id' => $tid,
            'patient_id' => $this->discount_patient_id,
            'entry_type' => 'discount',
            'source_type' => 'Discount',
            'source_code' => 'DISC-' . $now->format('YmdHis'),
            'credit' => $this->ledger_discount_computed,
            'debit' => 0,
            'description' => 'Discount: ' . ($this->ledger_discount_type === 'percentage' ? $this->ledger_discount_value . '%' : '₱' . number_format($this->ledger_discount_computed, 2))
                . ($this->ledger_discount_notes ? ' — ' . $this->ledger_discount_notes : ''),
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $this->closeDiscountModal();
        $this->refreshLedger();
        $this->dispatch('toast', type: 'success', message: 'Discount applied to patient account.');
    }

    // ── Invoice Creation (now posts to ledger) ────────────────────────────

    public function payPatientCharges(int $patientId): void
    {
        $patient = collect($this->unbilled_patients)->firstWhere('patient_id', $patientId);
        if (!$patient || (empty($patient['items']) && empty($patient['manual_charges']))) {
            $this->dispatch('toast', type: 'error', message: 'No unbilled charges for this patient.');
            return;
        }

        $this->resetInvoiceForm();
        $this->skipPatientSearchUpdate = true;
        $this->patient_id = (string) $patientId;
        $this->patient_search = $patient['patient_name'];
        $this->selectedServiceItems = array_map(fn ($item) => $item['id'], $patient['items']);

        $firstItem = $patient['items'][0] ?? null;
        if ($firstItem) {
            $this->er_admission_id = $firstItem['er_admission_id'] ?? null;
            $this->admission_id = $firstItem['admission_id'] ?? null;
            $this->opd_consultation_id = $firstItem['opd_consultation_id'] ?? null;
            $this->dr_schedule_id = $firstItem['dr_schedule_id'] ?? null;
            $this->ob_consultation_id = $firstItem['ob_consultation_id'] ?? null;
        }

        $this->invoice_code = $this->generateInvoiceCode();
        $this->showInvoiceModal = true;
    }

    public function openCreateInvoice(): void
    {
        $this->resetInvoiceForm();
        $this->invoice_code = $this->generateInvoiceCode();
        $this->showInvoiceModal = true;
    }

    public function toggleServiceItem(string $itemId): void
    {
        if (in_array($itemId, $this->selectedServiceItems)) {
            $this->selectedServiceItems = array_values(array_filter($this->selectedServiceItems, fn ($id) => $id !== $itemId));
        } else {
            $this->selectedServiceItems[] = $itemId;
        }
    }

    public function updatedPatientSearch(): void
    {
        if ($this->skipPatientSearchUpdate) {
            $this->skipPatientSearchUpdate = false;
            return;
        }
        $this->patient_id = null;
        $this->er_admission_id = null;
        $this->admission_id = null;
        $this->opd_consultation_id = null;
    }

    public function selectPatient(int $patientId): void
    {
        $this->skipPatientSearchUpdate = true;
        $this->patient_id = (string) $patientId;
        $this->patient_search = null;
    }

    public function createInvoice(): void
    {
        $this->validate([
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'invoice_date' => ['required', 'date'],
        ]);

        $stagingChargeCount = BillingManualCharge::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->where('patient_id', (int) $this->patient_id)
            ->whereNull('billing_invoice_id')
            ->where('is_active', true)
            ->where('history', 1)
            ->count();

        if (empty($this->selectedServiceItems) && $stagingChargeCount === 0) {
            $this->dispatch('toast', type: 'warning', message: 'Select at least one service request or add a manual charge to bill.');
            return;
        }

        $uid = $this->currentUserId();
        $tid = $this->currentTenantId();
        $now = now();

        $totalAmount = 0;
        $itemsToCreate = [];

        $patientId = (int) $this->patient_id;
        $erAdmissionId = $this->er_admission_id ? (int) $this->er_admission_id : null;
        $opdConsultationId = $this->opd_consultation_id ? (int) $this->opd_consultation_id : null;
        $admissionId = $this->admission_id ? (int) $this->admission_id : null;
        $drScheduleId = $this->dr_schedule_id ? (int) $this->dr_schedule_id : null;
        $obConsultationId = $this->ob_consultation_id ? (int) $this->ob_consultation_id : null;

        foreach ($this->selectedServiceItems as $selId) {
            $parts = explode('_', $selId, 2);
            $type = $parts[0];
            $sourceId = (int) $parts[1];
            $amount = 0;

            if ($type === 'lab') {
                $req = LabRequest::withoutGlobalScopes()->find($sourceId);
                if (!$req) continue;
                $amount = (float) $req->total_charge;
                $itemsToCreate[] = [
                    'source_type' => 'Lab',
                    'source_id' => $req->id,
                    'source_request_code' => $req->request_code,
                    'description' => 'Lab: ' . $req->request_code,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                    'update_table' => 'lab_requests',
                    'update_id' => $req->id,
                    'er_admission_id' => $req->er_admission_id,
                    'admission_id' => $req->admission_id,
                    'opd_consultation_id' => $req->opd_consultation_id,
                    'or_schedule_id' => $req->or_schedule_id,
                    'ob_consultation_id' => $req->ob_consultation_id,
                    'dr_schedule_id' => $req->dr_schedule_id,
                    'dialysis_session_id' => $req->dialysis_session_id,
                ];
            } elseif ($type === 'pharm') {
                $req = PharmacyRequest::withoutGlobalScopes()->find($sourceId);
                if (!$req) continue;
                $amount = (float) $req->total_amount;
                $itemsToCreate[] = [
                    'source_type' => 'Pharmacy',
                    'source_id' => $req->id,
                    'source_request_code' => $req->request_code,
                    'description' => 'Pharmacy: ' . $req->request_code,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                    'update_table' => 'pharmacy_requests',
                    'update_id' => $req->id,
                    'er_admission_id' => $req->er_admission_id,
                    'admission_id' => $req->admission_id,
                    'opd_consultation_id' => $req->opd_consultation_id,
                    'or_schedule_id' => $req->or_schedule_id,
                    'ob_consultation_id' => $req->ob_consultation_id,
                    'dr_schedule_id' => $req->dr_schedule_id,
                    'dialysis_session_id' => $req->dialysis_session_id,
                ];
            } elseif ($type === 'csr') {
                $req = CsrRequest::withoutGlobalScopes()->find($sourceId);
                if (!$req) continue;
                $amount = (float) $req->total_amount;
                $itemsToCreate[] = [
                    'source_type' => 'CSR',
                    'source_id' => $req->id,
                    'source_request_code' => $req->request_code,
                    'description' => 'CSR: ' . $req->request_code,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                    'update_table' => 'csr_requests',
                    'update_id' => $req->id,
                    'er_admission_id' => $req->er_admission_id,
                    'admission_id' => $req->admission_id,
                    'opd_consultation_id' => $req->opd_consultation_id,
                    'or_schedule_id' => $req->or_schedule_id,
                    'ob_consultation_id' => $req->ob_consultation_id,
                    'dr_schedule_id' => $req->dr_schedule_id,
                    'dialysis_session_id' => $req->dialysis_session_id,
                ];
            } elseif ($type === 'rad') {
                $req = RadiologyRequest::withoutGlobalScopes()->find($sourceId);
                if (!$req) continue;
                $amount = (float) $req->total_amount;
                $itemsToCreate[] = [
                    'source_type' => 'Radiology',
                    'source_id' => $req->id,
                    'source_request_code' => $req->request_code,
                    'description' => 'Radiology: ' . $req->request_code,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                    'update_table' => 'radiology_requests',
                    'update_id' => $req->id,
                    'er_admission_id' => $req->er_admission_id,
                    'admission_id' => $req->admission_id,
                    'opd_consultation_id' => $req->opd_consultation_id,
                    'or_schedule_id' => $req->or_schedule_id,
                    'ob_consultation_id' => $req->ob_consultation_id,
                    'dr_schedule_id' => $req->dr_schedule_id,
                    'dialysis_session_id' => $req->dialysis_session_id,
                ];
            } elseif ($type === 'proc') {
                $req = ProcedureRequest::withoutGlobalScopes()->find($sourceId);
                if (!$req) continue;
                $amount = (float) $req->total_amount;
                $itemsToCreate[] = [
                    'source_type' => 'Procedure',
                    'source_id' => $req->id,
                    'source_request_code' => $req->request_code,
                    'description' => 'Procedure: ' . $req->request_code,
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                    'update_table' => 'procedure_requests',
                    'update_id' => $req->id,
                    'er_admission_id' => $req->er_admission_id,
                    'admission_id' => $req->admission_id,
                    'opd_consultation_id' => $req->opd_consultation_id,
                    'or_schedule_id' => $req->or_schedule_id,
                    'ob_consultation_id' => $req->ob_consultation_id,
                    'dr_schedule_id' => $req->dr_schedule_id,
                    'dialysis_session_id' => $req->dialysis_session_id,
                ];
            } elseif ($type === 'oecb') {
                $entry = \App\Models\Oecb\OecbEntry::with('erAdmission')->find($sourceId);
                if (!$entry || !$entry->erAdmission) continue;
                $amount = (float) ($entry->oecb_fixed_fee ?? 0);
                $itemsToCreate[] = [
                    'source_type' => 'OECB',
                    'source_id' => $entry->id,
                    'source_request_code' => $entry->erAdmission->admission_code,
                    'description' => 'OECB: ' . ($entry->core_presentation ?? $entry->erAdmission->admission_code) . ' [' . ($entry->oecb_package_code ?? '') . ']',
                    'quantity' => 1,
                    'unit_price' => $amount,
                    'total_price' => $amount,
                    'update_table' => 'oecb_entries',
                    'update_id' => $entry->id,
                    'er_admission_id' => $entry->er_admission_id,
                ];
            } elseif ($type === 'adm') {
                $selParts = explode('_', $selId);
                $hasHistory = in_array('hist', $selParts);

                if ($hasHistory) {
                    $histId = (int) end($selParts);
                    $h = AdmissionRoomHistory::withoutGlobalScopes()->with(['admission'])->find($histId);
                    if (!$h) continue;
                    $days = $h->days_stayed;
                    $rate = (float) $h->room_rate;
                    $roomName = $h->room_name ?? 'Unknown Room';
                    $wardName = $h->ward_name ?? '';
                    $amount = (float) $h->total_charge;
                    $itemsToCreate[] = [
                        'source_type' => 'Admission',
                        'source_id' => $h->admission_id,
                        'source_request_code' => $h->admission?->admission_code ?? (Admission::withoutGlobalScopes()->find($h->admission_id)?->admission_code ?? ''),
                        'description' => 'Room & Board: ' . $roomName . ($wardName ? ' (' . $wardName . ')' : '') . ' — ' . $days . ' day(s) @ ₱' . number_format($rate, 2) . '/day',
                        'quantity' => $days,
                        'unit_price' => $rate,
                        'total_price' => $amount,
                        'update_table' => 'admissions',
                        'update_id' => $h->admission_id,
                        'admission_id' => $h->admission_id,
                    ];
                } else {
                    $req = Admission::withoutGlobalScopes()->with(['wardRoom', 'icuBed'])->find($sourceId);
                    if (!$req) continue;
                    $days = $req->days_stayed;
                    $rate = (float) ($req->wardRoom?->room_rate ?? $req->icuBed?->room_rate ?? 0);
                    $roomName = $req->wardRoom?->name ?? $req->icuBed?->name ?? 'Room & Board';
                    $amount = (float) ($days * $rate);
                    $itemsToCreate[] = [
                        'source_type' => 'Admission',
                        'source_id' => $req->id,
                        'source_request_code' => $req->admission_code,
                        'description' => 'Room & Board: ' . $roomName . ' — ' . $days . ' day(s) @ ₱' . number_format($rate, 2) . '/day',
                        'quantity' => $days,
                        'unit_price' => $rate,
                        'total_price' => $amount,
                        'update_table' => 'admissions',
                        'update_id' => $req->id,
                        'admission_id' => $req->id,
                    ];
                }
            }

            $totalAmount += $amount;
        }

        $stagingCharges = BillingManualCharge::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('patient_id', $patientId)
            ->whereNull('billing_invoice_id')
            ->where('is_active', true)
            ->where('history', 1)
            ->get();
        $manualTotal = (float) $stagingCharges->sum('total_price');
        $totalAmount += $manualTotal;

        if (empty($itemsToCreate) && $stagingCharges->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No valid service requests selected.');
            return;
        }

        $netTotal = $totalAmount - $this->discount_amount;

        $invoice = BillingInvoice::create([
            'tenant_id' => $tid,
            'invoice_code' => $this->invoice_code,
            'invoice_date' => $this->invoice_date,
            'total_amount' => $totalAmount,
            'discount_type' => $this->discount_type ?: null,
            'discount_value' => $this->discount_value,
            'discount_amount' => $this->discount_amount,
            'paid_amount' => 0,
            'balance' => max(0, $netTotal),
            'status' => 'Unpaid',
            'notes' => $this->invoice_notes,
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $invoice->update([
            'patient_id' => $patientId,
            'er_admission_id' => $erAdmissionId,
            'opd_consultation_id' => $opdConsultationId,
            'admission_id' => $admissionId,
            'dr_schedule_id' => $drScheduleId,
            'ob_consultation_id' => $obConsultationId,
        ]);

        foreach ($itemsToCreate as $itm) {
            BillingInvoiceItem::create([
                'billing_invoice_id' => $invoice->id,
                'source_type' => $itm['source_type'],
                'source_id' => $itm['source_id'],
                'source_request_code' => $itm['source_request_code'],
                'description' => $itm['description'],
                'quantity' => $itm['quantity'],
                'unit_price' => $itm['unit_price'],
                'total_price' => $itm['total_price'],
                'is_active' => true,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => $now,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);

            $sourceTable = $itm['update_table'];
            $sourceId = $itm['update_id'];
            $allowedBillingTables = ['lab_requests', 'pharmacy_requests', 'csr_requests', 'radiology_requests', 'procedure_requests', 'oecb_entries', 'admissions'];
            if (in_array($sourceTable, $allowedBillingTables, true)) {
                \Illuminate\Support\Facades\DB::table($sourceTable)
                    ->where('id', $sourceId)
                    ->update([
                        'billing_id' => $invoice->id,
                        'billing_status' => 'Billed',
                        'updated_by' => $uid,
                        'updated_date' => $now,
                    ]);
            }
        }

        $this->selectedServiceItems = [];

        $stagingCharges = BillingManualCharge::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('patient_id', $patientId)
            ->whereNull('billing_invoice_id')
            ->where('is_active', true)
            ->where('history', 1)
            ->get();

        if ($stagingCharges->isNotEmpty()) {
            BillingManualCharge::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('patient_id', $patientId)
                ->whereNull('billing_invoice_id')
                ->where('is_active', true)
                ->where('history', 1)
                ->update([
                    'billing_invoice_id' => $invoice->id,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);

            $this->recalculateInvoiceTotals($invoice->id);
        }

        // Post charges to patient account ledger
        $this->postChargesToLedger($patientId, $itemsToCreate, $manualTotal, $invoice->id);

        $this->closeInvoiceModal();
        $this->dispatch('toast', type: 'success', message: 'Invoice ' . $this->invoice_code . ' created and posted to patient account.');
    }

    public function viewInvoice(int $id): void
    {
        $this->viewingInvoiceId = $id;
        $this->showInvoiceDetailModal = true;
    }

    public function confirmDeleteInvoice(int $id): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $invoice = BillingInvoice::findOrFail($id);
        $invoice->update([
            'status' => 'Cancelled',
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        // Reverse ledger entries for this invoice
        PatientAccountEntry::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->where('billing_invoice_id', $id)
            ->where('entry_type', 'charge')
            ->update([
                'history' => 0,
                'updated_by' => $uid,
                'updated_date' => $now,
            ]);

        // Recalculate balances for affected patients
        $affectedPatients = PatientAccountEntry::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->where('billing_invoice_id', $id)
            ->distinct()
            ->pluck('patient_id');

        foreach ($affectedPatients as $pid) {
            $lastEntry = PatientAccountEntry::withoutGlobalScopes()
                ->where('tenant_id', $this->currentTenantId())
                ->where('patient_id', $pid)
                ->orderBy('id', 'desc')
                ->first();
            if ($lastEntry) $lastEntry->recalculateBalance();
        }

        // Restore billing_status on linked source requests
        $items = BillingInvoiceItem::where('billing_invoice_id', $id)->get();
        foreach ($items as $item) {
            $sourceTable = match ($item->source_type) {
                'Lab' => 'lab_requests',
                'Pharmacy' => 'pharmacy_requests',
                'CSR' => 'csr_requests',
                'Radiology' => 'radiology_requests',
                'Procedure' => 'procedure_requests',
                'OECB' => 'oecb_entries',
                'Admission' => 'admissions',
                default => null,
            };
            if ($sourceTable && $item->source_id) {
                \Illuminate\Support\Facades\DB::table($sourceTable)
                    ->where('id', $item->source_id)
                    ->update([
                        'billing_id' => null,
                        'billing_status' => 'Pending',
                        'updated_by' => $uid,
                        'updated_date' => $now,
                    ]);
            }
        }

        foreach (BillingPayment::withoutGlobalScopes()->where('billing_invoice_id', $id)->whereNotNull('fund_source_id')->get() as $payment) {
            if ($payment->fundSource) {
                $payment->fundSource->restoreBudget((float) $payment->amount);
            }
        }
        $this->dispatch('toast', type: 'success', message: 'Invoice cancelled. Ledger entries reversed.');
    }

    // ── Payments (now post to ledger) ─────────────────────────────────────

    public function openAddPayment(int $patientId): void
    {
        $this->resetPaymentForm();
        $this->paymentPatientId = $patientId;
        $this->payment_code = $this->generatePaymentCode();
        $this->showPaymentModal = true;
    }

    public function addPayment(): void
    {
        $this->validate([
            'payment_date' => ['required', 'date'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $uid = $this->currentUserId();
        $tid = $this->currentTenantId();

        // Prevent duplicate OR number within same tenant
        if (!empty($this->payment_reference)) {
            $duplicateExists = BillingPayment::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('reference_no', $this->payment_reference)
                ->exists();
            if ($duplicateExists) {
                $this->dispatch('toast', type: 'error', message: 'This OR / Reference number has already been used. Please check your records.');
                return;
            }
        }
        $now = now();
        $amount = (float) $this->payment_amount;
        $patientId = $this->paymentPatientId;

        // Post payment to ledger
        PatientAccountEntry::postPayment([
            'tenant_id' => $tid,
            'patient_id' => $patientId,
            'entry_type' => 'payment',
            'source_type' => 'Payment',
            'source_code' => $this->payment_code,
            'credit' => $amount,
            'description' => 'Payment via ' . $this->payment_method . ($this->payment_reference ? ' (Ref: ' . $this->payment_reference . ')' : ''),
            'notes' => $this->payment_notes ?: null,
            'fund_source_id' => $this->payment_fund_source_id ? (int) $this->payment_fund_source_id : null,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        // Also create BillingPayment record for backward compatibility
        BillingPayment::create([
            'tenant_id' => $tid,
            'patient_id' => $patientId,
            'billing_invoice_id' => $this->paymentInvoiceId,
            'payment_code' => $this->payment_code,
            'payment_date' => $this->payment_date,
            'amount' => $amount,
            'payment_method' => $this->payment_method,
            'fund_source_id' => $this->payment_fund_source_id ? (int) $this->payment_fund_source_id : null,
            'health_insurance_provider_id' => $this->payment_insurance_provider_id ? (int) $this->payment_insurance_provider_id : null,
            'reference_no' => $this->payment_reference ?: null,
            'notes' => $this->payment_notes ?: null,
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        if ($this->payment_fund_source_id) {
            FundSource::findOrFail((int) $this->payment_fund_source_id)->deductBudget($amount);
        }

        // Update invoice if linked
        if ($this->paymentInvoiceId) {
            $invoice = BillingInvoice::find($this->paymentInvoiceId);
            if ($invoice) {
                $newPaid = $invoice->paid_amount + $amount;
                $newBalance = $invoice->total_amount - $newPaid - $invoice->discount_amount;
                $newStatus = $newBalance <= 0 ? 'Paid' : ($newPaid > 0 ? 'Partial' : 'Unpaid');
                if ($newBalance < 0) $newBalance = 0;

                $invoice->update([
                    'paid_amount' => $newPaid,
                    'balance' => $newBalance,
                    'status' => $newStatus,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);
            }
        }

        $this->closePaymentModal();
        $this->dispatch('toast', type: 'success', message: 'Payment recorded to patient account.');

        // Refresh ledger if open
        if ($this->showLedgerModal && $this->viewingPatientId) {
            $this->refreshLedger();
        }
    }

    public function resetServiceItems(): void
    {
        $this->selectedServiceItems = [];
    }

    private function resetInvoiceForm(): void
    {
        $this->reset([
            'editingInvoiceId', 'patient_id', 'patient_search', 'admission_id',
            'er_admission_id', 'opd_consultation_id', 'dr_schedule_id', 'ob_consultation_id',
            'invoice_notes',
            'discount_type', 'discount_value', 'discount_amount',
        ]);
        $this->invoice_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    private function resetPaymentForm(): void
    {
        $this->reset([
            'paymentInvoiceId', 'paymentPatientId', 'payment_code', 'payment_amount', 'payment_method',
            'payment_reference', 'payment_notes', 'payment_fund_source_id', 'payment_insurance_provider_id',
        ]);
        $this->payment_date = now()->format('Y-m-d');
        $this->payment_method = 'Cash';
        $this->resetValidation();
    }

    // ── Manual Charges ────────────────────────────────────────────────────

    public function openManualChargeForm(): void
    {
        $this->resetManualChargeForm();
        $this->showManualChargeForm = true;
    }

    public function addManualCharge(): void
    {
        $this->validate([
            'manualChargeDesc' => ['required', 'string', 'max:255'],
            'manualChargeQty' => ['required', 'numeric', 'min:0.01'],
            'manualChargePrice' => ['required', 'numeric', 'min:0'],
            'manualChargeType' => ['required', 'string', 'max:50'],
        ]);

        if (!$this->viewingInvoiceId) return;

        $uid = $this->currentUserId();
        $tid = $this->currentTenantId();
        $now = now();
        $qty = (float) $this->manualChargeQty;
        $price = (float) $this->manualChargePrice;
        $total = $qty * $price;

        BillingManualCharge::create([
            'tenant_id' => $tid,
            'billing_invoice_id' => $this->viewingInvoiceId,
            'charge_type' => $this->manualChargeType,
            'description' => $this->manualChargeDesc,
            'quantity' => $qty,
            'unit_price' => $price,
            'total_price' => $total,
            'notes' => $this->manualChargeNotes ?: null,
            'is_active' => true,
            'history' => 1,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $this->recalculateInvoiceTotals($this->viewingInvoiceId);

        $this->showManualChargeForm = false;
        $this->resetManualChargeForm();
        $this->dispatch('toast', type: 'success', message: 'Manual charge added.');
    }

    public function removeManualCharge(int $chargeId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $charge = BillingManualCharge::findOrFail($chargeId);
        $invoiceId = $charge->billing_invoice_id;
        $charge->update([
            'history' => 0,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $this->recalculateInvoiceTotals($invoiceId);
        $this->dispatch('toast', type: 'success', message: 'Manual charge removed.');
    }

    public function openManualChargeForPatient(int $patientId): void
    {
        $this->resetManualChargeForm();
        $this->manualChargePatientId = $patientId;
        $this->showPatientManualChargeForm = true;
    }

    public function addManualChargeForPatient(): void
    {
        $this->validate([
            'manualChargeDesc' => ['required', 'string', 'max:255'],
            'manualChargeQty' => ['required', 'numeric', 'min:0.01'],
            'manualChargePrice' => ['required', 'numeric', 'min:0'],
            'manualChargeType' => ['required', 'string', 'max:50'],
        ]);

        if (!$this->manualChargePatientId) return;

        $qty = (float) $this->manualChargeQty;
        $price = (float) $this->manualChargePrice;
        $now = now();
        $uid = $this->currentUserId();

        // Post to ledger directly
        PatientAccountEntry::postCharge([
            'tenant_id' => $this->currentTenantId(),
            'patient_id' => $this->manualChargePatientId,
            'entry_type' => 'charge',
            'source_type' => 'Manual',
            'source_code' => $this->manualChargeType,
            'debit' => $qty * $price,
            'credit' => 0,
            'description' => $this->manualChargeDesc,
            'notes' => $this->manualChargeNotes ?: null,
            'created_by' => $uid,
            'created_date' => $now,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);

        $chargedPatientId = (int) $this->manualChargePatientId;
        $this->showPatientManualChargeForm = false;
        $this->resetManualChargeForm();

        if ($this->showLedgerModal && $this->viewingPatientId === $chargedPatientId) {
            $this->viewPatientLedger($chargedPatientId);
        }

        $this->dispatch('toast', type: 'success', message: 'Manual charge posted to patient account.');
    }

    public function removeManualChargeForPatient(int $patientId, int $index): void
    {
        $charges = BillingManualCharge::withoutGlobalScopes()
            ->where('tenant_id', $this->currentTenantId())
            ->where('patient_id', $patientId)
            ->whereNull('billing_invoice_id')
            ->where('is_active', true)
            ->where('history', 1)
            ->get();

        if (isset($charges[$index])) {
            $charges[$index]->update([
                'history' => 0,
                'updated_by' => $this->currentUserId(),
                'updated_date' => now(),
            ]);

            if ($this->showChargesModal && $this->chargesModalPatientId === $patientId) {
                $patient = collect($this->unbilled_patients)->firstWhere('patient_id', $patientId);
                $this->refreshViewingCharges($patientId, $patient['items'] ?? []);
            }

            $this->dispatch('toast', type: 'success', message: 'Manual charge removed.');
        }
    }

    public function closePatientManualChargeForm(): void
    {
        $this->showPatientManualChargeForm = false;
        $this->resetManualChargeForm();
    }

    private function recalculateInvoiceTotals(int $invoiceId): void
    {
        $uid = $this->currentUserId();
        $now = now();
        $invoice = BillingInvoice::findOrFail($invoiceId);

        $itemTotal = (float) BillingInvoiceItem::where('billing_invoice_id', $invoiceId)
            ->where('history', 1)->sum('total_price');

        $manualTotal = (float) BillingManualCharge::where('billing_invoice_id', $invoiceId)
            ->where('history', 1)->sum('total_price');

        $discountAmount = (float) $invoice->discount_amount;
        $newTotal = $itemTotal + $manualTotal;
        $newBalance = $newTotal - $discountAmount - $invoice->paid_amount;
        $newStatus = $newBalance <= 0 ? 'Paid' : ($invoice->paid_amount > 0 ? 'Partial' : 'Unpaid');
        if ($newBalance < 0) $newBalance = 0;

        $invoice->update([
            'total_amount' => $newTotal,
            'balance' => $newBalance,
            'status' => $newStatus,
            'updated_by' => $uid,
            'updated_date' => $now,
        ]);
    }

    private function resetManualChargeForm(): void
    {
        $this->reset([
            'manualChargeType', 'manualChargeDesc', 'manualChargeQty',
            'manualChargePrice', 'manualChargeNotes', 'manualChargePatientId',
        ]);
        $this->manualChargeType = 'Miscellaneous';
        $this->manualChargeQty = '1';
        $this->manualChargePrice = '0';
        $this->resetValidation();
    }

    public function closeInvoiceModal(): void
    {
        $this->showInvoiceModal = false;
        $this->resetInvoiceForm();
    }

    public function closeInvoiceDetailModal(): void
    {
        $this->showInvoiceDetailModal = false;
        $this->viewingInvoiceId = null;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetPaymentForm();
    }

    // ── Render ────────────────────────────────────────────────────────────

    public function render()
    {
        $tid = $this->currentTenantId();

        $patients = [];
        if ($this->patient_search && strlen($this->patient_search) >= 2) {
            $patients = Patient::where('tenant_id', $tid)
                ->where(function ($q) {
                    $q->where('last_name', 'like', '%' . $this->patient_search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->patient_search . '%')
                      ->orWhere('patient_code', 'like', '%' . $this->patient_search . '%');
                })
                ->orderBy('last_name')
                ->limit(10)
                ->get(['id', 'patient_code', 'last_name', 'first_name']);
        }

        $detailInvoice = null;
        if ($this->showInvoiceDetailModal && $this->viewingInvoiceId) {
            $detailInvoice = BillingInvoice::with(['patient', 'erAdmission', 'admission', 'items', 'payments', 'manualCharges'])
                ->find($this->viewingInvoiceId);
            if ($detailInvoice) {
                $this->detail_discount_type = $detailInvoice->discount_type ?? '';
                $this->detail_discount_value = (string) ($detailInvoice->discount_value ?? '0');
                $this->detail_discount_amount = (float) ($detailInvoice->discount_amount ?? 0);
            }
        }

        $invoices = BillingInvoice::query()
            ->with(['patient'])
            ->where('tenant_id', $tid)
            ->when($this->search && $this->activeTab === 2, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('invoice_code', 'like', '%' . $this->search . '%')
                          ->orWhere('status', 'like', '%' . $this->search . '%')
                          ->orWhereHas('patient', fn ($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                                ->orWhere('patient_code', 'like', '%' . $this->search . '%')
                          )
                )
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage, ['*'], 'invoicesPage');

        $payments = BillingPayment::query()
            ->with(['patient', 'invoice.patient'])
            ->where('tenant_id', $tid)
            ->when($this->search && $this->activeTab === 3, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('payment_code', 'like', '%' . $this->search . '%')
                          ->orWhere('payment_method', 'like', '%' . $this->search . '%')
                          ->orWhere('reference_no', 'like', '%' . $this->search . '%')
                          ->orWhereHas('patient', fn ($p) =>
                              $p->where('last_name', 'like', '%' . $this->search . '%')
                                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                          )
                )
            )
            ->orderBy('created_date', 'desc')
            ->paginate($this->perPage, ['*'], 'paymentsPage');

        $this->fund_sources = FundSource::where('is_active', true)
            ->where('tenant_id', $tid)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $this->insurance_providers = HealthInsuranceProvider::where('is_active', true)
            ->where('tenant_id', $tid)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        // Summary stats — from ledger
        $totalUnbilled = 0;
        foreach ($this->unbilled_requests as $req) {
            $totalUnbilled += $req['total_amount'];
        }
        $totalUnbilled += (float) BillingManualCharge::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->whereNull('billing_invoice_id')
            ->where('is_active', true)
            ->where('history', 1)
            ->sum('total_price');

        // Ledger-based stats
        $totalCharges = (float) PatientAccountEntry::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('history', 1)
            ->charges()
            ->sum('debit');

        $totalPayments = (float) PatientAccountEntry::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('history', 1)
            ->payments()
            ->sum('credit');

        $totalOutstanding = max(0, $totalCharges - $totalPayments);

        $totalCollected = (float) BillingPayment::where('tenant_id', $tid)->sum('amount');

        $agingBuckets = [];
        $now = now()->startOfDay();
        $unpaidInvoices = BillingInvoice::where('tenant_id', $tid)
            ->whereIn('status', ['Unpaid', 'Partial'])
            ->get();

        $buckets = [
            '0-30 days'  => ['min' => 0,  'max' => 30],
            '31-60 days' => ['min' => 31, 'max' => 60],
            '61-90 days' => ['min' => 61, 'max' => 90],
            '90+ days'   => ['min' => 91, 'max' => PHP_INT_MAX],
        ];

        foreach ($buckets as $label => $range) {
            $filtered = $unpaidInvoices->filter(fn ($inv) => $inv->invoice_date && $inv->invoice_date->diffInDays($now) >= $range['min'] && $inv->invoice_date->diffInDays($now) <= $range['max']);
            $agingBuckets[$label] = [
                'count'   => $filtered->count(),
                'total'   => (float) $filtered->sum('balance'),
                'invoices' => $filtered->values(),
            ];
        }

        $dailyCollections = BillingPayment::where('tenant_id', $tid)
            ->selectRaw('payment_date, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_date')
            ->orderBy('payment_date', 'desc')
            ->limit(30)
            ->get();

        $monthlyRevenue = BillingInvoice::where('tenant_id', $tid)
            ->whereIn('status', ['Paid', 'Partial'])
            ->selectRaw('YEAR(invoice_date) as yr, MONTH(invoice_date) as mo, COUNT(*) as count, SUM(total_amount) as total, SUM(paid_amount) as collected')
            ->groupByRaw('YEAR(invoice_date), MONTH(invoice_date)')
            ->orderByRaw('YEAR(invoice_date) DESC, MONTH(invoice_date) DESC')
            ->limit(12)
            ->get();

        return view('livewire.billing.billing-crud', compact(
            'patients', 'detailInvoice', 'invoices', 'payments',
            'totalUnbilled', 'totalOutstanding', 'totalCollected',
            'agingBuckets', 'dailyCollections', 'monthlyRevenue'
        ));
    }
}
