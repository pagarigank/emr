<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ward_rooms', function (Blueprint $table) {
            $table->string('status', 30)->default('Available')->after('discount_rate');
        });

        Schema::table('icu_beds', function (Blueprint $table) {
            $table->string('status', 30)->default('Available')->after('ward_id');
        });
    }

    public function down(): void
    {
        Schema::table('ward_rooms', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('icu_beds', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
