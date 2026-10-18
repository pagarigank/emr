<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use ScopedByTenant;
    protected $table = 'items';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime', 'unit_cost' => 'decimal:2'];

    public function itemCategory(): BelongsTo { return $this->belongsTo(ItemCategory::class, 'item_category_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class, 'supplier_id'); }
    public function supplyUnit(): BelongsTo { return $this->belongsTo(SupplyUnit::class, 'supply_unit_id'); }

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }
}
