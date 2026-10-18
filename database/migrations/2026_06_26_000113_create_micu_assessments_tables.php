<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('micu_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('admission_id');
            $table->unsignedBigInteger('assessed_by');
            $table->timestamp('assessed_at');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');
            $table->foreign('assessed_by')->references('id')->on('health_care_professionals')->onDelete('no action');
        });

        Schema::create('micu_assessment_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('micu_assessment_id');
            $table->unsignedBigInteger('micu_assessment_parameter_id');
            $table->string('value', 50)->nullable();
            $table->string('unit', 30)->nullable();

            $table->foreign('micu_assessment_id', 'fk_mav_assessment')
                  ->references('id')->on('micu_assessments')->onDelete('cascade');
            $table->foreign('micu_assessment_parameter_id', 'fk_mav_parameter')
                  ->references('id')->on('micu_assessment_parameters')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('micu_assessment_values');
        Schema::dropIfExists('micu_assessments');
    }
};
