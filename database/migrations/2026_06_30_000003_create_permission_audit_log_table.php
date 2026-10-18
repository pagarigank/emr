<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_audit_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('action', 40);
            $table->string('entity_type', 60);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('role', 40)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('permission_id')->nullable();
            $table->string('permission_name', 100)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->unsignedBigInteger('changed_by');
            $table->datetime('changed_date');
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->tinyInteger('history')->default(1);
            $table->index('action');
            $table->index('entity_type');
            $table->index('changed_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_audit_log');
    }
};
