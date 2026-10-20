<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drug extends Model
{
    use ScopedByTenant;
    protected $table = 'drugs';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'reorder_level' => 'integer',
        'reorder_qty' => 'integer',
    ];
    
    protected $appends = ['drug_name'];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function batches(): HasMany { return $this->hasMany(DrugBatch::class, 'drug_id'); }
    public function eeclItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Oecb\EeclItem::class, 'eecl_item_id'); }

    public function getDrugNameAttribute(): string
    {
        return $this->brand_name ? "{$this->generic_name} ({$this->brand_name})" : $this->generic_name;
    }
}
