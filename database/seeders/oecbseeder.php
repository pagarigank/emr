<?php

namespace Database\Seeders;

use App\Models\Oecb\OecbCorePresentation;
use App\Models\Oecb\OecbFeeSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class OecbSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::withoutGlobalScopes()->where('role', 'superadmin')->first();
        $userId = $superadmin?->id ?? 1;
        $now = now();

        $presentations = [
            ['code' => 'DOB', 'name' => 'Difficulty of Breathing',         'description' => 'Acute or chronic respiratory distress, shortness of breath, wheezing', 'sort_order' => 1,  'fee' => 9500.00],
            ['code' => 'CHP', 'name' => 'Chest Pain',                      'description' => 'Acute chest pain, tightness, pressure, suspected cardiac or non-cardiac', 'sort_order' => 2,  'fee' => 12000.00],
            ['code' => 'AMS', 'name' => 'Altered Mental Status',           'description' => 'Confusion, disorientation, loss of consciousness, lethargy', 'sort_order' => 3,  'fee' => 10000.00],
            ['code' => 'SAP', 'name' => 'Severe Abdominal Pain',           'description' => 'Acute severe abdominal pain, distension, peritonitis signs', 'sort_order' => 4,  'fee' => 8500.00],
            ['code' => 'SHD', 'name' => 'Severe Headache',                 'description' => 'Severe headache, migraine, suspected intracranial pathology', 'sort_order' => 5,  'fee' => 7500.00],
            ['code' => 'FVR', 'name' => 'Fever',                           'description' => 'High-grade fever with or without localizing signs', 'sort_order' => 6,  'fee' => 5500.00],
            ['code' => 'TRA', 'name' => 'Trauma/Injury',                   'description' => 'Blunt or penetrating trauma, fractures, lacerations, head injury', 'sort_order' => 7,  'fee' => 11000.00],
            ['code' => 'PSN', 'name' => 'Poisoning/Overdose',              'description' => 'Drug overdose, toxic ingestion, envenomation, chemical exposure', 'sort_order' => 8,  'fee' => 13000.00],
            ['code' => 'DEH', 'name' => 'Severe Dehydration',              'description' => 'Significant fluid loss, electrolyte imbalance, hypovolemia', 'sort_order' => 9,  'fee' => 6500.00],
            ['code' => 'SLZ', 'name' => 'Active Seizure',                  'description' => 'Ongoing or recent seizure activity, status epilepticus', 'sort_order' => 10, 'fee' => 9500.00],
            ['code' => 'ALR', 'name' => 'Severe Allergic Reaction',        'description' => 'Anaphylaxis, angioedema, severe drug/food reaction', 'sort_order' => 11, 'fee' => 8500.00],
            ['code' => 'OBS', 'name' => 'Obstetric Emergency',             'description' => 'Obstetric hemorrhage, pre-eclampsia/eclampsia, threatened abortion', 'sort_order' => 12, 'fee' => 12000.00],
            ['code' => 'NEO', 'name' => 'Neonatal Emergency',              'description' => 'Neonatal distress, sepsis, jaundice, feeding difficulty', 'sort_order' => 13, 'fee' => 10000.00],
            ['code' => 'OTH', 'name' => 'Other Emergency',                 'description' => 'Other emergency conditions not classified above', 'sort_order' => 14, 'fee' => 7000.00],
        ];

        foreach ($presentations as $p) {
            $core = OecbCorePresentation::withoutGlobalScopes()->updateOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'description' => $p['description'],
                    'sort_order' => $p['sort_order'],
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $userId,
                    'created_date' => $now,
                    'updated_by' => $userId,
                    'updated_date' => $now,
                ]
            );

            OecbFeeSchedule::withoutGlobalScopes()->updateOrCreate(
                ['package_code' => 'OECB-' . str_pad((string) $p['sort_order'], 3, '0', STR_PAD_LEFT), 'core_presentation_id' => $core->id],
                [
                    'description' => $p['name'] . ' — Fixed Fee Package',
                    'fixed_fee' => $p['fee'],
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $userId,
                    'created_date' => $now,
                    'updated_by' => $userId,
                    'updated_date' => $now,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($presentations) . ' OECB core presentations with fee schedules');
    }
}
