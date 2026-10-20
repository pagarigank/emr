<?php

namespace App\Models\Radiology;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyExamType extends Model
{
    use ScopedByTenant;
    protected $table = 'radiology_exam_types';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime', 'standard_charge' => 'decimal:2', 'tat_minutes' => 'integer'];

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }

    public function modality(): BelongsTo { return $this->belongsTo(RadiologyModality::class, 'modality_id'); }
    public function bodyPart(): BelongsTo { return $this->belongsTo(RadiologyBodyPart::class, 'body_part_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }
    public function eeclItem(): BelongsTo { return $this->belongsTo(\App\Models\Oecb\EeclItem::class, 'eecl_item_id'); }
}
