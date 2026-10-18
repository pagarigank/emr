<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('or_schedules', 'ob_consultation_id')) return;

        Schema::table('or_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('ob_consultation_id')->nullable()->after('admission_id');
            $table->foreign('ob_consultation_id', 'fk_or_schedules_ob_consultation')
                ->references('id')->on('ob_consultations')
                ->onDelete('set null');
            $table->index('ob_consultation_id', 'idx_or_schedules_ob_consultation_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('or_schedules', 'ob_consultation_id')) return;

        Schema::table('or_schedules', function (Blueprint $table) {
            $table->dropForeign('fk_or_schedules_ob_consultation');
            $table->dropIndex('idx_or_schedules_ob_consultation_id');
            $table->dropColumn('ob_consultation_id');
        });
    }
};
