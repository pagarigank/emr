<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radiology_request_items', function (Blueprint $table) {
            $table->text('impression')->nullable()->after('result_text')->comment('Radiologist impression / conclusion');
            $table->text('technique')->nullable()->after('impression')->comment('Technique description');
            $table->text('findings')->nullable()->after('technique')->comment('Detailed findings');
            $table->unsignedBigInteger('performed_by')->nullable()->after('radiologist_id');
            $table->timestamp('performed_at')->nullable()->after('performed_by');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('radiology_request_items', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->dropColumn(['impression', 'technique', 'findings', 'performed_by', 'performed_at']);
        });
    }
};
