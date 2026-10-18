<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::table('users')->where('email', 'admin@demohospital.com')->update(['tenant_id' => 1]);
echo "User admin@demohospital.com tenant_id set to 1\n";
