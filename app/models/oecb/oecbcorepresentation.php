<?php

namespace App\Models\Oecb;

use App\Models\Oecb\OecbFeeSchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class OecbCorePresentation extends Model
{
    protected $table = 'oecb_core_presentations';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'created_date' => 'datetime',
            'updated_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function feeSchedules(): HasMany
    {
        return $this->hasMany(OecbFeeSchedule::class, 'core_presentation_id');
    }
}
