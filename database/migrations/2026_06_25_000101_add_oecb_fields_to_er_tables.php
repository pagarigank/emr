<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('er_admissions', function (Blueprint $table) {
            $table->boolean('is_oecb')->default(false)->after('disposition');
            $table->string('core_presentation', 200)->nullable()->after('is_oecb');
            $table->string('oecb_package_code', 30)->nullable()->after('core_presentation');
            $table->decimal('oecb_fixed_fee', 12, 2)->nullable()->after('oecb_package_code');
        });
    }

    public function down(): void
    {
        Schema::table('er_admissions', function (Blueprint $table) {
            $table->dropColumn(['is_oecb', 'core_presentation', 'oecb_package_code', 'oecb_fixed_fee']);
        });
    }
};
