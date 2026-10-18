<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;

class FundSource extends Model
{
    use ScopedByTenant;
    protected $table = 'fund_sources';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'budget_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->budget_amount - (float) $this->used_amount);
    }

    public function deductBudget(float $amount): void
    {
        $this->increment('used_amount', $amount);
    }

    public function restoreBudget(float $amount): void
    {
        $newUsed = max(0, (float) $this->used_amount - $amount);
        $this->update(['used_amount' => $newUsed]);
    }
}
