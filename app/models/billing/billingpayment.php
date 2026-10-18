<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingPayment extends Model
{
    use ScopedByTenant;
    protected $table = 'billing_payments';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'billing_invoice_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Patient::class, 'patient_id');
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

    public function healthInsuranceProvider(): BelongsTo
    {
        return $this->belongsTo(HealthInsuranceProvider::class, 'health_insurance_provider_id');
    }
}
