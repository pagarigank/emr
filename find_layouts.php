<?php
foreach(glob('resources/views/components/layouts/*.blade.php') as $f) {
    echo $f."\n";
}
