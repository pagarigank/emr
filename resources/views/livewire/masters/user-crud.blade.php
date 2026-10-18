<div>
    {{-- ── Header & Add Button ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">User Management</h2>
            <p class="text-sm text-gray-500 mt-1">Manage system access and roles.</p>
        </div>
        <button wire:click="openCreate"
                class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </button>
    </div>

    {{-- ── Toolbar (search + per-page) ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/>
                </svg>
            </div>
            <input wire:model.live.debounce.300ms="search" id="search-user"
                   type="text" placeholder="Search users…"
                   class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm shadow-sm"/>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" id="per-page-user"
                    class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    {{-- ── Data Grid ── --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    @if($isSuperAdmin)
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Company</th>
                    @endif
                    @foreach([
                        ['username', 'Username'],
                        ['full_name', 'Full Name'],
                        ['email', 'Email'],
                        ['role', 'Role'],
                    ] as [$field, $label])
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none whitespace-nowrap hover:bg-gray-100/50 transition-colors"
                        wire:click="sortBy('{{ $field }}')">
                        <span class="inline-flex items-center gap-1">
                            {{ $label }}
                            <span class="text-gray-300">
                                @if($sortField === $field)
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @else
                                    &uarr;&darr;
                                @endif
                            </span>
                        </span>
                    </th>
                    @endforeach

                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($users as $user)
                    <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                        @if($isSuperAdmin)
                            <td class="px-5 py-4 text-gray-800 font-medium">
                                {{ $user->tenant_id ? ($user->tenant->tenant_name ?? 'Unknown') : 'Global (Superadmin)' }}
                            </td>
                        @endif
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $user->username }}</td>
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $user->full_name }}</td>
                        <td class="px-5 py-4 text-gray-500">{{ $user->email }}</td>
                        <td class="px-5 py-4 text-gray-500">
                            @if($user->role === 'superadmin')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Superadmin</span>
                            @elseif($user->role === 'tenant_admin')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Tenant Admin</span>
                            @elseif($user->role === 'doctor')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Doctor</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Staff</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200/50 shadow-sm">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200 shadow-sm">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="/admin/users/{{ $user->id }}/permissions" wire:navigate
                                   class="p-1.5 text-amber-600 hover:bg-amber-100 rounded-md transition-colors" title="Permission Overrides">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </a>
                                <button wire:click="openEdit({{ $user->id }})"
                                        class="p-1.5 text-light-blue-600 hover:bg-light-blue-100 rounded-md transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $user->id }})"
                                        class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isSuperAdmin ? 7 : 6 }}" class="px-5 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-lg font-medium text-gray-900">No users found</span>
                                <p class="mt-1 text-sm">Try adjusting your search filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- ── Create / Edit Modal ── --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
            
            {{-- Panel --}}
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden transform transition-all">
                
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50/50">
                    <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                        {{ $isEditing ? 'Edit User' : 'Add New User' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form wire:submit="save" class="px-6 py-5 space-y-4 max-h-[80vh] overflow-y-auto">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Username --}}
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="username" id="username" type="text" maxlength="60"
                                   placeholder="jdoe"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                          @error('username') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                            @error('username') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Role --}}
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Role <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="role" id="role"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                           @error('role') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                                @foreach($roles as $r)
                                    @if($r->name === 'superadmin' && !$isSuperAdmin)
                                        @continue
                                    @endif
                                    <option value="{{ $r->name }}">{{ $r->display_name }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Company (Tenant) Assignment --}}
                    @if($isSuperAdmin && $role !== 'superadmin')
                        <div>
                            <label for="tenant_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Company Assignment <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="tenant_id" id="tenant_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                           @error('tenant_id') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                                <option value="">Select a company...</option>
                                @foreach($tenants as $t)
                                    <option value="{{ $t->id }}">{{ $t->tenant_name }}</option>
                                @endforeach
                            </select>
                            @error('tenant_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Doctor Profile Link --}}
                    @if($role === 'doctor')
                        <div>
                            <label for="health_care_professional_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Link to Doctor Profile <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="health_care_professional_id" id="health_care_professional_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                           @error('health_care_professional_id') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                                <option value="">Select Doctor Profile...</option>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}">{{ $d->full_name }}</option>
                                @endforeach
                            </select>
                            @error('health_care_professional_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Full Name --}}
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="full_name" id="full_name" type="text" maxlength="150"
                               placeholder="John Doe"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                      @error('full_name') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                        @error('full_name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="email" id="email" type="email" maxlength="150"
                               placeholder="john@example.com"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                      @error('email') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Password @if(!$isEditing) <span class="text-red-500">*</span> @endif
                        </label>
                        <input wire:model="password" id="password" type="password"
                               placeholder="{{ $isEditing ? 'Leave blank to keep current password' : '••••••••' }}"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                      @error('password') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                        @error('password') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Is Active --}}
                    <div class="flex items-center space-x-3 pt-2">
                        <button type="button" wire:click="$toggle('is_active')" id="toggle-is-active"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue-400
                                       {{ $is_active ? 'bg-light-blue-500' : 'bg-gray-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                         {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                        <span class="text-sm text-gray-700 font-medium">
                            {{ $is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20">
                            Cancel
                        </button>
                        <button type="submit" id="btn-save-user"
                                class="px-5 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue-500/50">
                            <span wire:loading.remove wire:target="save">
                                {{ $isEditing ? 'Update User' : 'Save User' }}
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

    {{-- ── Delete/Archive Confirmation Modal ── --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6 transform transition-all">
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    @if($errors->has('delete'))
                        <div class="mb-4 text-sm font-medium text-red-600 bg-red-50 px-3 py-2 rounded-lg">
                            {{ $errors->first('delete') }}
                        </div>
                    @else
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Archive User</h3>
                        <p class="text-sm text-gray-500 mb-6">
                            Are you sure you want to archive this user? They will no longer be able to log in.
                        </p>
                    @endif
                    <div class="flex space-x-3 w-full">
                        <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                            Cancel
                        </button>
                        @if(!$errors->has('delete'))
                            <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500/50">
                                Archive
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
