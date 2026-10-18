<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingInvoiceItem extends Model
{
    protected $table = 'billing_invoice_items';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(BillingInvoice::class, 'billing_invoice_id');
    }
}
