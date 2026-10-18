<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_status_polls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('philhealth_claim_id')->index();
            $table->unsignedBigInteger('claim_batch_id')->nullable()->index();
            $table->timestamp('polled_at');
            $table->string('status_before', 30)->nullable();
            $table->string('status_after', 30)->nullable();
            $table->text('response_data')->nullable();
            $table->string('poll_source', 30)->default('manual');
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('philhealth_claim_id', 'fk_poll_claim')
                  ->references('id')->on('philhealth_claims')->onDelete('no action');
            $table->foreign('claim_batch_id', 'fk_poll_batch')
                  ->references('id')->on('claim_batches')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_status_polls');
    }
};
