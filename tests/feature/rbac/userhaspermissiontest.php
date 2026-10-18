<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserHasPermissionTest extends TestCase
{
    private array $permIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh', ['--path' => [
            'database/migrations/0001_01_01_000000_create_users_table.php',
            'database/migrations/2026_06_30_000001_create_permissions_tables.php',
            'database/migrations/2026_06_30_000002_create_user_permissions_table.php',
            'database/migrations/2026_06_30_000004_create_roles_table.php',
            'database/migrations/2026_06_30_000005_add_role_id_to_users_and_role_permissions.php',
        ]]);

        // Roles are pre-seeded by migration: superadmin, tenant_admin, staff, doctor

        // Create permissions
        foreach ([
            'dashboard.view', 'patient.view', 'patient.create',
            'admin.users', 'admin.masters', 'billing.view',
        ] as $name) {
            $perm = Permission::create([
                'name' => $name, 'display_name' => ucfirst($name), 'module' => explode('.', $name)[0],
                'guard_name' => 'web', 'is_active' => true, 'history' => 1, 'created_date' => now(),
            ]);
            $this->permIds[$name] = $perm->id;
        }
    }

    private function createRoleWithPerms(string $name, string $displayName, array $permNames): Role
    {
        $role = Role::firstOrCreate(
            ['name' => $name],
            ['display_name' => $displayName, 'is_system' => true, 'is_active' => true, 'history' => 1, 'created_date' => now()]
        );

        foreach ($permNames as $permName) {
            if (isset($this->permIds[$permName])) {
                DB::table('role_permissions')->insert([
                    'role' => $name, 'role_id' => $role->id,
                    'permission_id' => $this->permIds[$permName],
                    'tenant_id' => null, 'created_date' => now(),
                ]);
            }
        }

        return $role;
    }

    private function createUser(string $roleName, ?Role $role = null): User
    {
        if (!$role) {
            $role = Role::where('name', $roleName)->first();
        }
        return User::factory()->create([
            'role' => $roleName, 'role_id' => $role->id, 'is_active' => true, 'history' => 1,
        ]);
    }

    public function test_superadmin_has_all_permissions(): void
    {
        $role = $this->createRoleWithPerms('superadmin', 'Super Admin', [
            'dashboard.view', 'patient.view', 'admin.users',
        ]);
        // superadmin gets ALL permissions regardless of role_permissions
        $user = $this->createUser('superadmin', $role);

        $this->assertTrue($user->hasPermission('dashboard.view'));
        $this->assertTrue($user->hasPermission('patient.view'));
        $this->assertTrue($user->hasPermission('admin.users'));
        // Even permissions NOT in role_permissions are granted to superadmin
        $this->assertTrue($user->hasPermission('nonexistent.perm'));
    }

    public function test_tenant_admin_has_assigned_permissions(): void
    {
        $this->createRoleWithPerms('tenant_admin', 'Tenant Admin', [
            'dashboard.view', 'patient.view', 'patient.create', 'admin.users', 'admin.masters', 'billing.view',
        ]);
        $user = $this->createUser('tenant_admin');

        $this->assertTrue($user->hasPermission('dashboard.view'));
        $this->assertTrue($user->hasPermission('admin.users'));
        $this->assertFalse($user->hasPermission('nonexistent.perm'));
    }

    public function test_staff_has_limited_permissions(): void
    {
        $this->createRoleWithPerms('staff', 'Staff', [
            'dashboard.view', 'patient.view',
        ]);
        $user = $this->createUser('staff');

        $this->assertTrue($user->hasPermission('dashboard.view'));
        $this->assertTrue($user->hasPermission('patient.view'));
        $this->assertFalse($user->hasPermission('admin.users'));
        $this->assertFalse($user->hasPermission('admin.masters'));
    }

    public function test_doctor_has_clinical_permissions(): void
    {
        $this->createRoleWithPerms('doctor', 'Doctor', [
            'dashboard.view', 'patient.view', 'patient.create',
        ]);
        $user = $this->createUser('doctor');

        $this->assertTrue($user->hasPermission('dashboard.view'));
        $this->assertTrue($user->hasPermission('patient.view'));
        $this->assertTrue($user->hasPermission('patient.create'));
        $this->assertFalse($user->hasPermission('admin.users'));
        $this->assertFalse($user->hasPermission('billing.view'));
    }

    public function test_permissions_are_cached(): void
    {
        $this->createRoleWithPerms('tenant_admin', 'Tenant Admin', [
            'dashboard.view', 'patient.view',
        ]);
        $user = $this->createUser('tenant_admin');

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(['dashboard.view', 'patient.view']);

        $result = $user->permissions_list;
        $this->assertContains('dashboard.view', $result);
    }

    public function test_flush_permission_cache_clears_cache(): void
    {
        $this->createRoleWithPerms('tenant_admin', 'Tenant Admin', [
            'dashboard.view',
        ]);
        $user = $this->createUser('tenant_admin');

        Cache::shouldReceive('forget')
            ->once()
            ->with("user_permissions_{$user->id}")
            ->andReturn(true);

        $user->flushPermissionCache();
    }

    public function test_inactive_user_has_no_permissions(): void
    {
        $role = $this->createRoleWithPerms('staff', 'Staff', ['dashboard.view']);
        $user = User::factory()->create([
            'role' => 'staff', 'role_id' => $role->id,
            'is_active' => false, 'history' => 1,
        ]);

        $this->assertFalse($user->hasPermission('dashboard.view'));
    }
}
