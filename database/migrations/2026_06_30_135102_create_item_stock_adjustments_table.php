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
        Schema::create('item_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('adjustment_code', 100);
            $table->unsignedBigInteger('item_id')->index();
            $table->unsignedBigInteger('item_batch_id')->nullable()->index();
            $table->string('adjustment_type', 100);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->text('reason')->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->string('reference_id', 100)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_stock_adjustments');
    }
};
