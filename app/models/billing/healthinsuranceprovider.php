<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HealthInsuranceProvider extends Model
{
    protected $table = 'health_insurance_providers';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }
}
