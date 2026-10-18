<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oecb_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('er_admission_id')->index();
            $table->string('core_presentation', 200)->nullable();
            $table->string('oecb_package_code', 30)->nullable();
            $table->decimal('oecb_fixed_fee', 12, 2)->nullable();
            $table->unsignedBigInteger('billing_id')->nullable()->index();
            $table->string('billing_status', 30)->default('Pending');
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('er_admission_id')->references('id')->on('er_admissions')->onDelete('cascade');
        });

        Schema::table('er_admissions', function (Blueprint $table) {
            $table->dropColumn(['core_presentation', 'oecb_package_code', 'oecb_fixed_fee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oecb_entries');

        Schema::table('er_admissions', function (Blueprint $table) {
            $table->string('core_presentation', 200)->nullable()->after('is_oecb');
            $table->string('oecb_package_code', 30)->nullable()->after('core_presentation');
            $table->decimal('oecb_fixed_fee', 12, 2)->nullable()->after('oecb_package_code');
        });
    }
};
