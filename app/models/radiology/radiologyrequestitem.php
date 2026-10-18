<?php

namespace App\Models\Radiology;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiologyRequestItem extends Model
{
    protected $table = 'radiology_request_items';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'result_date' => 'datetime',
        'performed_at' => 'datetime',
        'charge_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function radiologyRequest(): BelongsTo { return $this->belongsTo(RadiologyRequest::class, 'radiology_request_id'); }
    public function examType(): BelongsTo { return $this->belongsTo(RadiologyExamType::class, 'exam_type_id'); }
    public function radiologist(): BelongsTo { return $this->belongsTo(\App\Models\HealthCareProfessional::class, 'radiologist_id'); }
    public function performedBy(): BelongsTo { return $this->belongsTo(\App\Models\User::class, 'performed_by'); }

    public function images(): HasMany
    {
        return $this->hasMany(RadiologyResultImage::class, 'radiology_request_item_id');
    }

    public function primaryImage(): BelongsTo
    {
        return $this->belongsTo(RadiologyResultImage::class, 'id', 'radiology_request_item_id')
            ->where('is_primary', true)
            ->where('history', 1);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Complete' => 'bg-green-50 text-green-700 border-green-200',
            'Approved' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'Partial' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }

    public function getPatientNameAttribute(): ?string
    {
        return $this->radiologyRequest?->patient_name;
    }

    public function getRequestCodeAttribute(): ?string
    {
        return $this->radiologyRequest?->request_code;
    }

    public function getImageCountAttribute(): int
    {
        return $this->images()->count();
    }

    public function getReportSummaryAttribute(): string
    {
        $text = $this->impression ?? $this->result_text ?? '';
        return mb_strlen($text) > 100 ? mb_substr($text, 0, 100) . '...' : $text;
    }
}
