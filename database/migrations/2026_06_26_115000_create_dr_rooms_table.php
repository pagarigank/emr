<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dr_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->string('location', 100)->nullable();
            $table->string('room_type', 50)->nullable()->comment('Labor Room, Delivery Room, LDR, LDRP, C-Section Suite');
            $table->integer('capacity')->default(1);
            $table->text('equipment')->nullable();
            $table->text('special_requirements')->nullable();
            $table->string('status', 30)->default('Available')->comment('Available, Occupied, Maintenance, Reserved');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_dr_rooms');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dr_rooms');
    }
};
