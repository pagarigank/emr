<?php

namespace Database\Seeders;

use App\Models\Icd10Code;
use App\Models\PhilhealthCaseRate;
use App\Models\RenalCare\DialysisMachine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DialysisMasterSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1. Seed ICD-10 Codes for Chronic Kidney Disease / ESRD
        $icdCodes = [
            [
                'code' => 'N18.5',
                'description' => 'Chronic kidney disease, stage 5',
                'category' => 'Renal failure',
                'is_active' => true,
                'history' => 1,
                'created_date' => $now,
            ],
            [
                'code' => 'N18.6',
                'description' => 'End-stage renal disease (ESRD)',
                'category' => 'Renal failure',
                'is_active' => true,
                'history' => 1,
                'created_date' => $now,
            ],
            [
                'code' => 'N18.9',
                'description' => 'Chronic kidney disease, unspecified',
                'category' => 'Renal failure',
                'is_active' => true,
                'history' => 1,
                'created_date' => $now,
            ],
        ];

        foreach ($icdCodes as $c) {
            $existing = DB::table('icd_10_codes')->where('code', $c['code'])->where('history', 1)->first();
            if (!$existing) {
                DB::table('icd_10_codes')->insert($c);
            }
        }

        // 2. Seed RVS Codes for Hemodialysis
        $rvsCodes = [
            [
                'code' => '90935',
                'name' => 'Hemodialysis procedure with single evaluation',
                'type' => 'Medical / Dialysis',
                'is_active' => 1,
                'history' => 1,
                'created_date' => $now,
            ],
            [
                'code' => '90937',
                'name' => 'Hemodialysis procedure requiring repeated evaluation',
                'type' => 'Medical / Dialysis',
                'is_active' => 1,
                'history' => 1,
                'created_date' => $now,
            ],
            [
                'code' => '90945',
                'name' => 'Dialysis procedure other than hemodialysis (e.g. peritoneal)',
                'type' => 'Medical / Dialysis',
                'is_active' => 1,
                'history' => 1,
                'created_date' => $now,
            ],
        ];

        foreach ($rvsCodes as $r) {
            $existing = DB::table('rvs_codes')->where('code', $r['code'])->where('history', 1)->first();
            if (!$existing) {
                DB::table('rvs_codes')->insert($r);
            }
        }

        // 3. Seed PhilHealth Case Rates for Hemodialysis (Circular 2024-0014 / 2024-0023: ₱4,000 / ₱6,350)
        $icdN185 = DB::table('icd_10_codes')->where('code', 'N18.5')->where('history', 1)->first();
        $rvs90935 = DB::table('rvs_codes')->where('code', '90935')->where('history', 1)->first();

        $caseRates = [
            [
                'rvs_code' => '90935',
                'icd10_code' => 'N18.5',
                'description' => 'Hemodialysis (Single Session, Circular 2024-0023)',
                'hf_fee' => 3500.00,
                'pf_fee' => 500.00,
                'total_rate' => 4000.00,
                'rvs_code_id' => $rvs90935?->id,
                'icd10_code_id' => $icdN185?->id,
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
            [
                'rvs_code' => '90935',
                'icd10_code' => 'N18.6',
                'description' => 'Hemodialysis for ESRD (Expanded 156-session benefit)',
                'hf_fee' => 3500.00,
                'pf_fee' => 500.00,
                'total_rate' => 4000.00,
                'rvs_code_id' => $rvs90935?->id,
                'icd10_code_id' => $icdN185?->id,
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
        ];

        foreach ($caseRates as $cr) {
            $existing = DB::table('philhealth_case_rates')
                ->where('rvs_code', $cr['rvs_code'])
                ->where('icd10_code', $cr['icd10_code'])
                ->where('history', 1)
                ->first();

            if (!$existing) {
                DB::table('philhealth_case_rates')->insert($cr);
            }
        }

        // 4. Seed Dialysis Machines & Stations
        $stations = [
            [
                'tenant_id' => 1,
                'station_code' => 'ST-01',
                'station_name' => 'Station 1 (Main Hall)',
                'serial_number' => 'FN-4008S-2024-001',
                'brand' => 'Fresenius Medical Care',
                'model_name' => '4008S Classic',
                'station_category' => 'Regular',
                'status' => 'Available',
                'notes' => 'Station near nurse station, standard bicarbonate bath line',
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
            [
                'tenant_id' => 1,
                'station_code' => 'ST-02',
                'station_name' => 'Station 2 (Main Hall)',
                'serial_number' => 'FN-4008S-2024-002',
                'brand' => 'Fresenius Medical Care',
                'model_name' => '4008S Classic',
                'station_category' => 'Regular',
                'status' => 'Available',
                'notes' => 'Standard bicarbonate line',
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
            [
                'tenant_id' => 1,
                'station_code' => 'ST-03',
                'station_name' => 'Station 3 (Main Hall)',
                'serial_number' => 'FN-5008S-2025-010',
                'brand' => 'Fresenius Medical Care',
                'model_name' => '5008 CorDiax',
                'station_category' => 'Regular',
                'status' => 'Available',
                'notes' => 'Equipped for On-Line Hemodiafiltration (HDF)',
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
            [
                'tenant_id' => 1,
                'station_code' => 'ST-04',
                'station_name' => 'Station 4 (Main Hall)',
                'serial_number' => 'NP-SURD-2024-008',
                'brand' => 'Nipro',
                'model_name' => 'Surdial-X',
                'station_category' => 'Regular',
                'status' => 'Available',
                'notes' => 'Automated ultrafiltration controller',
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
            [
                'tenant_id' => 1,
                'station_code' => 'ST-05',
                'station_name' => 'Station 5 (Main Hall)',
                'serial_number' => 'BB-DLG-2023-015',
                'brand' => 'B. Braun',
                'model_name' => 'Dialog+',
                'station_category' => 'Regular',
                'status' => 'Available',
                'notes' => 'Dual pump hemodialysis machine',
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
            [
                'tenant_id' => 1,
                'station_code' => 'ISO-B01',
                'station_name' => 'Isolation Bay 1 (Hep B Dedicated)',
                'serial_number' => 'FN-4008S-2023-099',
                'brand' => 'Fresenius Medical Care',
                'model_name' => '4008S Classic',
                'station_category' => 'HepB_Isolation',
                'status' => 'Available',
                'notes' => 'STRICT ISOLATION: Dedicated exclusively for HBsAg-positive patients per DOH & PhilHealth mandate',
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
            [
                'tenant_id' => 1,
                'station_code' => 'ISO-C01',
                'station_name' => 'Isolation Bay 2 (Hep C Dedicated)',
                'serial_number' => 'NP-SURD-2023-050',
                'brand' => 'Nipro',
                'model_name' => 'Surdial-X',
                'station_category' => 'HepC_Isolation',
                'status' => 'Available',
                'notes' => 'STRICT ISOLATION: Dedicated exclusively for Anti-HCV-positive patients',
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
            [
                'tenant_id' => 1,
                'station_code' => 'ICU-M01',
                'station_name' => 'Mobile Unit 1 (ICU/Ward Bedside)',
                'serial_number' => 'FN-5008S-2025-022',
                'brand' => 'Fresenius Medical Care',
                'model_name' => '5008 CorDiax Mobile',
                'station_category' => 'ICU_Mobile',
                'status' => 'Available',
                'notes' => 'Portable RO-equipped dialysis system for bedside ICU inpatient renal care',
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
            ],
        ];

        foreach ($stations as $s) {
            $existing = DB::table('dialysis_machines')
                ->where('tenant_id', $s['tenant_id'])
                ->where('station_code', $s['station_code'])
                ->where('history', 1)
                ->first();

            if (!$existing) {
                DB::table('dialysis_machines')->insert($s);
            }
        }
    }
}
