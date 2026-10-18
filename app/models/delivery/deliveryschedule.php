<?php

namespace App\Models\Delivery;

use App\Models\Admission;
use App\Models\CsrRequest;
use App\Models\Er\ErAdmission;
use App\Models\HealthCareProfessional;
use App\Models\LabRequest;
use App\Models\Ob\ObConsultation;
use App\Models\Patient;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Radiology\RadiologyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySchedule extends Model
{
    protected $table = 'dr_schedules';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_date' => 'date',
        'lmp_date' => 'date',
        'edc_date' => 'date',
        'baby_weight' => 'decimal:2',
        'baby_length' => 'decimal:1',
        'head_circumference' => 'decimal:1',
        'gravida' => 'integer',
        'para' => 'integer',
        'full_term' => 'integer',
        'preterm' => 'integer',
        'abortion' => 'integer',
        'living_children' => 'integer',
        'apgar_score_1min' => 'integer',
        'apgar_score_5min' => 'integer',
        'apgar_score_10min' => 'integer',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'disposition_datetime' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function drRoom(): BelongsTo { return $this->belongsTo(DeliveryRoom::class, 'dr_room_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function erAdmission(): BelongsTo { return $this->belongsTo(ErAdmission::class, 'er_admission_id'); }
    public function admission(): BelongsTo { return $this->belongsTo(Admission::class, 'admission_id'); }
    public function obConsultation(): BelongsTo { return $this->belongsTo(ObConsultation::class, 'ob_consultation_id'); }
    public function attendingPhysician(): BelongsTo { return $this->belongsTo(HealthCareProfessional::class, 'attending_physician_id'); }
    public function pediatrician(): BelongsTo { return $this->belongsTo(HealthCareProfessional::class, 'pediatrician_id'); }
    public function anesthesiologist(): BelongsTo { return $this->belongsTo(HealthCareProfessional::class, 'anesthesiologist_id'); }
    public function midwife(): BelongsTo { return $this->belongsTo(HealthCareProfessional::class, 'midwife_id'); }

    public function labRequests(): HasMany { return $this->hasMany(LabRequest::class, 'dr_schedule_id'); }
    public function csrRequests(): HasMany { return $this->hasMany(CsrRequest::class, 'dr_schedule_id'); }
    public function radiologyRequests(): HasMany { return $this->hasMany(RadiologyRequest::class, 'dr_schedule_id'); }
    public function pharmacyRequests(): HasMany { return $this->hasMany(PharmacyRequest::class, 'dr_schedule_id'); }
    public function procedureRequests(): HasMany { return $this->hasMany(ProcedureRequest::class, 'dr_schedule_id'); }
}
