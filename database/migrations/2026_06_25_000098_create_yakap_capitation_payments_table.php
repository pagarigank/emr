<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yakap_capitation_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('empanelment_id');
            $table->unsignedBigInteger('patient_id');

            $table->integer('tranche')->default(1); // 1 or 2
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_code', 50)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('status', 30)->default('Pending'); // Pending, Paid, Reversed

            $table->string('philhealth_reference', 100)->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('empanelment_id')->references('id')->on('yakap_empanelments')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yakap_capitation_payments');
    }
};
