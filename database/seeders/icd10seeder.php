<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Icd10Code;

class Icd10Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $codes = [
            ['code' => 'A09', 'description' => 'Infectious gastroenteritis and colitis, unspecified'],
            ['code' => 'A90', 'description' => 'Dengue fever [classical dengue]'],
            ['code' => 'A91', 'description' => 'Dengue hemorrhagic fever'],
            ['code' => 'E11.9', 'description' => 'Type 2 diabetes mellitus without complications'],
            ['code' => 'E11.65', 'description' => 'Type 2 diabetes mellitus with hyperglycemia'],
            ['code' => 'I10', 'description' => 'Essential (primary) hypertension'],
            ['code' => 'I11.9', 'description' => 'Hypertensive heart disease without heart failure'],
            ['code' => 'J02.9', 'description' => 'Acute pharyngitis, unspecified'],
            ['code' => 'J03.90', 'description' => 'Acute tonsillitis, unspecified'],
            ['code' => 'J06.9', 'description' => 'Acute upper respiratory infection, unspecified'],
            ['code' => 'J20.9', 'description' => 'Acute bronchitis, unspecified'],
            ['code' => 'J45.909', 'description' => 'Unspecified asthma, uncomplicated'],
            ['code' => 'J45.901', 'description' => 'Unspecified asthma with (acute) exacerbation'],
            ['code' => 'K21.9', 'description' => 'Gastro-esophageal reflux disease without esophagitis'],
            ['code' => 'N39.0', 'description' => 'Urinary tract infection, site not specified'],
            ['code' => 'O09.90', 'description' => 'Supervision of high risk pregnancy, unspecified'],
            ['code' => 'O80', 'description' => 'Encounter for full-term uncomplicated delivery'],
            ['code' => 'R05', 'description' => 'Cough'],
            ['code' => 'R07.9', 'description' => 'Chest pain, unspecified'],
            ['code' => 'R10.9', 'description' => 'Unspecified abdominal pain'],
            ['code' => 'R50.9', 'description' => 'Fever, unspecified'],
            ['code' => 'R51.9', 'description' => 'Headache, unspecified'],
            ['code' => 'Z00.00', 'description' => 'Encounter for general adult medical examination without abnormal findings'],
            ['code' => 'Z00.129', 'description' => 'Encounter for routine child health examination without abnormal findings'],
            ['code' => 'Z34.90', 'description' => 'Encounter for supervision of normal pregnancy, unspecified'],
        ];

        foreach ($codes as $code) {
            Icd10Code::firstOrCreate(
                ['code' => $code['code']],
                [
                    'description' => $code['description'],
                    'is_active' => true,
                    'history' => 1,
                    'created_date' => now(),
                    'updated_date' => now()
                ]
            );
        }
    }
}
