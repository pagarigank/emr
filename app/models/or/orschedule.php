<?php

namespace App\Models\Or;

use App\Models\CsrRequest;
use App\Models\Er\ErAdmission;
use App\Models\HealthCareProfessional;
use App\Models\LabRequest;
use App\Models\Patient;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Radiology\RadiologyRequest;
use App\Models\Ob\ObConsultation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrSchedule extends Model
{
    use ScopedByTenant;
    protected $table = 'or_schedules';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'disposition_datetime' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function orRoom(): BelongsTo { return $this->belongsTo(OrRoom::class, 'or_room_id'); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function erAdmission(): BelongsTo { return $this->belongsTo(ErAdmission::class, 'er_admission_id'); }
    public function admission(): BelongsTo { return $this->belongsTo(\App\Models\Admission::class, 'admission_id'); }
    public function obConsultation(): BelongsTo { return $this->belongsTo(ObConsultation::class, 'ob_consultation_id'); }
    public function surgeon(): BelongsTo { return $this->belongsTo(HealthCareProfessional::class, 'surgeon_id'); }
    public function anesthesiologist(): BelongsTo { return $this->belongsTo(HealthCareProfessional::class, 'anesthesiologist_id'); }

    public function labRequests(): HasMany { return $this->hasMany(LabRequest::class, 'or_schedule_id'); }
    public function csrRequests(): HasMany { return $this->hasMany(CsrRequest::class, 'or_schedule_id'); }
    public function radiologyRequests(): HasMany { return $this->hasMany(RadiologyRequest::class, 'or_schedule_id'); }
    public function pharmacyRequests(): HasMany { return $this->hasMany(PharmacyRequest::class, 'or_schedule_id'); }
    public function procedureRequests(): HasMany { return $this->hasMany(ProcedureRequest::class, 'or_schedule_id'); }
}
