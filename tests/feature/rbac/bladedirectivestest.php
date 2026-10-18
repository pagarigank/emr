<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BladeDirectivesTest extends TestCase
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

        $adminRole = Role::where('name', 'tenant_admin')->first();
        $staffRole = Role::where('name', 'staff')->first();

        $this->user = User::factory()->create(['role' => 'tenant_admin', 'role_id' => $adminRole->id, 'is_active' => true, 'history' => 1]);
        $this->noPermUser = User::factory()->create(['role' => 'staff', 'role_id' => $staffRole->id, 'is_active' => true, 'history' => 1]);

        $perm = Permission::create([
            'name' => 'admin.users', 'display_name' => 'Manage Users', 'module' => 'Administration',
            'guard_name' => 'web', 'is_active' => true, 'history' => 1, 'created_date' => now(),
        ]);

        DB::table('role_permissions')->insert([
            ['role' => 'tenant_admin', 'role_id' => $adminRole->id, 'permission_id' => $perm->id, 'tenant_id' => null, 'created_date' => now()],
        ]);
    }

    private function actingAsUser(?User $user = null): void
    {
        $u = $user ?? $this->user;
        auth('api')->setUser($u);
    }

    public function test_can_directive_shows_content_when_user_has_permission(): void
    {
        $this->actingAsUser();

        $rendered = Blade::render(
            '@can("admin.users") SECRET @endcan'
        );

        $this->assertStringContainsString('SECRET', $rendered);
    }

    public function test_can_directive_hides_content_when_user_lacks_permission(): void
    {
        $this->actingAsUser($this->noPermUser);

        $rendered = Blade::render(
            '@can("admin.users") SECRET @endcan'
        );

        $this->assertStringNotContainsString('SECRET', $rendered);
    }

    public function test_cannot_directive_shows_content_when_user_lacks_permission(): void
    {
        $this->actingAsUser($this->noPermUser);

        $rendered = Blade::render(
            '@cannot("admin.users") HIDDEN @endcannot'
        );

        $this->assertStringContainsString('HIDDEN', $rendered);
    }

    public function test_cannot_directive_hides_content_when_user_has_permission(): void
    {
        $this->actingAsUser();

        $rendered = Blade::render(
            '@cannot("admin.users") HIDDEN @endcannot'
        );

        $this->assertStringNotContainsString('HIDDEN', $rendered);
    }

    public function test_role_directive_matches_correct_role(): void
    {
        $this->actingAsUser();

        $rendered = Blade::render(
            '@role("tenant_admin") ADMIN @endrole'
        );

        $this->assertStringContainsString('ADMIN', $rendered);
    }

    public function test_role_directive_does_not_match_wrong_role(): void
    {
        $this->actingAsUser();

        $rendered = Blade::render(
            '@role("staff") STAFF @endrole'
        );

        $this->assertStringNotContainsString('STAFF', $rendered);
    }

    public function test_multiple_directives_work_together(): void
    {
        $this->actingAsUser();

        $rendered = Blade::render(<<<'BLADE'
@can("admin.users")
    CAN_SEE
@endcan
@cannot("admin.users")
    CANNOT_SEE
@endcannot
@role("tenant_admin")
    IS_ADMIN
@endrole
BLADE);

        $this->assertStringContainsString('CAN_SEE', $rendered);
        $this->assertStringNotContainsString('CANNOT_SEE', $rendered);
        $this->assertStringContainsString('IS_ADMIN', $rendered);
    }
}
