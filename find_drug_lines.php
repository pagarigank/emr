<?php
$files = [
    'app/Livewire/Admission/AdmissionCrud.php',
    'app/Livewire/Delivery/DeliveryScheduleCrud.php',
    'app/Livewire/Doctors/PatientChartCrud.php',
    'app/Livewire/MICU/MicuPatientCrud.php',
    'app/Livewire/Opd/OpdConsultationCrud.php',
    'app/Livewire/Or/OrScheduleCrud.php',
    'app/Livewire/RenalCare/DialysisSessionCrud.php'
];

foreach($files as $f) {
    if(file_exists($f)) {
        $lines = file($f);
        foreach($lines as $k => $line) {
            if(strpos($line, 'Drug::where') !== false || strpos($line, 'Drug::query') !== false) {
                echo $f . ":" . ($k+1) . " -> " . trim($line) . "\n";
            }
        }
    }
}
