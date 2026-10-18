<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('health_care_professionals');
        Schema::dropIfExists('doctors');

        Schema::create('health_care_professionals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('professional_code', 30);
            $table->string('professional_type', 50)->default('Doctor')->comment('Doctor, Nurse, Midwife, etc.');
            $table->string('full_name', 150);
            $table->string('specialization', 100)->nullable();
            $table->string('prc_license', 50)->nullable();
            $table->string('philhealth_accreditation', 50)->nullable();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->string('contact_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['tenant_id', 'professional_code', 'history']);
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_care_professionals');

        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('doctor_code', 20);
            $table->string('full_name', 150);
            $table->string('specialization', 100)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('license_number', 50)->nullable();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
            $table->unique(['tenant_id', 'doctor_code']);
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });

        Schema::create('health_care_professionals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('hcp_code', 30);
            $table->string('full_name', 150);
            $table->string('specialization', 100)->nullable();
            $table->string('prc_license', 50)->nullable();
            $table->string('philhealth_accreditation', 50)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
            $table->unique(['tenant_id', 'hcp_code']);
        });
    }
};
