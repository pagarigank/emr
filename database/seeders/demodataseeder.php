<?php

namespace Database\Seeders;

use App\Models\Delivery\DeliveryComplication;
use App\Models\Delivery\DeliveryType;
use App\Models\Department;
use App\Models\Icu\IcuBed;
use App\Models\Icu\IcuWard;
use App\Models\MICU\MicuAssessmentParameter;
use App\Models\Procedures\Procedure;
use App\Models\Procedures\ProcedureCategory;
use App\Models\Radiology\RadiologyBodyPart;
use App\Models\Radiology\RadiologyExamType;
use App\Models\Radiology\RadiologyModality;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Opd\OpdClinic;
use App\Models\Opd\OpdComplaint;
use App\Models\Opd\OpdConsultationType;
use App\Models\Opd\OpdReferralSource;
use App\Models\Opd\OpdService;
use App\Models\Ob\ObContraceptiveMethod;
use App\Models\Ob\ObFetalPresentation;
use App\Models\Ob\ObVisitType;
use App\Models\Opd\OpdVisitType;

use App\Models\Supply\Item;
use App\Models\Supply\ItemCategory;
use App\Models\Supply\StockLocation;
use App\Models\Supply\Supplier;
use App\Models\Supply\SupplyUnit;
use App\Models\Ward\Ward;
use App\Models\Ward\WardRoom;
use App\Models\Ward\WardRoomType;
use App\Models\Pharmacy\Drug;
use App\Models\Pharmacy\DrugBatch;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant     = Tenant::withoutGlobalScopes()->where('tenant_code', 'DEMO01')->first();
        $superadmin = User::withoutGlobalScopes()->where('role', 'superadmin')->first();
        $now        = now();

        if (! $tenant) {
            $this->command->warn('⚠️  No DEMO01 tenant found. Skipping demo data.');
            return;
        }

        $createdBy = $superadmin?->id;

        // ── Departments ─────────────────────────────────────────────────────────
        $depts = [
            ['dept_code' => 'CARDIO', 'dept_name' => 'Cardiology',             'description' => 'Heart and cardiovascular services'],
            ['dept_code' => 'ER',     'dept_name' => 'Emergency Room',         'description' => 'Emergency and trauma services'],
            ['dept_code' => 'OPTH',   'dept_name' => 'Ophthalmology',          'description' => 'Eye care and surgery'],
            ['dept_code' => 'ORTHO',  'dept_name' => 'Orthopedics',            'description' => 'Bone and joint services'],
            ['dept_code' => 'PEDIA',  'dept_name' => 'Pediatrics',             'description' => 'Child health services'],
            ['dept_code' => 'RAD',    'dept_name' => 'Radiology',              'description' => 'Diagnostic imaging and radiology'],
            ['dept_code' => 'NEPHRO', 'dept_name' => 'Nephrology',             'description' => 'Kidney care and dialysis services'],
            ['dept_code' => 'PULMO',  'dept_name' => 'Pulmonology',            'description' => 'Respiratory and lung services'],
            ['dept_code' => 'NEURO',  'dept_name' => 'Neurology',              'description' => 'Brain and nervous system services'],
            ['dept_code' => 'GS',     'dept_name' => 'General Surgery',        'description' => 'General surgical services'],
        ];

        foreach ($depts as $d) {
            Department::withoutGlobalScopes()->updateOrCreate(
                ['dept_code' => $d['dept_code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'    => $tenant->id,
                    'dept_name'    => $d['dept_name'],
                    'description'  => $d['description'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($depts) . ' departments');

        // ── ICU Wards ──────────────────────────────────────────────────────────
        $wards = [
            ['code' => 'MICU', 'name' => 'Medical ICU',     'location' => '2nd Floor, Wing A'],
            ['code' => 'SICU', 'name' => 'Surgical ICU',     'location' => '2nd Floor, Wing B'],
            ['code' => 'PICU', 'name' => 'Pediatric ICU',    'location' => '3rd Floor, Wing A'],
            ['code' => 'NICU', 'name' => 'Neonatal ICU',     'location' => '3rd Floor, Wing B'],
            ['code' => 'CCU',  'name' => 'Cardiac Care Unit', 'location' => '2nd Floor, Wing C'],
        ];

        foreach ($wards as $w) {
            IcuWard::withoutGlobalScopes()->updateOrCreate(
                ['code' => $w['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'    => $tenant->id,
                    'name'         => $w['name'],
                    'location'     => $w['location'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($wards) . ' ICU wards');

        // ── ICU Beds ───────────────────────────────────────────────────────────
        $wardMap = IcuWard::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->pluck('id', 'code');

        $bedData = [
            ['ward' => 'MICU', 'beds' => ['MICU-01', 'MICU-02', 'MICU-03', 'MICU-04', 'MICU-05']],
            ['ward' => 'SICU', 'beds' => ['SICU-01', 'SICU-02', 'SICU-03', 'SICU-04']],
            ['ward' => 'PICU', 'beds' => ['PICU-01', 'PICU-02', 'PICU-03']],
            ['ward' => 'NICU', 'beds' => ['NICU-01', 'NICU-02', 'NICU-03', 'NICU-04', 'NICU-05', 'NICU-06']],
            ['ward' => 'CCU',  'beds' => ['CCU-01', 'CCU-02', 'CCU-03', 'CCU-04']],
        ];

        $bedCount = 0;
        foreach ($bedData as $group) {
            $wardId = $wardMap[$group['ward']] ?? null;
            foreach ($group['beds'] as $code) {
                IcuBed::withoutGlobalScopes()->updateOrCreate(
                    ['code' => $code, 'tenant_id' => $tenant->id],
                    [
                        'tenant_id'    => $tenant->id,
                        'name'         => 'Bed ' . $code,
                        'ward_id'      => $wardId,
                        'is_active'    => true,
                        'history'      => 1,
                        'created_by'   => $createdBy,
                        'created_date' => $now,
                        'updated_by'   => $createdBy,
                        'updated_date' => null,
                    ]
                );
                $bedCount++;
            }
        }

        $this->command->info('✅  Seeded ' . $bedCount . ' ICU beds');

        // ── Procedures ─────────────────────────────────────────────────────────
        $catMap   = ProcedureCategory::withoutGlobalScopes()->pluck('id', 'code');
        $deptMap  = Department::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->pluck('id', 'dept_code');

        $procedures = [
            // Diagnostic
            ['code' => 'CATH',    'name' => 'Cardiac Catheterization',            'desc' => 'Coronary angiography',                'cat' => 'DX',  'dept' => 'CARDIO', 'charge' => 15000.00],
            ['code' => 'ECHO',    'name' => 'Echocardiogram',                     'desc' => 'Transthoracic echocardiography',       'cat' => 'DX',  'dept' => 'CARDIO', 'charge' => 5000.00],
            ['code' => 'ECG',     'name' => 'Electrocardiogram',                  'desc' => '12-lead ECG',                         'cat' => 'DX',  'dept' => 'CARDIO', 'charge' => 800.00],
            ['code' => 'CT-SCAN', 'name' => 'CT Scan',                            'desc' => 'Computed tomography (any region)',     'cat' => 'DX',  'dept' => 'RAD',   'charge' => 7000.00],
            ['code' => 'MRI',     'name' => 'Magnetic Resonance Imaging',         'desc' => 'MRI scan (any region)',                'cat' => 'DX',  'dept' => 'RAD',   'charge' => 12000.00],
            ['code' => 'XRAY',    'name' => 'X-Ray',                              'desc' => 'Plain radiography (any view)',         'cat' => 'DX',  'dept' => 'RAD',   'charge' => 500.00],
            // Surgical
            ['code' => 'CABG',    'name' => 'Coronary Artery Bypass Grafting',   'desc' => 'Open heart bypass surgery',            'cat' => 'SX',  'dept' => 'CARDIO', 'charge' => 350000.00],
            ['code' => 'APPY',    'name' => 'Appendectomy',                       'desc' => 'Open or laparoscopic appendectomy',     'cat' => 'SX',  'dept' => 'GS',    'charge' => 45000.00],
            ['code' => 'CHOLEC',  'name' => 'Cholecystectomy',                    'desc' => 'Laparoscopic gallbladder removal',      'cat' => 'SX',  'dept' => 'GS',    'charge' => 60000.00],
            ['code' => 'ORIF',    'name' => 'Open Reduction Internal Fixation',   'desc' => 'Fracture fixation with hardware',       'cat' => 'SX',  'dept' => 'ORTHO',  'charge' => 80000.00],
            ['code' => 'TKR',     'name' => 'Total Knee Replacement',             'desc' => 'Total knee arthroplasty',               'cat' => 'SX',  'dept' => 'ORTHO',  'charge' => 250000.00],
            // Renal / Dialysis (Nephrology)
            ['code' => 'HD-SESS', 'name' => 'Hemodialysis Session',               'desc' => 'Standard hemodialysis session (4 hrs)', 'cat' => 'MD',  'dept' => 'NEPHRO', 'charge' => 4500.00],
            ['code' => 'PD-CATH', 'name' => 'Peritoneal Dialysis Catheter Insertion', 'desc' => 'PD catheter placement',            'cat' => 'SX',  'dept' => 'NEPHRO', 'charge' => 35000.00],
            ['code' => 'AVF',     'name' => 'AV Fistula Creation',                'desc' => 'Arteriovenous fistula for dialysis',    'cat' => 'SX',  'dept' => 'NEPHRO', 'charge' => 40000.00],
            // Radiology / Imaging
            ['code' => 'US-PEL',  'name' => 'Pelvic Ultrasound',                  'desc' => 'Ultrasound of pelvis',                  'cat' => 'RX',  'dept' => 'RAD',   'charge' => 2500.00],
            ['code' => 'MAMMO',   'name' => 'Mammogram',                          'desc' => 'Screening mammography',                'cat' => 'RX',  'dept' => 'RAD',   'charge' => 3000.00],
            // Rehabilitation
            ['code' => 'PT-SESS', 'name' => 'Physical Therapy Session',          'desc' => '1-hour PT session',                     'cat' => 'REH', 'dept' => 'ORTHO',  'charge' => 1500.00],
            ['code' => 'OT-SESS', 'name' => 'Occupational Therapy Session',      'desc' => '1-hour OT session',                     'cat' => 'REH', 'dept' => 'ORTHO',  'charge' => 1500.00],
            // Preventive
            ['code' => 'PE-PKG',  'name' => 'Annual Physical Exam Package',      'desc' => 'Comprehensive annual physical',         'cat' => 'PREV','dept' => 'GS',    'charge' => 3500.00],
            ['code' => 'VAC-AD',  'name' => 'Adult Vaccination',                 'desc' => 'Adult immunization (per dose)',         'cat' => 'PREV','dept' => 'GS',    'charge' => 1200.00],
        ];

        foreach ($procedures as $p) {
            Procedure::withoutGlobalScopes()->updateOrCreate(
                ['code' => $p['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'      => $tenant->id,
                    'name'           => $p['name'],
                    'description'    => $p['desc'],
                    'category_id'    => $catMap[$p['cat']] ?? null,
                    'department_id'  => $deptMap[$p['dept']] ?? null,
                    'standard_charge'=> $p['charge'],
                    'is_active'      => true,
                    'history'        => 1,
                    'created_by'     => $createdBy,
                    'created_date'   => $now,
                    'updated_by'     => $createdBy,
                    'updated_date'   => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($procedures) . ' procedures');

        // ── Radiology Modalities ───────────────────────────────────────────────
        $modalities = [
            ['code' => 'XR',   'name' => 'X-Ray',               'desc' => 'Plain radiography and fluoroscopy'],
            ['code' => 'CT',   'name' => 'CT Scan',             'desc' => 'Computed tomography'],
            ['code' => 'MRI',  'name' => 'Magnetic Resonance Imaging', 'desc' => 'MRI and MRA'],
            ['code' => 'US',   'name' => 'Ultrasound',          'desc' => 'Diagnostic ultrasound and echo'],
            ['code' => 'MAMMO','name' => 'Mammography',         'desc' => 'Breast imaging and screening'],
            ['code' => 'NM',   'name' => 'Nuclear Medicine',    'desc' => 'Nuclear medicine and PET'],
            ['code' => 'DEXA', 'name' => 'DEXA Scan',           'desc' => 'Bone densitometry'],
            ['code' => 'ANGIO','name' => 'Angiography',         'desc' => 'Interventional angiography'],
        ];

        foreach ($modalities as $m) {
            RadiologyModality::withoutGlobalScopes()->updateOrCreate(
                ['code' => $m['code']],
                [
                    'name'         => $m['name'],
                    'description'  => $m['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($modalities) . ' radiology modalities');

        // ── Radiology Body Parts ───────────────────────────────────────────────
        $bodyParts = [
            ['code' => 'CHEST',  'name' => 'Chest',        'desc' => 'Thorax, lungs, heart, ribs'],
            ['code' => 'ABD',    'name' => 'Abdomen',      'desc' => 'Abdomen and pelvis'],
            ['code' => 'HEAD',   'name' => 'Head & Neck',  'desc' => 'Skull, brain, cervical spine, neck'],
            ['code' => 'CSPINE', 'name' => 'Cervical Spine','desc' => 'Cervical vertebrae and discs'],
            ['code' => 'TSPINE', 'name' => 'Thoracic Spine','desc' => 'Thoracic vertebrae and discs'],
            ['code' => 'LSPINE', 'name' => 'Lumbar Spine', 'desc' => 'Lumbar vertebrae and discs'],
            ['code' => 'UPPER',  'name' => 'Upper Extremity','desc' => 'Shoulder, arm, elbow, forearm, wrist, hand'],
            ['code' => 'LOWER',  'name' => 'Lower Extremity','desc' => 'Hip, thigh, knee, leg, ankle, foot'],
            ['code' => 'PELVIS', 'name' => 'Pelvis',       'desc' => 'Pelvic bones and hips'],
            ['code' => 'BREAST', 'name' => 'Breast',       'desc' => 'Breast tissue (mammography)'],
        ];

        foreach ($bodyParts as $bp) {
            RadiologyBodyPart::withoutGlobalScopes()->updateOrCreate(
                ['code' => $bp['code']],
                [
                    'name'         => $bp['name'],
                    'description'  => $bp['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($bodyParts) . ' radiology body parts');

        // ── Radiology Exam Types ───────────────────────────────────────────────
        $modMap    = RadiologyModality::withoutGlobalScopes()->pluck('id', 'code');
        $bodyMap   = RadiologyBodyPart::withoutGlobalScopes()->pluck('id', 'code');
        $radDeptId = $deptMap['RAD'] ?? null;

        $examTypes = [
            ['code' => 'CXR-PA',    'name' => 'Chest X-Ray PA',                     'mod' => 'XR', 'bp' => 'CHEST', 'charge' => 500.00,  'tat' => 30,  'prep' => null,                          'contrast' => null],
            ['code' => 'CXR-AP',    'name' => 'Chest X-Ray AP',                     'mod' => 'XR', 'bp' => 'CHEST', 'charge' => 500.00,  'tat' => 30,  'prep' => null,                          'contrast' => null],
            ['code' => 'CT-BRAIN',  'name' => 'CT Brain Non-Contrast',              'mod' => 'CT', 'bp' => 'HEAD',  'charge' => 4500.00, 'tat' => 60,  'prep' => null,                          'contrast' => null],
            ['code' => 'CT-BRAIN-C','name' => 'CT Brain with Contrast',             'mod' => 'CT', 'bp' => 'HEAD',  'charge' => 6500.00, 'tat' => 60,  'prep' => 'NPO 4 hours',                 'contrast' => 'IV Contrast'],
            ['code' => 'CT-ABD',    'name' => 'CT Abdomen Non-Contrast',            'mod' => 'CT', 'bp' => 'ABD',   'charge' => 6000.00, 'tat' => 60,  'prep' => 'NPO 6 hours',                 'contrast' => null],
            ['code' => 'CT-ABD-C',  'name' => 'CT Abdomen with Contrast',           'mod' => 'CT', 'bp' => 'ABD',   'charge' => 8000.00, 'tat' => 90,  'prep' => 'NPO 6 hours',                 'contrast' => 'IV + Oral'],
            ['code' => 'MRI-BRAIN', 'name' => 'MRI Brain Non-Contrast',             'mod' => 'MRI','bp' => 'HEAD',  'charge' => 8500.00, 'tat' => 120, 'prep' => null,                          'contrast' => null],
            ['code' => 'MRI-BRAIN-C','name' => 'MRI Brain with Contrast',           'mod' => 'MRI','bp' => 'HEAD',  'charge' => 11000.00,'tat' => 120, 'prep' => 'NPO 4 hours, check GFR',      'contrast' => 'IV Contrast'],
            ['code' => 'MRI-LSPINE','name' => 'MRI Lumbar Spine',                   'mod' => 'MRI','bp' => 'LSPINE','charge' => 9000.00, 'tat' => 90,  'prep' => null,                          'contrast' => null],
            ['code' => 'US-ABD',    'name' => 'Ultrasound Abdomen',                 'mod' => 'US', 'bp' => 'ABD',   'charge' => 2500.00, 'tat' => 45,  'prep' => 'NPO 6 hours',                 'contrast' => null],
            ['code' => 'US-PELVIC', 'name' => 'Ultrasound Pelvis',                  'mod' => 'US', 'bp' => 'PELVIS','charge' => 2500.00, 'tat' => 45,  'prep' => 'Full bladder',                 'contrast' => null],
            ['code' => 'MAMMO-SCR', 'name' => 'Screening Mammogram',                'mod' => 'MAMMO','bp' => 'BREAST','charge' => 2500.00,'tat' => 30,  'prep' => null,                          'contrast' => null],
            ['code' => 'MAMMO-DX',  'name' => 'Diagnostic Mammogram',               'mod' => 'MAMMO','bp' => 'BREAST','charge' => 3500.00,'tat' => 45,  'prep' => null,                          'contrast' => null],
            ['code' => 'XR-EXT-U',  'name' => 'X-Ray Upper Extremity',              'mod' => 'XR', 'bp' => 'UPPER',  'charge' => 400.00,  'tat' => 20,  'prep' => null,                          'contrast' => null],
            ['code' => 'XR-EXT-L',  'name' => 'X-Ray Lower Extremity',              'mod' => 'XR', 'bp' => 'LOWER',  'charge' => 400.00,  'tat' => 20,  'prep' => null,                          'contrast' => null],
            ['code' => 'XR-LSPINE', 'name' => 'X-Ray Lumbar Spine',                 'mod' => 'XR', 'bp' => 'LSPINE','charge' => 450.00,  'tat' => 20,  'prep' => null,                          'contrast' => null],
            ['code' => 'DEXA-HIP',  'name' => 'DEXA Scan Hip',                      'mod' => 'DEXA','bp' => 'PELVIS','charge' => 2000.00, 'tat' => 30,  'prep' => null,                          'contrast' => null],
        ];

        foreach ($examTypes as $e) {
            RadiologyExamType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $e['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'       => $tenant->id,
                    'name'            => $e['name'],
                    'modality_id'     => $modMap[$e['mod']] ?? null,
                    'body_part_id'    => $bodyMap[$e['bp']] ?? null,
                    'department_id'   => $radDeptId,
                    'standard_charge' => $e['charge'],
                    'tat_minutes'     => $e['tat'],
                    'preparation'     => $e['prep'],
                    'contrast_type'   => $e['contrast'],
                    'is_active'       => true,
                    'history'         => 1,
                    'created_by'      => $createdBy,
                    'created_date'    => $now,
                    'updated_by'      => $createdBy,
                    'updated_date'    => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($examTypes) . ' radiology exam types');

        // ── MICU Assessment Parameters ─────────────────────────────────────────
        $parameters = [
            ['code' => 'HR',     'name' => 'Heart Rate',              'desc' => 'Number of heartbeats per minute',                'cat' => 'Vital Signs', 'unit' => 'bpm'],
            ['code' => 'BP_SYS', 'name' => 'Systolic Blood Pressure', 'desc' => 'Systolic arterial pressure',                      'cat' => 'Vital Signs', 'unit' => 'mmHg'],
            ['code' => 'BP_DIA', 'name' => 'Diastolic Blood Pressure','desc' => 'Diastolic arterial pressure',                     'cat' => 'Vital Signs', 'unit' => 'mmHg'],
            ['code' => 'MAP',    'name' => 'Mean Arterial Pressure',  'desc' => 'Mean arterial pressure',                          'cat' => 'Vital Signs', 'unit' => 'mmHg'],
            ['code' => 'RR',     'name' => 'Respiratory Rate',        'desc' => 'Breaths per minute',                              'cat' => 'Vital Signs', 'unit' => 'breaths/min'],
            ['code' => 'O2_SAT', 'name' => 'Oxygen Saturation',       'desc' => 'Peripheral capillary oxygen saturation',          'cat' => 'Vital Signs', 'unit' => '%'],
            ['code' => 'TEMP',   'name' => 'Temperature',             'desc' => 'Body temperature',                                'cat' => 'Vital Signs', 'unit' => '°C'],
            ['code' => 'GCS',    'name' => 'Glasgow Coma Scale',      'desc' => 'Level of consciousness (Eye/Verbal/Motor)',       'cat' => 'Neuro',       'unit' => ''],
            ['code' => 'ICP',    'name' => 'Intracranial Pressure',   'desc' => 'Pressure inside the skull',                       'cat' => 'Neuro',       'unit' => 'mmHg'],
            ['code' => 'CVP',    'name' => 'Central Venous Pressure', 'desc' => 'Pressure in the vena cava',                       'cat' => 'Hemodynamic', 'unit' => 'mmHg'],
            ['code' => 'CO',     'name' => 'Cardiac Output',          'desc' => 'Volume of blood pumped by the heart per minute',  'cat' => 'Hemodynamic', 'unit' => 'L/min'],
            ['code' => 'UOP',    'name' => 'Urine Output',            'desc' => 'Hourly urine output',                             'cat' => 'Renal',       'unit' => 'mL/hr'],
            ['code' => 'BS',     'name' => 'Blood Sugar',             'desc' => 'Capillary blood glucose level',                   'cat' => 'Metabolic',   'unit' => 'mg/dL'],
        ];

        foreach ($parameters as $p) {
            MicuAssessmentParameter::withoutGlobalScopes()->updateOrCreate(
                ['code' => $p['code']],
                [
                    'name'         => $p['name'],
                    'description'  => $p['desc'],
                    'category'     => $p['cat'],
                    'default_unit' => $p['unit'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($parameters) . ' MICU assessment parameters');

        // ── Delivery Types ─────────────────────────────────────────────────────
        $delTypes = [
            ['code' => 'NSVD',  'name' => 'Normal Spontaneous Vaginal Delivery',            'desc' => 'Full-term vaginal delivery without instrumentation'],
            ['code' => 'AVB',   'name' => 'Assisted Vaginal Birth',                          'desc' => 'Vaginal delivery with forceps or vacuum assistance'],
            ['code' => 'C-S_EL','name' => 'Cesarean Section (Elective)',                     'desc' => 'Planned surgical delivery through abdominal incision'],
            ['code' => 'C-S_EM','name' => 'Cesarean Section (Emergency)',                    'desc' => 'Emergency surgical delivery due to maternal/fetal distress'],
            ['code' => 'VBAC',  'name' => 'Vaginal Birth After Cesarean',                    'desc' => 'Vaginal delivery after previous cesarean section'],
        ];

        foreach ($delTypes as $d) {
            DeliveryType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $d['code']],
                [
                    'name'         => $d['name'],
                    'description'  => $d['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($delTypes) . ' delivery types');

        // ── Delivery Complications ─────────────────────────────────────────────
        $delComps = [
            ['code' => 'PPH',        'name' => 'Postpartum Hemorrhage',                    'desc' => 'Excessive bleeding after childbirth (>500 mL vaginal, >1000 mL C/S)'],
            ['code' => 'PREECLAMPSIA','name' => 'Preeclampsia/Eclampsia',                   'desc' => 'Hypertensive disorder of pregnancy with proteinuria'],
            ['code' => 'GDM',        'name' => 'Gestational Diabetes Mellitus',             'desc' => 'Glucose intolerance first recognized during pregnancy'],
            ['code' => 'CORD_PRO',   'name' => 'Cord Prolapse',                             'desc' => 'Umbilical cord precedes fetal presenting part'],
            ['code' => 'SHOULDER',   'name' => 'Shoulder Dystocia',                         'desc' => 'Fetal shoulder impaction after head delivery'],
            ['code' => 'PLAC_PREVIA','name' => 'Placenta Previa',                           'desc' => 'Placenta covers the cervical os'],
            ['code' => 'PLAC_ABRUPT','name' => 'Placental Abruption',                       'desc' => 'Premature separation of placenta from uterine wall'],
            ['code' => 'PROM',       'name' => 'Premature Rupture of Membranes',            'desc' => 'Rupture of amniotic sac before labor onset'],
            ['code' => 'FETAL_DIST', 'name' => 'Fetal Distress',                            'desc' => 'Fetal compromise indicated by abnormal heart rate patterns'],
            ['code' => 'UTERINE_RUP','name' => 'Uterine Rupture',                           'desc' => 'Complete disruption of all uterine wall layers'],
        ];

        foreach ($delComps as $dc) {
            DeliveryComplication::withoutGlobalScopes()->updateOrCreate(
                ['code' => $dc['code']],
                [
                    'name'         => $dc['name'],
                    'description'  => $dc['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($delComps) . ' delivery complications');

        // ── Ward Room Types ─────────────────────────────────────────────────────
        $roomTypes = [
            ['code' => 'PRIVATE',      'name' => 'Private Room',       'desc' => 'Single-patient room with private bathroom'],
            ['code' => 'SEMI_PRIVATE', 'name' => 'Semi-Private Room',  'desc' => 'Two-patient room with shared bathroom'],
            ['code' => 'WARD',         'name' => 'Ward',               'desc' => 'Multi-patient room (4-8 beds)'],
            ['code' => 'SUITE',        'name' => 'Suite',              'desc' => 'Premium room with lounge area and amenities'],
            ['code' => 'ISOLATION',    'name' => 'Isolation Room',     'desc' => 'Negative/positive pressure room for infection control'],
        ];

        foreach ($roomTypes as $rt) {
            WardRoomType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $rt['code']],
                [
                    'name'         => $rt['name'],
                    'description'  => $rt['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($roomTypes) . ' ward room types');

        // ── Wards ───────────────────────────────────────────────────────────────
        $wards = [
            ['code' => 'GEN-MED',  'name' => 'General Medicine Ward',   'desc' => 'General internal medicine ward',          'loc' => '2F Building A'],
            ['code' => 'CARDIO',   'name' => 'Cardiology Ward',         'desc' => 'Cardiology and cardiovascular ward',      'loc' => '3F Building A'],
            ['code' => 'PEDIA',    'name' => 'Pediatrics Ward',        'desc' => 'Children and adolescent ward',            'loc' => '4F Building A'],
            ['code' => 'SURGERY',  'name' => 'Surgical Ward',          'desc' => 'General and specialized surgical ward',   'loc' => '5F Building A'],
            ['code' => 'ORTHO',    'name' => 'Orthopedics Ward',       'desc' => 'Orthopedic and trauma ward',              'loc' => '5F Building A'],
            ['code' => 'OB-GYNE',  'name' => 'OB-Gyne Ward',           'desc' => 'Obstetrics and gynecology ward',          'loc' => '3F Building B'],
            ['code' => 'PULMO',    'name' => 'Pulmonology Ward',       'desc' => 'Pulmonary and respiratory ward',          'loc' => '2F Building B'],
            ['code' => 'NEURO',    'name' => 'Neurology Ward',         'desc' => 'Neurology and neurosurgery ward',         'loc' => '4F Building B'],
        ];

        foreach ($wards as $w) {
            Ward::withoutGlobalScopes()->updateOrCreate(
                ['code' => $w['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'    => $tenant->id,
                    'name'         => $w['name'],
                    'description'  => $w['desc'],
                    'location'     => $w['loc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($wards) . ' wards');

        // ── Ward Rooms ─────────────────────────────────────────────────────────
        $roomTypeMap = WardRoomType::withoutGlobalScopes()->pluck('id', 'code');
        $wardMap     = Ward::withoutGlobalScopes()->where('tenant_id', $tenant->id)->pluck('id', 'code');

        $wardRooms = [
            ['code' => 'W-101', 'name' => 'Ward Room 101', 'wrd' => 'CARDIO', 'rt' => 'PRIVATE',      'dept' => 'CARDIO', 'cap' => 1, 'rate' => 2500.00, 'extra' => 750.00,  'disc' => null],
            ['code' => 'W-102', 'name' => 'Ward Room 102', 'wrd' => 'CARDIO', 'rt' => 'PRIVATE',      'dept' => 'CARDIO', 'cap' => 1, 'rate' => 2500.00, 'extra' => 750.00,  'disc' => null],
            ['code' => 'W-103', 'name' => 'Ward Room 103', 'wrd' => 'CARDIO', 'rt' => 'SEMI_PRIVATE', 'dept' => 'CARDIO', 'cap' => 2, 'rate' => 1500.00, 'extra' => 500.00,  'disc' => null],
            ['code' => 'W-201', 'name' => 'Ward Room 201', 'wrd' => 'SURGERY','rt' => 'WARD',         'dept' => 'GS',     'cap' => 6, 'rate' => 800.00,  'extra' => 350.00,  'disc' => 10.00],
            ['code' => 'W-202', 'name' => 'Ward Room 202', 'wrd' => 'SURGERY','rt' => 'WARD',         'dept' => 'GS',     'cap' => 6, 'rate' => 800.00,  'extra' => 350.00,  'disc' => 10.00],
            ['code' => 'W-301', 'name' => 'Ward Room 301', 'wrd' => 'PEDIA',  'rt' => 'PRIVATE',      'dept' => 'PEDIA',  'cap' => 1, 'rate' => 2000.00, 'extra' => 600.00,  'disc' => null],
            ['code' => 'W-302', 'name' => 'Ward Room 302', 'wrd' => 'PEDIA',  'rt' => 'SEMI_PRIVATE', 'dept' => 'PEDIA',  'cap' => 2, 'rate' => 1200.00, 'extra' => 400.00,  'disc' => null],
            ['code' => 'W-401', 'name' => 'Ward Room 401', 'wrd' => 'NEURO',  'rt' => 'SUITE',        'dept' => 'NEURO',  'cap' => 1, 'rate' => 5000.00, 'extra' => 1500.00, 'disc' => null],
            ['code' => 'W-402', 'name' => 'Ward Room 402', 'wrd' => 'PULMO',  'rt' => 'ISOLATION',    'dept' => 'PULMO',  'cap' => 1, 'rate' => 3000.00, 'extra' => 1000.00, 'disc' => null],
            ['code' => 'W-403', 'name' => 'Ward Room 403', 'wrd' => 'PULMO',  'rt' => 'ISOLATION',    'dept' => 'PULMO',  'cap' => 1, 'rate' => 3000.00, 'extra' => 1000.00, 'disc' => null],
        ];

        foreach ($wardRooms as $wr) {
            WardRoom::withoutGlobalScopes()->updateOrCreate(
                ['code' => $wr['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'      => $tenant->id,
                    'name'           => $wr['name'],
                    'ward_id'        => $wardMap[$wr['wrd']] ?? null,
                    'room_type_id'   => $roomTypeMap[$wr['rt']] ?? null,
                    'department_id'  => $deptMap[$wr['dept']] ?? null,
                    'capacity'       => $wr['cap'],
                    'room_rate'      => $wr['rate'],
                    'extra_bed_rate' => $wr['extra'],
                    'discount_rate'  => $wr['disc'],
                    'is_active'      => true,
                    'history'        => 1,
                    'created_by'     => $createdBy,
                    'created_date'   => $now,
                    'updated_by'     => $createdBy,
                    'updated_date'   => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($wardRooms) . ' ward rooms');

        // ── OPD Visit Types ────────────────────────────────────────────────────
        $visitTypes = [
            ['code' => 'BOOKING',  'name' => 'Booking Visit',             'desc' => 'Initial patient registration and history taking'],
            ['code' => 'GEN-CON',  'name' => 'General Consultation',      'desc' => 'General medical consultation with a physician'],
            ['code' => 'SPEC-CON', 'name' => 'Specialist Consultation',   'desc' => 'Consultation with a specialist physician'],
            ['code' => 'FOLLOW-UP','name' => 'Follow-up Visit',           'desc' => 'Return visit for follow-up of an existing condition'],
            ['code' => 'EMERG',    'name' => 'Emergency Visit',           'desc' => 'Unscheduled visit for acute/emergency care'],
        ];

        foreach ($visitTypes as $vt) {
            OpdVisitType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $vt['code']],
                [
                    'name'         => $vt['name'],
                    'description'  => $vt['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($visitTypes) . ' OPD visit types');

        // ── OPD Consultation Types ─────────────────────────────────────────────
        $consultTypes = [
            ['code' => 'NEW-PT',   'name' => 'New Patient',     'desc' => 'First-time consultation for a new patient'],
            ['code' => 'FOLLOW-UP','name' => 'Follow-up',       'desc' => 'Routine follow-up for an established patient'],
            ['code' => 'ACUTE',    'name' => 'Acute',           'desc' => 'Acute/urgent consultation for new or worsening symptoms'],
            ['code' => 'CHRONIC',  'name' => 'Chronic',         'desc' => 'Ongoing management of a chronic condition'],
            ['code' => 'WELLNESS', 'name' => 'Wellness',        'desc' => 'Preventive health check and wellness visit'],
            ['code' => 'PRE-OP',   'name' => 'Pre-Operative',   'desc' => 'Pre-surgical evaluation and clearance'],
            ['code' => 'POST-OP',  'name' => 'Post-Operative',  'desc' => 'Post-surgical follow-up and recovery check'],
        ];

        foreach ($consultTypes as $ct) {
            OpdConsultationType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $ct['code']],
                [
                    'name'         => $ct['name'],
                    'description'  => $ct['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($consultTypes) . ' OPD consultation types');

        // ── OPD Referral Sources ───────────────────────────────────────────────
        $refSources = [
            ['code' => 'WALK-IN',      'name' => 'Walk-in',                    'desc' => 'Patient walked in without referral'],
            ['code' => 'PHYSICIAN-REF', 'name' => 'Physician Referral',        'desc' => 'Referred by an external physician'],
            ['code' => 'ER-TRANS',      'name' => 'ER Transfer',               'desc' => 'Transferred from the Emergency Room'],
            ['code' => 'HMO',           'name' => 'HMO/Insurance',             'desc' => 'Referred through HMO or insurance provider'],
            ['code' => 'COMPANY',       'name' => 'Company/Employer',          'desc' => 'Referred by company clinic or employer'],
            ['code' => 'OPD-REF',       'name' => 'Other OPD Department',     'desc' => 'Referred from another OPD department/specialty'],
            ['code' => 'EXT-HOSP',      'name' => 'External Hospital',         'desc' => 'Referred from another hospital or facility'],
        ];

        foreach ($refSources as $rs) {
            OpdReferralSource::withoutGlobalScopes()->updateOrCreate(
                ['code' => $rs['code']],
                [
                    'name'         => $rs['name'],
                    'description'  => $rs['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($refSources) . ' OPD referral sources');

        // ── OPD Services ───────────────────────────────────────────────────────
        $services = [
            ['code' => 'CONS-FEE',    'name' => 'Consultation Fee',             'desc' => 'Standard physician consultation fee',             'charge' => 500.00],
            ['code' => 'SPEC-CONS',   'name' => 'Specialist Consultation Fee',  'desc' => 'Specialist physician consultation fee',            'charge' => 800.00],
            ['code' => 'RE-CONS',     'name' => 'Repeat Consultation Fee',      'desc' => 'Follow-up/repeat consultation fee',                'charge' => 350.00],
            ['code' => 'INJ-FEE',     'name' => 'Injection Fee',                'desc' => 'Administration of injectable medication',          'charge' => 100.00],
            ['code' => 'NEB-FEE',     'name' => 'Nebulization Fee',             'desc' => 'Nebulization treatment administration',            'charge' => 150.00],
            ['code' => 'ECG-FEE',     'name' => 'ECG Service',                  'desc' => 'Electrocardiogram tracing and interpretation',    'charge' => 350.00],
            ['code' => 'DRSG-FEE',    'name' => 'Dressing/Changing',            'desc' => 'Wound dressing and bandage change',               'charge' => 200.00],
            ['code' => 'MINOR-SURG',  'name' => 'Minor Surgical Procedure',     'desc' => 'Minor OTD procedure (excision, suturing, etc.)',   'charge' => 1500.00],
            ['code' => 'MED-CERT',    'name' => 'Medical Certificate',          'desc' => 'Issuance of medical certificate',                  'charge' => 150.00],
        ];

        foreach ($services as $svc) {
            OpdService::withoutGlobalScopes()->updateOrCreate(
                ['code' => $svc['code']],
                [
                    'name'            => $svc['name'],
                    'description'     => $svc['desc'],
                    'standard_charge' => $svc['charge'],
                    'is_active'       => true,
                    'history'         => 1,
                    'created_by'      => $createdBy,
                    'created_date'    => $now,
                    'updated_by'      => $createdBy,
                    'updated_date'    => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($services) . ' OPD services');

        // ── OPD Clinics ────────────────────────────────────────────────────────
        $clinics = [
            ['code' => 'GEN-MED',  'name' => 'General Medicine Clinic',   'desc' => 'General internal medicine outpatient clinic'],
            ['code' => 'PEDIA',    'name' => 'Pediatrics Clinic',         'desc' => 'Child health and wellness outpatient clinic'],
            ['code' => 'OB-GYNE',  'name' => 'OB-Gyne Clinic',            'desc' => 'Obstetrics and gynecology outpatient clinic'],
            ['code' => 'HTN-DM',   'name' => 'Hypertension & Diabetes Clinic','desc' => 'Specialized clinic for HTN and DM management'],
            ['code' => 'WELL-BABY','name' => 'Well-Baby/Immunization Clinic','desc' => 'Well-baby checkups and immunization services'],
            ['code' => 'FAM-PLAN', 'name' => 'Family Planning Clinic',    'desc' => 'Family planning and reproductive health services'],
            ['code' => 'TB-DOTS',  'name' => 'TB-DOTS Clinic',            'desc' => 'TB directly observed treatment short-course clinic'],
            ['code' => 'NUTRITION','name' => 'Nutrition/Feeding Clinic',  'desc' => 'Nutritional assessment and feeding program clinic'],
        ];

        foreach ($clinics as $cl) {
            OpdClinic::withoutGlobalScopes()->updateOrCreate(
                ['code' => $cl['code']],
                [
                    'name'         => $cl['name'],
                    'description'  => $cl['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($clinics) . ' OPD clinics');

        // ── OPD Complaints ─────────────────────────────────────────────────────
        $complaints = [
            ['code' => 'FEVER',     'name' => 'Fever',              'desc' => 'Elevated body temperature (pyrexia)'],
            ['code' => 'COUGH',     'name' => 'Cough',              'desc' => 'Persistent or acute cough'],
            ['code' => 'HEADACHE',  'name' => 'Headache',           'desc' => 'Head pain or cephalalgia'],
            ['code' => 'ABD-PAIN',  'name' => 'Abdominal Pain',     'desc' => 'Pain or discomfort in the abdominal area'],
            ['code' => 'BODY-PAIN', 'name' => 'Body Pain / Myalgia','desc' => 'Generalized body aches and muscle pain'],
            ['code' => 'COLD',      'name' => 'Colds / Coryza',     'desc' => 'Common cold symptoms with nasal congestion'],
            ['code' => 'SORE-THROAT','name' => 'Sore Throat',       'desc' => 'Pharyngeal pain or irritation'],
            ['code' => 'DIZZINESS', 'name' => 'Dizziness',          'desc' => 'Vertigo or lightheadedness'],
            ['code' => 'DIARRHEA',  'name' => 'Diarrhea',           'desc' => 'Loose or watery bowel movements'],
            ['code' => 'VOMITING',  'name' => 'Vomiting',           'desc' => 'Emesis or regurgitation'],
            ['code' => 'CHEST-PAIN','name' => 'Chest Pain',         'desc' => 'Pain or tightness in the chest area'],
            ['code' => 'RASH',      'name' => 'Skin Rash',          'desc' => 'Skin eruption or cutaneous lesions'],
            ['code' => 'DYSPNEA',   'name' => 'Difficulty Breathing','desc' => 'Shortness of breath or dyspnea'],
            ['code' => 'FATIGUE',   'name' => 'Fatigue / Weakness', 'desc' => 'Generalized weakness or malaise'],
            ['code' => 'COUGH-2WK', 'name' => 'Cough > 2 Weeks',    'desc' => 'Prolonged cough lasting more than two weeks'],
        ];

        foreach ($complaints as $cp) {
            OpdComplaint::withoutGlobalScopes()->updateOrCreate(
                ['code' => $cp['code']],
                [
                    'name'         => $cp['name'],
                    'description'  => $cp['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($complaints) . ' OPD complaints');

        // ── OB Visit Types ──────────────────────────────────────────────────────
        $obVisitTypes = [
            ['code' => 'PRENATAL',  'name' => 'Prenatal Visit',           'desc' => 'Routine prenatal check-up during pregnancy'],
            ['code' => 'POSTNATAL', 'name' => 'Postnatal Visit',          'desc' => 'Post-delivery follow-up visit for mother'],
            ['code' => 'HIGH-RISK', 'name' => 'High-Risk Pregnancy Visit','desc' => 'Specialized visit for high-risk obstetric cases'],
            ['code' => 'TEEN-PREG', 'name' => 'Teen Pregnancy Visit',     'desc' => 'Prenatal care for adolescent pregnant patients'],
            ['code' => 'POST-ABRT', 'name' => 'Post-Abortion Care Visit',  'desc' => 'Follow-up care after abortion or miscarriage'],
        ];

        foreach ($obVisitTypes as $ovt) {
            ObVisitType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $ovt['code']],
                [
                    'name'         => $ovt['name'],
                    'description'  => $ovt['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($obVisitTypes) . ' OB visit types');

        // ── OB Fetal Presentations ──────────────────────────────────────────────
        $fetalPresentations = [
            ['code' => 'VERTEX',   'name' => 'Vertex (Cephalic)',          'desc' => 'Head-first presentation; most common (96% of births)'],
            ['code' => 'BREECH',   'name' => 'Breech',                     'desc' => 'Buttocks or feet-first presentation'],
            ['code' => 'TRANSVERSE','name' => 'Transverse Lie',            'desc' => 'Fetus lies horizontally across the uterus'],
            ['code' => 'FACE',     'name' => 'Face Presentation',          'desc' => 'Fetal face is the presenting part'],
            ['code' => 'BROW',     'name' => 'Brow Presentation',          'desc' => 'Fetal brow (forehead) is the presenting part'],
            ['code' => 'COMPOUND', 'name' => 'Compound Presentation',      'desc' => 'Fetal extremity presents alongside the presenting part'],
            ['code' => 'CORD',     'name' => 'Cord Presentation',          'desc' => 'Umbilical cord presents before the fetal part'],
        ];

        foreach ($fetalPresentations as $fp) {
            ObFetalPresentation::withoutGlobalScopes()->updateOrCreate(
                ['code' => $fp['code']],
                [
                    'name'         => $fp['name'],
                    'description'  => $fp['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($fetalPresentations) . ' OB fetal presentations');

        // ── OB Contraceptive Methods ────────────────────────────────────────────
        $contraceptives = [
            ['code' => 'COC',      'name' => 'Combined Oral Contraceptive',      'cat' => 'Hormonal',        'desc' => 'Daily pill containing estrogen and progestin'],
            ['code' => 'POP',      'name' => 'Progestin-Only Pill',              'cat' => 'Hormonal',        'desc' => 'Daily pill containing only progestin'],
            ['code' => 'DMPA',     'name' => 'DMPA (Depo-Provera)',              'cat' => 'Hormonal',        'desc' => 'Injectable progestin every 3 months'],
            ['code' => 'IMPLANT',  'name' => 'Subdermal Implant',                'cat' => 'Hormonal',        'desc' => 'Implantable rod effective for 3 years'],
            ['code' => 'IUD-CU',   'name' => 'Copper IUD (Non-Hormonal)',        'cat' => 'IUD',             'desc' => 'Copper intrauterine device effective for 10 years'],
            ['code' => 'IUD-LNG',  'name' => 'Levonorgestrel IUD (Mirena)',      'cat' => 'IUD',             'desc' => 'Hormonal intrauterine device effective for 5 years'],
            ['code' => 'CONDOM-M', 'name' => 'Male Condom',                      'cat' => 'Barrier',         'desc' => 'Latex sheath for male use'],
            ['code' => 'CONDOM-F', 'name' => 'Female Condom',                    'cat' => 'Barrier',         'desc' => 'Polyurethane sheath for female use'],
            ['code' => 'DIAPHRAGM','name' => 'Diaphragm',                        'cat' => 'Barrier',         'desc' => 'Reusable silicone dome with spermicide'],
            ['code' => 'BTL',      'name' => 'Bilateral Tubal Ligation',         'cat' => 'Permanent',       'desc' => 'Surgical female sterilization'],
            ['code' => 'VASECTOMY','name' => 'Vasectomy',                        'cat' => 'Permanent',       'desc' => 'Surgical male sterilization'],
            ['code' => 'LAM',      'name' => 'Lactational Amenorrhea Method',    'cat' => 'Natural',         'desc' => 'Breastfeeding-based temporary method'],
            ['code' => 'STM',      'name' => 'Standard Days Method',             'cat' => 'Natural',         'desc' => 'Calendar-based fertility tracking'],
            ['code' => 'ECP',      'name' => 'Emergency Contraceptive Pill',     'cat' => 'Emergency',       'desc' => 'Post-coital hormonal contraception'],
            ['code' => 'IUD-EC',   'name' => 'Copper IUD (Emergency)',           'cat' => 'Emergency',       'desc' => 'Post-coital IUD insertion within 5 days'],
        ];

        foreach ($contraceptives as $cm) {
            ObContraceptiveMethod::withoutGlobalScopes()->updateOrCreate(
                ['code' => $cm['code']],
                [
                    'name'         => $cm['name'],
                    'description'  => $cm['desc'],
                    'category'     => $cm['cat'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($contraceptives) . ' OB contraceptive methods');

        // ════════════════════════════════════════════════════════════════════════
        // LABORATORY SEEDING (sections, units, specimen types, tests, panels, requests)
        // ════════════════════════════════════════════════════════════════════════

        // ── Lab Sections ─────────────────────────────────────────────────────────
        $labSections = [
            ['code' => 'CHEM',  'name' => 'Clinical Chemistry',   'desc' => 'Blood chemistry and metabolic panel testing'],
            ['code' => 'HEMA',  'name' => 'Hematology',           'desc' => 'Complete blood count and coagulation studies'],
            ['code' => 'MICRO', 'name' => 'Microbiology',         'desc' => 'Culture, sensitivity, and infectious disease testing'],
            ['code' => 'SERO',  'name' => 'Serology / Immunology','desc' => 'Antibody and antigen testing'],
            ['code' => 'UA',    'name' => 'Urinalysis',           'desc' => 'Urine chemistry and microscopic examination'],
        ];

        $labSectionIds = [];
        foreach ($labSections as $ls) {
            $section = \App\Models\LabSection::withoutGlobalScopes()->updateOrCreate(
                ['code' => $ls['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'    => $tenant->id,
                    'name'         => $ls['name'],
                    'description'  => $ls['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
            $labSectionIds[$ls['code']] = $section->id;
        }
        $this->command->info('✅  Seeded ' . count($labSections) . ' lab sections');

        // ── Lab Units ────────────────────────────────────────────────────────────
        $labUnits = [
            ['code' => 'MG_DL',  'name' => 'mg/dL',  'cat' => 'Concentration'],
            ['code' => 'G_DL',   'name' => 'g/dL',   'cat' => 'Concentration'],
            ['code' => 'MMOL_L', 'name' => 'mmol/L', 'cat' => 'Concentration'],
            ['code' => 'U_L',    'name' => 'U/L',    'cat' => 'Enzyme Activity'],
            ['code' => 'X10_9',  'name' => 'x10^9/L','cat' => 'Cell Count'],
            ['code' => 'X10_12', 'name' => 'x10^12/L','cat' => 'Cell Count'],
            ['code' => 'FL',     'name' => 'fL',     'cat' => 'Red Cell Index'],
            ['code' => 'PG',     'name' => 'pg',     'cat' => 'Red Cell Index'],
            ['code' => 'PERCENT','name' => '%',      'cat' => 'Percentage'],
            ['code' => 'MM_HR',  'name' => 'mm/hr',  'cat' => 'Sedimentation'],
            ['code' => 'RATIO',  'name' => 'Ratio',  'cat' => 'Ratio'],
            ['code' => 'IU_ML',  'name' => 'IU/mL',  'cat' => 'Immunoassay'],
        ];

        $labUnitIds = [];
        foreach ($labUnits as $lu) {
            $unit = \App\Models\LabUnit::withoutGlobalScopes()->updateOrCreate(
                ['code' => $lu['code']],
                [
                    'name'         => $lu['name'],
                    'category'     => $lu['cat'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
            $labUnitIds[$lu['code']] = $unit->id;
        }
        $this->command->info('✅  Seeded ' . count($labUnits) . ' lab units');

        // ── Specimen Types ───────────────────────────────────────────────────────
        $specimenTypes = [
            ['code' => 'WB',   'name' => 'Whole Blood',           'container' => 'Lavender top (EDTA)'],
            ['code' => 'SERUM','name' => 'Serum',                  'container' => 'Red top (plain)'],
            ['code' => 'PLASMA','name' => 'Plasma',                'container' => 'Green top (Heparin)'],
            ['code' => 'URINE','name' => 'Urine',                  'container' => 'Urine collection cup'],
            ['code' => 'STOOL','name' => 'Stool',                  'container' => 'Stool collection container'],
            ['code' => 'CSF',  'name' => 'Cerebrospinal Fluid',    'container' => 'Sterile tube'],
            ['code' => 'SWAB', 'name' => 'Swab',                   'container' => 'Culture swab with transport media'],
        ];

        $specimenTypeIds = [];
        foreach ($specimenTypes as $st) {
            $sp = \App\Models\SpecimenType::withoutGlobalScopes()->updateOrCreate(
                ['code' => $st['code']],
                [
                    'name'         => $st['name'],
                    'description'  => $st['name'],
                    'container'    => $st['container'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
            $specimenTypeIds[$st['code']] = $sp->id;
        }
        $this->command->info('✅  Seeded ' . count($specimenTypes) . ' specimen types');

        // ── Lab Tests ────────────────────────────────────────────────────────────
        $labTests = [
            // Clinical Chemistry
            ['code' => 'GLU',    'name' => 'Glucose (FBS)',           'short' => 'FBS',     'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MG_DL',  'type' => 'Numeric',    'tat' => 60,  'charge' => 180.00],
            ['code' => 'CREA',   'name' => 'Creatinine',              'short' => 'CREA',    'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MG_DL',  'type' => 'Numeric',    'tat' => 60,  'charge' => 200.00],
            ['code' => 'BUN',    'name' => 'Blood Urea Nitrogen',     'short' => 'BUN',     'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MG_DL',  'type' => 'Numeric',    'tat' => 60,  'charge' => 180.00],
            ['code' => 'UA',     'name' => 'Uric Acid',               'short' => 'UA',      'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MG_DL',  'type' => 'Numeric',    'tat' => 60,  'charge' => 150.00],
            ['code' => 'CHOL',   'name' => 'Total Cholesterol',       'short' => 'CHOL',    'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MG_DL',  'type' => 'Numeric',    'tat' => 60,  'charge' => 200.00],
            ['code' => 'HDL',    'name' => 'HDL Cholesterol',         'short' => 'HDL',     'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MG_DL',  'type' => 'Numeric',    'tat' => 60,  'charge' => 220.00],
            ['code' => 'LDL',    'name' => 'LDL Cholesterol',         'short' => 'LDL',     'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MG_DL',  'type' => 'Numeric',    'tat' => 60,  'charge' => 220.00],
            ['code' => 'TG',     'name' => 'Triglycerides',           'short' => 'TG',      'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MG_DL',  'type' => 'Numeric',    'tat' => 60,  'charge' => 200.00],
            ['code' => 'ALT',    'name' => 'ALT (SGPT)',              'short' => 'ALT',     'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'U_L',    'type' => 'Numeric',    'tat' => 60,  'charge' => 200.00],
            ['code' => 'AST',    'name' => 'AST (SGOT)',              'short' => 'AST',     'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'U_L',    'type' => 'Numeric',    'tat' => 60,  'charge' => 200.00],
            ['code' => 'NA',     'name' => 'Sodium',                  'short' => 'Na',      'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MMOL_L', 'type' => 'Numeric',    'tat' => 60,  'charge' => 250.00],
            ['code' => 'K',      'name' => 'Potassium',               'short' => 'K',       'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MMOL_L', 'type' => 'Numeric',    'tat' => 60,  'charge' => 250.00],
            ['code' => 'CL',     'name' => 'Chloride',                'short' => 'Cl',      'section' => 'CHEM', 'specimen' => 'SERUM', 'unit' => 'MMOL_L', 'type' => 'Numeric',    'tat' => 60,  'charge' => 250.00],
            // Hematology
            ['code' => 'CBC',    'name' => 'Complete Blood Count',    'short' => 'CBC',     'section' => 'HEMA', 'specimen' => 'WB',    'unit' => null,     'type' => 'Numeric',    'tat' => 30,  'charge' => 350.00],
            ['code' => 'HGB',    'name' => 'Hemoglobin',              'short' => 'HGB',     'section' => 'HEMA', 'specimen' => 'WB',    'unit' => 'G_DL',   'type' => 'Numeric',    'tat' => 30,  'charge' => 150.00],
            ['code' => 'HCT',    'name' => 'Hematocrit',              'short' => 'HCT',     'section' => 'HEMA', 'specimen' => 'WB',    'unit' => 'PERCENT','type' => 'Numeric',    'tat' => 30,  'charge' => 150.00],
            ['code' => 'PLT',    'name' => 'Platelet Count',          'short' => 'PLT',     'section' => 'HEMA', 'specimen' => 'WB',    'unit' => 'X10_9',  'type' => 'Numeric',    'tat' => 30,  'charge' => 180.00],
            ['code' => 'WBC',    'name' => 'White Blood Cell Count',  'short' => 'WBC',     'section' => 'HEMA', 'specimen' => 'WB',    'unit' => 'X10_9',  'type' => 'Numeric',    'tat' => 30,  'charge' => 180.00],
            ['code' => 'RBC',    'name' => 'Red Blood Cell Count',    'short' => 'RBC',     'section' => 'HEMA', 'specimen' => 'WB',    'unit' => 'X10_12', 'type' => 'Numeric',    'tat' => 30,  'charge' => 180.00],
            ['code' => 'ESR',    'name' => 'Erythrocyte Sedimentation Rate', 'short' => 'ESR', 'section' => 'HEMA', 'specimen' => 'WB', 'unit' => 'MM_HR', 'type' => 'Numeric', 'tat' => 60,  'charge' => 120.00],
            ['code' => 'PT',     'name' => 'Prothrombin Time',        'short' => 'PT',      'section' => 'HEMA', 'specimen' => 'PLASMA', 'unit' => 'RATIO',  'type' => 'Numeric',    'tat' => 60,  'charge' => 250.00],
            ['code' => 'APTT',   'name' => 'Activated Partial Thromboplastin Time', 'short' => 'APTT', 'section' => 'HEMA', 'specimen' => 'PLASMA', 'unit' => 'RATIO', 'type' => 'Numeric', 'tat' => 60,  'charge' => 250.00],
            // Serology
            ['code' => 'HBSAG',  'name' => 'HBsAg (Hepatitis B Surface Antigen)', 'short' => 'HBsAg', 'section' => 'SERO', 'specimen' => 'SERUM', 'unit' => null, 'type' => 'Categorical', 'tat' => 120, 'charge' => 350.00],
            ['code' => 'ANTI_HBS','name' => 'Anti-HBs (Hepatitis B Antibody)',    'short' => 'Anti-HBs', 'section' => 'SERO', 'specimen' => 'SERUM', 'unit' => 'IU_ML', 'type' => 'Numeric', 'tat' => 120, 'charge' => 400.00],
            ['code' => 'VDRL',    'name' => 'VDRL / RPR (Syphilis)',              'short' => 'VDRL',   'section' => 'SERO', 'specimen' => 'SERUM', 'unit' => null,   'type' => 'Categorical', 'tat' => 120, 'charge' => 250.00],
            ['code' => 'DENGUE',  'name' => 'Dengue NS1 Antigen',                 'short' => 'Dengue', 'section' => 'SERO', 'specimen' => 'SERUM', 'unit' => null,   'type' => 'Categorical', 'tat' => 120, 'charge' => 500.00],
            // Urinalysis
            ['code' => 'U_A',    'name' => 'Urinalysis',              'short' => 'UA',      'section' => 'UA',   'specimen' => 'URINE', 'unit' => null,    'type' => 'Text',       'tat' => 45,  'charge' => 120.00],
        ];

        $testMap = [];
        foreach ($labTests as $lt) {
            $test = \App\Models\LabTest::withoutGlobalScopes()->updateOrCreate(
                ['code' => $lt['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'       => $tenant->id,
                    'name'            => $lt['name'],
                    'short_name'      => $lt['short'],
                    'lab_section_id'  => $labSectionIds[$lt['section']] ?? null,
                    'specimen_type_id'=> $specimenTypeIds[$lt['specimen']] ?? null,
                    'lab_unit_id'     => $labUnitIds[$lt['unit']] ?? null,
                    'test_type'       => $lt['type'],
                    'tat_minutes'     => $lt['tat'],
                    'standard_charge' => $lt['charge'],
                    'is_active'       => true,
                    'history'         => 1,
                    'created_by'      => $createdBy,
                    'created_date'    => $now,
                    'updated_by'      => $createdBy,
                    'updated_date'    => null,
                ]
            );
            $testMap[$lt['code']] = $test->id;
        }
        $this->command->info('✅  Seeded ' . count($labTests) . ' lab tests');

        // ════════════════════════════════════════════════════════════════════════
        // CSR / SUPPLY SEEDING (units, categories, suppliers, items, requests)
        // ════════════════════════════════════════════════════════════════════════

        // ── Supply Units ──────────────────────────────────────────────────────────
        $supplyUnits = [
            ['code' => 'BOX',   'name' => 'Box'],
            ['code' => 'BOTTLE','name' => 'Bottle'],
            ['code' => 'CAN',   'name' => 'Can'],
            ['code' => 'CASE',  'name' => 'Case'],
            ['code' => 'EA',    'name' => 'Each'],
            ['code' => 'GAL',   'name' => 'Gallon'],
            ['code' => 'PACK',  'name' => 'Pack'],
            ['code' => 'PAIR',  'name' => 'Pair'],
            ['code' => 'PCS',   'name' => 'Pieces'],
            ['code' => 'ROLL',  'name' => 'Roll'],
            ['code' => 'TUBE',  'name' => 'Tube'],
            ['code' => 'VIAL',  'name' => 'Vial'],
        ];

        $supplyUnitIds = [];
        foreach ($supplyUnits as $su) {
            $unit = SupplyUnit::withoutGlobalScopes()->updateOrCreate(
                ['code' => $su['code']],
                [
                    'name'         => $su['name'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
            $supplyUnitIds[$su['code']] = $unit->id;
        }
        $this->command->info('✅  Seeded ' . count($supplyUnits) . ' supply units');

        // ── Item Categories ───────────────────────────────────────────────────────
        $itemCategories = [
            ['code' => 'CONS',   'name' => 'Consumables',       'desc' => 'General medical and office consumables'],
            ['code' => 'MEDS',   'name' => 'Medications',       'desc' => 'Pharmaceuticals and drugs'],
            ['code' => 'IVFL',   'name' => 'IV Fluids',         'desc' => 'Intravenous fluids and electrolytes'],
            ['code' => 'SURG',   'name' => 'Surgical Supplies', 'desc' => 'Surgical instruments and disposables'],
            ['code' => 'LAB',    'name' => 'Lab Supplies',      'desc' => 'Laboratory reagents and consumables'],
            ['code' => 'LINEN',  'name' => 'Linens',            'desc' => 'Bed sheets, towels, patient gowns'],
            ['code' => 'OFFICE', 'name' => 'Office Supplies',   'desc' => 'Administrative and office materials'],
            ['code' => 'DIET',   'name' => 'Dietary',           'desc' => 'Food and dietary supplies'],
        ];

        $catIds = [];
        foreach ($itemCategories as $ic) {
            $cat = ItemCategory::withoutGlobalScopes()->updateOrCreate(
                ['code' => $ic['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'    => $tenant->id,
                    'name'         => $ic['name'],
                    'description'  => $ic['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
            $catIds[$ic['code']] = $cat->id;
        }
        $this->command->info('✅  Seeded ' . count($itemCategories) . ' item categories');

        // ── Suppliers ─────────────────────────────────────────────────────────────
        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'MediPro Distributors Inc.',      'contact' => '02-8123-4567', 'email' => 'orders@medipro.ph'],
            ['code' => 'SUP-002', 'name' => 'HealthCare Supply Chain Corp.',  'contact' => '02-8987-6543', 'email' => 'sales@healthcaresupply.ph'],
            ['code' => 'SUP-003', 'name' => 'PharmaLink Trading',             'contact' => '02-8555-1212', 'email' => 'info@pharmalink.ph'],
            ['code' => 'SUP-004', 'name' => 'Surgical Innovations Inc.',      'contact' => '02-8777-3434', 'email' => 'orders@surgicalinnovations.ph'],
            ['code' => 'SUP-005', 'name' => 'LabQuest Diagnostics Supply',    'contact' => '02-8999-5656', 'email' => 'supply@labquest.ph'],
        ];

        $supplierIds = [];
        foreach ($suppliers as $s) {
            $sup = Supplier::withoutGlobalScopes()->updateOrCreate(
                ['code' => $s['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'      => $tenant->id,
                    'name'           => $s['name'],
                    'contact_number' => $s['contact'],
                    'email'        => $s['email'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
            $supplierIds[$s['code']] = $sup->id;
        }
        $this->command->info('✅  Seeded ' . count($suppliers) . ' suppliers');

        // ── Stock Locations ───────────────────────────────────────────────────────
        $stockLocations = [
            ['code' => 'WH-01',  'name' => 'Main Warehouse',     'desc' => 'Central warehouse for bulk inventory',        'type' => 'Warehouse', 'parent' => null],
            ['code' => 'RM-A1',  'name' => 'Storage Room A',     'desc' => 'Main storage room (ground floor)',              'type' => 'Room',      'parent' => 'WH-01'],
            ['code' => 'RM-B1',  'name' => 'Storage Room B',     'desc' => 'Secondary storage room (basement)',              'type' => 'Room',      'parent' => 'WH-01'],
            ['code' => 'SH-A01', 'name' => 'Shelf A-01',         'desc' => 'Shelf A, first column',                         'type' => 'Shelf',     'parent' => 'RM-A1'],
            ['code' => 'SH-A02', 'name' => 'Shelf A-02',         'desc' => 'Shelf A, second column',                        'type' => 'Shelf',     'parent' => 'RM-A1'],
            ['code' => 'BN-A01', 'name' => 'Bin A-01',           'desc' => 'Bin on Shelf A-01 (left)',                      'type' => 'Bin',       'parent' => 'SH-A01'],
            ['code' => 'BN-A02', 'name' => 'Bin A-02',           'desc' => 'Bin on Shelf A-01 (right)',                     'type' => 'Bin',       'parent' => 'SH-A01'],
            ['code' => 'CB-OR',  'name' => 'OR Supply Cabinet',  'desc' => 'Locked cabinet in Operating Room',               'type' => 'Cabinet',   'parent' => null],
        ];

        $stockLocationIds = [];
        foreach ($stockLocations as $sl) {
            $loc = StockLocation::withoutGlobalScopes()->updateOrCreate(
                ['code' => $sl['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'    => $tenant->id,
                    'name'         => $sl['name'],
                    'description'  => $sl['desc'],
                    'type'         => $sl['type'],
                    'parent_id'    => $sl['parent'] ? ($stockLocationIds[$sl['parent']] ?? null) : null,
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
            $stockLocationIds[$sl['code']] = $loc->id;
        }
        $this->command->info('✅  Seeded ' . count($stockLocations) . ' stock locations');

        // ── Items (Supply Inventory) ──────────────────────────────────────────────
        $items = [
            ['code' => 'SYR-3CC',  'name' => 'Syringe 3cc with needle',          'cat' => 'CONS',  'sup' => null,    'unit' => 'EA',   'cost' => 8.50,   'reorder' => 500,  'reorder_qty' => 1000],
            ['code' => 'SYR-5CC',  'name' => 'Syringe 5cc with needle',          'cat' => 'CONS',  'sup' => null,    'unit' => 'EA',   'cost' => 10.00,  'reorder' => 500,  'reorder_qty' => 1000],
            ['code' => 'GLOV-NL',  'name' => 'Nitrile Gloves (Box/100)',          'cat' => 'CONS',  'sup' => 'SUP-001', 'unit' => 'BOX',  'cost' => 350.00, 'reorder' => 20,   'reorder_qty' => 50],
            ['code' => 'GLOV-LTX', 'name' => 'Latex Gloves (Box/100)',            'cat' => 'CONS',  'sup' => 'SUP-001', 'unit' => 'BOX',  'cost' => 280.00, 'reorder' => 20,   'reorder_qty' => 50],
            ['code' => 'MASK-SRG', 'name' => 'Surgical Mask (Box/50)',            'cat' => 'CONS',  'sup' => 'SUP-001', 'unit' => 'BOX',  'cost' => 150.00, 'reorder' => 30,   'reorder_qty' => 60],
            ['code' => 'MASK-N95', 'name' => 'N95 Mask (Box/20)',                 'cat' => 'CONS',  'sup' => 'SUP-001', 'unit' => 'BOX',  'cost' => 450.00, 'reorder' => 10,   'reorder_qty' => 30],
            ['code' => 'GAUZE',    'name' => 'Gauze Pad 4x4 (Pack/100)',          'cat' => 'CONS',  'sup' => 'SUP-004', 'unit' => 'PACK', 'cost' => 120.00, 'reorder' => 20,   'reorder_qty' => 50],
            ['code' => 'BND-EL',   'name' => 'Elastic Bandage 4in',              'cat' => 'CONS',  'sup' => 'SUP-004', 'unit' => 'EA',   'cost' => 85.00,  'reorder' => 30,   'reorder_qty' => 60],
            ['code' => 'TAPE-MIC', 'name' => 'Micropore Tape 1in',               'cat' => 'CONS',  'sup' => 'SUP-004', 'unit' => 'ROLL', 'cost' => 65.00,  'reorder' => 20,   'reorder_qty' => 40],
            ['code' => 'ALC-SWAB', 'name' => 'Alcohol Swab (Box/100)',           'cat' => 'CONS',  'sup' => 'SUP-001', 'unit' => 'BOX',  'cost' => 45.00,  'reorder' => 50,   'reorder_qty' => 100],
            ['code' => 'BETADINE', 'name' => 'Betadine Solution 30ml',            'cat' => 'MEDS',  'sup' => 'SUP-003', 'unit' => 'BOTTLE','cost' => 95.00,  'reorder' => 20,   'reorder_qty' => 40],
            ['code' => 'PN-10',    'name' => 'Normal Saline 500ml IV',            'cat' => 'IVFL',  'sup' => 'SUP-002', 'unit' => 'BOTTLE','cost' => 55.00,  'reorder' => 100,  'reorder_qty' => 200],
            ['code' => 'D5LR-1',   'name' => 'D5LR 1L IV',                       'cat' => 'IVFL',  'sup' => 'SUP-002', 'unit' => 'BOTTLE','cost' => 85.00,  'reorder' => 50,   'reorder_qty' => 100],
            ['code' => 'IV-SET',   'name' => 'IV Set with drip chamber',         'cat' => 'CONS',  'sup' => 'SUP-004', 'unit' => 'EA',   'cost' => 45.00,  'reorder' => 50,   'reorder_qty' => 100],
            ['code' => 'BUTT-FLY', 'name' => 'Butterfly Needle 23G',              'cat' => 'CONS',  'sup' => 'SUP-001', 'unit' => 'EA',   'cost' => 15.00,  'reorder' => 100,  'reorder_qty' => 200],
            ['code' => 'UR-BAG',   'name' => 'Urine Drainage Bag',               'cat' => 'CONS',  'sup' => 'SUP-004', 'unit' => 'EA',   'cost' => 120.00, 'reorder' => 30,   'reorder_qty' => 60],
            ['code' => 'FOL-CATH', 'name' => 'Foley Catheter 16Fr',               'cat' => 'SURG',  'sup' => 'SUP-004', 'unit' => 'EA',   'cost' => 180.00, 'reorder' => 20,   'reorder_qty' => 40],
            ['code' => 'SUT-3-0',  'name' => 'Suture Silk 3-0 (Box/36)',          'cat' => 'SURG',  'sup' => 'SUP-004', 'unit' => 'BOX',  'cost' => 650.00, 'reorder' => 5,    'reorder_qty' => 15],
            ['code' => 'SCALP-11', 'name' => 'Scalpel Blade #11 (Box/100)',       'cat' => 'SURG',  'sup' => 'SUP-004', 'unit' => 'BOX',  'cost' => 350.00, 'reorder' => 5,    'reorder_qty' => 10],
            ['code' => 'CHEM-STRP','name' => 'Urine Chemistry Strip (Vial/100)',  'cat' => 'LAB',   'sup' => 'SUP-005', 'unit' => 'VIAL', 'cost' => 850.00, 'reorder' => 3,    'reorder_qty' => 10],
            ['code' => 'LANCE',    'name' => 'Lancet (Box/100)',                  'cat' => 'LAB',   'sup' => 'SUP-005', 'unit' => 'BOX',  'cost' => 250.00, 'reorder' => 5,    'reorder_qty' => 15],
            ['code' => 'VAC-WB',   'name' => 'Vacutainer EDTA Tube (Box/100)',    'cat' => 'LAB',   'sup' => 'SUP-005', 'unit' => 'BOX',  'cost' => 550.00, 'reorder' => 10,   'reorder_qty' => 20],
        ];

        $itemIds = [];
        foreach ($items as $it) {
            $item = Item::withoutGlobalScopes()->updateOrCreate(
                ['code' => $it['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'       => $tenant->id,
                    'name'            => $it['name'],
                    'item_category_id'=> $catIds[$it['cat']] ?? null,
                    'supplier_id'     => $it['sup'] ? ($supplierIds[$it['sup']] ?? null) : null,
                    'supply_unit_id'  => $supplyUnitIds[$it['unit']] ?? null,
                    'unit_cost'       => $it['cost'],
                    'reorder_level'   => $it['reorder'],
                    'reorder_qty'     => $it['reorder_qty'],
                    'is_active'       => true,
                    'history'         => 1,
                    'created_by'      => $createdBy,
                    'created_date'    => $now,
                    'updated_by'      => $createdBy,
                    'updated_date'    => null,
                ]
            );
            $itemIds[$it['code']] = $item->id;
        }
        $this->command->info('✅  Seeded ' . count($items) . ' supply items');

        // ════════════════════════════════════════════════════════════════════════
        // PHARMACY / DRUG SEEDING
        // ════════════════════════════════════════════════════════════════════════

        // ── Drugs ────────────────────────────────────────────────────────────────
        $drugs = [
            // Cardiovascular
            ['code' => 'AMLO-5',   'generic' => 'Amlodipine',          'brand' => 'Norvasc',       'strength' => '5mg',      'form' => 'Tablet',   'class' => 'Antihypertensive',       'schedule' => 'Non-Rx', 'route' => 'PO',    'ingredient' => 'Amlodipine Besylate',    'mfr' => 'Pfizer',         'reorder' => 200, 'reorder_qty' => 500],
            ['code' => 'AMLO-10',  'generic' => 'Amlodipine',          'brand' => 'Norvasc',       'strength' => '10mg',     'form' => 'Tablet',   'class' => 'Antihypertensive',       'schedule' => 'Non-Rx', 'route' => 'PO',    'ingredient' => 'Amlodipine Besylate',    'mfr' => 'Pfizer',         'reorder' => 200, 'reorder_qty' => 500],
            ['code' => 'METOP-50', 'generic' => 'Metoprolol Tartrate',  'brand' => 'Lopressor',     'strength' => '50mg',     'form' => 'Tablet',   'class' => 'Beta Blocker',            'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Metoprolol Tartrate',    'mfr' => 'Novartis',       'reorder' => 150, 'reorder_qty' => 400],
            ['code' => 'LOSAR-50', 'generic' => 'Losartan Potassium',   'brand' => 'Cozaar',        'strength' => '50mg',     'form' => 'Tablet',   'class' => 'ARB',                     'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Losartan Potassium',     'mfr' => 'MSD',            'reorder' => 150, 'reorder_qty' => 400],
            ['code' => 'ATRV-20',  'generic' => 'Atorvastatin',         'brand' => 'Lipitor',       'strength' => '20mg',     'form' => 'Tablet',   'class' => 'Statin',                  'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Atorvastatin Calcium',   'mfr' => 'Pfizer',         'reorder' => 200, 'reorder_qty' => 500],
            // Antibiotics
            ['code' => 'AMOX-500', 'generic' => 'Amoxicillin',          'brand' => 'Amoxil',        'strength' => '500mg',    'form' => 'Capsule',  'class' => 'Penicillin Antibiotic',    'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Amoxicillin Trihydrate', 'mfr' => 'GSK',            'reorder' => 300, 'reorder_qty' => 600],
            ['code' => 'AZIT-500', 'generic' => 'Azithromycin',         'brand' => 'Zithromax',     'strength' => '500mg',    'form' => 'Tablet',   'class' => 'Macrolide Antibiotic',     'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Azithromycin Dihydrate', 'mfr' => 'Pfizer',         'reorder' => 200, 'reorder_qty' => 400],
            ['code' => 'CEFT-1G',  'generic' => 'Ceftriaxone',          'brand' => 'Rocephin',      'strength' => '1g',       'form' => 'Injection','class' => 'Cephalosporin Antibiotic',  'schedule' => 'Rx',     'route' => 'IV',    'ingredient' => 'Ceftriaxone Sodium',     'mfr' => 'Roche',          'reorder' => 100, 'reorder_qty' => 300],
            ['code' => 'CIPRO-500','generic' => 'Ciprofloxacin',        'brand' => 'Cipro',         'strength' => '500mg',    'form' => 'Tablet',   'class' => 'Fluoroquinolone',          'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Ciprofloxacin HCl',      'mfr' => 'Bayer',          'reorder' => 200, 'reorder_qty' => 500],
            ['code' => 'METRO-500','generic' => 'Metronidazole',        'brand' => 'Flagyl',        'strength' => '500mg',    'form' => 'Tablet',   'class' => 'Nitroimidazole',           'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Metronidazole',          'mfr' => 'Sanofi',         'reorder' => 200, 'reorder_qty' => 500],
            // Analgesics
            ['code' => 'PARA-500', 'generic' => 'Paracetamol',          'brand' => 'Biogesic',      'strength' => '500mg',    'form' => 'Tablet',   'class' => 'Antipyretic/Analgesic',    'schedule' => 'Non-Rx', 'route' => 'PO',    'ingredient' => 'Paracetamol',            'mfr' => 'United Lab',     'reorder' => 500, 'reorder_qty' => 1000],
            ['code' => 'IBU-400',  'generic' => 'Ibuprofen',            'brand' => 'Advil',         'strength' => '400mg',    'form' => 'Tablet',   'class' => 'NSAID',                   'schedule' => 'Non-Rx', 'route' => 'PO',    'ingredient' => 'Ibuprofen',              'mfr' => 'Pfizer',         'reorder' => 300, 'reorder_qty' => 600],
            ['code' => 'TRAM-50',  'generic' => 'Tramadol',             'brand' => 'Ultram',        'strength' => '50mg',     'form' => 'Capsule',  'class' => 'Opioid Analgesic',        'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Tramadol HCl',           'mfr' => 'Janssen',        'reorder' => 100, 'reorder_qty' => 300],
            // GI
            ['code' => 'OMEP-20',  'generic' => 'Omeprazole',           'brand' => 'Losec',         'strength' => '20mg',     'form' => 'Capsule',  'class' => 'Proton Pump Inhibitor',    'schedule' => 'Non-Rx', 'route' => 'PO',    'ingredient' => 'Omeprazole',             'mfr' => 'AstraZeneca',    'reorder' => 300, 'reorder_qty' => 600],
            ['code' => 'RANI-150', 'generic' => 'Ranitidine',           'brand' => 'Zantac',        'strength' => '150mg',    'form' => 'Tablet',   'class' => 'H2 Antagonist',            'schedule' => 'Non-Rx', 'route' => 'PO',    'ingredient' => 'Ranitidine HCl',         'mfr' => 'GSK',            'reorder' => 200, 'reorder_qty' => 500],
            ['code' => 'METO-10',  'generic' => 'Metoclopramide',       'brand' => 'Maxolon',       'strength' => '10mg',     'form' => 'Tablet',   'class' => 'Antiemetic',               'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Metoclopramide HCl',     'mfr' => 'Sanofi',         'reorder' => 150, 'reorder_qty' => 400],
            // Respiratory
            ['code' => 'SALB-100', 'generic' => 'Salbutamol',           'brand' => 'Ventolin',      'strength' => '100mcg',   'form' => 'Inhaler',  'class' => 'Bronchodilator',           'schedule' => 'Rx',     'route' => 'INH',   'ingredient' => 'Salbutamol Sulfate',     'mfr' => 'GSK',            'reorder' => 50,  'reorder_qty' => 150],
            ['code' => 'MONT-10',  'generic' => 'Montelukast',          'brand' => 'Singulair',     'strength' => '10mg',     'form' => 'Tablet',   'class' => 'Leukotriene Antagonist',   'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Montelukast Sodium',     'mfr' => 'MSD',            'reorder' => 100, 'reorder_qty' => 300],
            // Endocrine
            ['code' => 'METFO-500','generic' => 'Metformin',            'brand' => 'Glucophage',    'strength' => '500mg',    'form' => 'Tablet',   'class' => 'Biguanide (Antidiabetic)', 'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Metformin HCl',          'mfr' => 'Bristol-Myers',  'reorder' => 300, 'reorder_qty' => 600],
            ['code' => 'GLIB-5',   'generic' => 'Glibenclamide',        'brand' => 'Daonil',        'strength' => '5mg',      'form' => 'Tablet',   'class' => 'Sulfonylurea',             'schedule' => 'Rx',     'route' => 'PO',    'ingredient' => 'Glibenclamide',          'mfr' => 'Sanofi',         'reorder' => 150, 'reorder_qty' => 400],
            ['code' => 'INSU-R',   'generic' => 'Insulin Regular',      'brand' => 'Humulin R',     'strength' => '100IU/mL', 'form' => 'Injection','class' => 'Insulin',                  'schedule' => 'Rx',     'route' => 'SC',    'ingredient' => 'Human Insulin (rDNA)',   'mfr' => 'Eli Lilly',      'reorder' => 30,  'reorder_qty' => 100],
            // IV Fluids
            ['code' => 'PNSS-1L',  'generic' => '0.9% NaCl IV',         'brand' => 'PNSS',          'strength' => '1L',       'form' => 'Solution', 'class' => 'IV Fluid',                'schedule' => 'Non-Rx', 'route' => 'IV',    'ingredient' => 'Sodium Chloride 0.9%',   'mfr' => 'Baxter',         'reorder' => 100, 'reorder_qty' => 300],
            ['code' => 'D5LR-1L',  'generic' => 'D5LR IV',              'brand' => 'D5LR',          'strength' => '1L',       'form' => 'Solution', 'class' => 'IV Fluid',                'schedule' => 'Non-Rx', 'route' => 'IV',    'ingredient' => 'Dextrose 5% in LR',      'mfr' => 'Baxter',         'reorder' => 80,  'reorder_qty' => 200],
        ];

        $drugIds = [];
        foreach ($drugs as $d) {
            $drug = Drug::withoutGlobalScopes()->updateOrCreate(
                ['drug_code' => $d['code'], 'tenant_id' => $tenant->id],
                [
                    'tenant_id'          => $tenant->id,
                    'generic_name'       => $d['generic'],
                    'brand_name'         => $d['brand'],
                    'drug_strength'      => $d['strength'],
                    'dosage_form'        => $d['form'],
                    'therapeutic_class'  => $d['class'],
                    'drug_schedule'      => $d['schedule'],
                    'route'              => $d['route'],
                    'active_ingredient'  => $d['ingredient'],
                    'manufacturer'       => $d['mfr'],
                    'reorder_level'      => $d['reorder'],
                    'reorder_qty'        => $d['reorder_qty'],
                    'is_active'          => true,
                    'history'            => 1,
                    'created_by'         => $createdBy,
                    'created_date'       => $now,
                    'updated_by'         => $createdBy,
                    'updated_date'       => null,
                ]
            );
            $drugIds[$d['code']] = $drug->id;
        }
        $this->command->info('✅  Seeded ' . count($drugs) . ' drugs');

        // ── Drug Batches ─────────────────────────────────────────────────────────
        $futureDates = [];
        for ($i = 1; $i <= 12; $i++) {
            $futureDates[] = now()->addMonths($i)->toDateString();
        }
        $pastDates = [];
        for ($i = 1; $i <= 3; $i++) {
            $pastDates[] = now()->subMonths($i)->toDateString();
        }

        $batchNos = 0;
        $batchCount = 0;
        foreach ($drugIds as $drugCode => $drugId) {
            $batchesPerDrug = ($drugCode === 'PARA-500' || $drugCode === 'AMOX-500') ? 5 : 3;
            for ($b = 1; $b <= $batchesPerDrug; $b++) {
                $batchNos++;
                $isExpired = ($b === 1 && $drugCode !== 'PARA-500');
                $expiryDate = $isExpired ? $pastDates[array_rand($pastDates)] : $futureDates[array_rand($futureDates)];
                $qty = $isExpired ? 0 : rand(50, 500);
                $batchNo = 'BCH-' . str_pad($batchNos, 4, '0', STR_PAD_LEFT);

                DrugBatch::withoutGlobalScopes()->updateOrCreate(
                    ['drug_id' => $drugId, 'batch_no' => $batchNo, 'tenant_id' => $tenant->id, 'history' => 1],
                    [
                        'expiry_date'      => $expiryDate,
                        'quantity_on_hand' => $qty,
                        'unit_cost'        => round(rand(10, 5000) / 10, 2),
                        'selling_price'    => round(rand(15, 7500) / 10, 2),
                        'location'         => 'Pharmacy Shelf ' . chr(64 + $b),
                        'is_active'        => true,
                        'created_by'       => $createdBy,
                        'created_date'     => $now,
                        'updated_by'       => $createdBy,
                        'updated_date'     => null,
                    ]
                );
                $batchCount++;
            }
        }
        $this->command->info("✅  Seeded {$batchCount} drug batches");

        // ── Fund Sources ─────────────────────────────────────────────────────────
        $this->command->info('Seeding fund sources...');

        $fundSources = [
            ['code' => 'PRIVATE-PAY', 'name' => 'Private Pay / Self-Pay', 'desc' => 'Paid directly by the patient or family'],
            ['code' => 'PHILHEALTH',  'name' => 'PhilHealth',             'desc' => 'Philippine Health Insurance Corporation'],
            ['code' => 'HMO',         'name' => 'HMO / Insurance',        'desc' => 'Health Maintenance Organization or private insurance'],
            ['code' => 'GOV-SUBSIDY', 'name' => 'Government Subsidy',     'desc' => 'DSWD, LGU, or other government subsidy programs'],
            ['code' => 'CHARITY',     'name' => 'Charity / Donor Fund',   'desc' => 'Hospital charity fund or sponsored donations'],
        ];

        foreach ($fundSources as $fs) {
            \App\Models\Billing\FundSource::withoutGlobalScopes()->updateOrCreate(
                ['code' => $fs['code'], 'tenant_id' => $tenant->id, 'history' => 1],
                [
                    'tenant_id'    => $tenant->id,
                    'code'         => $fs['code'],
                    'name'         => $fs['name'],
                    'description'  => $fs['desc'],
                    'is_active'    => true,
                    'history'      => 1,
                    'created_by'   => $createdBy,
                    'created_date' => $now,
                    'updated_by'   => $createdBy,
                    'updated_date' => null,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($fundSources) . ' fund sources');

    }
}
