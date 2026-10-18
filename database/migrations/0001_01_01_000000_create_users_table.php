<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Users table supports two roles:
     *   superadmin  — tenant_id IS NULL, can access all tenants
     *   tenant_admin — tenant_id set, restricted to their tenant
     *
     * NOTE: No timestamps() — we use explicit created_date / updated_date.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Multi-tenant: NULL = superadmin (global), set = tenant-scoped user
            $table->unsignedBigInteger('tenant_id')->nullable()->index()
                  ->comment('NULL for superadmin, FK → tenants.id for tenant users');

            $table->string('username', 60)->comment('Login username');
            $table->string('full_name', 150);
            $table->string('email', 150);
            $table->string('password');

            // Role: superadmin | tenant_admin | staff (extendable)
            $table->string('role', 30)->default('tenant_admin')
                  ->comment('superadmin | tenant_admin | staff');

            $table->boolean('is_active')->default(true);

            // ── Audit & History (project standard) ──────────────────────────
            $table->tinyInteger('history')->default(1)->comment('1=current, 0=archived');
            $table->unsignedBigInteger('created_by')->nullable()->comment('FK → users.id');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable()->comment('FK → users.id');
            $table->timestamp('updated_date')->nullable();

            // Username must be unique globally for superadmin, per tenant for others
            $table->unique(['tenant_id', 'username']);
            // Email unique globally
            $table->unique('email');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
