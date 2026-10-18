<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('provinces_cities', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('region_id');
            // Adding NO ACTION constraint to avoid circular cascade issues in SQL Server
            $table->foreign('parent_id')->references('id')->on('provinces_cities')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provinces_cities', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
