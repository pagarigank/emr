<?php

namespace App\Livewire\Doctors;

use App\Models\Admission;
use App\Models\Er\ErAdmission;
use App\Models\Opd\OpdConsultation;
use Livewire\Component;
use Livewire\WithPagination;

class DashboardCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $activeTab = 'opd'; // opd, er, ward

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    private function getDoctorId(): ?int
    {
        return auth()->user()?->health_care_professional_id;
    }

    private function getTenantId(): ?int
    {
        return request()->attributes->get('tenant_id');
    }

    private function isAdmin(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'tenant_admin']);
    }

    public function render()
    {
        $doctorId = $this->getDoctorId();
        $tenantId = $this->getTenantId();
        $isAdmin = $this->isAdmin();

        $opdPatients = collect();
        $erPatients = collect();
        $wardPatients = collect();

        if ($doctorId || $isAdmin) {
            if ($this->activeTab === 'opd') {
                $query = OpdConsultation::with(['patient'])
                    ->where('tenant_id', $tenantId)
                    ->whereIn('status', ['Waiting', 'In Progress']);

                if (!$isAdmin) {
                    $query->where('attending_physician_id', $doctorId);
                }

                $opdPatients = $query
                    ->when($this->search, fn ($q) => $q->whereHas('patient', fn ($p) => $p->where('last_name', 'like', '%' . $this->search . '%')->orWhere('first_name', 'like', '%' . $this->search . '%')->orWhere('patient_code', 'like', '%' . $this->search . '%')))
                    ->orderBy('consultation_datetime', 'desc')
                    ->paginate(10);
            }

            if ($this->activeTab === 'er') {
                $query = ErAdmission::with(['patient'])
                    ->where('tenant_id', $tenantId)
                    ->whereNotIn('status', ['Discharged', 'Transferred', 'Cancelled']);

                if (!$isAdmin) {
                    $query->where('attending_physician_id', $doctorId);
                }

                $erPatients = $query
                    ->when($this->search, fn ($q) => $q->whereHas('patient', fn ($p) => $p->where('last_name', 'like', '%' . $this->search . '%')->orWhere('first_name', 'like', '%' . $this->search . '%')->orWhere('patient_code', 'like', '%' . $this->search . '%')))
                    ->orderBy('admission_datetime', 'desc')
                    ->paginate(10);
            }

            if ($this->activeTab === 'ward') {
                $query = Admission::with(['patient', 'wardRoom'])
                    ->where('tenant_id', $tenantId)
                    ->whereNotIn('status', ['Discharged', 'Cancelled']);

                if (!$isAdmin) {
                    $query->where(function ($q) use ($doctorId) {
                        $q->where('attending_physician_id', $doctorId)
                          ->orWhere('admitting_physician_id', $doctorId);
                    });
                }

                $wardPatients = $query
                    ->when($this->search, fn ($q) => $q->whereHas('patient', fn ($p) => $p->where('last_name', 'like', '%' . $this->search . '%')->orWhere('first_name', 'like', '%' . $this->search . '%')->orWhere('patient_code', 'like', '%' . $this->search . '%')))
                    ->orderBy('admission_date', 'desc')
                    ->paginate(10);
            }
        }

        return view('livewire.doctors.dashboard-crud', [
            'opdPatients' => $opdPatients,
            'erPatients' => $erPatients,
            'wardPatients' => $wardPatients,
            'doctorId' => $doctorId,
            'isAdmin' => $isAdmin,
        ])->layout('layouts.app');
    }
}
