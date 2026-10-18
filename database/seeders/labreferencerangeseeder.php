<?php

namespace Database\Seeders;

use App\Models\LabTest;
use App\Models\ReferenceRange;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class LabReferenceRangeSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::withoutGlobalScopes()->where('tenant_code', 'DEMO01')->first();
        $superadmin = User::withoutGlobalScopes()->where('role', 'superadmin')->first();

        if (!$tenant || !$superadmin) {
            $this->command->warn('⚠️  No DEMO01 tenant or superadmin found. Skipping reference ranges.');
            return;
        }

        $createdBy = $superadmin->id;
        $now = now();

        $testMap = [];
        $tests = LabTest::withoutGlobalScopes()->where('tenant_id', $tenant->id)->get();
        foreach ($tests as $t) {
            $testMap[$t->code] = $t->id;
        }

        // Clear existing ranges for these tests
        $testIds = array_values($testMap);
        ReferenceRange::withoutGlobalScopes()->whereIn('lab_test_id', $testIds)->delete();

        $ranges = [

            // ═══════════════════════════════════════════════════════
            // CLINICAL CHEMISTRY
            // ═══════════════════════════════════════════════════════

            // GLU — Glucose (FBS)  | 70-100 mg/dL
            ['code' => 'GLU', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 70, 'max' => 100, 'text' => null, 'interp' => 'Normal fasting glucose'],

            // CREA — Creatinine   | M: 0.7-1.3, F: 0.6-1.1, Ped: 0.2-0.7 mg/dL
            ['code' => 'CREA', 'gender' => 'Male', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 0.7, 'max' => 1.3, 'text' => null, 'interp' => null],
            ['code' => 'CREA', 'gender' => 'Female', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 0.6, 'max' => 1.1, 'text' => null, 'interp' => null],
            ['code' => 'CREA', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 17.9,
             'min' => 0.2, 'max' => 0.7, 'text' => null, 'interp' => 'Pediatric'],

            // BUN — Blood Urea Nitrogen  | 7-20 mg/dL
            ['code' => 'BUN', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 7, 'max' => 20, 'text' => null, 'interp' => null],

            // UA — Uric Acid  | M: 3.5-7.2, F: 2.6-6.0, Ped: 2.0-5.5 mg/dL
            ['code' => 'UA', 'gender' => 'Male', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 3.5, 'max' => 7.2, 'text' => null, 'interp' => null],
            ['code' => 'UA', 'gender' => 'Female', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 2.6, 'max' => 6.0, 'text' => null, 'interp' => null],
            ['code' => 'UA', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 17.9,
             'min' => 2.0, 'max' => 5.5, 'text' => null, 'interp' => 'Pediatric'],

            // CHOL — Total Cholesterol  | Desirable: 0-199 mg/dL
            ['code' => 'CHOL', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 0, 'max' => 199, 'text' => 'Desirable: <200 mg/dL', 'interp' => 'Desirable'],

            // HDL — HDL Cholesterol  | M: ≥40, F: ≥50 mg/dL
            ['code' => 'HDL', 'gender' => 'Male', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 40, 'max' => 9999, 'text' => '≥40 mg/dL', 'interp' => 'Desirable ≥40'],
            ['code' => 'HDL', 'gender' => 'Female', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 50, 'max' => 9999, 'text' => '≥50 mg/dL', 'interp' => 'Desirable ≥50'],

            // LDL — LDL Cholesterol  | Optimal: 0-99 mg/dL
            ['code' => 'LDL', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 0, 'max' => 99, 'text' => 'Optimal: <100 mg/dL', 'interp' => 'Optimal'],

            // TG — Triglycerides  | Normal: 0-149 mg/dL
            ['code' => 'TG', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 0, 'max' => 149, 'text' => 'Normal: <150 mg/dL', 'interp' => 'Normal'],

            // ALT — ALT (SGPT)  | M: 10-40, F: 7-35 U/L
            ['code' => 'ALT', 'gender' => 'Male', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 10, 'max' => 40, 'text' => null, 'interp' => null],
            ['code' => 'ALT', 'gender' => 'Female', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 7, 'max' => 35, 'text' => null, 'interp' => null],

            // AST — AST (SGOT)  | M: 10-40, F: 8-35 U/L
            ['code' => 'AST', 'gender' => 'Male', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 10, 'max' => 40, 'text' => null, 'interp' => null],
            ['code' => 'AST', 'gender' => 'Female', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 8, 'max' => 35, 'text' => null, 'interp' => null],

            // NA — Sodium  | 136-145 mmol/L
            ['code' => 'NA', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 136, 'max' => 145, 'text' => null, 'interp' => null],

            // K — Potassium  | 3.5-5.1 mmol/L
            ['code' => 'K', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 3.5, 'max' => 5.1, 'text' => null, 'interp' => null],

            // CL — Chloride  | 98-107 mmol/L
            ['code' => 'CL', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 98, 'max' => 107, 'text' => null, 'interp' => null],

            // ═══════════════════════════════════════════════════════
            // HEMATOLOGY
            // ═══════════════════════════════════════════════════════

            // HGB — Hemoglobin (g/dL)
            ['code' => 'HGB', 'gender' => 'Male', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 13.5, 'max' => 17.5, 'text' => null, 'interp' => 'Adult male'],
            ['code' => 'HGB', 'gender' => 'Female', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 12.0, 'max' => 15.5, 'text' => null, 'interp' => 'Adult female'],
            ['code' => 'HGB', 'gender' => 'Any', 'age_min' => 6, 'age_max' => 12.9,
             'min' => 11.5, 'max' => 15.5, 'text' => null, 'interp' => 'Child (6-12 yrs)'],
            ['code' => 'HGB', 'gender' => 'Any', 'age_min' => 1, 'age_max' => 5.9,
             'min' => 11.0, 'max' => 14.0, 'text' => null, 'interp' => 'Child (1-5 yrs)'],
            ['code' => 'HGB', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 0.9,
             'min' => 10.0, 'max' => 13.0, 'text' => null, 'interp' => 'Infant (<1 yr)'],

            // HCT — Hematocrit (%)
            ['code' => 'HCT', 'gender' => 'Male', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 38.8, 'max' => 50.0, 'text' => null, 'interp' => 'Adult male'],
            ['code' => 'HCT', 'gender' => 'Female', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 34.9, 'max' => 44.5, 'text' => null, 'interp' => 'Adult female'],
            ['code' => 'HCT', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 17.9,
             'min' => 30.0, 'max' => 42.0, 'text' => null, 'interp' => 'Pediatric'],

            // PLT — Platelet Count  | 150-450 x10^9/L
            ['code' => 'PLT', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 150, 'max' => 450, 'text' => null, 'interp' => null],

            // WBC — White Blood Cell Count (x10^9/L)
            ['code' => 'WBC', 'gender' => 'Any', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 4.5, 'max' => 11.0, 'text' => null, 'interp' => 'Adult'],
            ['code' => 'WBC', 'gender' => 'Any', 'age_min' => 6, 'age_max' => 17.9,
             'min' => 4.5, 'max' => 13.5, 'text' => null, 'interp' => 'Child (6-17 yrs)'],
            ['code' => 'WBC', 'gender' => 'Any', 'age_min' => 2, 'age_max' => 5.9,
             'min' => 5.0, 'max' => 15.5, 'text' => null, 'interp' => 'Child (2-5 yrs)'],
            ['code' => 'WBC', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 1.9,
             'min' => 6.0, 'max' => 17.5, 'text' => null, 'interp' => 'Infant/toddler'],

            // RBC — Red Blood Cell Count (x10^12/L)
            ['code' => 'RBC', 'gender' => 'Male', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 4.5, 'max' => 5.9, 'text' => null, 'interp' => 'Adult male'],
            ['code' => 'RBC', 'gender' => 'Female', 'age_min' => 18, 'age_max' => 999.9,
             'min' => 4.1, 'max' => 5.1, 'text' => null, 'interp' => 'Adult female'],
            ['code' => 'RBC', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 17.9,
             'min' => 3.8, 'max' => 5.5, 'text' => null, 'interp' => 'Pediatric'],

            // ESR — Erythrocyte Sedimentation Rate (mm/hr)
            ['code' => 'ESR', 'gender' => 'Male', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 0, 'max' => 15, 'text' => null, 'interp' => null],
            ['code' => 'ESR', 'gender' => 'Female', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 0, 'max' => 20, 'text' => null, 'interp' => null],

            // PT — Prothrombin Time (Ratio)
            ['code' => 'PT', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 0.9, 'max' => 1.2, 'text' => null, 'interp' => 'INR-equivalent ratio'],

            // APTT — Activated Partial Thromboplastin Time (Ratio)
            ['code' => 'APTT', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 0.8, 'max' => 1.2, 'text' => null, 'interp' => 'Ratio'],

            // CBC — Complete Blood Count (panel-level reference)
            ['code' => 'CBC', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => null, 'max' => null, 'text' => 'Refer to component values',
             'interp' => 'Panel — interpret individual components'],

            // ═══════════════════════════════════════════════════════
            // SEROLOGY / IMMUNOLOGY
            // ═══════════════════════════════════════════════════════

            // HBSAG — HBsAg (Categorical)
            ['code' => 'HBSAG', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => null, 'max' => null, 'text' => 'Non-reactive (Negative)',
             'interp' => 'Non-reactive = Negative for Hepatitis B surface antigen'],

            // ANTI_HBS — Anti-HBs  | ≥10 IU/mL = protective
            ['code' => 'ANTI_HBS', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => 10, 'max' => 9999, 'text' => 'Protective: ≥10 IU/mL',
             'interp' => 'Immune / Protective'],

            // VDRL — VDRL / RPR (Categorical)
            ['code' => 'VDRL', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => null, 'max' => null, 'text' => 'Non-reactive (Negative)',
             'interp' => 'Non-reactive = Negative for syphilis'],

            // DENGUE — Dengue NS1 (Categorical)
            ['code' => 'DENGUE', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => null, 'max' => null, 'text' => 'Negative',
             'interp' => 'Negative = No dengue NS1 antigen detected'],

            // ═══════════════════════════════════════════════════════
            // URINALYSIS
            // ═══════════════════════════════════════════════════════

            // U_A — Urinalysis (Text-based)
            ['code' => 'U_A', 'gender' => 'Any', 'age_min' => 0, 'age_max' => 999.9,
             'min' => null, 'max' => null,
             'text' => 'Refer to individual dipstick/microscopic parameters',
             'interp' => 'Text-based — interpret per component'],
        ];

        $inserted = 0;
        foreach ($ranges as $r) {
            $testId = $testMap[$r['code']] ?? null;
            if (!$testId) {
                $this->command->warn("  ⚠️  Test code '{$r['code']}' not found, skipping.");
                continue;
            }

            ReferenceRange::withoutGlobalScopes()->create([
                'lab_test_id'       => $testId,
                'gender'            => $r['gender'],
                'age_min_years'     => $r['age_min'],
                'age_max_years'     => $r['age_max'],
                'min_value'         => $r['min'],
                'max_value'         => $r['max'],
                'normal_values_text'=> $r['text'],
                'interpretation'    => $r['interp'],
                'is_active'         => true,
                'history'           => 1,
                'created_by'        => $createdBy,
                'created_date'      => $now,
                'updated_by'        => $createdBy,
                'updated_date'      => null,
            ]);
            $inserted++;
        }

        $this->command->info("✅  Seeded {$inserted} reference ranges across " . count($testMap) . ' lab tests');
    }
}
