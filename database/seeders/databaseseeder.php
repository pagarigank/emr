<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed order matters:
     *   1. RoleSeeder         — creates the 4 system roles
     *   2. PermissionSeeder   — creates permissions and maps them to roles
     *   3. SuperadminSeeder   — creates the global superadmin (no tenant)
     *   4. TenantSeeder       — creates a demo company + tenant_admin user
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            SuperadminSeeder::class,
            TenantSeeder::class,
            GeographicReferenceSeeder::class,
            DemographicReferenceSeeder::class,
            ReferenceDataSeeder::class,
            DemoMasterDataSeeder::class,
            DemoHospitalSeeder::class,
            OecbSeeder::class,
            EeclSeeder::class,
            DemoDataSeeder::class,
            LabReferenceRangeSeeder::class,
            PatientSeeder::class,
            Icd10Seeder::class,
            PhilhealthReferenceSeeder::class,
        ]);
    }
}
