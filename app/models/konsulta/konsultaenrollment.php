<?php

namespace App\Models\Konsulta;

use App\Models\Patient;
use App\Models\PhilhealthMember;
use App\Models\HealthCareProfessional;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KonsultaEnrollment extends Model
{
    use ScopedByTenant;
    protected $table = 'konsulta_enrollments';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'enrollment_date' => 'date',
        'expiry_date' => 'date',
        'fpe_completed' => 'boolean',
        'fpe_completed_at' => 'datetime',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function philhealthMember(): BelongsTo
    {
        return $this->belongsTo(PhilhealthMember::class, 'philhealth_member_id');
    }

    public function primaryCareProvider(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'primary_care_provider_id');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(KonsultaConsultation::class, 'enrollment_id');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(KonsultaPrescription::class, 'enrollment_id');
    }

    public function labReferrals(): HasMany
    {
        return $this->hasMany(KonsultaLabReferral::class, 'enrollment_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
