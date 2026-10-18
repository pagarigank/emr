<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabInstrument extends Model
{
    use ScopedByTenant;
    protected $table = 'lab_instruments';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime'];

    public function labSection(): BelongsTo { return $this->belongsTo(LabSection::class, 'lab_section_id'); }

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }
}
