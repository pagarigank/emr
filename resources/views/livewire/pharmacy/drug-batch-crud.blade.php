<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Drug Batches</h2>
            <p class="text-sm text-gray-500 mt-1">Manage drug inventory batches, lot numbers, and expiry tracking.</p>
        </div>
        <button wire:click="openCreate"
                class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Batch
        </button>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search drug name, code, or batch no&hellip;"
                   class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm shadow-sm"/>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage"
                    class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Drug Code</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Drug Name</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('batch_no')">
                        <span class="inline-flex items-center gap-1">Batch No<span class="text-gray-300">@if($sortField === 'batch_no'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('expiry_date')">
                        <span class="inline-flex items-center gap-1">Expiry Date<span class="text-gray-300">@if($sortField === 'expiry_date'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('quantity_on_hand')">
                        <span class="inline-flex items-center gap-1">Qty On Hand<span class="text-gray-300">@if($sortField === 'quantity_on_hand'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('unit_cost')">
                        <span class="inline-flex items-center gap-1">Unit Cost<span class="text-gray-300">@if($sortField === 'unit_cost'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('selling_price')">
                        <span class="inline-flex items-center gap-1">Selling Price<span class="text-gray-300">@if($sortField === 'selling_price'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($batches as $batch)
                    @php
                        $isExpired = $batch->expiry_date?->isPast();
                        $isExpiringSoon = !$isExpired && $batch->expiry_date && $batch->expiry_date->lte(now()->addDays(30));
                        $expiresIn = $batch->expiry_date ? now()->diffInDays($batch->expiry_date, false) : null;
                    @endphp
                    <tr class="hover:bg-emerald-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $batch->drug?->drug_code ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $batch->drug?->drug_name ?? '—' }}</td>
                        <td class="px-5 py-4 font-mono text-gray-900">{{ $batch->batch_no }}</td>
                        <td class="px-5 py-4 text-center font-mono text-gray-700 {{ $isExpired ? 'text-red-600 font-semibold' : ($isExpiringSoon ? 'text-amber-600 font-semibold' : '') }}">
                            {{ $batch->expiry_date?->format('Y-m-d') ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-right font-mono text-gray-900 font-semibold">{{ number_format($batch->quantity_on_hand, 2) }}</td>
                        <td class="px-5 py-4 text-right font-mono text-gray-700">{{ $batch->unit_cost !== null ? number_format($batch->unit_cost, 2) : '—' }}</td>
                        <td class="px-5 py-4 text-right font-mono text-gray-700">{{ $batch->selling_price !== null ? number_format($batch->selling_price, 2) : '—' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $batch->location ?? '—' }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($isExpired)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200 shadow-sm">Expired</span>
                            @elseif($isExpiringSoon)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 shadow-sm" title="Expires in {{ floor($expiresIn) }} day(s)">Expiring</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200 shadow-sm">Available</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <button wire:click="openEdit({{ $batch->id }})" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-md transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $batch->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-5 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span class="text-lg font-medium text-gray-900">No drug batches found</span>
                                <p class="mt-1 text-sm">Add a new batch to start tracking inventory.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $batches->links() }}</div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/50">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isEditing ? 'Edit Drug Batch' : 'Add New Drug Batch' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="save" class="px-6 py-5 space-y-4">
                {{-- Drug Search --}}
                <div class="relative">
                    <label for="drugSearch" class="block text-sm font-medium text-gray-700 mb-1.5">Drug <span class="text-red-500">*</span></label>
                    <input wire:model.live="drugSearch" id="drugSearch" type="text" autocomplete="off" placeholder="Search drug by code or name&hellip;"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('drug_id') border-red-300 bg-red-50 @enderror">
                    @error('drug_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    @if($showDrugDropdown && count($drugSearchResults) > 0)
                        <div class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            @foreach($drugSearchResults as $drug)
                                <button type="button" wire:click="selectDrug({{ $drug['id'] }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-900 transition-colors border-b border-gray-100 last:border-b-0">
                                    <span class="font-mono font-medium">{{ $drug['drug_code'] }}</span> — {{ $drug['drug_name'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @if($showDrugDropdown && strlen($drugSearch) > 0 && count($drugSearchResults) === 0)
                        <div class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-3 text-sm text-gray-500">No drugs found matching "{{ $drugSearch }}"</div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="batch_no" class="block text-sm font-medium text-gray-700 mb-1.5">Batch/Lot No. <span class="text-red-500">*</span></label>
                        <input wire:model="batch_no" id="batch_no" type="text" maxlength="100" placeholder="e.g. BATCH-001"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('batch_no') border-red-300 bg-red-50 @enderror">
                        @error('batch_no') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1.5">Expiry Date <span class="text-red-500">*</span></label>
                        <input wire:model="expiry_date" id="expiry_date" type="date"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('expiry_date') border-red-300 bg-red-50 @enderror">
                        @error('expiry_date') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="quantity_on_hand" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity On Hand <span class="text-red-500">*</span></label>
                        <input wire:model="quantity_on_hand" id="quantity_on_hand" type="number" step="0.01" min="0" placeholder="0.00"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('quantity_on_hand') border-red-300 bg-red-50 @enderror">
                        @error('quantity_on_hand') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                        <input wire:model="location" id="location" type="text" maxlength="100" placeholder="e.g. Shelf A-12"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('location') border-red-300 bg-red-50 @enderror">
                        @error('location') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="unit_cost" class="block text-sm font-medium text-gray-700 mb-1.5">Unit Cost</label>
                        <input wire:model="unit_cost" id="unit_cost" type="number" step="0.01" min="0" placeholder="0.00"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('unit_cost') border-red-300 bg-red-50 @enderror">
                        @error('unit_cost') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-1.5">Selling Price</label>
                        <input wire:model="selling_price" id="selling_price" type="number" step="0.01" min="0" placeholder="0.00"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('selling_price') border-red-300 bg-red-50 @enderror">
                        @error('selling_price') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update' : 'Save' }}</span>
                        <span wire:loading wire:target="save" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Archive Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                    <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Archive Batch</h3>
                <p class="text-sm text-gray-500 mb-6">This batch record will be archived. No data is permanently deleted.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Archive</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
