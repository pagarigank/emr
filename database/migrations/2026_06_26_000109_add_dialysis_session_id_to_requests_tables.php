<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
                $table->unsignedBigInteger('dialysis_session_id')->nullable()->after('tenant_id');
                $table->index('dialysis_session_id', $tableName . '_ds_idx');
                $table->foreign('dialysis_session_id', $tableName . '_ds_fk')
                      ->references('id')->on('dialysis_sessions')
                      ->onDelete('no action');
            });
        }
    }

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
                $table->dropForeign($tableName . '_ds_fk');
                $table->dropIndex($tableName . '_ds_idx');
                $table->dropColumn('dialysis_session_id');
            });
        }
    }
};
