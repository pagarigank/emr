<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'permission_audit_log';

    protected $fillable = [
        'action',
        'entity_type',
        'role',
        'permission_name',
        'old_value',
        'new_value',
        'changed_by',
        'changed_date',
        'history',
    ];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function affectedUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
