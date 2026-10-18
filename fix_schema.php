<?php
$f = file_get_contents('E:\sithemr\unified_schema.sql');

// Remove duplicate unique constraints with backticks
$lines = explode("\n", $f);
$result = [];
foreach ($lines as $i => $line) {
    // Skip UNIQUE constraint lines with backtick-wrapped column refs or malformed names
    if (preg_match('/UNIQUE \(\[`\(/', $line)) continue;
    // Skip IF NOT EXISTS preceding those
    $prevLine = $i > 0 ? $result[count($result)-1] ?? '' : '';
    if (preg_match('/ALTER TABLE.*ADD CONSTRAINT \[UQ_\w+_`\(/', $line)) continue;
    if ($prevLine !== '' && preg_match('/UNIQUE \(\[`\(/', $line)) {
        array_pop($result);
        continue;
    }
    $result[] = $line;
}
$f = implode("\n", $result);
file_put_contents('E:\sithemr\unified_schema.sql', $f);

// Now count all CREATE TABLE statements properly
preg_match_all('/^CREATE TABLE/m', $f, $matches);
echo "Total CREATE TABLE: " . count($matches[0]) . "\n";

// List all table comment lines
preg_match_all('/^-- (\w+)$/m', $f, $m);
echo "Tables found: " . count($m[1]) . "\n";

// Check specifically for previously "missing" tables
$check = ['lab_panels', 'lab_panel_tests', 'micu_assessments', 'micu_assessment_values',
    'lab_requests', 'lab_request_items', 'csr_requests', 'csr_request_items',
    'pharmacy_requests', 'pharmacy_request_items', 'radiology_requests', 'radiology_request_items',
    'procedure_requests', 'procedure_request_items', 'item_batches', 'item_stock_adjustments',
    'stock_adjustments', 'purchase_orders', 'purchase_order_items',
    'radiology_result_images', 'oecb_entries', 'admission_room_histories',
    'hospital_transfers',
    'yakap_empanelments', 'yakap_fpe_records', 'yakap_screenings', 'yakap_capitation_payments',
    'konsulta_enrollments', 'konsulta_consultations', 'konsulta_prescriptions', 'konsulta_lab_referrals',
    'billing_manual_charges', 'claim_rejections', 'claim_status_polls', 'cf5_shadow_billings',
    'philhealth_claims', 'user_permissions', 'permission_audit_log',
    'nursing_vitals', 'nursing_care_notes', 'nursing_medication_administrations',
    'lab_panels', 'micu_assessments'];

foreach ($check as $t) {
    $found = preg_match("/^-- {$t}$/m", $f) === 1;
    echo ($found ? "OK  " : "MISS") . ": {$t}\n";
}
