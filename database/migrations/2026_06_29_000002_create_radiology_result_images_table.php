<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiology_result_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('radiology_request_item_id');
            $table->string('filename', 255);
            $table->string('original_name', 255);
            $table->string('path', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable()->comment('Size in bytes');
            $table->string('dicom_study_uid', 255)->nullable()->comment('DICOM Study Instance UID for PACS linkage');
            $table->string('dicom_series_uid', 255)->nullable()->comment('DICOM Series Instance UID');
            $table->string('dicom_sop_uid', 255)->nullable()->comment('DICOM SOP Instance UID');
            $table->boolean('is_primary')->default(false)->comment('Primary/representative image');
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamp('updated_date')->nullable();
            $table->foreign('radiology_request_item_id', 'fk_rri_item')->references('id')->on('radiology_request_items')->onDelete('cascade');
            $table->index('radiology_request_item_id', 'idx_rri_item');
            $table->index('dicom_study_uid', 'idx_rri_dicom_study');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radiology_result_images');
    }
};
