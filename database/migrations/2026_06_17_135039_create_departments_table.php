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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();          // Multi-tenant FK
            $table->string('dept_code', 20);
            $table->string('dept_name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);

            // ── Audit & History columns (project standard) ──────────────────
            $table->tinyInteger('history')->default(1)->comment('1=current, 0=historical/deleted');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            // ── Composite unique: code must be unique per tenant ────────────
            $table->unique(['tenant_id', 'dept_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
