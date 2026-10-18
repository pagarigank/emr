<?php

namespace App\Models;

use App\Models\Er\ErAdmission;
use App\Models\Icu\IcuBed;
use App\Models\Nursing\NursingCareNote;
use App\Models\Or\OrSchedule;
use App\Models\Nursing\NursingMedicationAdministration;
use App\Models\Nursing\NursingVital;
use App\Models\Ward\WardRoom;
use App\Models\Delivery\DeliverySchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Admission extends Model
{
    use ScopedByTenant;
    protected $table = 'admissions';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'admission_date' => 'datetime',
        'discharge_date' => 'datetime',
        'disposition_datetime' => 'datetime',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function erAdmission(): BelongsTo
    {
        return $this->belongsTo(ErAdmission::class, 'er_admission_id');
    }

    public function attendingPhysician(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'attending_physician_id');
    }

    public function admittingPhysician(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'admitting_physician_id');
    }

    public function wardRoom(): BelongsTo
    {
        return $this->belongsTo(WardRoom::class, 'ward_room_id');
    }

    public function icuBed(): BelongsTo
    {
        return $this->belongsTo(IcuBed::class, 'icu_bed_id');
    }

    public function nursingVitals(): HasMany
    {
        return $this->hasMany(NursingVital::class, 'admission_id');
    }

    public function nursingCareNotes(): HasMany
    {
        return $this->hasMany(NursingCareNote::class, 'admission_id');
    }

    public function medicationAdministrations(): HasMany
    {
        return $this->hasMany(NursingMedicationAdministration::class, 'admission_id');
    }

    // Orders linked directly to this admission (for nursing/ward ordering)
    public function labRequests(): HasMany
    {
        return $this->hasMany(\App\Models\LabRequest::class, 'admission_id');
    }

    public function pharmacyRequests(): HasMany
    {
        return $this->hasMany(\App\Models\Pharmacy\PharmacyRequest::class, 'admission_id');
    }

    public function csrRequests(): HasMany
    {
        return $this->hasMany(\App\Models\CsrRequest::class, 'admission_id');
    }

    public function radiologyRequests(): HasMany
    {
        return $this->hasMany(\App\Models\Radiology\RadiologyRequest::class, 'admission_id');
    }

    public function procedureRequests(): HasMany
    {
        return $this->hasMany(\App\Models\Procedures\ProcedureRequest::class, 'admission_id');
    }

    public function orSchedules(): HasMany
    {
        return $this->hasMany(OrSchedule::class, 'admission_id');
    }

    public function drSchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class, 'admission_id');
    }

    public function billingInvoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Billing\BillingInvoice::class, 'billing_id');
    }

    public function roomHistories(): HasMany
    {
        return $this->hasMany(AdmissionRoomHistory::class, 'admission_id');
    }

    /**
     * Auto-compute the number of days stayed to date (or through discharge date).
     * Follows hospital calendar-day standard:
     * - Formula: calendar days between startOfDay of admission_date and end date + 1 day
     * - If discharged before 12:00 PM (noon cutoff) on a multi-day stay, subtract 1 day
     * - Minimum 1 day
     */
    public function getDaysStayedAttribute(): int
    {
        if (!$this->admission_date) {
            return 0;
        }

        $end = $this->discharge_date ?? now();
        $calendarDays = (int) $this->admission_date->copy()->startOfDay()->diffInDays($end->copy()->startOfDay());
        $days = max(1, $calendarDays + 1);

        if ($this->discharge_date && $calendarDays > 0 && (int) $this->discharge_date->format('H') < 12) {
            $days = max(1, $days - 1);
        }

        return $days;
    }

    /**
     * Compute total accrued room & board charges to date.
     */
    public function computeAccruedRoomCharges(): float
    {
        $histories = $this->roomHistories()
            ->where('history', 1)
            ->orderBy('assigned_at')
            ->get();

        if ($histories->isEmpty()) {
            $rate = (float) ($this->wardRoom?->room_rate ?? $this->icuBed?->room_rate ?? 0);
            return (float) ($this->days_stayed * $rate);
        }

        $total = 0.0;
        foreach ($histories as $h) {
            $total += (float) $h->total_charge;
        }

        return (float) $total;
    }
}
