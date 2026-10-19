<?php

namespace App\Models\Er;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;

class ErTriageRecord extends Model
{
    use ScopedByTenant;
    protected $table = 'er_triage_records';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'triage_datetime' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('history', 1);
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function acuityLevel()
    {
        return $this->belongsTo(ErAcuityLevel::class, 'er_acuity_level_id');
    }

    public function mobilityStatus()
    {
        return $this->belongsTo(ErMobilityStatus::class, 'er_mobility_status_id');
    }

    public function admission()
    {
        return $this->hasOne(ErAdmission::class, 'er_triage_record_id');
    }
}

if (!class_exists('App\Models\ER\ErTriageRecord', false)) {
    class_alias(ErTriageRecord::class, 'App\Models\ER\ErTriageRecord');
}

