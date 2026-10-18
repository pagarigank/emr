<?php

namespace App\Models\Nursing;

use App\Models\Admission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingVital extends Model
{
    use ScopedByTenant;
    protected $table = 'nursing_vitals';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'recorded_at' => 'datetime',
        'vital_temperature' => 'decimal:1',
        'vital_heart_rate' => 'integer',
        'vital_respiratory_rate' => 'integer',
        'vital_bp_systolic' => 'integer',
        'vital_bp_diastolic' => 'integer',
        'vital_o2_saturation' => 'decimal:2',
        'vital_pain_score' => 'integer',
        'blood_sugar' => 'decimal:2',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }
}
