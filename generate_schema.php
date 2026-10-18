<?php
/**
 * Generates unified_schema.sql from tables.md
 *
 * Two-pass approach:
 *   Pass 1: Extract table boundaries and column definitions
 *   Pass 2: Generate T-SQL
 *
 * Input: tables.md — formatted with ### N. `tablename` headers followed by
 *        markdown tables or ```sql blocks for column definitions.
 *
 * Parser features:
 *   - Headers: ### N. `name`, ### Na. `name`, ### N.N. `name`, or ### `name`
 *   - Sub-tables: **`name`** within a combined section (e.g. lab_panels + lab_panel_tests)
 *   - Column types: BIGINT, INT, VARCHAR, TEXT, DECIMAL, DATE, DATETIME, etc.
 *   - Constraints: PK, auto-increment, NOT NULL, UNIQUE, DEFAULT, FK → table.col
 *   - ON DELETE actions extracted from column-level FK constraints
 *   - Composite Unique lines: backtick-wrapped column tuples
 *   - Foreign Key lines: detailed FK references with ON DELETE actions
 *   - Global tables (no tenant_id) vs tenant-scoped tables
 *
 * Output: unified_schema.sql — T-SQL for SQL Server with:
 *   - CREATE TABLE with IDENTITY(1,1), NVARCHAR, DATETIME2, DECIMAL types
 *   - PRIMARY KEY, UNIQUE constraints (with IF NOT EXISTS guards)
 *   - Nonclustered indexes on FK columns, tenant_id, history, is_active, etc.
 *   - Foreign key constraints with proper ON DELETE actions
 *   - 120+ tables from all modules (Master, Lab, MICU, OPD, OB, ER, OR, DR,
 *     Pharmacy, Supply, Billing, PhilHealth, Konsulta, Yakap, Nursing, etc.)
 *
 * Usage: php generate_schema.php
 */

$input = file_get_contents(__DIR__ . '/tables.md');
$lines = explode("\n", $input);

// ---- Type mapping ----
function toTSqlType($type) {
    $t = strtoupper(trim($type));
    if ($t === 'BIGINT UNSIGNED') return 'BIGINT';
    if ($t === 'BIGINT') return 'BIGINT';
    if ($t === 'TINYINT UNSIGNED') return 'TINYINT';
    if (preg_match('/^TINYINT\(\d+\)$/i', $t)) return 'TINYINT';
    if ($t === 'TINYINT') return 'TINYINT';
    if ($t === 'INT' || $t === 'INTEGER') return 'INT';
    if ($t === 'INT UNSIGNED') return 'INT';
    if ($t === 'TEXT') return 'NVARCHAR(MAX)';
    if (preg_match('/^VARCHAR\((\d+)\)$/i', $t, $m)) return "NVARCHAR($m[1])";
    if ($t === 'VARCHAR' || $t === 'VARCHAR(MAX)') return 'NVARCHAR(255)';
    if (preg_match('/^NVARCHAR\((\d+)\)$/i', $t, $m)) return "NVARCHAR($m[1])";
    if ($t === 'TIMESTAMP') return 'DATETIME2';
    if ($t === 'DATETIME') return 'DATETIME2';
    if ($t === 'DATE') return 'DATE';
    if ($t === 'TIME') return 'TIME';
    if ($t === 'BOOLEAN' || $t === 'BOOL') return 'TINYINT';
    if (preg_match('/^DECIMAL\((\d+),(\d+)\)$/i', $t, $m)) return "DECIMAL($m[1],$m[2])";
    if (preg_match('/^FLOAT/i', $t)) return 'FLOAT';
    return $t;
}

