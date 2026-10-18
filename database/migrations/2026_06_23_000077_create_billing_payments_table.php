<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('billing_invoice_id');
            $table->string('payment_code', 30);
            $table->date('payment_date');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_method', 50)->default('Cash')->comment('Cash, Credit Card, Check, Bank Transfer, HMO, PhilHealth, Others');
            $table->string('reference_no', 100)->nullable()->comment('Check no, transaction ID, OR no');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->unique(['payment_code', 'tenant_id', 'history'], 'uq_billing_payments');
            $table->foreign('billing_invoice_id')->references('id')->on('billing_invoices')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('billing_payments'); }
};
