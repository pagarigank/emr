<?php

namespace App\Models\Procedures;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Procedure extends Model
{
    use ScopedByTenant;
    protected $table = 'procedures';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean', 'created_date' => 'datetime', 'updated_date' => 'datetime', 'standard_charge' => 'decimal:2'];

    protected static function booted(): void { static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1)); }

    public function category(): BelongsTo { return $this->belongsTo(ProcedureCategory::class, 'category_id'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class, 'department_id'); }
    public function eeclItem(): BelongsTo { return $this->belongsTo(\App\Models\Oecb\EeclItem::class, 'eecl_item_id'); }
}
