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
                $table->unsignedBigInteger('or_schedule_id')->nullable()->after('tenant_id');
                $table->index('or_schedule_id', $tableName . '_or_idx');
                $table->foreign('or_schedule_id', $tableName . '_or_fk')
                      ->references('id')->on('or_schedules')
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
                $table->dropForeign($tableName . '_or_fk');
                $table->dropIndex($tableName . '_or_idx');
                $table->dropColumn('or_schedule_id');
            });
        }
    }
};
