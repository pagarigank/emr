<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('role');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('no action');
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('role');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('no action');
        });

        $roleIds = DB::table('roles')->pluck('id', 'name');

        foreach ($roleIds as $roleName => $roleId) {
            DB::table('users')
                ->where('role', $roleName)
                ->update(['role_id' => $roleId]);

            DB::table('role_permissions')
                ->where('role', $roleName)
                ->update(['role_id' => $roleId]);
        }

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropUnique('role_perm_unique');
            $table->unique(['role_id', 'permission_id', 'tenant_id'], 'role_perm_unique');
        });
    }

    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropUnique('role_perm_unique');
            $table->unique(['role', 'permission_id', 'tenant_id'], 'role_perm_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
