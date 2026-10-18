<?php

namespace App\Models\Pharmacy;

use App\Models\Department;
use App\Models\HealthCareProfessional;
use App\Models\Er\ErAdmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyRequest extends Model
{
    use ScopedByTenant;
    protected $table = 'pharmacy_requests';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function patient(): BelongsTo { return $this->belongsTo(\App\Models\Patient::class, 'patient_id'); }
    public function items(): HasMany { return $this->hasMany(PharmacyRequestItem::class, 'pharmacy_request_id'); }
    public function requestingDepartment(): BelongsTo { return $this->belongsTo(Department::class, 'requesting_department_id'); }
    public function requestingDoctor(): BelongsTo { return $this->belongsTo(HealthCareProfessional::class, 'requesting_doctor_id'); }
    public function erAdmission(): BelongsTo { return $this->belongsTo(ErAdmission::class, 'er_admission_id'); }
    public function opdConsultation(): BelongsTo { return $this->belongsTo(\App\Models\Opd\OpdConsultation::class, 'opd_consultation_id'); }
    public function admission(): BelongsTo { return $this->belongsTo(\App\Models\Admission::class, 'admission_id'); }
    public function dialysisSession(): BelongsTo { return $this->belongsTo(\App\Models\RenalCare\DialysisSession::class, 'dialysis_session_id'); }
    public function orSchedule(): BelongsTo { return $this->belongsTo(\App\Models\Or\OrSchedule::class, 'or_schedule_id'); }
    public function obConsultation(): BelongsTo { return $this->belongsTo(\App\Models\Ob\ObConsultation::class, 'ob_consultation_id'); }
    public function drSchedule(): BelongsTo { return $this->belongsTo(\App\Models\Delivery\DeliverySchedule::class, 'dr_schedule_id'); }

    public function getPatientNameAttribute()
    {
        if ($this->opdConsultation && $this->opdConsultation->patient) {
            return $this->opdConsultation->patient->full_name;
        }
        if ($this->erAdmission && $this->erAdmission->patient) {
            return $this->erAdmission->patient->full_name;
        }
        if ($this->admission && $this->admission->patient) {
            return $this->admission->patient->full_name;
        }
        if ($this->dialysisSession && $this->dialysisSession->patient) {
            return $this->dialysisSession->patient->full_name;
        }
        if ($this->orSchedule && $this->orSchedule->patient) {
            return $this->orSchedule->patient->full_name;
        }
        if ($this->drSchedule && $this->drSchedule->patient) {
            return $this->drSchedule->patient->full_name;
        }
        if ($this->obConsultation && $this->obConsultation->patient) {
            return $this->obConsultation->patient->full_name;
        }
        if ($this->patient) {
            return $this->patient->full_name;
        }
        return $this->requesting_person ?? '-';
    }

    public function getDepartmentNameAttribute()
    {
        if ($this->opdConsultation && $this->opdConsultation->opdClinic) {
            return 'OPD - ' . $this->opdConsultation->opdClinic->name;
        }
        if ($this->requestingDepartment) {
            return $this->requestingDepartment->dept_name;
        }
        return '—';
    }

    public function resolvePatientId(): ?int
    {
        foreach (['opdConsultation', 'erAdmission', 'admission', 'dialysisSession', 'orSchedule', 'drSchedule', 'obConsultation', 'patient'] as $rel) {
            $model = $this->{$rel};
            if ($model && method_exists($model, 'getAttribute') && $model->patient_id) {
                return (int) $model->patient_id;
            }
            if ($model && isset($model->patient) && $model->patient) {
                return (int) $model->patient->id;
            }
        }
        return null;
    }

    public static function postChargeToLedger(self $request, float $amount, int $userId): void
    {
        if ($amount <= 0) return;
        $patientId = $request->resolvePatientId();
        if (!$patientId) return;

        $now = now();

        $existing = \App\Models\Billing\PatientAccountEntry::withoutGlobalScopes()
            ->where('tenant_id', $request->tenant_id)
            ->where('source_type', 'Pharmacy')
            ->where('source_id', $request->id)
            ->where('entry_type', 'charge')
            ->where('history', 1)
            ->first();

        if ($existing) {
            if ((float) $existing->debit !== $amount) {
                $existing->update([
                    'debit'        => $amount,
                    'description'  => 'Pharmacy: ' . $request->request_code,
                    'updated_by'   => $userId,
                    'updated_date' => $now,
                ]);
                $existing->recalculateBalance();
            }
            return;
        }

        \App\Models\Billing\PatientAccountEntry::postCharge([
            'tenant_id'           => $request->tenant_id,
            'patient_id'          => $patientId,
            'admission_id'        => $request->admission_id,
            'er_admission_id'     => $request->er_admission_id,
            'opd_consultation_id' => $request->opd_consultation_id,
            'or_schedule_id'      => $request->or_schedule_id,
            'dr_schedule_id'      => $request->dr_schedule_id,
            'ob_consultation_id'  => $request->ob_consultation_id,
            'dialysis_session_id' => $request->dialysis_session_id,
            'entry_type'          => 'charge',
            'source_type'         => 'Pharmacy',
            'source_id'           => $request->id,
            'source_code'         => $request->request_code,
            'debit'               => $amount,
            'credit'              => 0,
            'description'         => 'Pharmacy: ' . $request->request_code,
            'billing_invoice_id'  => null,
            'created_by'          => $userId,
            'created_date'        => $now,
            'updated_by'          => $userId,
            'updated_date'        => $now,
        ]);
    }
}
