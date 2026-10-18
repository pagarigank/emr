<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-emerald-800">Pharmacy Reports</h2>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-emerald-200 overflow-hidden">
        <div class="flex border-b border-emerald-200 bg-emerald-50">
            <button
                wire:click="$set('reportType', 'expiry')"
                class="px-6 py-3 text-sm font-medium transition-colors {{ $reportType === 'expiry' ? 'bg-white text-emerald-800 border-b-2 border-emerald-600' : 'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100' }}"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Expiry Report
                </div>
            </button>
            <button
                wire:click="$set('reportType', 'stock-status')"
                class="px-6 py-3 text-sm font-medium transition-colors {{ $reportType === 'stock-status' ? 'bg-white text-emerald-800 border-b-2 border-emerald-600' : 'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100' }}"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Stock Status
                </div>
            </button>
            <button
                wire:click="$set('reportType', 'consumption')"
                class="px-6 py-3 text-sm font-medium transition-colors {{ $reportType === 'consumption' ? 'bg-white text-emerald-800 border-b-2 border-emerald-600' : 'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100' }}"
            >
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Consumption Report
                </div>
            </button>
        </div>

        <div class="p-6">
            @if ($reportType === 'expiry')
                <div class="space-y-4">
                    <p class="text-sm text-emerald-600">Drug batches grouped by expiry status. Expand each section to view details.</p>

                    @php
                        $categories = ['Expired', 'Expiring Soon (≤30 days)', 'Expiring (31-90 days)', 'Good (>90 days)'];
                    @endphp

                    @foreach ($categories as $category)
                        @php $categoryData = $data->get($category, collect()); @endphp
                        <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="border border-emerald-200 rounded-lg overflow-hidden">
                            <div
                                class="flex items-center justify-between px-4 py-3 cursor-pointer transition-colors
                                    {{ $category === 'Expired' ? 'bg-red-50 hover:bg-red-100' : '' }}
                                    {{ $category === 'Expiring Soon (≤30 days)' ? 'bg-amber-50 hover:bg-amber-100' : '' }}
                                    {{ $category === 'Expiring (31-90 days)' ? 'bg-yellow-50 hover:bg-yellow-100' : '' }}
                                    {{ $category === 'Good (>90 days)' ? 'bg-green-50 hover:bg-green-100' : '' }}"
                                @click="open = !open"
                            >
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 text-emerald-500 transition-transform" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <h4 class="text-sm font-semibold
                                        {{ $category === 'Expired' ? 'text-red-700' : '' }}
                                        {{ $category === 'Expiring Soon (≤30 days)' ? 'text-amber-700' : '' }}
                                        {{ $category === 'Expiring (31-90 days)' ? 'text-yellow-700' : '' }}
                                        {{ $category === 'Good (>90 days)' ? 'text-green-700' : '' }}">
                                        {{ $category }}
                                    </h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-white/70
                                        {{ $category === 'Expired' ? 'text-red-600' : '' }}
                                        {{ $category === 'Expiring Soon (≤30 days)' ? 'text-amber-600' : '' }}
                                        {{ $category === 'Expiring (31-90 days)' ? 'text-yellow-600' : '' }}
                                        {{ $category === 'Good (>90 days)' ? 'text-green-600' : '' }}">
                                        {{ $categoryData->count() }} batches
                                    </span>
                                </div>
                                @if ($categoryData->count() > 0)
                                    <div class="text-xs text-emerald-500">
                                        ₱{{ number_format($categoryData->sum(fn($b) => $b->quantity_on_hand * $b->unit_cost), 2) }}
                                    </div>
                                @endif
                            </div>

                            <div x-show="open" x-cloak>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-emerald-100">
                                        <thead class="bg-emerald-50/50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-emerald-600 uppercase tracking-wider">Drug</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-emerald-600 uppercase tracking-wider">Batch No</th>
                                                <th class="px-4 py-2 text-center text-xs font-medium text-emerald-600 uppercase tracking-wider">Expiry Date</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-emerald-600 uppercase tracking-wider">Qty</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-emerald-600 uppercase tracking-wider">Unit Cost</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-emerald-600 uppercase tracking-wider">Total Value</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-emerald-50">
                                            @forelse ($categoryData as $batch)
                                                <tr class="hover:bg-emerald-50/50">
                                                    <td class="px-4 py-2 text-sm text-emerald-800">
                                                        <div class="font-medium">{{ $batch->drug?->generic_name ?? '—' }}</div>
                                                        <div class="text-xs text-emerald-400">{{ $batch->drug?->drug_code ?? '' }}</div>
                                                    </td>
                                                    <td class="px-4 py-2 text-sm font-mono text-emerald-700">{{ $batch->batch_no }}</td>
                                                    <td class="px-4 py-2 text-sm text-center {{ $batch->expiry_date < now() ? 'text-red-600 font-semibold' : 'text-emerald-700' }}">
                                                        {{ $batch->expiry_date ? $batch->expiry_date->format('M d, Y') : '—' }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-right font-semibold text-emerald-800">{{ number_format($batch->quantity_on_hand) }}</td>
                                                    <td class="px-4 py-2 text-sm text-right text-emerald-700">₱{{ number_format($batch->unit_cost, 2) }}</td>
                                                    <td class="px-4 py-2 text-sm text-right text-emerald-700">₱{{ number_format($batch->quantity_on_hand * $batch->unit_cost, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-4 py-4 text-sm text-emerald-500 italic text-center">No batches in this category.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif ($reportType === 'stock-status')
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div>
                            <label class="block text-xs font-medium text-emerald-600 mb-1">Filter by Drug</label>
                            <select wire:model.live="drug_id" class="px-3 py-2 border border-emerald-300 rounded-lg text-sm text-emerald-800 bg-white focus:ring-2 focus:ring-emerald-500">
                                <option value="">— All Drugs —</option>
                                @foreach ($drugs as $drug)
                                    <option value="{{ $drug->id }}">{{ $drug->generic_name }} ({{ $drug->drug_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-emerald-200">
                            <thead class="bg-emerald-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider">Drug</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Total Stock</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Reorder Level</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Stock Value</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-emerald-700 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100">
                                @php
                                    $filtered = $drug_id ? $data->where('drug.id', $drug_id) : $data;
                                @endphp
                                @forelse ($filtered as $item)
                                    <tr class="hover:bg-emerald-50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-emerald-800">
                                            <div class="font-medium">{{ $item['drug']->generic_name }}</div>
                                            <div class="text-xs text-emerald-400">{{ $item['drug']->drug_code }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold {{ $item['total_stock'] <= $item['drug']->reorder_level ? 'text-red-600' : 'text-emerald-800' }}">
                                            {{ number_format($item['total_stock']) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-emerald-700">{{ number_format($item['drug']->reorder_level) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-emerald-700">₱{{ number_format($item['stock_value'], 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-center">
                                            @php
                                                $statusClasses = [
                                                    'Low Stock' => 'bg-red-100 text-red-700',
                                                    'Adequate' => 'bg-green-100 text-green-700',
                                                    'Over Stock' => 'bg-amber-100 text-amber-700',
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$item['status']] ?? 'bg-emerald-100 text-emerald-700' }}">
                                                {{ $item['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-sm text-emerald-500 italic text-center">No drug stock data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @elseif ($reportType === 'consumption')
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div>
                            <label class="block text-xs font-medium text-emerald-600 mb-1">Date From</label>
                            <input type="date" wire:model.live="dateFrom" class="px-3 py-2 border border-emerald-300 rounded-lg text-sm text-emerald-800 bg-white focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-emerald-600 mb-1">Date To</label>
                            <input type="date" wire:model.live="dateTo" class="px-3 py-2 border border-emerald-300 rounded-lg text-sm text-emerald-800 bg-white focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        <div class="flex items-end">
                            <p class="text-xs text-emerald-500 pb-2">{{ $data->count() }} drugs dispensed</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-emerald-200">
                            <thead class="bg-emerald-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-emerald-700 uppercase tracking-wider">Drug Name</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Total Qty Dispensed</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Total Cost</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-emerald-700 uppercase tracking-wider">Request Count</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-emerald-100">
                                @forelse ($data as $item)
                                    <tr class="hover:bg-emerald-50 transition-colors">
                                        <td class="px-4 py-3 text-sm text-emerald-800">
                                            <div class="font-medium">{{ $item['drug']?->generic_name ?? 'Deleted Drug' }}</div>
                                            <div class="text-xs text-emerald-400">{{ $item['drug']?->drug_code ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold text-emerald-800">{{ number_format($item['total_qty']) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-emerald-700">₱{{ number_format($item['total_cost'], 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-emerald-700">{{ number_format($item['request_count']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-sm text-emerald-500 italic text-center">
                                            No consumption data available for the selected period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
