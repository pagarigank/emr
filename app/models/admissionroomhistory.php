<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;

class AdmissionRoomHistory extends Model
{
    use ScopedByTenant;
    protected $table = 'admission_room_histories';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
        'room_rate' => 'float',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public static function recordAssignment(
        int $admissionId,
        string $roomType,
        int $roomId,
        ?string $roomName,
        ?string $wardName,
        float $roomRate,
        int $tenantId,
        int $userId,
        \DateTimeInterface $assignedAt
    ): self {
        return static::create([
            'tenant_id' => $tenantId,
            'admission_id' => $admissionId,
            'room_type' => $roomType,
            'room_id' => $roomId,
            'room_name' => $roomName,
            'ward_name' => $wardName,
            'room_rate' => $roomRate,
            'assigned_at' => $assignedAt,
            'is_active' => true,
            'history' => 1,
            'created_by' => $userId,
            'created_date' => $assignedAt,
            'updated_by' => $userId,
            'updated_date' => $assignedAt,
        ]);
    }

    public static function closeActive(int $admissionId, \DateTimeInterface $unassignedAt, int $userId): void
    {
        static::where('admission_id', $admissionId)
            ->where('is_active', true)
            ->update([
                'unassigned_at' => $unassignedAt,
                'is_active' => false,
                'updated_by' => $userId,
                'updated_date' => $unassignedAt,
            ]);
    }

    /**
     * Auto-compute days stayed in this room/bed assignment.
     * Uses unassigned_at or admission discharge_date or now() if still in this room.
     */
    public function getDaysStayedAttribute(): int
    {
        if (!$this->assigned_at) {
            return 0;
        }

        $end = $this->unassigned_at ?? $this->admission?->discharge_date ?? now();
        $calendarDays = (int) $this->assigned_at->copy()->startOfDay()->diffInDays($end->copy()->startOfDay());
        $days = max(1, $calendarDays + 1);

        // Noon cutoff rule: if unassigned/discharged before 12:00 PM on multi-day stay
        if ($this->unassigned_at && $calendarDays > 0 && (int) $this->unassigned_at->format('H') < 12) {
            $days = max(1, $days - 1);
        } elseif (!$this->unassigned_at && $this->admission?->discharge_date && $calendarDays > 0 && (int) $this->admission->discharge_date->format('H') < 12) {
            $days = max(1, $days - 1);
        }

        return $days;
    }

    /**
     * Compute total charge for this room assignment.
     */
    public function getTotalChargeAttribute(): float
    {
        return (float) ($this->days_stayed * (float) $this->room_rate);
    }
}
