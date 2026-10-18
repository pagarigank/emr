<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yakap_fpe_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('empanelment_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('consultation_id')->nullable();
            $table->unsignedBigInteger('physician_id')->nullable();
            $table->dateTime('fpe_datetime');

            // YAKAP FPE Risk Assessment
            $table->string('risk_classification', 30)->nullable(); // Low, Moderate, High
            $table->integer('risk_score')->nullable();

            // Lifestyle Factors (YAKAP required)
            $table->string('smoking_status', 30)->nullable(); // Never, Former, Current
            $table->string('alcohol_use', 30)->nullable(); // None, Moderate, Heavy
            $table->string('diet_type', 50)->nullable();
            $table->string('physical_activity_level', 30)->nullable(); // Sedentary, Moderate, Active

            // Family & Social History
            $table->text('family_history')->nullable();
            $table->text('past_medical_history')->nullable();
            $table->text('immunization_history')->nullable();
            $table->text('obstetric_history')->nullable();

            // Anthropometric Measurements
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->decimal('waist_circumference_cm', 5, 1)->nullable();

            // FPE Status
            $table->string('status', 30)->default('Completed'); // Completed, Pending, Skipped
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('empanelment_id')->references('id')->on('yakap_empanelments')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            // FK to opd_consultations added in a separate migration (alphabetical ordering fix)
            $table->foreign('physician_id')->references('id')->on('health_care_professionals')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yakap_fpe_records');
    }
};
