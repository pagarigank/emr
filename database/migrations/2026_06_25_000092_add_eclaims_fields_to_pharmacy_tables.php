<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add eClaims / CF4 compliance fields to pharmacy_request_items
        Schema::table('pharmacy_request_items', function (Blueprint $table) {
            // Denormalized at order time — CF4 Part V requires generic & brand name
            $table->string('generic_name', 200)->nullable()
                ->after('item_name')
                ->comment('CF4 Part V — Generic name, denormalized at order time');
            $table->string('brand_name', 200)->nullable()
                ->after('generic_name')
                ->comment('CF4 Part V — Brand name, denormalized at order time');
            $table->string('dosage_form', 50)->nullable()
                ->after('dosage')
                ->comment('CF4 Part V — Tablet, Capsule, Syrup, Injectable, Vial, Ampule, etc.');
            $table->string('philhealth_item_code', 50)->nullable()
                ->after('item_code')
                ->comment('PhilHealth Medicine Library code (pItemCode) for eSOA XML submission');
            // Stamped at actual dispense time — eSOA service_date field
            $table->timestamp('dispensed_date')->nullable()
                ->after('updated_date')
                ->comment('Date/time drug was actually dispensed — eSOA service date');
        });

        // Add PhilHealth Item Code to drugs master for library mapping
        Schema::table('drugs', function (Blueprint $table) {
            $table->string('philhealth_item_code', 50)->nullable()
                ->after('drug_code')
                ->comment('PhilHealth Medicine Library code for eSOA XML (pItemCode field)');
        });
    }

    public function down(): void
    {
        Schema::table('pharmacy_request_items', function (Blueprint $table) {
            $table->dropColumn([
                'generic_name',
                'brand_name',
                'dosage_form',
                'philhealth_item_code',
                'dispensed_date',
            ]);
        });

        Schema::table('drugs', function (Blueprint $table) {
            $table->dropColumn('philhealth_item_code');
        });
    }
};
