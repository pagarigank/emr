<?php

namespace App\Models\Er;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\LabRequest;
use App\Models\CsrRequest;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Admission;
use App\Models\Billing\BillingInvoice;
use App\Models\Oecb\OecbEntry;
use App\Models\Or\OrSchedule;
use App\Models\Delivery\DeliverySchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ErAdmission extends Model
{
    use ScopedByTenant;
    protected $table = 'er_admissions';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'admission_datetime' => 'datetime',
        'disposition_datetime' => 'datetime',
        'is_oecb' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('history', 1);
        });
    }

    public function triageRecord()
    {
        return $this->belongsTo(ErTriageRecord::class, 'er_triage_record_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function bed()
    {
        return $this->belongsTo(ErBed::class, 'er_bed_id');
    }

    public function erBed()
    {
        return $this->belongsTo(ErBed::class, 'er_bed_id');
    }

    public function attendingPhysician()
    {
        return $this->belongsTo(HealthCareProfessional::class, 'attending_physician_id');
    }

    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class, 'er_admission_id');
    }

    public function csrRequests(): HasMany
    {
        return $this->hasMany(CsrRequest::class, 'er_admission_id');
    }

    public function radiologyRequests(): HasMany
    {
        return $this->hasMany(RadiologyRequest::class, 'er_admission_id');
    }

    public function pharmacyRequests(): HasMany
    {
        return $this->hasMany(PharmacyRequest::class, 'er_admission_id');
    }

    public function procedureRequests(): HasMany
    {
        return $this->hasMany(ProcedureRequest::class, 'er_admission_id');
    }

    public function oecbEntries(): HasMany
    {
        return $this->hasMany(OecbEntry::class, 'er_admission_id');
    }

    public function admission(): HasOne
    {
        return $this->hasOne(Admission::class, 'er_admission_id');
    }

    public function billingInvoices(): HasMany
    {
        return $this->hasMany(BillingInvoice::class, 'er_admission_id');
    }

    public function orSchedules(): HasMany
    {
        return $this->hasMany(OrSchedule::class, 'er_admission_id');
    }

    public function drSchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class, 'er_admission_id');
    }
}
