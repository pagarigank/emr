<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('philhealth_claims', 'er_admission_id')) {
            Schema::table('philhealth_claims', function (Blueprint $table) {
                $table->unsignedBigInteger('er_admission_id')->nullable()->after('claim_batch_id');
            });
        }
        try {
            Schema::table('philhealth_claims', function (Blueprint $table) {
                $table->foreign('er_admission_id', 'fk_philhealth_claims_er_admission')
                      ->references('id')->on('er_admissions')->onDelete('no action');
            });
        } catch (\Exception $e) {
            Log::warning('FK fk_philhealth_claims_er_admission may already exist: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::table('philhealth_claims', function (Blueprint $table) {
            try {
                $table->dropForeign('fk_philhealth_claims_er_admission');
            } catch (\Exception $e) {
                Log::warning('FK fk_philhealth_claims_er_admission not found for drop: ' . $e->getMessage());
            }
            if (Schema::hasColumn('philhealth_claims', 'er_admission_id')) {
                $table->dropColumn('er_admission_id');
            }
        });
    }
};
