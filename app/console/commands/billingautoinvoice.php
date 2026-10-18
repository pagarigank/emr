<?php

namespace App\Console\Commands;

use App\Models\Admission;
use App\Models\Billing\BillingInvoice;
use App\Models\Billing\BillingInvoiceItem;
use App\Models\CsrRequest;
use App\Models\Er\ErAdmission;
use App\Models\LabRequest;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Radiology\RadiologyRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BillingAutoInvoice extends Command
{
    protected $signature = 'billing:auto-invoice';
    protected $description = 'Auto-create invoices for completed billable service requests, OECB charges, and discharged room charges';

    private function resolvePatientId(object $r): ?int
    {
        return $r->patient_id
            ?? $r->erAdmission?->patient_id
            ?? $r->opdConsultation?->patient_id
            ?? $r->admission?->patient_id
            ?? $r->dialysisSession?->patient_id
            ?? $r->orSchedule?->patient_id
            ?? $r->obConsultation?->patient_id
            ?? $r->drSchedule?->patient_id;
    }

    public function handle(): int
    {
        $now = now();
        $uid = 1;

        $items = [];

        // ── Lab Requests ──────────────────────────────────────────────────────
        LabRequest::withoutGlobalScopes()
            ->where('history', 1)
            ->where('status', 'Completed')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['erAdmission', 'opdConsultation', 'admission', 'dialysisSession', 'orSchedule', 'obConsultation', 'drSchedule'])
            ->chunk(100, function ($requests) use (&$items) {
                foreach ($requests as $r) {
                    $pid = $this->resolvePatientId($r);
                    if (!$pid) continue;
                    $items[] = [
                        'tenant_id' => $r->tenant_id,
                        'patient_id' => $pid,
                        'er_admission_id' => $r->er_admission_id,
                        'opd_consultation_id' => $r->opd_consultation_id,
                        'admission_id' => $r->admission_id,
                        'dialysis_session_id' => $r->dialysis_session_id,
                        'or_schedule_id' => $r->or_schedule_id,
                        'ob_consultation_id' => $r->ob_consultation_id,
                        'dr_schedule_id' => $r->dr_schedule_id,
                        'source_type' => 'Lab',
                        'source_id' => $r->id,
                        'source_request_code' => $r->request_code,
                        'description' => 'Lab: ' . $r->request_code,
                        'quantity' => 1,
                        'unit_price' => (float) $r->total_charge,
                        'total_price' => (float) $r->total_charge,
                        'update_table' => 'lab_requests',
                        'update_id' => $r->id,
                    ];
                }
            });

        // ── Pharmacy Requests ─────────────────────────────────────────────────
        PharmacyRequest::withoutGlobalScopes()
            ->where('history', 1)
            ->where('status', 'Complete')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['erAdmission', 'opdConsultation', 'admission', 'dialysisSession', 'orSchedule', 'obConsultation', 'drSchedule'])
            ->chunk(100, function ($requests) use (&$items) {
                foreach ($requests as $r) {
                    $pid = $this->resolvePatientId($r);
                    if (!$pid) continue;
                    $items[] = [
                        'tenant_id' => $r->tenant_id,
                        'patient_id' => $pid,
                        'er_admission_id' => $r->er_admission_id,
                        'opd_consultation_id' => $r->opd_consultation_id,
                        'admission_id' => $r->admission_id,
                        'dialysis_session_id' => $r->dialysis_session_id,
                        'or_schedule_id' => $r->or_schedule_id,
                        'ob_consultation_id' => $r->ob_consultation_id,
                        'dr_schedule_id' => $r->dr_schedule_id,
                        'source_type' => 'Pharmacy',
                        'source_id' => $r->id,
                        'source_request_code' => $r->request_code,
                        'description' => 'Pharmacy: ' . $r->request_code,
                        'quantity' => 1,
                        'unit_price' => (float) $r->total_amount,
                        'total_price' => (float) $r->total_amount,
                        'update_table' => 'pharmacy_requests',
                        'update_id' => $r->id,
                    ];
                }
            });

        // ── CSR Requests ──────────────────────────────────────────────────────
        CsrRequest::withoutGlobalScopes()
            ->where('history', 1)
            ->where('status', 'Complete')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['erAdmission', 'opdConsultation', 'admission', 'dialysisSession', 'orSchedule', 'obConsultation', 'drSchedule'])
            ->chunk(100, function ($requests) use (&$items) {
                foreach ($requests as $r) {
                    $pid = $this->resolvePatientId($r);
                    if (!$pid) continue;
                    $items[] = [
                        'tenant_id' => $r->tenant_id,
                        'patient_id' => $pid,
                        'er_admission_id' => $r->er_admission_id,
                        'opd_consultation_id' => $r->opd_consultation_id,
                        'admission_id' => $r->admission_id,
                        'dialysis_session_id' => $r->dialysis_session_id,
                        'or_schedule_id' => $r->or_schedule_id,
                        'ob_consultation_id' => $r->ob_consultation_id,
                        'dr_schedule_id' => $r->dr_schedule_id,
                        'source_type' => 'CSR',
                        'source_id' => $r->id,
                        'source_request_code' => $r->request_code,
                        'description' => 'CSR: ' . $r->request_code,
                        'quantity' => 1,
                        'unit_price' => (float) $r->total_amount,
                        'total_price' => (float) $r->total_amount,
                        'update_table' => 'csr_requests',
                        'update_id' => $r->id,
                    ];
                }
            });

        // ── Radiology Requests ────────────────────────────────────────────────
        RadiologyRequest::withoutGlobalScopes()
            ->where('history', 1)
            ->where('status', 'Complete')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['erAdmission', 'opdConsultation', 'admission', 'dialysisSession', 'orSchedule', 'obConsultation', 'drSchedule'])
            ->chunk(100, function ($requests) use (&$items) {
                foreach ($requests as $r) {
                    $pid = $this->resolvePatientId($r);
                    if (!$pid) continue;
                    $items[] = [
                        'tenant_id' => $r->tenant_id,
                        'patient_id' => $pid,
                        'er_admission_id' => $r->er_admission_id,
                        'opd_consultation_id' => $r->opd_consultation_id,
                        'admission_id' => $r->admission_id,
                        'dialysis_session_id' => $r->dialysis_session_id,
                        'or_schedule_id' => $r->or_schedule_id,
                        'ob_consultation_id' => $r->ob_consultation_id,
                        'dr_schedule_id' => $r->dr_schedule_id,
                        'source_type' => 'Radiology',
                        'source_id' => $r->id,
                        'source_request_code' => $r->request_code,
                        'description' => 'Radiology: ' . $r->request_code,
                        'quantity' => 1,
                        'unit_price' => (float) $r->total_amount,
                        'total_price' => (float) $r->total_amount,
                        'update_table' => 'radiology_requests',
                        'update_id' => $r->id,
                    ];
                }
            });

        // ── Procedure Requests ────────────────────────────────────────────────
        ProcedureRequest::withoutGlobalScopes()
            ->where('history', 1)
            ->where('status', 'Complete')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['erAdmission', 'opdConsultation', 'admission', 'dialysisSession', 'orSchedule', 'obConsultation', 'drSchedule'])
            ->chunk(100, function ($requests) use (&$items) {
                foreach ($requests as $r) {
                    $pid = $this->resolvePatientId($r);
                    if (!$pid) continue;
                    $items[] = [
                        'tenant_id' => $r->tenant_id,
                        'patient_id' => $pid,
                        'er_admission_id' => $r->er_admission_id,
                        'opd_consultation_id' => $r->opd_consultation_id,
                        'admission_id' => $r->admission_id,
                        'dialysis_session_id' => $r->dialysis_session_id,
                        'or_schedule_id' => $r->or_schedule_id,
                        'ob_consultation_id' => $r->ob_consultation_id,
                        'dr_schedule_id' => $r->dr_schedule_id,
                        'source_type' => 'Procedure',
                        'source_id' => $r->id,
                        'source_request_code' => $r->request_code,
                        'description' => 'Procedure: ' . $r->request_code,
                        'quantity' => 1,
                        'unit_price' => (float) $r->total_amount,
                        'total_price' => (float) $r->total_amount,
                        'update_table' => 'procedure_requests',
                        'update_id' => $r->id,
                    ];
                }
            });

        // ── OECB Charges ──────────────────────────────────────────────────────
        ErAdmission::withoutGlobalScopes()
            ->where('history', 1)
            ->where('is_oecb', true)
            ->where('status', 'Completed')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['patient', 'oecbEntries'])
            ->chunk(100, function ($admissions) use (&$items) {
                foreach ($admissions as $r) {
                    if (!$r->patient_id) continue;
                    foreach ($r->oecbEntries as $entry) {
                        if (!$entry->core_presentation) continue;
                        $items[] = [
                            'tenant_id' => $r->tenant_id,
                            'patient_id' => $r->patient_id,
                            'er_admission_id' => $r->id,
                            'opd_consultation_id' => null,
                            'admission_id' => null,
                            'dialysis_session_id' => null,
                            'or_schedule_id' => null,
                            'ob_consultation_id' => null,
                            'dr_schedule_id' => null,
                            'source_type' => 'OECB',
                            'source_id' => $entry->id,
                            'source_request_code' => $r->admission_code,
                            'description' => 'OECB: ' . ($entry->core_presentation ?? $r->admission_code) . ' [' . ($entry->oecb_package_code ?? '') . ']',
                            'quantity' => 1,
                            'unit_price' => (float) ($entry->oecb_fixed_fee ?? 0),
                            'total_price' => (float) ($entry->oecb_fixed_fee ?? 0),
                            'update_table' => 'oecb_entries',
                            'update_id' => $entry->id,
                        ];
                    }
                }
            });

        // ── Discharged Admission Room Charges ─────────────────────────────────
        Admission::withoutGlobalScopes()
            ->where('history', 1)
            ->where('status', 'Discharged')
            ->where('billing_status', 'Pending')
            ->whereNull('billing_id')
            ->with(['patient', 'wardRoom', 'icuBed'])
            ->chunk(100, function ($admissions) use (&$items) {
                foreach ($admissions as $r) {
                    if (!$r->patient_id) continue;
                    $days = max(1, (int) ceil($r->admission_date->diffInDays($r->discharge_date)));
                    $rate = $r->wardRoom?->room_rate ?? $r->icuBed?->room_rate ?? 0;
                    $roomName = $r->wardRoom?->name ?? $r->icuBed?->name ?? 'Unknown Room';
                    $items[] = [
                        'tenant_id' => $r->tenant_id,
                        'patient_id' => $r->patient_id,
                        'er_admission_id' => $r->er_admission_id,
                        'opd_consultation_id' => null,
                        'admission_id' => $r->id,
                        'dialysis_session_id' => null,
                        'or_schedule_id' => null,
                        'ob_consultation_id' => null,
                        'dr_schedule_id' => null,
                        'source_type' => 'Admission',
                        'source_id' => $r->id,
                        'source_request_code' => $r->admission_code,
                        'description' => 'Room & Board: ' . $roomName . ' (' . $days . ' days @ ₱' . number_format($rate, 2) . '/day)',
                        'quantity' => $days,
                        'unit_price' => $rate,
                        'total_price' => (float) ($days * $rate),
                        'update_table' => 'admissions',
                        'update_id' => $r->id,
                    ];
                }
            });

        if (empty($items)) {
            $this->info('No unbilled items found.');
            return Command::SUCCESS;
        }

        // ── Group by patient_id and create invoices ───────────────────────────
        $grouped = collect($items)->groupBy('patient_id');
        $created = 0;

        foreach ($grouped as $patientId => $patientItems) {
            $first = $patientItems->first();
            $tid = $first['tenant_id'];

            $last = BillingInvoice::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('id', 'desc')->first();
            $num = $last ? ((int) substr($last->invoice_code, 4)) + 1 : 1;
            $invoiceCode = 'INV-' . str_pad($num, 5, '0', STR_PAD_LEFT);

            $totalAmount = (float) $patientItems->sum('total_price');

            $allErAdmissionIds = $patientItems->pluck('er_admission_id')->filter()->unique()->values();
            $allAdmissionIds = $patientItems->pluck('admission_id')->filter()->unique()->values();
            $allOpdConsultationIds = $patientItems->pluck('opd_consultation_id')->filter()->unique()->values();

            DB::beginTransaction();
            try {
                $invoice = BillingInvoice::create([
                    'tenant_id' => $tid,
                    'invoice_code' => $invoiceCode,
                    'patient_id' => $patientId,
                    'er_admission_id' => $allErAdmissionIds->count() === 1 ? $allErAdmissionIds->first() : null,
                    'admission_id' => $allAdmissionIds->count() === 1 ? $allAdmissionIds->first() : null,
                    'opd_consultation_id' => $allOpdConsultationIds->count() === 1 ? $allOpdConsultationIds->first() : null,
                    'invoice_date' => $now->format('Y-m-d'),
                    'total_amount' => $totalAmount,
                    'discount_type' => null,
                    'discount_value' => 0,
                    'discount_amount' => 0,
                    'paid_amount' => 0,
                    'balance' => $totalAmount,
                    'status' => 'Unpaid',
                    'notes' => 'Auto-created by billing:auto-invoice',
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]);

                foreach ($patientItems as $itm) {
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

                    DB::table($itm['update_table'])
                        ->where('id', $itm['update_id'])
                        ->update([
                            'billing_id' => $invoice->id,
                            'billing_status' => 'Billed',
                            'updated_by' => $uid,
                            'updated_date' => $now,
                        ]);
                }

                DB::commit();
                $this->info("Created {$invoiceCode} for patient #{$patientId} (₱" . number_format($totalAmount, 2) . ')');
                $created++;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed for patient #{$patientId}: " . $e->getMessage());
            }
        }

        $this->info("Done. Created {$created} invoice(s) from " . count($items) . ' billable item(s).');
        return Command::SUCCESS;
    }
}
