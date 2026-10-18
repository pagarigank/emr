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
        Schema::table('health_care_professionals', function (Blueprint $table) {
            $table->string('last_name', 50)->nullable()->after('full_name');
            $table->string('first_name', 50)->nullable()->after('last_name');
            $table->string('middle_name', 50)->nullable()->after('first_name');
            $table->string('suffix', 10)->nullable()->after('middle_name');
            $table->date('date_of_birth')->nullable()->after('suffix');
            $table->string('tin', 30)->nullable()->after('date_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_care_professionals', function (Blueprint $table) {
            $table->dropColumn([
                'last_name', 'first_name', 'middle_name', 'suffix', 'date_of_birth', 'tin'
            ]);
        });
    }
};
