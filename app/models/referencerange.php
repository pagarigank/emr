<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceRange extends Model
{
    protected $table = 'reference_ranges';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime', 'age_min_years' => 'float', 'age_max_years' => 'float', 'min_value' => 'float', 'max_value' => 'float'];

    public function labTest(): BelongsTo { return $this->belongsTo(LabTest::class, 'lab_test_id'); }

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('reference_ranges.history', 1)); }
}
