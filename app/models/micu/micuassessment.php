<?php

namespace App\Models\MICU;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MicuAssessment extends Model
{
    use ScopedByTenant;
    protected $table = 'micu_assessments';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['assessed_at' => 'datetime', 'is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime'];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admission::class, 'admission_id');
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\HealthCareProfessional::class, 'assessed_by');
    }

    public function values(): HasMany
    {
        return $this->hasMany(MicuAssessmentValue::class, 'micu_assessment_id');
    }
}
