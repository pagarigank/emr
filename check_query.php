<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$patientSearch = 'penduko';
$patients = \App\Models\Patient::where('tenant_id', 1)
    ->where(function($q) use ($patientSearch) {
        $q->where('last_name', 'like', '%' . $patientSearch . '%')
          ->orWhere('first_name', 'like', '%' . $patientSearch . '%')
          ->orWhere('middle_name', 'like', '%' . $patientSearch . '%')
          ->orWhere('maiden_name', 'like', '%' . $patientSearch . '%')
          ->orWhere('patient_code', 'like', '%' . $patientSearch . '%');
    })
    ->limit(20)
    ->get();

echo "Count: " . $patients->count() . "\n";
foreach ($patients as $p) {
    echo "ID: {$p->id} Name: {$p->first_name} {$p->last_name}\n";
}
