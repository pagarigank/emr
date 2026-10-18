<div x-data="{ activeTab: 'for-admission' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Admission Management</h2>
            <p class="text-sm text-gray-500 mt-1">Manage patient admissions from ER and active inpatients.</p>
        </div>
    </div>

    @if($showTreatmentForm && $activeAdmissionForTreatmentId)
        @php
            $a = $currentAdmission;
            $isIcu = $a && $a->icuBed;
        @endphp
        <div class="fixed inset-0 z-50 flex flex-col bg-gradient-to-br from-indigo-50/95 via-white/95 to-purple-50/95 backdrop-blur-sm">
            <!-- Header -->
            <div class="flex-none bg-white border-b border-gray-200 shadow-sm px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button wire:click="closeTreatment" class="p-2 -ml-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        </button>
                        <div>
                            <h2 class="text-xl font-bold text-white">
                                <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Admission Treatment Record</span>
                            </h2>
                            <p class="text-xs text-gray-500">{{ $a?->admission_code }} &middot; {{ $isIcu ? 'ICU' : 'Ward' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button wire:click="openDisposition" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $a?->disposition ?? 'Set Disposition' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Patient Summary Card -->
            <div class="flex-none mx-6 mt-4">
                <div class="bg-white rounded-xl border border-indigo-100 shadow-sm overflow-hidden">
                    <div class="flex items-center px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                <span class="text-lg font-bold">{{ $a?->patient?->first_name[0] ?? '?' }}{{ $a?->patient?->last_name[0] ?? '?' }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">{{ $a?->patient?->last_name }}, {{ $a?->patient?->first_name }}</p>
                                <p class="text-xs text-white/70">{{ $a?->patient?->patient_code ?? '' }} &middot; {{ $a?->admission_code }}</p>
                            </div>
                        </div>
                        <div class="ml-auto flex items-center space-x-6 text-xs">
                            <div class="text-center">
                                <p class="text-white/70">Admission</p>
                                <p class="font-semibold">{{ $a?->admission_date?->format('M d, Y') ?? '' }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-white/70">Location</p>
                                <p class="font-semibold">
                                    @if($a?->icuBed)
                                        {{ $a->icuBed->ward?->name }} / {{ $a->icuBed->name }}
                                    @elseif($a?->wardRoom)
                                        {{ $a->wardRoom->ward?->name }} / {{ $a->wardRoom->name }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-white/70">Attending</p>
                                <p class="font-semibold">{{ $a?->attendingPhysician?->full_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3-Tab Treatment Form -->
            <div x-data="{ treatmentTab: 'clinical-notes' }" class="flex-1 flex flex-col mx-6 mt-4 mb-6 min-h-0">
                <!-- Tabs -->
                <div class="flex-none border-b border-gray-200 bg-white rounded-t-xl">
                    <nav class="flex space-x-1 px-4">
                        <button @click="treatmentTab = 'clinical-notes'" :class="{ 'border-indigo-500 text-indigo-600': treatmentTab === 'clinical-notes', 'border-transparent text-gray-500 hover:text-gray-700': treatmentTab !== 'clinical-notes' }" class="pb-3 pt-3 px-4 border-b-2 font-medium text-xs transition-colors focus:outline-none">
                            <svg class="w-4 h-4 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Clinical Notes
                        </button>
                        <button @click="treatmentTab = 'orders'" :class="{ 'border-indigo-500 text-indigo-600': treatmentTab === 'orders', 'border-transparent text-gray-500 hover:text-gray-700': treatmentTab !== 'orders' }" class="pb-3 pt-3 px-4 border-b-2 font-medium text-xs transition-colors focus:outline-none">
                            <svg class="w-4 h-4 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Orders &amp; Interventions
                        </button>
                        <button @click="treatmentTab = 'disposition'" :class="{ 'border-indigo-500 text-indigo-600': treatmentTab === 'disposition', 'border-transparent text-gray-500 hover:text-gray-700': treatmentTab !== 'disposition' }" class="pb-3 pt-3 px-4 border-b-2 font-medium text-xs transition-colors focus:outline-none">
                            <svg class="w-4 h-4 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Disposition
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="flex-1 bg-white border-x border-b border-gray-200 rounded-b-xl overflow-y-auto min-h-0">
                    <!-- Clinical Notes Tab -->
                    <div x-show="treatmentTab === 'clinical-notes'" x-cloak class="p-6">
                        <form wire:submit="saveTreatment">
                            <div class="grid grid-cols-1 gap-5">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                        <span class="flex items-center"><svg class="w-4 h-4 mr-1.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>History of Present Illness</span>
                                    </label>
                                    <textarea wire:model="treatment_history_of_present_illness" rows="4" placeholder="Narrative of present illness, onset, duration, and associated symptoms..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 @error('treatment_history_of_present_illness') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_history_of_present_illness') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                        <span class="flex items-center"><svg class="w-4 h-4 mr-1.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Physical Exam Findings</span>
                                    </label>
                                    <textarea wire:model="treatment_physical_exam_findings" rows="5" placeholder="Physical exam findings by system..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 @error('treatment_physical_exam_findings') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_physical_exam_findings') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                        <span class="flex items-center"><svg class="w-4 h-4 mr-1.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Assessment</span>
                                    </label>
                                    <textarea wire:model="treatment_assessment" rows="4" placeholder="Working diagnosis, differential diagnoses, and treatment plan..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 @error('treatment_assessment') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_assessment') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                        <span class="flex items-center"><svg class="w-4 h-4 mr-1.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Treatment Notes</span>
                                    </label>
                                    <textarea wire:model="treatment_treatment_notes" rows="4" placeholder="Additional clinical notes, progress updates, reassessments..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 @error('treatment_treatment_notes') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_treatment_notes') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                        <span class="flex items-center"><svg class="w-4 h-4 mr-1.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>Orders Summary</span>
                                    </label>
                                    <textarea wire:model="treatment_orders_summary" rows="4" placeholder="Medications, IV fluids, consults, and other orders..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 font-mono hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 @error('treatment_orders_summary') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_orders_summary') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-100">
                                <button type="button" wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
                                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
                                    <span wire:loading.remove wire:target="saveTreatment">Save Clinical Notes</span>
                                    <span wire:loading wire:target="saveTreatment" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving&hellip;</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Orders & Interventions Tab -->
                    <div x-show="treatmentTab === 'orders'" x-cloak class="p-6">
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button wire:click="openOrderLab" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                + Lab
                            </button>
                            <button wire:click="openOrderCsr" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                + CSR
                            </button>
                            <button wire:click="openOrderRadiology" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-lg transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                + Radiology
                            </button>
                            <button wire:click="openOrderPharmacy" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                + Pharmacy
                            </button>
                            <button wire:click="openOrderProcedure" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/></svg>
                                + Procedure
                            </button>
                        </div>

                        @php
                            $labOrders = $a?->labRequests ?? collect();
                            $csrOrders = $a?->csrRequests ?? collect();
                            $radiologyOrders = $a?->radiologyRequests ?? collect();
                            $pharmacyOrders = $a?->pharmacyRequests ?? collect();
                            $procedureOrders = $a?->procedureRequests ?? collect();
                        @endphp

                        <!-- Lab Orders Datagrid -->
                        @if($labOrders->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center"><span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>Lab Orders</h4>
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50">
                                        <tr><th class="px-3 py-2 text-left font-semibold text-gray-600">Code</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Items</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Charges</th><th class="px-3 py-2 text-center font-semibold text-gray-600">Actions</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($labOrders as $o)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-2 font-mono font-medium">{{ $o->request_code }}</td>
                                            <td class="px-3 py-2">
                                                @foreach($o->requestItems as $item)
                                                    <div class="flex items-center justify-between py-0.5">
                                                        <span>{{ $item->test_name }}</span>
                                                        <select wire:change="updateLabItemStatus({{ $item->id }}, $event.target.value)" class="ml-2 text-xs border border-gray-200 rounded px-1.5 py-0.5 bg-white {{ $item->status === 'Completed' ? 'text-green-700' : ($item->status === 'Cancelled' ? 'text-red-600' : 'text-gray-700') }}">
                                                            <option value="Pending" @selected($item->status === 'Pending')>Pending</option>
                                                            <option value="Collected" @selected($item->status === 'Collected')>Collected</option>
                                                            <option value="Processing" @selected($item->status === 'Processing')>Processing</option>
                                                            <option value="Completed" @selected($item->status === 'Completed')>Completed</option>
                                                            <option value="Cancelled" @selected($item->status === 'Cancelled')>Cancelled</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $o->status === 'Completed' ? 'bg-green-50 text-green-700' : ($o->status === 'Cancelled' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">{{ $o->status }}</span></td>
                                            <td class="px-3 py-2 font-mono">{{ number_format($o->total_charge, 2) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if($o->status !== 'Cancelled' && $o->status !== 'Completed')
                                                    <button wire:click="cancelLabRequest({{ $o->id }})" class="text-red-500 hover:text-red-700 text-xs font-medium">Cancel</button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- CSR Orders Datagrid -->
                        @if($csrOrders->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center"><span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span>CSR Orders</h4>
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50">
                                        <tr><th class="px-3 py-2 text-left font-semibold text-gray-600">Code</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Items</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Amount</th><th class="px-3 py-2 text-center font-semibold text-gray-600">Actions</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($csrOrders as $o)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-2 font-mono font-medium">{{ $o->request_code }}</td>
                                            <td class="px-3 py-2">
                                                @foreach($o->requestItems as $item)
                                                    <div class="flex items-center justify-between py-0.5">
                                                        <span>{{ $item->item_name }} (x{{ $item->quantity }})</span>
                                                        <select wire:change="updateCsrItemStatus({{ $item->id }}, $event.target.value)" class="ml-2 text-xs border border-gray-200 rounded px-1.5 py-0.5 bg-white {{ $item->status === 'Complete' ? 'text-green-700' : ($item->status === 'Cancelled' ? 'text-red-600' : 'text-gray-700') }}">
                                                            <option value="Pending" @selected($item->status === 'Pending')>Pending</option>
                                                            <option value="Approved" @selected($item->status === 'Approved')>Approved</option>
                                                            <option value="Partial" @selected($item->status === 'Partial')>Partial</option>
                                                            <option value="Complete" @selected($item->status === 'Complete')>Complete</option>
                                                            <option value="Cancelled" @selected($item->status === 'Cancelled')>Cancelled</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $o->status === 'Complete' ? 'bg-green-50 text-green-700' : ($o->status === 'Cancelled' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">{{ $o->status }}</span></td>
                                            <td class="px-3 py-2 font-mono">{{ number_format($o->total_amount, 2) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if($o->status !== 'Cancelled' && $o->status !== 'Complete')
                                                    <button wire:click="cancelCsrRequest({{ $o->id }})" class="text-red-500 hover:text-red-700 text-xs font-medium">Cancel</button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Radiology Orders Datagrid -->
                        @if($radiologyOrders->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center"><span class="w-2 h-2 rounded-full bg-cyan-500 mr-2"></span>Radiology Orders</h4>
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50">
                                        <tr><th class="px-3 py-2 text-left font-semibold text-gray-600">Code</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Exams</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Amount</th><th class="px-3 py-2 text-center font-semibold text-gray-600">Actions</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($radiologyOrders as $o)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-2 font-mono font-medium">{{ $o->request_code }}</td>
                                            <td class="px-3 py-2">
                                                @foreach($o->requestItems as $item)
                                                    <div class="flex items-center justify-between py-0.5">
                                                        <span>{{ $item->exam_name }}</span>
                                                        <select wire:change="updateRadiologyItemStatus({{ $item->id }}, $event.target.value)" class="ml-2 text-xs border border-gray-200 rounded px-1.5 py-0.5 bg-white {{ $item->status === 'Complete' ? 'text-green-700' : ($item->status === 'Cancelled' ? 'text-red-600' : 'text-gray-700') }}">
                                                            <option value="Pending" @selected($item->status === 'Pending')>Pending</option>
                                                            <option value="Approved" @selected($item->status === 'Approved')>Approved</option>
                                                            <option value="Partial" @selected($item->status === 'Partial')>Partial</option>
                                                            <option value="Complete" @selected($item->status === 'Complete')>Complete</option>
                                                            <option value="Cancelled" @selected($item->status === 'Cancelled')>Cancelled</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $o->status === 'Complete' ? 'bg-green-50 text-green-700' : ($o->status === 'Cancelled' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">{{ $o->status }}</span></td>
                                            <td class="px-3 py-2 font-mono">{{ number_format($o->total_amount, 2) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if($o->status !== 'Cancelled' && $o->status !== 'Complete')
                                                    <button wire:click="cancelRadiologyRequest({{ $o->id }})" class="text-red-500 hover:text-red-700 text-xs font-medium">Cancel</button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Pharmacy Orders Datagrid -->
                        @if($pharmacyOrders->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>Pharmacy Orders</h4>
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50">
                                        <tr><th class="px-3 py-2 text-left font-semibold text-gray-600">Code</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Drugs</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Amount</th><th class="px-3 py-2 text-center font-semibold text-gray-600">Actions</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($pharmacyOrders as $o)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-2 font-mono font-medium">{{ $o->request_code }}</td>
                                            <td class="px-3 py-2">
                                                @foreach($o->requestItems as $item)
                                                    <div class="flex items-center justify-between py-0.5">
                                                        <span>{{ $item->drug_name }} (x{{ $item->quantity }})</span>
                                                        <select wire:change="updatePharmacyItemStatus({{ $item->id }}, $event.target.value)" class="ml-2 text-xs border border-gray-200 rounded px-1.5 py-0.5 bg-white {{ $item->status === 'Complete' ? 'text-green-700' : ($item->status === 'Cancelled' ? 'text-red-600' : 'text-gray-700') }}">
                                                            <option value="Pending" @selected($item->status === 'Pending')>Pending</option>
                                                            <option value="Approved" @selected($item->status === 'Approved')>Approved</option>
                                                            <option value="Partial" @selected($item->status === 'Partial')>Partial</option>
                                                            <option value="Complete" @selected($item->status === 'Complete')>Complete</option>
                                                            <option value="Cancelled" @selected($item->status === 'Cancelled')>Cancelled</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $o->status === 'Complete' ? 'bg-green-50 text-green-700' : ($o->status === 'Cancelled' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">{{ $o->status }}</span></td>
                                            <td class="px-3 py-2 font-mono">{{ number_format($o->total_amount, 2) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if($o->status !== 'Cancelled' && $o->status !== 'Complete')
                                                    <button wire:click="cancelPharmacyRequest({{ $o->id }})" class="text-red-500 hover:text-red-700 text-xs font-medium">Cancel</button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Procedure Orders Datagrid -->
                        @if($procedureOrders->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center"><span class="w-2 h-2 rounded-full bg-rose-500 mr-2"></span>Procedure Orders</h4>
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50">
                                        <tr><th class="px-3 py-2 text-left font-semibold text-gray-600">Code</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Procedures</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Amount</th><th class="px-3 py-2 text-center font-semibold text-gray-600">Actions</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($procedureOrders as $o)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-3 py-2 font-mono font-medium">{{ $o->request_code }}</td>
                                            <td class="px-3 py-2">
                                                @foreach($o->requestItems as $item)
                                                    <div class="flex items-center justify-between py-0.5">
                                                        <span>{{ $item->procedure_name }}</span>
                                                        <select wire:change="updateProcedureItemStatus({{ $item->id }}, $event.target.value)" class="ml-2 text-xs border border-gray-200 rounded px-1.5 py-0.5 bg-white {{ $item->status === 'Complete' ? 'text-green-700' : ($item->status === 'Cancelled' ? 'text-red-600' : 'text-gray-700') }}">
                                                            <option value="Pending" @selected($item->status === 'Pending')>Pending</option>
                                                            <option value="Approved" @selected($item->status === 'Approved')>Approved</option>
                                                            <option value="Partial" @selected($item->status === 'Partial')>Partial</option>
                                                            <option value="Complete" @selected($item->status === 'Complete')>Complete</option>
                                                            <option value="Cancelled" @selected($item->status === 'Cancelled')>Cancelled</option>
                                                        </select>
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $o->status === 'Complete' ? 'bg-green-50 text-green-700' : ($o->status === 'Cancelled' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">{{ $o->status }}</span></td>
                                            <td class="px-3 py-2 font-mono">{{ number_format($o->total_amount, 2) }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if($o->status !== 'Cancelled' && $o->status !== 'Complete')
                                                    <button wire:click="cancelProcedureRequest({{ $o->id }})" class="text-red-500 hover:text-red-700 text-xs font-medium">Cancel</button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                        @if($labOrders->count() === 0 && $csrOrders->count() === 0 && $radiologyOrders->count() === 0 && $pharmacyOrders->count() === 0 && $procedureOrders->count() === 0)
                            <div class="text-center py-12 text-gray-500">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-base font-medium text-gray-900">No orders yet</p>
                                <p class="text-sm mt-1">Click one of the buttons above to place an order.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Disposition Tab -->
                    <div x-show="treatmentTab === 'disposition'" x-cloak class="p-6">
                        <div class="max-w-lg mx-auto">
                            <div class="text-center mb-6">
                                <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Patient Disposition</h3>
                                <p class="text-sm text-gray-500">Set the clinical outcome and discharge instructions.</p>
                            </div>
                            <div class="bg-indigo-50/50 rounded-xl p-5 border border-indigo-100">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-semibold text-gray-700">Current Status</span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $a?->disposition === 'Completed' ? 'bg-green-100 text-green-700' : ($a?->disposition === 'Transferred' ? 'bg-blue-100 text-blue-700' : ($a?->disposition === 'Discharged' ? 'bg-gray-100 text-gray-700' : ($a?->disposition === 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700'))) }}">{{ $a?->disposition ?? 'Pending' }}</div>
                                </div>
                                @if($a?->disposition_datetime)
                                <p class="text-xs text-gray-500 mb-4">Set on {{ $a->disposition_datetime->format('M d, Y h:i A') }}</p>
                                @endif
                                <button wire:click="openDisposition" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-sm">
                                    {{ $a?->disposition && $a->disposition !== 'Pending' ? 'Update Disposition' : 'Set Disposition' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disposition Modal -->
            @if($showDispoModal)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeTreatment"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                        <h3 class="text-lg font-semibold text-gray-900">Set Disposition</h3>
                        <button wire:click="$set('showDispoModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <form wire:submit="saveDisposition" class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Disposition <span class="text-red-500">*</span></label>
                            <select wire:model="treatment_disposition" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 @error('treatment_disposition') border-red-300 bg-red-50 @enderror">
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Discharged">Discharged</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            @error('treatment_disposition') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Discharge Instructions</label>
                            <textarea wire:model="treatment_discharge_instructions" rows="4" placeholder="Follow-up care, medications, restrictions..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="$set('showDispoModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">Save Disposition</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Lab Order Modal -->
        @if($showOrderLabModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="$set('showOrderLabModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <h3 class="text-lg font-semibold text-gray-900">New Lab Order</h3>
                    <button wire:click="$set('showOrderLabModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form wire:submit="requestLabOrder" class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority</label>
                            <select wire:model="orderPriority" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                <option value="Routine">Routine</option>
                                <option value="Urgent">Urgent</option>
                                <option value="STAT">STAT</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Select Lab Tests <span class="text-red-500">*</span></label>
                        <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                            @foreach($labTests as $test)
                            <label class="flex items-center px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm">
                                <input type="checkbox" wire:model="selectedLabItems" value="{{ $test->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-3"><span class="font-medium text-gray-900">{{ $test->name }}</span> @if($test->labSection)<span class="text-gray-500 ml-1">({{ $test->labSection->name }})</span>@endif</span>
                                <span class="ml-auto text-xs font-mono text-gray-500">{{ number_format($test->standard_charge ?? 0, 2) }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('selectedLabItems') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea wire:model="orderNotes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showOrderLabModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
                            <span wire:loading.remove wire:target="requestLabOrder">Create Lab Request</span>
                            <span wire:loading wire:target="requestLabOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Creating&hellip;</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- CSR Order Modal -->
        @if($showOrderCsrModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="$set('showOrderCsrModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-orange-50">
                    <h3 class="text-lg font-semibold text-gray-900">New Supply Order</h3>
                    <button wire:click="$set('showOrderCsrModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form wire:submit="requestCsrOrder" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Select Supply Items <span class="text-red-500">*</span></label>
                        <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                            @foreach($csrItems as $item)
                            <label class="flex items-center px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm">
                                <input type="checkbox" wire:model="selectedCsrItems" value="{{ $item->id }}" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                <span class="ml-3"><span class="font-medium text-gray-900">{{ $item->item_name }}</span> @if($item->category)<span class="text-gray-500 ml-1">({{ $item->category->name }})</span>@endif</span>
                                <span class="ml-auto flex items-center space-x-2">
                                    <input type="number" wire:model="csrItemQuantities.{{ $item->id }}" value="1" min="1" class="w-16 px-2 py-1 text-xs border border-gray-200 rounded text-center">
                                    <span class="text-xs font-mono text-gray-500 w-16 text-right">{{ number_format($item->unit_cost ?? 0, 2) }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @error('selectedCsrItems') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea wire:model="orderNotes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showOrderCsrModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition-colors">
                            <span wire:loading.remove wire:target="requestCsrOrder">Create Supply Request</span>
                            <span wire:loading wire:target="requestCsrOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Creating&hellip;</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Radiology Order Modal -->
        @if($showOrderRadiologyModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="$set('showOrderRadiologyModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-cyan-50 to-sky-50">
                    <h3 class="text-lg font-semibold text-gray-900">New Radiology Order</h3>
                    <button wire:click="$set('showOrderRadiologyModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form wire:submit="requestRadiologyOrder" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Select Radiology Exams <span class="text-red-500">*</span></label>
                        <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                            @foreach($radiologyExams as $exam)
                            <label class="flex items-center px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm">
                                <input type="checkbox" wire:model="selectedRadiologyItems" value="{{ $exam->id }}" class="rounded border-gray-300 text-cyan-600 focus:ring-cyan-500">
                                <span class="ml-3 font-medium text-gray-900">{{ $exam->exam_name }}</span>
                                <span class="ml-auto text-xs font-mono text-gray-500">{{ number_format($exam->standard_charge ?? 0, 2) }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('selectedRadiologyItems') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea wire:model="orderNotes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showOrderRadiologyModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-xl shadow-sm transition-colors">
                            <span wire:loading.remove wire:target="requestRadiologyOrder">Create Radiology Request</span>
                            <span wire:loading wire:target="requestRadiologyOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Creating&hellip;</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Pharmacy Order Modal -->
        @if($showOrderPharmacyModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="$set('showOrderPharmacyModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50">
                    <h3 class="text-lg font-semibold text-gray-900">New Pharmacy Order</h3>
                    <button wire:click="$set('showOrderPharmacyModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form wire:submit="requestPharmacyOrder" class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority</label>
                            <select wire:model="orderPriority" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="Routine">Routine</option>
                                <option value="Urgent">Urgent</option>
                                <option value="STAT">STAT</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Select Drugs <span class="text-red-500">*</span></label>
                        <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                            @foreach($pharmacyDrugs as $drug)
                            <label class="flex items-center px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm">
                                <input type="checkbox" wire:model="selectedPharmacyItems" value="{{ $drug->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="ml-3 font-medium text-gray-900">{{ $drug->drug_name }}</span>
                                <span class="ml-auto flex items-center space-x-2">
                                    <input type="number" wire:model="pharmacyItemQuantities.{{ $drug->id }}" value="1" min="1" class="w-16 px-2 py-1 text-xs border border-gray-200 rounded text-center">
                                    <span class="text-xs font-mono text-gray-500 w-16 text-right">{{ number_format($drug->unit_price ?? 0, 2) }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @error('selectedPharmacyItems') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea wire:model="orderNotes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showOrderPharmacyModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                            <span wire:loading.remove wire:target="requestPharmacyOrder">Create Pharmacy Request</span>
                            <span wire:loading wire:target="requestPharmacyOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Creating&hellip;</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Procedure Order Modal -->
        @if($showOrderProcedureModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="$set('showOrderProcedureModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-rose-50 to-pink-50">
                    <h3 class="text-lg font-semibold text-gray-900">New Procedure Order</h3>
                    <button wire:click="$set('showOrderProcedureModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form wire:submit="requestProcedureOrder" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Select Procedures <span class="text-red-500">*</span></label>
                        <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                            @foreach($procedures as $proc)
                            <label class="flex items-center px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm">
                                <input type="checkbox" wire:model="selectedProcedureItems" value="{{ $proc->id }}" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                <span class="ml-3"><span class="font-medium text-gray-900">{{ $proc->procedure_name }}</span> @if($proc->category)<span class="text-gray-500 ml-1">({{ $proc->category->name }})</span>@endif</span>
                                <span class="ml-auto text-xs font-mono text-gray-500">{{ number_format($proc->standard_charge ?? 0, 2) }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('selectedProcedureItems') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea wire:model="orderNotes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showOrderProcedureModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors">
                            <span wire:loading.remove wire:target="requestProcedureOrder">Create Procedure Request</span>
                            <span wire:loading wire:target="requestProcedureOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Creating&hellip;</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    @endif

    @if(!$showTreatmentForm)
    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-6">
            <button @click="activeTab = 'for-admission'" :class="{ 'border-light-blue-500 text-light-blue-600': activeTab === 'for-admission', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'for-admission' }" class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors focus:outline-none">
                For Admission
                @if($pendingAdmissions->count() > 0)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">{{ $pendingAdmissions->count() }}</span>
                @endif
            </button>
            <button @click="activeTab = 'active-admissions'" :class="{ 'border-light-blue-500 text-light-blue-600': activeTab === 'active-admissions', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'active-admissions' }" class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors focus:outline-none">
                Active Admissions
            </button>
        </nav>
    </div>

    <!-- Tab: For Admission -->
    <div x-show="activeTab === 'for-admission'">
        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Encounter #</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient Name</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Disposition</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ER Attending</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Disposition Date</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($pendingAdmissions as $er)
                        <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                            <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $er->admission_code }}</td>
                            <td class="px-5 py-4 text-gray-800 font-medium">{{ $er->patient?->last_name }}, {{ $er->patient?->first_name }}</td>
                            <td class="px-5 py-4">
                                @php
                                    $isIcu = str_contains($er->disposition ?? '', 'ICU');
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $isIcu ? 'bg-purple-50 text-purple-700 border border-purple-200/50' : 'bg-blue-50 text-blue-700 border border-blue-200/50' }} shadow-sm">
                                    {{ $er->disposition }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $er->attendingPhysician?->full_name }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $er->disposition_datetime ? $er->disposition_datetime->format('M d, Y h:i A') : '' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center">
                                    <button wire:click="openAdmit({{ $er->id }})" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                        Admit
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span class="text-lg font-medium text-gray-900">No pending admissions</span>
                                    <p class="mt-1 text-sm">No ER patients are waiting for admission.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Active Admissions -->
    <div x-show="activeTab === 'active-admissions'">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search admission code or patient&hellip;" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm shadow-sm">
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span class="font-medium">Rows:</span>
                <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm rounded-xl shadow-sm">
                    <option value="10">10</option><option value="25">25</option><option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                    <tr>
                        @foreach([['admission_code', 'Admission Code'], ['admission_type', 'Type']] as [$field, $label])
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('{{ $field }}')">
                            <span class="inline-flex items-center gap-1">{{ $label }}<span class="text-gray-300">@if($sortField === $field){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                        </th>
                        @endforeach
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient Name</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ward / Room</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Attending</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Admission Date</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($activeAdmissions as $adm)
                        <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                            <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $adm->admission_code }}</td>
                            <td class="px-5 py-4 text-gray-600">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-700 border border-gray-200/50 shadow-sm">{{ $adm->admission_type }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-800 font-medium">{{ $adm->patient?->last_name }}, {{ $adm->patient?->first_name }}</td>
                            <td class="px-5 py-4 text-gray-600">
                                @if($adm->wardRoom)
                                    {{ $adm->wardRoom->ward?->name }} / {{ $adm->wardRoom->name }}
                                @elseif($adm->icuBed)
                                    {{ $adm->icuBed->ward?->name }} / {{ $adm->icuBed->name }}
                                @endif
                            </td>
                            <td class="px-5 py-4 text-gray-600">{{ $adm->attendingPhysician?->full_name }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $adm->admission_date ? $adm->admission_date->format('M d, Y h:i A') : '' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="viewDetail({{ $adm->id }})" class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button wire:click="openTreatment({{ $adm->id }})" class="inline-flex items-center px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors" title="Open Treatment Record">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Treatment
                                    </button>
                                    <button wire:click="confirmDischarge({{ $adm->id }})" class="p-1.5 text-amber-600 hover:bg-amber-100 rounded-md transition-colors" title="Discharge">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-lg font-medium text-gray-900">No active admissions</span>
                                    <p class="mt-1 text-sm">There are no currently admitted patients.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $activeAdmissions->links() }}</div>
    </div>

    <!-- Admit Modal -->
    @if($showAdmitModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Admit Patient</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveAdmit" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Admission Code</label>
                        <input type="text" value="Auto-generated" disabled class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label for="admission_date" class="block text-sm font-medium text-gray-700 mb-1.5">Admission Date/Time <span class="text-red-500">*</span></label>
                        <input wire:model="admission_date" id="admission_date" type="datetime-local" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('admission_date') border-red-300 bg-red-50 @enderror">
                        @error('admission_date') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="admission_type" class="block text-sm font-medium text-gray-700 mb-1.5">Admission Type <span class="text-red-500">*</span></label>
                        <select wire:model="admission_type" id="admission_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('admission_type') border-red-300 bg-red-50 @enderror">
                            <option value="Emergency">Emergency</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Elective">Elective</option>
                            <option value="Routine">Routine</option>
                        </select>
                        @error('admission_type') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admission_source" class="block text-sm font-medium text-gray-700 mb-1.5">Admission Source <span class="text-red-500">*</span></label>
                        <select wire:model="admission_source" id="admission_source" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('admission_source') border-red-300 bg-red-50 @enderror">
                            <option value="ER">ER</option>
                            <option value="OPD">OPD</option>
                            <option value="Referral">Referral</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                        @error('admission_source') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="attending_physician_id" class="block text-sm font-medium text-gray-700 mb-1.5">Attending Physician</label>
                        <select wire:model="attending_physician_id" id="attending_physician_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('attending_physician_id') border-red-300 bg-red-50 @enderror">
                            <option value="">-- Select --</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->full_name }}</option>
                            @endforeach
                        </select>
                        @error('attending_physician_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="admitting_physician_id" class="block text-sm font-medium text-gray-700 mb-1.5">Admitting Physician</label>
                        <select wire:model="admitting_physician_id" id="admitting_physician_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('admitting_physician_id') border-red-300 bg-red-50 @enderror">
                            <option value="">-- Select --</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->full_name }}</option>
                            @endforeach
                        </select>
                        @error('admitting_physician_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                @php
                    $erRecord = $admitErAdmissionId ? \App\Models\Er\ErAdmission::find($admitErAdmissionId) : null;
                    $isIcuDisposition = $erRecord && str_contains($erRecord->disposition ?? '', 'ICU');
                @endphp

                @if($erRecord && $isIcuDisposition)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="icu_ward_id" class="block text-sm font-medium text-gray-700 mb-1.5">ICU Ward</label>
                        <select wire:model.live="icu_ward_id" id="icu_ward_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">-- Select ICU Ward --</option>
                            @foreach($icuWards as $w)
                                <option value="{{ $w->id }}">[{{ $w->code }}] {{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="icu_bed_id" class="block text-sm font-medium text-gray-700 mb-1.5">ICU Bed</label>
                        <select wire:model="icu_bed_id" id="icu_bed_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('icu_bed_id') border-red-300 bg-red-50 @enderror">
                            <option value="">-- Select Bed --</option>
                            @foreach($icuBeds->where('ward_id', $icu_ward_id) as $b)
                                <option value="{{ $b->id }}">[{{ $b->code }}] {{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('icu_bed_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                @elseif($erRecord)
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="ward_id" class="block text-sm font-medium text-gray-700 mb-1.5">Ward</label>
                        <select wire:model.live="ward_id" id="ward_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">-- Select Ward --</option>
                            @foreach($wards as $w)
                                <option value="{{ $w->id }}">[{{ $w->code }}] {{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="ward_room_id" class="block text-sm font-medium text-gray-700 mb-1.5">Room</label>
                        <select wire:model="ward_room_id" id="ward_room_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('ward_room_id') border-red-300 bg-red-50 @enderror">
                            <option value="">-- Select Room --</option>
                            @foreach($wardRooms->where('ward_id', $ward_id) as $r)
                                <option value="{{ $r->id }}">[{{ $r->code }}] {{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('ward_room_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endif

                <div>
                    <label for="admitting_diagnosis" class="block text-sm font-medium text-gray-700 mb-1.5">Admitting Diagnosis</label>
                    <textarea wire:model="admitting_diagnosis" id="admitting_diagnosis" rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('admitting_diagnosis') border-red-300 bg-red-50 @enderror"></textarea>
                    @error('admitting_diagnosis') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea wire:model="notes" id="notes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('notes') border-red-300 bg-red-50 @enderror"></textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="saveAdmit">Confirm Admission</span>
                        <span wire:loading wire:target="saveAdmit" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Processing&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Discharge Confirmation Modal -->
    @if($showDischargeModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                    <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Discharge Patient</h3>
                <p class="text-sm text-gray-500 mb-6">This will mark the patient as discharged and free up the assigned room/bed.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="discharge" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Discharge</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detail Modal -->
    @if($showDetailModal && $detailAdmissionId)
        @php $detail = \App\Models\Admission::with(['patient', 'erAdmission', 'attendingPhysician', 'admittingPhysician', 'wardRoom.ward', 'icuBed.ward'])->find($detailAdmissionId); @endphp
        @if($detail)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50/50">
                    <h3 class="text-lg font-semibold text-gray-900">Admission Details</h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-500 font-medium">Admission Code:</span><br><span class="text-gray-900 font-mono font-semibold">{{ $detail->admission_code }}</span></div>
                        <div><span class="text-gray-500 font-medium">Status:</span><br><span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200/50">{{ $detail->status }}</span></div>
                        <div><span class="text-gray-500 font-medium">Patient:</span><br><span class="text-gray-900">{{ $detail->patient?->last_name }}, {{ $detail->patient?->first_name }}</span></div>
                        <div><span class="text-gray-500 font-medium">Admission Type:</span><br><span class="text-gray-900">{{ $detail->admission_type }}</span></div>
                        <div><span class="text-gray-500 font-medium">Admission Source:</span><br><span class="text-gray-900">{{ $detail->admission_source }}</span></div>
                        <div><span class="text-gray-500 font-medium">Admission Date:</span><br><span class="text-gray-900">{{ $detail->admission_date ? $detail->admission_date->format('M d, Y h:i A') : '' }}</span></div>
                        <div><span class="text-gray-500 font-medium">Days Stayed:</span><br><span class="text-teal-700 font-bold">{{ $detail->days_stayed }} {{ $detail->days_stayed === 1 ? 'day' : 'days' }}@if($detail->status === 'Admitted' || !$detail->discharge_date) <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60 ml-0.5">to date</span>@endif</span></div>
                        <div><span class="text-gray-500 font-medium">Attending Physician:</span><br><span class="text-gray-900">{{ $detail->attendingPhysician?->full_name }}</span></div>
                        <div><span class="text-gray-500 font-medium">Admitting Physician:</span><br><span class="text-gray-900">{{ $detail->admittingPhysician?->full_name }}</span></div>
                        <div class="col-span-2"><span class="text-gray-500 font-medium">Location:</span><br><span class="text-gray-900">
                            @if($detail->wardRoom)
                                {{ $detail->wardRoom->ward?->name }} - {{ $detail->wardRoom->name }}
                            @elseif($detail->icuBed)
                                {{ $detail->icuBed->ward?->name }} - {{ $detail->icuBed->name }}
                            @else
                                Not assigned
                            @endif
                        </span></div>
                        @if($detail->admitting_diagnosis)
                        <div class="col-span-2"><span class="text-gray-500 font-medium">Admitting Diagnosis:</span><br><span class="text-gray-900">{{ $detail->admitting_diagnosis }}</span></div>
                        @endif
                        @if($detail->discharge_date)
                        <div class="col-span-2"><span class="text-gray-500 font-medium">Discharge Date:</span><br><span class="text-gray-900">{{ $detail->discharge_date->format('M d, Y h:i A') }}</span></div>
                        @endif
                    </div>
                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
    @endif
</div>
