<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_exam_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 30);
            $table->string('name', 200);
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('modality_id')->nullable();
            $table->unsignedBigInteger('body_part_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->decimal('standard_charge', 12, 2)->default(0);
            $table->string('preparation', 500)->nullable();
            $table->string('contrast_type', 50)->nullable();
            $table->integer('tat_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_radiology_exam_types');
            $table->foreign('modality_id')->references('id')->on('radiology_modalities')->onDelete('set null');
            $table->foreign('body_part_id')->references('id')->on('radiology_body_parts')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('radiology_exam_types'); }
};
