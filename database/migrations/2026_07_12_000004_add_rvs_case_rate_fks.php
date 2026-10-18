<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('philhealth_case_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('rvs_code_id')->nullable()->index()
                ->after('id');
            $table->unsignedBigInteger('icd10_code_id')->nullable()->index()
                ->after('rvs_code_id');
        });

        // Backfill rvs_code_id from rvs_codes
        if (Schema::hasTable('rvs_codes')) {
            $caseRates = DB::table('philhealth_case_rates')->where('history', 1)->get();
            foreach ($caseRates as $cr) {
                $rvsId = DB::table('rvs_codes')
                    ->where('code', $cr->rvs_code)
                    ->where('history', 1)
                    ->value('id');
                if ($rvsId) {
                    DB::table('philhealth_case_rates')
                        ->where('id', $cr->id)
                        ->update(['rvs_code_id' => $rvsId]);
                }
            }
        }

        // Backfill icd10_code_id from icd_10_codes
        if (Schema::hasTable('icd_10_codes')) {
            $caseRates = DB::table('philhealth_case_rates')
                ->where('history', 1)
                ->whereNull('icd10_code_id')
                ->get();
            foreach ($caseRates as $cr) {
                if (!$cr->icd10_code) continue;
                $icdId = DB::table('icd_10_codes')
                    ->where('code', $cr->icd10_code)
                    ->where('history', 1)
                    ->value('id');
                if ($icdId) {
                    DB::table('philhealth_case_rates')
                        ->where('id', $cr->id)
                        ->update(['icd10_code_id' => $icdId]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('philhealth_case_rates', function (Blueprint $table) {
            $table->dropColumn(['rvs_code_id', 'icd10_code_id']);
        });
    }
};
