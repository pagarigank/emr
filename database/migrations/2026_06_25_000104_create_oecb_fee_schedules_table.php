<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oecb_fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('package_code', 30);
            $table->unsignedBigInteger('core_presentation_id');
            $table->text('description')->nullable();
            $table->decimal('fixed_fee', 12, 2)->default(0);
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('core_presentation_id', 'fk_oecb_fee_core_presentation')
                  ->references('id')->on('oecb_core_presentations')->onDelete('no action');
            $table->unique(['package_code', 'core_presentation_id', 'history'], 'uq_oecb_fee_schedule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oecb_fee_schedules');
    }
};
