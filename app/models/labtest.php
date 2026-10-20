<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTest extends Model
{
    use ScopedByTenant;
    protected $table = 'lab_tests';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime'];

    public function labSection(): BelongsTo { return $this->belongsTo(LabSection::class, 'lab_section_id'); }
    public function specimenType(): BelongsTo { return $this->belongsTo(SpecimenType::class, 'specimen_type_id'); }
    public function labUnit(): BelongsTo { return $this->belongsTo(LabUnit::class, 'lab_unit_id'); }
    public function referenceRanges(): HasMany { return $this->hasMany(ReferenceRange::class, 'lab_test_id'); }
    public function eeclItem(): BelongsTo { return $this->belongsTo(\App\Models\Oecb\EeclItem::class, 'eecl_item_id'); }

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }
}
