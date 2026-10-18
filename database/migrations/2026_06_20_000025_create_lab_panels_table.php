<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_panels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 20);
            $table->string('name', 200);
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('lab_section_id')->nullable();
            $table->integer('tat_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_lab_panels');
            $table->foreign('lab_section_id')->references('id')->on('lab_sections')->onDelete('set null');
        });

        Schema::create('lab_panel_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('panel_id');
            $table->unsignedBigInteger('test_id');
            $table->integer('display_order')->default(0);
            $table->unique(['panel_id', 'test_id'], 'uq_panel_tests');
            $table->foreign('panel_id')->references('id')->on('lab_panels')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('lab_tests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_panel_tests');
        Schema::dropIfExists('lab_panels');
    }
};
