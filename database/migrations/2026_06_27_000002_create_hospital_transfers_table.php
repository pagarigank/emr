<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('transfer_code', 20);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('destination_hospital_id');
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('transfer_reason', 500)->nullable();
            $table->string('transfer_diagnosis', 500)->nullable();
            $table->unsignedBigInteger('transferred_by')->nullable();
            $table->string('accompanying_personnel', 200)->nullable();
            $table->string('transport_mode', 50)->nullable();
            $table->text('condition_on_transfer')->nullable();
            $table->string('status', 30)->default('Pending');
            $table->timestamp('transfer_datetime')->nullable();
            $table->timestamp('completed_datetime')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->unique(['transfer_code', 'tenant_id', 'history'], 'uq_transfers_code');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('NO ACTION');
            $table->foreign('destination_hospital_id')->references('id')->on('hospitals')->onDelete('NO ACTION');
            $table->foreign('transferred_by')->references('id')->on('health_care_professionals')->onDelete('SET NULL');
            $table->index('source_type');
            $table->index('source_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_transfers');
    }
};
