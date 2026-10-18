<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('request_code', 30);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('requesting_department_id')->nullable();
            $table->unsignedBigInteger('requesting_doctor_id')->nullable();
            $table->string('priority', 20)->default('Routine')->comment('Routine, Urgent, STAT');
            $table->text('request_notes')->nullable();
            $table->string('status', 30)->default('Pending')->comment('Pending, Collected, Processing, Completed, Cancelled');
            $table->string('billing_status', 20)->default('Pending')->comment('Pending, Billed, Exempt, Credited');
            $table->unsignedBigInteger('billing_id')->nullable()->comment('FK to future billing_invoices table');
            $table->decimal('total_charge', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['request_code', 'tenant_id', 'history'], 'uq_lab_requests');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('no action');
            $table->foreign('requesting_department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('requesting_doctor_id')->references('id')->on('health_care_professionals')->onDelete('set null');
            $table->index('tenant_id');
            $table->index('billing_status');
            $table->index('billing_id');
        });
    }

    public function down(): void { Schema::dropIfExists('lab_requests'); }
};
