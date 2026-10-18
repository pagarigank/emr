<?php

namespace App\Models\Yakap;

use App\Models\Patient;
use App\Models\PhilhealthMember;
use App\Models\HealthCareProfessional;
use App\Models\Opd\OpdClinic;
use App\Models\Opd\OpdConsultation;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YakapEmpanelment extends Model
{
    use ScopedByTenant;
    protected $table = 'yakap_empanelments';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'empanelment_date' => 'date',
        'yes_signed_date' => 'date',
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

    public function opdClinic(): BelongsTo
    {
        return $this->belongsTo(OpdClinic::class, 'opd_clinic_id');
    }

    public function fpeConsultation(): BelongsTo
    {
        return $this->belongsTo(OpdConsultation::class, 'fpe_consultation_id');
    }

    public function fpeRecords(): HasMany
    {
        return $this->hasMany(YakapFpeRecord::class, 'empanelment_id');
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(YakapScreening::class, 'empanelment_id');
    }

    public function capitationPayments(): HasMany
    {
        return $this->hasMany(YakapCapitationPayment::class, 'empanelment_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
