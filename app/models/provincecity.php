<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProvinceCity extends Model
{
    protected $table = 'provinces_cities';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'type',
        'is_active',
        'history',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class, 'province_city_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProvinceCity::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProvinceCity::class, 'parent_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }
}
