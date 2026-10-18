<div>
    {{-- ── Page header ── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-light-blue-800">Permission Management</h2>
            <p class="text-sm text-gray-500 mt-1">Manage system permissions</p>
        </div>
        <button wire:click="openCreate" id="btn-add-permission"
                class="inline-flex items-center px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue-400">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Permission
        </button>
    </div>

    {{-- ── Toolbar (search + per-page) ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" id="search-permission"
                   type="text" placeholder="Search name, display name, module, description…"
                   class="pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 w-full sm:w-80 shadow-sm"/>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" id="per-page-permission"
                    class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 shadow-sm">
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
                    @foreach([
                        ['name', 'Permission Key'],
                        ['display_name', 'Display Name'],
                        ['module', 'Module'],
                        ['description', 'Description'],
                    ] as [$field, $label])
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none whitespace-nowrap hover:bg-gray-100/50 transition-colors"
                        wire:click="sortBy('{{ $field }}')">
                        <span class="inline-flex items-center gap-1">
                            {{ $label }}
                            @if($sortField === $field)
                                <svg class="w-3.5 h-3.5 text-light-blue-600 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }} transition-transform"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 10l5-5 5 5H5z"/>
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 10l5-5 5 5H5z"/>
                                </svg>
                            @endif
                        </span>
                    </th>
                    @endforeach

                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($permissions as $perm)
                    <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $perm->name }}</td>
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $perm->display_name }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-light-blue-50 text-light-blue-700 border border-light-blue-200/50">{{ $perm->module ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-500 max-w-xs truncate">{{ $perm->description ?? '—' }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($perm->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200/50 shadow-sm">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200 shadow-sm">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <button wire:click="openEdit({{ $perm->id }})"
                                        class="p-1.5 text-light-blue-600 hover:bg-light-blue-100 rounded-md transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $perm->id }})"
                                        class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive (soft delete)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8M10 12v4m4-4v4"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <svg class="mx-auto w-10 h-10 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            No permissions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    <div class="mt-4">
        {{ $permissions->links() }}
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         CREATE / EDIT MODAL
    ════════════════════════════════════════════════════════════════════════ --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center"
         x-data x-init="$el.querySelector('[data-modal]').focus()">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>

        {{-- Panel --}}
        <div data-modal tabindex="-1"
             class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 z-10 overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50">
                <h3 class="text-lg font-semibold text-light-blue-900">
                    {{ $isEditing ? 'Edit Permission' : 'Add New Permission' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form wire:submit="save" class="px-6 py-5 space-y-4">

                {{-- Name (Permission Key) --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Permission Key <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="name" id="name" type="text" maxlength="100"
                           placeholder="e.g. patients.create"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                  @error('name') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Display Name --}}
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Display Name <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="display_name" id="display_name" type="text" maxlength="200"
                           placeholder="e.g. Create Patients"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                  @error('display_name') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    @error('display_name')
                        <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Module --}}
                <div>
                    <label for="module" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Module <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="module" id="module" type="text" maxlength="100" list="module-list"
                           placeholder="e.g. Konsulta (type existing or new module name)"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500
                                  @error('module') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <datalist id="module-list">
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}">
                        @endforeach
                    </datalist>
                    @error('module')
                        <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <textarea wire:model="description" id="description" rows="3" maxlength="255"
                              placeholder="Optional description…"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 resize-none"></textarea>
                </div>

                {{-- Is Active --}}
                <div class="flex items-center space-x-3">
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
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="btn-save-permission"
                            class="px-5 py-2 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue-400">
                        <span wire:loading.remove wire:target="save">
                            {{ $isEditing ? 'Update' : 'Save' }}
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
            </form>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         ARCHIVE (SOFT DELETE) CONFIRM MODAL
    ════════════════════════════════════════════════════════════════════════ --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 z-10 p-6"
             x-data x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8M10 12v4m4-4v4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Archive Permission</h3>
                <p class="text-sm text-gray-500 mb-1">This record will be <strong>archived</strong> (history = 0).</p>
                <p class="text-xs text-gray-400 mb-6">No data is permanently deleted. Records can be restored from the database.</p>
                <div class="flex space-x-3 w-full">
                    <button wire:click="closeModal"
                            class="flex-1 px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="delete" id="btn-confirm-delete"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors">
                        <span wire:loading.remove wire:target="delete">Yes, Archive</span>
                        <span wire:loading wire:target="delete">Archiving…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
