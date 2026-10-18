<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tenant (Company) Model
 *
 * Root entity — has no tenant_id itself.
 * Superadmin manages all Tenant records.
 */
class Tenant extends Model
{
    // No Laravel auto-timestamps — we use created_date / updated_date
    public $timestamps = false;

    protected $fillable = [
        'tenant_code',
        'tenant_name',
        'address',
        'city',
        'contact_number',
        'philhealth_hci_code',
        'philhealth_accreditation_no',
        'accreditation_date',
        'email',
        'logo_path',
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
     * Global scope: always show only current (non-archived) tenants.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }
}
