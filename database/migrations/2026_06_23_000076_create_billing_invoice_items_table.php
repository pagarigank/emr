<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_invoice_id');
            $table->string('source_type', 30)->comment('Lab, Pharmacy, CSR, Radiology, Procedure, Manual');
            $table->unsignedBigInteger('source_id')->nullable()->comment('FK to the originating request table');
            $table->string('source_request_code', 30)->nullable()->comment('Denormalized request code for cross-reference');
            $table->string('description', 255);
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0)->comment('quantity x unit_price');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->foreign('billing_invoice_id')->references('id')->on('billing_invoices')->onDelete('cascade');
            $table->index('source_type');
            $table->index('source_id');
        });
    }

    public function down(): void { Schema::dropIfExists('billing_invoice_items'); }
};
