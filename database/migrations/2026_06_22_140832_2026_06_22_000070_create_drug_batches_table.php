<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drug_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('drug_id');
            $table->string('batch_no', 50);
            $table->date('expiry_date');
            $table->decimal('quantity_on_hand', 12, 2)->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->string('location', 100)->nullable()->comment('Shelf/bin location');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['drug_id', 'batch_no', 'tenant_id', 'history']);
            $table->foreign('drug_id')->references('id')->on('drugs')->onDelete('cascade');
            $table->index('expiry_date');
        });
    }

    public function down(): void { Schema::dropIfExists('drug_batches'); }
};
