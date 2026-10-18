<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimDocument extends Model
{
    use ScopedByTenant;
    protected $table = 'claim_documents';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_date' => 'datetime',
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
