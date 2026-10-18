<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'lab_requests',
            'pharmacy_requests',
            'csr_requests',
            'radiology_requests',
            'procedure_requests',
            'billing_invoices',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Add column
                $table->unsignedBigInteger('opd_consultation_id')->nullable()->after('tenant_id');
                // Add index & foreign key constraint
                $table->index('opd_consultation_id', $tableName . '_opd_consult_idx');
                $table->foreign('opd_consultation_id', $tableName . '_opd_consult_fk')
                      ->references('id')->on('opd_consultations')
                      ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'lab_requests',
            'pharmacy_requests',
            'csr_requests',
            'radiology_requests',
            'procedure_requests',
            'billing_invoices',
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign($tableName . '_opd_consult_fk');
                $table->dropIndex($tableName . '_opd_consult_idx');
                $table->dropColumn('opd_consultation_id');
            });
        }
    }
};
