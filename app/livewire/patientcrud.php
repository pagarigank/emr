<?php

namespace App\Livewire;

use App\Models\Patient;
use App\Models\Gender;
use App\Models\CivilStatus;
use App\Models\Religion;
use App\Models\Nationality;
use App\Models\Occupation;
use App\Models\BloodType;
use App\Models\Relationship;
use App\Models\Country;
use App\Models\Region;
use App\Models\ProvinceCity;
use App\Models\Barangay;
use App\Models\PhilhealthClaim;
use App\Models\PhilhealthMember;
use App\Models\HealthCareProfessional;
use Livewire\Component;
use Livewire\WithPagination;

class PatientCrud extends Component
{
    use WithPagination;

    public string $search = '', $sortField = 'patient_code', $sortDirection = 'asc';
    public int $perPage = 10;
    public bool $showModal = false, $showDeleteModal = false, $isEditing = false;
    public ?int $editingId = null;

    // Form fields
    public string $patient_code = '', $last_name = '', $first_name = '', $middle_name = '', $suffix = '', $date_of_birth = '';
    public ?int $philhealth_member_id = null, $gender_id = null, $civil_status_id = null, $religion_id = null, $nationality_id = null, $occupation_id = null, $blood_type_id = null;
    public bool $is_philhealth_member = false;
    public string $contact_no = '', $email = '', $address_street = '', $zip_code = '';
    public ?int $barangay_id = null, $city_id = null, $province_id = null, $region_id = null, $country_id = null;
    public string $emergency_contact_name = '', $emergency_contact_no = '', $emergency_relationship = '';
    
    // New EMR standard fields
    public string $maiden_name = '', $place_of_birth = '', $preferred_language = '';
    public string $national_id_no = '', $passport_no = '';
    public string $father_name = '', $mother_maiden_name = '', $spouse_name = '';
    public bool $multiple_birth_indicator = false, $is_deceased = false;
    public string $date_of_death = '';
    public ?int $primary_care_provider_id = null;
    
    public bool $is_active = true;

