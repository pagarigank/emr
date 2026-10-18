<?php

namespace Database\Seeders;

use App\Models\Gender;
use App\Models\CivilStatus;
use App\Models\BloodType;
use App\Models\Relationship;
use App\Models\Religion;
use App\Models\Nationality;
use App\Models\Occupation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemographicReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::withoutGlobalScopes()
                          ->where('role', 'superadmin')
                          ->first();

        $now = now();

        // ── Genders ────────────────────────────────────────────────────────
        $genders = [
            ['code' => 'M',   'name' => 'Male'],
            ['code' => 'F',   'name' => 'Female'],
            ['code' => 'NB',  'name' => 'Non-Binary'],
            ['code' => 'UN',  'name' => 'Unspecified'],
        ];

        foreach ($genders as $g) {
            Gender::withoutGlobalScopes()->updateOrCreate(
                ['code' => $g['code']],
                [
                    'name'         => $g['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($genders) . ' genders');

        // ── Civil Statuses ─────────────────────────────────────────────────
        $civilStatuses = [
            ['code' => 'S',    'name' => 'Single'],
            ['code' => 'M',    'name' => 'Married'],
            ['code' => 'W',    'name' => 'Widowed'],
            ['code' => 'D',    'name' => 'Divorced'],
            ['code' => 'SEP',  'name' => 'Separated'],
            ['code' => 'ANN',  'name' => 'Annulled'],
        ];

        foreach ($civilStatuses as $cs) {
            CivilStatus::withoutGlobalScopes()->updateOrCreate(
                ['code' => $cs['code']],
                [
                    'name'         => $cs['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($civilStatuses) . ' civil statuses');

        // ── Blood Types ────────────────────────────────────────────────────
        $bloodTypes = [
            ['code' => 'A+',  'name' => 'A Positive'],
            ['code' => 'A-',  'name' => 'A Negative'],
            ['code' => 'B+',  'name' => 'B Positive'],
            ['code' => 'B-',  'name' => 'B Negative'],
            ['code' => 'AB+', 'name' => 'AB Positive'],
            ['code' => 'AB-', 'name' => 'AB Negative'],
            ['code' => 'O+',  'name' => 'O Positive'],
            ['code' => 'O-',  'name' => 'O Negative'],
        ];

        foreach ($bloodTypes as $bt) {
            BloodType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $bt['code']],
                [
                    'name'         => $bt['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($bloodTypes) . ' blood types');

        // ── Relationships ──────────────────────────────────────────────────
        $relationships = [
            ['code' => 'SELF',   'name' => 'Self'],
            ['code' => 'SPOUSE', 'name' => 'Spouse'],
            ['code' => 'FATH',   'name' => 'Father'],
            ['code' => 'MOTH',   'name' => 'Mother'],
            ['code' => 'SON',    'name' => 'Son'],
            ['code' => 'DAU',    'name' => 'Daughter'],
            ['code' => 'SIB',    'name' => 'Sibling'],
            ['code' => 'GRPAR',  'name' => 'Grandparent'],
            ['code' => 'GRCHD',  'name' => 'Grandchild'],
            ['code' => 'AUNT',   'name' => 'Aunt'],
            ['code' => 'UNC',    'name' => 'Uncle'],
            ['code' => 'NEPH',   'name' => 'Nephew'],
            ['code' => 'NIEC',   'name' => 'Niece'],
            ['code' => 'COUS',   'name' => 'Cousin'],
            ['code' => 'LGL',    'name' => 'Legal Guardian'],
            ['code' => 'OTH',    'name' => 'Other'],
        ];

        foreach ($relationships as $r) {
            Relationship::withoutGlobalScopes()->updateOrCreate(
                ['code' => $r['code']],
                [
                    'name'         => $r['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($relationships) . ' relationships');

        // ── Religions ──────────────────────────────────────────────────────
        $religions = [
            ['code' => 'ROMCAT',  'name' => 'Roman Catholic'],
            ['code' => 'ISLAM',   'name' => 'Islam'],
            ['code' => 'Evang',   'name' => 'Evangelical'],
            ['code' => 'INC',     'name' => 'Iglesia ni Cristo'],
            ['code' => 'PROT',    'name' => 'Protestant'],
            ['code' => 'BORN',    'name' => 'Born Again Christian'],
            ['code' => 'BAPT',    'name' => 'Baptist'],
            ['code' => 'ADVENT',  'name' => 'Seventh Day Adventist'],
            ['code' => 'MORM',    'name' => 'Latter-day Saints'],
            ['code' => 'BUD',     'name' => 'Buddhist'],
            ['code' => 'HIND',    'name' => 'Hinduism'],
            ['code' => 'JEW',     'name' => 'Judaism'],
            ['code' => 'SHINTO',  'name' => 'Shintoism'],
            ['code' => 'TAO',     'name' => 'Taoism'],
            ['code' => 'AGLI',    'name' => 'Aglipayan'],
            ['code' => 'NONE',    'name' => 'None'],
        ];

        foreach ($religions as $rel) {
            Religion::withoutGlobalScopes()->updateOrCreate(
                ['code' => $rel['code']],
                [
                    'name'         => $rel['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($religions) . ' religions');

        // ── Nationalities ──────────────────────────────────────────────────
        $nationalities = [
            ['code' => 'PH',  'name' => 'Filipino'],
            ['code' => 'US',  'name' => 'American'],
            ['code' => 'JP',  'name' => 'Japanese'],
            ['code' => 'CN',  'name' => 'Chinese'],
            ['code' => 'KR',  'name' => 'Korean'],
            ['code' => 'GB',  'name' => 'British'],
            ['code' => 'AU',  'name' => 'Australian'],
            ['code' => 'CA',  'name' => 'Canadian'],
            ['code' => 'SG',  'name' => 'Singaporean'],
            ['code' => 'MY',  'name' => 'Malaysian'],
            ['code' => 'TH',  'name' => 'Thai'],
            ['code' => 'IN',  'name' => 'Indian'],
            ['code' => 'DE',  'name' => 'German'],
            ['code' => 'FR',  'name' => 'French'],
            ['code' => 'SA',  'name' => 'Saudi Arabian'],
            ['code' => 'AE',  'name' => 'Emirati'],
            ['code' => 'ID',  'name' => 'Indonesian'],
            ['code' => 'TW',  'name' => 'Taiwanese'],
            ['code' => 'HK',  'name' => 'Hong Konger'],
            ['code' => 'VN',  'name' => 'Vietnamese'],
        ];

        foreach ($nationalities as $n) {
            Nationality::withoutGlobalScopes()->updateOrCreate(
                ['code' => $n['code']],
                [
                    'name'         => $n['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($nationalities) . ' nationalities');

        // ── Occupations ────────────────────────────────────────────────────
        $occupations = [
            ['code' => 'EMP',    'name' => 'Employed'],
            ['code' => 'SEL',    'name' => 'Self-Employed'],
            ['code' => 'BIZ',    'name' => 'Business Owner'],
            ['code' => 'FARM',   'name' => 'Farmer'],
            ['code' => 'FISH',   'name' => 'Fisher'],
            ['code' => 'TRADE',  'name' => 'Tradesman'],
            ['code' => 'PROF',   'name' => 'Professional'],
            ['code' => 'GOV',    'name' => 'Government Employee'],
            ['code' => 'MIL',    'name' => 'Military'],
            ['code' => 'POL',    'name' => 'Police'],
            ['code' => 'TEACH',  'name' => 'Teacher'],
            ['code' => 'NUR',    'name' => 'Nurse'],
            ['code' => 'DOC',    'name' => 'Doctor'],
            ['code' => 'DOM',    'name' => 'Domestic Helper'],
            ['code' => 'STU',    'name' => 'Student'],
            ['code' => 'RET',    'name' => 'Retired'],
            ['code' => 'OFW',    'name' => 'Overseas Filipino Worker (OFW)'],
            ['code' => 'UNEMP',  'name' => 'Unemployed'],
            ['code' => 'NONE',   'name' => 'None / Not Applicable'],
        ];

        foreach ($occupations as $o) {
            Occupation::withoutGlobalScopes()->updateOrCreate(
                ['code' => $o['code']],
                [
                    'name'         => $o['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                ]
            );
        }

        $this->command->info('Seeded ' . count($occupations) . ' occupations');
    }
}
