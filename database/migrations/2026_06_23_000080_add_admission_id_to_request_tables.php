<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // lab_requests: add admission_id
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('admission_id')->nullable()->after('patient_id');
            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('set null');
            $table->index('admission_id');
        });

        // pharmacy_requests: add admission_id
        Schema::table('pharmacy_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('admission_id')->nullable()->after('er_admission_id');
            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('set null');
            $table->index('admission_id');
        });

        // csr_requests: add patient_id and admission_id
        Schema::table('csr_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('request_code');
            $table->unsignedBigInteger('admission_id')->nullable()->after('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('set null');
            $table->index('admission_id');
        });

        // radiology_requests: add patient_id and admission_id
        Schema::table('radiology_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('request_code');
            $table->unsignedBigInteger('admission_id')->nullable()->after('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('set null');
            $table->index('admission_id');
        });

        // procedure_requests: add admission_id
        Schema::table('procedure_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('admission_id')->nullable()->after('er_admission_id');
            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('set null');
            $table->index('admission_id');
        });
    }

    public function down(): void
    {
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->dropForeign(['admission_id']);
            $table->dropColumn('admission_id');
        });
        Schema::table('pharmacy_requests', function (Blueprint $table) {
            $table->dropForeign(['admission_id']);
            $table->dropColumn('admission_id');
        });
        Schema::table('csr_requests', function (Blueprint $table) {
            $table->dropForeign(['admission_id']);
            $table->dropForeign(['patient_id']);
            $table->dropColumn(['patient_id', 'admission_id']);
        });
        Schema::table('radiology_requests', function (Blueprint $table) {
            $table->dropForeign(['admission_id']);
            $table->dropForeign(['patient_id']);
            $table->dropColumn(['patient_id', 'admission_id']);
        });
        Schema::table('procedure_requests', function (Blueprint $table) {
            $table->dropForeign(['admission_id']);
            $table->dropColumn('admission_id');
        });
    }
};
