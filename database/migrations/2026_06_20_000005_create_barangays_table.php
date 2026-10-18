<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('province_city_id')->index();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);

            $table->tinyInteger('history')->default(1)->comment('1=current, 0=archived');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['province_city_id', 'code']);
            $table->foreign('province_city_id')->references('id')->on('provinces_cities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangays');
    }
};
