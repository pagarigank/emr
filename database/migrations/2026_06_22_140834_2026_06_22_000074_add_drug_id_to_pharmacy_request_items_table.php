<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pharmacy_request_items', function (Blueprint $table) {
            $table->unsignedBigInteger('drug_id')->nullable()->after('item_id');
            $table->unsignedBigInteger('drug_batch_id')->nullable()->after('drug_id');
            $table->foreign('drug_id')->references('id')->on('drugs');
            $table->foreign('drug_batch_id')->references('id')->on('drug_batches');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_request_items', function (Blueprint $table) {
            $table->dropForeign(['drug_batch_id']);
            $table->dropForeign(['drug_id']);
            $table->dropColumn(['drug_id', 'drug_batch_id']);
        });
    }
};
