<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhilhealthCaseRate extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function rvsCode(): BelongsTo
    {
        return $this->belongsTo(RvsCode::class, 'rvs_code_id');
    }

    public function icd10Code(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'icd10_code_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(PhilhealthClaim::class, 'case_rate_id');
    }

    public function secondaryClaims(): HasMany
    {
        return $this->hasMany(PhilhealthClaim::class, 'second_case_rate_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('history', fn ($q) => $q->where('history', 1));
    }
}
