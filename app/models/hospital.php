<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\ScopedByTenant;

class Hospital extends Model
{
    use ScopedByTenant;
    protected $table = 'hospitals';
    public $timestamps = false;
    protected $guarded = ['id'];
}
