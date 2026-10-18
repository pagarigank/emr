<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursing_care_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('admission_id');
            $table->unsignedBigInteger('nurse_id')->nullable();
            $table->date('care_date');
            $table->string('shift', 20)->default('AM')->comment('AM, PM, Night');
            $table->text('subjective')->nullable()->comment('Patient-reported symptoms/concerns');
            $table->text('objective')->nullable()->comment('Observed signs, measurements, findings');
            $table->text('assessment')->nullable()->comment('Nursing assessment/interpretation');
            $table->text('plan')->nullable()->comment('Nursing plan/interventions');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');
            $table->index(['admission_id', 'care_date', 'shift'], 'idx_care_notes_date_shift');
        });
    }

    public function down(): void { Schema::dropIfExists('nursing_care_notes'); }
};
