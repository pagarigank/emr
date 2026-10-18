<?php

namespace App\Models\Konsulta;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonsultaLabReferral extends Model
{
    use ScopedByTenant;
    protected $table = 'konsulta_lab_referrals';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'referral_date' => 'date',
        'performed_date' => 'date',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(KonsultaConsultation::class, 'consultation_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(KonsultaEnrollment::class, 'enrollment_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
