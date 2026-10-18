<div>
    {{-- ── Page header ── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-amber-800">Permission Audit Trail</h2>
            <p class="text-sm text-gray-500 mt-1">View all permission changes across the system</p>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="mb-6 p-4 bg-gray-50/80 border border-gray-200 rounded-xl space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Permission, role, action…"
                       class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Action</label>
                <select wire:model.live="filterAction"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    @foreach($actions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Entity Type</label>
                <select wire:model.live="filterEntityType"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    @foreach($entityTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <input type="text" wire:model.live.debounce.300ms="filterRole" placeholder="e.g. staff, doctor…"
                       class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From Date</label>
                <input type="date" wire:model.live="filterDateFrom"
                       class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To Date</label>
                <input type="date" wire:model.live="filterDateTo"
                       class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            </div>
            <div class="flex items-end">
                <button wire:click="resetFilters"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    {{-- ── Audit Log Table ── --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none" wire:click="sortBy('changed_date')">
                        <div class="flex items-center space-x-1">
                            <span>Date/Time</span>
                            @if($sortField === 'changed_date')
                                <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Entity Type</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Details</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Changed By</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Changes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-amber-50/30 transition-colors" x-data="{ open: false }">
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                            {{ $log->changed_date instanceof \Carbon\Carbon ? $log->changed_date->format('M d, Y h:i A') : $log->changed_date }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $badge = match($log->action) {
                                    'created' => 'bg-green-100 text-green-700',
                                    'updated' => 'bg-blue-100 text-blue-700',
                                    'archived' => 'bg-red-100 text-red-700',
                                    'assigned' => 'bg-indigo-100 text-indigo-700',
                                    'revoked' => 'bg-orange-100 text-orange-700',
                                    'override_allowed' => 'bg-teal-100 text-teal-700',
                                    'override_denied' => 'bg-rose-100 text-rose-700',
                                    'override_removed' => 'bg-gray-100 text-gray-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };
                            @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                {{ $actions[$log->action] ?? ucfirst($log->action) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                            <span class="font-mono text-xs">{{ $log->entity_type }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="flex flex-col">
                                @if($log->permission_name)
                                    <span class="font-medium">{{ $log->permission_name }}</span>
                                @endif
                                @if($log->role)
                                    <span class="text-xs text-gray-500">Role: <span class="font-mono">{{ $log->role }}</span></span>
                                @endif
                                @if($log->affectedUser)
                                    <span class="text-xs text-gray-500">User: {{ $log->affectedUser->full_name ?? '#' . $log->user_id }}</span>
                                @elseif($log->user_id)
                                    <span class="text-xs text-gray-500">User: #{{ $log->user_id }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                            {{ $log->changedBy->full_name ?? '#' . $log->changed_by }}
                            <span class="text-xs text-gray-400 ml-1">#{{ $log->changed_by }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($log->old_value || $log->new_value)
                                <button @click="open = !open" class="text-amber-600 hover:text-amber-800 text-xs font-medium inline-flex items-center transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span x-text="open ? 'Hide' : 'View'">View</span>
                                </button>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <template x-if="open">
                            <tr class="bg-amber-50/50">
                                <td colspan="6" class="px-6 py-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @if($log->old_value)
                                            <div>
                                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Old Value</h4>
                                                <pre class="text-xs bg-white border border-gray-200 rounded-lg p-3 overflow-x-auto max-h-40 text-gray-700 font-mono">{{ is_string($log->old_value) ? $log->old_value : json_encode($log->old_value, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                        @if($log->new_value)
                                            <div>
                                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">New Value</h4>
                                                <pre class="text-xs bg-white border border-gray-200 rounded-lg p-3 overflow-x-auto max-h-40 text-gray-700 font-mono">{{ is_string($log->new_value) ? $log->new_value : json_encode($log->new_value, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <svg class="mx-auto w-10 h-10 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-400 text-sm">No audit log entries found.</p>
                            @if($search || $filterAction || $filterDateFrom || $filterDateTo || $filterEntityType || $filterRole || $filterUserId)
                                <p class="text-gray-400 text-xs mt-1">Try adjusting your filters.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
