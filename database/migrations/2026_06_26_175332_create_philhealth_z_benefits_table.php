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
        Schema::create('philhealth_z_benefits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('philhealth_claim_id')->index();
            $table->string('z_benefit_code', 20);
            $table->string('z_benefit_name', 255)->nullable();
            $table->string('pre_authorization_code', 50)->nullable();
            $table->tinyInteger('tranche_number')->unsigned()->default(1);
            $table->decimal('tranche_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('Pending'); // Pending, Approved, Denied
            
            // Standard tracking fields
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('philhealth_claim_id')
                  ->references('id')
                  ->on('philhealth_claims')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('philhealth_z_benefits');
    }
};
