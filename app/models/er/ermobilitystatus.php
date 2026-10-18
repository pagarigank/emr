<?php

namespace App\Models\ER;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ErMobilityStatus extends Model
{
    protected $table = 'er_mobility_statuses';
    public $timestamps = false;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('history', 1);
        });
    }
}
