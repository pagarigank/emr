<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('er_admission_id')->nullable()->index();
            $table->string('request_code', 30);
            $table->unsignedBigInteger('requesting_department_id')->nullable();
            $table->string('requesting_person', 150)->nullable();
            $table->unsignedBigInteger('requesting_doctor_id')->nullable();
            $table->string('priority', 20)->default('Routine')->comment('Routine, Urgent, STAT');
            $table->string('status', 30)->default('Pending')->comment('Pending, Approved, Partial, Complete, Cancelled');
            $table->string('billing_status', 30)->default('Pending')->comment('Pending, Billed, Exempt, Credited');
            $table->unsignedBigInteger('billing_id')->nullable()->index()->comment('Future FK to billing module');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->unique(['request_code', 'tenant_id', 'history']);
            $table->foreign('er_admission_id')->references('id')->on('er_admissions')->onDelete('set null');
            $table->foreign('requesting_department_id')->references('id')->on('departments')->onDelete('no action');
            $table->foreign('requesting_doctor_id')->references('id')->on('health_care_professionals')->onDelete('no action');
            $table->index('billing_status');
        });
    }

    public function down(): void { Schema::dropIfExists('pharmacy_requests'); }
};
