<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CivilStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code', 'name', 'is_active', 'history',
        'created_by', 'created_date', 'updated_by', 'updated_date',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }
}
