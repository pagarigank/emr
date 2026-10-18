<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 40)->unique();
            $table->string('display_name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->datetime('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->datetime('updated_date')->nullable();
        });

        DB::table('roles')->insert([
            ['name' => 'superadmin',   'display_name' => 'Super Admin',   'description' => 'Unrestricted access across all tenants', 'is_system' => 1, 'is_active' => 1, 'history' => 1, 'created_date' => now()],
            ['name' => 'tenant_admin', 'display_name' => 'Tenant Admin',  'description' => 'Full access within their own tenant',   'is_system' => 1, 'is_active' => 1, 'history' => 1, 'created_date' => now()],
            ['name' => 'staff',        'display_name' => 'Staff',         'description' => 'Limited feature access within tenant',  'is_system' => 1, 'is_active' => 1, 'history' => 1, 'created_date' => now()],
            ['name' => 'doctor',       'display_name' => 'Doctor',        'description' => 'Linked to a health care professional profile', 'is_system' => 1, 'is_active' => 1, 'history' => 1, 'created_date' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
