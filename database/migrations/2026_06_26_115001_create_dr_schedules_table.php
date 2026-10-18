<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dr_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();

            // Core
            $table->string('schedule_code', 30);
            $table->unsignedBigInteger('dr_room_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('er_admission_id')->nullable()->index();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->unsignedBigInteger('ob_consultation_id')->nullable()->index();

            // Clinical team
            $table->unsignedBigInteger('attending_physician_id')->nullable()->index();
            $table->unsignedBigInteger('pediatrician_id')->nullable()->index();
            $table->unsignedBigInteger('anesthesiologist_id')->nullable()->index();
            $table->unsignedBigInteger('midwife_id')->nullable()->index();

            // Delivery details
            $table->string('delivery_type', 50)->nullable()->comment('NSVD, C/S, AVB, Breech, Vacuum, Forceps, Others');
            $table->text('delivery_description')->nullable();

            // Scheduling
            $table->date('scheduled_date')->nullable();
            $table->string('start_time', 8)->nullable();
            $table->string('end_time', 8)->nullable();
            $table->string('status', 30)->default('Scheduled')->comment('Scheduled, In Progress, Completed, Cancelled');

            // OB History
            $table->string('gestational_age', 20)->nullable();
            $table->date('lmp_date')->nullable();
            $table->date('edc_date')->nullable();
            $table->integer('gravida')->nullable();
            $table->integer('para')->nullable();
            $table->integer('full_term')->nullable();
            $table->integer('preterm')->nullable();
            $table->integer('abortion')->nullable();
            $table->integer('living_children')->nullable();

            // Clinical notes
            $table->text('pre_op_notes')->nullable();
            $table->text('intra_op_notes')->nullable();
            $table->text('post_op_notes')->nullable();
            $table->text('history_of_present_illness')->nullable();
            $table->text('physical_exam_findings')->nullable();
            $table->text('assessment')->nullable();
            $table->text('orders_summary')->nullable();
            $table->text('treatment_notes')->nullable();

            // Neonatal outcome
            $table->string('baby_sex', 10)->nullable();
            $table->decimal('baby_weight', 6, 2)->nullable()->comment('kg');
            $table->decimal('baby_length', 5, 1)->nullable()->comment('cm');
            $table->decimal('head_circumference', 5, 1)->nullable()->comment('cm');
            $table->integer('apgar_score_1min')->nullable();
            $table->integer('apgar_score_5min')->nullable();
            $table->integer('apgar_score_10min')->nullable();
            $table->text('delivery_complications')->nullable();
            $table->string('newborn_outcome', 50)->nullable()->comment('Live Birth, Stillbirth, Early Neonatal Death, Late Neonatal Death');

            // Disposition
            $table->string('disposition', 30)->default('Pending')->comment('Pending, Delivered, Admitted to Ward, Admitted to NICU, Discharged Home, Transferred, AMA, Expired, Cancelled');
            $table->text('discharge_instructions')->nullable();
            $table->timestamp('disposition_datetime')->nullable();
            $table->string('cancellation_reason', 500)->nullable();

            // Audit
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->unique(['schedule_code', 'tenant_id', 'history'], 'uq_dr_schedules');

            $table->foreign('dr_room_id', 'fk_dr_schedules_room')->references('id')->on('dr_rooms')->onDelete('NO ACTION');
            $table->foreign('patient_id', 'fk_dr_schedules_patient')->references('id')->on('patients')->onDelete('NO ACTION');
            $table->foreign('er_admission_id', 'fk_dr_schedules_er_admission')->references('id')->on('er_admissions')->onDelete('SET NULL');
            $table->foreign('admission_id', 'fk_dr_schedules_admission')->references('id')->on('admissions')->onDelete('SET NULL');
            $table->foreign('ob_consultation_id', 'fk_dr_schedules_ob_consultation')->references('id')->on('ob_consultations')->onDelete('SET NULL');
            $table->foreign('attending_physician_id', 'fk_dr_schedules_attending')->references('id')->on('health_care_professionals')->onDelete('NO ACTION');
            $table->foreign('pediatrician_id', 'fk_dr_schedules_pediatrician')->references('id')->on('health_care_professionals')->onDelete('NO ACTION');
            $table->foreign('anesthesiologist_id', 'fk_dr_schedules_anesthesiologist')->references('id')->on('health_care_professionals')->onDelete('NO ACTION');
            $table->foreign('midwife_id', 'fk_dr_schedules_midwife')->references('id')->on('health_care_professionals')->onDelete('NO ACTION');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dr_schedules');
    }
};
