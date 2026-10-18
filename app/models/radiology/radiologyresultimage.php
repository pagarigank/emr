<?php

namespace App\Models\Radiology;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyResultImage extends Model
{
    protected $table = 'radiology_result_images';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_primary' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'file_size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function radiologyRequestItem(): BelongsTo
    {
        return $this->belongsTo(RadiologyRequestItem::class, 'radiology_request_item_id');
    }
}
