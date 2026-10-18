<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;

class ClaimProcedure extends Model
{
    use ScopedByTenant;
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'procedure_date' => 'date',
        'is_active' => 'boolean',
        'history' => 'integer',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function philhealthClaim()
    {
        return $this->belongsTo(PhilhealthClaim::class, 'philhealth_claim_id');
    }

    public function rvsCode()
    {
        return $this->belongsTo(RvsCode::class, 'rvs_code', 'code');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('history', fn ($q) => $q->where('history', 1));
    }
}
