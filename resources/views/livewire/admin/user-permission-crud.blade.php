<div>
    {{-- ── Page header ── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-light-blue-800">User Permission Overrides</h2>
            <p class="text-sm text-gray-500 mt-1">
                Override permissions for <strong>{{ $user->full_name }}</strong>
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                    @if($user->role === 'superadmin') bg-purple-100 text-purple-800
                    @elseif($user->role === 'tenant_admin') bg-blue-100 text-blue-800
                    @elseif($user->role === 'doctor') bg-emerald-100 text-emerald-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($user->role) }}
                </span>
            </p>
        </div>
        <a href="/masters/users" wire:navigate
           class="inline-flex items-center px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Users
        </a>
    </div>

    {{-- ── Info Banner ── --}}
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
        <div class="flex items-start space-x-3">
            <svg class="w-5 h-5 mt-0.5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <div>
                <p class="font-medium">How Permission Overrides Work</p>
                <p class="mt-1 text-amber-700">Set <strong>Allow</strong> to grant a permission not in the user's role, or <strong>Deny</strong> to explicitly revoke a role-based permission. <strong>Neutral</strong> means the role-based permission applies. Superadmin users always have full access regardless of overrides.</p>
            </div>
        </div>
    </div>

    {{-- ── Permissions Grid (grouped by module) ── --}}
    <div class="space-y-6">
        @forelse($groupedPermissions as $module => $permissions)
            <div class="rounded-xl border border-gray-200 shadow-sm bg-white overflow-hidden">
                <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">{{ $module }}</h3>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($permissions as $perm)
                        <div class="p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col min-w-0 mr-2">
                                    <span class="text-sm font-medium text-gray-800">{{ $perm->display_name }}</span>
                                    <span class="text-xs text-gray-400 font-mono truncate">{{ $perm->name }}</span>
                                </div>
                                <div class="flex items-center space-x-1 flex-shrink-0">
                                    <button type="button"
                                            wire:click="$set('permissionOverrides.{{ $perm->id }}', 'allow')"
                                            class="px-2 py-1 text-xs font-medium rounded-md transition-colors
                                                   {{ ($permissionOverrides[$perm->id] ?? 'neutral') === 'allow'
                                                       ? 'bg-green-100 text-green-700 ring-1 ring-green-300'
                                                       : 'bg-gray-50 text-gray-500 hover:bg-green-50 hover:text-green-600' }}">
                                        Allow
                                    </button>
                                    <button type="button"
                                            wire:click="$set('permissionOverrides.{{ $perm->id }}', 'neutral')"
                                            class="px-2 py-1 text-xs font-medium rounded-md transition-colors
                                                   {{ ($permissionOverrides[$perm->id] ?? 'neutral') === 'neutral'
                                                       ? 'bg-gray-200 text-gray-700 ring-1 ring-gray-300'
                                                       : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}">
                                        —
                                    </button>
                                    <button type="button"
                                            wire:click="$set('permissionOverrides.{{ $perm->id }}', 'deny')"
                                            class="px-2 py-1 text-xs font-medium rounded-md transition-colors
                                                   {{ ($permissionOverrides[$perm->id] ?? 'neutral') === 'deny'
                                                       ? 'bg-red-100 text-red-700 ring-1 ring-red-300'
                                                       : 'bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-600' }}">
                                        Deny
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-400">
                <svg class="mx-auto w-10 h-10 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                No permissions found.
            </div>
        @endforelse
    </div>

    {{-- ── Save Button ── --}}
    <div class="mt-6 flex justify-end space-x-3">
        <a href="/masters/users" wire:navigate
           class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
            Cancel
        </a>
        <button wire:click="save" id="btn-save-user-permissions"
                class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue-400">
            <span wire:loading.remove wire:target="save">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Save Overrides
            </span>
            <span wire:loading wire:target="save" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Saving…
            </span>
        </button>
    </div>
</div>
