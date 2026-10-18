<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cf5_shadow_billings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('philhealth_claim_id')->index();
            $table->string('drg_code', 20)->nullable();
            $table->string('drg_description')->nullable();
            $table->decimal('drg_weight', 10, 4)->default(0);
            $table->decimal('base_rate', 12, 2)->default(0);
            $table->decimal('hf_amount', 12, 2)->default(0);
            $table->decimal('pf_amount', 12, 2)->default(0);
            $table->decimal('total_drg_amount', 12, 2)->default(0);
            $table->decimal('actual_case_rate_amount', 12, 2)->default(0);
            $table->decimal('difference_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('Draft');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('philhealth_claim_id', 'fk_cf5_claim')
                  ->references('id')->on('philhealth_claims')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cf5_shadow_billings');
    }
};
