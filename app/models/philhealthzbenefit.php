<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PhilhealthZBenefit extends Model
{
    use ScopedByTenant;
    use HasFactory;

    protected $table = 'philhealth_z_benefits';
    
    public $timestamps = false; // We use custom created_date / updated_date

    protected $fillable = [
        'z_benefit_code',
        'z_benefit_name',
        'pre_authorization_code',
        'tranche_number',
        'tranche_amount',
        'status',
        'is_active',
        'history',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'history' => 'boolean',
        'tranche_number' => 'integer',
        'tranche_amount' => 'decimal:2',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('current', function (Builder $builder) {
            $builder->where('history', 1);
        });
    }

    public function philhealthClaim(): BelongsTo
    {
        return $this->belongsTo(PhilhealthClaim::class, 'philhealth_claim_id');
    }
}
