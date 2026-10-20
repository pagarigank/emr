<div class="p-6 max-w-7xl mx-auto space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-light-blue-50 text-light-blue-700 border border-light-blue-200">
                    PhilHealth Circular No. 2024-0033 &amp; 2025-0020
                </span>
                <span class="text-xs text-gray-500 font-medium">OECB-FBE Master Catalog</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight mt-1 flex items-center gap-2">
                <span>Essential Emergency Care List (EECL)</span>
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Statutory fixed fee schedule and compensable items for Emergency Department Outpatient Care Benefit.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="/philhealth/oecb-packages" wire:navigate
               class="inline-flex items-center px-4 py-2 border border-gray-200 text-xs font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
                <span>View Core Packages</span>
                <svg class="w-4 h-4 ml-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <button wire:click="openCreate" type="button"
                class="inline-flex items-center px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add EECL Item</span>
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-2xs">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total Items</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['total']) }}</p>
            <p class="text-[11px] text-teal-600 font-medium mt-0.5">{{ number_format($stats['active']) }} active</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-2xs">
            <p class="text-[11px] font-semibold text-indigo-600 uppercase tracking-wider">Diagnostics</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['diagnostic']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Labs &amp; Tests</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-2xs">
            <p class="text-[11px] font-semibold text-sky-600 uppercase tracking-wider">ER Services</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['er_services']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Consults &amp; Procs</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-2xs">
            <p class="text-[11px] font-semibold text-purple-600 uppercase tracking-wider">Imaging</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['imaging']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">X-Ray, ECG, US</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-2xs">
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">Medicines</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['medicines']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">Emergency Drugs</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-2xs">
            <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider">Supplies</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['supplies']) }}</p>
            <p class="text-[11px] text-gray-400 mt-0.5">CSR &amp; Disposables</p>
        </div>
    </div>

    {{-- Filter & Control Bar --}}
    <div class="bg-white rounded-2xl p-4 border border-gray-200/90 shadow-xs space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Search Input --}}
            <div class="relative flex-1 max-w-md">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search item code, name, description, or subcategory..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-xs bg-gray-50/50 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 transition-colors">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <div class="flex items-center space-x-2">
                {{-- Status Filter --}}
                <select wire:model.live="statusFilter" class="px-3 py-1.5 border border-gray-200 rounded-xl text-xs bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    <option value="all">All Statuses</option>
                    <option value="active">Active Only</option>
                    <option value="inactive">Inactive Only</option>
                </select>

                {{-- Per Page --}}
                <select wire:model.live="perPage" class="px-3 py-1.5 border border-gray-200 rounded-xl text-xs bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    <option value="15">15 per page</option>
                    <option value="30">30 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
        </div>

        {{-- Category Pills Filter --}}
        <div class="flex items-center space-x-1.5 overflow-x-auto no-scrollbar scrollbar-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden pt-2 border-t border-gray-100" style="scrollbar-width: none; -ms-overflow-style: none;">
            <button wire:click="$set('selectedCategory', '')" type="button"
                class="flex-shrink-0 px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ $selectedCategory === '' ? 'bg-light-blue-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                All Categories ({{ $stats['total'] }})
            </button>
            @foreach($categories as $cat)
                @php
                    $badgeCount = match($cat) {
                        'Diagnostic Services' => $stats['diagnostic'],
                        'ER Services' => $stats['er_services'],
                        'Imaging Services' => $stats['imaging'],
                        'Medicines' => $stats['medicines'],
                        'Supplies' => $stats['supplies'],
                        default => 0
                    };
                @endphp
                <button wire:click="$set('selectedCategory', '{{ $cat }}')" type="button"
                    class="flex-shrink-0 px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ $selectedCategory === $cat ? 'bg-light-blue-600 text-white shadow-2xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $cat }} ({{ $badgeCount }})
                </button>
            @endforeach
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-[11px] uppercase font-bold text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4 cursor-pointer hover:text-gray-900" wire:click="sortBy('item_code')">
                            <div class="flex items-center space-x-1">
                                <span>Item Code</span>
                                @if($sortField === 'item_code')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-4 cursor-pointer hover:text-gray-900" wire:click="sortBy('category')">
                            <div class="flex items-center space-x-1">
                                <span>Category / Group</span>
                                @if($sortField === 'category')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-4 cursor-pointer hover:text-gray-900" wire:click="sortBy('item_name')">
                            <div class="flex items-center space-x-1">
                                <span>Item Name &amp; Description</span>
                                @if($sortField === 'item_name')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-4 text-center">Unit</th>
                        <th class="py-3 px-4 text-right cursor-pointer hover:text-gray-900" wire:click="sortBy('fixed_fee')">
                            <div class="flex items-center justify-end space-x-1">
                                <span>PhilHealth Fixed Fee</span>
                                @if($sortField === 'fixed_fee')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </div>
                        </th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-light-blue-800 whitespace-nowrap">
                                {{ $item->item_code }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold
                                    @if($item->category === 'Diagnostic Services') bg-indigo-50 text-indigo-700 border border-indigo-200
                                    @elseif($item->category === 'ER Services') bg-sky-50 text-sky-700 border border-sky-200
                                    @elseif($item->category === 'Imaging Services') bg-purple-50 text-purple-700 border border-purple-200
                                    @elseif($item->category === 'Medicines') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($item->category === 'Supplies') bg-amber-50 text-amber-700 border border-amber-200
                                    @else bg-gray-100 text-gray-700 border border-gray-200 @endif">
                                    {{ $item->category }}
                                </span>
                                @if($item->subcategory)
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $item->subcategory }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900">{{ $item->item_name }}</div>
                                @if($item->description)
                                    <div class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center text-gray-600 font-medium">
                                {{ $item->unit ?? 'service' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-gray-900">
                                ₱{{ number_format((float) $item->fixed_fee, 2) }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <button wire:click="toggleActive({{ $item->id }})" type="button"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors cursor-pointer {{ $item->is_active ? 'bg-teal-50 text-teal-700 border border-teal-200 hover:bg-teal-100' : 'bg-gray-100 text-gray-500 border border-gray-200 hover:bg-gray-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $item->is_active ? 'bg-teal-500' : 'bg-gray-400' }}"></span>
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <button wire:click="openEdit({{ $item->id }})" type="button"
                                        class="p-1.5 text-gray-500 hover:text-light-blue-600 hover:bg-light-blue-50 rounded-lg transition-colors" title="Edit Item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" type="button"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Archive Item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm font-semibold text-gray-600">No EECL items found</p>
                                <p class="text-xs text-gray-400 mt-0.5">Try refining your search or category filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-200">
                    <form wire:submit.prevent="save">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">
                                {{ $isEditing ? 'Edit EECL Item' : 'Add New EECL Item' }}
                            </h3>
                            <button wire:click="closeModal" type="button" class="text-gray-400 hover:text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Item Code <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="item_code" placeholder="e.g. EECL-DS-028"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('item_code') border-red-300 bg-red-50 @enderror">
                                    @error('item_code') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                                    <select wire:model="category"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('category') border-red-300 @enderror">
                                        @foreach($categories as $c)
                                            <option value="{{ $c }}">{{ $c }}</option>
                                        @endforeach
                                    </select>
                                    @error('category') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Subcategory / Clinical Group</label>
                                    <input type="text" wire:model="subcategory" placeholder="e.g. Hematology, Airway, Analgesics"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Unit of Service</label>
                                    <input type="text" wire:model="unit" placeholder="e.g. test, session, vial, pc"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="item_name" placeholder="Full official PhilHealth EECL description"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('item_name') border-red-300 bg-red-50 @enderror">
                                @error('item_name') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">PhilHealth Fixed Fee (₱) <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" min="0" wire:model="fixed_fee" placeholder="0.00"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-mono font-bold bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('fixed_fee') border-red-300 bg-red-50 @enderror">
                                    @error('fixed_fee') <p class="text-[11px] text-red-500 mt-1 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div class="flex items-center pt-6">
                                    <label class="flex items-center cursor-pointer select-none">
                                        <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded border-gray-300 text-light-blue-600 focus:ring-light-blue-500">
                                        <span class="ml-2 text-xs font-semibold text-gray-800">Active &amp; Compensable</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Notes / Clinical Guidelines</label>
                                <textarea wire:model="description" rows="2" placeholder="Optional notes, indications, or restrictions per Circular 2024-0033..."
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-2">
                            <button wire:click="closeModal" type="button"
                                class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-colors">
                                {{ $isEditing ? 'Save Changes' : 'Create EECL Item' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs transition-opacity" wire:click="closeModal"></div>
                <div class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-gray-200 p-6 space-y-4">
                    <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="text-center">
                        <h3 class="text-base font-bold text-gray-900">Archive EECL Item</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Are you sure you want to archive this EECL item? It will be deactivated and hidden from active emergency billing forms.
                        </p>
                    </div>
                    <div class="flex justify-end space-x-2 pt-2">
                        <button wire:click="closeModal" type="button" class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button wire:click="delete" type="button" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-semibold shadow-xs">
                            Yes, Archive
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
