<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('or_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('schedule_code', 30);
            $table->unsignedBigInteger('or_room_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('er_admission_id')->nullable();
            $table->unsignedBigInteger('surgeon_id')->nullable();
            $table->unsignedBigInteger('anesthesiologist_id')->nullable();
            $table->string('procedure_type', 100)->nullable();
            $table->text('procedure_description')->nullable();
            $table->date('scheduled_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status', 30)->default('Scheduled');
            $table->text('pre_op_notes')->nullable();
            $table->text('intra_op_notes')->nullable();
            $table->text('post_op_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['schedule_code', 'tenant_id', 'history'], 'or_schedules_unique');
            $table->foreign('or_room_id')->references('id')->on('or_rooms')->onDelete('no action');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('er_admission_id')->references('id')->on('er_admissions')->onDelete('no action');
            $table->foreign('surgeon_id')->references('id')->on('health_care_professionals')->onDelete('no action');
            $table->foreign('anesthesiologist_id')->references('id')->on('health_care_professionals')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('or_schedules');
    }
};
