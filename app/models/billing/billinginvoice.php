<?php

namespace App\Models\Billing;

use App\Models\Admission;
use App\Models\Er\ErAdmission;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Billing\BillingManualCharge;

class BillingInvoice extends Model
{
    use ScopedByTenant;
    protected $table = 'billing_invoices';
    public $timestamps = false;
    protected $fillable = [
        'tenant_id',
        'invoice_date',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
        'notes',
        'discount_type',
        'discount_value',
        'discount_amount',
        'is_active',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function getNetAmountAttribute(): float
    {
        return (float) $this->total_amount - (float) $this->discount_amount;
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function erAdmission(): BelongsTo
    {
        return $this->belongsTo(ErAdmission::class, 'er_admission_id');
    }

    public function opdConsultation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Opd\OpdConsultation::class, 'opd_consultation_id');
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function dialysisSession(): BelongsTo
    {
        return $this->belongsTo(\App\Models\RenalCare\DialysisSession::class, 'dialysis_session_id');
    }

    public function orSchedule(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Or\OrSchedule::class, 'or_schedule_id');
    }

    public function obConsultation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Ob\ObConsultation::class, 'ob_consultation_id');
    }

    public function drSchedule(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Delivery\DeliverySchedule::class, 'dr_schedule_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillingInvoiceItem::class, 'billing_invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class, 'billing_invoice_id');
    }

    public function manualCharges(): HasMany
    {
        return $this->hasMany(BillingManualCharge::class, 'billing_invoice_id');
    }
}
