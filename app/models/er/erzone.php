<?php

namespace App\Models\Er;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;

class ErZone extends Model
{
    use ScopedByTenant;
    protected $table = 'er_zones';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('history', 1);
        });
    }
}
