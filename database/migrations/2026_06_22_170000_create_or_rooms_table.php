<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('or_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->string('location', 100)->nullable();
            $table->string('room_type', 50)->nullable();
            $table->integer('capacity')->default(1);
            $table->text('equipment')->nullable();
            $table->text('special_requirements')->nullable();
            $table->string('status', 30)->default('Available');
            $table->boolean('is_active')->default(1);
            $table->boolean('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['code', 'tenant_id', 'history'], 'or_rooms_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('or_rooms');
    }
};
