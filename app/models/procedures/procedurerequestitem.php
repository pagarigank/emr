<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureRequestItem extends Model
{
    protected $table = 'procedure_request_items';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'unit_charge' => 'decimal:2',
        'total_charge' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function procedureRequest(): BelongsTo { return $this->belongsTo(ProcedureRequest::class, 'procedure_request_id'); }
    public function procedure(): BelongsTo { return $this->belongsTo(Procedure::class, 'procedure_id'); }
}
