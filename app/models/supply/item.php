<?php

namespace App\Models\Supply;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $table = 'items';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime', 'unit_cost' => 'decimal:2'];

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }

    public function category(): BelongsTo { return $this->belongsTo(ItemCategory::class, 'item_category_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class, 'supplier_id'); }
    public function unit(): BelongsTo { return $this->belongsTo(SupplyUnit::class, 'supply_unit_id'); }
    
    public function batches() { return $this->hasMany(ItemBatch::class, 'item_id'); }
    public function stockAdjustments() { return $this->hasMany(ItemStockAdjustment::class, 'item_id'); }
}
