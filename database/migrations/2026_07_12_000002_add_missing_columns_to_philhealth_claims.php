<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('philhealth_claims', function (Blueprint $table) {
            // eClaims XML fields
            $table->string('patient_is', 5)->nullable()
                ->after('relationship_to_member')
                ->comment('M=Member, S=Spouse, C=Child, P=Parent');
            $table->string('membership_type', 10)->nullable()
                ->after('patient_is')
                ->comment('S=SG, G=GS, I=IP, NS=NS, NO=NO, PS=PS, PG=PG');
            $table->string('encounter_type', 20)->nullable()
                ->after('membership_type')
                ->comment('admission, emergency, outpatient');
            $table->string('disposition_code', 5)->nullable()
                ->after('outcome')
                ->comment('R=Recovered, I=Improved, E=Expired, T=Transferred, H=HAMA, A=Absconded');
            $table->string('tracking_number', 50)->nullable()
                ->after('disposition_code')
                ->comment('CEWS eligibility tracking number');
            $table->string('pension_type', 10)->nullable()
                ->after('tracking_number');
            $table->string('gender', 5)->nullable()
                ->after('pension_type')
                ->comment('Denormalized patient gender for XML');

            // Secondary case rate
            $table->unsignedBigInteger('second_case_rate_id')->nullable()->index()
                ->after('case_rate_id');
        });
    }

    public function down(): void
    {
        Schema::table('philhealth_claims', function (Blueprint $table) {
            $table->dropColumn([
                'patient_is',
                'membership_type',
                'encounter_type',
                'disposition_code',
                'tracking_number',
                'pension_type',
                'gender',
                'second_case_rate_id',
            ]);
        });
    }
};
