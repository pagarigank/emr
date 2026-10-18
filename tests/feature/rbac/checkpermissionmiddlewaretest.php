<?php

namespace Tests\Feature\Rbac;

use App\Http\Middleware\CheckPermission;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class CheckPermissionMiddlewareTest extends TestCase
{
    private User $user;
    private User $noPermUser;

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

        // Roles are pre-seeded by the migration

        $perm = Permission::create([
            'name' => 'admin.users', 'display_name' => 'Manage Users', 'module' => 'Administration',
            'guard_name' => 'web', 'is_active' => true, 'history' => 1, 'created_date' => now(),
        ]);

        $this->user = User::factory()->create([
            'role' => 'tenant_admin', 'is_active' => true, 'history' => 1,
        ]);

        $this->noPermUser = User::factory()->create([
            'role' => 'staff', 'is_active' => true, 'history' => 1,
        ]);

        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $adminRole = Role::where('name', 'tenant_admin')->firstOrFail();

        $this->user->update(['role_id' => $adminRole->id]);
        $this->noPermUser->update(['role_id' => $staffRole->id]);

        \Illuminate\Support\Facades\DB::table('role_permissions')->insert([
            ['role' => 'tenant_admin', 'role_id' => $adminRole->id, 'permission_id' => $perm->id, 'tenant_id' => null, 'created_date' => now()],
        ]);
    }

    public function test_unauthenticated_user_is_denied(): void
    {
        $middleware = new CheckPermission;
        $request = Request::create('/api/admin/users', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $middleware->handle($request, fn () => response('OK'), 'admin.users');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_user_without_permission_is_denied(): void
    {
        $middleware = new CheckPermission;
        $request = Request::create('/api/admin/users', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->attributes->set('jwt_user', $this->noPermUser);
        auth('api')->setUser($this->noPermUser);

        $response = $middleware->handle($request, fn () => response('OK'), 'admin.users');

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_user_with_permission_is_allowed(): void
    {
        $middleware = new CheckPermission;
        $request = Request::create('/api/admin/users', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->attributes->set('jwt_user', $this->user);
        auth('api')->setUser($this->user);

        $response = $middleware->handle($request, fn () => response('OK'), 'admin.users');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_api_request_returns_json_forbidden(): void
    {
        $middleware = new CheckPermission;
        $request = Request::create('/api/admin/users', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->attributes->set('jwt_user', $this->noPermUser);
        auth('api')->setUser($this->noPermUser);

        $response = $middleware->handle($request, fn () => response('OK'), 'admin.users');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function test_superadmin_has_all_permissions(): void
    {
        $saRole = Role::where('name', 'superadmin')->firstOrFail();

        $sa = User::factory()->create([
            'role' => 'superadmin', 'role_id' => $saRole->id, 'is_active' => true, 'history' => 1,
        ]);

        $middleware = new CheckPermission;
        $request = Request::create('/api/admin/users', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->attributes->set('jwt_user', $sa);
        auth('api')->setUser($sa);

        $response = $middleware->handle($request, fn () => response('OK'), 'admin.users');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
