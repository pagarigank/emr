<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_rejections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('philhealth_claim_id')->index();
            $table->string('rejection_code', 50)->nullable();
            $table->text('rejection_reason');
            $table->date('rejection_date')->nullable();
            $table->string('resubmission_status', 30)->default('Pending');
            $table->timestamp('resubmitted_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('philhealth_claim_id', 'fk_rejection_claim')
                  ->references('id')->on('philhealth_claims')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_rejections');
    }
};
