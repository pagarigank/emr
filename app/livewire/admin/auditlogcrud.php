<?php

namespace App\Livewire\Admin;

use App\Models\PermissionAuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogCrud extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'changed_date';
    public string $sortDirection = 'desc';
    public int $perPage = 25;

    public string $filterAction = '';
    public ?string $filterDateFrom = null;
    public ?string $filterDateTo = null;
    public string $filterEntityType = '';
    public string $filterRole = '';
    public ?int $filterUserId = null;

    public array $actions = [
        '' => 'All Actions',
        'created' => 'Created',
        'updated' => 'Updated',
        'archived' => 'Archived',
        'assigned' => 'Assigned',
        'revoked' => 'Revoked',
        'override_allowed' => 'Override Allowed',
        'override_denied' => 'Override Denied',
        'override_removed' => 'Override Removed',
    ];

    public array $entityTypes = [
        '' => 'All Types',
        'permission' => 'Permission',
        'role_permission' => 'Role Permission',
        'user_permission' => 'User Permission',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['filterAction', 'filterDateFrom', 'filterDateTo', 'filterEntityType', 'filterRole', 'filterUserId', 'search']);
        $this->resetPage();
    }

    public function render()
    {
        $query = PermissionAuditLog::query()
            ->with(['changedBy', 'permission', 'affectedUser'])
            ->where('history', 1);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('permission_name', 'like', '%' . $this->search . '%')
                  ->orWhere('role', 'like', '%' . $this->search . '%')
                  ->orWhere('action', 'like', '%' . $this->search . '%')
                  ->orWhere('entity_type', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterAction) {
            $query->where('action', $this->filterAction);
        }

        if ($this->filterDateFrom) {
            $query->where('changed_date', '>=', $this->filterDateFrom . ' 00:00:00');
        }

        if ($this->filterDateTo) {
            $query->where('changed_date', '<=', $this->filterDateTo . ' 23:59:59');
        }

        if ($this->filterEntityType) {
            $query->where('entity_type', $this->filterEntityType);
        }

        if ($this->filterRole) {
            $query->where('role', $this->filterRole);
        }

        if ($this->filterUserId) {
            $query->where('user_id', $this->filterUserId);
        }

        $logs = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.audit-log-crud', compact('logs'));
    }
}
