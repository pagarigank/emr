<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 30);
            $table->string('name', 200);
            $table->string('description', 500)->nullable();
            $table->unsignedBigInteger('item_category_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('supply_unit_id')->nullable();
            $table->string('sku', 50)->nullable()->comment('Stock Keeping Unit / manufacturer code');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->integer('reorder_level')->default(0)->comment('Minimum stock before reorder');
            $table->integer('reorder_qty')->default(0)->comment('Default reorder quantity');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_items');
            $table->foreign('item_category_id')->references('id')->on('item_categories')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            $table->foreign('supply_unit_id')->references('id')->on('supply_units')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('items'); }
};
