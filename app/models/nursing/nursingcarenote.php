<?php

namespace App\Models\Nursing;

use App\Models\Admission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingCareNote extends Model
{
    use ScopedByTenant;
    protected $table = 'nursing_care_notes';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'care_date' => 'date',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'nurse_id');
    }
}
