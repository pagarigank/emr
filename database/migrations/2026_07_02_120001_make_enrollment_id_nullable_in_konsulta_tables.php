<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konsulta_consultations', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollment_id')->nullable()->change();
        });

        Schema::table('konsulta_prescriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollment_id')->nullable()->change();
        });

        Schema::table('konsulta_lab_referrals', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollment_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('konsulta_consultations', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollment_id')->nullable(false)->change();
        });

        Schema::table('konsulta_prescriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollment_id')->nullable(false)->change();
        });

        Schema::table('konsulta_lab_referrals', function (Blueprint $table) {
            $table->unsignedBigInteger('enrollment_id')->nullable(false)->change();
        });
    }
};
