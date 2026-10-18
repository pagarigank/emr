<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->index();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);

            $table->tinyInteger('history')->default(1)->comment('1=current, 0=archived');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['country_id', 'code']);
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
