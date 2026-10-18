<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Identity & Background
            $table->string('maiden_name', 60)->nullable()->after('last_name');
            $table->string('place_of_birth', 255)->nullable()->after('date_of_birth');
            $table->string('preferred_language', 60)->nullable()->after('blood_type_id');
            
            // Standard Identifiers
            $table->string('national_id_no', 30)->nullable()->after('philhealth_member_id');
            $table->string('passport_no', 30)->nullable()->after('national_id_no');

            // Family & Next of Kin
            $table->string('father_name', 120)->nullable()->after('zip_code');
            $table->string('mother_maiden_name', 120)->nullable()->after('father_name');
            $table->string('spouse_name', 120)->nullable()->after('mother_maiden_name');

            // Clinical / Interoperability Indicators
            $table->boolean('multiple_birth_indicator')->default(false)->after('spouse_name');
            $table->boolean('is_deceased')->default(false)->after('multiple_birth_indicator');
            $table->date('date_of_death')->nullable()->after('is_deceased');
            $table->unsignedBigInteger('primary_care_provider_id')->nullable()->after('date_of_death');
            
            // Note: In an actual enterprise app, you might want a foreign key here, 
            // but we'll assume it might reference health_care_professionals if it exists or we just leave it as unsignedBigInteger for now.
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'maiden_name',
                'place_of_birth',
                'preferred_language',
                'national_id_no',
                'passport_no',
                'father_name',
                'mother_maiden_name',
                'spouse_name',
                'multiple_birth_indicator',
                'is_deceased',
                'date_of_death',
                'primary_care_provider_id'
            ]);
        });
    }
};
