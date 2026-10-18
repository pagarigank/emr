<?php
$res = [];
foreach(glob('app/Livewire/*/*.php') as $f) {
    if(strpos(file_get_contents($f), 'openOrderPharmacy') !== false) {
        $res[] = $f;
    }
}
echo implode("\n", $res);
