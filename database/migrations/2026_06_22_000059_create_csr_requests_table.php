<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('csr_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('request_code', 30);
            $table->unsignedBigInteger('requesting_department_id')->nullable();
            $table->string('requesting_person', 150)->nullable()->comment('Name of person requesting');
            $table->string('status', 30)->default('Pending')->comment('Pending, Approved, Partial, Complete, Cancelled');
            $table->string('billing_status', 30)->default('Pending')->comment('Pending, Billed, Exempt, Credited');
            $table->unsignedBigInteger('billing_id')->nullable()->index()->comment('FK to future billing module');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['request_code', 'tenant_id', 'history'], 'uq_csr_requests');
            $table->foreign('requesting_department_id')->references('id')->on('departments')->onDelete('no action');
            $table->index('billing_status');
        });
    }

    public function down(): void { Schema::dropIfExists('csr_requests'); }
};
