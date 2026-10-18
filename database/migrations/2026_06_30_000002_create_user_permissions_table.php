<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('permission_id');
            $table->string('type', 10); // 'allow' or 'deny'
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->datetime('created_date')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->unique(['user_id', 'permission_id', 'tenant_id'], 'user_perm_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
