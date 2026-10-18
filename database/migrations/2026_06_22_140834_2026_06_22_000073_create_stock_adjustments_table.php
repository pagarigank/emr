<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('adjustment_code', 30);
            $table->unsignedBigInteger('drug_id');
            $table->unsignedBigInteger('drug_batch_id')->nullable();
            $table->string('adjustment_type', 30)->comment('Write-off, Damage, Theft, Return, Transfer Out, Transfer In, Correction');
            $table->decimal('quantity', 12, 2)->comment('Positive=in, Negative=out');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->text('reason');
            $table->string('reference_type', 50)->nullable()->comment('PO, Dispensing, Transfer');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['adjustment_code', 'tenant_id', 'history']);
            $table->foreign('drug_id')->references('id')->on('drugs')->onDelete('no action');
            $table->foreign('drug_batch_id')->references('id')->on('drug_batches')->onDelete('set null');
            $table->index('adjustment_type');
        });
    }

    public function down(): void { Schema::dropIfExists('stock_adjustments'); }
};
