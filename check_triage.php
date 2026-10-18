<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = \Illuminate\Support\Facades\DB::table('er_triage_records')->select('id', 'patient_id', 'tenant_id')->get();
foreach ($t as $r) {
    echo "Triage ID: {$r->id}, Patient: {$r->patient_id}, Tenant: {$r->tenant_id}\n";
}
