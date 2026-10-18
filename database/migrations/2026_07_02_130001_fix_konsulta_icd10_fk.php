<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The konsulta_consultations table was created with icd10_code_id FK
        // pointing to the old `icd10_codes` table (no underscore). The app uses
        // the newer `icd_10_codes` table (with underscore) via the Icd10Code model.
        // Drop the wrong FK and re-add it pointing to the correct table.
        Schema::table('konsulta_consultations', function (Blueprint $table) {
            // Drop old FK (Laravel convention: tablename_column_foreign)
            $table->dropForeign('konsulta_consultations_icd10_code_id_foreign');
            // Re-add pointing to correct icd_10_codes table
            $table->foreign('icd10_code_id')
                  ->references('id')
                  ->on('icd_10_codes')
                  ->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('konsulta_consultations', function (Blueprint $table) {
            $table->dropForeign('konsulta_consultations_icd10_code_id_foreign');
            $table->foreign('icd10_code_id')
                  ->references('id')
                  ->on('icd10_codes')
                  ->onDelete('no action');
        });
    }
};
