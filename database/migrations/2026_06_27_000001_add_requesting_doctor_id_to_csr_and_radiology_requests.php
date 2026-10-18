<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csr_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('requesting_doctor_id')->nullable()->after('requesting_person');
            $table->foreign('requesting_doctor_id')->references('id')->on('health_care_professionals')->onDelete('set null');
        });

        Schema::table('radiology_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('requesting_doctor_id')->nullable()->after('requesting_person');
            $table->foreign('requesting_doctor_id')->references('id')->on('health_care_professionals')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('csr_requests', function (Blueprint $table) {
            $table->dropForeign(['requesting_doctor_id']);
            $table->dropColumn('requesting_doctor_id');
        });

        Schema::table('radiology_requests', function (Blueprint $table) {
            $table->dropForeign(['requesting_doctor_id']);
            $table->dropColumn('requesting_doctor_id');
        });
    }
};
