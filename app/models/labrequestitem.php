<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabRequestItem extends Model
{
    protected $table = 'lab_request_items';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'result_date' => 'datetime',
        'verified_date' => 'datetime',
        'validated_at' => 'datetime',
        'charge_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function labRequest(): BelongsTo { return $this->belongsTo(LabRequest::class, 'lab_request_id'); }
    public function labTest(): BelongsTo { return $this->belongsTo(LabTest::class, 'lab_test_id'); }
    public function labPanel(): BelongsTo { return $this->belongsTo(LabPanel::class, 'lab_panel_id'); }
    public function performedBy(): BelongsTo { return $this->belongsTo(User::class, 'performed_by'); }
    public function verifiedBy(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }
    public function validatedBy(): BelongsTo { return $this->belongsTo(User::class, 'validated_by'); }

    public function getFlagBadgeClassAttribute(): string
    {
        return match ($this->flag) {
            'H' => 'bg-red-100 text-red-800 border-red-200',
            'L' => 'bg-amber-100 text-amber-800 border-amber-200',
            'N' => 'bg-green-100 text-green-800 border-green-200',
            'Critical' => 'bg-red-200 text-red-900 border-red-300',
            'Abnormal' => 'bg-red-100 text-red-800 border-red-200',
            'Normal' => 'bg-green-100 text-green-800 border-green-200',
            default => 'bg-gray-100 text-gray-600 border-gray-200',
        };
    }

    public function getReferenceRangeTextAttribute(): string
    {
        return $this->reference_range ?? '--';
    }
}
