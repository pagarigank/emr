<div>
    @if($showTreatmentForm && $activeConsultation)
    {{-- ===== TREATMENT FORM VIEW ===== --}}
    <div x-data="{ activeTab: 'clinical' }">
        {{-- Patient Header Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-lg mb-6 overflow-hidden transform transition-all hover:shadow-xl">
            <div class="bg-gradient-to-br from-rose-600 via-rose-700 to-pink-800 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-white">OB/GYN Consultation</h2>
                        <p class="text-rose-100 text-sm mt-1">#{{ $activeConsultation->consultation_code }}</p>
                    </div>
                    <div>
                        <span class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-white/20 text-white">
                            {{ $activeConsultation->status }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 px-6 py-4 bg-gray-50/80 border-b border-gray-100">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeConsultation->patient->last_name }}, {{ $activeConsultation->patient->first_name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Attending</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeConsultation->attendingPhysician ? 'Dr. ' . $activeConsultation->attendingPhysician->full_name : 'Unassigned' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Visit Type</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeConsultation->visitType?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeConsultation->created_date?->format('M d, Y H:i') }}</p>
                </div>
            </div>
            {{-- OB Vitals Summary --}}
            <div class="grid grid-cols-4 md:grid-cols-8 gap-3 px-6 py-3 bg-gray-50/40 border-b border-gray-100 text-center">
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Gest Age</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeConsultation->gestational_age ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Gravida</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeConsultation->gravida ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Para</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeConsultation->para ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Full Term</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeConsultation->full_term ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Preterm</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeConsultation->preterm ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Abortion</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeConsultation->abortion ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Living</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeConsultation->living_children ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">LMP</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeConsultation->lmp_date?->format('M d, Y') ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-6" aria-label="Tabs">
                    <button @click="activeTab = 'clinical'" :class="activeTab === 'clinical' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Clinical Notes
                    </button>
                    <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Orders &amp; Interventions
                    </button>
                    <button @click="activeTab = 'orschedule'" :class="activeTab === 'orschedule' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Delivery Room
                    </button>
                    <button @click="activeTab = 'disposition'" :class="activeTab === 'disposition' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Disposition
                    </button>
                </nav>
            </div>
        </div>

        <form wire:submit="saveTreatment">
            {{-- Tab: OB Fields + Clinical Notes --}}
            <div x-show="activeTab === 'clinical'" class="space-y-5">
                {{-- OB-Specific Fields --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Obstetric Details
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Gestational Age</label>
                            <input type="text" wire:model="gestational_age" placeholder="e.g. 32 weeks"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">LMP Date</label>
                            <input type="date" wire:model="lmp_date"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">EDC Date</label>
                            <input type="date" wire:model="edc_date"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Visit Type</label>
                            <select wire:model="visit_type_id"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                                <option value="">— Select —</option>
                                @foreach($visitTypes as $vt)
                                    <option value="{{ $vt->id }}">{{ $vt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Gravida</label>
                            <input type="number" min="0" wire:model="gravida" placeholder="0"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Para</label>
                            <input type="number" min="0" wire:model="para" placeholder="0"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Full Term</label>
                            <input type="number" min="0" wire:model="full_term" placeholder="0"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Preterm</label>
                            <input type="number" min="0" wire:model="preterm" placeholder="0"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Abortion</label>
                            <input type="number" min="0" wire:model="abortion" placeholder="0"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Living Children</label>
                            <input type="number" min="0" wire:model="living_children" placeholder="0"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Attending Physician</label>
                            <select wire:model="attending_physician_id"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                                <option value="">— Select —</option>
                                @foreach($physicians as $dr)
                                    <option value="{{ $dr->id }}">Dr. {{ $dr->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Obstetrical History</label>
                        <textarea wire:model="obstetrical_history" rows="2" placeholder="Previous pregnancies, complications, deliveries..."
                                  class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                    </div>
                </div>

                {{-- Clinical Notes --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        History of Present Illness
                    </h3>
                    <textarea wire:model="treatment_history_of_present_illness" rows="4" placeholder="Detailed narrative of the present illness..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Physical Examination Findings
                    </h3>
                    <textarea wire:model="treatment_physical_exam_findings" rows="5" placeholder="Document physical exam findings..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Assessment &amp; Plan
                    </h3>
                    <textarea wire:model="treatment_assessment" rows="4" placeholder="Working diagnosis, differential diagnoses, and treatment plan..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        Treatment Notes
                    </h3>
                    <textarea wire:model="treatment_treatment_notes" rows="4" placeholder="Additional clinical notes, progress updates..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>
            </div>

            {{-- Tab: Orders & Interventions --}}
            <div x-show="activeTab === 'orders'" class="space-y-5" wire:poll.30s="refreshOrders">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Orders Summary
                    </h3>
                    <textarea wire:model="treatment_orders_summary" rows="4" placeholder="Medications, IV fluids, consults..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 font-mono focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>

                {{-- Lab Orders --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            Laboratory Orders
                            <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $labOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderLab" class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Lab Test
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th><th class="px-5 py-2.5 text-left">Items</th>
                                <th class="px-5 py-2.5 text-center">Priority</th><th class="px-5 py-2.5 text-center">Status</th>
                                <th class="px-5 py-2.5 text-center">Billing</th><th class="px-5 py-2.5 text-right">Total</th>
                                <th class="px-5 py-2.5 text-left">Date</th><th class="px-5 py-2.5 text-center">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($labOrders as $order)
                                <tr class="hover:bg-purple-50/40 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                                    <td class="px-5 py-3">
                                        <div class="space-y-1">
                                        @foreach($order->items as $item)
                                            <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                                <span class="bg-purple-50 text-purple-700 px-2 py-0.5 rounded font-medium">{{ $item->test_name }}</span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Processing' || $is === 'Collected' ? 'bg-blue-100 text-blue-700' : ($is === 'Completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $order->priority === 'STAT' ? 'bg-red-100 text-red-700' : ($order->priority === 'Urgent' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">{{ $order->priority }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php $st = $order->status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Processing' || $st === 'Collected' ? 'bg-blue-100 text-blue-700' : ($st === 'Completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php $bs = $order->billing_status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span>
                                    </td>
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

                {{-- CSR / Supply Orders --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Supply Orders (CSR)
                            <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $csrOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderCsr" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Supply
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th><th class="px-5 py-2.5 text-left">Items</th>
                                <th class="px-5 py-2.5 text-center">Status</th><th class="px-5 py-2.5 text-center">Billing</th>
                                <th class="px-5 py-2.5 text-right">Total</th><th class="px-5 py-2.5 text-left">Date</th>
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
                                                <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-medium">{{ $item->item_name }} <span class="text-emerald-400">(x{{ $item->quantity }})</span></span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center">@php $st = $order->status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span></td>
                                    <td class="px-5 py-3 text-center">@php $bs = $order->billing_status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span></td>
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
                            Radiology Orders
                            <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $radiologyOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderRadiology" class="inline-flex items-center px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Exam
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th><th class="px-5 py-2.5 text-left">Exams</th>
                                <th class="px-5 py-2.5 text-center">Status</th><th class="px-5 py-2.5 text-center">Billing</th>
                                <th class="px-5 py-2.5 text-right">Total</th><th class="px-5 py-2.5 text-left">Date</th>
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
                                                <span class="bg-sky-50 text-sky-700 px-2 py-0.5 rounded font-medium">{{ $item->exam_name }}</span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center">@php $st = $order->status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span></td>
                                    <td class="px-5 py-3 text-center">@php $bs = $order->billing_status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span></td>
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
                            Pharmacy Orders
                            <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $pharmacyOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderPharmacy" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Medication
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th><th class="px-5 py-2.5 text-left">Medications</th>
                                <th class="px-5 py-2.5 text-center">Priority</th><th class="px-5 py-2.5 text-center">Status</th>
                                <th class="px-5 py-2.5 text-center">Billing</th><th class="px-5 py-2.5 text-right">Total</th>
                                <th class="px-5 py-2.5 text-left">Date</th><th class="px-5 py-2.5 text-center">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($pharmacyOrders as $order)
                                <tr class="hover:bg-emerald-50/40 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                                    <td class="px-5 py-3">
                                        <div class="space-y-1">
                                        @foreach($order->items as $item)
                                            <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                                <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-medium">{{ $item->item_name }} <span class="text-emerald-400">(x{{ $item->quantity }})</span></span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center">@php $pr = $order->priority; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $pr === 'STAT' ? 'bg-red-100 text-red-700' : ($pr === 'Urgent' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">{{ $pr }}</span></td>
                                    <td class="px-5 py-3 text-center">@php $st = $order->status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span></td>
                                    <td class="px-5 py-3 text-center">@php $bs = $order->billing_status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span></td>
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
                            Procedure Orders
                            <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $procedureOrders->count() }}</span>
                        </h3>
                        <button wire:click="openOrderProcedure" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Request Procedure
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Request #</th><th class="px-5 py-2.5 text-left">Procedures</th>
                                <th class="px-5 py-2.5 text-center">Status</th><th class="px-5 py-2.5 text-center">Billing</th>
                                <th class="px-5 py-2.5 text-right">Total</th><th class="px-5 py-2.5 text-left">Date</th>
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
                                                <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-medium">{{ $item->procedure_name }}</span>
                                                @php $is = $item->status; @endphp
                                                <span class="font-semibold px-1.5 py-0.5 rounded {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                            </div>
                                        @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-center">@php $st = $order->status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span></td>
                                    <td class="px-5 py-3 text-center">@php $bs = $order->billing_status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span></td>
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

            {{-- Tab: DR Schedule --}}
            <div x-show="activeTab === 'orschedule'" class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            DR Schedule
                            <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $drSchedules->count() }}</span>
                        </h3>
                        <button wire:click="openDrScheduleForm" class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Schedule Delivery
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Schedule #</th>
                                <th class="px-5 py-2.5 text-left">DR Room</th>
                                <th class="px-5 py-2.5 text-left">Delivery Type</th>
                                <th class="px-5 py-2.5 text-left">Physician</th>
                                <th class="px-5 py-2.5 text-left">Date / Time</th>
                                <th class="px-5 py-2.5 text-center">Status</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($drSchedules as $sched)
                                <tr class="hover:bg-rose-50/40 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $sched->schedule_code }}</td>
                                    <td class="px-5 py-3">
                                        <span class="bg-rose-50 text-rose-700 px-2 py-0.5 rounded font-medium text-xs">{{ $sched->drRoom?->name ?? '—' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700 text-xs">{{ $sched->delivery_type ?? $sched->delivery_description ?? '—' }}</td>
                                    <td class="px-5 py-3 text-gray-700 text-xs">{{ $sched->attendingPhysician ? 'Dr. ' . $sched->attendingPhysician->full_name : '—' }}</td>
                                    <td class="px-5 py-3 text-gray-700 text-xs">
                                        {{ $sched->scheduled_date?->format('M d, Y') }}
                                        @if($sched->start_time)<span class="text-gray-400">{{ $sched->start_time }}@if($sched->end_time)-{{ $sched->end_time }}@endif</span>@endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php $st = $sched->status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Scheduled' ? 'bg-blue-100 text-blue-700' : ($st === 'In Progress' ? 'bg-amber-100 text-amber-700' : ($st === 'Completed' ? 'bg-green-100 text-green-700' : ($st === 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'))) }}">{{ $st }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400 text-sm">No DR schedules for this patient.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tab: Disposition --}}
            <div x-show="activeTab === 'disposition'" class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 mb-6">
                        <p class="text-sm text-amber-800">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Setting a disposition other than <strong>"Pending"</strong> will mark this consultation as <strong>Completed</strong>.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Disposition <span class="text-red-500">*</span></label>
                            <select wire:model="disposition"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium">
                                <option value="Pending">Pending (Stay in OB)</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Admitted to Ward">Admitted to Ward</option>
                                <option value="Admitted to ICU">Admitted to ICU</option>
                                <option value="Discharged Home">Discharged Home</option>
                                <option value="AMA">Against Medical Advice (AMA)</option>
                                <option value="Expired">Expired</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Discharge Instructions</label>
                            <textarea wire:model="discharge_instructions" rows="4" placeholder="Follow-up instructions, medications, restrictions..."
                                      class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end mt-6">
                        <button type="button" wire:click="saveDisposition" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Confirm Disposition
                        </button>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="sticky bottom-4 bg-white rounded-xl border border-gray-200 shadow-lg p-4 flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Changes are auto-saved per section
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" wire:click="closeTreatment"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">
                        &larr; Back to List
                    </button>
                    <button type="submit"
                            class="px-6 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                        <span wire:loading.remove wire:target="saveTreatment">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Save Consultation
                        </span>
                        <span wire:loading wire:target="saveTreatment" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving…</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Order Lab Modal --}}
    @if($showOrderLabModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeTreatment"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-purple-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Lab Tests</h3>
                <button wire:click="closeTreatment" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
                        <select wire:model="orderLabPriority" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                            <option value="Routine">Routine</option><option value="Urgent">Urgent</option><option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                        <input type="text" wire:model="orderLabNotes" placeholder="e.g. Fasting..."
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    </div>
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($labTests as $test)
                    <label class="flex items-center px-4 py-2.5 hover:bg-purple-50/40 cursor-pointer transition-colors">
                        <input type="checkbox" wire:model="orderLabSelection" value="{{ $test->id }}" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 mr-3">
                        <div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900">{{ $test->code }} — {{ $test->name }}</p></div>
                        <span class="text-xs font-mono text-gray-500">&#8369;{{ number_format($test->standard_charge ?? 0, 2) }}</span>
                    </label>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No lab tests available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="requestLabOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm transition-colors">Place Lab Order</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Order CSR Modal --}}
    @if($showOrderCsrModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeTreatment"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-emerald-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Supply Items</h3>
                <button wire:click="closeTreatment" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                    <input type="text" wire:model="orderCsrNotes" placeholder="e.g. Urgent delivery..."
                           class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($supplyItems as $item)
                    <div class="flex items-center px-4 py-2.5 hover:bg-emerald-50/40 transition-colors">
                        <input type="checkbox" wire:model="orderCsrSelection" value="{{ $item->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-3">
                        <div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900">{{ $item->code }} — {{ $item->name }}</p></div>
                        <input type="number" wire:model="orderCsrQuantities.{{ $item->id }}" min="1" value="1"
                               class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 mx-2 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <span class="text-xs font-mono text-gray-500 w-20 text-right">&#8369;{{ number_format($item->unit_cost ?? 0, 2) }}</span>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No supply items available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="requestCsrOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">Place Supply Order</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Order Radiology Modal --}}
    @if($showOrderRadiologyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeTreatment"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-sky-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Radiology Exam</h3>
                <button wire:click="closeTreatment" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
                        <select wire:model="orderRadiologyPriority" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                            <option value="Routine">Routine</option><option value="Urgent">Urgent</option><option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                        <input type="text" wire:model="orderRadiologyNotes" placeholder="e.g. Contrast..."
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                    </div>
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($examTypes as $exam)
                    <label class="flex items-center px-4 py-2.5 hover:bg-sky-50/40 cursor-pointer transition-colors">
                        <input type="checkbox" wire:model="orderRadiologySelection" value="{{ $exam->id }}" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500 mr-3">
                        <div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900">{{ $exam->code }} — {{ $exam->name }}</p></div>
                        <span class="text-xs font-mono text-gray-500">&#8369;{{ number_format($exam->standard_charge ?? 0, 2) }}</span>
                    </label>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No exam types available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="requestRadiologyOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-sm transition-colors">Place Radiology Order</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Order Pharmacy Modal --}}
    @if($showOrderPharmacyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeTreatment"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-emerald-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Medications</h3>
                <button wire:click="closeTreatment" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
                        <select wire:model="orderPharmacyPriority" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="Routine">Routine</option><option value="Urgent">Urgent</option><option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                        <input type="text" wire:model="orderPharmacyNotes" placeholder="e.g. IV route..."
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>
                <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($drugs as $drug)
                    <div class="px-4 py-3 hover:bg-emerald-50/30 transition-colors">
                        <div class="flex items-center gap-3 mb-2">
                            <input type="checkbox" wire:model="orderPharmacySelection" value="{{ $drug->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $drug->generic_name }} {{ $drug->drug_strength }} @if($drug->brand_name)<span class="text-gray-400">({{ $drug->brand_name }})</span>@endif</p>
                                <p class="text-xs text-gray-400">{{ $drug->drug_code }} | {{ $drug->dosage_form ?? '' }}</p>
                            </div>
                            <input type="number" wire:model="orderPharmacyQuantities.{{ $drug->id }}" min="1" value="1"
                                   class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                        <div class="grid grid-cols-2 gap-2 pl-7">
                            <div>
                                <label class="block text-xs text-gray-500 mb-0.5">Frequency <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="orderPharmacyFrequencies.{{ $drug->id }}" placeholder="e.g. BID, TID, q8h"
                                       class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-0.5">Route <span class="text-red-400">*</span></label>
                                <select wire:model="orderPharmacyRoutes.{{ $drug->id }}" class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="">— Select —</option>
                                    <option value="PO">PO (Oral)</option><option value="IV">IV (Intravenous)</option>
                                    <option value="IM">IM (Intramuscular)</option><option value="SC">SC (Subcutaneous)</option>
                                    <option value="SL">SL (Sublingual)</option><option value="TOP">TOP (Topical)</option>
                                    <option value="PR">PR (Rectal)</option><option value="INH">INH (Inhalation)</option>
                                    <option value="NEB">NEB (Nebulization)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No drugs available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="requestPharmacyOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">Place Pharmacy Order</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Order Procedure Modal --}}
    @if($showOrderProcedureModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeTreatment"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-indigo-50/50">
                <h3 class="text-lg font-bold text-gray-900">Order Procedure</h3>
                <button wire:click="closeTreatment" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Notes</label>
                    <input type="text" wire:model="orderProcedureNotes" placeholder="e.g. Pre-op clearance..."
                           class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($procedures as $procedure)
                    <div class="flex items-center px-4 py-2.5 hover:bg-indigo-50/40 transition-colors">
                        <input type="checkbox" wire:model="orderProcedureSelection" value="{{ $procedure->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                        <div class="flex-1 min-w-0"><p class="text-sm font-medium text-gray-900">{{ $procedure->name }}</p></div>
                        <input type="number" wire:model="orderProcedureQuantities.{{ $procedure->id }}" min="1" value="1"
                               class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 mx-2 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <span class="text-xs font-mono text-gray-500 w-20 text-right">&#8369;{{ number_format($procedure->standard_charge ?? 0, 2) }}</span>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No procedures available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="requestProcedureOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">Place Procedure Order</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- DR Schedule Modal --}}
    @if($showDrScheduleModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeDrScheduleModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-rose-50/50">
                <h3 class="text-lg font-bold text-gray-900">Schedule Delivery</h3>
                <button wire:click="closeDrScheduleModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Delivery Room <span class="text-red-500">*</span></label>
                        <select wire:model="dr_room_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            <option value="">— Select Delivery Room —</option>
                            @foreach($drRooms as $room)
                                <option value="{{ $room->id }}">{{ $room->code }} — {{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Delivery Type</label>
                        <select wire:model="delivery_type" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            <option value="">— Select Delivery Type —</option>
                            <option value="NSVD">NSVD — Normal Spontaneous Vaginal Delivery</option>
                            <option value="C/S">C/S — Cesarean Section</option>
                            <option value="AVB">AVB — Assisted Vaginal Birth</option>
                            <option value="Breech">Breech Delivery</option>
                            <option value="Vacuum">Vacuum Extraction</option>
                            <option value="Forceps">Forceps Delivery</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Scheduled Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="dr_scheduled_date"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Delivery Description</label>
                        <input type="text" wire:model="delivery_description" placeholder="Brief description"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Start Time</label>
                        <input type="time" wire:model="dr_start_time"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">End Time</label>
                        <input type="time" wire:model="dr_end_time"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Attending Physician</label>
                        <select wire:model="dr_attending_physician_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            <option value="">— Select Physician —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">Dr. {{ $doc->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Anesthesiologist</label>
                        <select wire:model="dr_anesthesiologist_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            <option value="">— Select Anesthesiologist —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">Dr. {{ $doc->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Pediatrician</label>
                        <select wire:model="dr_pediatrician_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            <option value="">— Select Pediatrician —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">Dr. {{ $doc->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Midwife</label>
                        <select wire:model="dr_midwife_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            <option value="">— Select Midwife —</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Pre-Op Notes</label>
                    <textarea wire:model="dr_pre_op_notes" rows="2" placeholder="Pre-operative instructions..."
                              class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Intra-Op Notes</label>
                    <textarea wire:model="dr_intra_op_notes" rows="2" placeholder="Intra-operative notes..."
                              class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Post-Op Notes</label>
                    <textarea wire:model="dr_post_op_notes" rows="2" placeholder="Post-operative care..."
                              class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeDrScheduleModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="saveDrSchedule" class="px-5 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors">Create Schedule</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @else
    {{-- ===== DATAGRID VIEW ===== --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-gray-800">OB/GYN Consultations</h2>
        <div class="flex items-center space-x-2">
            <div class="flex items-center bg-gray-100 rounded-xl p-1 shadow-sm">
                <button wire:click="$set('showCompleted', false)"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-colors {{ !$showCompleted ? 'bg-white text-rose-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Active
                </button>
                <button wire:click="$set('showCompleted', true)"
                        class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-colors {{ $showCompleted ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Completed
                </button>
            </div>
            <button wire:click="openCreate"
                    class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Consultation
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-md bg-white">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50/90 backdrop-blur-md">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by patient name or consultation #..."
                   class="w-full sm:w-80 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 shadow-sm">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span class="font-medium">Rows:</span>
                <select wire:model.live="perPage"
                        class="block w-20 pl-3 pr-8 py-2.5 bg-white border border-gray-200 text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 sm:text-sm rounded-xl shadow-sm">
                    <option value="10">10</option><option value="25">25</option><option value="50">50</option>
                </select>
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/90 backdrop-blur-md border-b border-gray-200 text-gray-600 sticky top-0 z-10">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('consultation_code')">
                        <span class="inline-flex items-center gap-1">Consultation #</span>
                    </th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Patient</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Visit Type</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Attending</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Gest. Age</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">G/P</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Created</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($consultations as $c)
                    <tr class="hover:bg-rose-50/40 transition-colors duration-200 group">
                        <td class="px-5 py-4 font-mono font-bold text-gray-900">{{ $c->consultation_code }}</td>
                        <td class="px-5 py-4">
                            <div class="font-medium text-gray-900">{{ $c->patient->last_name }}, {{ $c->patient->first_name }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-700">{{ $c->visitType?->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-700">{{ $c->attendingPhysician ? 'Dr. ' . $c->attendingPhysician->full_name : '—' }}</td>
                        <td class="px-5 py-4 text-gray-700">{{ $c->gestational_age ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-700">{{ $c->gravida ?? '—' }}/{{ $c->para ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-500 text-xs">{{ $c->created_date?->format('M d, H:i') }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <button wire:click="openTreatment({{ $c->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                    Treatment
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <span class="text-lg font-medium text-gray-900">No {{ $showCompleted ? 'completed' : 'active' }} consultations</span>
                            <p class="mt-1 text-sm">Create a new OB/GYN consultation to begin.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-200">{{ $consultations->links() }}</div>
    </div>
    @endif

    {{-- Create Consultation Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="$set('showCreateModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-rose-50/50">
                <h3 class="text-lg font-bold text-gray-900">New OB/GYN Consultation</h3>
                <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="save" class="px-6 py-5 space-y-4">
                {{-- Patient Search --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Patient <span class="text-red-500">*</span></label>
                    @if($selected_patient_name)
                        <div class="flex items-center gap-2 px-4 py-2.5 bg-rose-50 border border-rose-200 rounded-lg">
                            <span class="text-sm font-medium text-rose-800">{{ $selected_patient_name }}</span>
                            <button type="button" wire:click="clearPatient" class="text-rose-400 hover:text-rose-600 ml-auto"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                    @else
                        <div class="relative">
                            <input type="text" wire:model.live="patient_search" placeholder="Search patient by name or code..."
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            @if($patients->isNotEmpty())
                                <div class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                    @foreach($patients as $p)
                                        <button type="button" wire:click="selectPatient({{ $p->id }})"
                                                class="block w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-rose-50 transition-colors">
                                            {{ $p->last_name }}, {{ $p->first_name }} ({{ $p->patient_code }})
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                    @error('patient_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Attending Physician</label>
                        <select wire:model="attending_physician_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            <option value="">— Select —</option>
                            @foreach($physicians as $dr)
                                <option value="{{ $dr->id }}">Dr. {{ $dr->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Visit Type</label>
                        <select wire:model="visit_type_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                            <option value="">— Select —</option>
                            @foreach($visitTypes as $vt)
                                <option value="{{ $vt->id }}">{{ $vt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Gestational Age</label>
                        <input type="text" wire:model="gestational_age" placeholder="e.g. 32 weeks"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">LMP Date</label>
                        <input type="date" wire:model="lmp_date"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">EDC Date</label>
                        <input type="date" wire:model="edc_date"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    </div>
                    <div class="grid grid-cols-5 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">G</label>
                            <input type="number" min="0" wire:model="gravida" placeholder="0"
                                   class="w-full px-2 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">P</label>
                            <input type="number" min="0" wire:model="para" placeholder="0"
                                   class="w-full px-2 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">FT</label>
                            <input type="number" min="0" wire:model="full_term" placeholder="0"
                                   class="w-full px-2 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">PT</label>
                            <input type="number" min="0" wire:model="preterm" placeholder="0"
                                   class="w-full px-2 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">A</label>
                            <input type="number" min="0" wire:model="abortion" placeholder="0"
                                   class="w-full px-2 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Living Children</label>
                        <input type="number" min="0" wire:model="living_children" placeholder="0"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Obstetrical History</label>
                    <textarea wire:model="obstetrical_history" rows="2" placeholder="Brief obstetrical history..."
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors">Create Consultation</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
