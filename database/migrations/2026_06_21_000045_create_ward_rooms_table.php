<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ward_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 20);
            $table->string('name', 100);
            $table->unsignedBigInteger('room_type_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->integer('capacity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_ward_rooms');
            $table->foreign('room_type_id')->references('id')->on('ward_room_types')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('ward_rooms'); }
};
