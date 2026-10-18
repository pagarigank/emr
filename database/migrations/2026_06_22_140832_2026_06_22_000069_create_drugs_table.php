<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('drug_code', 30);
            $table->string('generic_name', 200);
            $table->string('brand_name', 200)->nullable();
            $table->string('drug_strength', 50)->nullable()->comment('e.g. 500mg, 1g, 250mg/5mL');
            $table->string('dosage_form', 50)->nullable()->comment('Tablet, Capsule, Syrup, Injection, Cream, Ointment');
            $table->string('therapeutic_class', 100)->nullable()->comment('Antibiotic, Antihypertensive, NSAID, etc.');
            $table->string('drug_schedule', 30)->nullable()->comment('OTC, Prescription, Controlled, Narcotic');
            $table->string('route', 100)->nullable()->comment('PO, IV, IM, SC, SL, TOP, PR, INH');
            $table->string('active_ingredient', 200)->nullable();
            $table->string('manufacturer', 200)->nullable();
            $table->text('side_effects')->nullable();
            $table->text('contraindications')->nullable();
            $table->integer('reorder_level')->default(0);
            $table->integer('reorder_qty')->default(0);
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->unique(['drug_code', 'tenant_id', 'history']);
        });
    }

    public function down(): void { Schema::dropIfExists('drugs'); }
};
