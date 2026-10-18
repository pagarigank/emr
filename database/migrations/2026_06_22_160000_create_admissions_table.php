<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('admission_code', 30);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('er_admission_id')->nullable();
            $table->string('admission_type', 30);
            $table->string('admission_source', 30);
            $table->dateTime('admission_date');
            $table->text('admitting_diagnosis')->nullable();
            $table->unsignedBigInteger('attending_physician_id')->nullable();
            $table->unsignedBigInteger('admitting_physician_id')->nullable();
            $table->unsignedBigInteger('ward_room_id')->nullable();
            $table->unsignedBigInteger('icu_bed_id')->nullable();
            $table->string('status', 30)->default('Admitted');
            $table->dateTime('discharge_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['admission_code', 'tenant_id', 'history'], 'admissions_unique');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('er_admission_id')->references('id')->on('er_admissions')->onDelete('no action');
            $table->foreign('attending_physician_id')->references('id')->on('health_care_professionals')->onDelete('no action');
            $table->foreign('admitting_physician_id')->references('id')->on('health_care_professionals')->onDelete('no action');
            $table->foreign('ward_room_id')->references('id')->on('ward_rooms')->onDelete('no action');
            $table->foreign('icu_bed_id')->references('id')->on('icu_beds')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
