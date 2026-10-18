<?php

namespace App\Models\Delivery;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryRoom extends Model
{
    protected $table = 'dr_rooms';
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
        return $this->hasMany(DeliverySchedule::class, 'dr_room_id');
    }
}
