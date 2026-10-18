<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('philhealth_claim_id')->index();
            $table->string('document_type', 30);
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->unsignedInteger('file_size')->nullable();
            $table->timestamp('uploaded_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('history')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_documents');
    }
};
