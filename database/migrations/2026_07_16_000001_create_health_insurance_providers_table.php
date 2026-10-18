<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_insurance_providers', function (Blueprint $table) {
            $table->id();
            $table->integer('tenant_id')->index();
            $table->string('code', 30);
            $table->string('name', 200);
            $table->string('contact_person', 200)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('website', 200)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('provider_type', 50)->nullable()->comment('HMO, Government, Private, Employer');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(1);
            $table->integer('created_by')->nullable();
            $table->dateTime('created_date')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'hip_c_t_h_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_insurance_providers');
    }
};
