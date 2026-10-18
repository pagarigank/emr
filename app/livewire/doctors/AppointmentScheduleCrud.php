<?php

namespace App\Livewire\Doctors;

use App\Models\Department;
use App\Models\DoctorAppointment;
use App\Models\HealthCareProfessional;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentScheduleCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $dateFilter = '';
    public ?string $doctorFilter = null;
    public int $perPage = 10;

    public bool $showModal = false;
    public ?int $editingId = null;
    public ?string $edit_date = null;
    public ?string $edit_start_time = null;
    public ?string $edit_notes = null;
    public string $edit_status = 'Scheduled';
    public array $availableSlots = [];

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    private function isAdmin(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'tenant_admin']);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDoctorFilter(): void
    {
        $this->resetPage();
    }

    public function openEdit(int $id): void
    {
        $apt = DoctorAppointment::with(['department', 'doctor'])->findOrFail($id);
        $this->editingId = $apt->id;
        $this->edit_date = $apt->appointment_date?->format('Y-m-d') ?? '';
        $this->edit_start_time = $apt->start_time;
        $this->edit_notes = $apt->notes ?? '';
        $this->edit_status = $apt->status;
        $this->showModal = true;
        $this->loadAvailableSlots();
    }

    public function updatedEditDate(): void
    {
        $this->edit_start_time = null;
        $this->loadAvailableSlots();
    }

    public function selectSlot(string $slot): void
    {
        $this->edit_start_time = $slot;
    }

    public function loadAvailableSlots(): void
    {
        $this->availableSlots = [];
        if (! $this->editingId || ! $this->edit_date) {
            return;
        }

        $apt = DoctorAppointment::find($this->editingId);
        if (! $apt) {
            return;
        }

        $date = Carbon::parse($this->edit_date)->toDateString();
        $slotMinutes = (int) config('appointment.slot_minutes', 30);
        $start = Carbon::parse(config('appointment.working_start', '08:00'));
        $end = Carbon::parse(config('appointment.working_end', '17:00'));

        $taken = DoctorAppointment::query()
            ->where('health_care_professional_id', $apt->health_care_professional_id)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'Cancelled')
            ->where('id', '!=', $apt->id)
            ->get(['start_time', 'end_time'])
            ->map(fn ($a) => ['start' => $a->start_time, 'end' => $a->end_time]);

        $slots = [];
        while ($start->lt($end)) {
            $slotStart = $start->copy();
            $slotEnd = $start->copy()->addMinutes($slotMinutes);
            $st = $slotStart->format('H:i');
            $et = $slotEnd->format('H:i');

            $overlap = $taken->contains(fn ($b) => $st < $b['end'] && $et > $b['start']);

            if (! $overlap) {
                $slots[] = ['start' => $st, 'end' => $et, 'label' => $slotStart->format('g:i A')];
            }
            $start->addMinutes($slotMinutes);
        }

        $this->availableSlots = $slots;
    }

    public function save(): void
    {
        $this->validate([
            'edit_date' => ['required', 'date'],
            'edit_start_time' => ['required', 'string'],
            'edit_notes' => ['nullable', 'string', 'max:5000'],
            'edit_status' => ['required', 'string', 'in:Scheduled,In Progress,Completed,Cancelled'],
        ]);

        $apt = DoctorAppointment::findOrFail($this->editingId);
        $slotMinutes = (int) config('appointment.slot_minutes', 30);
        $date = Carbon::parse($this->edit_date)->toDateString();
        $st = $this->edit_start_time;
        $et = Carbon::parse($date . ' ' . $st)->addMinutes($slotMinutes)->format('H:i');

        $conflict = DoctorAppointment::query()
            ->where('health_care_professional_id', $apt->health_care_professional_id)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'Cancelled')
            ->where('id', '!=', $apt->id)
            ->where('start_time', '<', $et)
            ->where('end_time', '>', $st)
            ->exists();

        if ($conflict) {
            $this->addError('edit_start_time', 'This time conflicts with an existing appointment. Please pick another slot.');
            $this->loadAvailableSlots();
            return;
        }

        $apt->update([
            'appointment_date' => $date,
            'start_time' => $st,
            'end_time' => $et,
            'notes' => $this->edit_notes,
            'status' => $this->edit_status,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->showModal = false;
        $this->reset(['editingId', 'edit_date', 'edit_start_time', 'edit_notes', 'edit_status', 'availableSlots']);
        $this->dispatch('toast', type: 'success', message: 'Appointment updated successfully.');
    }

    public function markInProgress(int $id): void
    {
        DoctorAppointment::findOrFail($id)->update([
            'status' => 'In Progress',
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: 'Appointment marked as in progress.');
    }

    public function markDone(int $id): void
    {
        DoctorAppointment::findOrFail($id)->update([
            'status' => 'Completed',
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: 'Appointment marked as completed.');
    }

    public function cancelAppointment(int $id): void
    {
        DoctorAppointment::findOrFail($id)->update([
            'status' => 'Cancelled',
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);
        $this->dispatch('toast', type: 'success', message: 'Appointment cancelled.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingId', 'edit_date', 'edit_start_time', 'edit_notes', 'edit_status', 'availableSlots']);
    }

    public function render()
    {
        $isAdmin = $this->isAdmin();
        $doctorId = auth()->user()?->health_care_professional_id;

        $items = DoctorAppointment::with(['department', 'doctor'])
            ->when(! $isAdmin && $doctorId, fn ($q) => $q->where('health_care_professional_id', $doctorId))
            ->when($isAdmin && $this->doctorFilter, fn ($q) => $q->where('health_care_professional_id', $this->doctorFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->dateFilter, fn ($q) => $q->where('appointment_date', $this->dateFilter))
            ->when($this->search, fn ($q) => $q->where(fn ($inner) =>
                $inner->where('patient_name', 'like', '%' . $this->search . '%')
                    ->orWhere('appointment_code', 'like', '%' . $this->search . '%')
            ))
            ->orderBy('appointment_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate($this->perPage);

        $departments = Department::query()
            ->where('tenant_id', $this->currentTenantId())
            ->where('is_active', 1)
            ->orderBy('dept_name')
            ->get(['id', 'dept_name']);

        $doctors = $isAdmin
            ? HealthCareProfessional::query()
                ->where('tenant_id', $this->currentTenantId())
                ->where('is_active', 1)
                ->orderBy('full_name')
                ->get(['id', 'full_name'])
            : collect();

        return view('livewire.doctors.appointment-schedule-crud', [
            'items' => $items,
            'departments' => $departments,
            'doctors' => $doctors,
            'isAdmin' => $isAdmin,
            'doctorId' => $doctorId,
        ])->layout('layouts.app');
    }
}
