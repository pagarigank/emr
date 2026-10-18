<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Icd10Code extends Model
{
    use HasFactory;

    protected $table = 'icd_10_codes';

    public $timestamps = false; // We use created_date and updated_date

    protected $fillable = [
        'code',
        'description',
        'is_active',
        'history',
        'created_by',
        'created_date',
        'updated_by',
        'updated_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'history' => 'integer',
        'created_date' => 'datetime',
        'updated_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('history', function (Builder $builder) {
            $builder->where('history', 1);
        });

        static::creating(function ($model) {
            $model->created_date = $model->created_date ?: now();
            if (auth()->check()) {
                $model->created_by = $model->created_by ?: auth()->id();
            }
        });

        static::updating(function ($model) {
            $model->updated_date = now();
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
