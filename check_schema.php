<?php
$f = file_get_contents('E:\sithemr\unified_schema.sql');

// Check for specific tables
$tables_to_check = ['lab_panels', 'lab_panel_tests', 'micu_assessments', 'micu_assessment_values', 'icu_wards', 'icu_beds', 'philhealth_claims', 'admissions'];
foreach ($tables_to_check as $t) {
    $has = strpos($f, "-- $t\nCREATE TABLE") !== false;
    echo "$t: " . ($has ? "FOUND" : "NOT FOUND") . "\n";
}

// Show sample of icu_wards
$start = strpos($f, "-- icu_wards\n");
if ($start !== false) {
    $end = strpos($f, "\n-- ", $start + 20);
    if (!$end) $end = $start + 1000;
    echo "\n--- icu_wards ---\n";
    echo substr($f, $start, $end - $start);
}

// Show a sample of billing_invoices
$start = strpos($f, "-- billing_invoices\n");
if ($start !== false) {
    $end = strpos($f, "\n-- ", $start + 20);
    if (!$end) $end = $start + 1000;
    echo "\n--- billing_invoices ---\n";
    echo substr($f, $start, $end - $start);
}
