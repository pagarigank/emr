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
        Schema::create('opd_consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('patient_id');
            $table->string('consultation_code', 30);
            
            // Link to OPD Master Tables
            $table->unsignedBigInteger('opd_clinic_id')->nullable();
            $table->unsignedBigInteger('attending_physician_id')->nullable();
            $table->unsignedBigInteger('consultation_type_id')->nullable();
            $table->unsignedBigInteger('visit_type_id')->nullable();
            $table->unsignedBigInteger('referral_source_id')->nullable();
            
            $table->dateTime('consultation_datetime');
            $table->string('status', 30)->default('Pending'); // Pending, In Progress, Completed, Cancelled
            $table->string('billing_status', 30)->default('Pending'); // Pending, Billed
            
            // Check-in / Vitals (Nursing)
            $table->integer('vital_bp_systolic')->nullable();
            $table->integer('vital_bp_diastolic')->nullable();
            $table->integer('vital_heart_rate')->nullable();
            $table->decimal('vital_temperature', 4, 1)->nullable();
            $table->integer('vital_respiratory_rate')->nullable();
            $table->decimal('vital_weight', 5, 2)->nullable();
            $table->decimal('vital_height', 5, 2)->nullable();
            $table->decimal('vital_o2_saturation', 5, 2)->nullable();
            
            // Clinical Encounter (SOAP)
            $table->text('chief_complaint')->nullable();
            $table->text('history_of_present_illness')->nullable();
            $table->text('physical_exam_findings')->nullable();
            $table->text('assessment')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('orders_summary')->nullable();
            
            // Disposition
            $table->string('disposition', 50)->nullable();
            $table->date('next_follow_up_date')->nullable();
            
            // Standard audit columns
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
            
            // Constraints
            $table->unique(['consultation_code', 'tenant_id', 'history'], 'opd_consultations_unique');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('opd_clinic_id')->references('id')->on('opd_clinics')->onDelete('set null');
            $table->foreign('attending_physician_id')->references('id')->on('health_care_professionals')->onDelete('set null');
            $table->foreign('consultation_type_id')->references('id')->on('opd_consultation_types')->onDelete('set null');
            $table->foreign('visit_type_id')->references('id')->on('opd_visit_types')->onDelete('set null');
            $table->foreign('referral_source_id')->references('id')->on('opd_referral_sources')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opd_consultations');
    }
};
