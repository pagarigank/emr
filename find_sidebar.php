<?php
foreach(glob('app/Livewire/*.php') as $f) {
    if(stripos($f, 'sidebar') !== false) echo $f."\n";
}
foreach(glob('resources/views/**/*.blade.php') as $f) {
    if(stripos($f, 'sidebar') !== false) echo $f."\n";
}
