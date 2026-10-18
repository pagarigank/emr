<div>
    <!-- Top Toolbar -->
    <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
        <div class="flex items-center gap-3">
            <div class="relative w-72 sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search companies..." class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm shadow-sm">
            </div>
            
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span class="font-medium">Rows:</span>
                <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm rounded-xl shadow-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
        
        @can('admin.tenants')
        <div>
            <button wire:click="create" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Company
            </button>
        </div>
        @endcan
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white mx-6 mb-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/50 transition-colors group whitespace-nowrap" wire:click="sortBy('tenant_code')">
                        <div class="flex items-center gap-2">
                            Code
                            <span class="text-gray-300 group-hover:text-gray-500">
                                @if($sortField === 'tenant_code')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @else
                                    &uarr;&darr;
                                @endif
                            </span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer hover:bg-gray-100/50 transition-colors group whitespace-nowrap" wire:click="sortBy('tenant_name')">
                        <div class="flex items-center gap-2">
                            Name
                            <span class="text-gray-300 group-hover:text-gray-500">
                                @if($sortField === 'tenant_name')
                                    {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                                @else
                                    &uarr;&darr;
                                @endif
                            </span>
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Location
                    </th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($tenants as $tenant)
                    <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $tenant->tenant_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $tenant->tenant_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $tenant->city ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($tenant->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200/50 shadow-sm">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200 shadow-sm">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="edit({{ $tenant->id }})" class="text-light-blue-600 hover:text-light-blue-900 mx-2 transition-colors">Edit</button>
                            @can('admin.tenants')
                                <button wire:click="confirmArchive({{ $tenant->id }})" class="text-red-600 hover:text-red-900 transition-colors">Archive</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-lg font-medium text-gray-900">No companies found</span>
                                <p class="text-sm mt-1">Try adjusting your search or filter to find what you're looking for.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
        {{ $tenants->links() }}
    </div>

    <!-- Create/Edit Modal -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="closeModal"></div>
            
            {{-- Panel --}}
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 z-10 overflow-hidden transform transition-all">
                <form wire:submit.prevent="save">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50">
                        <h3 class="text-lg font-semibold text-light-blue-900" id="modal-title">
                            {{ $tenant_id ? 'Edit Company' : 'Add New Company' }}
                        </h3>
                        <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="px-6 py-5 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="tenant_code" class="block text-sm font-medium text-gray-700 mb-1.5">Company Code <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="tenant_code" id="tenant_code" class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm" @cannot('admin.tenants') disabled @endcannot>
                                @error('tenant_code') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="tenant_name" class="block text-sm font-medium text-gray-700 mb-1.5">Company Name <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="tenant_name" id="tenant_name" class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm">
                                @error('tenant_name') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
                            <input type="text" wire:model="address" id="address" class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm">
                            @error('address') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                                <input type="text" wire:model="city" id="city" class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm">
                                @error('city') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1.5">Contact Number</label>
                                <input type="text" wire:model="contact_number" id="contact_number" class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm">
                                @error('contact_number') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                            <input type="email" wire:model="email" id="email" class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm">
                            @error('email') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="pt-2">
                            <div class="flex items-center">
                                <input id="is_active" wire:model="is_active" type="checkbox" class="h-4 w-4 text-light-blue-600 focus:ring-light-blue-500 border-gray-300 rounded transition-colors" @cannot('admin.tenants') disabled @endcannot>
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                    Active Status
                                </label>
                            </div>
                            @error('is_active') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end space-x-3">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue-400">
                            Save Company
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Archive Confirmation Modal -->
    @if($isArchiveModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="$set('isArchiveModalOpen', false)"></div>
            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 z-10 p-6 transform transition-all">
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2" id="modal-title">
                        Archive Company
                    </h3>
                    <p class="text-sm text-gray-500 mb-6">
                        Are you sure you want to archive this company? This action will remove the company from the active list but preserve its data history.
                    </p>
                    <div class="flex space-x-3 w-full">
                        <button type="button" wire:click="$set('isArchiveModalOpen', false)" class="flex-1 px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="archive" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                            Yes, Archive It
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
