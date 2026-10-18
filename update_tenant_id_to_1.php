<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'er_zones',
    'er_beds',
    'wards',
    'ward_rooms',
    'drugs',
    'item_categories',
    'items',
    'item_batches'
];

foreach ($tables as $table) {
    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
        $records = \Illuminate\Support\Facades\DB::table($table)->where('tenant_id', 2)->get();
        $count = 0;
        foreach ($records as $r) {
            try {
                \Illuminate\Support\Facades\DB::table($table)->where('id', $r->id)->update(['tenant_id' => 1]);
                $count++;
            } catch (\Exception $e) {
                // If it already exists for tenant 1, we can optionally delete the tenant 2 version
                \Illuminate\Support\Facades\DB::table($table)->where('id', $r->id)->delete();
            }
        }
        echo "Updated $count records in $table to tenant_id = 1 (deleted duplicates)\n";
    }
}
