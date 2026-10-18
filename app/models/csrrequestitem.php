<?php

namespace App\Models;

use App\Models\Supply\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsrRequestItem extends Model
{
    protected $table = 'csr_request_items';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function csrRequest(): BelongsTo { return $this->belongsTo(CsrRequest::class, 'csr_request_id'); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class, 'item_id'); }
}
