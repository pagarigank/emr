<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philhealth_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('claim_code', 20);
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('er_admission_id')->nullable()->index();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->unsignedBigInteger('philhealth_member_id')->nullable()->index();
            $table->unsignedBigInteger('claim_batch_id')->nullable()->index();
            $table->string('status', 30)->default('Draft');
            $table->string('claim_type', 30)->default('ACR');
            $table->text('admitting_diagnosis')->nullable();
            $table->text('discharge_diagnosis')->nullable();
            $table->unsignedBigInteger('icd10_code_id')->nullable()->index();
            $table->date('date_admitted')->nullable();
            $table->time('time_admitted')->nullable();
            $table->date('date_discharged')->nullable();
            $table->time('time_discharged')->nullable();
            $table->text('history_of_present_illness')->nullable();
            $table->text('pertinent_past_medical_history')->nullable();
            $table->text('physical_exam_findings')->nullable();
            $table->text('course_in_ward')->nullable();
            $table->string('outcome', 30)->nullable();
            $table->unsignedBigInteger('case_rate_id')->nullable()->index();
            $table->decimal('total_actual_amount', 12, 2)->default(0);
            $table->decimal('total_claimed_amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['claim_code', 'tenant_id', 'history']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philhealth_claims');
    }
};
