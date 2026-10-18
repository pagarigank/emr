<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 20);
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->string('type', 30)->default('Room')->comment('Warehouse, Room, Shelf, Bin, Cabinet');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Self-referencing FK for sub-locations');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_stock_locations');
            $table->foreign('parent_id')->references('id')->on('stock_locations')->onDelete('no action');
        });
    }

    public function down(): void { Schema::dropIfExists('stock_locations'); }
};
