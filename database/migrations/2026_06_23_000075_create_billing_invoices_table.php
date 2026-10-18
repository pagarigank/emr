<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('invoice_code', 30);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('er_admission_id')->nullable()->index();
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->date('invoice_date');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('Sum of all line items');
            $table->decimal('paid_amount', 12, 2)->default(0)->comment('Sum of payments applied');
            $table->decimal('balance', 12, 2)->default(0)->comment('total_amount - paid_amount');
            $table->string('status', 30)->default('Unpaid')->comment('Unpaid, Partial, Paid, Cancelled, Credited');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->unique(['invoice_code', 'tenant_id', 'history'], 'uq_billing_invoices');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('er_admission_id')->references('id')->on('er_admissions')->onDelete('set null');
            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('billing_invoices'); }
};
