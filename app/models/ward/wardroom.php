<?php

namespace App\Models\Ward;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WardRoom extends Model
{
    use ScopedByTenant;
    protected $table = 'ward_rooms';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'capacity' => 'integer',
        'room_rate' => 'decimal:2',
        'extra_bed_rate' => 'decimal:2',
        'discount_rate' => 'decimal:2',
    ];

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }

    public function ward(): BelongsTo { return $this->belongsTo(Ward::class, 'ward_id'); }
    public function roomType(): BelongsTo { return $this->belongsTo(WardRoomType::class, 'room_type_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }
}
