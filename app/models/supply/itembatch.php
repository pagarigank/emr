<?php

namespace App\Models\Supply;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemBatch extends Model
{
    protected $table = 'item_batches';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean', 
        'created_date' => 'datetime', 
        'updated_date' => 'datetime', 
        'quantity_on_hand' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'expiry_date' => 'date'
    ];

    protected static function booted(): void 
    { 
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); 
    }

    public function item(): BelongsTo 
    { 
        return $this->belongsTo(Item::class, 'item_id'); 
    }
}
