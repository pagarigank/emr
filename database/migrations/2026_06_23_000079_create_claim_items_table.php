<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('philhealth_claim_id')->index();
            $table->string('item_type', 30);
            $table->string('item_code', 30)->nullable();
            $table->string('item_name', 255);
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('actual_amount', 12, 2)->default(0);
            $table->decimal('claimed_amount', 12, 2)->default(0);
            $table->string('remarks', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_items');
    }
};
