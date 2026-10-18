<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['wards', 'ward_rooms', 'er_zones', 'er_beds', 'drugs'];
$schemas = [];
foreach ($tables as $table) {
    $schemas[$table] = Illuminate\Support\Facades\Schema::getColumnListing($table);
}
echo json_encode($schemas);
