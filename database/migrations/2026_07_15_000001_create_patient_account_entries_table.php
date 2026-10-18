<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_account_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('patient_id')->index();

            // Encounter scope — which visit this entry belongs to
            $table->unsignedBigInteger('admission_id')->nullable()->index();
            $table->unsignedBigInteger('er_admission_id')->nullable()->index();
            $table->unsignedBigInteger('opd_consultation_id')->nullable()->index();
            $table->unsignedBigInteger('or_schedule_id')->nullable()->index();
            $table->unsignedBigInteger('dr_schedule_id')->nullable()->index();
            $table->unsignedBigInteger('ob_consultation_id')->nullable()->index();
            $table->unsignedBigInteger('dialysis_session_id')->nullable()->index();

            // Entry classification
            $table->string('entry_type', 20);        // charge, payment, adjustment, discount, philhealth_credit
            $table->string('source_type', 30);        // Lab, Pharmacy, CSR, Radiology, Procedure, OECB, Admission, Manual, RoomBoard
            $table->unsignedBigInteger('source_id')->nullable();  // FK to originating request
            $table->string('source_code', 30)->nullable();       // Denormalized request code for quick display

            // Financials
            $table->decimal('debit', 12, 2)->default(0);   // charge amount (patient owes)
            $table->decimal('credit', 12, 2)->default(0);  // payment/discount (reduces balance)
            $table->decimal('balance', 12, 2)->default(0); // running balance after this entry

            // Description & metadata
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            // Optional link to invoice/statement when generated
            $table->unsignedBigInteger('billing_invoice_id')->nullable()->index();

            // Fund source (for payments)
            $table->unsignedBigInteger('fund_source_id')->nullable()->index();

            // Audit
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['tenant_id', 'patient_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_account_entries');
    }
};
