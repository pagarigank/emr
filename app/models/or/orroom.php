<?php

namespace App\Models\Or;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrRoom extends Model
{
    use ScopedByTenant;
    protected $table = 'or_rooms';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(OrSchedule::class, 'or_room_id');
    }
}
