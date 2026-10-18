<?php

namespace App\Models\Ob;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\LabRequest;
use App\Models\CsrRequest;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Or\OrSchedule;
use App\Models\Billing\BillingInvoice;
use App\Models\Delivery\DeliverySchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObConsultation extends Model
{
    use ScopedByTenant;
    protected $table = 'ob_consultations';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'lmp_date' => 'date',
        'edc_date' => 'date',
        'disposition_datetime' => 'datetime',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function attendingPhysician(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'attending_physician_id');
    }

    public function visitType(): BelongsTo
    {
        return $this->belongsTo(ObVisitType::class, 'visit_type_id');
    }

    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class, 'ob_consultation_id');
    }

    public function csrRequests(): HasMany
    {
        return $this->hasMany(CsrRequest::class, 'ob_consultation_id');
    }

    public function radiologyRequests(): HasMany
    {
        return $this->hasMany(RadiologyRequest::class, 'ob_consultation_id');
    }

    public function pharmacyRequests(): HasMany
    {
        return $this->hasMany(PharmacyRequest::class, 'ob_consultation_id');
    }

    public function procedureRequests(): HasMany
    {
        return $this->hasMany(ProcedureRequest::class, 'ob_consultation_id');
    }

    public function orSchedules(): HasMany
    {
        return $this->hasMany(OrSchedule::class, 'ob_consultation_id');
    }

    public function billingInvoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class, 'ob_consultation_id');
    }

    public function drSchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class, 'ob_consultation_id');
    }
}
