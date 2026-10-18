<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('oecb_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('eecl_item_id')->nullable()->after('er_admission_id');
            $table->foreign('eecl_item_id')->references('id')->on('eecl_items')->onDelete('set null');
            $table->decimal('quantity', 8, 2)->default(1)->after('oecb_fixed_fee');
            $table->decimal('total_amount', 12, 2)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('oecb_entries', function (Blueprint $table) {
            $table->dropForeign(['eecl_item_id']);
            $table->dropColumn(['eecl_item_id', 'quantity', 'total_amount']);
        });
    }
};
