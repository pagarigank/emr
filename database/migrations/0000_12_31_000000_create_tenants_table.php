<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * NOTE: tenants is the root entity — it has NO tenant_id column itself.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_code', 30)->unique()->comment('Unique short code, e.g. HOSP01');
            $table->string('tenant_name', 150)->comment('Full company/hospital name');
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('logo_path', 255)->nullable()->comment('Path to tenant logo');
            $table->boolean('is_active')->default(true);

            // ── Audit & History (project standard) ──────────────────────────
            // No tenant_id — this table IS the tenant.
            $table->tinyInteger('history')->default(1)->comment('1=current, 0=archived');
            $table->unsignedBigInteger('created_by')->nullable()->comment('FK → users.id');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable()->comment('FK → users.id');
            $table->timestamp('updated_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
