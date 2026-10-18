<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Region;
use App\Models\ProvinceCity;
use App\Models\Barangay;
use App\Models\User;
use Illuminate\Database\Seeder;

class GeographicReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::withoutGlobalScopes()
                          ->where('role', 'superadmin')
                          ->first();

        $now = now();

        // ── Countries ──────────────────────────────────────────────────────
        $countries = [
            ['code' => 'PH', 'name' => 'Philippines'],
            ['code' => 'US', 'name' => 'United States of America'],
            ['code' => 'JP', 'name' => 'Japan'],
            ['code' => 'CN', 'name' => 'China'],
            ['code' => 'KR', 'name' => 'South Korea'],
            ['code' => 'GB', 'name' => 'United Kingdom'],
            ['code' => 'AU', 'name' => 'Australia'],
            ['code' => 'CA', 'name' => 'Canada'],
            ['code' => 'SG', 'name' => 'Singapore'],
            ['code' => 'MY', 'name' => 'Malaysia'],
            ['code' => 'TH', 'name' => 'Thailand'],
            ['code' => 'IN', 'name' => 'India'],
            ['code' => 'DE', 'name' => 'Germany'],
            ['code' => 'FR', 'name' => 'France'],
            ['code' => 'SA', 'name' => 'Saudi Arabia'],
            ['code' => 'AE', 'name' => 'United Arab Emirates'],
            ['code' => 'OM', 'name' => 'Oman'],
            ['code' => 'QA', 'name' => 'Qatar'],
            ['code' => 'KW', 'name' => 'Kuwait'],
            ['code' => 'HK', 'name' => 'Hong Kong'],
        ];

        $countryModels = [];
        foreach ($countries as $c) {
            $countryModels[$c['code']] = Country::withoutGlobalScopes()->updateOrCreate(
                ['code' => $c['code']],
                [
                    'name'         => $c['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($countries) . ' countries');

        // ── Philippine Regions ─────────────────────────────────────────────
        $phId = $countryModels['PH']->id;

        $regions = [
            ['code' => '010000000', 'name' => 'Ilocos Region (Region I)'],
            ['code' => '020000000', 'name' => 'Cagayan Valley (Region II)'],
            ['code' => '030000000', 'name' => 'Central Luzon (Region III)'],
            ['code' => '040000000', 'name' => 'CALABARZON (Region IV-A)'],
            ['code' => '170000000', 'name' => 'MIMAROPA (Region IV-B)'],
            ['code' => '050000000', 'name' => 'Bicol Region (Region V)'],
            ['code' => '060000000', 'name' => 'Western Visayas (Region VI)'],
            ['code' => '070000000', 'name' => 'Central Visayas (Region VII)'],
            ['code' => '080000000', 'name' => 'Eastern Visayas (Region VIII)'],
            ['code' => '090000000', 'name' => 'Zamboanga Peninsula (Region IX)'],
            ['code' => '100000000', 'name' => 'Northern Mindanao (Region X)'],
            ['code' => '110000000', 'name' => 'Davao Region (Region XI)'],
            ['code' => '120000000', 'name' => 'SOCCSKSARGEN (Region XII)'],
            ['code' => '130000000', 'name' => 'National Capital Region (NCR)'],
            ['code' => '140000000', 'name' => 'Cordillera Administrative Region (CAR)'],
            ['code' => '150000000', 'name' => 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)'],
            ['code' => '160000000', 'name' => 'Caraga (Region XIII)'],
        ];

        $regionModels = [];
        foreach ($regions as $r) {
            $regionModels[$r['code']] = Region::withoutGlobalScopes()->updateOrCreate(
                ['country_id' => $phId, 'code' => $r['code']],
                [
                    'name'         => $r['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($regions) . ' Philippine regions');

        // ── Provinces / Cities (representative sample) ──────────────────────
        $provinces = [
            ['region' => '130000000', 'code' => '133900000', 'name' => 'City of Manila', 'type' => 'city'],
            ['region' => '130000000', 'code' => '137400000', 'name' => 'Quezon City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '137200000', 'name' => 'Makati City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '137600000', 'name' => 'Pasig City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '137300000', 'name' => 'Mandaluyong City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '137500000', 'name' => 'Parañaque City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '137700000', 'name' => 'Taguig City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '137800000', 'name' => 'Valenzuela City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '130100000', 'name' => 'Caloocan City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '136700000', 'name' => 'Las Piñas City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '136800000', 'name' => 'Malabon City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '136900000', 'name' => 'Marikina City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '137100000', 'name' => 'Muntinlupa City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '137000000', 'name' => 'Navotas City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '138000000', 'name' => 'San Juan City', 'type' => 'city'],
            ['region' => '130000000', 'code' => '138100000', 'name' => 'Pasay City', 'type' => 'city'],
            ['region' => '040000000', 'code' => '041000000', 'name' => 'Cavite', 'type' => 'province'],
            ['region' => '040000000', 'code' => '042100000', 'name' => 'Laguna', 'type' => 'province'],
            ['region' => '040000000', 'code' => '043400000', 'name' => 'Batangas', 'type' => 'province'],
            ['region' => '040000000', 'code' => '045600000', 'name' => 'Rizal', 'type' => 'province'],
            ['region' => '040000000', 'code' => '045800000', 'name' => 'Quezon', 'type' => 'province'],
            ['region' => '030000000', 'code' => '031400000', 'name' => 'Bulacan', 'type' => 'province'],
            ['region' => '030000000', 'code' => '034900000', 'name' => 'Pampanga', 'type' => 'province'],
            ['region' => '030000000', 'code' => '035400000', 'name' => 'Tarlac', 'type' => 'province'],
            ['region' => '030000000', 'code' => '036900000', 'name' => 'Nueva Ecija', 'type' => 'province'],
            ['region' => '030000000', 'code' => '037100000', 'name' => 'Zambales', 'type' => 'province'],
            ['region' => '060000000', 'code' => '063000000', 'name' => 'Iloilo', 'type' => 'province'],
            ['region' => '060000000', 'code' => '060400000', 'name' => 'Aklan', 'type' => 'province'],
            ['region' => '060000000', 'code' => '060600000', 'name' => 'Antique', 'type' => 'province'],
            ['region' => '060000000', 'code' => '061800000', 'name' => 'Capiz', 'type' => 'province'],
            ['region' => '060000000', 'code' => '063200000', 'name' => 'Negros Occidental', 'type' => 'province'],
            ['region' => '070000000', 'code' => '072200000', 'name' => 'Cebu', 'type' => 'province'],
            ['region' => '070000000', 'code' => '071200000', 'name' => 'Bohol', 'type' => 'province'],
            ['region' => '070000000', 'code' => '074600000', 'name' => 'Negros Oriental', 'type' => 'province'],
            ['region' => '070000000', 'code' => '076100000', 'name' => 'Siquijor', 'type' => 'province'],
            ['region' => '110000000', 'code' => '112300000', 'name' => 'Davao del Sur', 'type' => 'province'],
            ['region' => '110000000', 'code' => '118200000', 'name' => 'Davao City', 'type' => 'city'],
        ];

        $provinceModels = [];
        foreach ($provinces as $p) {
            $provinceModels[$p['code']] = ProvinceCity::withoutGlobalScopes()->updateOrCreate(
                ['region_id' => $regionModels[$p['region']]->id, 'code' => $p['code']],
                [
                    'name'         => $p['name'],
                    'type'         => $p['type'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($provinces) . ' provinces/cities');

        // ── Barangays (representative sample per key city) ──────────────────
        $barangays = [
            // Manila (sample)
            ['province' => '133900000', 'code' => '133901001', 'name' => 'Barangay 1'],
            ['province' => '133900000', 'code' => '133901002', 'name' => 'Barangay 2'],
            ['province' => '133900000', 'code' => '133901003', 'name' => 'Barangay 3'],
            ['province' => '133900000', 'code' => '133901004', 'name' => 'Barangay 4'],
            ['province' => '133900000', 'code' => '133901005', 'name' => 'Barangay 5'],
            ['province' => '133900000', 'code' => '133901006', 'name' => 'Barangay 6'],
            ['province' => '133900000', 'code' => '133901007', 'name' => 'Barangay 7'],
            ['province' => '133900000', 'code' => '133901008', 'name' => 'Barangay 8'],
            ['province' => '133900000', 'code' => '133901009', 'name' => 'Barangay 9'],
            ['province' => '133900000', 'code' => '133901010', 'name' => 'Barangay 10'],
            // Quezon City (sample)
            ['province' => '137400000', 'code' => '137401001', 'name' => 'Barangay Commonwealth'],
            ['province' => '137400000', 'code' => '137401002', 'name' => 'Barangay Batasan Hills'],
            ['province' => '137400000', 'code' => '137401003', 'name' => 'Barangay Tandang Sora'],
            ['province' => '137400000', 'code' => '137401004', 'name' => 'Barangay Payatas'],
            ['province' => '137400000', 'code' => '137401005', 'name' => 'Barangay Holy Spirit'],
            ['province' => '137400000', 'code' => '137401006', 'name' => 'Barangay Diliman'],
            ['province' => '137400000', 'code' => '137401007', 'name' => 'Barangay Project 6'],
            ['province' => '137400000', 'code' => '137401008', 'name' => 'Barangay West Triangle'],
            ['province' => '137400000', 'code' => '137401009', 'name' => 'Barangay Philam'],
            ['province' => '137400000', 'code' => '137401010', 'name' => 'Barangay Bagong Pag-asa'],
            // Makati (sample)
            ['province' => '137200000', 'code' => '137201001', 'name' => 'Barangay Poblacion'],
            ['province' => '137200000', 'code' => '137201002', 'name' => 'Barangay Bel-Air Village'],
            ['province' => '137200000', 'code' => '137201003', 'name' => 'Barangay Uptown Bonifacio'],
            ['province' => '137200000', 'code' => '137201004', 'name' => 'Barangay Ayala Alabang'],
            ['province' => '137200000', 'code' => '137201005', 'name' => 'Barangay Forbes Park'],
            ['province' => '137200000', 'code' => '137201006', 'name' => 'Barangay Dasmariñas Village'],
            ['province' => '137200000', 'code' => '137201007', 'name' => 'Barangay San Lorenzo Village'],
            ['province' => '137200000', 'code' => '137201008', 'name' => 'Barangay Rockwell Center'],
            // Cebu (sample)
            ['province' => '072200000', 'code' => '072201001', 'name' => 'Barangay Guadalupe'],
            ['province' => '072200000', 'code' => '072201002', 'name' => 'Barangay Mabolo'],
            ['province' => '072200000', 'code' => '072201003', 'name' => 'Barangay Banilad'],
            ['province' => '072200000', 'code' => '072201004', 'name' => 'Barangay Lahug'],
            ['province' => '072200000', 'code' => '072201005', 'name' => 'Barangay Talamban'],
            // Davao City (sample)
            ['province' => '118200000', 'code' => '118201001', 'name' => 'Barangay Poblacion'],
            ['province' => '118200000', 'code' => '118201002', 'name' => 'Barangay Matina'],
            ['province' => '118200000', 'code' => '118201003', 'name' => 'Barangay Buhangin'],
            ['province' => '118200000', 'code' => '118201004', 'name' => 'Barangay Talomo'],
            ['province' => '118200000', 'code' => '118201005', 'name' => 'Barangay Lanang'],
        ];

        foreach ($barangays as $b) {
            Barangay::withoutGlobalScopes()->updateOrCreate(
                ['province_city_id' => $provinceModels[$b['province']]->id, 'code' => $b['code']],
                [
                    'name'         => $b['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($barangays) . ' barangays (representative sample)');
    }
}
