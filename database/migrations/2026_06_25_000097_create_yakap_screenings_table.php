<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yakap_screenings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('empanelment_id');
            $table->unsignedBigInteger('patient_id');

            // Screening Type — 6 required YAKAP cancer screenings
            $table->string('screening_type', 50); // Breast, Cervical, Colon, Prostate, Lung, Oral
            $table->string('screening_name', 200);

            $table->date('screening_date')->nullable();
            $table->string('result', 50)->nullable(); // Normal, Abnormal, Positive, Negative, Inconclusive
            $table->text('result_details')->nullable();
            $table->string('facility_name', 200)->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();

            $table->string('status', 30)->default('Pending'); // Pending, Completed, Referred, Declined
            $table->date('next_screening_due')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('empanelment_id')->references('id')->on('yakap_empanelments')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('performed_by')->references('id')->on('health_care_professionals')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yakap_screenings');
    }
};
