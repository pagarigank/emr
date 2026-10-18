<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoHospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = \App\Models\Tenant::withoutGlobalScopes()->where('tenant_code', 'DEMO01')->first();
        $tenantId = $tenant ? $tenant->id : 1;
        $now = Carbon::now();

        // Helper function to insert data safely
        $insertData = function($table, $data, $uniqueKeys) {
            foreach ($data as $row) {
                $match = [];
                foreach ($uniqueKeys as $key) {
                    if (isset($row[$key])) {
                        $match[$key] = $row[$key];
                    }
                }
                DB::table($table)->updateOrInsert($match, $row);
            }
        };

        // 1. Wards
        $wards = [
            ['tenant_id' => $tenantId, 'code' => 'W-M', 'name' => 'Male Medical Ward', 'description' => 'General Medical Ward for Male Patients', 'location' => '2nd Floor, West Wing', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'W-F', 'name' => 'Female Medical Ward', 'description' => 'General Medical Ward for Female Patients', 'location' => '2nd Floor, East Wing', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'W-PED', 'name' => 'Pediatric Ward', 'description' => 'Ward for infants and children', 'location' => '3rd Floor, South Wing', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
        ];
        $insertData('wards', $wards, ['tenant_id', 'code']);
        
        $maleWardId = DB::table('wards')->where('code', 'W-M')->value('id');
        $femaleWardId = DB::table('wards')->where('code', 'W-F')->value('id');
        $pedWardId = DB::table('wards')->where('code', 'W-PED')->value('id');

        // 2. Ward Rooms
        $wardRooms = [
            ['tenant_id' => $tenantId, 'ward_id' => $maleWardId, 'code' => 'R-201', 'name' => 'Room 201', 'capacity' => 4, 'room_rate' => 1500.00, 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'ward_id' => $maleWardId, 'code' => 'R-202', 'name' => 'Room 202', 'capacity' => 2, 'room_rate' => 2500.00, 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'ward_id' => $femaleWardId, 'code' => 'R-203', 'name' => 'Room 203', 'capacity' => 4, 'room_rate' => 1500.00, 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'ward_id' => $femaleWardId, 'code' => 'R-204', 'name' => 'Room 204', 'capacity' => 2, 'room_rate' => 2500.00, 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'ward_id' => $pedWardId, 'code' => 'R-301', 'name' => 'Room 301', 'capacity' => 6, 'room_rate' => 1000.00, 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
        ];
        $insertData('ward_rooms', $wardRooms, ['tenant_id', 'code']);

        // 3. ER Zones
        $erZones = [
            ['tenant_id' => $tenantId, 'code' => 'ER-RED', 'name' => 'Red Zone (Resuscitation)', 'description' => 'Critical Care / Immediate Life Support', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'ER-ORG', 'name' => 'Orange Zone (Emergent)', 'description' => 'High Risk / Severe Pain', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'ER-YEL', 'name' => 'Yellow Zone (Urgent)', 'description' => 'Stable but multiple resources needed', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'ER-GRN', 'name' => 'Green Zone (Less Urgent)', 'description' => 'Minor injuries or illnesses', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
        ];
        $insertData('er_zones', $erZones, ['tenant_id', 'code']);

        $redZoneId = DB::table('er_zones')->where('code', 'ER-RED')->value('id');
        $orgZoneId = DB::table('er_zones')->where('code', 'ER-ORG')->value('id');
        $yelZoneId = DB::table('er_zones')->where('code', 'ER-YEL')->value('id');
        $grnZoneId = DB::table('er_zones')->where('code', 'ER-GRN')->value('id');

        // 4. ER Beds
        $erBeds = [
            ['tenant_id' => $tenantId, 'er_zone_id' => $redZoneId, 'code' => 'ER-B1', 'name' => 'Trauma Bed 1', 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'er_zone_id' => $redZoneId, 'code' => 'ER-B2', 'name' => 'Trauma Bed 2', 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'er_zone_id' => $orgZoneId, 'code' => 'ER-B3', 'name' => 'Emergent Bed 1', 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'er_zone_id' => $orgZoneId, 'code' => 'ER-B4', 'name' => 'Emergent Bed 2', 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'er_zone_id' => $yelZoneId, 'code' => 'ER-B5', 'name' => 'Observation Bed 1', 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'er_zone_id' => $yelZoneId, 'code' => 'ER-B6', 'name' => 'Observation Bed 2', 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'er_zone_id' => $grnZoneId, 'code' => 'ER-C1', 'name' => 'Consultation Chair 1', 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'er_zone_id' => $grnZoneId, 'code' => 'ER-C2', 'name' => 'Consultation Chair 2', 'status' => 'Available', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
        ];
        $insertData('er_beds', $erBeds, ['tenant_id', 'code']);

        // 5. Drugs (Pharmacy Data)
        $drugs = [
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-001', 'generic_name' => 'Paracetamol', 'brand_name' => 'Biogesic', 'drug_strength' => '500mg', 'dosage_form' => 'Tablet', 'therapeutic_class' => 'Analgesic/Antipyretic', 'route' => 'Oral', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-002', 'generic_name' => 'Amoxicillin', 'brand_name' => 'Amoxil', 'drug_strength' => '500mg', 'dosage_form' => 'Capsule', 'therapeutic_class' => 'Antibiotic', 'route' => 'Oral', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-003', 'generic_name' => 'Ibuprofen', 'brand_name' => 'Advil', 'drug_strength' => '200mg', 'dosage_form' => 'Tablet', 'therapeutic_class' => 'NSAID', 'route' => 'Oral', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-004', 'generic_name' => 'Omeprazole', 'brand_name' => 'Losec', 'drug_strength' => '20mg', 'dosage_form' => 'Capsule', 'therapeutic_class' => 'Proton Pump Inhibitor', 'route' => 'Oral', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-005', 'generic_name' => 'Losartan', 'brand_name' => 'Cozaar', 'drug_strength' => '50mg', 'dosage_form' => 'Tablet', 'therapeutic_class' => 'Antihypertensive', 'route' => 'Oral', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-006', 'generic_name' => 'Metformin', 'brand_name' => 'Glucophage', 'drug_strength' => '500mg', 'dosage_form' => 'Tablet', 'therapeutic_class' => 'Antidiabetic', 'route' => 'Oral', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-007', 'generic_name' => 'Salbutamol', 'brand_name' => 'Ventolin', 'drug_strength' => '2mg/5mL', 'dosage_form' => 'Syrup', 'therapeutic_class' => 'Bronchodilator', 'route' => 'Oral', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-008', 'generic_name' => 'Ceftriaxone', 'brand_name' => 'Rocephin', 'drug_strength' => '1g', 'dosage_form' => 'Vial', 'therapeutic_class' => 'Antibiotic', 'route' => 'IV', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-009', 'generic_name' => 'Epinephrine', 'brand_name' => 'Adrenalin', 'drug_strength' => '1mg/mL', 'dosage_form' => 'Ampule', 'therapeutic_class' => 'Sympathomimetic', 'route' => 'IV/IM', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'drug_code' => 'DRG-010', 'generic_name' => 'Diazepam', 'brand_name' => 'Valium', 'drug_strength' => '5mg', 'dosage_form' => 'Tablet', 'therapeutic_class' => 'Benzodiazepine', 'route' => 'Oral', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
        ];
        $insertData('drugs', $drugs, ['tenant_id', 'drug_code']);
        
        // 6. CSR Data (Item Categories, Units, and Items)
        
        $units = [
            ['code' => 'PCS', 'name' => 'Pieces', 'description' => 'Pieces', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['code' => 'BOX', 'name' => 'Box', 'description' => 'Box', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['code' => 'PACK', 'name' => 'Pack', 'description' => 'Pack', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
        ];
        $insertData('supply_units', $units, ['code']); // NO tenant_id on supply_units
        
        $pcsUnitId = DB::table('supply_units')->where('code', 'PCS')->value('id');
        
        $categories = [
            ['tenant_id' => $tenantId, 'code' => 'CAT-MED', 'name' => 'Medical Supplies', 'description' => 'General Medical Supplies', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CAT-SUR', 'name' => 'Surgical Supplies', 'description' => 'Supplies used for surgeries', 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
        ];
        $insertData('item_categories', $categories, ['tenant_id', 'code']);
        
        $catMedId = DB::table('item_categories')->where('code', 'CAT-MED')->value('id');
        $catSurId = DB::table('item_categories')->where('code', 'CAT-SUR')->value('id');
        
        $items = [
            ['tenant_id' => $tenantId, 'code' => 'CSR-001', 'name' => 'Disposable Syringe 5cc', 'description' => 'Syringe 5cc with needle', 'item_category_id' => $catMedId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-SYR-05', 'unit_cost' => 5.50, 'reorder_level' => 100, 'reorder_qty' => 500, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CSR-002', 'name' => 'Disposable Syringe 10cc', 'description' => 'Syringe 10cc with needle', 'item_category_id' => $catMedId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-SYR-10', 'unit_cost' => 7.00, 'reorder_level' => 100, 'reorder_qty' => 500, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CSR-003', 'name' => 'IV Fluid PNSS 1L', 'description' => 'Plain Normal Saline Solution 1L', 'item_category_id' => $catMedId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-IV-PNSS', 'unit_cost' => 65.00, 'reorder_level' => 50, 'reorder_qty' => 200, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CSR-004', 'name' => 'IV Fluid D5LR 1L', 'description' => 'Dextrose 5% in Lactated Ringers 1L', 'item_category_id' => $catMedId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-IV-D5LR', 'unit_cost' => 70.00, 'reorder_level' => 50, 'reorder_qty' => 200, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CSR-005', 'name' => 'Surgical Gloves Size 7', 'description' => 'Sterile Surgical Gloves Size 7', 'item_category_id' => $catSurId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-GLV-70', 'unit_cost' => 25.00, 'reorder_level' => 200, 'reorder_qty' => 1000, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CSR-006', 'name' => 'Surgical Gloves Size 7.5', 'description' => 'Sterile Surgical Gloves Size 7.5', 'item_category_id' => $catSurId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-GLV-75', 'unit_cost' => 25.00, 'reorder_level' => 200, 'reorder_qty' => 1000, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CSR-007', 'name' => 'Gauze Pad 4x4', 'description' => 'Sterile Gauze Pad 4x4 inches', 'item_category_id' => $catMedId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-GZ-44', 'unit_cost' => 3.50, 'reorder_level' => 300, 'reorder_qty' => 1500, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CSR-008', 'name' => 'Plaster Tape 1 inch', 'description' => 'Medical Plaster Tape 1 inch width', 'item_category_id' => $catMedId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-TP-10', 'unit_cost' => 45.00, 'reorder_level' => 50, 'reorder_qty' => 100, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CSR-009', 'name' => 'Cotton Balls (Pack of 100)', 'description' => 'Sterile Cotton Balls', 'item_category_id' => $catMedId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-CTN-100', 'unit_cost' => 20.00, 'reorder_level' => 100, 'reorder_qty' => 300, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
            ['tenant_id' => $tenantId, 'code' => 'CSR-010', 'name' => 'Face Mask (Surgical)', 'description' => '3-Ply Surgical Face Mask', 'item_category_id' => $catMedId, 'supply_unit_id' => $pcsUnitId, 'sku' => 'SKU-MSK-3P', 'unit_cost' => 2.50, 'reorder_level' => 500, 'reorder_qty' => 2000, 'is_active' => 1, 'history' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_date' => $now, 'updated_date' => $now],
        ];
        $insertData('items', $items, ['tenant_id', 'code']);

        // Seed Item Batches for CSR Items
        $itemBatches = [];
        $itemsData = DB::table('items')->where('tenant_id', $tenantId)->get();
        foreach ($itemsData as $item) {
            $itemBatches[] = [
                'tenant_id' => $tenantId,
                'item_id' => $item->id,
                'batch_no' => 'BATCH-' . $item->code . '-' . date('Ym'),
                'quantity_on_hand' => 100,
                'unit_cost' => $item->unit_cost,
                'selling_price' => $item->unit_cost * 1.5,
                'expiry_date' => $now->copy()->addYears(2)->toDateString(),
                'is_active' => 1,
                'history' => 1,
                'created_by' => 1,
                'created_date' => $now,
                'updated_by' => 1,
                'updated_date' => $now,
            ];
        }
        $insertData('item_batches', $itemBatches, ['tenant_id', 'batch_no']);

        $this->command->info('DemoHospitalSeeder completed: Wards, Rooms, ER Zones, ER Beds, Pharmacy Drugs, CSR Items, and CSR Batches seeded for Tenant ID 1.');
    }
}
