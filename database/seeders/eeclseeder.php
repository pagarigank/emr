<?php

namespace Database\Seeders;

use App\Models\Oecb\EeclItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class EeclSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::withoutGlobalScopes()->where('role', 'superadmin')->first();
        $userId = $superadmin?->id ?? 1;
        $now = now();

        $items = [
            // ══════════════════════════════════════════════════════════════════
            // A. DIAGNOSTIC SERVICES
            // ══════════════════════════════════════════════════════════════════
            ['item_code' => 'EECL-001', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Complete Blood Count (CBC)', 'fixed_fee' => 310.00, 'unit' => 'test'],
            ['item_code' => 'EECL-002', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Urinalysis', 'fixed_fee' => 105.00, 'unit' => 'test'],
            ['item_code' => 'EECL-003', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Fecalysis', 'fixed_fee' => 105.00, 'unit' => 'test'],
            ['item_code' => 'EECL-004', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Blood Typing', 'fixed_fee' => 158.00, 'unit' => 'test'],
            ['item_code' => 'EECL-005', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Crossmatching', 'fixed_fee' => 368.00, 'unit' => 'test'],
            ['item_code' => 'EECL-006', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Blood Sugar (Fasting/Random)', 'fixed_fee' => 105.00, 'unit' => 'test'],
            ['item_code' => 'EECL-007', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Blood Urea Nitrogen (BUN)', 'fixed_fee' => 158.00, 'unit' => 'test'],
            ['item_code' => 'EECL-008', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Creatinine', 'fixed_fee' => 158.00, 'unit' => 'test'],
            ['item_code' => 'EECL-009', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Electrolytes (Na, K, Cl)', 'fixed_fee' => 368.00, 'unit' => 'test'],
            ['item_code' => 'EECL-010', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Liver Function Tests (SGPT, SGOT)', 'fixed_fee' => 316.00, 'unit' => 'test'],
            ['item_code' => 'EECL-011', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Prothrombin Time (PT)', 'fixed_fee' => 316.00, 'unit' => 'test'],
            ['item_code' => 'EECL-012', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Activated Partial Thromboplastin Time (aPTT)', 'fixed_fee' => 316.00, 'unit' => 'test'],
            ['item_code' => 'EECL-013', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Troponin I', 'fixed_fee' => 1914.00, 'unit' => 'test'],
            ['item_code' => 'EECL-014', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Creatine Kinase (CK-MB)', 'fixed_fee' => 737.00, 'unit' => 'test'],
            ['item_code' => 'EECL-015', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Uric Acid', 'fixed_fee' => 158.00, 'unit' => 'test'],
            ['item_code' => 'EECL-016', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Lipid Profile', 'fixed_fee' => 526.00, 'unit' => 'test'],
            ['item_code' => 'EECL-017', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'HbA1c (Glycosylated Hemoglobin)', 'fixed_fee' => 737.00, 'unit' => 'test'],
            ['item_code' => 'EECL-018', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Dengue NS1 Antigen', 'fixed_fee' => 947.00, 'unit' => 'test'],
            ['item_code' => 'EECL-019', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Dengue IgG/IgM', 'fixed_fee' => 737.00, 'unit' => 'test'],
            ['item_code' => 'EECL-020', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Malaria Smear/Rapid Diagnostic Test', 'fixed_fee' => 263.00, 'unit' => 'test'],
            ['item_code' => 'EECL-021', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Urine Pregnancy Test', 'fixed_fee' => 105.00, 'unit' => 'test'],
            ['item_code' => 'EECL-022', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Serum Pregnancy Test (Beta-hCG)', 'fixed_fee' => 526.00, 'unit' => 'test'],
            ['item_code' => 'EECL-023', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Stool Culture', 'fixed_fee' => 526.00, 'unit' => 'test'],
            ['item_code' => 'EECL-024', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Blood Culture', 'fixed_fee' => 737.00, 'unit' => 'test'],
            ['item_code' => 'EECL-025', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Sputum AFB Smear', 'fixed_fee' => 158.00, 'unit' => 'test'],
            ['item_code' => 'EECL-026', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Arterial Blood Gas (ABG)', 'fixed_fee' => 737.00, 'unit' => 'test'],
            ['item_code' => 'EECL-027', 'category' => 'Diagnostic Services', 'subcategory' => 'Laboratory', 'item_name' => 'Lactate (Blood)', 'fixed_fee' => 526.00, 'unit' => 'test'],

            // ══════════════════════════════════════════════════════════════════
            // B. IMAGING / RADIOLOGY
            // ══════════════════════════════════════════════════════════════════
            ['item_code' => 'EECL-030', 'category' => 'Imaging Services', 'subcategory' => 'X-Ray', 'item_name' => 'Chest X-Ray (PA View)', 'fixed_fee' => 426.00, 'unit' => 'view'],
            ['item_code' => 'EECL-031', 'category' => 'Imaging Services', 'subcategory' => 'X-Ray', 'item_name' => 'Chest X-Ray (AP View)', 'fixed_fee' => 426.00, 'unit' => 'view'],
            ['item_code' => 'EECL-032', 'category' => 'Imaging Services', 'subcategory' => 'X-Ray', 'item_name' => 'Abdomen X-Ray (Supine/Upright)', 'fixed_fee' => 426.00, 'unit' => 'view'],
            ['item_code' => 'EECL-033', 'category' => 'Imaging Services', 'subcategory' => 'X-Ray', 'item_name' => 'Extremity X-Ray', 'fixed_fee' => 426.00, 'unit' => 'view'],
            ['item_code' => 'EECL-034', 'category' => 'Imaging Services', 'subcategory' => 'X-Ray', 'item_name' => 'Skull X-Ray', 'fixed_fee' => 426.00, 'unit' => 'view'],
            ['item_code' => 'EECL-035', 'category' => 'Imaging Services', 'subcategory' => 'X-Ray', 'item_name' => 'Spine X-Ray (Cervical/Thoracic/Lumbar)', 'fixed_fee' => 426.00, 'unit' => 'view'],
            ['item_code' => 'EECL-036', 'category' => 'Imaging Services', 'subcategory' => 'X-Ray', 'item_name' => 'Pelvis X-Ray', 'fixed_fee' => 426.00, 'unit' => 'view'],
            ['item_code' => 'EECL-037', 'category' => 'Imaging Services', 'subcategory' => 'Ultrasound', 'item_name' => 'Abdominal Ultrasound', 'fixed_fee' => 842.00, 'unit' => 'exam'],
            ['item_code' => 'EECL-038', 'category' => 'Imaging Services', 'subcategory' => 'Ultrasound', 'item_name' => 'Transvaginal Ultrasound', 'fixed_fee' => 842.00, 'unit' => 'exam'],
            ['item_code' => 'EECL-039', 'category' => 'Imaging Services', 'subcategory' => 'Ultrasound', 'item_name' => 'Transabdominal Obstetric Ultrasound', 'fixed_fee' => 842.00, 'unit' => 'exam'],
            ['item_code' => 'EECL-040', 'category' => 'Imaging Services', 'subcategory' => 'Ultrasound', 'item_name' => 'Echocardiography (2D Echo)', 'fixed_fee' => 2105.00, 'unit' => 'exam'],
            ['item_code' => 'EECL-041', 'category' => 'Imaging Services', 'subcategory' => 'CT Scan', 'item_name' => 'CT Scan Head (Non-Contrast)', 'fixed_fee' => 3684.00, 'unit' => 'exam'],
            ['item_code' => 'EECL-042', 'category' => 'Imaging Services', 'subcategory' => 'CT Scan', 'item_name' => 'CT Scan Chest (Non-Contrast)', 'fixed_fee' => 4210.00, 'unit' => 'exam'],
            ['item_code' => 'EECL-043', 'category' => 'Imaging Services', 'subcategory' => 'CT Scan', 'item_name' => 'CT Scan Abdomen & Pelvis (Non-Contrast)', 'fixed_fee' => 4737.00, 'unit' => 'exam'],
            ['item_code' => 'EECL-044', 'category' => 'Imaging Services', 'subcategory' => 'CT Scan', 'item_name' => 'CT Scan with Contrast (Any Region)', 'fixed_fee' => 5789.00, 'unit' => 'exam'],
            ['item_code' => 'EECL-045', 'category' => 'Imaging Services', 'subcategory' => 'MRI', 'item_name' => 'MRI Brain (Non-Contrast)', 'fixed_fee' => 5263.00, 'unit' => 'exam'],

            // ══════════════════════════════════════════════════════════════════
            // C. ER SERVICES / CONSULTATION
            // ══════════════════════════════════════════════════════════════════
            ['item_code' => 'EECL-050', 'category' => 'ER Services', 'subcategory' => 'Consultation', 'item_name' => 'Urgent ER Consultation', 'fixed_fee' => 260.00, 'unit' => 'encounter'],
            ['item_code' => 'EECL-051', 'category' => 'ER Services', 'subcategory' => 'Consultation', 'item_name' => 'Emergent ER Consultation', 'fixed_fee' => 707.00, 'unit' => 'encounter'],
            ['item_code' => 'EECL-052', 'category' => 'ER Services', 'subcategory' => 'Bed', 'item_name' => 'ER Bed (per day)', 'fixed_fee' => 650.00, 'unit' => 'day'],
            ['item_code' => 'EECL-053', 'category' => 'ER Services', 'subcategory' => 'Monitoring', 'item_name' => 'Vital Signs Monitoring (per shift)', 'fixed_fee' => 158.00, 'unit' => 'shift'],
            ['item_code' => 'EECL-054', 'category' => 'ER Services', 'subcategory' => 'Monitoring', 'item_name' => 'Cardiac Monitor (per day)', 'fixed_fee' => 737.00, 'unit' => 'day'],
            ['item_code' => 'EECL-055', 'category' => 'ER Services', 'subcategory' => 'Monitoring', 'item_name' => 'Pulse Oximetry (per day)', 'fixed_fee' => 263.00, 'unit' => 'day'],
            ['item_code' => 'EECL-056', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Oxygen Therapy (per day)', 'fixed_fee' => 526.00, 'unit' => 'day'],
            ['item_code' => 'EECL-057', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Nasogastric Tube Insertion', 'fixed_fee' => 263.00, 'unit' => 'procedure'],
            ['item_code' => 'EECL-058', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Urinary Catheterization', 'fixed_fee' => 263.00, 'unit' => 'procedure'],
            ['item_code' => 'EECL-059', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Wound Suturing (simple)', 'fixed_fee' => 526.00, 'unit' => 'procedure'],
            ['item_code' => 'EECL-060', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Wound Suturing (complicated/deep)', 'fixed_fee' => 1053.00, 'unit' => 'procedure'],
            ['item_code' => 'EECL-061', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Incision and Drainage', 'fixed_fee' => 526.00, 'unit' => 'procedure'],
            ['item_code' => 'EECL-062', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Foreign Body Removal', 'fixed_fee' => 526.00, 'unit' => 'procedure'],
            ['item_code' => 'EECL-063', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Chest Tube Insertion (Thoracostomy)', 'fixed_fee' => 2632.00, 'unit' => 'procedure'],
            ['item_code' => 'EECL-064', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Endotracheal Intubation', 'fixed_fee' => 1053.00, 'unit' => 'procedure'],
            ['item_code' => 'EECL-065', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Cardiopulmonary Resuscitation (CPR)', 'fixed_fee' => 3158.00, 'unit' => 'procedure'],
            ['item_code' => 'EECL-066', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'IV Fluid Administration (per day)', 'fixed_fee' => 263.00, 'unit' => 'day'],
            ['item_code' => 'EECL-067', 'category' => 'ER Services', 'subcategory' => 'Procedures', 'item_name' => 'Blood Transfusion (per unit)', 'fixed_fee' => 1579.00, 'unit' => 'unit'],

            // ══════════════════════════════════════════════════════════════════
            // D. MEDICINES / DRUGS
            // ══════════════════════════════════════════════════════════════════
            ['item_code' => 'EECL-070', 'category' => 'Medicines', 'subcategory' => 'Analgesics', 'item_name' => 'Paracetamol 500mg (tab)', 'fixed_fee' => 5.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-071', 'category' => 'Medicines', 'subcategory' => 'Analgesics', 'item_name' => 'Paracetamol 1g (IV)', 'fixed_fee' => 52.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-072', 'category' => 'Medicines', 'subcategory' => 'Analgesics', 'item_name' => 'Tramadol 50mg (tab)', 'fixed_fee' => 10.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-073', 'category' => 'Medicines', 'subcategory' => 'Analgesics', 'item_name' => 'Morphine Sulfate 10mg/mL (ampule)', 'fixed_fee' => 84.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-074', 'category' => 'Medicines', 'subcategory' => 'Analgesics', 'item_name' => 'Ketorolac 30mg/mL (ampule)', 'fixed_fee' => 105.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-075', 'category' => 'Medicines', 'subcategory' => 'NSAIDs', 'item_name' => 'Ibuprofen 400mg (tab)', 'fixed_fee' => 5.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-076', 'category' => 'Medicines', 'subcategory' => 'Antibiotics', 'item_name' => 'Amoxicillin 500mg (cap)', 'fixed_fee' => 10.00, 'unit' => 'cap'],
            ['item_code' => 'EECL-077', 'category' => 'Medicines', 'subcategory' => 'Antibiotics', 'item_name' => 'Cefuroxime 750mg (IV)', 'fixed_fee' => 158.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-078', 'category' => 'Medicines', 'subcategory' => 'Antibiotics', 'item_name' => 'Ceftriaxone 1g (IV)', 'fixed_fee' => 105.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-079', 'category' => 'Medicines', 'subcategory' => 'Antibiotics', 'item_name' => 'Metronidazole 500mg (IV)', 'fixed_fee' => 26.00, 'unit' => 'bottle'],
            ['item_code' => 'EECL-080', 'category' => 'Medicines', 'subcategory' => 'Antibiotics', 'item_name' => 'Clindamycin 600mg (IV)', 'fixed_fee' => 105.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-081', 'category' => 'Medicines', 'subcategory' => 'Antibiotics', 'item_name' => 'Gentamicin 80mg/2mL (ampule)', 'fixed_fee' => 32.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-082', 'category' => 'Medicines', 'subcategory' => 'Antibiotics', 'item_name' => 'Meropenem 1g (IV)', 'fixed_fee' => 474.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-083', 'category' => 'Medicines', 'subcategory' => 'Antibiotics', 'item_name' => 'Azithromycin 500mg (tab)', 'fixed_fee' => 42.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-084', 'category' => 'Medicines', 'subcategory' => 'Antibiotics', 'item_name' => 'Levofloxacin 500mg (IV)', 'fixed_fee' => 211.00, 'unit' => 'bottle'],
            ['item_code' => 'EECL-085', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Aspirin 80mg (tab)', 'fixed_fee' => 3.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-086', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Clopidogrel 75mg (tab)', 'fixed_fee' => 37.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-087', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Nitroglycerin 0.5mg (tab SL)', 'fixed_fee' => 16.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-088', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Nitroglycerin 5mg/mL (IV)', 'fixed_fee' => 105.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-089', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Amlodipine 5mg (tab)', 'fixed_fee' => 11.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-090', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Metoprolol 50mg (tab)', 'fixed_fee' => 11.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-091', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Diltiazem 60mg (tab)', 'fixed_fee' => 21.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-092', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Amiodarone 150mg/3mL (ampule)', 'fixed_fee' => 211.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-093', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Dopamine 40mg/2mL (ampule)', 'fixed_fee' => 53.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-094', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Dobutamine 250mg (vial)', 'fixed_fee' => 211.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-095', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Epinephrine 1mg/mL (ampule)', 'fixed_fee' => 21.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-096', 'category' => 'Medicines', 'subcategory' => 'Cardiovascular', 'item_name' => 'Atropine 0.5mg/mL (ampule)', 'fixed_fee' => 21.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-097', 'category' => 'Medicines', 'subcategory' => 'Respiratory', 'item_name' => 'Salbutamol Nebulization 2.5mg/2.5mL', 'fixed_fee' => 26.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-098', 'category' => 'Medicines', 'subcategory' => 'Respiratory', 'item_name' => 'Ipratropium Bromide Nebulization 0.5mg/2.5mL', 'fixed_fee' => 53.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-099', 'category' => 'Medicines', 'subcategory' => 'Respiratory', 'item_name' => 'Prednisone 20mg (tab)', 'fixed_fee' => 5.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-100', 'category' => 'Medicines', 'subcategory' => 'Respiratory', 'item_name' => 'Hydrocortisone 100mg (IV)', 'fixed_fee' => 53.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-101', 'category' => 'Medicines', 'subcategory' => 'Respiratory', 'item_name' => 'Methylprednisolone 125mg (IV)', 'fixed_fee' => 105.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-102', 'category' => 'Medicines', 'subcategory' => 'Antihistamines', 'item_name' => 'Diphenhydramine 50mg/mL (ampule)', 'fixed_fee' => 21.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-103', 'category' => 'Medicines', 'subcategory' => 'Antihistamines', 'item_name' => 'Dexamethasone 4mg/mL (ampule)', 'fixed_fee' => 21.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-104', 'category' => 'Medicines', 'subcategory' => 'Emergency', 'item_name' => 'Naloxone 0.4mg/mL (ampule)', 'fixed_fee' => 105.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-105', 'category' => 'Medicines', 'subcategory' => 'Emergency', 'item_name' => 'Flumazenil 0.1mg/mL (ampule)', 'fixed_fee' => 316.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-106', 'category' => 'Medicines', 'subcategory' => 'Emergency', 'item_name' => 'Glucagon 1mg (vial)', 'fixed_fee' => 211.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-107', 'category' => 'Medicines', 'subcategory' => 'Emergency', 'item_name' => 'Dextrose 50% 50mL (ampule)', 'fixed_fee' => 26.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-108', 'category' => 'Medicines', 'subcategory' => 'Emergency', 'item_name' => 'Dextrose 5% 500mL (IV)', 'fixed_fee' => 26.00, 'unit' => 'bottle'],
            ['item_code' => 'EECL-109', 'category' => 'Medicines', 'subcategory' => 'Emergency', 'item_name' => 'Normal Saline 0.9% 500mL (IV)', 'fixed_fee' => 26.00, 'unit' => 'bottle'],
            ['item_code' => 'EECL-110', 'category' => 'Medicines', 'subcategory' => 'Emergency', 'item_name' => 'Lactated Ringer\'s 500mL (IV)', 'fixed_fee' => 32.00, 'unit' => 'bottle'],
            ['item_code' => 'EECL-111', 'category' => 'Medicines', 'subcategory' => 'GI', 'item_name' => 'Omeprazole 20mg (cap)', 'fixed_fee' => 11.00, 'unit' => 'cap'],
            ['item_code' => 'EECL-112', 'category' => 'Medicines', 'subcategory' => 'GI', 'item_name' => 'Pantoprazole 40mg (IV)', 'fixed_fee' => 105.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-113', 'category' => 'Medicines', 'subcategory' => 'GI', 'item_name' => 'Ranitidine 50mg/mL (ampule)', 'fixed_fee' => 32.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-114', 'category' => 'Medicines', 'subcategory' => 'Antiemetics', 'item_name' => 'Metoclopramide 10mg/mL (ampule)', 'fixed_fee' => 16.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-115', 'category' => 'Medicines', 'subcategory' => 'Antiemetics', 'item_name' => 'Ondansetron 4mg/2mL (ampule)', 'fixed_fee' => 53.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-116', 'category' => 'Medicines', 'subcategory' => 'Anticonvulsants', 'item_name' => 'Diazepam 5mg/mL (ampule)', 'fixed_fee' => 21.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-117', 'category' => 'Medicines', 'subcategory' => 'Anticonvulsants', 'item_name' => 'Phenytoin 100mg (cap)', 'fixed_fee' => 11.00, 'unit' => 'cap'],
            ['item_code' => 'EECL-118', 'category' => 'Medicines', 'subcategory' => 'Anticonvulsants', 'item_name' => 'Levetiracetam 500mg (IV)', 'fixed_fee' => 211.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-119', 'category' => 'Medicines', 'subcategory' => 'Anticonvulsants', 'item_name' => 'Magnesium Sulfate 50% 2mL (ampule)', 'fixed_fee' => 16.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-120', 'category' => 'Medicines', 'subcategory' => 'Electrolytes', 'item_name' => 'Potassium Chloride 20mEq/100mL (IV)', 'fixed_fee' => 26.00, 'unit' => 'bottle'],
            ['item_code' => 'EECL-121', 'category' => 'Medicines', 'subcategory' => 'Electrolytes', 'item_name' => 'Calcium Gluconate 10% 10mL (ampule)', 'fixed_fee' => 26.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-122', 'category' => 'Medicines', 'subcategory' => 'Electrolytes', 'item_name' => 'Sodium Bicarbonate 8.4% 50mL (ampule)', 'fixed_fee' => 32.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-123', 'category' => 'Medicines', 'subcategory' => 'Anesthesia', 'item_name' => 'Lidocaine 2% 20mL (ampule)', 'fixed_fee' => 21.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-124', 'category' => 'Medicines', 'subcategory' => 'Anesthesia', 'item_name' => 'Propofol 200mg/20mL (vial)', 'fixed_fee' => 105.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-125', 'category' => 'Medicines', 'subcategory' => 'Anesthesia', 'item_name' => 'Ketamine 50mg/mL (ampule)', 'fixed_fee' => 53.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-126', 'category' => 'Medicines', 'subcategory' => 'Anesthesia', 'item_name' => 'Midazolam 5mg/mL (ampule)', 'fixed_fee' => 53.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-127', 'category' => 'Medicines', 'subcategory' => 'Anesthesia', 'item_name' => 'Succinylcholine 200mg (vial)', 'fixed_fee' => 105.00, 'unit' => 'vial'],
            ['item_code' => 'EECL-128', 'category' => 'Medicines', 'subcategory' => 'Anesthesia', 'item_name' => 'Bupivacaine 0.5% 20mL (ampule)', 'fixed_fee' => 53.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-129', 'category' => 'Medicines', 'subcategory' => 'Obstetric', 'item_name' => 'Oxytocin 10IU/mL (ampule)', 'fixed_fee' => 21.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-130', 'category' => 'Medicines', 'subcategory' => 'Obstetric', 'item_name' => 'Methylergometrine 0.2mg/mL (ampule)', 'fixed_fee' => 32.00, 'unit' => 'ampule'],
            ['item_code' => 'EECL-131', 'category' => 'Medicines', 'subcategory' => 'Obstetric', 'item_name' => 'Misoprostol 200mcg (tab)', 'fixed_fee' => 21.00, 'unit' => 'tab'],
            ['item_code' => 'EECL-132', 'category' => 'Medicines', 'subcategory' => 'Obstetric', 'item_name' => 'Magnesium Sulfate 4g/100mL (IV)', 'fixed_fee' => 53.00, 'unit' => 'bottle'],

            // ══════════════════════════════════════════════════════════════════
            // E. SUPPLIES & MATERIALS
            // ══════════════════════════════════════════════════════════════════
            ['item_code' => 'EECL-140', 'category' => 'Supplies', 'subcategory' => 'IV Supplies', 'item_name' => 'IV Cannula (catheter)', 'fixed_fee' => 26.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-141', 'category' => 'Supplies', 'subcategory' => 'IV Supplies', 'item_name' => 'IV Giving Set', 'fixed_fee' => 11.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-142', 'category' => 'Supplies', 'subcategory' => 'IV Supplies', 'item_name' => 'IV Extension Set', 'fixed_fee' => 26.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-143', 'category' => 'Supplies', 'subcategory' => 'IV Supplies', 'item_name' => 'Three-Way Stopcock', 'fixed_fee' => 26.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-144', 'category' => 'Supplies', 'subcategory' => 'Surgical', 'item_name' => 'Suture Kit (absorbable)', 'fixed_fee' => 105.00, 'unit' => 'pack'],
            ['item_code' => 'EECL-145', 'category' => 'Supplies', 'subcategory' => 'Surgical', 'item_name' => 'Suture Kit (non-absorbable)', 'fixed_fee' => 105.00, 'unit' => 'pack'],
            ['item_code' => 'EECL-146', 'category' => 'Supplies', 'subcategory' => 'Surgical', 'item_name' => 'Sterile Gauze (pack)', 'fixed_fee' => 26.00, 'unit' => 'pack'],
            ['item_code' => 'EECL-147', 'category' => 'Supplies', 'subcategory' => 'Surgical', 'item_name' => 'Elastic Bandage', 'fixed_fee' => 21.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-148', 'category' => 'Supplies', 'subcategory' => 'Airway', 'item_name' => 'Endotracheal Tube', 'fixed_fee' => 105.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-149', 'category' => 'Supplies', 'subcategory' => 'Airway', 'item_name' => 'Laryngoscope Blade', 'fixed_fee' => 263.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-150', 'category' => 'Supplies', 'subcategory' => 'Airway', 'item_name' => 'Ambu Bag (BVM)', 'fixed_fee' => 526.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-151', 'category' => 'Supplies', 'subcategory' => 'Monitoring', 'item_name' => 'Nasogastric Tube', 'fixed_fee' => 53.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-152', 'category' => 'Supplies', 'subcategory' => 'Monitoring', 'item_name' => 'Urinary Catheter (Foley)', 'fixed_fee' => 53.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-153', 'category' => 'Supplies', 'subcategory' => 'Monitoring', 'item_name' => 'Chest Drainage System', 'fixed_fee' => 526.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-154', 'category' => 'Supplies', 'subcategory' => 'General', 'item_name' => 'Nebulizer Kit', 'fixed_fee' => 53.00, 'unit' => 'kit'],
            ['item_code' => 'EECL-155', 'category' => 'Supplies', 'subcategory' => 'General', 'item_name' => 'Oxygen Mask', 'fixed_fee' => 21.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-156', 'category' => 'Supplies', 'subcategory' => 'General', 'item_name' => 'Nasal Cannula', 'fixed_fee' => 21.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-157', 'category' => 'Supplies', 'subcategory' => 'General', 'item_name' => 'Suction Catheter', 'fixed_fee' => 26.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-158', 'category' => 'Supplies', 'subcategory' => 'General', 'item_name' => 'Gloves (pair)', 'fixed_fee' => 5.00, 'unit' => 'pair'],
            ['item_code' => 'EECL-159', 'category' => 'Supplies', 'subcategory' => 'General', 'item_name' => 'Face Mask (surgical)', 'fixed_fee' => 5.00, 'unit' => 'piece'],
            ['item_code' => 'EECL-160', 'category' => 'Supplies', 'subcategory' => 'General', 'item_name' => 'Syringe (various sizes)', 'fixed_fee' => 5.00, 'unit' => 'piece'],
        ];

        foreach ($items as $item) {
            EeclItem::withoutGlobalScopes()->updateOrCreate(
                ['item_code' => $item['item_code']],
                [
                    'category' => $item['category'],
                    'subcategory' => $item['subcategory'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['item_name'],
                    'unit' => $item['unit'],
                    'fixed_fee' => $item['fixed_fee'],
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $userId,
                    'created_date' => $now,
                    'updated_by' => $userId,
                    'updated_date' => $now,
                ]
            );
        }

        $this->command->info('✅  Seeded ' . count($items) . ' EECL items with fixed fees');
    }
}
