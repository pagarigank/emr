<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RvsCode;
use App\Models\Icd10Code;
use App\Models\PhilhealthCaseRate;

class PhilhealthReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRvsCodes();
        $this->seedCaseRates();
    }

    private function seedRvsCodes(): void
    {
        $codes = [
            ['code' => '11450', 'name' => 'Appendectomy', 'type' => 'Surgical'],
            ['code' => '11460', 'name' => 'Appendectomy with drainage', 'type' => 'Surgical'],
            ['code' => '11500', 'name' => 'Hernia Repair (Inguinal, Unilateral)', 'type' => 'Surgical'],
            ['code' => '11510', 'name' => 'Hernia Repair (Inguinal, Bilateral)', 'type' => 'Surgical'],
            ['code' => '11520', 'name' => 'Hernia Repair (Umbilical)', 'type' => 'Surgical'],
            ['code' => '11600', 'name' => 'Hernia Repair (Ventral/Incisional)', 'type' => 'Surgical'],
            ['code' => '19710', 'name' => 'Mastectomy (Simple/Partial)', 'type' => 'Surgical'],
            ['code' => '19720', 'name' => 'Mastectomy (Modified Radical)', 'type' => 'Surgical'],
            ['code' => '20470', 'name' => 'Cholecystectomy (Open)', 'type' => 'Surgical'],
            ['code' => '20480', 'name' => 'Cholecystectomy (Laparoscopic)', 'type' => 'Surgical'],
            ['code' => '27890', 'name' => 'Cesarean Section', 'type' => 'OB'],
            ['code' => '27891', 'name' => 'Cesarean Section with Tubal Ligation', 'type' => 'OB'],
            ['code' => '31320', 'name' => 'Thyroidectomy (Total/Partial)', 'type' => 'Surgical'],
            ['code' => '33210', 'name' => 'Thyroidectomy (Total)', 'type' => 'Surgical'],
            ['code' => '33220', 'name' => 'Thyroidectomy (Subtotal)', 'type' => 'Surgical'],
            ['code' => '40500', 'name' => 'Mandibular Fracture Repair', 'type' => 'Surgical'],
            ['code' => '45720', 'name' => 'Exploratory Laparotomy', 'type' => 'Surgical'],
            ['code' => '49500', 'name' => 'Inguinal Hernia Repair (Pediatric)', 'type' => 'Surgical'],
            ['code' => '50010', 'name' => 'Circumcision', 'type' => 'Surgical'],
            ['code' => '50020', 'name' => 'Hydrocele Repair', 'type' => 'Surgical'],
            ['code' => '51040', 'name' => 'Drainage of Abscess (Perianal)', 'type' => 'Surgical'],
            ['code' => '53410', 'name' => 'Hemorrhoidectomy', 'type' => 'Surgical'],
            ['code' => '54000', 'name' => 'Frenuloplasty (Tongue-tie Release)', 'type' => 'Surgical'],
            ['code' => '59400', 'name' => 'Normal Spontaneous Delivery', 'type' => 'OB'],
            ['code' => '59410', 'name' => 'Episiotomy Repair', 'type' => 'OB'],
            ['code' => '59510', 'name' => 'Vacuum-Assisted Delivery', 'type' => 'OB'],
            ['code' => '59515', 'name' => 'Forceps Delivery', 'type' => 'OB'],
            ['code' => '59620', 'name' => 'Repair of Uterine Perforation', 'type' => 'OB'],
            ['code' => '66930', 'name' => 'Postpartum Dilation and Curettage', 'type' => 'OB'],
            ['code' => '66940', 'name' => 'Manual Removal of Retained Placenta', 'type' => 'OB'],
            ['code' => '77600', 'name' => 'Cesarean Section with Tubal Ligation (OB Package)', 'type' => 'OB'],
            ['code' => '80010', 'name' => 'Excision of Skin Lesion (Simple)', 'type' => 'Surgical'],
            ['code' => '80020', 'name' => 'Excision of Skin Lesion (Complex)', 'type' => 'Surgical'],
            ['code' => '81000', 'name' => 'I&D of Fingertip Abscess', 'type' => 'Surgical'],
            ['code' => '81100', 'name' => 'Nail Avulsion (Partial/Complete)', 'type' => 'Surgical'],
            ['code' => '86000', 'name' => 'Biopsy of Skin (Punch)', 'type' => 'Surgical'],
            ['code' => '87000', 'name' => 'Debridement of Wound (Simple)', 'type' => 'Surgical'],
            ['code' => '87010', 'name' => 'Debridement of Wound (Complex)', 'type' => 'Surgical'],
            ['code' => '90010', 'name' => 'Incision and Drainage of Abscess', 'type' => 'Surgical'],
            ['code' => '91000', 'name' => 'Foreign Body Removal (Superficial)', 'type' => 'Surgical'],
            ['code' => '92000', 'name' => 'Suturing of Wound (Simple)', 'type' => 'Surgical'],
            ['code' => '92010', 'name' => 'Suturing of Wound (Complex)', 'type' => 'Surgical'],
            ['code' => '93000', 'name' => 'Closed Reduction of Nasal Fracture', 'type' => 'Surgical'],
            ['code' => '93100', 'name' => 'Closed Reduction of Finger Fracture', 'type' => 'Surgical'],
            ['code' => '94000', 'name' => 'Application of Cast (Upper Extremity)', 'type' => 'Ortho'],
            ['code' => '94010', 'name' => 'Application of Cast (Lower Extremity)', 'type' => 'Ortho'],
            ['code' => '95000', 'name' => 'Removal of Cast', 'type' => 'Ortho'],
            ['code' => '96000', 'name' => 'Closed Reduction of Dislocated Shoulder', 'type' => 'Ortho'],
            ['code' => '96100', 'name' => 'Closed Reduction of Dislocated Elbow', 'type' => 'Ortho'],
            ['code' => '97000', 'name' => 'Knee Arthroscopy (Diagnostic)', 'type' => 'Ortho'],
            ['code' => '97010', 'name' => 'Knee Arthroscopy (Meniscectomy)', 'type' => 'Ortho'],
            ['code' => '98000', 'name' => 'Shoulder Arthroscopy', 'type' => 'Ortho'],
            ['code' => '99000', 'name' => 'ACL Reconstruction', 'type' => 'Ortho'],
        ];

        foreach ($codes as $c) {
            RvsCode::firstOrCreate(
                ['code' => $c['code']],
                [
                    'name' => $c['name'],
                    'type' => $c['type'],
                    'is_active' => true,
                    'history' => 1,
                    'created_date' => now(),
                ]
            );
        }
    }

    private function seedCaseRates(): void
    {
        $rates = [
            ['rvs' => '11450', 'icd' => 'K37', 'desc' => 'Appendectomy (Uncomplicated)', 'hf' => 15000, 'pf' => 10000],
            ['rvs' => '11460', 'icd' => 'K35.80', 'desc' => 'Appendectomy with Drainage', 'hf' => 18000, 'pf' => 12000],
            ['rvs' => '11500', 'icd' => 'K40.90', 'desc' => 'Inguinal Hernia Repair (Unilateral)', 'hf' => 12000, 'pf' => 8000],
            ['rvs' => '11510', 'icd' => 'K40.91', 'desc' => 'Inguinal Hernia Repair (Bilateral)', 'hf' => 16000, 'pf' => 10000],
            ['rvs' => '11520', 'icd' => 'K42.9', 'desc' => 'Umbilical Hernia Repair', 'hf' => 10000, 'pf' => 7000],
            ['rvs' => '19710', 'icd' => 'C50.9', 'desc' => 'Simple Mastectomy', 'hf' => 20000, 'pf' => 15000],
            ['rvs' => '19720', 'icd' => 'C50.9', 'desc' => 'Modified Radical Mastectomy', 'hf' => 25000, 'pf' => 18000],
            ['rvs' => '20470', 'icd' => 'K81.0', 'desc' => 'Cholecystectomy (Open)', 'hf' => 22000, 'pf' => 15000],
            ['rvs' => '20480', 'icd' => 'K81.0', 'desc' => 'Cholecystectomy (Laparoscopic)', 'hf' => 28000, 'pf' => 18000],
            ['rvs' => '27890', 'icd' => 'O82', 'desc' => 'Cesarean Section', 'hf' => 18000, 'pf' => 12000],
            ['rvs' => '27891', 'icd' => 'O82', 'desc' => 'Cesarean Section with Tubal Ligation', 'hf' => 20000, 'pf' => 13000],
            ['rvs' => '31320', 'icd' => 'E04.9', 'desc' => 'Thyroidectomy (Partial/Total)', 'hf' => 25000, 'pf' => 18000],
            ['rvs' => '45720', 'icd' => 'R10.0', 'desc' => 'Exploratory Laparotomy', 'hf' => 20000, 'pf' => 15000],
            ['rvs' => '53410', 'icd' => 'K64.0', 'desc' => 'Hemorrhoidectomy', 'hf' => 12000, 'pf' => 8000],
            ['rvs' => '59400', 'icd' => 'O80', 'desc' => 'Normal Spontaneous Delivery', 'hf' => 8000, 'pf' => 5000],
            ['rvs' => '59510', 'icd' => 'O81.0', 'desc' => 'Vacuum-Assisted Delivery', 'hf' => 10000, 'pf' => 7000],
            ['rvs' => '59515', 'icd' => 'O81.1', 'desc' => 'Forceps Delivery', 'hf' => 10000, 'pf' => 7000],
            ['rvs' => '77600', 'icd' => 'O82', 'desc' => 'Cesarean Section (OB Package)', 'hf' => 22000, 'pf' => 14000],
            ['rvs' => '80010', 'icd' => 'L40.9', 'desc' => 'Excision of Skin Lesion (Simple)', 'hf' => 5000, 'pf' => 3000],
            ['rvs' => '80020', 'icd' => 'D22.9', 'desc' => 'Excision of Skin Lesion (Complex)', 'hf' => 8000, 'pf' => 5000],
            ['rvs' => '86000', 'icd' => 'R19.7', 'desc' => 'Biopsy of Skin (Punch)', 'hf' => 3000, 'pf' => 2000],
            ['rvs' => '87000', 'icd' => 'L03.90', 'desc' => 'Debridement of Wound (Simple)', 'hf' => 3000, 'pf' => 2000],
            ['rvs' => '87010', 'icd' => 'L03.90', 'desc' => 'Debridement of Wound (Complex)', 'hf' => 6000, 'pf' => 4000],
            ['rvs' => '90010', 'icd' => 'L02.9', 'desc' => 'Incision and Drainage of Abscess', 'hf' => 4000, 'pf' => 3000],
            ['rvs' => '92000', 'icd' => 'S61.41', 'desc' => 'Suturing of Wound (Simple)', 'hf' => 2000, 'pf' => 1500],
            ['rvs' => '92010', 'icd' => 'S61.41', 'desc' => 'Suturing of Wound (Complex)', 'hf' => 4000, 'pf' => 3000],
            ['rvs' => '93000', 'icd' => 'S02.2', 'desc' => 'Closed Reduction of Nasal Fracture', 'hf' => 5000, 'pf' => 4000],
            ['rvs' => '96000', 'icd' => 'S43.0', 'desc' => 'Closed Reduction of Dislocated Shoulder', 'hf' => 5000, 'pf' => 4000],
            ['rvs' => '97000', 'icd' => 'M23.2', 'desc' => 'Knee Arthroscopy (Diagnostic)', 'hf' => 15000, 'pf' => 10000],
            ['rvs' => '97010', 'icd' => 'M23.2', 'desc' => 'Knee Arthroscopy (Meniscectomy)', 'hf' => 20000, 'pf' => 12000],
            ['rvs' => '99000', 'icd' => 'M23.9', 'desc' => 'ACL Reconstruction', 'hf' => 35000, 'pf' => 20000],
        ];

        foreach ($rates as $r) {
            $rvs = RvsCode::where('code', $r['rvs'])->first();
            $icd = Icd10Code::where('code', $r['icd'])->first();

            PhilhealthCaseRate::firstOrCreate(
                ['rvs_code' => $r['rvs']],
                [
                    'icd10_code' => $r['icd'],
                    'description' => $r['desc'],
                    'hf_fee' => $r['hf'],
                    'pf_fee' => $r['pf'],
                    'total_rate' => $r['hf'] + $r['pf'],
                    'rvs_code_id' => $rvs?->id,
                    'icd10_code_id' => $icd?->id,
                    'is_active' => true,
                    'history' => 1,
                    'created_date' => now(),
                ]
            );
        }
    }
}
