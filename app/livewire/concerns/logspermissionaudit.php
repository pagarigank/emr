<?php

namespace App\Livewire\Concerns;

use App\Models\PermissionAuditLog;

trait LogsPermissionAudit
{
    private static function auditChangedBy(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    private static function auditTenantId(): ?int
    {
        return request()->attributes->get('tenant_id');
    }

    protected function logAudit(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $role = null,
        ?int $userId = null,
        ?int $permissionId = null,
        ?string $permissionName = null,
        mixed $oldValue = null,
        mixed $newValue = null,
    ): void {
        $log = PermissionAuditLog::create([
            'action'          => $action,
            'entity_type'     => $entityType,
            'role'            => $role,
            'permission_name' => $permissionName,
            'old_value'       => is_null($oldValue) ? null : (is_string($oldValue) ? $oldValue : json_encode($oldValue)),
            'new_value'       => is_null($newValue) ? null : (is_string($newValue) ? $newValue : json_encode($newValue)),
            'changed_by'      => static::auditChangedBy(),
            'changed_date'    => now(),
            'history'         => 1,
        ]);
        $log->entity_id = $entityId;
        $log->user_id = $userId;
        $log->permission_id = $permissionId;
        $log->tenant_id = static::auditTenantId();
        $log->save();
    }
}
