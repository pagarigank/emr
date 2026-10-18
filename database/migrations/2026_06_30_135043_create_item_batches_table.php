<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('item_id')->index();
            $table->string('batch_no', 100);
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity_on_hand', 10, 2)->default(0);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('location', 100)->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['tenant_id', 'item_id', 'batch_no'], 'item_batches_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_batches');
    }
};
