<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dialysis_sessions', function (Blueprint $table) {
            $table->text('history_of_present_illness')->nullable()->after('notes');
            $table->text('physical_exam_findings')->nullable()->after('history_of_present_illness');
            $table->text('assessment')->nullable()->after('physical_exam_findings');
            $table->text('orders_summary')->nullable()->after('assessment');
            $table->text('treatment_notes')->nullable()->after('orders_summary');
            $table->string('disposition', 30)->default('Pending')->after('treatment_notes')->comment('Pending, Completed, Transferred, Discharged, Cancelled');
            $table->text('discharge_instructions')->nullable()->after('disposition');
            $table->timestamp('disposition_datetime')->nullable()->after('discharge_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('dialysis_sessions', function (Blueprint $table) {
            $table->dropColumn(['history_of_present_illness', 'physical_exam_findings', 'assessment', 'orders_summary', 'treatment_notes', 'disposition', 'discharge_instructions', 'disposition_datetime']);
        });
    }
};
