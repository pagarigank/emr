<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $uid = 1;

        $roles = [
            ['name' => 'superadmin',   'display_name' => 'Super Admin',   'description' => 'Unrestricted access across all tenants'],
            ['name' => 'tenant_admin', 'display_name' => 'Tenant Admin',  'description' => 'Full access within their own tenant'],
            ['name' => 'staff',        'display_name' => 'Staff',         'description' => 'Limited feature access within tenant'],
            ['name' => 'doctor',       'display_name' => 'Doctor',        'description' => 'Linked to a health care professional profile'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(
                ['name' => $r['name']],
                [
                    'display_name' => $r['display_name'],
                    'description'  => $r['description'],
                    'is_system'    => true,
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $uid,
                    'created_date' => $now,
                    'updated_by'   => $uid,
                    'updated_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($roles) . ' system roles.');
    }
}
