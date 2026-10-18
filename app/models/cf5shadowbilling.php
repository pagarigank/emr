<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cf5ShadowBilling extends Model
{
    use ScopedByTenant;
    protected $table = 'cf5_shadow_billings';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'drg_weight' => 'decimal:4',
        'base_rate' => 'decimal:2',
        'hf_amount' => 'decimal:2',
        'pf_amount' => 'decimal:2',
        'total_drg_amount' => 'decimal:2',
        'actual_case_rate_amount' => 'decimal:2',
        'difference_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function philhealthClaim(): BelongsTo
    {
        return $this->belongsTo(PhilhealthClaim::class, 'philhealth_claim_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
