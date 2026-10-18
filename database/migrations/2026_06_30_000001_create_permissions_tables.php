<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->unique();
            $table->string('display_name', 200);
            $table->string('module', 100)->nullable();
            $table->string('guard_name', 40)->default('web');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->datetime('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->datetime('updated_date')->nullable();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('role', 40);
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->datetime('created_date')->nullable();

            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->unique(['role', 'permission_id', 'tenant_id'], 'role_perm_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
