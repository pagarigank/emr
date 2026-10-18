<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('philhealth_claims', function (Blueprint $table) {
            $table->string('ecs_tracking_no', 50)->nullable()->after('remarks');
            $table->timestamp('ecs_uploaded_at')->nullable()->after('ecs_tracking_no');
            $table->string('cloud_folder', 100)->nullable()->after('ecs_uploaded_at');
            $table->string('cloud_file', 200)->nullable()->after('cloud_folder');
            $table->timestamp('cloud_uploaded_at')->nullable()->after('cloud_file');
            $table->string('acknowledgment_code', 50)->nullable()->after('cloud_uploaded_at');
            $table->timestamp('acknowledgment_date')->nullable()->after('acknowledgment_code');
            $table->string('voucher_number', 50)->nullable()->after('acknowledgment_date');
            $table->decimal('voucher_amount', 12, 2)->nullable()->after('voucher_number');
            $table->timestamp('voucher_date')->nullable()->after('voucher_amount');
        });
    }

    public function down(): void
    {
        Schema::table('philhealth_claims', function (Blueprint $table) {
            $table->dropColumn([
                'ecs_tracking_no', 'ecs_uploaded_at',
                'cloud_folder', 'cloud_file', 'cloud_uploaded_at',
                'acknowledgment_code', 'acknowledgment_date',
                'voucher_number', 'voucher_amount', 'voucher_date',
            ]);
        });
    }
};
