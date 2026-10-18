<?php

namespace App\Models\Ward;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    use ScopedByTenant;
    protected $table = 'wards';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime'];

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }

    public function rooms(): HasMany { return $this->hasMany(WardRoom::class, 'ward_id'); }
}
