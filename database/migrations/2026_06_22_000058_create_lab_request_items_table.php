<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_request_id');
            $table->string('item_type', 20)->comment('Test, Panel');
            $table->unsignedBigInteger('lab_test_id')->nullable();
            $table->unsignedBigInteger('lab_panel_id')->nullable();
            $table->string('test_name', 200);
            $table->decimal('charge_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('Pending')->comment('Pending, Collected, Processing, Completed, Cancelled');
            $table->text('result_value')->nullable()->comment('Numeric value stored as string for flexibility');
            $table->text('result_text')->nullable();
            $table->string('result_categorical', 100)->nullable();
            $table->text('result_notes')->nullable();
            $table->timestamp('result_date')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->foreign('lab_request_id')->references('id')->on('lab_requests')->onDelete('cascade');
            $table->foreign('lab_test_id')->references('id')->on('lab_tests')->onDelete('set null');
            $table->foreign('lab_panel_id')->references('id')->on('lab_panels')->onDelete('set null');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('no action');
            $table->index('lab_request_id');
            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('lab_request_items'); }
};
