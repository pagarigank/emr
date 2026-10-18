<?php

namespace App\Models\Er;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;

class ErBed extends Model
{
    use ScopedByTenant;
    protected $table = 'er_beds';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('history', 1);
        });
    }

    public function zone()
    {
        return $this->belongsTo(ErZone::class, 'er_zone_id');
    }
}
