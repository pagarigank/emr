<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_request_items', function (Blueprint $table) {
            $table->string('flag', 10)->nullable()->after('result_categorical');
            $table->string('reference_range', 100)->nullable()->after('flag');
            $table->unsignedBigInteger('validated_by')->nullable()->after('verified_date');
            $table->timestamp('validated_at')->nullable()->after('validated_by');
            $table->foreign('validated_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('lab_request_items', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['flag', 'reference_range', 'validated_by', 'validated_at']);
        });
    }
};
