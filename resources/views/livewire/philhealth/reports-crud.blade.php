<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">PhilHealth Reports</h2>
            <p class="text-sm text-gray-500 mt-1">Summary statistics for PhilHealth e-Claims performance.</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Date From</label>
            <input wire:model.live="date_from" type="date"
                   class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Date To</label>
            <input wire:model.live="date_to" type="date"
                   class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
        </div>
        <div class="flex items-end gap-2">
            <button wire:click="loadReports" class="px-4 py-2 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-lg transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
            <button wire:click="exportCsv" class="px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-lg transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </button>
            <button wire:click="exportExcel" class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 rounded-lg transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Excel
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-light-blue-500 to-blue-600 rounded-xl p-5 shadow-sm text-white">
            <p class="text-sm opacity-80 font-medium">Total Claims</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($summary['total_claims'] ?? 0) }}</p>
            <p class="text-xs opacity-70 mt-1">Total: &#x20B1;{{ number_format($summary['total_claimed'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl p-5 shadow-sm text-white">
            <p class="text-sm opacity-80 font-medium">Paid</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($summary['paid'] ?? 0) }}</p>
            <p class="text-xs opacity-70 mt-1">Total Paid: &#x20B1;{{ number_format($summary['total_paid'] ?? 0, 2) }}</p>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-5 shadow-sm text-white">
            <p class="text-sm opacity-80 font-medium">Approval Rate</p>
            <p class="text-3xl font-bold mt-1">{{ $summary['approval_rate'] ?? 0 }}%</p>
            <p class="text-xs opacity-70 mt-1">Acknowledged: {{ number_format($summary['acknowledged'] ?? 0) }} | Denied: {{ number_format($summary['denied'] ?? 0) }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl p-5 shadow-sm text-white">
            <p class="text-sm opacity-80 font-medium">Collection Rate</p>
            <p class="text-3xl font-bold mt-1">{{ $summary['collection_rate'] ?? 0 }}%</p>
            <p class="text-xs opacity-70 mt-1">Actual: &#x20B1;{{ number_format($summary['total_actual'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Status Breakdown --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-sm font-semibold text-gray-700">Status Breakdown</h3>
            </div>
            <div class="p-5">
                @if(count($statusBreakdown) > 0)
                    <div class="space-y-3">
                        @php $totalCount = array_sum(array_column($statusBreakdown, 'count')); @endphp
                        @foreach($statusBreakdown as $sb)
                            @php $pct = $totalCount > 0 ? round($sb['count'] / $totalCount * 100, 1) : 0; @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700">{{ $sb['status'] }}</span>
                                    <span class="text-gray-500">{{ number_format($sb['count']) }} ({{ $pct }}%) — &#x20B1;{{ number_format($sb['total_amount'], 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ match($sb['status']) { 'Paid' => 'bg-green-500', 'Denied' => 'bg-red-500', 'Acknowledged' => 'bg-indigo-500', 'Submitted' => 'bg-blue-500', 'For Submission' => 'bg-yellow-500', default => 'bg-gray-400' } }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm text-center py-4">No claim data available.</p>
                @endif
            </div>
        </div>

        {{-- Aging Report --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-sm font-semibold text-gray-700">Aging (Pending Claims)</h3>
            </div>
            <div class="p-5">
                @if(count($aging) > 0 && collect($aging)->sum('count') > 0)
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase">
                                <th class="px-3 py-2 text-left">Bucket</th>
                                <th class="px-3 py-2 text-right">Claims</th>
                                <th class="px-3 py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($aging as $a)
                            <tr class="{{ $a['bucket'] === '90+ days' && $a['count'] > 0 ? 'bg-red-50' : '' }}">
                                <td class="px-3 py-2 font-medium">{{ $a['bucket'] }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($a['count']) }}</td>
                                <td class="px-3 py-2 text-right font-mono">&#x20B1;{{ number_format($a['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500 text-sm text-center py-4">No pending claims.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Monthly Trend --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80">
            <h3 class="text-sm font-semibold text-gray-700">Monthly Trend (Last 12 Months)</h3>
        </div>
        <div class="p-5 overflow-x-auto">
            @if(count($monthly) > 0)
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase">
                            <th class="px-3 py-2 text-left">Month</th>
                            <th class="px-3 py-2 text-right">Claims</th>
                            <th class="px-3 py-2 text-right">Actual Amount</th>
                            <th class="px-3 py-2 text-right">Claimed Amount</th>
                            <th class="px-3 py-2 text-right">Paid Amount</th>
                            <th class="px-3 py-2 text-right">Collection %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($monthly as $m)
                        <tr>
                            <td class="px-3 py-2 font-medium">{{ $m['month'] }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($m['claims']) }}</td>
                            <td class="px-3 py-2 text-right font-mono">&#x20B1;{{ number_format($m['actual'], 2) }}</td>
                            <td class="px-3 py-2 text-right font-mono">&#x20B1;{{ number_format($m['claimed'], 2) }}</td>
                            <td class="px-3 py-2 text-right font-mono">&#x20B1;{{ number_format($m['paid'], 2) }}</td>
                            <td class="px-3 py-2 text-right">{{ $m['claimed'] > 0 ? round($m['paid'] / $m['claimed'] * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-500 text-sm text-center py-4">No monthly data available.</p>
            @endif
        </div>
    </div>
</div>
