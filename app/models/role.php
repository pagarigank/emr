<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system',
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
            'is_system'    => 'boolean',
            'is_active'    => 'boolean',
            'created_date' => 'datetime',
            'updated_date' => 'datetime',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->where('history', 1);
    }
}
