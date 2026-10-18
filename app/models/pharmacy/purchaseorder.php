<?php

namespace App\Models\Pharmacy;

use App\Models\Supply\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use ScopedByTenant;
    protected $table = 'purchase_orders';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'order_date' => 'date',
        'expected_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function items(): HasMany { return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class, 'supplier_id'); }
}