// ---- Determine global/tenant-scoped tables ----
$globalTables = [
    'tenants', 'countries', 'regions', 'provinces_cities', 'barangays',
    'religions', 'genders', 'civil_statuses', 'nationalities', 'occupations',
    'blood_types', 'relationships', 'icd10_codes', 'icd_10_codes',
    'rvs_codes', 'philhealth_case_rates', 'lab_units', 'specimen_types',
    'reference_ranges', 'supply_units', 'procedure_categories',
    'dialysis_types', 'delivery_types', 'delivery_complications',
    'ward_room_types', 'opd_visit_types', 'opd_consultation_types',
    'opd_referral_sources', 'opd_services', 'opd_clinics', 'opd_complaints',
    'ob_visit_types', 'ob_fetal_presentations', 'ob_contraceptive_methods',
    'er_acuity_levels', 'er_mobility_statuses',
    'radiology_modalities', 'radiology_body_parts',
    'micu_assessment_parameters', 'permissions', 'roles',
    'oecb_core_presentations', 'oecb_fee_schedules',
];

// Child tables (no tenant_id, inherited via parent)
$childTables = [
    'micu_assessment_values', 'lab_panel_tests', 'purchase_order_items',
    'billing_invoice_items', 'role_permissions', 'user_permissions',
];

// ---- Parse tables.md ----
$tables = []; // tableName => [cols => [], uniques => [], fks => []]
$currentTable = null;
$currentCols = [];
$currentUniques = [];
$currentFks = [];
$currentExtraFks = []; // FK strings from "Foreign Keys:" lines
$lineNum = 0;

function startTable($name) {
    global $currentTable, $currentCols, $currentUniques, $currentFks, $currentExtraFks;
    $currentTable = $name;
    $currentCols = [];
    $currentUniques = [];
    $currentFks = [];
    $currentExtraFks = [];
}

function saveTable() {
    global $tables, $currentTable, $currentCols, $currentUniques, $currentFks;
    if ($currentTable && !empty($currentCols)) {
        $tables[$currentTable] = [
            'cols' => $currentCols,
            'uniques' => $currentUniques,
            'fks' => $currentFks,
        ];
    }
}

// Extract FK info from "FK → table.column" or "FK → table(column)"
function parseFK($str) {
    $onDelete = null;
    if (preg_match('/ON\s+DELETE\s+(NO\s+ACTION|SET\s+NULL|CASCADE)/i', $str, $mDel)) {
        $onDelete = $mDel[1];
    }
    if (preg_match('/FK\s*[→>]?\s*`?(\w+(?:_\w+)*)`?\.`?(\w+)`?/i', $str, $m)) {
        return [$m[1], $m[2], $onDelete];
    }
    if (preg_match('/FK\s*[→>]?\s*`?(\w+(?:_\w+)*)`?\(`?(\w+)`?\)/i', $str, $m)) {
        return [$m[1], $m[2], $onDelete];
    }
    if (preg_match('/FK\s*→\s*(\w+(?:_\w+)*)/i', $str, $m)) {
        return [$m[1], 'id', $onDelete];
    }
    return null;
}

function parseMarkdownRow($line) {
    $parts = explode('|', $line);
    $parts = array_map('trim', $parts);
    // Remove leading/trailing empty strings from leading/trailing |
    while (!empty($parts) && $parts[0] === '') array_shift($parts);
    while (!empty($parts) && $parts[count($parts)-1] === '') array_pop($parts);

    if (count($parts) < 2) return null;
    if (preg_match('/^[-: ]+$/', $parts[0])) return null; // separator row

    $colName = trim($parts[0], '` ');
    if ($colName === '') return null;
    if ($colName === '---' || $colName === '--------') return null;
    if (preg_match('/^\|[-:\s]+\|[-:\s]+\|/', $line)) return null; // separator

    $type = $parts[1] ?? '';
    $constraints = $parts[2] ?? '';

    // Skip if this is a header row (Column/col/Col)
    if (preg_match('/^(Column|col|Col)$/', $colName)) return null;

    $nullable = stripos($constraints, 'nullable') !== false;
    $notNull = stripos($constraints, 'not null') !== false;
    $pk = stripos($constraints, 'PK') !== false || stripos($constraints, 'PRIMARY KEY') !== false;
    $ai = stripos($constraints, 'auto-increment') !== false || stripos($constraints, 'auto_increment') !== false;
    $unique = stripos($constraints, 'UNIQUE') !== false;
    $default = null;

    if (preg_match("/default\s+'([^']*)'/i", $constraints, $m)) $default = $m[1];
    elseif (preg_match('/default\s+(\d+(?:\.\d+)?)/i', $constraints, $m)) $default = $m[1];
    elseif (stripos($constraints, 'default true') !== false) $default = '1';
    elseif (stripos($constraints, 'default false') !== false) $default = '0';

    $fk = parseFK($constraints);

    return [
        'name' => $colName,
        'type' => toTSqlType($type),
        'nullable' => $nullable || ($colName !== 'id' && $colName !== 'tenant_id' && $colName !== 'created_by' && !$notNull && $colName !== 'created_date'),
        'pk' => $pk || $colName === 'id',
        'ai' => $ai || $colName === 'id',
        'default' => $default,
        'unique' => $unique,
        'fk' => $fk,
    ];
}

