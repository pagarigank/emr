<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use ScopedByTenant;
    protected $table = 'patients';
    public $timestamps = false;
    protected $fillable = [
        'tenant_id',
        'patient_code',
        'philhealth_member_id',
        'is_philhealth_member',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'maiden_name',
        'place_of_birth',
        'date_of_birth',
        'gender_id',
        'civil_status_id',
        'religion_id',
        'nationality_id',
        'occupation_id',
        'blood_type_id',
        'preferred_language',
        'national_id_no',
        'passport_no',
        'contact_no',
        'email',
        'address_street',
        'barangay_id',
        'city_id',
        'province_id',
        'region_id',
        'country_id',
        'zip_code',
        'father_name',
        'mother_maiden_name',
        'spouse_name',
        'multiple_birth_indicator',
        'is_deceased',
        'date_of_death',
        'primary_care_provider_id',
        'emergency_contact_name',
        'emergency_contact_no',
        'emergency_relationship',
        'is_active',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]))) ?: 'Unknown';
    }

    protected $casts = [
        'is_active' => 'boolean',
        'is_philhealth_member' => 'boolean',
        'date_of_birth' => 'date',
        'multiple_birth_indicator' => 'boolean',
        'is_deceased' => 'boolean',
        'date_of_death' => 'date',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Patient $patient) {
            // If a member record is linked, auto-sync PIN and flag membership
            if ($patient->philhealth_member_id) {
                $member = \App\Models\PhilhealthMember::find($patient->philhealth_member_id);
                if ($member) {
                    $patient->philhealth_pin = $member->pin;
                    $patient->is_philhealth_member = true;
                }
            }
        });
    }

    public function gender(): BelongsTo { return $this->belongsTo(Gender::class, 'gender_id'); }
    public function civilStatus(): BelongsTo { return $this->belongsTo(CivilStatus::class, 'civil_status_id'); }
    public function religion(): BelongsTo { return $this->belongsTo(Religion::class, 'religion_id'); }
    public function nationality(): BelongsTo { return $this->belongsTo(Nationality::class, 'nationality_id'); }
    public function occupation(): BelongsTo { return $this->belongsTo(Occupation::class, 'occupation_id'); }
    public function bloodType(): BelongsTo { return $this->belongsTo(BloodType::class, 'blood_type_id'); }
    public function barangay(): BelongsTo { return $this->belongsTo(Barangay::class, 'barangay_id'); }
    public function city(): BelongsTo { return $this->belongsTo(ProvinceCity::class, 'city_id'); }
    public function province(): BelongsTo { return $this->belongsTo(ProvinceCity::class, 'province_id'); }
    public function region(): BelongsTo { return $this->belongsTo(Region::class, 'region_id'); }
    public function country(): BelongsTo { return $this->belongsTo(Country::class, 'country_id'); }
    public function philhealthMember(): BelongsTo { return $this->belongsTo(PhilhealthMember::class, 'philhealth_member_id'); }
    public function primaryCareProvider(): BelongsTo { return $this->belongsTo(HealthCareProfessional::class, 'primary_care_provider_id'); }

    public function billingInvoices(): HasMany
    {
        return $this->hasMany(\App\Models\Billing\BillingInvoice::class, 'patient_id');
    }

    public function opdConsultations(): HasMany
    {
        return $this->hasMany(\App\Models\Opd\OpdConsultation::class, 'patient_id');
    }

    public function erAdmissions(): HasMany
    {
        return $this->hasMany(\App\Models\Er\ErAdmission::class, 'patient_id');
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(\App\Models\Admission::class, 'patient_id');
    }

    public function obConsultations(): HasMany
    {
        return $this->hasMany(\App\Models\Ob\ObConsultation::class, 'patient_id');
    }

    // YAKAP Relationships
    public function yakapEmpanelments(): HasMany
    {
        return $this->hasMany(\App\Models\Yakap\YakapEmpanelment::class, 'patient_id');
    }

    public function yakapFpeRecords(): HasMany
    {
        return $this->hasMany(\App\Models\Yakap\YakapFpeRecord::class, 'patient_id');
    }

    public function yakapScreenings(): HasMany
    {
        return $this->hasMany(\App\Models\Yakap\YakapScreening::class, 'patient_id');
    }

    public function philhealthClaims(): HasMany
    {
        return $this->hasMany(PhilhealthClaim::class, 'patient_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }
}
