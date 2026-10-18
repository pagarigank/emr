<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PhilhealthMember;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::withoutGlobalScopes()->where('tenant_code', 'DEMO01')->first();
        $superadmin = User::withoutGlobalScopes()->where('role', 'superadmin')->first();
        $now = now();

        if (!$tenant) {
            $this->command->warn('⚠️ No DEMO01 tenant found. Skipping Patient seeder.');
            return;
        }

        $createdBy = $superadmin?->id ?? 1;

        $patients = [
            // PhilHealth Members (5)
            ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'is_ph' => true, 'gender' => 'Male', 'dob' => '1980-05-15'],
            ['first_name' => 'Maria', 'last_name' => 'Santos', 'is_ph' => true, 'gender' => 'Female', 'dob' => '1992-08-22'],
            ['first_name' => 'Pedro', 'last_name' => 'Penduko', 'is_ph' => true, 'gender' => 'Male', 'dob' => '1975-11-03'],
            ['first_name' => 'Leonor', 'last_name' => 'Rivera', 'is_ph' => true, 'gender' => 'Female', 'dob' => '1988-02-14'],
            ['first_name' => 'Andres', 'last_name' => 'Bonifacio', 'is_ph' => true, 'gender' => 'Male', 'dob' => '1995-12-30'],
            
            // Non-PhilHealth Members (5)
            ['first_name' => 'Jose', 'last_name' => 'Rizal', 'is_ph' => false, 'gender' => 'Male', 'dob' => '1990-06-19'],
            ['first_name' => 'Gabriela', 'last_name' => 'Silang', 'is_ph' => false, 'gender' => 'Female', 'dob' => '1998-03-08'],
            ['first_name' => 'Emilio', 'last_name' => 'Aguinaldo', 'is_ph' => false, 'gender' => 'Male', 'dob' => '1985-04-22'],
            ['first_name' => 'Melchora', 'last_name' => 'Aquino', 'is_ph' => false, 'gender' => 'Female', 'dob' => '1970-01-06'],
            ['first_name' => 'Apolinario', 'last_name' => 'Mabini', 'is_ph' => false, 'gender' => 'Male', 'dob' => '1982-07-23'],
        ];

        $patientCount = 1000; // start suffix for patient code

        foreach ($patients as $idx => $p) {
            $patientCount++;
            $phMemberId = null;
            $phPin = null;

            if ($p['is_ph']) {
                $phPin = '10-' . rand(1000000, 9999999) . '-' . rand(1, 9);
                
                $member = PhilhealthMember::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'pin' => $phPin,
                    'last_name' => $p['last_name'],
                    'first_name' => $p['first_name'],
                    'date_of_birth' => $p['dob'],
                    'gender' => $p['gender'],
                    'membership_type' => 'Employed',
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $createdBy,
                    'created_date' => $now,
                    'updated_by' => $createdBy,
                    'updated_date' => $now,
                ]);
                $phMemberId = $member->id;
            }

            Patient::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'patient_code' => now()->format('Y') . str_pad($patientCount, 10, '0', STR_PAD_LEFT),
                'philhealth_member_id' => $phMemberId,
                'philhealth_pin' => $phPin,
                'is_philhealth_member' => $p['is_ph'],
                'last_name' => $p['last_name'],
                'first_name' => $p['first_name'],
                'date_of_birth' => $p['dob'],
                // Gender ID would usually link to the genders table, but we don't have the map here
                // We'll leave it null for this simple seed or you can look up Gender ID
                'multiple_birth_indicator' => false,
                'is_deceased' => false,
                'is_active' => true,
                'history' => 1,
                'created_by' => $createdBy,
                'created_date' => $now,
                'updated_by' => $createdBy,
                'updated_date' => $now,
            ]);
        }

        $this->command->info('✅ Seeded 10 demo patients (5 PhilHealth, 5 Non-PhilHealth)');
    }
}
