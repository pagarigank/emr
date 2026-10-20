<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'lab_tests',
        'drugs',
        'radiology_exam_types',
        'procedures',
        'items',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'eecl_item_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->unsignedBigInteger('eecl_item_id')->nullable()->index();
                    $fkName = 'fk_' . substr($tableName, 0, 15) . '_eecl';
                    $table->foreign('eecl_item_id', $fkName)->references('id')->on('eecl_items')->onDelete('set null');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'eecl_item_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $fkName = 'fk_' . substr($tableName, 0, 15) . '_eecl';
                    try {
                        $table->dropForeign($fkName);
                    } catch (\Throwable $e) {}
                    $table->dropColumn('eecl_item_id');
                });
            }
        }
    }
};
