<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('drug_id');
            $table->decimal('quantity_ordered', 12, 2)->default(0);
            $table->decimal('quantity_received', 12, 2)->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->string('batch_no', 50)->nullable()->comment('Filled on receipt');
            $table->date('expiry_date')->nullable()->comment('Filled on receipt');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('drug_id')->references('id')->on('drugs')->onDelete('no action');
            $table->index('purchase_order_id');
        });
    }

    public function down(): void { Schema::dropIfExists('purchase_order_items'); }
};
