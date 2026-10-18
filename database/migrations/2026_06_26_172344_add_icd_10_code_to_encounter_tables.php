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
        Schema::table('opd_consultations', function (Blueprint $table) {
            $table->string('icd_10_code', 20)->nullable()->after('assessment');
        });
        Schema::table('er_admissions', function (Blueprint $table) {
            $table->string('icd_10_code', 20)->nullable()->after('assessment');
        });
        Schema::table('admissions', function (Blueprint $table) {
            $table->string('icd_10_code', 20)->nullable()->after('assessment');
        });
        Schema::table('ob_consultations', function (Blueprint $table) {
            $table->string('icd_10_code', 20)->nullable()->after('assessment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opd_consultations', function (Blueprint $table) {
            $table->dropColumn('icd_10_code');
        });
        Schema::table('er_admissions', function (Blueprint $table) {
            $table->dropColumn('icd_10_code');
        });
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropColumn('icd_10_code');
        });
        Schema::table('ob_consultations', function (Blueprint $table) {
            $table->dropColumn('icd_10_code');
        });
    }
};
