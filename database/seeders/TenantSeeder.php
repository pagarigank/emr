<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * TenantSeeder
 *
 * 1. Creates the first demo company/tenant.
 * 2. Creates a tenant_admin user linked to that company.
 *
 * Run AFTER SuperadminSeeder so the superadmin user ID exists for created_by.
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $now        = now();
        $superadmin = User::withoutGlobalScopes()
                          ->where('role', 'superadmin')
                          ->first();

        // ── Step 1: Create the demo company/tenant ────────────────────────────
        $tenant = Tenant::withoutGlobalScopes()->updateOrCreate(
            ['tenant_code' => 'DEMO01'],
            [
                'tenant_code'    => 'DEMO01',
                'tenant_name'    => 'SITHEMR Demo Hospital',
                'address'        => '123 Health Avenue, Manila',
                'city'           => 'Manila',
                'contact_number' => '+63-2-8888-0001',
                'email'          => 'admin@demohospital.com',
                'logo_path'      => null,
                'is_active'      => true,
                'history'        => 1,
                'created_by'     => $superadmin?->id,
                'created_date'   => $now,
                'updated_by'     => null,
                'updated_date'   => null,
            ]
        );

        $this->command->info("✅  Tenant created: [{$tenant->tenant_code}] {$tenant->tenant_name}");

        // ── Step 2: Create a tenant_admin for this company ────────────────────
        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'admin@demohospital.com'],
            [
                'tenant_id'    => $tenant->id,
                'username'     => 'demoadmin',
                'full_name'    => 'Demo Hospital Admin',
                'email'        => 'admin@demohospital.com',
                'password'     => Hash::make('Admin@12345'),
                'role'         => 'tenant_admin',
                'is_active'    => true,
                'history'      => 1,
                'created_by'   => $superadmin?->id,
                'created_date' => $now,
                'updated_by'   => null,
                'updated_date' => null,
            ]
        );

        $this->command->info("✅  Tenant Admin created: admin@demohospital.com / Admin@12345  (tenant: {$tenant->tenant_name})");
    }
}
