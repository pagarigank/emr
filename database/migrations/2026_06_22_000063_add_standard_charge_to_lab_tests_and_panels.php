<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->decimal('standard_charge', 12, 2)->default(0)->after('description');
        });
        Schema::table('lab_panels', function (Blueprint $table) {
            $table->decimal('standard_charge', 12, 2)->default(0)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->dropColumn('standard_charge');
        });
        Schema::table('lab_panels', function (Blueprint $table) {
            $table->dropColumn('standard_charge');
        });
    }
};
