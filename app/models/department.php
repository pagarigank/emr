<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;

class Department extends Model
{
    use ScopedByTenant;
    // We use explicit audit timestamps — not Laravel's default
    public $timestamps = false;

    protected $fillable = [
        'dept_code',
        'dept_name',
        'description',
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

    /**
     * Boot: automatically filter to current (non-deleted) records.
     * history=1 means the record is the latest/active version.
     * history=0 means it has been logically deleted.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }
}
