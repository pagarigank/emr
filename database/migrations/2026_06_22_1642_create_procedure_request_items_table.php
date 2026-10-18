<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procedure_request_id');
            $table->unsignedBigInteger('procedure_id')->nullable();
            $table->string('procedure_name', 200);
            $table->string('category_name', 100)->nullable()->comment('Denormalized category name');
            $table->integer('quantity')->default(1);
            $table->integer('qty_given')->default(0);
            $table->decimal('unit_charge', 12, 2)->default(0);
            $table->decimal('total_charge', 12, 2)->default(0);
            $table->string('status', 30)->default('Pending')->comment('Pending, Approved, Partial, Complete, Cancelled');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->foreign('procedure_request_id')->references('id')->on('procedure_requests')->onDelete('cascade');
            $table->foreign('procedure_id')->references('id')->on('procedures')->onDelete('set null');
            $table->index('procedure_request_id');
            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('procedure_request_items'); }
};
