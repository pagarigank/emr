<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Hospitals</h2>
            <p class="text-sm text-gray-500 mt-1">Manage referral hospitals for patient transfers.</p>
        </div>
        <button wire:click="openCreate" class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Hospital
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search hospitals..." class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th wire:click="sortBy('code')" class="px-5 py-3 text-left font-semibold text-gray-600 uppercase cursor-pointer hover:text-gray-900">Code @include('components.sort-icon', ['field' => 'code'])</th>
                        <th wire:click="sortBy('name')" class="px-5 py-3 text-left font-semibold text-gray-600 uppercase cursor-pointer hover:text-gray-900">Name @include('components.sort-icon', ['field' => 'name'])</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase">Type</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase">Contact</th>
                        <th class="px-5 py-3 text-center font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-5 py-3 text-center font-semibold text-gray-600 uppercase w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($hospitals as $h)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $h->code }}</td>
                            <td class="px-5 py-4 text-gray-800 font-medium">{{ $h->name }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $h->hospital_type ?? '—' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $h->contact_number ?? '—' }}</td>
                            <td class="px-5 py-4 text-center">
                                @if($h->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openEdit({{ $h->id }})" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $h->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg><span class="text-lg font-medium text-gray-900">No hospitals found</span><p class="mt-1 text-sm">Add a hospital to get started.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t">{{ $hospitals->links() }}</div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isEditing ? 'Edit' : 'Add' }} Hospital</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                        <input wire:model="code" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm @error('code') border-red-300 bg-red-50 @enderror">
                        @error('code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select wire:model="hospital_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                            <option value="">Select Type</option>
                            <option value="Primary">Primary</option>
                            <option value="Secondary">Secondary</option>
                            <option value="Tertiary">Tertiary</option>
                            <option value="Specialty">Specialty</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm @error('name') border-red-300 bg-red-50 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea wire:model="address" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                    <input wire:model="contact_number" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm">{{ $isEditing ? 'Update' : 'Save' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 p-6">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Archive Hospital?</h3>
                <p class="text-sm text-gray-500">This will deactivate this hospital record. Are you sure?</p>
                <div class="flex justify-center gap-3 mt-6">
                    <button wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button wire:click="delete" class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm">Archive</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
