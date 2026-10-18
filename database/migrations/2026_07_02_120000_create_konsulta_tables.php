<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konsulta_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('enrollment_code', 30);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('philhealth_member_id')->nullable();
            $table->unsignedBigInteger('primary_care_provider_id')->nullable();

            $table->date('enrollment_date');
            $table->date('expiry_date')->nullable();
            $table->string('status', 30)->default('Active');
            $table->string('risk_classification', 30)->nullable();
            $table->boolean('fpe_completed')->default(false);
            $table->dateTime('fpe_completed_at')->nullable();

            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['enrollment_code', 'tenant_id', 'history'], 'konsulta_enr_unique');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('philhealth_member_id')->references('id')->on('philhealth_members')->onDelete('set null');
            $table->foreign('primary_care_provider_id')->references('id')->on('health_care_professionals')->onDelete('set null');
        });

        Schema::create('konsulta_consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('consultation_code', 30);
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('provider_id')->nullable();

            $table->dateTime('consultation_date');
            $table->string('chief_complaint', 500)->nullable();
            $table->string('diagnosis', 500)->nullable();
            $table->unsignedBigInteger('icd10_code_id')->nullable();
            $table->text('assessment')->nullable();
            $table->text('management_plan')->nullable();
            $table->text('notes')->nullable();

            $table->string('bp', 20)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->integer('heart_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();

            $table->boolean('is_fpe')->default(false);
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['consultation_code', 'tenant_id', 'history'], 'konsulta_cons_unique');
            $table->foreign('enrollment_id')->references('id')->on('konsulta_enrollments')->onDelete('no action');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('provider_id')->references('id')->on('health_care_professionals')->onDelete('set null');
            $table->foreign('icd10_code_id')->references('id')->on('icd10_codes')->onDelete('set null');
        });

        Schema::create('konsulta_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('consultation_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('patient_id');

            $table->string('medicine_name', 200);
            $table->string('dosage', 100)->nullable();
            $table->string('frequency', 100)->nullable();
            $table->string('duration', 100)->nullable();
            $table->integer('quantity')->default(0);
            $table->boolean('is_gamot_covered')->default(false);
            $table->string('status', 30)->default('Prescribed');

            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('consultation_id')->references('id')->on('konsulta_consultations')->onDelete('no action');
            $table->foreign('enrollment_id')->references('id')->on('konsulta_enrollments')->onDelete('no action');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
        });

        Schema::create('konsulta_lab_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('consultation_id');
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('patient_id');

            $table->string('test_type', 200);
            $table->date('referral_date');
            $table->date('performed_date')->nullable();
            $table->string('result', 500)->nullable();
            $table->string('status', 30)->default('Pending');
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('consultation_id')->references('id')->on('konsulta_consultations')->onDelete('no action');
            $table->foreign('enrollment_id')->references('id')->on('konsulta_enrollments')->onDelete('no action');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konsulta_lab_referrals');
        Schema::dropIfExists('konsulta_prescriptions');
        Schema::dropIfExists('konsulta_consultations');
        Schema::dropIfExists('konsulta_enrollments');
    }
};
