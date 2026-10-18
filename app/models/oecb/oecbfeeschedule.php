<?php

namespace App\Models\Oecb;

use App\Models\Oecb\OecbCorePresentation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class OecbFeeSchedule extends Model
{
    protected $table = 'oecb_fee_schedules';
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

    public function corePresentation(): BelongsTo
    {
        return $this->belongsTo(OecbCorePresentation::class, 'core_presentation_id');
    }
}
