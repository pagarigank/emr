<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = \Illuminate\Support\Facades\DB::table('users')->where('email', 'admin@demohospital.com')->first();
echo "ID: {$u->id} Name: {$u->name} Tenant: {$u->tenant_id}\n";
