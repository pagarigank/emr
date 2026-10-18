<?php

namespace App\Livewire\Public;

use App\Models\Department;
use App\Models\DoctorAppointment;
use App\Models\HealthCareProfessional;
use App\Models\Hospital;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AppointmentBooking extends Component
{
    // Selection state
    public ?string $department_id = null;
    public ?string $health_care_professional_id = null;
    public ?string $appointment_date = null;

    // Patient info
    public string $patient_name = '';
    public ?string $patient_phone = null;
    public ?string $patient_email = null;
    public ?string $reason = null;

    // Booked slot result
    public ?string $selected_slot = null;
    public bool $booked = false;
    public ?string $booked_code = null;
    public array $booked_slots = [];

    // Validation
    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer'],
            'health_care_professional_id' => ['required', 'integer'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'patient_name' => ['required', 'string', 'max:150'],
            'patient_phone' => ['nullable', 'string', 'max:30'],
            'patient_email' => ['nullable', 'email', 'max:150'],
            'reason' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function updatedDepartmentId(): void
    {
        $this->health_care_professional_id = null;
        $this->appointment_date = null;
        $this->selected_slot = null;
        $this->booked_slots = [];
    }

    public function updatedHealthCareProfessionalId(): void
    {
        $this->appointment_date = null;
        $this->selected_slot = null;
        $this->booked_slots = [];
    }

    public function updatedAppointmentDate(): void
    {
        $this->selected_slot = null;
        $this->loadAvailableSlots();
    }

    public function selectSlot(string $slot): void
    {
        $this->selected_slot = $slot;
    }

    public function loadAvailableSlots(): void
    {
        $this->booked_slots = [];
        if (! $this->health_care_professional_id || ! $this->appointment_date) {
            return;
        }

        $tid = $this->publicTenantId();
        $date = Carbon::parse($this->appointment_date)->toDateString();
        $slotMinutes = (int) config('appointment.slot_minutes', 30);
        $start = Carbon::parse(config('appointment.working_start', '08:00'));
        $end = Carbon::parse(config('appointment.working_end', '17:00'));

        $taken = DoctorAppointment::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('health_care_professional_id', $this->health_care_professional_id)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'Cancelled')
            ->get(['start_time', 'end_time'])
            ->map(fn ($a) => ['start' => $a->start_time, 'end' => $a->end_time]);

        $slots = [];
        $now = Carbon::now();
        while ($start->lt($end)) {
            $slotStart = $start->copy();
            $slotEnd = $start->copy()->addMinutes($slotMinutes);
            $st = $slotStart->format('H:i');
            $et = $slotEnd->format('H:i');

            $overlap = $taken->contains(fn ($b) => $st < $b['end'] && $et > $b['start']);

            $isPast = $this->appointment_date === $now->toDateString() && $slotStart->lt($now);

            if (! $overlap && ! $isPast) {
                $slots[] = ['start' => $st, 'end' => $et, 'label' => $slotStart->format('g:i A')];
            }
            $start->addMinutes($slotMinutes);
        }

        $this->booked_slots = $slots;
    }

    public function getDepartmentsProperty()
    {
        return Department::withoutGlobalScopes()
            ->where('tenant_id', $this->publicTenantId())
            ->where('is_active', 1)
            ->where('history', 1)
            ->orderBy('dept_name')
            ->get(['id', 'dept_name', 'dept_code']);
    }

    public function getDoctorsProperty()
    {
        if (! $this->department_id) {
            return collect();
        }
        return HealthCareProfessional::withoutGlobalScopes()
            ->where('tenant_id', $this->publicTenantId())
            ->where('department_id', $this->department_id)
            ->where('is_active', 1)
            ->where('history', 1)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'professional_type', 'specialization']);
    }

    public function book(): void
    {
        $this->validate();

        if (! $this->selected_slot) {
            $this->addError('selected_slot', 'Please choose an available time slot.');
            return;
        }

        $tid = $this->publicTenantId();
        $slotMinutes = (int) config('appointment.slot_minutes', 30);
        $date = Carbon::parse($this->appointment_date)->toDateString();
        $slotStart = Carbon::parse($date . ' ' . $this->selected_slot);
        $slotEnd = $slotStart->copy()->addMinutes($slotMinutes);
        $st = $slotStart->format('H:i');
        $et = $slotEnd->format('H:i');

        // Atomic check-and-create to prevent double booking (race condition safe)
        $code = null;
        DB::transaction(function () use (&$code, $tid, $st, $et, $date) {
            $conflict = DoctorAppointment::withoutGlobalScopes()
                ->where('tenant_id', $tid)
                ->where('health_care_professional_id', $this->health_care_professional_id)
                ->where('appointment_date', $date)
                ->where('status', '!=', 'Cancelled')
                ->where('start_time', '<', $et)
                ->where('end_time', '>', $st)
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                $this->addError('selected_slot', 'Sorry, this slot was just taken. Please pick another time.');
                $this->loadAvailableSlots();
                return;
            }

            $code = $this->generateCode($tid);
            DoctorAppointment::create([
                'tenant_id' => $tid,
                'appointment_code' => $code,
                'department_id' => $this->department_id,
                'health_care_professional_id' => $this->health_care_professional_id,
                'appointment_date' => $date,
                'start_time' => $st,
                'end_time' => $et,
                'patient_name' => $this->patient_name,
                'patient_phone' => $this->patient_phone,
                'patient_email' => $this->patient_email,
                'reason' => $this->reason,
                'status' => 'Scheduled',
                'is_active' => true,
                'history' => 1,
                'created_by' => null,
                'created_date' => now(),
                'updated_by' => null,
                'updated_date' => null,
            ]);
        });

        if ($code) {
            $this->booked = true;
            $this->booked_code = $code;
        }
    }

    public function bookAnother(): void
    {
        $this->reset(['department_id', 'health_care_professional_id', 'appointment_date', 'patient_name', 'patient_phone', 'patient_email', 'reason', 'selected_slot', 'booked_code', 'booked']);
        $this->resetValidation();
    }

    private function publicTenantId(): int
    {
        $configured = config('appointment.public_tenant_id');
        if ($configured) {
            return (int) $configured;
        }
        $first = Hospital::withoutGlobalScopes()->orderBy('id')->value('id');
        return $first ? (int) $first : 1;
    }

    private function generateCode(int $tid): string
    {
        $last = DoctorAppointment::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->orderBy('id', 'desc')
            ->first();
        $num = $last ? ((int) substr($last->appointment_code, 4)) + 1 : 1;
        return 'APT-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.public.appointment-booking')->layout('layouts.guest');
    }
}
