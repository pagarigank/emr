<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-emerald-800">Drug Inventory Dashboard</h2>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search drugs..."
                class="pl-10 pr-4 py-2 border border-emerald-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 w-72 text-sm"
            />
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-emerald-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-emerald-600 font-medium uppercase tracking-wide">Total Drugs</p>
                    <p class="text-2xl font-bold text-emerald-900">{{ number_format($totals['total_drugs']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-emerald-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-emerald-600 font-medium uppercase tracking-wide">Total Batches</p>
                    <p class="text-2xl font-bold text-emerald-900">{{ number_format($totals['total_batches']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-emerald-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-100 rounded-lg">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-emerald-600 font-medium uppercase tracking-wide">Stock Value</p>
                    <p class="text-2xl font-bold text-emerald-900">₱{{ number_format($totals['total_stock_value'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-amber-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-100 rounded-lg">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-amber-600 font-medium uppercase tracking-wide">Near Expiry</p>
                    <p class="text-2xl font-bold text-amber-900">{{ number_format($totals['near_expiry']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-red-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-red-600 font-medium uppercase tracking-wide">Expired</p>
                    <p class="text-2xl font-bold text-red-900">{{ number_format($totals['expired']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-orange-200 p-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-orange-600 font-medium uppercase tracking-wide">Needs Reorder</p>
                    <p class="text-2xl font-bold text-orange-900">{{ number_format($totals['needs_reorder']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-emerald-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-200">
                <thead class="bg-emerald-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider cursor-pointer hover:bg-emerald-100" wire:click="sortBy('drug_code')">
                            <div class="flex items-center gap-1">
                                Drug Code
                                @if ($sortField === 'drug_code')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider cursor-pointer hover:bg-emerald-100" wire:click="sortBy('generic_name')">
                            <div class="flex items-center gap-1">
                                Generic Name
                                @if ($sortField === 'generic_name')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider">Brand Name</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider cursor-pointer hover:bg-emerald-100" wire:click="sortBy('total_stock')">
                            <div class="flex items-center justify-end gap-1">
                                Total Stock
                                @if ($sortField === 'total_stock')
                                    <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                @endif
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-emerald-700 uppercase tracking-wider">Batches</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Avg Cost</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Stock Value</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-emerald-700 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-emerald-700 uppercase tracking-wider">Reorder</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-emerald-100">
                    @forelse ($drugs as $drug)
                        <tr
                            x-data="{ open: false }"
                            class="hover:bg-emerald-50 cursor-pointer transition-colors"
                            @click="open = !open"
                        >
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-emerald-800">{{ $drug->drug_code }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-emerald-900">{{ $drug->generic_name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-emerald-700">{{ $drug->brand_name ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold {{ $drug->total_stock <= $drug->reorder_level ? 'text-red-600' : 'text-emerald-800' }}">{{ number_format($drug->total_stock) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">{{ $drug->batch_count }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-emerald-700">₱{{ number_format($drug->avg_unit_cost, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-emerald-700">₱{{ number_format($drug->stock_value, 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if ($drug->expired_count > 0)
                                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500" title="Has expired stock"></span>
                                    @elseif ($drug->near_expiry_count > 0)
                                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-amber-500" title="Has near-expiry stock"></span>
                                    @else
                                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500" title="All stock OK"></span>
                                    @endif
                                    @if ($drug->near_expiry_count > 0)
                                        <span class="text-xs text-amber-600 font-medium">{{ $drug->near_expiry_count }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                @if ($drug->needs_reorder)
                                    <span class="inline-flex items-center gap-1 text-orange-600 font-medium" title="Below reorder level">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                @else
                                    <span class="text-emerald-400">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr x-show="open" x-cloak>
                            <td colspan="9" class="px-4 py-0 bg-emerald-50">
                                <div class="p-4">
                                    <h4 class="text-sm font-semibold text-emerald-800 mb-2">Batch Details — {{ $drug->generic_name }}</h4>
                                    <table class="min-w-full text-xs">
                                        <thead>
                                            <tr class="text-emerald-600 uppercase tracking-wider">
                                                <th class="px-3 py-1 text-left">Batch No</th>
                                                <th class="px-3 py-1 text-right">Qty On Hand</th>
                                                <th class="px-3 py-1 text-right">Unit Cost</th>
                                                <th class="px-3 py-1 text-right">Total</th>
                                                <th class="px-3 py-1 text-center">Expiry Date</th>
                                                <th class="px-3 py-1 text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-emerald-100">
                                            @php
                                                $batches = \App\Models\Pharmacy\DrugBatch::withoutGlobalScopes()
                                                    ->where('drug_id', $drug->id)
                                                    ->where('history', 1)
                                                    ->get();
                                            @endphp
                                            @forelse ($batches as $batch)
                                                <tr>
                                                    <td class="px-3 py-1 font-mono text-emerald-800">{{ $batch->batch_no }}</td>
                                                    <td class="px-3 py-1 text-right font-semibold">{{ number_format($batch->quantity_on_hand) }}</td>
                                                    <td class="px-3 py-1 text-right">₱{{ number_format($batch->unit_cost, 2) }}</td>
                                                    <td class="px-3 py-1 text-right">₱{{ number_format($batch->quantity_on_hand * $batch->unit_cost, 2) }}</td>
                                                    <td class="px-3 py-1 text-center">{{ $batch->expiry_date ? $batch->expiry_date->format('M d, Y') : '—' }}</td>
                                                    <td class="px-3 py-1 text-center">
                                                        @if ($batch->expiry_date && $batch->expiry_date < now())
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Expired</span>
                                                        @elseif ($batch->expiry_date && $batch->expiry_date <= now()->addDays(30))
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Near Expiry</span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Good</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-3 py-2 text-center text-emerald-500 italic">No active batches found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-emerald-500 italic">No drugs found matching your search.</td>
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
                {{ $drugs->firstItem() }}–{{ $drugs->lastItem() }} of {{ $drugs->total() }}
            </div>
        </div>

        <div class="px-4 py-3 border-t border-emerald-200 bg-emerald-50">
            {{ $drugs->links() }}
        </div>
    </div>
</div>
