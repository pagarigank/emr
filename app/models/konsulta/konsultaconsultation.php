<?php

namespace App\Models\Konsulta;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\Icd10Code;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KonsultaConsultation extends Model
{
    use ScopedByTenant;
    protected $table = 'konsulta_consultations';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'consultation_date' => 'datetime',
        'temperature' => 'float',
        'heart_rate' => 'integer',
        'respiratory_rate' => 'integer',
        'weight' => 'float',
        'height' => 'float',
        'is_fpe' => 'boolean',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(KonsultaEnrollment::class, 'enrollment_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'provider_id');
    }

    public function icd10Code(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'icd10_code_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(KonsultaPrescription::class, 'consultation_id');
    }

    public function labReferrals(): HasMany
    {
        return $this->hasMany(KonsultaLabReferral::class, 'consultation_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
