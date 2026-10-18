<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── tenants — PhilHealth HCI accreditation fields for CF2/CF4 ──
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('philhealth_hci_code', 30)->nullable()
                ->after('contact_number')
                ->comment('PhilHealth-issued HCI facility code');
            $table->string('philhealth_accreditation_no', 50)->nullable()
                ->after('philhealth_hci_code')
                ->comment('HCI PhilHealth accreditation number');
            $table->date('accreditation_date')->nullable()
                ->after('philhealth_accreditation_no')
                ->comment('HCI accreditation validity date');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'philhealth_hci_code',
                'philhealth_accreditation_no',
                'accreditation_date',
            ]);
        });
    }
};
