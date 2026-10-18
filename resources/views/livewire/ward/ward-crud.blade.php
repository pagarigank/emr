<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Wards</h2>
            <p class="text-sm text-gray-500 mt-1">Manage hospital wards and their details.</p>
        </div>
        <button wire:click="openCreate" class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Add Ward</button>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search code or name&hellip;" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm shadow-sm"/></div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
        </div>
    </div>
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    @foreach([['code', 'Code'], ['name', 'Name']] as [$field, $label])
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('{{ $field }}')">
                        <span class="inline-flex items-center gap-1">{{ $label }}<span class="text-gray-300">@if($sortField === $field){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    @endforeach
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($items as $item)
                    <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $item->code }}</td>
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $item->name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->description }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->location }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($item->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200/50 shadow-sm">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200 shadow-sm">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4"><div class="flex items-center justify-center space-x-2">
                            <button wire:click="openEdit({{ $item->id }})" class="p-1.5 text-light-blue-600 hover:bg-light-blue-100 rounded-md transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                            <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span class="text-lg font-medium text-gray-900">No wards found</span><p class="mt-1 text-sm">Try adjusting your search filters.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50/50">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isEditing ? 'Edit Ward' : 'Add New Ward' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="save" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">Code <span class="text-red-500">*</span></label>
                        <input wire:model="code" id="code" type="text" maxlength="20" placeholder="e.g. GEN-MED" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('code') border-red-300 bg-red-50 @enderror">
                        @error('code') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" id="name" type="text" maxlength="100" placeholder="e.g. General Medicine Ward" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('name') border-red-300 bg-red-50 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                    <input wire:model="description" id="description" type="text" maxlength="255" placeholder="Optional description" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('description') border-red-300 bg-red-50 @enderror">
                    @error('description') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                    <input wire:model="location" id="location" type="text" maxlength="255" placeholder="e.g. 2nd Floor, Building A" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('location') border-red-300 bg-red-50 @enderror">
                    @error('location') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center space-x-3 pt-2">
                    <button type="button" wire:click="$toggle('is_active')" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue-400 {{ $is_active ? 'bg-light-blue-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span></button>
                    <span class="text-sm text-gray-700 font-medium">{{ $is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update' : 'Save' }}</span>
                        <span wire:loading wire:target="save" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Archive Ward</h3>
                <p class="text-sm text-gray-500 mb-6">This record will be archived. No data is permanently deleted.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Archive</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
