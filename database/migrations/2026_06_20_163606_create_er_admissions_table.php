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
        Schema::create('er_admissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('admission_code', 30);
            $table->unsignedBigInteger('er_triage_record_id')->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('er_bed_id')->nullable();
            $table->unsignedBigInteger('attending_physician_id')->nullable();
            $table->string('arrival_mode', 50)->nullable(); // Ambulance, Walk-in, Wheelchair, Police
            $table->dateTime('admission_datetime');
            $table->text('initial_diagnosis')->nullable();
            $table->text('treatment_notes')->nullable();
            $table->string('disposition', 50)->default('Pending'); // Pending, Admitted to Ward, Admitted to ICU, Discharged Home, Expired, AMA
            $table->dateTime('disposition_datetime')->nullable();
            $table->string('status', 30)->default('Active'); // Active, Completed, Cancelled
            
            // Standard audit columns
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            // Constraints
            $table->unique(['admission_code', 'tenant_id', 'history'], 'er_admissions_unique');
            $table->foreign('er_triage_record_id')->references('id')->on('er_triage_records')->onDelete('no action');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('er_bed_id')->references('id')->on('er_beds')->onDelete('no action');
            $table->foreign('attending_physician_id')->references('id')->on('health_care_professionals')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('er_admissions');
    }
};
