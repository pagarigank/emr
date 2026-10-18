<?php

namespace Database\Seeders;

use App\Models\Procedures\ProcedureCategory;
use App\Models\RenalCare\DialysisType;
use App\Models\ER\ErAcuityLevel;
use App\Models\ER\ErMobilityStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::withoutGlobalScopes()
                          ->where('role', 'superadmin')
                          ->first();

        $now = now();

        // ── Procedure Categories ────────────────────────────────────────────────
        $categories = [
            ['code' => 'DX',  'name' => 'Diagnostic',          'description' => 'Diagnostic procedures (imaging, endoscopy, etc.)'],
            ['code' => 'LB',  'name' => 'Laboratory',          'description' => 'Laboratory and pathology procedures'],
            ['code' => 'SX',  'name' => 'Surgical',            'description' => 'Surgical and operative procedures'],
            ['code' => 'MD',  'name' => 'Medical',             'description' => 'General medical procedures'],
            ['code' => 'RX',  'name' => 'Radiology',           'description' => 'Radiology and imaging procedures'],
            ['code' => 'REH', 'name' => 'Rehabilitation',      'description' => 'Physical and occupational therapy procedures'],
            ['code' => 'PREV','name' => 'Preventive',          'description' => 'Preventive care and screening procedures'],
            ['code' => 'OP',  'name' => 'Outpatient Procedure', 'description' => 'Same-day outpatient procedures'],
        ];

        foreach ($categories as $cat) {
            ProcedureCategory::withoutGlobalScopes()->updateOrCreate(
                ['code' => $cat['code']],
                [
                    'name'         => $cat['name'],
                    'description'  => $cat['description'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                    'updated_by'   => $superadmin?->id,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($categories) . ' procedure categories');

        // ── Dialysis Types ──────────────────────────────────────────────────────
        $dialysisTypes = [
            ['code' => 'HD',   'name' => 'Hemodialysis',          'description' => 'Standard in-center hemodialysis'],
            ['code' => 'HDF',  'name' => 'Hemodiafiltration',      'description' => 'Combined hemodialysis and hemofiltration'],
            ['code' => 'PD',   'name' => 'Peritoneal Dialysis',    'description' => 'Continuous ambulatory peritoneal dialysis (CAPD)'],
            ['code' => 'APD',  'name' => 'Automated Peritoneal Dialysis', 'description' => 'Cycler-assisted peritoneal dialysis (CCPD)'],
            ['code' => 'SLED', 'name' => 'Sustained Low-Efficiency Dialysis', 'description' => 'Slow prolonged hemodialysis for critically ill patients'],
            ['code' => 'IRRT', 'name' => 'Intermittent Renal Replacement Therapy', 'description' => 'IRRT for acute kidney injury'],
            ['code' => 'CRRT', 'name' => 'Continuous Renal Replacement Therapy', 'description' => 'CRRT for hemodynamically unstable patients'],
            ['code' => 'PE',   'name' => 'Plasma Exchange',        'description' => 'Therapeutic plasma exchange (plasmapheresis)'],
        ];

        foreach ($dialysisTypes as $dt) {
            DialysisType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $dt['code']],
                [
                    'name'         => $dt['name'],
                    'description'  => $dt['description'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                    'updated_by'   => $superadmin?->id,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($dialysisTypes) . ' dialysis types');

        // ── ER Acuity Levels ──────────────────────────────────────────────────────
        $acuityLevels = [
            ['code' => 'ESI-1', 'name' => 'Resuscitation', 'color_code' => '#ef4444', 'description' => 'Immediate, life-saving intervention required without delay.'],
            ['code' => 'ESI-2', 'name' => 'Emergent',      'color_code' => '#f97316', 'description' => 'High risk of deterioration, or signs of a time-critical problem.'],
            ['code' => 'ESI-3', 'name' => 'Urgent',        'color_code' => '#eab308', 'description' => 'Stable, with multiple types of resources required to investigate or treat.'],
            ['code' => 'ESI-4', 'name' => 'Less Urgent',   'color_code' => '#22c55e', 'description' => 'Stable, with only one type of resource anticipated (e.g., only an X-ray or only sutures).'],
            ['code' => 'ESI-5', 'name' => 'Non-Urgent',    'color_code' => '#3b82f6', 'description' => 'Stable, with no resources anticipated except oral or topical medications, or prescriptions.'],
        ];

        foreach ($acuityLevels as $al) {
            ErAcuityLevel::withoutGlobalScopes()->updateOrCreate(
                ['code' => $al['code']],
                [
                    'name'         => $al['name'],
                    'color_code'   => $al['color_code'],
                    'description'  => $al['description'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                    'updated_by'   => $superadmin?->id,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($acuityLevels) . ' ER acuity levels (ESI)');

        // ── ER Mobility Statuses ──────────────────────────────────────────────────
        $mobilityStatuses = [
            ['code' => 'AMB', 'name' => 'Ambulatory', 'description' => 'Walking without assistance.'],
            ['code' => 'WC',  'name' => 'Wheelchair', 'description' => 'Arrived or moving via wheelchair.'],
            ['code' => 'STR', 'name' => 'Stretcher',  'description' => 'Arrived or moving via stretcher/gurney.'],
            ['code' => 'CRI', 'name' => 'Carried In', 'description' => 'Carried into the ED.'],
        ];

        foreach ($mobilityStatuses as $ms) {
            ErMobilityStatus::withoutGlobalScopes()->updateOrCreate(
                ['code' => $ms['code']],
                [
                    'name'         => $ms['name'],
                    'description'  => $ms['description'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $superadmin?->id,
                    'created_date' => $now,
                    'updated_by'   => $superadmin?->id,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($mobilityStatuses) . ' ER mobility statuses');
    }
}
