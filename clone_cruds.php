<?php

function cloneAndReplace($src, $dest, $replacements) {
    if(file_exists($src)) {
        $c = file_get_contents($src);
        foreach($replacements as $search => $replace) {
            $c = str_replace($search, $replace, $c);
        }
        file_put_contents($dest, $c);
        echo "Created $dest\n";
    }
}

$replacements = [
    'namespace App\Livewire\Pharmacy;' => 'namespace App\Livewire\Supply;',
    'App\Models\Pharmacy\DrugBatch' => 'App\Models\Supply\ItemBatch',
    'App\Models\Pharmacy\Drug' => 'App\Models\Supply\Item',
    'DrugBatchCrud' => 'ItemBatchCrud',
    'DrugBatch' => 'ItemBatch',
    'Drug' => 'Item',
    'drug_id' => 'item_id',
    'drug_search' => 'item_search',
    'drug_batch_id' => 'item_batch_id',
    'drug_text' => 'item_text',
    'drugResults' => 'itemResults',
    'drugSearch' => 'itemSearch',
    'drug' => 'item',
    'generic_name' => 'name',
    'brand_name' => 'description',
    'drug_code' => 'code',
    "('generic_name', 'like'" => "('name', 'like'",
    "('brand_name', 'like'" => "('description', 'like'",
    "('drug_code', 'like'" => "('code', 'like'",
    "'drug_code', 'generic_name', 'brand_name'" => "'code', 'name', 'description'",
    "view('livewire.pharmacy.drug-batch-crud'" => "view('livewire.supply.item-batch-crud'",
    "App\Models\Pharmacy\StockAdjustment" => "App\Models\Supply\ItemStockAdjustment",
    "StockAdjustmentCrud" => "ItemStockAdjustmentCrud",
    "StockAdjustment" => "ItemStockAdjustment",
    "view('livewire.pharmacy.stock-adjustment-crud'" => "view('livewire.supply.item-stock-adjustment-crud'",
    "drugBatch" => "itemBatch"
];

$viewReplacements = [
    'wire:model.live.debounce.300ms="drug_search"' => 'wire:model.live.debounce.300ms="item_search"',
    'wire:click="selectDrug' => 'wire:click="selectItem',
    'wire:click="removeDrug' => 'wire:click="removeItem',
    '$drugResults' => '$itemResults',
    '$drug_search' => '$item_search',
    '$drug_id' => '$item_id',
    '$drug_batch_id' => '$item_batch_id',
    '$drug_text' => '$item_text',
    'drugResults' => 'itemResults',
    'drugSearch' => 'itemSearch',
    'generic_name' => 'name',
    'brand_name' => 'description',
    'drug_code' => 'code',
    'drugBatch' => 'itemBatch',
    'drug' => 'item',
    'Drug' => 'Item',
    '$drug' => '$item',
    "{{ \$item['name'] }} ({{ \$item['description'] }})" => "{{ \$item['name'] }}"
];

cloneAndReplace('app/Livewire/Pharmacy/DrugBatchCrud.php', 'app/Livewire/Supply/ItemBatchCrud.php', $replacements);
cloneAndReplace('app/Livewire/Pharmacy/StockAdjustmentCrud.php', 'app/Livewire/Supply/ItemStockAdjustmentCrud.php', $replacements);
cloneAndReplace('resources/views/livewire/pharmacy/drug-batch-crud.blade.php', 'resources/views/livewire/supply/item-batch-crud.blade.php', $viewReplacements);
cloneAndReplace('resources/views/livewire/pharmacy/stock-adjustment-crud.blade.php', 'resources/views/livewire/supply/item-stock-adjustment-crud.blade.php', $viewReplacements);
