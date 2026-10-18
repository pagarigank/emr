<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-emerald-800">Stock Adjustments</h2>
        <div class="flex items-center gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search adjustments..."
                    class="pl-10 pr-4 py-2 border border-emerald-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 w-64 text-sm"
                />
            </div>
            <button
                wire:click="openCreate"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Adjustment
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-emerald-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-200">
                <thead class="bg-emerald-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider cursor-pointer hover:bg-emerald-100" wire:click="sortBy('adjustment_code')">
                            <div class="flex items-center gap-1">
                                Adj. Code
                                @if ($sortField === 'adjustment_code')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider cursor-pointer hover:bg-emerald-100" wire:click="sortBy('adjustment_type')">
                            <div class="flex items-center gap-1">
                                Type
                                @if ($sortField === 'adjustment_type')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider">Drug</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider">Batch</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider cursor-pointer hover:bg-emerald-100" wire:click="sortBy('quantity')">
                            <div class="flex items-center justify-end gap-1">
                                Quantity
                                @if ($sortField === 'quantity')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Unit Cost</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Total Value</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider">Reason</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider cursor-pointer hover:bg-emerald-100" wire:click="sortBy('created_date')">
                            <div class="flex items-center gap-1">
                                Date
                                @if ($sortField === 'created_date')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-emerald-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-emerald-100">
                    @forelse ($adjustments as $adj)
                        <tr class="hover:bg-emerald-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-emerald-800">{{ $adj->adjustment_code }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @php
                                    $negativeTypes = ['Write-off', 'Damage', 'Theft', 'Transfer Out'];
                                    $isNegative = in_array($adj->adjustment_type, $negativeTypes);
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isNegative ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $adj->adjustment_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-emerald-800">
                                <div class="font-medium">{{ $adj->drug?->generic_name ?? '—' }}</div>
                                <div class="text-xs text-emerald-500">{{ $adj->drug?->drug_code ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-emerald-700">
                                {{ $adj->drugBatch?->batch_no ?? '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold {{ $adj->quantity < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $adj->quantity < 0 ? '' : '+' }}{{ number_format($adj->quantity, 2) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-emerald-700">
                                ₱{{ number_format($adj->unit_cost, 2) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-emerald-700">
                                ₱{{ number_format($adj->quantity * $adj->unit_cost, 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-emerald-700 max-w-xs truncate" title="{{ $adj->reason }}">
                                {{ $adj->reason }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-emerald-600">
                                {{ $adj->created_date ? $adj->created_date->format('M d, Y H:i') : '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                <button
                                    wire:click="confirmDelete({{ $adj->id }})"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded hover:bg-amber-100 transition-colors"
                                    title="Archive adjustment"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Archive
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-emerald-500 italic">No stock adjustments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-emerald-200 bg-emerald-50 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-emerald-600">
                <span>Show</span>
                <select wire:model.live="perPage" class="border border-emerald-300 rounded px-2 py-1 text-sm text-emerald-800 bg-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>entries</span>
            </div>
            <div class="text-sm text-emerald-600">
                {{ $adjustments->firstItem() }}–{{ $adjustments->lastItem() }} of {{ $adjustments->total() }}
            </div>
        </div>

        <div class="px-4 py-3 border-t border-emerald-200 bg-emerald-50">
            {{ $adjustments->links() }}
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" x-data="{ drugOpen: false }">
            <div class="bg-white rounded-xl shadow-xl border border-emerald-200 w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-emerald-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-emerald-800">New Stock Adjustment</h3>
                    <button wire:click="closeModal" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-emerald-700 mb-1">Adjustment Code</label>
                            <input type="text" wire:model="adjustment_code" readonly class="w-full px-3 py-2 border border-emerald-300 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-mono" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-emerald-700 mb-1">Adjustment Type</label>
                            <select wire:model="adjustment_type" class="w-full px-3 py-2 border border-emerald-300 rounded-lg text-sm text-emerald-800 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="Write-off">Write-off</option>
                                <option value="Damage">Damage</option>
                                <option value="Theft">Theft</option>
                                <option value="Return">Return</option>
                                <option value="Transfer Out">Transfer Out</option>
                                <option value="Transfer In">Transfer In</option>
                                <option value="Correction">Correction</option>
                            </select>
                            @error('adjustment_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div x-data="{ open: false }">
                        <label class="block text-sm font-medium text-emerald-700 mb-1">Drug</label>
                        <div class="relative">
                            @if ($drug_text)
                                <div class="flex items-center justify-between px-3 py-2 border border-emerald-300 rounded-lg bg-emerald-50 text-sm text-emerald-800">
                                    <span>{{ $drug_text }}</span>
                                    <button type="button" wire:click="removeDrug" class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <input
                                    type="text"
                                    wire:model.live.debounce.200ms="drug_search"
                                    placeholder="Search by generic name, brand name, or drug code..."
                                    class="w-full px-3 py-2 border border-emerald-300 rounded-lg text-sm text-emerald-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    autocomplete="off"
                                />
                            @endif

                            @if (count($drugResults) > 0)
                                <div class="absolute z-10 mt-1 w-full bg-white border border-emerald-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                    @foreach ($drugResults as $result)
                                        <button
                                            type="button"
                                            wire:click="selectDrug({{ $result['id'] }})"
                                            class="w-full text-left px-4 py-2 text-sm text-emerald-800 hover:bg-emerald-50 border-b border-emerald-100 last:border-0"
                                        >
                                            <div class="font-medium">{{ $result['generic_name'] }}</div>
                                            <div class="text-xs text-emerald-500">{{ $result['drug_code'] }} — {{ $result['brand_name'] ?? 'N/A' }}{{ $result['drug_strength'] ? ' - ' . $result['drug_strength'] : '' }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        @error('drug_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-emerald-700 mb-1">Batch</label>
                        <select wire:model="drug_batch_id" class="w-full px-3 py-2 border border-emerald-300 rounded-lg text-sm text-emerald-800 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">— No specific batch —</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}">
                                    {{ $batch->batch_no }} — Qty: {{ number_format($batch->quantity_on_hand) }} — Exp: {{ $batch->expiry_date ? $batch->expiry_date->format('M d, Y') : 'N/A' }} — ₱{{ number_format($batch->unit_cost, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-emerald-700 mb-1">Quantity</label>
                            <input type="number" step="0.01" wire:model="quantity" class="w-full px-3 py-2 border border-emerald-300 rounded-lg text-sm text-emerald-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                            @error('quantity') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-emerald-700 mb-1">Unit Cost</label>
                            <input type="number" step="0.01" wire:model="unit_cost" class="w-full px-3 py-2 border border-emerald-300 rounded-lg text-sm text-emerald-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-emerald-700 mb-1">Reason</label>
                        <textarea wire:model="reason" rows="3" class="w-full px-3 py-2 border border-emerald-300 rounded-lg text-sm text-emerald-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Describe the reason for this adjustment..."></textarea>
                        @error('reason') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                            Save Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-xl shadow-xl border border-amber-200 w-full max-w-md mx-4 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-amber-100 rounded-full">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-amber-800">Archive Adjustment</h3>
                        <p class="text-sm text-amber-600">This action will archive the adjustment record.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="delete" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors">
                        Archive
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
