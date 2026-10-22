<?php

namespace App\Models\RenalCare;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DialysisMachine extends Model
{
    use ScopedByTenant;

    protected $table = 'dialysis_machines';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('current', fn (Builder $q) => $q->where('history', 1));
    }

    public function dialysisSessions(): HasMany
    {
        return $this->hasMany(DialysisSession::class, 'dialysis_machine_id');
    }

    public function activeSession()
    {
        return $this->hasOne(DialysisSession::class, 'dialysis_machine_id')
            ->whereIn('status', ['In Progress', 'Scheduled'])
            ->whereDate('session_date', now()->toDateString())
            ->latest('id');
    }
}
