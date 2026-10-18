<?php

namespace App\Models\Nursing;

use App\Models\Admission;
use App\Models\Pharmacy\PharmacyRequestItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NursingMedicationAdministration extends Model
{
    use ScopedByTenant;
    protected $table = 'nursing_medication_administrations';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'administered_at' => 'datetime',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function pharmacyRequestItem(): BelongsTo
    {
        return $this->belongsTo(PharmacyRequestItem::class, 'pharmacy_request_item_id');
    }

    public function administeredBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'administered_by');
    }
}
