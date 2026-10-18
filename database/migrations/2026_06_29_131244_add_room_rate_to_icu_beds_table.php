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
        Schema::table('icu_beds', function (Blueprint $table) {
            $table->decimal('room_rate', 12, 2)->default(0)->after('ward_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('icu_beds', function (Blueprint $table) {
            $table->dropColumn('room_rate');
        });
    }
};
