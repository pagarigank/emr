<?php

namespace App\Models\Supply;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemStockAdjustment extends Model
{
    use ScopedByTenant;
    protected $table = 'item_stock_adjustments';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean', 
        'created_date' => 'datetime', 
        'updated_date' => 'datetime', 
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2'
    ];

    protected static function booted(): void 
    { 
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); 
    }

    public function item(): BelongsTo 
    { 
        return $this->belongsTo(Item::class, 'item_id'); 
    }

    public function itemBatch(): BelongsTo 
    { 
        return $this->belongsTo(ItemBatch::class, 'item_batch_id'); 
    }
}
