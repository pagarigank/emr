<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philhealth_case_rates', function (Blueprint $table) {
            $table->id();
            $table->string('rvs_code', 10)->nullable()->index();
            $table->string('icd10_code', 10)->nullable()->index();
            $table->string('description', 255);
            $table->decimal('hf_fee', 12, 2)->default(0);
            $table->decimal('pf_fee', 12, 2)->default(0);
            $table->decimal('total_rate', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philhealth_case_rates');
    }
};
