<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('patient_code', 20);

            // Link to PhilHealth member (optional)
            $table->unsignedBigInteger('philhealth_member_id')->nullable();

            // Personal Information
            $table->string('last_name', 60);
            $table->string('first_name', 60);
            $table->string('middle_name', 60)->nullable();
            $table->string('suffix', 20)->nullable();
            $table->date('date_of_birth')->nullable();

            // Demographics (FK to reference tables)
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('civil_status_id')->nullable();
            $table->unsignedBigInteger('religion_id')->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->unsignedBigInteger('occupation_id')->nullable();
            $table->unsignedBigInteger('blood_type_id')->nullable();

            // Contact Information
            $table->string('contact_no', 30)->nullable();
            $table->string('email', 100)->nullable();

            // Address
            $table->string('address_street', 255)->nullable();
            $table->unsignedBigInteger('barangay_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('zip_code', 10)->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name', 120)->nullable();
            $table->string('emergency_contact_no', 30)->nullable();
            $table->string('emergency_relationship', 60)->nullable();

            // Status & Audit
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();

            $table->unique(['patient_code', 'tenant_id', 'history'], 'uq_patients_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
