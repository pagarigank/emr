<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrugBatch extends Model
{
    protected $table = 'drug_batches';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'expiry_date' => 'date',
        'quantity_on_hand' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function drug(): BelongsTo { return $this->belongsTo(Drug::class, 'drug_id'); }

    public function scopeAvailable(Builder $q): Builder
    {
        return $q->where('quantity_on_hand', '>', 0)->where('expiry_date', '>=', now()->toDateString());
    }
}
