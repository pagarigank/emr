<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_beds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('code', 20);
            $table->string('name', 100);
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['code', 'tenant_id', 'history'], 'uq_icu_beds');
            $table->foreign('ward_id')->references('id')->on('icu_wards')->onDelete('set null');
        });
    }

    public function down(): void { Schema::dropIfExists('icu_beds'); }
};
