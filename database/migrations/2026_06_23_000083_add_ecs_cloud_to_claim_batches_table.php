<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claim_batches', function (Blueprint $table) {
            $table->string('ecs_tracking_no', 50)->nullable()->after('acknowledgement_date');
            $table->timestamp('ecs_uploaded_at')->nullable()->after('ecs_tracking_no');
            $table->string('cloud_folder', 100)->nullable()->after('ecs_uploaded_at');
            $table->timestamp('cloud_uploaded_at')->nullable()->after('cloud_folder');
            $table->string('acknowledgment_code', 50)->nullable()->after('cloud_uploaded_at');
            $table->text('voucher_reference')->nullable()->after('acknowledgment_code');
        });
    }

    public function down(): void
    {
        Schema::table('claim_batches', function (Blueprint $table) {
            $table->dropColumn([
                'ecs_tracking_no', 'ecs_uploaded_at',
                'cloud_folder', 'cloud_uploaded_at',
                'acknowledgment_code', 'voucher_reference',
            ]);
        });
    }
};
