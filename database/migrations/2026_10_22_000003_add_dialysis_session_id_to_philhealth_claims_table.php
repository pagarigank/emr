<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('philhealth_claims') && !Schema::hasColumn('philhealth_claims', 'dialysis_session_id')) {
            Schema::table('philhealth_claims', function (Blueprint $table) {
                $table->unsignedBigInteger('dialysis_session_id')->nullable()->after('admission_id');
                $table->index('dialysis_session_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('philhealth_claims') && Schema::hasColumn('philhealth_claims', 'dialysis_session_id')) {
            Schema::table('philhealth_claims', function (Blueprint $table) {
                $table->dropColumn('dialysis_session_id');
            });
        }
    }
};
