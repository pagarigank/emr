<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('er_admissions', function (Blueprint $table) {
            $table->unsignedBigInteger('billing_id')->nullable()->after('oecb_fixed_fee');
            $table->string('billing_status', 30)->default('Pending')->after('billing_id');
        });
    }

    public function down(): void
    {
        Schema::table('er_admissions', function (Blueprint $table) {
            $table->dropColumn(['billing_id', 'billing_status']);
        });
    }
};
