<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'display_name',
        'module',
        'guard_name',
        'description',
        'is_active',
        'history',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_date' => 'datetime',
            'updated_date' => 'datetime',
        ];
    }
}
