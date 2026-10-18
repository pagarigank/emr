<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-gray-800 ">ER Beds</h2>
        <button wire:click="openCreate" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Bed
        </button>
    </div>

    <!-- Datagrid -->
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50/80 backdrop-blur-sm">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." class="w-full sm:w-80 px-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500">
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200 text-gray-600">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer hover:bg-gray-100/50 transition-colors" wire:click="sortBy('code')">Bed Code</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer hover:bg-gray-100/50 transition-colors" wire:click="sortBy('name')">Bed Name</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer hover:bg-gray-100/50 transition-colors" wire:click="sortBy('er_zone_id')">Zone</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer hover:bg-gray-100/50 transition-colors" wire:click="sortBy('status')">Current Status</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Active</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($beds as $b)
                        <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                            <td class="px-5 py-4 font-bold">{{ $b->code }}</td>
                            <td class="px-5 py-4">{{ $b->name }}</td>
                            <td class="px-5 py-4">{{ $b->zone ? $b->zone->name : 'N/A' }}</td>
                            <td class="px-5 py-4">
                                @if($b->status === 'Available')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">{{ $b->status }}</span>
                                @elseif($b->status === 'Occupied')
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">{{ $b->status }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">{{ $b->status }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($b->is_active)
                                    <span class="text-green-500"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                                @else
                                    <span class="text-red-500"><svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center space-x-2">
                                <button wire:click="openEdit({{ $b->id }})" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-md transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                <button wire:click="confirmDelete({{ $b->id }})" class="p-1.5 text-red-600 hover:bg-red-100 rounded-md transition-colors" title="Archive"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">No ER Beds found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        <div class="p-4 border-t border-gray-200">{{ $beds->links() }}</div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg z-10 p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b">
                <h3 class="text-lg font-bold text-gray-900">{{ $isEditing ? 'Edit Bed' : 'Create Bed' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700  mb-1">Bed Code</label>
                    <input type="text" wire:model="code" class="w-full px-4 py-2 border rounded-lg   " required>
                    @error('code') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700  mb-1">Bed Name</label>
                    <input type="text" wire:model="name" class="w-full px-4 py-2 border rounded-lg   " required>
                    @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700  mb-1">ER Zone (Optional)</label>
                    <select wire:model="er_zone_id" class="w-full px-4 py-2 border rounded-lg   ">
                        <option value="">-- No Zone --</option>
                        @foreach($zones as $z)
                            <option value="{{ $z->id }}">{{ $z->name }}</option>
                        @endforeach
                    </select>
                    @error('er_zone_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700  mb-1">Current Status</label>
                    <select wire:model="status" class="w-full px-4 py-2 border rounded-lg   " required>
                        <option value="Available">Available</option>
                        <option value="Occupied">Occupied</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                    @error('status') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
            <div>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700 ">Is Active</span>
                </label>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" wire:click="closeModal" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200  ">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
            </div>
        </form>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Confirm Archive</h3>
        <div class="p-4">
            <p class="text-gray-700 ">Are you sure you want to archive this ER Bed?</p>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" wire:click="closeModal" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                <button type="button" wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Archive</button>
            </div>
            </div>
        </div>
    </div>
    @endif
</div>
