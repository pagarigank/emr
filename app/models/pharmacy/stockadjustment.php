<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use ScopedByTenant;
    protected $table = 'stock_adjustments';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function drug(): BelongsTo { return $this->belongsTo(Drug::class, 'drug_id'); }
    public function drugBatch(): BelongsTo { return $this->belongsTo(DrugBatch::class, 'drug_batch_id'); }
}
