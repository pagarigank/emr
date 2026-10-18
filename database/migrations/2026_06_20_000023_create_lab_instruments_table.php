<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_instruments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 20);
            $table->string('name', 100);
            $table->string('model', 100)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->unsignedBigInteger('lab_section_id')->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_lab_instruments');
            $table->foreign('lab_section_id')->references('id')->on('lab_sections')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('lab_instruments'); }
};
