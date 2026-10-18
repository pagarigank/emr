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
        Schema::create('er_beds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->unsignedBigInteger('er_zone_id')->nullable();
            $table->string('status', 30)->default('Available'); // Available, Occupied, Maintenance
            
            // Standard audit columns
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            // Constraints
            $table->unique(['code', 'tenant_id', 'history'], 'er_beds_unique');
            $table->foreign('er_zone_id')->references('id')->on('er_zones')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('er_beds');
    }
};
