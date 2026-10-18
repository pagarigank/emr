<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimItem extends Model
{
    use ScopedByTenant;
    protected $table = 'claim_items';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'claimed_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function philhealthClaim(): BelongsTo
    {
        return $this->belongsTo(PhilhealthClaim::class, 'philhealth_claim_id');
    }

    public function healthCareProfessional(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'health_care_professional_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
