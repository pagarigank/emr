<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('tenant_id')->index();
            $table->unsignedBigInteger('admission_id')->nullable()->after('billing_invoice_id')->index();
            $table->unsignedBigInteger('er_admission_id')->nullable()->after('admission_id')->index();
            $table->unsignedBigInteger('opd_consultation_id')->nullable()->after('er_admission_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->dropColumn(['patient_id', 'admission_id', 'er_admission_id', 'opd_consultation_id']);
        });
    }
};
