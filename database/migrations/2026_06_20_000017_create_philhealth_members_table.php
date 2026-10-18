<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philhealth_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('pin', 30);
            $table->string('last_name', 60);
            $table->string('first_name', 60);
            $table->string('middle_name', 60)->nullable();
            $table->string('suffix', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('membership_type', 50)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('mailing_address', 255)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->unique(['tenant_id', 'pin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philhealth_members');
    }
};
