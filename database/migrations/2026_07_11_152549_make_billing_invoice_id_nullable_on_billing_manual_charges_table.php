<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE billing_manual_charges DROP CONSTRAINT billing_manual_charges_billing_invoice_id_foreign');
        DB::statement('ALTER TABLE billing_manual_charges ALTER COLUMN billing_invoice_id bigint NULL');
        DB::statement('ALTER TABLE billing_manual_charges ADD CONSTRAINT billing_manual_charges_billing_invoice_id_foreign FOREIGN KEY (billing_invoice_id) REFERENCES billing_invoices(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE billing_manual_charges DROP CONSTRAINT billing_manual_charges_billing_invoice_id_foreign');
        DB::statement('ALTER TABLE billing_manual_charges ALTER COLUMN billing_invoice_id bigint NOT NULL');
        DB::statement('ALTER TABLE billing_manual_charges ADD CONSTRAINT billing_manual_charges_billing_invoice_id_foreign FOREIGN KEY (billing_invoice_id) REFERENCES billing_invoices(id) ON DELETE CASCADE');
    }
};
