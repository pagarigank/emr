<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_care_professionals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('hcp_code', 30);
            $table->string('full_name', 150);
            $table->string('specialization', 100)->nullable();
            $table->string('prc_license', 50)->nullable();
            $table->string('philhealth_accreditation', 50)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['tenant_id', 'hcp_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_care_professionals');
    }
};
