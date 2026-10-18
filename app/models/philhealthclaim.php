<?php

namespace App\Models;

use App\Models\Delivery\DeliveryType;
use App\Models\Er\ErAdmission;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhilhealthClaim extends Model
{
    use ScopedByTenant;
    protected $table = 'philhealth_claims';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'date_admitted' => 'date',
        'date_discharged' => 'date',
        'total_actual_amount' => 'decimal:2',
        'total_claimed_amount' => 'decimal:2',
        'voucher_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
        'ecs_uploaded_at' => 'datetime',
        'cloud_uploaded_at' => 'datetime',
        'acknowledgment_date' => 'datetime',
        'voucher_date' => 'datetime',
        'lmp_date' => 'date',
        'edc_date' => 'date',
        'patient_signature_date' => 'datetime',
        'physician_signature_date' => 'datetime',
        'hci_signature_date' => 'datetime',
        'delivery_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function philhealthMember(): BelongsTo
    {
        return $this->belongsTo(PhilhealthMember::class, 'philhealth_member_id');
    }

    public function icd10Code(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'icd10_code_id');
    }

    public function caseRate(): BelongsTo
    {
        return $this->belongsTo(PhilhealthCaseRate::class, 'case_rate_id');
    }

    public function claimBatch(): BelongsTo
    {
        return $this->belongsTo(ClaimBatch::class, 'claim_batch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ClaimItem::class, 'philhealth_claim_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ClaimDocument::class, 'philhealth_claim_id');
    }

    public function rejections(): HasMany
    {
        return $this->hasMany(ClaimRejection::class, 'philhealth_claim_id');
    }

    public function statusPolls(): HasMany
    {
        return $this->hasMany(ClaimStatusPoll::class, 'philhealth_claim_id');
    }

    public function cf5ShadowBilling(): HasMany
    {
        return $this->hasMany(Cf5ShadowBilling::class, 'philhealth_claim_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id');
    }

    public function attendingPhysician(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'attending_physician_id');
    }

    public function referringPhysician(): BelongsTo
    {
        return $this->belongsTo(HealthCareProfessional::class, 'referring_physician_id');
    }

    public function secondaryIcd10Code(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'secondary_icd10_code_id');
    }

    public function tertiaryIcd10Code(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'tertiary_icd10_code_id');
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(DeliveryType::class, 'delivery_type_id');
    }

    public function claimItems(): HasMany
    {
        return $this->hasMany(ClaimItem::class, 'philhealth_claim_id');
    }

    public function zBenefits(): HasMany
    {
        return $this->hasMany(PhilhealthZBenefit::class, 'philhealth_claim_id');
    }

    public function erAdmission(): BelongsTo
    {
        return $this->belongsTo(ErAdmission::class, 'er_admission_id');
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admission::class, 'admission_id');
    }

    public function secondCaseRate(): BelongsTo
    {
        return $this->belongsTo(PhilhealthCaseRate::class, 'second_case_rate_id');
    }

    public function claimProcedures(): HasMany
    {
        return $this->hasMany(ClaimProcedure::class, 'philhealth_claim_id');
    }

    public static function ensureDraftForPatient(Patient $patient, int $tenantId, int $userId): ?self
    {
        // Allow draft creation based on is_philhealth_member flag alone — PIN is filled later from eClaims module

        if (self::withoutGlobalScope('current')->where('tenant_id', $tenantId)->where('patient_id', $patient->id)->where('status', 'Draft')->exists()) {
            return null;
        }

        $prefix = 'PHC-';
        $last = self::withoutGlobalScope('current')->where('tenant_id', $tenantId)->where('claim_code', 'like', $prefix . '%')->orderBy('id', 'desc')->value('claim_code');
        $num = $last ? (int) substr($last, 4) + 1 : 1;
        $code = $prefix . str_pad((string) $num, 5, '0', STR_PAD_LEFT);

        return self::create([
            'tenant_id' => $tenantId,
            'claim_code' => $code,
            'patient_id' => $patient->id,
            'philhealth_member_id' => $patient->philhealth_member_id,
            'status' => 'Draft',
            'claim_type' => 'ACR',
            'is_active' => true,
            'history' => 1,
            'created_by' => $userId,
            'created_date' => now(),
            'updated_by' => $userId,
            'updated_date' => now(),
        ]);
    }

    public function scopeForBatch($q, ClaimBatch $batch)
    {
        return $q->where('claim_batch_id', $batch->id);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
