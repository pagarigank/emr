<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\UserPermission;

/**
 * User Model
 *
 * Roles:
 *   superadmin   — tenant_id IS NULL, unrestricted access to all tenants
 *   tenant_admin — tenant_id set, restricted to their own tenant
 *   staff        — tenant_id set, limited feature access within tenant
 *   doctor       — tenant_id set, linked to health_care_professional_id
 *
 * Permissions are derived from the user's role via the `permissions_list`
 * accessor, which reads from the `role_permissions` pivot table.
 *
 * Follows project standard: no timestamps(), history global scope, audit columns.
 */
class User extends Authenticatable implements JWTSubject
{
    use ScopedByTenant;
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'username',
        'full_name',
        'email',
        'password',
        'role',
        'is_active',
        'history',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password'     => 'hashed',
            'is_active'    => 'boolean',
            'created_date' => 'datetime',
            'updated_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    // ── Role helpers ──────────────────────────────────────────────────────────

    /**
     * Check if user has superadmin role.
     *
     * @deprecated Use permission-based checks via hasPermission() where possible.
     *             Kept for: (1) core RBAC permission resolution, (2) tenant-scoping
     *             in Livewire components, (3) superadmin-only feature gating
     *             (e.g. creating superadmin users, cross-tenant access).
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * @deprecated Use hasPermission() or check the role string directly.
     */
    public function isTenantAdmin(): bool
    {
        return $this->role === 'tenant_admin';
    }

    /**
     * @deprecated Use hasPermission() or check the role string directly.
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * @deprecated Use hasPermission() or check the role string directly.
     */
    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return in_array($permission, $this->permissions_list, true);
    }

    // ── Virtual role accessor (backward compat) ───────────────────────────────

    public function getRoleAttribute($value)
    {
        if ($this->role_id && $this->relationLoaded('roleRelation') && $this->roleRelation) {
            return $this->roleRelation->name;
        }
        return $value;
    }

    // ── Permissions (derived from role) ───────────────────────────────────────

    public function getPermissionsListAttribute(): array
    {
        if (!$this->is_active) {
            return [];
        }

        if ($this->isSuperAdmin()) {
            return Permission::where('is_active', true)->pluck('name')->toArray();
        }

        return Cache::remember("user_permissions_{$this->id}", 3600, function () {
            $rolePerms = Permission::where('is_active', true)
                ->whereExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('role_permissions')
                        ->whereColumn('role_permissions.permission_id', 'permissions.id')
                        ->where(function ($w) {
                            $w->where('role_permissions.role', $this->role)
                              ->orWhere('role_permissions.role_id', $this->role_id);
                        });
                })
                ->pluck('name')
                ->toArray();

            $overrides = UserPermission::where('user_id', $this->id)->get();

            foreach ($overrides as $override) {
                $permName = $override->permission->name ?? null;
                if (!$permName) continue;

                if ($override->type === 'allow') {
                    $rolePerms[] = $permName;
                } elseif ($override->type === 'deny') {
                    $rolePerms = array_filter($rolePerms, fn ($n) => $n !== $permName);
                }
            }

            return array_values(array_unique($rolePerms));
        });
    }

    public function flushPermissionCache(): void
    {
        Cache::forget("user_permissions_{$this->id}");
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'user_id');
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function healthCareProfessional(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'health_care_professional_id');
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // ── JWTSubject contract ───────────────────────────────────────────────────

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'tenant_id' => $this->tenant_id,
            'role'      => $this->role,
            'name'      => $this->full_name,
            'email'     => $this->email,
            'health_care_professional_id' => $this->health_care_professional_id,
            'permissions' => $this->permissions_list,
        ];
    }
}
