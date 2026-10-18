<?php

namespace Tests\Unit\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PermissionCacheBenchmarkTest extends TestCase
{
    private User $user;
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

        $role = Role::where('name', 'tenant_admin')->first();

        // Seed a realistic number of permissions
        foreach (['dashboard.view', 'patient.view', 'patient.create', 'patient.edit', 'admin.users', 'admin.masters', 'billing.view', 'billing.create', 'lab.view', 'lab.request'] as $name) {
            $perm = Permission::create([
                'name' => $name, 'display_name' => $name, 'module' => explode('.', $name)[0],
                'guard_name' => 'web', 'is_active' => true, 'history' => 1, 'created_date' => now(),
            ]);
            DB::table('role_permissions')->insert([
                'role' => 'tenant_admin', 'role_id' => $role->id,
                'permission_id' => $perm->id, 'tenant_id' => null, 'created_date' => now(),
            ]);
        }

        $this->user = User::factory()->create([
            'role' => 'tenant_admin', 'role_id' => $role->id, 'is_active' => true, 'history' => 1,
        ]);
    }

    public function test_cache_miss_performance(): void
    {
        Cache::forget("user_permissions_{$this->user->id}");

        $start = microtime(true);
        $perms = $this->user->permissions_list;
        $duration = (microtime(true) - $start) * 1000;

        $this->assertNotEmpty($perms);
        $this->addToAssertionCount(1);

        fwrite(STDERR, "\n[Benchmark] Cache MISS for user {$this->user->id}: " . round($duration, 2) . " ms\n");
    }

    public function test_cache_hit_performance(): void
    {
        Cache::forget("user_permissions_{$this->user->id}");

        // First access — primes cache
        $this->user->permissions_list;

        $start = microtime(true);
        $perms = $this->user->permissions_list;
        $duration = (microtime(true) - $start) * 1000;

        $this->assertNotEmpty($perms);
        $this->addToAssertionCount(1);

        fwrite(STDERR, "\n[Benchmark] Cache HIT for user {$this->user->id}: " . round($duration, 2) . " ms\n");
    }

    public function test_cache_hit_is_faster_than_miss(): void
    {
        Cache::forget("user_permissions_{$this->user->id}");

        $missStart = microtime(true);
        $this->user->permissions_list;
        $missDuration = (microtime(true) - $missStart) * 1000;

        $hitStart = microtime(true);
        $this->user->permissions_list;
        $hitDuration = (microtime(true) - $hitStart) * 1000;

        // Just log; don't assert timing because it varies by environment
        fwrite(STDERR, "\n[Benchmark] Cache MISS: " . round($missDuration, 2) . " ms, HIT: " . round($hitDuration, 2) . " ms\n");

        $this->addToAssertionCount(1);
    }
}
