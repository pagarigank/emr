<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yakap_empanelments', function (Blueprint $table) {
            $table->foreign('fpe_consultation_id')->references('id')->on('opd_consultations')->onDelete('set null');
        });

        Schema::table('yakap_fpe_records', function (Blueprint $table) {
            $table->foreign('consultation_id')->references('id')->on('opd_consultations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('yakap_fpe_records', function (Blueprint $table) {
            $table->dropForeign(['consultation_id']);
        });

        Schema::table('yakap_empanelments', function (Blueprint $table) {
            $table->dropForeign(['fpe_consultation_id']);
        });
    }
};
