<?php

namespace App\Models\Oecb;

use App\Models\Er\ErAdmission;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OecbEntry extends Model
{
    use ScopedByTenant;
    protected $table = 'oecb_entries';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'oecb_fixed_fee' => 'decimal:2',
        'quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'history' => 'boolean',
    ];

    public function erAdmission(): BelongsTo
    {
        return $this->belongsTo(ErAdmission::class, 'er_admission_id');
    }

    public function eeclItem(): BelongsTo
    {
        return $this->belongsTo(EeclItem::class, 'eecl_item_id');
    }

    public static function postChargeToLedger(self $entry, float $amount, int $userId): void
    {
        if ($amount <= 0) return;
        if (!$entry->erAdmission || !$entry->erAdmission->patient_id) return;

        $adm = $entry->erAdmission;
        $now = now();

        \App\Models\Billing\PatientAccountEntry::postCharge([
            'tenant_id'           => $entry->tenant_id,
            'patient_id'          => $adm->patient_id,
            'admission_id'        => $adm->admission_id,
            'er_admission_id'     => $adm->id,
            'opd_consultation_id' => null,
            'or_schedule_id'      => null,
            'dr_schedule_id'      => null,
            'ob_consultation_id'  => null,
            'dialysis_session_id' => null,
            'entry_type'          => 'charge',
            'source_type'         => 'OECB',
            'source_id'           => $entry->id,
            'source_code'         => $entry->oecb_package_code ?? $entry->core_presentation,
            'debit'               => $amount,
            'credit'              => 0,
            'description'         => 'OECB: ' . ($entry->core_presentation ?? $entry->oecb_package_code),
            'billing_invoice_id'  => null,
            'created_by'          => $userId,
            'created_date'        => $now,
            'updated_by'          => $userId,
            'updated_date'        => $now,
        ]);
    }
}
