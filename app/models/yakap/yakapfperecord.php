<?php

namespace App\Models\Yakap;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use App\Models\Opd\OpdConsultation;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YakapFpeRecord extends Model
{
    use ScopedByTenant;
    protected $table = 'yakap_fpe_records';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'fpe_datetime' => 'datetime',
        'height_cm' => 'decimal:1',
        'weight_kg' => 'decimal:1',
        'bmi' => 'decimal:1',
        'waist_circumference_cm' => 'decimal:1',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function empanelment(): BelongsTo
    {
        return $this->belongsTo(YakapEmpanelment::class, 'empanelment_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(OpdConsultation::class, 'consultation_id');
    }

    public function physician(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'physician_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
