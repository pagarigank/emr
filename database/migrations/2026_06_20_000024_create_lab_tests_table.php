<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 20);
            $table->string('name', 200);
            $table->string('short_name', 50)->nullable();
            $table->unsignedBigInteger('lab_section_id')->nullable();
            $table->unsignedBigInteger('specimen_type_id')->nullable();
            $table->unsignedBigInteger('lab_unit_id')->nullable();
            $table->string('test_type', 20)->default('Numeric')->comment('Numeric, Text, Categorical');
            $table->integer('tat_minutes')->nullable()->comment('Turnaround time in minutes');
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_lab_tests');
            $table->foreign('lab_section_id')->references('id')->on('lab_sections')->onDelete('set null');
            $table->foreign('specimen_type_id')->references('id')->on('specimen_types')->onDelete('set null');
            $table->foreign('lab_unit_id')->references('id')->on('lab_units')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('lab_tests'); }
};
