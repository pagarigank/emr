<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dialysis_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 30);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('dialysis_type_id')->nullable();
            $table->string('access_type', 50)->nullable()->comment('AV Fistula, AV Graft, Catheter, etc.');
            $table->date('session_date');
            $table->decimal('pre_weight', 6, 2)->nullable()->comment('Weight before dialysis in kg');
            $table->decimal('post_weight', 6, 2)->nullable()->comment('Weight after dialysis in kg');
            $table->string('pre_bp', 20)->nullable()->comment('BP before dialysis (e.g. 140/90)');
            $table->string('post_bp', 20)->nullable()->comment('BP after dialysis');
            $table->integer('heart_rate')->nullable();
            $table->text('medications')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('Scheduled')->comment('Scheduled, In Progress, Completed, Cancelled');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_dialysis_sessions');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('dialysis_type_id')->references('id')->on('dialysis_types')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('dialysis_sessions'); }
};
