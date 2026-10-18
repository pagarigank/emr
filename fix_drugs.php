<?php
$files = [
    'app/Livewire/Admission/AdmissionCrud.php',
    'app/Livewire/Delivery/DeliveryScheduleCrud.php',
    'app/Livewire/Er/ErAdmissionCrud.php',
    'app/Livewire/MICU/MicuPatientCrud.php',
    'app/Livewire/Ob/ObConsultationCrud.php',
    'app/Livewire/Opd/OpdConsultationCrud.php',
    'app/Livewire/Or/OrScheduleCrud.php',
    'app/Livewire/RenalCare/DialysisSessionCrud.php'
];

$pattern = '/\$drugs\s*=\s*Drug::where\(\'is_active\',\s*true\)\s*->when\(!\s*\$this->isSuperAdmin\(\),\s*fn\s*\(\$q\)\s*=>\s*\$q->where\(\'tenant_id\',\s*\$this->currentTenantId\(\)\)\)\s*->orderBy\(\'generic_name\'\)/';

$replace = <<<EOT
\$drugs = Drug::where('is_active', true)
            ->when(! \$this->isSuperAdmin(), fn (\$q) => \$q->where('tenant_id', \$this->currentTenantId()))
            ->whereHas('batches', function (\$query) {
                \$query->where('quantity_on_hand', '>', 0)
                      ->where('expiry_date', '>=', now()->toDateString())
                      ->where('history', 1)
                      ->where('is_active', 1);
            })
            ->orderBy('generic_name')
EOT;

foreach($files as $f) {
    if(file_exists($f)) {
        $c = file_get_contents($f);
        if(preg_match($pattern, $c)) {
            $c = preg_replace($pattern, $replace, $c);
            file_put_contents($f, $c);
            echo "Updated: $f\n";
        } else {
            echo "NOT FOUND in: $f\n";
        }
    }
}
