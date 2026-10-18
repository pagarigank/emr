<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearClinicalData extends Command
{
    protected $signature = 'clinical:clear {--force : Skip the confirmation prompt}';

    protected $description = 'Clear all patient-facing clinical data while preserving reference/system tables';

    protected array $tables = [
        'billing_manual_charges',
        'billing_invoice_items',
        'billing_payments',
        'billing_invoices',
        'claim_items',
        'claim_documents',
        'claim_rejections',
        'claim_status_polls',
        'cf5_shadow_billings',
        'hospital_transfers',
        'philhealth_claims',
        'lab_request_items',
        'lab_requests',
        'csr_request_items',
        'csr_requests',
        'radiology_request_items',
        'radiology_requests',
        'pharmacy_request_items',
        'pharmacy_requests',
        'procedure_request_items',
        'procedure_requests',
        'yakap_capitation_payments',
        'yakap_screenings',
        'yakap_fpe_records',
        'yakap_empanelments',
        'dr_schedules',
        'or_schedules',
        'ob_consultations',
        'opd_consultations',
        'nursing_medication_administrations',
        'nursing_care_notes',
        'nursing_vitals',
        'micu_assessment_values',
        'micu_assessments',
        'admissions',
        'dialysis_sessions',
        'er_admissions',
        'er_triage_records',
        'patients',
    ];

    public function handle(): int
    {
        if (!$this->option('force')) {
            $confirmed = $this->components->confirm(
                'Are you sure you want to delete ALL patient clinical data? This action cannot be undone.',
                false
            );

            if (!$confirmed) {
                $this->components->info('Command cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->components->warn('=== CLEARING ALL PATIENT CLINICAL DATA ===');
        $this->components->warn('This will delete all data from patient-facing tables.');
        $this->components->warn('Reference and system tables will be preserved.');
        $this->line('');

        $deleted = [];

        DB::transaction(function () use (&$deleted) {
            foreach ($this->tables as $table) {
                if (!Schema::hasTable($table)) {
                    $this->warn("  [SKIP] Table '{$table}' does not exist.");
                    $deleted[$table] = 0;
                    continue;
                }

                $this->info("  Clearing table: {$table}...");

                $count = DB::table($table)->count();
                DB::table($table)->delete();
                DB::statement("DBCC CHECKIDENT(?, RESEED, 0)", [$table]);

                $deleted[$table] = $count;
                $this->info("    ✓ Deleted {$count} record(s) from {$table}");
            }
        });

        $this->line('');
        $this->components->info('=== CLEAR SUMMARY ===');

        $total = 0;
        foreach ($deleted as $table => $count) {
            if ($count > 0) {
                $this->line(sprintf('  %-45s %s records', $table, number_format($count)));
                $total += $count;
            } else {
                $this->line(sprintf('  %-45s 0 records (empty or skipped)', $table));
            }
        }

        $this->line('');
        $this->components->info("Total records deleted: " . number_format($total));
        $this->components->warn('=== CLEAR COMPLETE ===');

        return Command::SUCCESS;
    }
}