function parseSqlBlockLine($line) {
    $trimmed = trim($line);
    if (!preg_match('/`(\w+)`\s+(.+)$/', $trimmed, $m)) return null;
    $colName = $m[1];
    $rest = $m[2];

    if (strpos($rest, '---') !== false) return null;

    // Extract type
    $knownTypes = ['BIGINT UNSIGNED', 'TINYINT UNSIGNED', 'BIGINT', 'TINYINT(1)', 'TINYINT',
                   'INT', 'VARCHAR', 'TEXT', 'DATE', 'TIMESTAMP', 'DATETIME', 'TIME',
                   'DECIMAL(10,2)', 'DECIMAL(12,2)', 'DECIMAL(5,2)', 'DECIMAL(8,2)',
                   'DECIMAL(6,2)', 'DECIMAL(4,1)', 'DECIMAL(10,4)', 'DECIMAL(18,6)',
                   'DECIMAL(12,2)', 'BOOLEAN', 'TINYINT UNSIGNED'];

    $type = '';
    $constraints = '';

    // Try to match known type
    $matched = false;
    foreach ($knownTypes as $kt) {
        if (stripos($rest, $kt) === 0 || stripos($rest, $kt) === 0) {
            $pos = stripos($rest, $kt);
            if ($pos === 0) {
                $type = substr($rest, 0, strlen($kt));
                $constraints = trim(substr($rest, strlen($kt)));
                $matched = true;
                break;
            }
        }
    }

    if (!$matched) {
        // Try regex: first word(s) as type
        if (preg_match('/^(BIGINT UNSIGNED|TINYINT UNSIGNED|BIGINT|TINYINT\(\d+\)|TINYINT|VARCHAR(?:\(\d+\))?|TEXT|INT|DATE|TIMESTAMP|DATETIME|TIME|DECIMAL(?:\(\d+(?:,\d+)?\))?|BOOLEAN)\s*(.*)$/i', $rest, $m2)) {
            $type = $m2[1];
            $constraints = trim($m2[2]);
        } else {
            $type = $rest;
            $constraints = '';
        }
    }

    $nullable = stripos($constraints, 'NULLABLE') !== false;
    $notNull = stripos($constraints, 'NOT NULL') !== false;
    $pk = stripos($constraints, 'PK') !== false || $colName === 'id';
    $ai = stripos($constraints, 'AUTO_INCREMENT') !== false || $colName === 'id';
    $unique = stripos($constraints, 'UNIQUE') !== false;
    $default = null;

    if (preg_match("/default\s+'([^']*)'/i", $constraints, $m)) $default = $m[1];
    elseif (preg_match('/default\s+(\d+(?:\.\d+)?)/i', $constraints, $m)) $default = $m[1];
    elseif (stripos($constraints, 'DEFAULT 1') !== false && stripos($constraints, 'DEFAULT 10') === false) $default = '1';

    // Override: created_date, updated_date are nullable
    if ($colName === 'created_date' || $colName === 'updated_date') $nullable = true;

    // created_by typically NOT NULL
    if ($colName === 'created_by' && !$nullable) $nullable = false;

    $fk = parseFK($constraints);

    return [
        'name' => $colName,
        'type' => toTSqlType($type),
        'nullable' => $nullable,
        'pk' => $pk,
        'ai' => $ai,
        'default' => $default,
        'unique' => $unique,
        'fk' => $fk,
    ];
}

