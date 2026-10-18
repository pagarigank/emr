<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pharmacy_request_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('item_name', 200);
            $table->string('item_code', 30)->nullable()->comment('Denormalized item code');
            $table->string('supply_unit_name', 100)->nullable()->comment('Denormalized unit name');
            $table->string('dosage', 100)->nullable()->comment('e.g. 500mg, 1g');
            $table->string('frequency', 100)->nullable()->comment('e.g. BID, TID, QID, QHS, PRN');
            $table->string('route', 100)->nullable()->comment('e.g. PO, IV, IM, SC, SL, TOP');
            $table->string('duration', 100)->nullable()->comment('e.g. 7 days, 2 weeks');
            $table->decimal('quantity', 12, 2)->default(1)->comment('Requested quantity');
            $table->decimal('given_quantity', 12, 2)->default(0)->comment('Cumulative quantity dispensed');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0)->comment('given_quantity x unit_cost');
            $table->string('status', 30)->default('Pending')->comment('Pending, Approved, Partial, Complete, Cancelled');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->foreign('pharmacy_request_id')->references('id')->on('pharmacy_requests')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('set null');
            $table->index('pharmacy_request_id');
            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('pharmacy_request_items'); }
};
