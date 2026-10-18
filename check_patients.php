<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$patients = \Illuminate\Support\Facades\DB::table('patients')->select('id', 'patient_code', 'first_name', 'last_name', 'tenant_id', 'history', 'is_active')->get();
foreach ($patients as $p) {
    echo "Patient ID: {$p->id}, Code: {$p->patient_code}, Name: {$p->first_name} {$p->last_name}, Tenant: {$p->tenant_id}, History: {$p->history}, Active: {$p->is_active}\n";
}
