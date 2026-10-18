<?php
foreach(glob('app/Livewire/*/*.php') as $f) {
    $c = file_get_contents($f);
    if(strpos($c, 'Drug::where') !== false) {
        echo $f . "\n";
    }
}
