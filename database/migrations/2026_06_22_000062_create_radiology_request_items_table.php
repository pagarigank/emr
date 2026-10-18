<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('radiology_request_id');
            $table->unsignedBigInteger('exam_type_id')->nullable();
            $table->string('exam_name', 200);
            $table->string('modality_name', 100)->nullable()->comment('Denormalized modality name');
            $table->decimal('charge_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('Pending')->comment('Pending, Approved, Complete, Cancelled');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->foreign('radiology_request_id')->references('id')->on('radiology_requests')->onDelete('cascade');
            $table->foreign('exam_type_id')->references('id')->on('radiology_exam_types')->onDelete('set null');
            $table->index('radiology_request_id');
            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('radiology_request_items'); }
};
