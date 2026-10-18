<div>
    {{-- ── Page header ── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-amber-800">Role Management</h2>
            <p class="text-sm text-gray-500 mt-1">Create, edit, and manage system roles and their permissions</p>
        </div>
        <button wire:click="openCreate"
                class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Role
        </button>
    </div>

    {{-- ── Filters ── --}}
    <div class="mb-6 p-4 bg-gray-50/80 border border-gray-200 rounded-xl">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Role name, display name…"
                       class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Rows</label>
                <select wire:model.live="perPage"
                        class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ── Datagrid ── --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none" wire:click="sortBy('name')">
                        <div class="flex items-center space-x-1">
                            <span>Role Key</span>
                            @if($sortField === 'name')
                                <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none" wire:click="sortBy('display_name')">
                        <div class="flex items-center space-x-1">
                            <span>Display Name</span>
                            @if($sortField === 'display_name')
                                <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($roles as $role)
                    <tr class="hover:bg-amber-50/30 transition-colors">
                        <td class="px-4 py-3 text-sm">
                            <span class="font-mono font-medium text-gray-900">{{ $role->name }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 font-medium">{{ $role->display_name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 max-w-xs truncate">{{ $role->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($role->is_system)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">System</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Custom</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($role->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200/50">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button wire:click="openEdit({{ $role->id }})"
                                        class="p-1.5 text-amber-600 hover:bg-amber-100 rounded-md transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                @unless($role->is_system)
                                    <button wire:click="confirmDelete({{ $role->id }})"
                                            class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <svg class="mx-auto w-10 h-10 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <p class="text-gray-400 text-sm">No roles found.</p>
                            @if($search)
                                <p class="text-gray-400 text-xs mt-1">Try adjusting your search.</p>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    <div class="mt-4">
        {{ $roles->links() }}
    </div>

    {{-- ── Create / Edit Modal ── --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden transform transition-all">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-amber-50/50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $isEditing ? 'Edit Role' : 'Add New Role' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Tabs --}}
                <div class="flex border-b border-gray-200 px-6">
                    <button wire:click="$set('activeTab', 'details')"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors
                                   {{ $activeTab === 'details' ? 'border-amber-600 text-amber-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Details
                    </button>
                    <button wire:click="$set('activeTab', 'permissions')"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors
                                   {{ $activeTab === 'permissions' ? 'border-amber-600 text-amber-700' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Permissions
                    </button>
                </div>

                <form wire:submit="save" class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                    @if($activeTab === 'details')
                        <div class="space-y-4">
                            {{-- Name --}}
                            <div>
                                <label for="role-name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Role Key <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="name" id="role-name" type="text" maxlength="40"
                                       placeholder="e.g. billing_staff"
                                       {{ $editingSystemRole ? 'disabled' : '' }}
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500
                                              @error('name') border-red-300 bg-red-50 @enderror
                                              {{ $editingSystemRole ? 'opacity-60 cursor-not-allowed' : '' }}">
                                @error('name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                @if($editingSystemRole)
                                    <p class="mt-1 text-xs text-amber-600">System role key cannot be changed.</p>
                                @endif
                            </div>

                            {{-- Display Name --}}
                            <div>
                                <label for="role-display-name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Display Name <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="display_name" id="role-display-name" type="text" maxlength="100"
                                       placeholder="e.g. Billing Staff"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500
                                              @error('display_name') border-red-300 bg-red-50 @enderror">
                                @error('display_name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="role-description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                                <textarea wire:model="description" id="role-description" rows="3" maxlength="500"
                                          placeholder="Optional description of this role's purpose"
                                          class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500
                                                 @error('description') border-red-300 bg-red-50 @enderror"></textarea>
                                @error('description') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            {{-- Is Active --}}
                            <div class="flex items-center space-x-3 pt-2">
                                <button type="button" wire:click="$toggle('is_active')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400
                                               {{ $is_active ? 'bg-amber-500' : 'bg-gray-300' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                                 {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                                <span class="text-sm text-gray-700 font-medium">
                                    {{ $is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    @else
                        {{-- Permissions Tab --}}
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-600">
                                    Toggle permissions for <strong>{{ $display_name ?: 'this role' }}</strong>
                                </p>
                                <div class="space-x-2">
                                    <button type="button" wire:click="$set('permissionCheckboxes', {{ json_encode(array_combine(array_keys($permissionCheckboxes), array_fill(0, count($permissionCheckboxes), true))) }})"
                                            class="px-3 py-1.5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-md hover:bg-amber-100 transition-colors">
                                        Select All
                                    </button>
                                    <button type="button" wire:click="$set('permissionCheckboxes', {{ json_encode(array_combine(array_keys($permissionCheckboxes), array_fill(0, count($permissionCheckboxes), false))) }})"
                                            class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-md hover:bg-gray-100 transition-colors">
                                        Deselect All
                                    </button>
                                </div>
                            </div>

                            @forelse($groupedPermissions as $module => $permissions)
                                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                                    <div class="px-5 py-3 bg-gray-50/80 border-b border-gray-200">
                                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">{{ $module }}</h3>
                                    </div>
                                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($permissions as $perm)
                                            <label class="flex items-start space-x-3 p-2.5 rounded-lg hover:bg-amber-50/60 transition-colors cursor-pointer group">
                                                <div class="flex items-center h-5 mt-0.5">
                                                    <input type="checkbox"
                                                           wire:model="permissionCheckboxes.{{ $perm->id }}"
                                                           value="{{ $perm->id }}"
                                                           class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 transition-colors">
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-medium text-gray-800 group-hover:text-amber-700 transition-colors">{{ $perm->display_name }}</span>
                                                    <span class="text-xs text-gray-400 font-mono">{{ $perm->name }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 text-gray-400">
                                    No permissions found.
                                </div>
                            @endforelse
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex justify-end space-x-3 pt-4 mt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500/50">
                            <span wire:loading.remove wire:target="save">
                                {{ $isEditing ? 'Update Role' : 'Save Role' }}
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                Saving…
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
