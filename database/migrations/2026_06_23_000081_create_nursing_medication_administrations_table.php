<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursing_medication_administrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('admission_id');
            $table->unsignedBigInteger('pharmacy_request_item_id')->nullable()->comment('Optional link to pharmacy request item');
            $table->string('drug_name', 200);
            $table->string('dosage', 100)->nullable()->comment('Prescribed dosage (e.g. 500mg)');
            $table->string('dose_given', 100)->nullable()->comment('Actual dose administered');
            $table->string('route', 50)->nullable()->comment('PO, IV, IM, SC, etc.');
            $table->dateTime('administered_at');
            $table->unsignedBigInteger('administered_by')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();

            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');
            $table->foreign('pharmacy_request_item_id')->references('id')->on('pharmacy_request_items')->onDelete('set null');
            $table->foreign('administered_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['admission_id', 'administered_at'], 'idx_mar_admission_date');
        });
    }

    public function down(): void { Schema::dropIfExists('nursing_medication_administrations'); }
};
