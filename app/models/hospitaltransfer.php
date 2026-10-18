<?php

namespace App\Models;

use App\Models\Delivery\DeliverySchedule;
use App\Models\Er\ErAdmission;
use App\Models\Ob\ObConsultation;
use App\Models\Opd\OpdConsultation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalTransfer extends Model
{
    use ScopedByTenant;
    protected $table = 'hospital_transfers';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'transfer_datetime' => 'datetime',
        'completed_datetime' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function destinationHospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class, 'destination_hospital_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'transferred_by');
    }

    public function source()
    {
        return $this->morphTo(null, 'source_type', 'source_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }
}