// ============ PASS 1: Extract table definitions ============
$state = 'scan';
$braceDepth = 0;

foreach ($lines as $i => $line) {
    $trimmed = trim($line);
    $prevLine = $i > 0 ? trim($lines[$i - 1]) : '';
    $prevPrevLine = $i > 1 ? trim($lines[$i - 2]) : '';

    // --- Detect table header ---
    // Pattern: ### N. `tablename`, ### Na. `tablename`, or ### `tablename`
    // Sub-tables (e.g. `lab_panel_tests` inside combined section) handled by **`name`** detection
    // Matches: ### N. `name`, ### Na. `name`, ### N.N. `name`, or ### `name`
    if (preg_match('/^###\s+\d+(?:[a-zA-Z]?\.\d*)?\.?\s*`(\w+)`/', $trimmed, $m) ||
        preg_match('/^###\s+`(\w+)`/', $trimmed, $m)) {
        saveTable();
        startTable($m[1]);

        $state = 'table_header';
        continue;
    }

    // --- Detect sub-table header: **`tablename`** within a section ---
    if ($currentTable && preg_match('/^\*\*`(\w+)`\*\*/', $trimmed, $m)) {
        $subTable = $m[1];
        // Only start if it's a different table name
        if ($subTable !== $currentTable) {
            saveTable();
            startTable($subTable);
            $state = 'table_header';
            continue;
        }
    }

    if (!$currentTable) continue;

    // --- Detect SQL blocks ---
    if (preg_match('/^```sql/', $trimmed)) {
        $state = 'sql_block';
        $braceDepth = 1;
        continue;
    }
    if ($state === 'sql_block') {
        if (preg_match('/^```/', $trimmed)) {
            $state = 'in_table';
            continue;
        }
        $col = parseSqlBlockLine($trimmed);
        if ($col) {
            $currentCols[] = $col;
            if ($col['unique'] && $col['name'] !== 'id') {
                $currentUniques[] = $col['name'];
            }
            if ($col['fk']) {
                $ref = $col['fk'];
                $fkEntry = ['col' => $col['name'], 'ref' => [$ref[0], $ref[1]]];
                if (!empty($ref[2])) $fkEntry['on_delete'] = $ref[2];
                $currentFks[] = $fkEntry;
            }
        }
        continue;
    }

    // --- Detect markdown table ---
    if (preg_match('/^\|.*\|$/', $trimmed)) {
        // Check if it's a separator row
        if (preg_match('/^\|[-:\s]+\|[-:\s]+\|/', $trimmed)) {
            $state = 'markdown_table';
            continue;
        }
        if ($state === 'markdown_table' || $state === 'table_header') {
            $col = parseMarkdownRow($trimmed);
            if ($col) {
                $currentCols[] = $col;
                if ($col['unique'] && $col['name'] !== 'id') {
                    $currentUniques[] = $col['name'];
                }
                if ($col['fk']) {
                    $ref = $col['fk'];
                    $fkEntry = ['col' => $col['name'], 'ref' => [$ref[0], $ref[1]]];
                    if (!empty($ref[2])) $fkEntry['on_delete'] = $ref[2];
                    $currentFks[] = $fkEntry;
                }
            }
            $state = 'markdown_table';
            continue;
        }
    }

    // --- Post-table details ---
    if ($currentTable && !empty($currentCols)) {
        // Composite Unique constraints
        if (preg_match('/\*\*Composite Unique\*\*:\s*(.+)/i', $trimmed, $m)) {
            $raw = trim($m[1]);
            // Extract only content between first pair of backticks, ignore trailing description
            if (preg_match('/^`([^`]+)`/', $raw, $bt)) {
                $uniq = $bt[1];
            } else {
                $uniq = $raw;
            }
            $currentUniques[] = $uniq;
        }
        // Multiple individual FKs separated by commas
        if (preg_match('/\*\*Foreign Keys?\*\*:\s*(.+)/i', $trimmed, $m)) {
            $fkStr = $m[1];
            // Parse individual FK definitions like: `column` → `table`(`ref`) ON DELETE CASCADE
            if (preg_match_all('/`(\w+)`\s*[→>]\s*`?(\w+(?:_\w+)*)`?\(`?(\w+)`?\)\s*(?:ON\s+DELETE\s+(\w+(?:\s+\w+)?))?/i', $fkStr, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $col = $match[1];
                    $refTable = $match[2];
                    $refCol = $match[3];
                    $onDelete = $match[4] ?? null;
                    // Update existing FK or add new one
                    $found = false;
                    foreach ($currentFks as &$fkItem) {
                        if ($fkItem['col'] === $col) {
                            $fkItem['ref'] = [$refTable, $refCol];
                            $fkItem['on_delete'] = $onDelete;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $currentFks[] = ['col' => $col, 'ref' => [$refTable, $refCol], 'on_delete' => $onDelete];
                    }
                }
            }
            // Also parse NO ACTION / SET NULL patterns
            if (preg_match_all('/`(\w+)`\s*[→>]\s*`?(\w+)`?\(`?(\w+)`?\)\s*ON\s+DELETE\s+(NO\s+ACTION|SET\s+NULL|CASCADE)/i', $fkStr, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $col = $match[1];
                    $refTable = $match[2];
                    $refCol = $match[3];
                    $onDelete = $match[4];
                    foreach ($currentFks as &$fkItem) {
                        if ($fkItem['col'] === $col) {
                            $fkItem['on_delete'] = $onDelete;
                            break;
                        }
                    }
                }
            }
        }
    }
}

// Save last table
saveTable();

// ---- Apply hard-coded unique constraints (from table docs) ----
$compositeUniques = [
    'tenants' => [],
    'users' => ['(tenant_id, username)'],
    'departments' => ['(tenant_id, dept_code)'],
    'patients' => ['(patient_code, tenant_id, history)'],
    'drugs' => ['(drug_code, tenant_id, history)'],
    'drug_batches' => ['(drug_id, batch_no, tenant_id, history)'],
    'lab_sections' => ['(code, tenant_id, history)'],
    'lab_tests' => ['(code, tenant_id, history)'],
    'lab_panels' => ['(code, tenant_id, history)'],
    'lab_panel_tests' => ['(panel_id, test_id)'],
    'items' => ['(code, tenant_id, history)'],
    'item_categories' => ['(code, tenant_id, history)'],
    'suppliers' => ['(code, tenant_id, history)'],
    'stock_locations' => ['(code, tenant_id, history)'],
    'item_batches' => ['(tenant_id, item_id, batch_no)'],
    'procedures' => ['(code, tenant_id, history)'],
    'dialysis_sessions' => ['(code, tenant_id, history)'],
    'icu_wards' => ['(code, tenant_id, history)'],
    'icu_beds' => ['(code, tenant_id, history)'],
    'radiology_exam_types' => ['(code, tenant_id, history)'],
    'micu_assessments' => [],
    'admissions' => ['(admission_code, tenant_id, history)'],
    'wards' => ['(code, tenant_id, history)'],
    'ward_rooms' => ['(code, tenant_id, history)'],
    'opd_consultations' => ['(consultation_code, tenant_id, history)'],
    'ob_consultations' => ['(consultation_code, tenant_id, history)'],
    'er_admissions' => ['(admission_code, tenant_id, history)'],
    'er_triage_records' => ['(triage_code, tenant_id, history)'],
    'er_zones' => ['(code, tenant_id, history)'],
    'er_beds' => ['(code, tenant_id, history)'],
    'or_rooms' => ['(code, tenant_id, history)'],
    'or_schedules' => ['(schedule_code, tenant_id, history)'],
    'dr_rooms' => ['(code, tenant_id, history)'],
    'dr_schedules' => ['(schedule_code, tenant_id, history)'],
    'lab_requests' => ['(request_code, tenant_id, history)'],
    'csr_requests' => ['(request_code, tenant_id, history)'],
    'radiology_requests' => ['(request_code, tenant_id, history)'],
    'pharmacy_requests' => ['(request_code, tenant_id, history)'],
    'procedure_requests' => ['(request_code, tenant_id, history)'],
    'purchase_orders' => ['(po_code, tenant_id, history)'],
    'stock_adjustments' => ['(adjustment_code, tenant_id, history)'],
    'billing_invoices' => ['(invoice_code, tenant_id, history)'],
    'billing_payments' => ['(payment_code, tenant_id, history)'],
    'fund_sources' => ['(code, tenant_id, history)'],
    'philhealth_members' => ['(tenant_id, pin)'],
    'health_care_professionals' => ['(tenant_id, professional_code, history)'],
    'philhealth_z_benefits' => [],
    'hospitals' => ['(code, tenant_id, history)'],
    'hospital_transfers' => ['(transfer_code, tenant_id, history)'],
    'nursing_vitals' => [],
    'nursing_care_notes' => [],
    'nursing_medication_administrations' => [],
    'admission_room_histories' => [],
    'yakap_empanelments' => ['(empanelment_code, tenant_id, history)'],
    'yakap_fpe_records' => [],
    'yakap_screenings' => [],
    'yakap_capitation_payments' => ['(empanelment_id, tranche, history)'],
    'konsulta_enrollments' => ['(enrollment_code, tenant_id, history)'],
    'konsulta_consultations' => ['(consultation_code, tenant_id, history)'],
    'konsulta_prescriptions' => [],
    'konsulta_lab_referrals' => [],
    'cf5_shadow_billings' => ['(philhealth_claim_id, history)'],
];

foreach ($compositeUniques as $tname => $uniques) {
    if (isset($tables[$tname]) && !empty($uniques)) {
        foreach ($uniques as $u) {
            if (!in_array($u, $tables[$tname]['uniques'])) {
                $tables[$tname]['uniques'][] = $u;
            }
        }
    }
}

// ---- Generate SQL Server schema ----
$sql = "-- ============================================================\n";
$sql .= "-- SITHEMR Unified Database Schema (SQL Server / T-SQL)\n";
$sql .= "-- Auto-generated from tables.md\n";
$sql .= "-- Usage: Execute in SQL Server Management Studio (SSMS)\n";
$sql .= "-- ============================================================\n\n";

$sql .= "-- Ensure database exists\n";
$sql .= "IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = N'SITHEMR')\n";
$sql .= "BEGIN\n";
$sql .= "    CREATE DATABASE [SITHEMR];\n";
$sql .= "END\n";
$sql .= "GO\n\n";

$sql .= "USE [SITHEMR];\n";
$sql .= "GO\n\n";

// Table creation order (dependencies first)
$tableOrder = [
    'tenants', 'countries', 'regions', 'provinces_cities', 'barangays',
    'religions', 'genders', 'civil_statuses', 'nationalities', 'occupations',
    'blood_types', 'relationships',
    'departments',
    'users',
    'icd10_codes', 'rvs_codes', 'icd_10_codes',
    'health_care_professionals',
    'philhealth_members', 'philhealth_case_rates', 'philhealth_z_benefits',
    'patients', 'hospitals',
    'lab_sections', 'lab_units', 'specimen_types', 'lab_instruments', 'lab_tests',
    'lab_panels', 'lab_panel_tests', 'reference_ranges',
    'supply_units', 'item_categories', 'suppliers', 'stock_locations', 'items',
    'item_batches', 'item_stock_adjustments',
    'procedure_categories', 'procedures',
    'dialysis_types', 'dialysis_sessions',
    'radiology_modalities', 'radiology_body_parts', 'radiology_exam_types',
    'micu_assessment_parameters', 'micu_assessments', 'micu_assessment_values',
    'delivery_types', 'delivery_complications',
    'ward_room_types', 'wards', 'ward_rooms',
    'opd_visit_types', 'opd_consultation_types', 'opd_referral_sources',
    'opd_services', 'opd_clinics', 'opd_complaints', 'opd_consultations',
    'ob_visit_types', 'ob_fetal_presentations', 'ob_contraceptive_methods', 'ob_consultations',
    'er_acuity_levels', 'er_mobility_statuses', 'er_zones', 'er_beds',
    'er_triage_records', 'er_admissions',
    'admissions',
    'or_rooms', 'or_schedules',
    'dr_rooms', 'dr_schedules',
    'drugs', 'drug_batches', 'purchase_orders', 'purchase_order_items', 'stock_adjustments',
    'lab_requests', 'lab_request_items',
    'csr_requests', 'csr_request_items',
    'radiology_requests', 'radiology_request_items', 'radiology_result_images',
    'pharmacy_requests', 'pharmacy_request_items',
    'procedure_requests', 'procedure_request_items',
    'oecb_core_presentations', 'oecb_fee_schedules', 'oecb_entries',
    'permissions', 'roles', 'role_permissions', 'user_permissions', 'permission_audit_log',
    'fund_sources',
    'billing_invoices', 'billing_invoice_items', 'billing_payments', 'billing_manual_charges',
    'claim_rejections', 'claim_status_polls', 'cf5_shadow_billings',
    'nursing_vitals', 'nursing_care_notes', 'nursing_medication_administrations',
    'admission_room_histories',
    'hospital_transfers',
    'yakap_empanelments', 'yakap_fpe_records', 'yakap_screenings', 'yakap_capitation_payments',
    'konsulta_enrollments', 'konsulta_consultations', 'konsulta_prescriptions', 'konsulta_lab_referrals',
];

function generateTable($name, $def) {
    global $globalTables, $childTables, $compositeUniques;

    $cols = $def['cols'];
    $uniques = $def['uniques'] ?? [];
    $fks = $def['fks'] ?? [];
    $isGlobal = in_array($name, $globalTables);
    $isChild = in_array($name, $childTables);

    $out = "-- {$name}\n";

    // Drop if exists (for idempotency)
    $out .= "IF OBJECT_ID(N'[dbo].[{$name}]', N'U') IS NOT NULL\n";
    $out .= "    DROP TABLE [dbo].[{$name}];\n";
    $out .= "GO\n\n";

    $out .= "CREATE TABLE [dbo].[{$name}] (\n";

    $colDefs = [];
    $pkCol = null;

    foreach ($cols as $col) {
        $n = $col['name'];
        $t = $col['type'];
        $nullable = $col['nullable'] ?? false;
        $ai = $col['ai'] ?? false;
        $pk = $col['pk'] ?? false;
        $default = $col['default'] ?? null;

        if ($pk && $n === 'id') $pkCol = $n;

        $cd = "    [{$n}] {$t}";

        if ($ai || ($n === 'id' && $t === 'BIGINT')) {
            $cd .= ' IDENTITY(1,1)';
        }

        $cd .= $nullable ? ' NULL' : ' NOT NULL';

        if ($default !== null && $default !== '') {
            if (is_numeric($default)) {
                $cd .= " DEFAULT ({$default})";
            } elseif (in_array(strtoupper($default), ['TRUE', 'FALSE'])) {
                $cd .= ' DEFAULT (' . (strtoupper($default) === 'TRUE' ? '1' : '0') . ')';
            } else {
                $cd .= " DEFAULT (N'{$default}')";
            }
        }

        // If tenant_id or is_active/history defaults not set
        if ($n === 'is_active' && $default === null) {
            $cd .= " DEFAULT (1)";
        }
        if ($n === 'history' && $default === null) {
            $cd .= " DEFAULT (1)";
        }

        $colDefs[] = $cd;
    }

    // PK constraint
    if ($pkCol) {
        $colDefs[] = "    CONSTRAINT [PK_{$name}] PRIMARY KEY CLUSTERED ([{$pkCol}] ASC)";
    }

    $out .= implode(",\n", $colDefs);
    $out .= "\n);\n";

    // Unique indexes
    foreach ($uniques as $uniq) {
        if (is_string($uniq) && $uniq !== '') {
            if (preg_match('/^\((.+)\)$/', $uniq, $m)) {
                $colsStr = $m[1];
                $constraintName = 'UQ_' . $name . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $colsStr);
                $out .= "IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'{$constraintName}' AND object_id = OBJECT_ID(N'[dbo].[{$name}]'))\n";
                $out .= "    ALTER TABLE [dbo].[{$name}] ADD CONSTRAINT [{$constraintName}] UNIQUE ({$colsStr});\n";
            } else {
                $constraintName = "UQ_{$name}_{$uniq}";
                $out .= "IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'{$constraintName}' AND object_id = OBJECT_ID(N'[dbo].[{$name}]'))\n";
                $out .= "    ALTER TABLE [dbo].[{$name}] ADD CONSTRAINT [{$constraintName}] UNIQUE ([{$uniq}]);\n";
            }
        }
    }

    // Standard indexes
    $indexCols = [];

    // tenant_id index (for tenant-scoped tables)
    if (!$isGlobal && !$isChild) {
        $hasTenant = false;
        foreach ($cols as $c) {
            if ($c['name'] === 'tenant_id') {
                $hasTenant = true;
                break;
            }
        }
        if ($hasTenant) $indexCols[] = 'tenant_id';
    }

    // FK column indexes
    foreach ($fks as $fk) {
        $col = $fk['col'];
        if (!in_array($col, $indexCols)) $indexCols[] = $col;
    }

    // history, is_active, created_date indexes
    foreach (['history', 'is_active', 'created_date', 'status'] as $ic) {
        foreach ($cols as $c) {
            if ($c['name'] === $ic && !in_array($ic, $indexCols)) {
                $indexCols[] = $ic;
            }
        }
    }

    $indexCols = array_unique($indexCols);

    foreach ($indexCols as $ic) {
        $idxName = "IX_{$name}_{$ic}";
        $out .= "IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = N'{$idxName}' AND object_id = OBJECT_ID(N'[dbo].[{$name}]'))\n";
        $out .= "    CREATE NONCLUSTERED INDEX [{$idxName}] ON [dbo].[{$name}]([{$ic}] ASC);\n";
    }

    // Foreign key constraints
    foreach ($fks as $fk) {
        $col = $fk['col'];
        $refTable = $fk['ref'][0];
        $refCol = $fk['ref'][1];
        $onDelete = $fk['on_delete'] ?? 'NO ACTION';

        $fkName = "FK_{$name}_{$col}";
        $out .= "IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE name = N'{$fkName}' AND parent_object_id = OBJECT_ID(N'[dbo].[{$name}]'))\n";
        $out .= "    ALTER TABLE [dbo].[{$name}] ADD CONSTRAINT [{$fkName}] FOREIGN KEY ([{$col}]) REFERENCES [dbo].[{$refTable}]([{$refCol}]) ON DELETE {$onDelete};\n";
    }

    return $out;
}

// Generate in order
$generated = [];
foreach ($tableOrder as $tname) {
    if (isset($tables[$tname])) {
        $sql .= generateTable($tname, $tables[$tname]);
        $generated[] = $tname;
    }
}

// Remaining tables not in order
foreach ($tables as $tname => $def) {
    if (!in_array($tname, $generated)) {
        $sql .= "\n-- [Extra: {$tname}]\n";
        $sql .= generateTable($tname, $def);
        $generated[] = $tname;
    }
}

file_put_contents(__DIR__ . '/unified_schema.sql', $sql);
echo "Generated unified_schema.sql with " . count($generated) . " tables\n";
echo "Tables generated:\n";
foreach ($generated as $t) {
    echo "  - {$t}\n";
}
