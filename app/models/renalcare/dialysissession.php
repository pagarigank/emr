<?php

namespace App\Models\RenalCare;

use App\Models\CsrRequest;
use App\Models\LabRequest;
use App\Models\Patient;
use App\Models\Pharmacy\PharmacyRequest;
use App\Models\Procedures\ProcedureRequest;
use App\Models\Radiology\RadiologyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DialysisSession extends Model
{
    use ScopedByTenant;
    protected $table = 'dialysis_sessions';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'session_date' => 'date',
        'pre_weight' => 'decimal:2',
        'post_weight' => 'decimal:2',
        'dry_weight' => 'decimal:2',
        'uf_goal_liters' => 'decimal:2',
        'uf_actual_liters' => 'decimal:2',
        'pre_temp' => 'decimal:1',
        'post_temp' => 'decimal:1',
        'disposition_datetime' => 'datetime',
    ];

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class, 'patient_id'); }
    public function dialysisType(): BelongsTo { return $this->belongsTo(DialysisType::class, 'dialysis_type_id'); }
    public function dialysisMachine(): BelongsTo { return $this->belongsTo(DialysisMachine::class, 'dialysis_machine_id'); }
    public function attendingPhysician(): BelongsTo { return $this->belongsTo(\App\Models\HealthCareProfessional::class, 'attending_physician_id'); }
    public function dialysisNurse(): BelongsTo { return $this->belongsTo(\App\Models\User::class, 'dialysis_nurse_id'); }
    public function admission(): BelongsTo { return $this->belongsTo(\App\Models\Admission::class, 'admission_id'); }
    public function erAdmission(): BelongsTo { return $this->belongsTo(\App\Models\Er\ErAdmission::class, 'er_admission_id'); }
    public function procedureRequest(): BelongsTo { return $this->belongsTo(ProcedureRequest::class, 'procedure_request_id'); }

    public function labRequests(): HasMany { return $this->hasMany(LabRequest::class, 'dialysis_session_id'); }
    public function csrRequests(): HasMany { return $this->hasMany(CsrRequest::class, 'dialysis_session_id'); }
    public function radiologyRequests(): HasMany { return $this->hasMany(RadiologyRequest::class, 'dialysis_session_id'); }
    public function pharmacyRequests(): HasMany { return $this->hasMany(PharmacyRequest::class, 'dialysis_session_id'); }
    public function procedureRequests(): HasMany { return $this->hasMany(ProcedureRequest::class, 'dialysis_session_id'); }
}
