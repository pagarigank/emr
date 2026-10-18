<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('batch_code', 20);
            $table->string('status', 30)->default('Draft');
            $table->integer('total_claims')->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->timestamp('submission_date')->nullable();
            $table->timestamp('acknowledgement_date')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['batch_code', 'tenant_id', 'history']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_batches');
    }
};
