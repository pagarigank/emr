<div>
    {{-- ── Page header ── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-light-blue-800">Role Permissions</h2>
            <p class="text-sm text-gray-500 mt-1">Assign permissions to system roles</p>
        </div>
    </div>

    {{-- ── Role Selector ── --}}
    <div class="mb-6">
        <label for="role-select" class="block text-sm font-medium text-gray-700 mb-2">Select Role</label>
        <select wire:model.live="selectedRole" id="role-select"
                class="w-full sm:w-72 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 shadow-sm">
            @foreach($roles as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
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
                        <label class="flex items-start space-x-3 p-2.5 rounded-lg hover:bg-light-blue-50/60 transition-colors cursor-pointer group">
                            <div class="flex items-center h-5 mt-0.5">
                                <input type="checkbox"
                                       wire:model="permissionCheckboxes.{{ $perm->id }}"
                                       value="{{ $perm->id }}"
                                       class="rounded border-gray-300 text-light-blue-600 focus:ring-light-blue-500 transition-colors">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-800 group-hover:text-light-blue-700 transition-colors">{{ $perm->display_name }}</span>
                                <span class="text-xs text-gray-400 font-mono">{{ $perm->name }}</span>
                            </div>
                        </label>
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
    <div class="mt-6 flex justify-end">
        <button wire:click="save" id="btn-save-role-permissions"
                class="inline-flex items-center px-6 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue-400">
            <span wire:loading.remove wire:target="save">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Save Permissions
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
