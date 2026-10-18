<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClaimBatch extends Model
{
    protected $table = 'claim_batches';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'total_claims' => 'integer',
        'total_amount' => 'decimal:2',
        'submission_date' => 'datetime',
        'acknowledgement_date' => 'datetime',
        'ecs_uploaded_at' => 'datetime',
        'cloud_uploaded_at' => 'datetime',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function claims(): HasMany
    {
        return $this->hasMany(PhilhealthClaim::class, 'claim_batch_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
