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
        Schema::table('or_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('admission_id')->nullable()->after('er_admission_id')->index();
            $table->foreign('admission_id', 'fk_or_schedules_admission')
                  ->references('id')->on('admissions')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('or_schedules', function (Blueprint $table) {
            $table->dropForeign('fk_or_schedules_admission');
            $table->dropColumn('admission_id');
        });
    }
};
