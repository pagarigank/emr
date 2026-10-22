<div>
    @if($showTreatmentForm && $activeSession)
    {{-- ===== TREATMENT FORM VIEW ===== --}}
    <div x-data="{ activeTab: 'clinical' }">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-lg mb-6 overflow-hidden">
            <div class="bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-white">Dialysis Treatment Record</h2>
                        <p class="text-emerald-100 text-sm mt-1">Session #{{ $activeSession->code }}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-white/20 text-white">{{ $activeSession->status }}</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 px-6 py-4 bg-gray-50/80 border-b border-gray-100">
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Patient</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeSession->patient?->last_name ?? '—' }}, {{ $activeSession->patient?->first_name ?? '—' }}</p>
                    <p class="text-[11px] font-mono text-gray-500">{{ $activeSession->patient?->patient_code }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Station / Bay</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">
                        @if($activeSession->dialysisMachine)
                            {{ $activeSession->dialysisMachine->station_name }}
                            @if($activeSession->dialysisMachine->station_category === 'HepB_Isolation')
                                <span class="block text-[10px] text-amber-700 font-semibold">☣ HepB Bay</span>
                            @elseif($activeSession->dialysisMachine->station_category === 'HepC_Isolation')
                                <span class="block text-[10px] text-red-700 font-semibold">☣ HepC Bay</span>
                            @elseif($activeSession->dialysisMachine->station_category === 'ICU_Mobile')
                                <span class="block text-[10px] text-purple-700 font-semibold">ICU Mobile</span>
                            @else
                                <span class="block text-[10px] text-gray-500">Regular Bay</span>
                            @endif
                        @else
                            <span class="text-gray-400">Not Assigned</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Shift &amp; Session</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeSession->shift ?? 'Shift 1 (AM)' }}</p>
                    <p class="text-[11px] text-emerald-700 font-semibold">#{{ $activeSession->session_number ?? '1' }} of 156 (eCF4)</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Weights &amp; UF</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeSession->pre_weight ?? '?' }} &rarr; {{ $activeSession->post_weight ?? '?' }} kg</p>
                    <p class="text-[11px] text-gray-600">Dry: {{ $activeSession->dry_weight ? $activeSession->dry_weight.'kg' : '—' }} | UF: {{ $activeSession->uf_actual_liters ? $activeSession->uf_actual_liters.'L' : '—' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Dialyzer &amp; Reuse</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5 truncate">{{ $activeSession->dialyzer_model ?? 'Fresenius FX80' }}</p>
                    <p class="text-[11px] text-gray-600">Reuse #{{ $activeSession->dialyzer_reuse_number ?? 0 }} | Bath: {{ Str::limit($activeSession->dialysate_bath, 15) }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Attending Nephrologist</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5 truncate">
                        {{ $activeSession->attendingPhysician ? 'Dr. '.$activeSession->attendingPhysician->first_name.' '.$activeSession->attendingPhysician->last_name : '—' }}
                    </p>
                    <p class="text-[11px] text-gray-500">{{ $activeSession->session_date?->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-6" aria-label="Tabs">
                    <button @click="activeTab = 'clinical'" :class="activeTab === 'clinical' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Clinical Notes
                    </button>
                    <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Orders &amp; Interventions
                    </button>
                    <button @click="activeTab = 'disposition'" :class="activeTab === 'disposition' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Disposition
                    </button>
                </nav>
            </div>
        </div>

        <form wire:submit="saveTreatment">
            <div x-show="activeTab === 'clinical'" class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        History of Present Illness
                    </h3>
                    <textarea wire:model="treatment_history_of_present_illness" rows="4" placeholder="Narrative of present illness, onset, duration, and associated symptoms..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Physical Examination Findings
                    </h3>
                    <textarea wire:model="treatment_physical_exam_findings" rows="5" placeholder="Physical exam findings by system..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Assessment &amp; Plan
                    </h3>
                    <textarea wire:model="treatment_assessment" rows="4" placeholder="Working diagnosis, differential diagnoses, and treatment plan..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        Treatment Notes
                    </h3>
                    <textarea wire:model="treatment_treatment_notes" rows="4" placeholder="Additional clinical notes, progress updates, reassessments..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                </div>
            </div>

            <div x-show="activeTab === 'orders'" class="space-y-5" wire:poll.30s="refreshOrders">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Orders Summary
                    </h3>
                    <textarea wire:model="treatment_orders_summary" rows="4" placeholder="Medications, IV fluids, consults, and other orders..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 font-mono hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                </div>

                {{-- Lab Orders --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            Lab Orders <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $labOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderLab" class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Lab Test
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th>
                                <th class="px-5 py-2.5 text-left">Items</th>
                                <th class="px-5 py-2.5 text-center">Priority</th>
                                <th class="px-5 py-2.5 text-center">Status</th>
                                <th class="px-5 py-2.5 text-center">Billing</th>
                                <th class="px-5 py-2.5 text-right">Total</th>
                                <th class="px-5 py-2.5 text-left">Date</th>
                                <th class="px-5 py-2.5 text-center">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($labOrders as $order)
                                <tr class="hover:bg-purple-50/40 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                                    <td class="px-5 py-3">
                                        <div class="space-y-1">
                                        @foreach($order->items as $item)
                                            <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                                <span class="bg-purple-50 text-purple-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">{{ $item->test_name }}</span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded whitespace-nowrap {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Processing' || $is === 'Collected' ? 'bg-blue-100 text-blue-700' : ($is === 'Completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                                @if($is !== 'Completed' && $is !== 'Cancelled')
                                                <div class="relative" x-data="{ open: false }">
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors">&#8942;</button>
                                                    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateLabItemStatus({{ $item->id }}, 'Collected')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Collected</button>
                                                        @endif
                                                        @if($is === 'Pending' || $is === 'Collected')
                                                        <button wire:click="updateLabItemStatus({{ $item->id }}, 'Processing')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Processing</button>
                                                        @endif
                                                        @if($is === 'Processing' || $is === 'Collected')
                                                        <button wire:click="updateLabItemStatus({{ $item->id }}, 'Completed')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Completed</button>
                                                        @endif
                                                        <button wire:click="updateLabItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">Cancel</button>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->priority === 'STAT' ? 'bg-red-100 text-red-700' : ($order->priority === 'Urgent' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">{{ $order->priority }}</span></td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->status === 'Pending' ? 'bg-gray-100 text-gray-600' : ($order->status === 'Processing' || $order->status === 'Collected' ? 'bg-blue-100 text-blue-700' : ($order->status === 'Completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $order->status }}</span></td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->billing_status === 'Billed' ? 'bg-green-100 text-green-700' : ($order->billing_status === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($order->billing_status === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $order->billing_status ?? 'Pending' }}</span></td>
                                    <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format($order->total_charge, 2) }}</td>
                                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $order->created_date?->format('M d, H:i') }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if($order->status !== 'Cancelled' && $order->status !== 'Completed')
                                        <button wire:click="cancelLabRequest({{ $order->id }})" wire:confirm="Cancel this lab request?" class="text-xs text-red-500 hover:text-red-700 font-medium">Cancel</button>
                                        @else
                                        <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="px-5 py-8 text-center text-gray-400 text-sm">No lab orders placed yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- CSR Orders --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Supply Orders (CSR) <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $csrOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderCsr" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Supply
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th>
                                <th class="px-5 py-2.5 text-left">Items</th>
                                <th class="px-5 py-2.5 text-center">Status</th>
                                <th class="px-5 py-2.5 text-center">Billing</th>
                                <th class="px-5 py-2.5 text-right">Total</th>
                                <th class="px-5 py-2.5 text-left">Date</th>
                                <th class="px-5 py-2.5 text-center">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($csrOrders as $order)
                                <tr class="hover:bg-emerald-50/40 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                                    <td class="px-5 py-3">
                                        <div class="space-y-1">
                                        @foreach($order->items as $item)
                                            <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                                <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">{{ $item->item_name }} <span class="text-emerald-400">(x{{ $item->quantity }})</span></span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded whitespace-nowrap {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                                @if($is !== 'Complete' && $is !== 'Cancelled')
                                                <div class="relative" x-data="{ open: false }">
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors">&#8942;</button>
                                                    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Approved</button>
                                                        @endif
                                                        @if($is === 'Approved' || $is === 'Partial')
                                                        <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Partial</button>
                                                        <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Complete</button>
                                                        @endif
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Complete</button>
                                                        @endif
                                                        <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">Cancel</button>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->status === 'Pending' ? 'bg-gray-100 text-gray-600' : ($order->status === 'Approved' || $order->status === 'Partial' ? 'bg-blue-100 text-blue-700' : ($order->status === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $order->status }}</span></td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->billing_status === 'Billed' ? 'bg-green-100 text-green-700' : ($order->billing_status === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($order->billing_status === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $order->billing_status ?? 'Pending' }}</span></td>
                                    <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $order->created_date?->format('M d, H:i') }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if($order->status !== 'Cancelled' && $order->status !== 'Complete')
                                        <button wire:click="cancelCsrRequest({{ $order->id }})" wire:confirm="Cancel this supply request?" class="text-xs text-red-500 hover:text-red-700 font-medium">Cancel</button>
                                        @else
                                        <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400 text-sm">No supply orders placed yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Radiology Orders --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Radiology Orders <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $radiologyOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderRadiology" class="inline-flex items-center px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Exam
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th>
                                <th class="px-5 py-2.5 text-left">Exams</th>
                                <th class="px-5 py-2.5 text-center">Status</th>
                                <th class="px-5 py-2.5 text-center">Billing</th>
                                <th class="px-5 py-2.5 text-right">Total</th>
                                <th class="px-5 py-2.5 text-left">Date</th>
                                <th class="px-5 py-2.5 text-center">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($radiologyOrders as $order)
                                <tr class="hover:bg-sky-50/40 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                                    <td class="px-5 py-3">
                                        <div class="space-y-1">
                                        @foreach($order->items as $item)
                                            <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                                <span class="bg-sky-50 text-sky-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">{{ $item->exam_name }} @if($item->modality_name)<span class="text-sky-400">({{ $item->modality_name }})</span>@endif</span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded whitespace-nowrap {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                                @if($is !== 'Complete' && $is !== 'Cancelled')
                                                <div class="relative" x-data="{ open: false }">
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors">&#8942;</button>
                                                    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Approved</button>
                                                        @endif
                                                        @if($is === 'Approved' || $is === 'Partial')
                                                        <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Partial</button>
                                                        <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Complete</button>
                                                        @endif
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Complete</button>
                                                        @endif
                                                        <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">Cancel</button>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->status === 'Pending' ? 'bg-gray-100 text-gray-600' : ($order->status === 'Approved' || $order->status === 'Partial' ? 'bg-blue-100 text-blue-700' : ($order->status === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $order->status }}</span></td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->billing_status === 'Billed' ? 'bg-green-100 text-green-700' : ($order->billing_status === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($order->billing_status === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $order->billing_status ?? 'Pending' }}</span></td>
                                    <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $order->created_date?->format('M d, H:i') }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if($order->status !== 'Cancelled' && $order->status !== 'Complete')
                                        <button wire:click="cancelRadiologyRequest({{ $order->id }})" wire:confirm="Cancel this radiology request?" class="text-xs text-red-500 hover:text-red-700 font-medium">Cancel</button>
                                        @else
                                        <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400 text-sm">No radiology orders placed yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pharmacy Orders --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Pharmacy Orders <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $pharmacyOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderPharmacy" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Medication
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th>
                                <th class="px-5 py-2.5 text-left">Medications</th>
                                <th class="px-5 py-2.5 text-center">Priority</th>
                                <th class="px-5 py-2.5 text-center">Status</th>
                                <th class="px-5 py-2.5 text-center">Billing</th>
                                <th class="px-5 py-2.5 text-right">Total</th>
                                <th class="px-5 py-2.5 text-left">Date</th>
                                <th class="px-5 py-2.5 text-center">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($pharmacyOrders as $order)
                                <tr class="hover:bg-emerald-50/40 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                                    <td class="px-5 py-3">
                                        <div class="space-y-1">
                                        @foreach($order->items as $item)
                                            <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                                <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">{{ $item->item_name }} <span class="text-emerald-400">(x{{ $item->quantity }})</span></span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded whitespace-nowrap {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                                @if($is !== 'Complete' && $is !== 'Cancelled')
                                                <div class="relative" x-data="{ open: false }">
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors">&#8942;</button>
                                                    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                        @if($is === 'Pending')
                                                        <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Approved</button>
                                                        @endif
                                                        @if($is === 'Approved' || $is === 'Partial')
                                                        <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Partial</button>
                                                        <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Complete</button>
                                                        @endif
                                                        @if($is === 'Pending')
                                                        <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Complete</button>
                                                        @endif
                                                        <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">Cancel</button>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->priority === 'STAT' ? 'bg-red-100 text-red-700' : ($order->priority === 'Urgent' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">{{ $order->priority }}</span></td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->status === 'Pending' ? 'bg-gray-100 text-gray-600' : ($order->status === 'Approved' || $order->status === 'Partial' ? 'bg-blue-100 text-blue-700' : ($order->status === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $order->status }}</span></td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->billing_status === 'Billed' ? 'bg-green-100 text-green-700' : ($order->billing_status === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($order->billing_status === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $order->billing_status ?? 'Pending' }}</span></td>
                                    <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $order->created_date?->format('M d, H:i') }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if($order->status !== 'Cancelled' && $order->status !== 'Complete')
                                        <button wire:click="cancelPharmacyRequest({{ $order->id }})" wire:confirm="Cancel this pharmacy request?" class="text-xs text-red-500 hover:text-red-700 font-medium">Cancel</button>
                                        @else
                                        <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="px-5 py-8 text-center text-gray-400 text-sm">No pharmacy orders placed yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Procedure Orders --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            Procedure Orders <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $procedureOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderProcedure" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Procedure
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th>
                                <th class="px-5 py-2.5 text-left">Procedures</th>
                                <th class="px-5 py-2.5 text-center">Status</th>
                                <th class="px-5 py-2.5 text-center">Billing</th>
                                <th class="px-5 py-2.5 text-right">Total</th>
                                <th class="px-5 py-2.5 text-left">Date</th>
                                <th class="px-5 py-2.5 text-center">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($procedureOrders as $order)
                                <tr class="hover:bg-indigo-50/40 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                                    <td class="px-5 py-3">
                                        <div class="space-y-1">
                                        @foreach($order->items as $item)
                                            <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                                <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">{{ $item->procedure_name }} @if($item->category_name)<span class="text-indigo-400">({{ $item->category_name }})</span>@endif</span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded whitespace-nowrap {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                                @if($is !== 'Complete' && $is !== 'Cancelled')
                                                <div class="relative" x-data="{ open: false }">
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors">&#8942;</button>
                                                    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Approved</button>
                                                        @endif
                                                        @if($is === 'Approved' || $is === 'Partial')
                                                        <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50">Partial</button>
                                                        <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Complete</button>
                                                        @endif
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Complete</button>
                                                        @endif
                                                        <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">Cancel</button>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->status === 'Pending' ? 'bg-gray-100 text-gray-600' : ($order->status === 'Approved' || $order->status === 'Partial' ? 'bg-blue-100 text-blue-700' : ($order->status === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $order->status }}</span></td>
                                    <td class="px-5 py-3 text-center"><span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->billing_status === 'Billed' ? 'bg-green-100 text-green-700' : ($order->billing_status === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($order->billing_status === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $order->billing_status ?? 'Pending' }}</span></td>
                                    <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $order->created_date?->format('M d, H:i') }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if($order->status !== 'Cancelled' && $order->status !== 'Complete')
                                        <button wire:click="cancelProcedureRequest({{ $order->id }})" wire:confirm="Cancel this procedure request?" class="text-xs text-red-500 hover:text-red-700 font-medium">Cancel</button>
                                        @else
                                        <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400 text-sm">No procedure orders placed yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'disposition'" class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Disposition
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                            <select wire:model="disposition" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Discharged">Discharged</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Discharge Instructions</label>
                            <textarea wire:model="discharge_instructions" rows="3" placeholder="Instructions for the patient..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center px-6 py-4 bg-white border-t border-gray-200 rounded-b-xl mt-6">
                <button type="button" wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">
                    Back to Sessions
                </button>
                <div class="flex space-x-3">
                    @if($disposition !== 'Pending')
                    <button type="button" wire:click="saveDisposition" class="px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                        Complete &amp; Close
                    </button>
                    @endif
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="saveTreatment">Save Clinical Notes</span>
                        <span wire:loading wire:target="saveTreatment" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving…</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Order Modals --}}
    @if($showOrderLabModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="closeOrderLab"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-purple-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Lab Tests</h3>
                <button wire:click="closeOrderLab" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
                        <select wire:model="orderLabPriority" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                            <option value="Routine">Routine</option><option value="Urgent">Urgent</option><option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                        <input type="text" wire:model="orderLabNotes" placeholder="e.g. Fasting, stat..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    </div>
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($labTests as $test)
                    <label class="flex items-center px-4 py-2.5 hover:bg-purple-50/40 cursor-pointer">
                        <input type="checkbox" wire:model="orderLabSelection" value="{{ $test->id }}" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 mr-3">
                        <div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900">{{ $test->code }} — {{ $test->name }}</p></div>
                        <span class="text-xs font-mono text-gray-500">&#8369;{{ number_format($test->standard_charge ?? 0, 2) }}</span>
                    </label>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No lab tests available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeOrderLab" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button wire:click="requestLabOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm">
                        <span wire:loading.remove wire:target="requestLabOrder">Place Lab Order</span>
                        <span wire:loading wire:target="requestLabOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showOrderCsrModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="closeOrderCsr"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-emerald-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Supply Items</h3>
                <button wire:click="closeOrderCsr" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div><input type="text" wire:model="orderCsrNotes" placeholder="Notes..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($supplyItems as $item)
                    <div class="flex items-center px-4 py-2.5 hover:bg-emerald-50/40">
                        <input type="checkbox" wire:model="orderCsrSelection" value="{{ $item->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-3">
                        <div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900">{{ $item->code }} — {{ $item->name }}</p></div>
                        <input type="number" wire:model="orderCsrQuantities.{{ $item->id }}" min="1" value="1" class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center mx-2">
                        <span class="text-xs font-mono text-gray-500 w-20 text-right">&#8369;{{ number_format($item->unit_cost ?? 0, 2) }}</span>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No supply items available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeOrderCsr" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button wire:click="requestCsrOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm">
                        <span wire:loading.remove wire:target="requestCsrOrder">Place Supply Order</span>
                        <span wire:loading wire:target="requestCsrOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showOrderRadiologyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="closeOrderRadiology"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-sky-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Radiology Exam</h3>
                <button wire:click="closeOrderRadiology" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
                        <select wire:model="orderRadiologyPriority" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                            <option value="Routine">Routine</option><option value="Urgent">Urgent</option><option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                        <input type="text" wire:model="orderRadiologyNotes" placeholder="e.g. Contrast needed..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                    </div>
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($examTypes as $exam)
                    <label class="flex items-center px-4 py-2.5 hover:bg-sky-50/40 cursor-pointer">
                        <input type="checkbox" wire:model="orderRadiologySelection" value="{{ $exam->id }}" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500 mr-3">
                        <div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900">{{ $exam->code }} — {{ $exam->name }} @if($exam->modality)<span class="text-xs text-gray-400">({{ $exam->modality->name }})</span>@endif</p></div>
                        <span class="text-xs font-mono text-gray-500">&#8369;{{ number_format($exam->standard_charge ?? 0, 2) }}</span>
                    </label>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No exam types available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeOrderRadiology" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button wire:click="requestRadiologyOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-sm">
                        <span wire:loading.remove wire:target="requestRadiologyOrder">Place Radiology Order</span>
                        <span wire:loading wire:target="requestRadiologyOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showOrderPharmacyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="closeOrderPharmacy"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-emerald-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Medications</h3>
                <button wire:click="closeOrderPharmacy" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
                        <select wire:model="orderPharmacyPriority" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="Routine">Routine</option><option value="Urgent">Urgent</option><option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                        <input type="text" wire:model="orderPharmacyNotes" placeholder="Notes..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($drugs as $drug)
                    <div x-data="{ open: false }" class="px-4 py-2.5 hover:bg-emerald-50/40">
                        <div class="flex items-center">
                            <input type="checkbox" wire:model="orderPharmacySelection" value="{{ $drug->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-3">
                            <div class="flex-1 min-w-0" @click="open = !open">
                                <p class="text-sm font-medium text-gray-900">{{ $drug->generic_name }} {{ $drug->drug_strength }} <span class="text-xs text-gray-400">({{ $drug->brand_name ?? '—' }})</span></p>
                                <p class="text-xs text-gray-400">{{ $drug->dosage_form ?? '' }}</p>
                            </div>
                            <input type="number" wire:model="orderPharmacyQuantities.{{ $drug->id }}" min="1" value="1" class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center mx-2">
                        </div>
                        <div x-show="open" x-collapse class="mt-2 grid grid-cols-2 gap-2 pl-7">
                            <div>
                                <label class="text-xs text-gray-500">Frequency</label>
                                <select wire:model="orderPharmacyFrequencies.{{ $drug->id }}" class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="">—</option><option value="OD">OD</option><option value="BID">BID</option><option value="TID">TID</option><option value="QID">QID</option><option value="QHS">QHS</option><option value="PRN">PRN</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Route</label>
                                <select wire:model="orderPharmacyRoutes.{{ $drug->id }}" class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="">—</option><option value="PO">PO</option><option value="IV">IV</option><option value="IM">IM</option><option value="SC">SC</option><option value="SL">SL</option><option value="TOP">TOP</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No drugs available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeOrderPharmacy" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button wire:click="requestPharmacyOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm">
                        <span wire:loading.remove wire:target="requestPharmacyOrder">Place Pharmacy Order</span>
                        <span wire:loading wire:target="requestPharmacyOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showOrderProcedureModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="closeOrderProcedure"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-indigo-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Procedures</h3>
                <button wire:click="closeOrderProcedure" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div><input type="text" wire:model="orderProcedureNotes" placeholder="Notes..." class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($procedures as $procedure)
                    <div class="flex items-center px-4 py-2.5 hover:bg-indigo-50/40">
                        <input type="checkbox" wire:model="orderProcedureSelection" value="{{ $procedure->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                        <div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900">{{ $procedure->name }} @if($procedure->category)<span class="text-xs text-gray-400">({{ $procedure->category->name }})</span>@endif</p></div>
                        <input type="number" wire:model="orderProcedureQuantities.{{ $procedure->id }}" min="1" value="1" class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center mx-2">
                        <span class="text-xs font-mono text-gray-500 w-20 text-right">&#8369;{{ number_format($procedure->standard_charge ?? 0, 2) }}</span>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No procedures available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeOrderProcedure" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button wire:click="requestProcedureOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm">
                        <span wire:loading.remove wire:target="requestProcedureOrder">Place Procedure Order</span>
                        <span wire:loading wire:target="requestProcedureOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @else
    {{-- ===== DATAGRID VIEW ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Dialysis Sessions</h2>
            <p class="text-sm text-gray-500 mt-1">Record and manage dialysis treatment sessions.</p>
        </div>
        <button wire:click="openCreate" class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Add Session</button>
    </div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4">
        <div class="flex flex-wrap items-center gap-2 flex-1">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search code, patient, access..." class="block w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-xs shadow-sm"/>
            </div>

            <select wire:model.live="stationFilter" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 hover:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm">
                <option value="">All Stations / Bays</option>
                @foreach($dialysisMachines as $dm)
                    <option value="{{ $dm->id }}">{{ $dm->station_code }} - {{ $dm->station_name }}</option>
                @endforeach
            </select>

            <select wire:model.live="shiftFilter" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 hover:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm">
                <option value="">All Shifts</option>
                <option value="Shift 1 (AM)">Shift 1 (AM)</option>
                <option value="Shift 2 (Mid)">Shift 2 (Mid)</option>
                <option value="Shift 3 (PM)">Shift 3 (PM)</option>
            </select>

            <select wire:model.live="statusFilter" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 hover:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm">
                <option value="">All Statuses</option>
                <option value="Scheduled">Scheduled</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <div class="flex items-center space-x-2 text-xs text-gray-600 shrink-0">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" class="pl-2 pr-6 py-1.5 bg-gray-50 border border-gray-200 text-gray-900 rounded-lg shadow-sm text-xs focus:ring-emerald-500">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    @foreach([['code', 'Code'], ['session_date', 'Date & Shift']] as [$field, $label])
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('{{ $field }}')">
                        <span class="inline-flex items-center gap-1">{{ $label }}<span class="text-gray-300">@if($sortField === $field){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    @endforeach
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient &amp; Session #</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Station / Isolation Bay</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Access &amp; Type</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Weights &amp; UF</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Billing Queue</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($items as $item)
                    <tr class="hover:bg-emerald-50/40 transition-colors duration-150">
                        <td class="px-4 py-3.5 font-mono font-medium text-gray-900 text-xs">{{ $item->code }}</td>
                        <td class="px-4 py-3.5 text-xs text-gray-700 whitespace-nowrap">
                            <div>{{ $item->session_date ? $item->session_date->format('M d, Y') : '—' }}</div>
                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">{{ $item->shift ?? 'Shift 1 (AM)' }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="text-xs font-bold text-gray-900">{{ $item->patient?->last_name }}, {{ $item->patient?->first_name }}</div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono text-[10px] text-gray-500">{{ $item->patient?->patient_code }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">#{{ $item->session_number ?? '1' }} of 156</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if($item->dialysisMachine)
                                <div class="text-xs font-semibold text-gray-800">{{ $item->dialysisMachine->station_name }} <span class="text-[10px] font-mono text-gray-400">({{ $item->dialysisMachine->station_code }})</span></div>
                                @if($item->dialysisMachine->station_category === 'HepB_Isolation')
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">☣ HepB Isolation</span>
                                @elseif($item->dialysisMachine->station_category === 'HepC_Isolation')
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-900 border border-rose-300">☣ HepC Isolation</span>
                                @elseif($item->dialysisMachine->station_category === 'ICU_Mobile')
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-900 border border-purple-200">ICU Mobile</span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">Regular Bay</span>
                                @endif
                            @else
                                <span class="text-xs text-gray-400 italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-700 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $item->access_type ?? 'AV Fistula' }}</div>
                            <div class="text-[10px] text-gray-500 truncate max-w-[140px]">{{ $item->access_site ?: ($item->dialysisType?->name ?? 'HD') }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-center text-xs whitespace-nowrap">
                            <span class="font-semibold text-gray-800">{{ $item->pre_weight ?? '?' }} &rarr; {{ $item->post_weight ?? '?' }} kg</span>
                            <div class="text-[10px] text-gray-500">Dry: {{ $item->dry_weight ? $item->dry_weight.'kg' : '—' }} | UF: {{ $item->uf_actual_liters ? $item->uf_actual_liters.'L' : '—' }}</div>
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            @if($item->procedure_request_id)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300" title="Procedure charge ₱4,500.00 posted to billing queue">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    HD-SESS (₱4.5k)
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">Unbilled</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium 
                                @if($item->status === 'Completed') bg-green-50 text-green-700 border border-green-200
                                @elseif($item->status === 'In Progress') bg-blue-50 text-blue-700 border border-blue-200
                                @elseif($item->status === 'Cancelled') bg-red-50 text-red-700 border border-red-200
                                @else bg-gray-50 text-gray-600 border border-gray-200 @endif">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-1.5">
                                <button wire:click="openTreatment({{ $item->id }})"
                                        class="inline-flex items-center px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors" title="Open Treatment Record">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                    Treat
                                </button>
                                <button wire:click="openEdit({{ $item->id }})" class="p-1 text-emerald-600 hover:bg-emerald-100 rounded-md transition-colors" title="Edit Session &amp; eCF4"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                <button wire:click="openDisposition({{ $item->id }})"
                                        class="p-1 text-green-700 hover:bg-green-100 rounded-md transition-colors" title="Set Disposition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $item->id }})" class="p-1 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span class="text-lg font-medium text-gray-900">No sessions found</span><p class="mt-1 text-sm">Try adjusting your search filters.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
    @endif

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl z-10 flex flex-col max-h-[92vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/70 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $isEditing ? 'Edit Dialysis Session & eCF4 Record' : 'New Hemodialysis Treatment Session' }}</h3>
                        <p class="text-xs text-gray-500 font-mono">{{ $isEditing ? $code : ($code ?: 'DS-xxxxx (auto-assigned)') }}</p>
                    </div>
                </div>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>

            <form wire:submit="save" class="flex flex-col min-h-0">
                <div class="px-6 py-5 space-y-6 overflow-y-auto">
                    {{-- Section 1: Scheduling & Operation --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center">1</span>
                            Session Scheduling &amp; Status
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-gray-50/70 p-3.5 rounded-xl border border-gray-200">
                            <div>
                                <label for="session_date" class="block text-xs font-semibold text-gray-700 mb-1">Session Date <span class="text-red-500">*</span></label>
                                <input wire:model="session_date" id="session_date" type="date" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('session_date') border-red-300 bg-red-50 @enderror">
                                @error('session_date') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="shift" class="block text-xs font-semibold text-gray-700 mb-1">Shift Allocation <span class="text-red-500">*</span></label>
                                <select wire:model="shift" id="shift" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="Shift 1 (AM)">Shift 1 (AM) &bull; 06:00 - 10:00</option>
                                    <option value="Shift 2 (Mid)">Shift 2 (Mid) &bull; 10:30 - 14:30</option>
                                    <option value="Shift 3 (PM)">Shift 3 (PM) &bull; 15:00 - 19:00</option>
                                </select>
                            </div>

                            <div>
                                <label for="session_number" class="block text-xs font-semibold text-gray-700 mb-1">Session # (1-156)</label>
                                <div class="relative">
                                    <input wire:model="session_number" id="session_number" type="number" min="1" max="156" placeholder="e.g. 1" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <span class="absolute right-2.5 top-2 text-[10px] text-gray-400 font-semibold">/ 156</span>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-0.5">PhilHealth statutory benefit cap</p>
                            </div>

                            <div>
                                <label for="status" class="block text-xs font-semibold text-gray-700 mb-1">Session Status</label>
                                <select wire:model="status" id="status" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="In Progress">In Progress (Active)</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Patient & Care Team --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center">2</span>
                            Patient &amp; Attending Care Team
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-gray-50/70 p-3.5 rounded-xl border border-gray-200">
                            <div class="sm:col-span-1">
                                <label for="patient_search" class="block text-xs font-semibold text-gray-700 mb-1">Patient <span class="text-red-500">*</span></label>
                                @if(!$isEditing)
                                    <input wire:model.live.debounce.250ms="patient_search" id="patient_search" type="text" placeholder="Search patient name or code..." autocomplete="off" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('patient_id') border-red-300 bg-red-50 @enderror">
                                    @if($patient_search && $patients->isNotEmpty())
                                        <ul class="mt-1 border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-40 overflow-y-auto bg-white shadow-lg text-xs">
                                            @foreach($patients as $p)
                                                <li wire:click="$set('patient_id', {{ $p->id }}); $set('patient_search', '{{ $p->last_name }}, {{ $p->first_name }} ({{ $p->patient_code }})')" class="px-3 py-2 cursor-pointer hover:bg-emerald-50 transition-colors">
                                                    <span class="font-bold text-gray-900">{{ $p->last_name }}, {{ $p->first_name }}</span>
                                                    <span class="font-mono text-gray-500 ml-1">({{ $p->patient_code }})</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @else
                                    <p class="text-xs text-gray-800 py-2 px-1 font-semibold">{{ $patient_search }}</p>
                                @endif
                                @error('patient_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="attending_physician_id" class="block text-xs font-semibold text-gray-700 mb-1">Attending Nephrologist</label>
                                <select wire:model="attending_physician_id" id="attending_physician_id" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="">-- Select Nephrologist / Doctor --</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}">Dr. {{ $doc->last_name }}, {{ $doc->first_name }} ({{ $doc->specialization ?? 'Nephrology' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="dialysis_nurse_id" class="block text-xs font-semibold text-gray-700 mb-1">Dialysis Nurse</label>
                                <select wire:model="dialysis_nurse_id" id="dialysis_nurse_id" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="">-- Select Dialysis Nurse --</option>
                                    @foreach($nurses as $nurse)
                                        <option value="{{ $nurse->id }}">{{ $nurse->first_name }} {{ $nurse->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Inpatient / ER Linking --}}
                            @if($admissions->isNotEmpty() || $erAdmissions->isNotEmpty())
                            <div class="sm:col-span-3 pt-2 border-t border-gray-200 flex flex-wrap gap-4">
                                @if($admissions->isNotEmpty())
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-[11px] font-semibold text-emerald-800 mb-1">Link In-Patient Ward Admission</label>
                                    <select wire:model="admission_id" class="w-full px-3 py-1.5 bg-white border border-emerald-300 rounded-lg text-xs text-emerald-900">
                                        <option value="">-- Outpatient (No Inpatient Admission) --</option>
                                        @foreach($admissions as $adm)
                                            <option value="{{ $adm->id }}">{{ $adm->admission_code }} (Admitted: {{ $adm->admission_date?->format('M d, Y') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                @if($erAdmissions->isNotEmpty())
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-[11px] font-semibold text-sky-800 mb-1">Link ER Case</label>
                                    <select wire:model="er_admission_id" class="w-full px-3 py-1.5 bg-white border border-sky-300 rounded-lg text-xs text-sky-900">
                                        <option value="">-- None (Direct Dialysis Walk-in) --</option>
                                        @foreach($erAdmissions as $er)
                                            <option value="{{ $er->id }}">{{ $er->er_case_no }} (Admitted: {{ $er->admission_datetime?->format('M d, Y') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Section 3: Station Assignment & Infection Isolation --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center">3</span>
                            Machine / Station &amp; Infection Isolation
                        </h4>

                        @php
                            $selMachine = $dialysisMachines->firstWhere('id', (int) $dialysis_machine_id);
                        @endphp

                        @if($selMachine && ($selMachine->station_category === 'HepB_Isolation' || $selMachine->station_category === 'HepC_Isolation'))
                            <div class="mb-3 p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs flex items-center gap-2.5">
                                <span class="text-xl">☣️</span>
                                <div>
                                    <span class="font-bold uppercase tracking-wider text-amber-800">Infection Isolation Bay Protocol Active:</span>
                                    Station <strong>{{ $selMachine->station_name }} ({{ $selMachine->station_code }})</strong> is dedicated strictly for <strong>{{ $selMachine->station_category === 'HepB_Isolation' ? 'HBsAg+ (Hepatitis B)' : 'Anti-HCV+ (Hepatitis C)' }}</strong> patients per DOH &amp; PhilHealth statutory infection prevention standards.
                                </div>
                            </div>
                        @elseif($selMachine && $selMachine->station_category === 'ICU_Mobile')
                            <div class="mb-3 p-3 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 text-xs flex items-center gap-2.5">
                                <span class="text-xl">🚑</span>
                                <div>
                                    <span class="font-bold uppercase tracking-wider text-purple-800">ICU Bedside Mobile Dialysis:</span>
                                    Unit is designated for telemetry-monitored intensive care inpatients.
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-gray-50/70 p-3.5 rounded-xl border border-gray-200">
                            <div class="sm:col-span-2">
                                <label for="dialysis_machine_id" class="block text-xs font-semibold text-gray-700 mb-1">Dialysis Machine / Station Bay</label>
                                <select wire:model.live="dialysis_machine_id" id="dialysis_machine_id" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="">-- Select Station Bay --</option>
                                    @foreach($dialysisMachines as $dm)
                                        <option value="{{ $dm->id }}">
                                            [{{ $dm->station_code }}] {{ $dm->station_name }} &bull; {{ $dm->station_category }} ({{ $dm->status }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="dialysis_type_id" class="block text-xs font-semibold text-gray-700 mb-1">Modality Type</label>
                                <select wire:model="dialysis_type_id" id="dialysis_type_id" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="">-- Select --</option>
                                    @foreach($dialysisTypes as $dt)
                                        <option value="{{ $dt->id }}">[{{ $dt->code }}] {{ $dt->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="access_type" class="block text-xs font-semibold text-gray-700 mb-1">Vascular Access</label>
                                <select wire:model="access_type" id="access_type" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="AV Fistula">AV Fistula (AVF)</option>
                                    <option value="AV Graft">AV Graft (AVG)</option>
                                    <option value="Permcath">Permcath (Tunneled)</option>
                                    <option value="Catheter">Temporary Catheter</option>
                                </select>
                            </div>

                            <div class="sm:col-span-4">
                                <label for="access_site" class="block text-xs font-semibold text-gray-700 mb-1">Access Anatomical Site</label>
                                <input wire:model="access_site" id="access_site" type="text" placeholder="e.g. Left Radiocephalic, Left Brachiocephalic, Right Internal Jugular..." class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>
                    </div>

                    {{-- Section 4: eCF4 Hemodialysis Clinical Parameters --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center">4</span>
                                eCF4 Hemodialysis Clinical Parameters (PhilHealth Statutory)
                            </span>
                            <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">PhilHealth RVS 90935</span>
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50/70 p-3.5 rounded-xl border border-gray-200">
                            <div>
                                <label for="dry_weight" class="block text-xs font-semibold text-gray-700 mb-1">Dry Weight (kg)</label>
                                <input wire:model="dry_weight" id="dry_weight" type="text" inputmode="decimal" placeholder="e.g. 62.0" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="pre_weight" class="block text-xs font-semibold text-gray-700 mb-1">Pre Weight (kg)</label>
                                <input wire:model="pre_weight" id="pre_weight" type="text" inputmode="decimal" placeholder="e.g. 65.5" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="post_weight" class="block text-xs font-semibold text-gray-700 mb-1">Post Weight (kg)</label>
                                <input wire:model="post_weight" id="post_weight" type="text" inputmode="decimal" placeholder="e.g. 62.3" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="uf_actual_liters" class="block text-xs font-semibold text-gray-700 mb-1">Actual UF Removed (L)</label>
                                <input wire:model="uf_actual_liters" id="uf_actual_liters" type="text" inputmode="decimal" placeholder="e.g. 3.2" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="uf_goal_liters" class="block text-xs font-semibold text-gray-700 mb-1">UF Goal (L)</label>
                                <input wire:model="uf_goal_liters" id="uf_goal_liters" type="text" inputmode="decimal" placeholder="e.g. 3.0" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="blood_flow_rate_qb" class="block text-xs font-semibold text-gray-700 mb-1">Blood Flow Q<sub>B</sub> (mL/min)</label>
                                <input wire:model="blood_flow_rate_qb" id="blood_flow_rate_qb" type="number" min="50" max="600" placeholder="300" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="dialysate_flow_rate_qd" class="block text-xs font-semibold text-gray-700 mb-1">Dialysate Flow Q<sub>D</sub> (mL/min)</label>
                                <input wire:model="dialysate_flow_rate_qd" id="dialysate_flow_rate_qd" type="number" min="300" max="1000" placeholder="500" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="dialysate_bath" class="block text-xs font-semibold text-gray-700 mb-1">Dialysate Bath</label>
                                <input wire:model="dialysate_bath" id="dialysate_bath" type="text" placeholder="Bicarbonate / K+ 2.0 / Ca++ 1.25" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="dialyzer_model" class="block text-xs font-semibold text-gray-700 mb-1">Dialyzer Model</label>
                                <input wire:model="dialyzer_model" id="dialyzer_model" type="text" placeholder="Fresenius FX80" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="dialyzer_reuse_number" class="block text-xs font-semibold text-gray-700 mb-1">Dialyzer Reuse Count</label>
                                <select wire:model="dialyzer_reuse_number" id="dialyzer_reuse_number" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="0">0 (Single Use / New)</option>
                                    <option value="1">Reuse 1</option>
                                    <option value="2">Reuse 2</option>
                                    <option value="3">Reuse 3</option>
                                    <option value="4">Reuse 4</option>
                                    <option value="5">Reuse 5</option>
                                </select>
                            </div>

                            <div>
                                <label for="anticoagulation_type" class="block text-xs font-semibold text-gray-700 mb-1">Anticoagulation</label>
                                <select wire:model="anticoagulation_type" id="anticoagulation_type" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="Heparin">Standard Heparin</option>
                                    <option value="LMWH">LMWH (Enoxaparin)</option>
                                    <option value="Heparin-Free">Heparin-Free (Saline Flushes)</option>
                                    <option value="Citrate">Regional Citrate</option>
                                </select>
                            </div>

                            <div>
                                <label for="heparin_dose_units" class="block text-xs font-semibold text-gray-700 mb-1">Heparin Dose (IU)</label>
                                <input wire:model="heparin_dose_units" id="heparin_dose_units" type="number" min="0" max="30000" placeholder="5000" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div class="sm:col-span-4">
                                <label for="complications" class="block text-xs font-semibold text-gray-700 mb-1">Intradialytic Complications</label>
                                <input wire:model="complications" id="complications" type="text" placeholder="None, Hypotension, Muscle cramps, Access site clotting..." class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>
                    </div>

                    {{-- Section 5: Pre & Post Vitals --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center">5</span>
                            Pre &amp; Post Hemodialysis Vitals
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50/70 p-3.5 rounded-xl border border-gray-200">
                            <div>
                                <label for="pre_bp" class="block text-xs font-semibold text-gray-700 mb-1">Pre-Dialysis BP</label>
                                <input wire:model="pre_bp" id="pre_bp" type="text" maxlength="20" placeholder="e.g. 140/90" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="post_bp" class="block text-xs font-semibold text-gray-700 mb-1">Post-Dialysis BP</label>
                                <input wire:model="post_bp" id="post_bp" type="text" maxlength="20" placeholder="e.g. 130/80" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="heart_rate" class="block text-xs font-semibold text-gray-700 mb-1">Heart Rate (bpm)</label>
                                <input wire:model="heart_rate" id="heart_rate" type="number" min="30" max="250" placeholder="78" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>

                            <div>
                                <label for="pre_temp" class="block text-xs font-semibold text-gray-700 mb-1">Temperature (&deg;C)</label>
                                <input wire:model="pre_temp" id="pre_temp" type="text" inputmode="decimal" placeholder="36.5" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>
                    </div>

                    {{-- Section 6: Billing Hook & Notes --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center">6</span>
                            Automated Billing Hook &amp; Session Notes
                        </h4>
                        <div class="space-y-3 bg-gray-50/70 p-3.5 rounded-xl border border-gray-200">
                            <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-50/60 border border-emerald-200">
                                <div>
                                    <label class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Auto-Post Hemodialysis Procedure Charge (₱4,500.00 HD-SESS)
                                    </label>
                                    <p class="text-[11px] text-emerald-700 mt-0.5">Generates a Procedure Request hooked directly into the patient's Unbilled Billing Queue upon saving.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="auto_post_billing" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="medications" class="block text-xs font-semibold text-gray-700 mb-1">Medications Administered</label>
                                    <textarea wire:model="medications" id="medications" rows="2" placeholder="e.g. EPO 4000 IU SC post-HD, Iron Sucrose 100mg IV..." class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                                </div>
                                <div>
                                    <label for="notes" class="block text-xs font-semibold text-gray-700 mb-1">Session &amp; Nursing Notes</label>
                                    <textarea wire:model="notes" id="notes" rows="2" placeholder="Access thrill/bruit, patient tolerance, intradialytic observations..." class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3 pt-1">
                                <button type="button" wire:click="$toggle('is_active')" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-400 {{ $is_active ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform {{ $is_active ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                </button>
                                <span class="text-xs text-gray-700 font-medium">{{ $is_active ? 'Active Record' : 'Inactive' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center px-6 py-4 border-t border-gray-100 bg-gray-50/50 shrink-0">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Session & eCF4 Record' : 'Save Session & Post Billing' }}</span>
                        <span wire:loading wire:target="save" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ===== DELETE MODAL ===== --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Archive Session</h3>
                <p class="text-sm text-gray-500 mb-6">This record will be archived. No data is permanently deleted.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Archive</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showDispositionModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Set Disposition</h3>
            <form wire:submit="saveDisposition" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Disposition <span class="text-red-500">*</span></label>
                    <select wire:model="disposition" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Transferred">Transferred</option>
                        <option value="Discharged">Discharged</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Discharge Instructions</label>
                    <textarea wire:model="discharge_instructions" rows="3" placeholder="Instructions for the patient..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm">Save Disposition</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
