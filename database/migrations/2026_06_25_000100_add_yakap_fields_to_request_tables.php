<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_request_items', function (Blueprint $table) {
            $table->boolean('is_gamot')->default(false)->after('dispensed_date');
            $table->string('gamot_approval_code', 50)->nullable()->after('is_gamot');
        });

        Schema::table('lab_request_items', function (Blueprint $table) {
            $table->boolean('is_yakap_lab')->default(false)->after('verified_date');
        });

        Schema::table('opd_clinics', function (Blueprint $table) {
            $table->boolean('is_yakap_clinic')->default(false)->after('description');
            $table->string('yakap_accreditation_no', 50)->nullable()->after('is_yakap_clinic');
        });

        Schema::table('health_care_professionals', function (Blueprint $table) {
            $table->boolean('yakap_physician_nominated')->default(false)->after('philhealth_accreditation');
            $table->date('yakap_nomination_date')->nullable()->after('yakap_physician_nominated');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_request_items', function (Blueprint $table) {
            $table->dropColumn(['is_gamot', 'gamot_approval_code']);
        });
        Schema::table('lab_request_items', function (Blueprint $table) {
            $table->dropColumn('is_yakap_lab');
        });
        Schema::table('opd_clinics', function (Blueprint $table) {
            $table->dropColumn(['is_yakap_clinic', 'yakap_accreditation_no']);
        });
        Schema::table('health_care_professionals', function (Blueprint $table) {
            $table->dropColumn(['yakap_physician_nominated', 'yakap_nomination_date']);
        });
    }
};
