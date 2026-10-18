<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('radiology_request_items', function (Blueprint $table) {
            $table->text('result_text')->nullable()->after('status')->comment('Radiologist reading');
            $table->text('doctor_notes')->nullable()->after('result_text')->comment('Additional notes');
            $table->string('image_url')->nullable()->after('doctor_notes')->comment('Path to uploaded image/DICOM');
            $table->timestamp('result_date')->nullable()->after('image_url');
            $table->unsignedBigInteger('radiologist_id')->nullable()->after('result_date');

            $table->foreign('radiologist_id')->references('id')->on('health_care_professionals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radiology_request_items', function (Blueprint $table) {
            $table->dropForeign(['radiologist_id']);
            $table->dropColumn([
                'result_text',
                'doctor_notes',
                'image_url',
                'result_date',
                'radiologist_id'
            ]);
        });
    }
};
