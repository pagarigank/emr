<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add `category` column to icd_10_codes (exists in icd10_codes but missing here)
        Schema::table('icd_10_codes', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('description');
        });

        // Backfill category from icd10_codes if it exists
        if (Schema::hasTable('icd10_codes')) {
            $oldData = DB::table('icd10_codes')->where('history', 1)->get();
            foreach ($oldData as $row) {
                DB::table('icd_10_codes')
                    ->where('code', $row->code)
                    ->update(['category' => $row->category ?? null]);
            }

            // Backfill any missing codes from old table into new table
            $existingCodes = DB::table('icd_10_codes')->pluck('code')->toArray();
            $newInserts = $oldData->filter(fn ($r) => !in_array($r->code, $existingCodes));
            foreach ($newInserts as $row) {
                DB::table('icd_10_codes')->insert([
                    'code' => $row->code,
                    'description' => $row->name ?? $row->description ?? '',
                    'category' => $row->category ?? null,
                    'is_active' => $row->is_active ?? 1,
                    'history' => $row->history ?? 1,
                    'created_by' => $row->created_by ?? null,
                    'created_date' => $row->created_date ?? now(),
                    'updated_by' => $row->updated_by ?? null,
                    'updated_date' => $row->updated_date ?? null,
                ]);
            }
        }

        // Point FKs on philhealth_claims to icd_10_codes (drop old FKs first if they exist)
        $fkNamePattern = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'philhealth_claims' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
        foreach ($fkNamePattern as $fk) {
            if (str_contains($fk->CONSTRAINT_NAME, 'icd10')) {
                Schema::table('philhealth_claims', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                });
            }
        }

        // Drop old icd10_codes column data references (keep columns, they now point to icd_10_codes via convention)
        // Note: philhealth_claims columns icd10_code_id, secondary_icd10_code_id, tertiary_icd10_code_id
        // already point to icd_10_codes.id via the Icd10Code model which uses $table = 'icd_10_codes'
        // The old icd10_codes table can be dropped once confirmed no other code references it.

        // Drop old table if it exists
        if (Schema::hasTable('icd10_codes')) {
            Schema::dropIfExists('icd10_codes');
        }
    }

    public function down(): void
    {
        Schema::table('icd_10_codes', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
