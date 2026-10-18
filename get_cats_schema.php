<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo json_encode(['categories' => Illuminate\Support\Facades\Schema::getColumnListing('item_categories'), 'units' => Illuminate\Support\Facades\Schema::getColumnListing('supply_units')]);
