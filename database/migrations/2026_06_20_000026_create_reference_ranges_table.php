<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_test_id');
            $table->string('gender', 10)->default('Any')->comment('Male, Female, Any');
            $table->decimal('age_min_years', 5, 1)->default(0);
            $table->decimal('age_max_years', 5, 1)->default(999.9);
            $table->decimal('min_value', 18, 6)->nullable();
            $table->decimal('max_value', 18, 6)->nullable();
            $table->string('normal_values_text', 255)->nullable()->comment('For non-numeric ranges');
            $table->string('interpretation', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['lab_test_id', 'gender', 'age_min_years', 'age_max_years', 'history'], 'uq_ref_ranges');
            $table->foreign('lab_test_id')->references('id')->on('lab_tests')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('reference_ranges'); }
};
