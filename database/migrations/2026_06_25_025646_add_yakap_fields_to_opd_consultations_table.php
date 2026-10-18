<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opd_consultations', function (Blueprint $table) {
            $table->boolean('is_yakap')->default(false)->after('billing_status');
            $table->unsignedBigInteger('empanelment_id')->nullable()->after('is_yakap');
            $table->string('risk_classification', 30)->nullable()->after('empanelment_id');
            $table->boolean('fpe_completed')->default(false)->after('risk_classification');

            $table->foreign('empanelment_id')->references('id')->on('yakap_empanelments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('opd_consultations', function (Blueprint $table) {
            $table->dropForeign(['empanelment_id']);
            $table->dropColumn(['is_yakap', 'empanelment_id', 'risk_classification', 'fpe_completed']);
        });
    }
};
