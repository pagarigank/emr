<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yakap_empanelments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('empanelment_code', 30);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('philhealth_member_id')->nullable();

            $table->integer('empanelment_year');
            $table->string('status', 30)->default('Active'); // Active, Transferred, Expired, Cancelled
            $table->date('empanelment_date');
            $table->date('yes_signed_date')->nullable();
            $table->string('yes_reference_no', 50)->nullable();
            $table->unsignedBigInteger('primary_care_provider_id')->nullable();
            $table->unsignedBigInteger('opd_clinic_id')->nullable();

            $table->string('risk_classification', 30)->nullable(); // Low, Moderate, High
            $table->boolean('fpe_completed')->default(false);
            $table->dateTime('fpe_completed_at')->nullable();
            $table->unsignedBigInteger('fpe_consultation_id')->nullable();

            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['empanelment_code', 'tenant_id', 'history'], 'yakap_emp_unique');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('philhealth_member_id')->references('id')->on('philhealth_members')->onDelete('set null');
            $table->foreign('primary_care_provider_id')->references('id')->on('health_care_professionals')->onDelete('set null');
            $table->foreign('opd_clinic_id')->references('id')->on('opd_clinics')->onDelete('set null');
            // FK to opd_consultations added in a separate migration (alphabetical ordering fix)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yakap_empanelments');
    }
};
