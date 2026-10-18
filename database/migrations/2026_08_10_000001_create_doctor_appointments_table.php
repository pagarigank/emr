<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('appointment_code', 30);
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('health_care_professional_id');
            $table->date('appointment_date');
            $table->string('start_time', 8);
            $table->string('end_time', 8);
            $table->string('patient_name', 150);
            $table->string('patient_phone', 30)->nullable();
            $table->string('patient_email', 150)->nullable();
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('Scheduled'); // Scheduled, In Progress, Completed, Cancelled
            $table->text('notes')->nullable();

            // Standard audit columns
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['appointment_code', 'tenant_id', 'history'], 'doctor_appointments_unique');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('no action');
            $table->foreign('health_care_professional_id')->references('id')->on('health_care_professionals')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_appointments');
    }
};
