<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * SuperadminSeeder
 *
 * Creates the first superadmin user.
 * tenant_id = NULL  →  no tenant restriction, can access all companies.
 */
class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'superadmin@sithemr.com'],
            [
                'tenant_id'    => null,               // superadmin: no tenant
                'username'     => 'superadmin',
                'full_name'    => 'Super Administrator',
                'email'        => 'superadmin@sithemr.com',
                'password'     => Hash::make('Admin@1234'),
                'role'         => 'superadmin',
                'is_active'    => true,
                'history'      => 1,
                'created_by'   => null,               // self-seeded
                'created_date' => $now,
                'updated_by'   => null,
                'updated_date' => null,
            ]
        );

        $this->command->info('✅  Superadmin created: superadmin@sithemr.com / Admin@1234');
    }
}
