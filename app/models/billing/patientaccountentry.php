<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAccountEntry extends Model
{
    use ScopedByTenant;
    protected $table = 'patient_account_entries';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'debit'        => 'decimal:2',
        'credit'       => 'decimal:2',
        'balance'      => 'decimal:2',
        'is_active'    => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    // ── Relationships ───────────────────────────────────────────────────────

    public function patient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Patient::class, 'patient_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'billing_invoice_id');
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id');
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admission::class, 'admission_id');
    }

    public function erAdmission(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Er\ErAdmission::class, 'er_admission_id');
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeCharges(Builder $query): Builder
    {
        return $query->where('entry_type', 'charge');
    }

    public function scopePayments(Builder $query): Builder
    {
        return $query->where('entry_type', 'payment');
    }

    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeForEncounter(Builder $query, string $column, int $id): Builder
    {
        return $query->where($column, $id);
    }

    public function scopeUnbilled(Builder $query): Builder
    {
        return $query->whereNull('billing_invoice_id');
    }

    // ── Static helpers ──────────────────────────────────────────────────────

    /**
     * Post a charge entry to the patient's account.
     */
    public static function postCharge(array $data): self
    {
        $data['entry_type'] = 'charge';
        $data['debit']      = $data['debit'] ?? $data['total_price'] ?? 0;
        $data['credit']     = 0;

        $entry = static::create($data);
        $entry->recalculateBalance();

        return $entry;
    }

    /**
     * Post a payment entry to the patient's account.
     */
    public static function postPayment(array $data): self
    {
        $data['entry_type'] = 'payment';
        $data['debit']      = 0;
        $data['credit']     = $data['credit'] ?? $data['amount'] ?? 0;

        $entry = static::create($data);
        $entry->recalculateBalance();

        return $entry;
    }

    /**
     * Post an adjustment (discount, write-off, correction).
     */
    public static function postAdjustment(array $data): self
    {
        $data['entry_type'] = $data['entry_type'] ?? 'adjustment';
        $data['debit']      = $data['debit'] ?? 0;
        $data['credit']     = $data['credit'] ?? 0;

        $entry = static::create($data);
        $entry->recalculateBalance();

        return $entry;
    }

    /**
     * Recalculate and update the running balance for this entry and all subsequent entries.
     */
    public function recalculateBalance(): void
    {
        $previousBalance = static::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant_id)
            ->where('patient_id', $this->patient_id)
            ->where('id', '<', $this->id)
            ->where('history', 1)
            ->orderBy('id', 'desc')
            ->value('balance') ?? 0;

        $newBalance = $previousBalance + $this->debit - $this->credit;

        $this->update(['balance' => max(0, $newBalance)]);

        // Update all subsequent active entries' balances
        $subsequentEntries = static::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant_id)
            ->where('patient_id', $this->patient_id)
            ->where('id', '>', $this->id)
            ->where('history', 1)
            ->orderBy('id')
            ->get();

        $runningBalance = $this->balance;
        foreach ($subsequentEntries as $entry) {
            $runningBalance = $runningBalance + $entry->debit - $entry->credit;
            $entry->update(['balance' => max(0, $runningBalance)]);
        }
    }

    /**
     * Get the current balance for a patient.
     */
    public static function getPatientBalance(int $tenantId, int $patientId): float
    {
        $lastEntry = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('patient_id', $patientId)
            ->where('history', 1)
            ->orderBy('id', 'desc')
            ->first();

        return $lastEntry ? (float) $lastEntry->balance : 0;
    }

    /**
     * Get total charges for a patient (optionally scoped to encounter).
     */
    public static function getTotalCharges(int $tenantId, int $patientId, ?string $encounterColumn = null, ?int $encounterId = null): float
    {
        $query = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('patient_id', $patientId)
            ->charges();

        if ($encounterColumn && $encounterId) {
            $query->where($encounterColumn, $encounterId);
        }

        return (float) $query->sum('debit');
    }

    /**
     * Get total payments for a patient.
     */
    public static function getTotalPayments(int $tenantId, int $patientId): float
    {
        return (float) static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('patient_id', $patientId)
            ->payments()
            ->sum('credit');
    }

    /**
     * Get all ledger entries for a patient, optionally scoped to an encounter.
     */
    public static function getLedger(int $tenantId, int $patientId, ?string $encounterColumn = null, ?int $encounterId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('patient_id', $patientId)
            ->where('history', 1)
            ->orderBy('created_date')
            ->orderBy('id');

        if ($encounterColumn && $encounterId) {
            $query->where($encounterColumn, $encounterId);
        }

        return $query->get();
    }
}
