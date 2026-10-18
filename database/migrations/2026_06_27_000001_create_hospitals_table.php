<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 20);
            $table->string('name', 200);
            $table->string('address', 500)->nullable();
            $table->string('contact_number', 50)->nullable();
            $table->string('hospital_type', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->unique(['code', 'tenant_id', 'history'], 'uq_hospitals_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
