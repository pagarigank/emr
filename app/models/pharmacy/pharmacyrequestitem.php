<?php

namespace App\Models\Pharmacy;

use App\Models\Supply\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyRequestItem extends Model
{
    protected $table = 'pharmacy_request_items';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'quantity' => 'decimal:2',
        'given_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function pharmacyRequest(): BelongsTo { return $this->belongsTo(PharmacyRequest::class, 'pharmacy_request_id'); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class, 'item_id'); }
    public function drug(): BelongsTo { return $this->belongsTo(Drug::class, 'drug_id'); }
    public function drugBatch(): BelongsTo { return $this->belongsTo(DrugBatch::class, 'drug_batch_id'); }
}
