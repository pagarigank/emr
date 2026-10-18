<?php

namespace App\Models\Yakap;

use App\Models\Patient;
use App\Models\HealthCareProfessional;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YakapScreening extends Model
{
    use ScopedByTenant;
    protected $table = 'yakap_screenings';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'screening_date' => 'date',
        'next_screening_due' => 'date',
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

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'performed_by');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
