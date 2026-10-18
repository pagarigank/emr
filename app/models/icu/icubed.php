<?php

namespace App\Models\Icu;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcuBed extends Model
{
    use ScopedByTenant;
    protected $table = 'icu_beds';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime', 'room_rate' => 'decimal:2'];

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }

    public function ward(): BelongsTo { return $this->belongsTo(IcuWard::class, 'ward_id'); }
}
