<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('health_insurance_provider_id')->nullable()->after('fund_source_id');
            $table->foreign('health_insurance_provider_id', 'fk_pay_hpi')
                  ->references('id')->on('health_insurance_providers')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->dropForeign('fk_pay_hpi');
            $table->dropColumn('health_insurance_provider_id');
        });
    }
};
