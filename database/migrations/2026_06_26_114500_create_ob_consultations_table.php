<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ob_consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('consultation_code', 30);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('attending_physician_id')->nullable();
            $table->unsignedBigInteger('visit_type_id')->nullable();

            // OB-specific obstetric fields
            $table->string('gestational_age', 20)->nullable();
            $table->date('lmp_date')->nullable();
            $table->date('edc_date')->nullable();
            $table->integer('gravida')->nullable();
            $table->integer('para')->nullable();
            $table->integer('full_term')->nullable();
            $table->integer('preterm')->nullable();
            $table->integer('abortion')->nullable();
            $table->integer('living_children')->nullable();
            $table->text('obstetrical_history')->nullable();

            // Clinical notes (treatment form)
            $table->text('history_of_present_illness')->nullable();
            $table->text('physical_exam_findings')->nullable();
            $table->text('assessment')->nullable();
            $table->text('orders_summary')->nullable();
            $table->text('treatment_notes')->nullable();

            // Disposition
            $table->string('disposition', 50)->default('Pending');
            $table->text('discharge_instructions')->nullable();
            $table->timestamp('disposition_datetime')->nullable();

            $table->string('status', 30)->default('Active');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->unique(['consultation_code', 'tenant_id', 'history']);
            $table->index('tenant_id');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('attending_physician_id')->references('id')->on('health_care_professionals')->onDelete('no action');
            $table->foreign('visit_type_id')->references('id')->on('ob_visit_types')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ob_consultations');
    }
};
