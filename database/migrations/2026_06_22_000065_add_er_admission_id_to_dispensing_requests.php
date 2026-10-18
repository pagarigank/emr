<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('er_admission_id')->nullable()->after('tenant_id');
            $table->foreign('er_admission_id')->references('id')->on('er_admissions')->onDelete('set null');
            $table->index('er_admission_id');
        });
        Schema::table('csr_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('er_admission_id')->nullable()->after('tenant_id');
            $table->foreign('er_admission_id')->references('id')->on('er_admissions')->onDelete('set null');
            $table->index('er_admission_id');
        });
        Schema::table('radiology_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('er_admission_id')->nullable()->after('tenant_id');
            $table->foreign('er_admission_id')->references('id')->on('er_admissions')->onDelete('set null');
            $table->index('er_admission_id');
        });
    }

    public function down(): void
    {
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->dropForeign(['er_admission_id']);
            $table->dropColumn('er_admission_id');
        });
        Schema::table('csr_requests', function (Blueprint $table) {
            $table->dropForeign(['er_admission_id']);
            $table->dropColumn('er_admission_id');
        });
        Schema::table('radiology_requests', function (Blueprint $table) {
            $table->dropForeign(['er_admission_id']);
            $table->dropColumn('er_admission_id');
        });
    }
};
