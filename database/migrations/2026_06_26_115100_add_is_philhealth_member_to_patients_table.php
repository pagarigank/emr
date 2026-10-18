<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Add is_philhealth_member flag after philhealth_member_id
            // Membership is now declared at registration — PIN filled later in eClaims
            $table->boolean('is_philhealth_member')->default(false)->after('philhealth_member_id');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('is_philhealth_member');
        });
    }
};
