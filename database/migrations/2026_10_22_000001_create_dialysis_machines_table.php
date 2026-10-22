<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dialysis_machines')) {
            Schema::create('dialysis_machines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('station_code', 30);
                $table->string('station_name', 100);
                $table->string('serial_number', 100)->nullable();
                $table->string('brand', 100)->nullable();
                $table->string('model_name', 100)->nullable();
                $table->string('station_category', 30)->default('Regular')->comment('Regular, HepB_Isolation, HepC_Isolation, ICU_Mobile');
                $table->string('status', 30)->default('Available')->comment('Available, Occupied, Disinfecting, Maintenance, Offline');
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->tinyInteger('history')->default(1);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('created_date')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamp('updated_date')->nullable();

                $table->index(['tenant_id', 'history']);
                $table->index('station_code');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dialysis_machines');
    }
};
