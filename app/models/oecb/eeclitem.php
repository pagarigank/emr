<?php

namespace App\Models\Oecb;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EeclItem extends Model
{
    protected $table = 'eecl_items';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'fixed_fee' => 'decimal:2',
            'is_active' => 'boolean',
            'created_date' => 'datetime',
            'updated_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function oecbEntries()
    {
        return $this->hasMany(OecbEntry::class, 'eecl_item_id');
    }
}
