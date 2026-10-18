<?php
foreach(glob('resources/views/**/*.blade.php') as $f) {
    if(strpos(file_get_contents($f), 'supply.items') !== false || strpos(file_get_contents($f), 'Central Supply') !== false) {
        echo $f . "\n";
    }
}
foreach(glob('resources/views/**/**/*.blade.php') as $f) {
    if(strpos(file_get_contents($f), 'supply.items') !== false || strpos(file_get_contents($f), 'Central Supply') !== false) {
        echo $f . "\n";
    }
}