    // Cascading dropdown lists
    public array $regions = [], $provinces = [], $cities = [], $barangays = [];

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    protected function rules(): array
    {
        $tid = $this->currentTenantId();
        $u = "unique:patients,patient_code,NULL,id,tenant_id,{$tid},history,1";
        if ($this->isEditing && $this->editingId) $u = "unique:patients,patient_code,{$this->editingId},id,tenant_id,{$tid},history,1";
        return [
            'patient_code' => ['required', 'string', 'max:20', $u],
            'last_name' => ['required', 'string', 'max:60'],
            'first_name' => ['required', 'string', 'max:60'],
            'middle_name' => ['nullable', 'string', 'max:60'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'philhealth_member_id' => ['nullable', 'integer', 'exists:philhealth_members,id'],
            'gender_id' => ['nullable', 'integer', 'exists:genders,id'],
            'civil_status_id' => ['nullable', 'integer', 'exists:civil_statuses,id'],
            'religion_id' => ['nullable', 'integer', 'exists:religions,id'],
            'nationality_id' => ['nullable', 'integer', 'exists:nationalities,id'],
            'occupation_id' => ['nullable', 'integer', 'exists:occupations,id'],
            'blood_type_id' => ['nullable', 'integer', 'exists:blood_types,id'],
            'contact_no' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'barangay_id' => ['nullable', 'integer', 'exists:barangays,id'],
            'city_id' => ['nullable', 'integer', 'exists:provinces_cities,id'],
            'province_id' => ['nullable', 'integer', 'exists:provinces_cities,id'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'emergency_contact_name' => ['nullable', 'string', 'max:120'],
            'emergency_contact_no' => ['nullable', 'string', 'max:30'],
            'emergency_relationship' => ['nullable', 'string', 'max:60'],
            'maiden_name' => ['nullable', 'string', 'max:60'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'preferred_language' => ['nullable', 'string', 'max:60'],
            'national_id_no' => ['nullable', 'string', 'max:30'],
            'passport_no' => ['nullable', 'string', 'max:30'],
            'father_name' => ['nullable', 'string', 'max:120'],
            'mother_maiden_name' => ['nullable', 'string', 'max:120'],
            'spouse_name' => ['nullable', 'string', 'max:120'],
            'multiple_birth_indicator' => ['boolean'],
            'is_deceased' => ['boolean'],
            'date_of_death' => ['nullable', 'date'],
            'primary_care_provider_id' => ['nullable', 'integer', 'exists:health_care_professionals,id'],
            'is_active' => ['boolean'],
        ];
    }

    protected array $validationAttributes = [
        'patient_code' => 'Patient Code', 'last_name' => 'Last Name', 'first_name' => 'First Name',
        'middle_name' => 'Middle Name', 'suffix' => 'Suffix', 'date_of_birth' => 'Date of Birth',
        'philhealth_member_id' => 'PhilHealth Member', 'gender_id' => 'Gender', 'civil_status_id' => 'Civil Status',
        'religion_id' => 'Religion', 'nationality_id' => 'Nationality', 'occupation_id' => 'Occupation',
        'blood_type_id' => 'Blood Type', 'contact_no' => 'Contact No', 'email' => 'Email',
        'address_street' => 'Street Address', 'barangay_id' => 'Barangay', 'city_id' => 'City/Municipality',
        'province_id' => 'Province/State', 'region_id' => 'Region', 'country_id' => 'Country',
        'zip_code' => 'Zip Code', 'emergency_contact_name' => 'Emergency Contact Name',
        'emergency_contact_no' => 'Emergency Contact No', 'emergency_relationship' => 'Emergency Relationship',
        'maiden_name' => 'Maiden Name', 'place_of_birth' => 'Place of Birth', 'preferred_language' => 'Preferred Language',
        'national_id_no' => 'National ID No', 'passport_no' => 'Passport No',
        'father_name' => 'Father Name', 'mother_maiden_name' => 'Mother Maiden Name', 'spouse_name' => 'Spouse Name',
        'multiple_birth_indicator' => 'Multiple Birth Indicator', 'is_deceased' => 'Deceased',
        'date_of_death' => 'Date of Death', 'primary_care_provider_id' => 'Primary Care Provider',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortField = $field; $this->sortDirection = 'asc'; }
        $this->resetPage();
    }

    public function generatePatientCode(): string
    {
        $tid = $this->currentTenantId();
        $year = now()->format('Y');
        $last = Patient::query()->withoutGlobalScope('current')
            ->where('tenant_id', $tid)
            ->where('patient_code', 'like', $year . '%')
            ->orderBy('id', 'desc')
            ->first();
        $next = $last ? ((int) substr($last->patient_code, 4)) + 1 : 1;
        return $year . str_pad($next, 10, '0', STR_PAD_LEFT);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->patient_code = $this->generatePatientCode();
        $this->isEditing = false;
        $this->is_philhealth_member = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $r = Patient::with(['gender', 'civilStatus', 'religion', 'nationality', 'occupation', 'bloodType', 'country', 'region', 'province', 'city', 'barangay'])->findOrFail($id);
        $this->editingId = $r->id;
        $this->patient_code = $r->patient_code;
        $this->philhealth_member_id = $r->philhealth_member_id;
        $this->is_philhealth_member = (bool) $r->is_philhealth_member;
        $this->last_name = $r->last_name;
        $this->first_name = $r->first_name;
        $this->middle_name = $r->middle_name ?? '';
        $this->suffix = $r->suffix ?? '';
        $this->date_of_birth = $r->date_of_birth?->format('Y-m-d') ?? '';
        $this->gender_id = $r->gender_id;
        $this->civil_status_id = $r->civil_status_id;
        $this->religion_id = $r->religion_id;
        $this->nationality_id = $r->nationality_id;
        $this->occupation_id = $r->occupation_id;
        $this->blood_type_id = $r->blood_type_id;
        $this->contact_no = $r->contact_no ?? '';
        $this->email = $r->email ?? '';
        $this->address_street = $r->address_street ?? '';
        $this->barangay_id = $r->barangay_id;
        $this->city_id = $r->city_id;
        $this->province_id = $r->province_id;
        $this->region_id = $r->region_id;
        $this->country_id = $r->country_id;
        $this->zip_code = $r->zip_code ?? '';
        $this->emergency_contact_name = $r->emergency_contact_name ?? '';
        $this->emergency_contact_no = $r->emergency_contact_no ?? '';
        $this->emergency_relationship = $r->emergency_relationship ?? '';
        $this->maiden_name = $r->maiden_name ?? '';
        $this->place_of_birth = $r->place_of_birth ?? '';
        $this->preferred_language = $r->preferred_language ?? '';
        $this->national_id_no = $r->national_id_no ?? '';
        $this->passport_no = $r->passport_no ?? '';
        $this->father_name = $r->father_name ?? '';
        $this->mother_maiden_name = $r->mother_maiden_name ?? '';
        $this->spouse_name = $r->spouse_name ?? '';
        $this->multiple_birth_indicator = $r->multiple_birth_indicator;
        $this->is_deceased = $r->is_deceased;
        $this->date_of_death = $r->date_of_death?->format('Y-m-d') ?? '';
        $this->primary_care_provider_id = $r->primary_care_provider_id;
        $this->is_active = $r->is_active;
        $this->loadCascadingForEdit($r);
        $this->isEditing = true;
        $this->showModal = true;
    }

    private function loadCascadingForEdit($r): void
    {
        if ($r->country_id) $this->regions = Region::where('country_id', $r->country_id)->orderBy('name')->get()->toArray();
        if ($r->region_id) $this->provinces = ProvinceCity::where('region_id', $r->region_id)->where('type', 'province')->orderBy('name')->get()->toArray();
        if ($r->province_id) $this->cities = ProvinceCity::where('parent_id', $r->province_id)->where('type', 'city')->orderBy('name')->get()->toArray();
        if ($r->city_id) $this->barangays = Barangay::where('province_city_id', $r->city_id)->orderBy('name')->get()->toArray();
    }

    public function updatedCountryId($value): void
    {
        $this->reset('region_id', 'province_id', 'city_id', 'barangay_id', 'regions', 'provinces', 'cities', 'barangays');
        if ($value) $this->regions = Region::where('country_id', $value)->orderBy('name')->get()->toArray();
    }

    public function updatedRegionId($value): void
    {
        $this->reset('province_id', 'city_id', 'barangay_id', 'provinces', 'cities', 'barangays');
        if ($value) {
            $this->provinces = ProvinceCity::where('region_id', $value)->where('type', 'province')->orderBy('name')->get()->toArray();
        }
    }

    public function updatedProvinceId($value): void
    {
        $this->reset('city_id', 'barangay_id', 'cities', 'barangays');
        if ($value) {
            $this->cities = ProvinceCity::where('parent_id', $value)->where('type', 'city')->orderBy('name')->get()->toArray();
        }
    }

    public function updatedCityId($value): void
    {
        $this->reset('barangay_id', 'barangays');
        if ($value) $this->barangays = Barangay::where('province_city_id', $value)->orderBy('name')->get()->toArray();
    }

    public function save(): void
    {
        $data = $this->validate();
        $tid = $this->currentTenantId();
        $uid = $this->currentUserId();
        if ($this->isEditing) {
            Patient::findOrFail($this->editingId)->update(array_merge($data, ['updated_by' => $uid, 'updated_date' => now()]));
            $this->dispatch('toast', type: 'success', message: 'Patient updated successfully.');
        } else {
            $patient = Patient::create(array_merge($data, [
                'tenant_id' => $tid,
                'history' => 1,
                'created_by' => $uid,
                'created_date' => now(),
                'updated_by' => $uid,
                'updated_date' => now(),
            ]));
            if ($this->is_philhealth_member) {
                PhilhealthClaim::ensureDraftForPatient($patient, $tid, $uid);
            }
            $this->dispatch('toast', type: 'success', message: 'Patient registered successfully.');
        }
        $this->closeModal();
    }

    public function confirmDelete(int $id): void { $this->editingId = $id; $this->showDeleteModal = true; }

    public function delete(): void
    {
        Patient::findOrFail($this->editingId)->update(['history' => 0, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        $this->closeModal();
        $this->dispatch('toast', type: 'success', message: 'Patient archived successfully.');
    }

    public function closeModal(): void { $this->showModal = false; $this->showDeleteModal = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'patient_code', 'philhealth_member_id', 'is_philhealth_member', 'last_name', 'first_name', 'middle_name', 'suffix',
            'date_of_birth', 'gender_id', 'civil_status_id', 'religion_id', 'nationality_id', 'occupation_id',
            'blood_type_id', 'contact_no', 'email', 'address_street', 'barangay_id', 'city_id', 'province_id',
            'region_id', 'country_id', 'zip_code', 'emergency_contact_name', 'emergency_contact_no',
            'emergency_relationship', 'regions', 'provinces', 'cities', 'barangays',
            'maiden_name', 'place_of_birth', 'preferred_language', 'national_id_no', 'passport_no',
            'father_name', 'mother_maiden_name', 'spouse_name', 'date_of_death', 'primary_care_provider_id'
        ]);
        $this->multiple_birth_indicator = false;
        $this->is_deceased = false;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $items = Patient::query()
            ->with(['gender', 'civilStatus'])
            ->when($this->search, fn ($q) => $q->where(fn ($i) => $i
                ->where('patient_code', 'like', '%' . $this->search . '%')
                ->orWhere('last_name', 'like', '%' . $this->search . '%')
                ->orWhere('first_name', 'like', '%' . $this->search . '%')
                ->orWhere('contact_no', 'like', '%' . $this->search . '%')
            ))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $genders = Gender::orderBy('name')->get();
        $civilStatuses = CivilStatus::orderBy('name')->get();
        $religions = Religion::orderBy('name')->get();
        $nationalities = Nationality::orderBy('name')->get();
        $occupations = Occupation::orderBy('name')->get();
        $bloodTypes = BloodType::orderBy('name')->get();
        $countries = Country::orderBy('name')->get();
        $philhealthMembers = PhilhealthMember::orderBy('last_name')->get(['id', 'pin', 'last_name', 'first_name']);
        $pcps = HealthCareProfessional::orderBy('full_name')->get(['id', 'full_name']);

        return view('livewire.patient-crud', compact(
            'items', 'genders', 'civilStatuses', 'religions', 'nationalities',
            'occupations', 'bloodTypes', 'countries', 'philhealthMembers', 'pcps'
        ));
    }
}
