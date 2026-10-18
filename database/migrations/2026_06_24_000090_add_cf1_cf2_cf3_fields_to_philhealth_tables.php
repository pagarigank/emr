<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── philhealth_claims — CF1/CF2/CF3 supplementary fields ──
        Schema::table('philhealth_claims', function (Blueprint $table) {
            // CF2 — secondary/tertiary ICD-10 codes (multiple diagnoses)
            $table->unsignedBigInteger('secondary_icd10_code_id')->nullable()->index()
                ->after('icd10_code_id');
            $table->unsignedBigInteger('tertiary_icd10_code_id')->nullable()->index()
                ->after('secondary_icd10_code_id');

            $table->foreign('secondary_icd10_code_id', 'fk_claims_secondary_icd10')
                ->references('id')->on('icd10_codes')->onDelete('NO ACTION');
            $table->foreign('tertiary_icd10_code_id', 'fk_claims_tertiary_icd10')
                ->references('id')->on('icd10_codes')->onDelete('NO ACTION');

            // CF2 — HCI authorized signatory
            $table->string('hci_signatory_name', 150)->nullable()
                ->after('physician_signature_date')
                ->comment('HCI authorized representative name');
            $table->timestamp('hci_signature_date')->nullable()
                ->after('hci_signatory_name');

            // CF3 — significant lab/imaging results summary
            $table->text('lab_imaging_summary')->nullable()
                ->after('course_in_ward')
                ->comment('CF3 — Summary of significant laboratory/imaging results');

            // CF3 OB — delivery tracking
            $table->date('delivery_date')->nullable()
                ->after('para')
                ->comment('CF3 OB — Date of delivery');
            $table->unsignedBigInteger('delivery_type_id')->nullable()->index()
                ->after('delivery_date');
            $table->string('newborn_outcome', 50)->nullable()
                ->after('delivery_type_id')
                ->comment('Live Birth, Stillbirth, Early Neonatal Death, etc.');

            $table->foreign('delivery_type_id', 'fk_claims_delivery_type')
                ->references('id')->on('delivery_types')->onDelete('NO ACTION');
        });

        // ── philhealth_members — CF1 employer contact details ──
        Schema::table('philhealth_members', function (Blueprint $table) {
            $table->string('employer_contact', 30)->nullable()
                ->after('employer_address')
                ->comment('CF1 — Employer contact number');
            $table->timestamp('employer_signature_date')->nullable()
                ->after('zip_code')
                ->comment('CF1 — Employer certification signature date');
        });
    }

    public function down(): void
    {
        Schema::table('philhealth_claims', function (Blueprint $table) {
            $table->dropForeign('fk_claims_secondary_icd10');
            $table->dropForeign('fk_claims_tertiary_icd10');
            $table->dropForeign('fk_claims_delivery_type');
            $table->dropColumn([
                'secondary_icd10_code_id',
                'tertiary_icd10_code_id',
                'hci_signatory_name',
                'hci_signature_date',
                'lab_imaging_summary',
                'delivery_date',
                'delivery_type_id',
                'newborn_outcome',
            ]);
        });

        Schema::table('philhealth_members', function (Blueprint $table) {
            $table->dropColumn(['employer_contact', 'employer_signature_date']);
        });
    }
};
