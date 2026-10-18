<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RvsCode extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::addGlobalScope('history', fn ($q) => $q->where('history', 1));
    }
}
