<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'lab_requests' => 'lab_requests_ob_consultation_id_index',
            'csr_requests' => 'csr_requests_ob_consultation_id_index',
            'radiology_requests' => 'radiology_requests_ob_consultation_id_index',
            'pharmacy_requests' => 'pharmacy_requests_ob_consultation_id_index',
            'procedure_requests' => 'procedure_requests_ob_consultation_id_index',
            'billing_invoices' => 'billing_invoices_ob_consultation_id_index',
        ];

        foreach ($tables as $table => $indexName) {
            if (Schema::hasColumn($table, 'ob_consultation_id')) continue;

            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->unsignedBigInteger('ob_consultation_id')->nullable()->after('id');
                $t->foreign('ob_consultation_id', "fk_{$table}_ob_consultation")
                    ->references('id')->on('ob_consultations')
                    ->onDelete('set null');
                $t->index('ob_consultation_id', "idx_{$table}_ob_consultation_id");
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'lab_requests', 'csr_requests', 'radiology_requests',
            'pharmacy_requests', 'procedure_requests', 'billing_invoices',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'ob_consultation_id')) continue;

            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeign("fk_{$table}_ob_consultation");
                $t->dropIndex("idx_{$table}_ob_consultation_id");
                $t->dropColumn('ob_consultation_id');
            });
        }
    }
};
