<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhilhealthMember extends Model
{
    use ScopedByTenant;
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'employer_signature_date' => 'datetime',
    ];

    public function claims(): HasMany
    {
        return $this->hasMany(PhilhealthClaim::class, 'philhealth_member_id');
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'philhealth_member_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', [
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]));
    }

    protected static function booted(): void
    {
        static::addGlobalScope('history', fn ($q) => $q->where('history', 1));
    }
}
