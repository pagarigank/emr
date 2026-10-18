<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_sources', function (Blueprint $table) {
            $table->decimal('budget_amount', 12, 2)->default(0)->after('description');
            $table->decimal('used_amount', 12, 2)->default(0)->after('budget_amount');
        });
    }

    public function down(): void
    {
        Schema::table('fund_sources', function (Blueprint $table) {
            $table->dropColumn(['budget_amount', 'used_amount']);
        });
    }
};
