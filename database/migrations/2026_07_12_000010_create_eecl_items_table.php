<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eecl_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 30)->unique();
            $table->string('category', 100);
            $table->string('subcategory', 100)->nullable();
            $table->string('item_name', 300);
            $table->text('description')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('fixed_fee', 12, 2)->default(0);
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eecl_items');
    }
};
