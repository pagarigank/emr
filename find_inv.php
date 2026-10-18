<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$tables = Illuminate\Support\Facades\DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
foreach($tables as $t) {
    $cols = Illuminate\Support\Facades\Schema::getColumnListing($t->TABLE_NAME);
    if(in_array('quantity_on_hand', $cols) || in_array('stock_on_hand', $cols) || in_array('quantity', $cols)) {
        echo $t->TABLE_NAME . " -> " . implode(", ", array_intersect($cols, ['quantity_on_hand', 'stock_on_hand', 'quantity'])) . "\n";
    }
}
