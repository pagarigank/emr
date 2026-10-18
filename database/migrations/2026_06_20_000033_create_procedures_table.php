<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 30);
            $table->string('name', 200);
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->decimal('standard_charge', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_procedures');
            $table->foreign('category_id')->references('id')->on('procedure_categories')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('procedures'); }
};
