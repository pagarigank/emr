<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursing_vitals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('admission_id');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->dateTime('recorded_at');
            $table->decimal('vital_temperature', 4, 1)->nullable()->comment('Celsius');
            $table->integer('vital_heart_rate')->nullable()->comment('bpm');
            $table->integer('vital_respiratory_rate')->nullable()->comment('breaths/min');
            $table->integer('vital_bp_systolic')->nullable()->comment('mmHg');
            $table->integer('vital_bp_diastolic')->nullable()->comment('mmHg');
            $table->decimal('vital_o2_saturation', 5, 2)->nullable()->comment('%');
            $table->integer('vital_pain_score')->nullable()->comment('0-10');
            $table->string('level_of_consciousness', 50)->nullable()->comment('Alert, Verbal, Pain, Unresponsive');
            $table->decimal('blood_sugar', 5, 2)->nullable()->comment('mg/dL');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();

            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('nursing_vitals'); }
};
