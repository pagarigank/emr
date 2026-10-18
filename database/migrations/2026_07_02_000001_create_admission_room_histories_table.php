<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_room_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('admission_id');
            $table->string('room_type', 20)->comment('ward_room or icu_bed');
            $table->unsignedBigInteger('room_id');
            $table->string('room_name', 100)->nullable();
            $table->string('ward_name', 100)->nullable();
            $table->decimal('room_rate', 12, 2)->default(0);
            $table->dateTime('assigned_at');
            $table->dateTime('unassigned_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->dateTime('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->dateTime('updated_date')->nullable();

            $table->foreign('admission_id')->references('id')->on('admissions')->onDelete('cascade');
            $table->index(['admission_id', 'is_active']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_room_histories');
    }
};
