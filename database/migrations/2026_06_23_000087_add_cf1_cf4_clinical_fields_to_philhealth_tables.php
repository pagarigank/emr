<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── philhealth_claims — CF4 clinical fields, vital signs, OB, signatures, HCP refs ──
        Schema::table('philhealth_claims', function (Blueprint $table) {
            // CF1 — relationship to member
            $table->string('relationship_to_member', 30)->nullable()
                ->comment('Self, Spouse, Child, Parent, Sibling, Other');

            // CF4 Part III — additional clinical narratives
            $table->text('chief_complaint')->nullable()
                ->after('history_of_present_illness');
            $table->text('review_of_systems')->nullable()
                ->after('physical_exam_findings');

            // CF4 Part III — vital signs at admission
            $table->string('blood_pressure', 20)->nullable();
            $table->string('heart_rate', 10)->nullable();
            $table->string('temperature', 10)->nullable();
            $table->string('respiratory_rate', 10)->nullable();

            // CF4 (OB) — obstetrical data
            $table->date('lmp_date')->nullable()
                ->comment('Last Menstrual Period');
            $table->date('edc_date')->nullable()
                ->comment('Expected Date of Confinement');
            $table->unsignedTinyInteger('gravida')->nullable();
            $table->unsignedTinyInteger('para')->nullable();

            // CF2/CF4 — HCP references
            $table->unsignedBigInteger('attending_physician_id')->nullable()->index()
                ->after('case_rate_id');
            $table->unsignedBigInteger('referring_physician_id')->nullable()->index()
                ->after('attending_physician_id');

            $table->foreign('attending_physician_id', 'fk_claims_attending_physician')
                ->references('id')->on('health_care_professionals')->onDelete('NO ACTION');
            $table->foreign('referring_physician_id', 'fk_claims_referring_physician')
                ->references('id')->on('health_care_professionals')->onDelete('NO ACTION');

            // Signatures
            $table->timestamp('patient_signature_date')->nullable();
            $table->timestamp('physician_signature_date')->nullable();
        });

        // ── claim_items — per-item HCP attribution ──
        Schema::table('claim_items', function (Blueprint $table) {
            $table->unsignedBigInteger('health_care_professional_id')->nullable()->index()
                ->after('remarks');
            $table->foreign('health_care_professional_id', 'fk_claim_items_hcp')
                ->references('id')->on('health_care_professionals')->onDelete('NO ACTION');
        });

        // ── philhealth_members — employer info for CF1 ──
        Schema::table('philhealth_members', function (Blueprint $table) {
            $table->string('employer_name', 255)->nullable()
                ->after('mailing_address');
            $table->string('employer_address', 255)->nullable()
                ->after('employer_name');
        });
    }

    public function down(): void
    {
        Schema::table('philhealth_claims', function (Blueprint $table) {
            $table->dropForeign('fk_claims_attending_physician');
            $table->dropForeign('fk_claims_referring_physician');
            $table->dropColumn([
                'relationship_to_member',
                'chief_complaint',
                'review_of_systems',
                'blood_pressure',
                'heart_rate',
                'temperature',
                'respiratory_rate',
                'lmp_date',
                'edc_date',
                'gravida',
                'para',
                'attending_physician_id',
                'referring_physician_id',
                'patient_signature_date',
                'physician_signature_date',
            ]);
        });

        Schema::table('claim_items', function (Blueprint $table) {
            $table->dropForeign('fk_claim_items_hcp');
            $table->dropColumn('health_care_professional_id');
        });

        Schema::table('philhealth_members', function (Blueprint $table) {
            $table->dropColumn(['employer_name', 'employer_address']);
        });
    }
};
