<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_modalities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'history'], 'uq_radiology_modalities');
        });
    }

    public function down(): void { Schema::dropIfExists('radiology_modalities'); }
};
