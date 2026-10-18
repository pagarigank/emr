<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('fund_source_id')->nullable()->after('payment_method')->index();
            $table->foreign('fund_source_id')->references('id')->on('fund_sources')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->dropForeign(['fund_source_id']);
            $table->dropColumn('fund_source_id');
        });
    }
};
