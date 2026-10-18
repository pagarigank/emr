<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('er_acuity_levels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('color_code', 50)->nullable();
            $table->string('description', 255)->nullable();
            
            // Standard audit columns
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('er_acuity_levels');
    }
};
