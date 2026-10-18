<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('er_triage_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('er_acuity_level_id')->nullable();
            $table->unsignedBigInteger('er_mobility_status_id')->nullable();
            
            $table->dateTime('triage_datetime');
            $table->string('chief_complaint', 1000);
            
            // Vital Signs
            $table->decimal('vital_temperature', 4, 1)->nullable();
            $table->integer('vital_heart_rate')->nullable();
            $table->integer('vital_respiratory_rate')->nullable();
            $table->integer('vital_bp_systolic')->nullable();
            $table->integer('vital_bp_diastolic')->nullable();
            $table->decimal('vital_o2_saturation', 5, 2)->nullable();
            $table->integer('vital_pain_score')->nullable(); // 0-10
            
            // AVPU
            $table->string('level_of_consciousness', 50)->nullable();
            
            $table->text('allergies')->nullable();
            $table->text('notes')->nullable();
            
            // Standard audit columns
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('er_acuity_level_id')->references('id')->on('er_acuity_levels')->onDelete('no action');
            $table->foreign('er_mobility_status_id')->references('id')->on('er_mobility_statuses')->onDelete('no action');
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('er_triage_records');
    }
};
