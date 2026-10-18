<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorAppointment extends Model
{
    use ScopedByTenant;
    protected $table = 'doctor_appointments';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'appointment_date' => 'date',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'health_care_professional_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }
}
