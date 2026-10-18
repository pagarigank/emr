<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE billing_manual_charges ADD patient_id bigint NULL');
        DB::statement('CREATE INDEX idx_billing_manual_charges_patient_id ON billing_manual_charges (patient_id)');
        DB::statement('CREATE INDEX idx_billing_manual_charges_invoice_null ON billing_manual_charges (billing_invoice_id) WHERE billing_invoice_id IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX idx_billing_manual_charges_patient_id ON billing_manual_charges');
        DB::statement('DROP INDEX idx_billing_manual_charges_invoice_null ON billing_manual_charges');
        DB::statement('ALTER TABLE billing_manual_charges DROP COLUMN patient_id');
    }
};
