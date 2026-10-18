<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['lab_requests', 'csr_requests', 'radiology_requests', 'pharmacy_requests', 'procedure_requests', 'billing_invoices'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->foreignId('dr_schedule_id')->nullable()->after('or_schedule_id')
                    ->constrained('dr_schedules')
                    ->onDelete('SET NULL')
                    ->index("idx_{$table}_dr_schedule");
            });
        }
    }

    public function down(): void
    {
        $tables = ['lab_requests', 'csr_requests', 'radiology_requests', 'pharmacy_requests', 'procedure_requests', 'billing_invoices'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeign("fk_{$table}_dr_schedule");
                $t->dropIndex("idx_{$table}_dr_schedule");
                $t->dropColumn('dr_schedule_id');
            });
        }
    }
};
