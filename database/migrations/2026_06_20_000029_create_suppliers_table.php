<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 20);
            $table->string('name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('tin', 30)->nullable()->comment('Tax Identification Number');
            $table->string('payment_terms', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_suppliers');
        });
    }

    public function down(): void { Schema::dropIfExists('suppliers'); }
};
