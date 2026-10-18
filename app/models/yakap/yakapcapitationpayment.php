<?php

namespace App\Models\Yakap;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YakapCapitationPayment extends Model
{
    use ScopedByTenant;
    protected $table = 'yakap_capitation_payments';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function empanelment(): BelongsTo
    {
        return $this->belongsTo(YakapEmpanelment::class, 'empanelment_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn ($q) => $q->where('history', 1));
    }
}
