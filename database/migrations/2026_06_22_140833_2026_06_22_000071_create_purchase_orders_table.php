<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('po_code', 30);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status', 30)->default('Draft')->comment('Draft, Submitted, Approved, Partial, Received, Cancelled');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['po_code', 'tenant_id', 'history']);
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('purchase_orders'); }
};
