<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">MICU Patients</h2>
            <p class="text-sm text-gray-500 mt-1">Manage Medical ICU inpatient care, orders, and assessments.</p>
        </div>
    </div>

    @if($showTreatmentForm && $activeAdmissionForTreatmentId)
        @php $a = $currentAdmission; @endphp
        <div class="fixed inset-0 z-50 flex flex-col bg-gradient-to-br from-rose-50/95 via-white/95 to-pink-50/95 backdrop-blur-sm">
            <!-- Header -->
            <div class="flex-none bg-white border-b border-gray-200 shadow-sm px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button wire:click="closeTreatment" class="p-2 -ml-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        </button>
                        <div>
                            <h2 class="text-xl font-bold bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent">MICU Treatment Record</h2>
                            <p class="text-xs text-gray-500">{{ $a?->admission_code }} &middot; MICU &middot; {{ $a?->icuBed?->ward?->name }} / {{ $a?->icuBed?->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button wire:click="openDisposition" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $a?->disposition ?? 'Set Disposition' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Patient Summary Card -->
            <div class="flex-none mx-6 mt-4">
                <div class="bg-white rounded-xl border border-rose-100 shadow-sm overflow-hidden">
                    <div class="flex items-center px-5 py-3 bg-gradient-to-r from-rose-600 to-pink-600 text-white">
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
                            <div class="text-center"><p class="text-white/70">Admission</p><p class="font-semibold">{{ $a?->admission_date?->format('M d, Y') ?? '' }}</p></div>
                            <div class="text-center"><p class="text-white/70">Bed</p><p class="font-semibold">{{ $a?->icuBed?->name ?? '' }}</p></div>
                            <div class="text-center"><p class="text-white/70">Attending</p><p class="font-semibold">{{ $a?->attendingPhysician?->full_name ?? 'N/A' }}</p></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4-Tab Treatment Form -->
            <div x-data="{ treatmentTab: 'clinical-notes' }" class="flex-1 flex flex-col mx-6 mt-4 mb-6 min-h-0">
                <!-- Tabs -->
                <div class="flex-none border-b border-gray-200 bg-white rounded-t-xl">
                    <nav class="flex space-x-1 px-4">
                        <button @click="treatmentTab = 'clinical-notes'" :class="{ 'border-rose-500 text-rose-600': treatmentTab === 'clinical-notes', 'border-transparent text-gray-500 hover:text-gray-700': treatmentTab !== 'clinical-notes' }" class="pb-3 pt-3 px-4 border-b-2 font-medium text-xs transition-colors focus:outline-none">
                            <svg class="w-4 h-4 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Clinical Notes
                        </button>
                        <button @click="treatmentTab = 'orders'" :class="{ 'border-rose-500 text-rose-600': treatmentTab === 'orders', 'border-transparent text-gray-500 hover:text-gray-700': treatmentTab !== 'orders' }" class="pb-3 pt-3 px-4 border-b-2 font-medium text-xs transition-colors focus:outline-none">
                            <svg class="w-4 h-4 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Orders
                        </button>
                        <button @click="treatmentTab = 'assessments'" :class="{ 'border-rose-500 text-rose-600': treatmentTab === 'assessments', 'border-transparent text-gray-500 hover:text-gray-700': treatmentTab !== 'assessments' }" class="pb-3 pt-3 px-4 border-b-2 font-medium text-xs transition-colors focus:outline-none">
                            <svg class="w-4 h-4 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Assessments
                        </button>
                        <button @click="treatmentTab = 'disposition'" :class="{ 'border-rose-500 text-rose-600': treatmentTab === 'disposition', 'border-transparent text-gray-500 hover:text-gray-700': treatmentTab !== 'disposition' }" class="pb-3 pt-3 px-4 border-b-2 font-medium text-xs transition-colors focus:outline-none">
                            <svg class="w-4 h-4 inline mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Disposition
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
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5"><svg class="w-4 h-4 inline mr-1.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>History of Present Illness</label>
                                    <textarea wire:model="treatment_history_of_present_illness" rows="4" placeholder="Narrative of present illness, onset, duration, and associated symptoms..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 @error('treatment_history_of_present_illness') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_history_of_present_illness') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5"><svg class="w-4 h-4 inline mr-1.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>Physical Exam Findings</label>
                                    <textarea wire:model="treatment_physical_exam_findings" rows="5" placeholder="Physical exam findings by system..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 @error('treatment_physical_exam_findings') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_physical_exam_findings') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5"><svg class="w-4 h-4 inline mr-1.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Assessment</label>
                                    <textarea wire:model="treatment_assessment" rows="4" placeholder="Working diagnosis, differential diagnoses, and treatment plan..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 @error('treatment_assessment') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_assessment') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5"><svg class="w-4 h-4 inline mr-1.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Treatment Notes</label>
                                    <textarea wire:model="treatment_treatment_notes" rows="4" placeholder="Additional clinical notes, progress updates, reassessments..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 @error('treatment_treatment_notes') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_treatment_notes') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5"><svg class="w-4 h-4 inline mr-1.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>Orders Summary</label>
                                    <textarea wire:model="treatment_orders_summary" rows="4" placeholder="Medications, IV fluids, consults, and other orders..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 font-mono hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 @error('treatment_orders_summary') border-red-300 bg-red-50 @enderror"></textarea>
                                    @error('treatment_orders_summary') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-100">
                                <button type="button" wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
                                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors">
                                    <span wire:loading.remove wire:target="saveTreatment">Save Clinical Notes</span>
                                    <span wire:loading wire:target="saveTreatment" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving&hellip;</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Orders Tab -->
                    <div x-show="treatmentTab === 'orders'" x-cloak class="p-6">
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button wire:click="openOrderLab" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm">+ Lab</button>
                            <button wire:click="openOrderCsr" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm">+ CSR</button>
                            <button wire:click="openOrderRadiology" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-lg shadow-sm">+ Radiology</button>
                            <button wire:click="openOrderPharmacy" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">+ Pharmacy</button>
                            <button wire:click="openOrderProcedure" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm">+ Procedure</button>
                        </div>

                        @php
                            $labOrders = $a?->labRequests ?? collect();
                            $csrOrders = $a?->csrRequests ?? collect();
                            $radiologyOrders = $a?->radiologyRequests ?? collect();
                            $pharmacyOrders = $a?->pharmacyRequests ?? collect();
                            $procedureOrders = $a?->procedureRequests ?? collect();
                        @endphp

                        @foreach([
                            ['label' => 'Lab Orders', 'color' => 'bg-indigo-500', 'orders' => $labOrders, 'update' => 'updateLabItemStatus', 'cancel' => 'cancelLabRequest', 'code' => 'request_code', 'itemName' => 'test_name', 'total' => 'total_charge', 'items' => 'requestItems', 'statuses' => ['Pending','Collected','Processing','Completed','Cancelled']],
                            ['label' => 'CSR Orders', 'color' => 'bg-amber-500', 'orders' => $csrOrders, 'update' => 'updateCsrItemStatus', 'cancel' => 'cancelCsrRequest', 'code' => 'request_code', 'itemName' => 'item_name', 'total' => 'total_amount', 'items' => 'requestItems', 'statuses' => ['Pending','Approved','Partial','Complete','Cancelled']],
                            ['label' => 'Radiology Orders', 'color' => 'bg-cyan-500', 'orders' => $radiologyOrders, 'update' => 'updateRadiologyItemStatus', 'cancel' => 'cancelRadiologyRequest', 'code' => 'request_code', 'itemName' => 'exam_name', 'total' => 'total_amount', 'items' => 'requestItems', 'statuses' => ['Pending','Approved','Partial','Complete','Cancelled']],
                            ['label' => 'Pharmacy Orders', 'color' => 'bg-emerald-500', 'orders' => $pharmacyOrders, 'update' => 'updatePharmacyItemStatus', 'cancel' => 'cancelPharmacyRequest', 'code' => 'request_code', 'itemName' => 'drug_name', 'total' => 'total_amount', 'items' => 'requestItems', 'statuses' => ['Pending','Approved','Partial','Complete','Cancelled']],
                            ['label' => 'Procedure Orders', 'color' => 'bg-rose-500', 'orders' => $procedureOrders, 'update' => 'updateProcedureItemStatus', 'cancel' => 'cancelProcedureRequest', 'code' => 'request_code', 'itemName' => 'procedure_name', 'total' => 'total_amount', 'items' => 'requestItems', 'statuses' => ['Pending','Approved','Partial','Complete','Cancelled']],
                        ] as $g)
                            @if($g['orders']->count() > 0)
                            <div class="mb-6">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 flex items-center"><span class="w-2 h-2 rounded-full {{ $g['color'] }} mr-2"></span>{{ $g['label'] }}</h4>
                                <div class="overflow-x-auto rounded-lg border border-gray-200">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left font-semibold text-gray-600">Code</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Items</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Status</th><th class="px-3 py-2 text-left font-semibold text-gray-600">Amount</th><th class="px-3 py-2 text-center font-semibold text-gray-600">Actions</th></tr></thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach($g['orders'] as $o)
                                            <tr class="hover:bg-gray-50/50">
                                                <td class="px-3 py-2 font-mono font-medium">{{ $o->{$g['code']} }}</td>
                                                <td class="px-3 py-2">
                                                    @foreach($o->{$g['items']} as $item)
                                                        <div class="flex items-center justify-between py-0.5">
                                                            <span>{{ $item->{$g['itemName']} }}</span>
                                                            <select wire:change="{{ $g['update'] }}({{ $item->id }}, $event.target.value)" class="ml-2 text-xs border border-gray-200 rounded px-1.5 py-0.5 bg-white {{ $item->status === 'Completed' || $item->status === 'Complete' ? 'text-green-700' : ($item->status === 'Cancelled' ? 'text-red-600' : 'text-gray-700') }}">
                                                                @foreach($g['statuses'] as $st)
                                                                <option value="{{ $st }}" @selected($item->status === $st)>{{ $st }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endforeach
                                                </td>
                                                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded text-xs font-medium {{ in_array($o->status, ['Completed','Complete']) ? 'bg-green-50 text-green-700' : ($o->status === 'Cancelled' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700') }}">{{ $o->status }}</span></td>
                                                <td class="px-3 py-2 font-mono">{{ number_format($o->{$g['total']}, 2) }}</td>
                                                <td class="px-3 py-2 text-center">
                                                    @if(!in_array($o->status, ['Cancelled','Completed','Complete']))
                                                        <button wire:click="{{ $g['cancel'] }}({{ $o->id }})" class="text-red-500 hover:text-red-700 text-xs font-medium">Cancel</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        @endforeach

                        @if($labOrders->count() === 0 && $csrOrders->count() === 0 && $radiologyOrders->count() === 0 && $pharmacyOrders->count() === 0 && $procedureOrders->count() === 0)
                            <div class="text-center py-12 text-gray-500">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-base font-medium text-gray-900">No orders yet</p>
                                <p class="text-sm mt-1">Click a button above to place an order.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Assessments Tab -->
                    <div x-show="treatmentTab === 'assessments'" x-cloak class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-700">MICU Clinical Assessments</h3>
                            <button wire:click="openNewAssessment" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>New Assessment
                            </button>
                        </div>

                        @if($assessments->count() > 0)
                            <div class="space-y-4">
                                @foreach($assessments as $ass)
                                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                                    <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-xs font-medium text-gray-900">{{ $ass->assessed_at?->format('M d, Y h:i A') }}</span>
                                            <span class="text-xs text-gray-500">by {{ $ass->assessedBy?->full_name ?? 'Unknown' }}</span>
                                        </div>
                                        @if($ass->notes)
                                            <span class="text-xs text-gray-500 italic max-w-xs truncate">{{ $ass->notes }}</span>
                                        @endif
                                    </div>
                                    <div class="px-4 py-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                        @foreach($ass->values as $val)
                                        <div class="bg-gray-50 rounded px-3 py-2 border border-gray-100">
                                            <div class="text-xs text-gray-500 font-medium">{{ $val->parameter?->name ?? 'Unknown' }}</div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $val->value }} <span class="text-xs font-normal text-gray-500">{{ $val->unit }}</span></div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 text-gray-500">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <p class="text-base font-medium text-gray-900">No assessments recorded</p>
                                <p class="text-sm mt-1">Click "New Assessment" to record MICU clinical measurements.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Disposition Tab -->
                    <div x-show="treatmentTab === 'disposition'" x-cloak class="p-6">
                        <div class="max-w-lg mx-auto">
                            <div class="text-center mb-6">
                                <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Patient Disposition</h3>
                                <p class="text-sm text-gray-500">Set the clinical outcome and discharge instructions.</p>
                            </div>
                            <div class="bg-rose-50/50 rounded-xl p-5 border border-rose-100">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-semibold text-gray-700">Current Status</span>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $a?->disposition === 'Completed' ? 'bg-green-100 text-green-700' : ($a?->disposition === 'Transferred' ? 'bg-blue-100 text-blue-700' : ($a?->disposition === 'Discharged' ? 'bg-gray-100 text-gray-700' : ($a?->disposition === 'Cancelled' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700'))) }}">{{ $a?->disposition ?? 'Pending' }}</div>
                                </div>
                                @if($a?->disposition_datetime)
                                <p class="text-xs text-gray-500 mb-4">Set on {{ $a->disposition_datetime->format('M d, Y h:i A') }}</p>
                                @endif
                                <button wire:click="openDisposition" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-sm">
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
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="closeTreatment"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-rose-50 to-pink-50">
                        <h3 class="text-lg font-semibold text-gray-900">Set Disposition</h3>
                        <button wire:click="$set('showDispoModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <form wire:submit="saveDisposition" class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Disposition <span class="text-red-500">*</span></label>
                            <select wire:model="treatment_disposition" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 @error('treatment_disposition') border-red-300 bg-red-50 @enderror">
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
                            <textarea wire:model="treatment_discharge_instructions" rows="4" placeholder="Follow-up care, medications, restrictions..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="$set('showDispoModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors">Save Disposition</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- New Assessment Modal -->
            @if($showAssessmentModal)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="$set('showAssessmentModal', false)"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-rose-50 to-pink-50">
                        <h3 class="text-lg font-semibold text-gray-900">New MICU Assessment</h3>
                        <button wire:click="$set('showAssessmentModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <form wire:submit="saveAssessment" class="px-6 py-5 space-y-4">
                        @php
                            $grouped = $assessmentParameters->groupBy('category');
                        @endphp
                        @foreach($grouped as $category => $params)
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ $category ?: 'General' }}</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($params as $param)
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">{{ $param->name }}</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="text" wire:model="assessmentValues.{{ $param->id }}" placeholder="{{ $param->default_unit ?? 'Value' }}" class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded text-sm text-gray-900 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                                        @if($param->default_unit)
                                            <span class="text-xs text-gray-500 w-8">{{ $param->default_unit }}</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                            <textarea wire:model="assessmentNotes" rows="2" placeholder="Additional observations..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="$set('showAssessmentModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors">
                                <span wire:loading.remove wire:target="saveAssessment">Record Assessment</span>
                                <span wire:loading wire:target="saveAssessment" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving&hellip;</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- 5 Order Modals (same pattern as AdmissionCrud) -->
            @if($showOrderLabModal)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="$set('showOrderLabModal', false)"></div>
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
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">Create Lab Request</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($showOrderCsrModal)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="$set('showOrderCsrModal', false)"></div>
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
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition-colors">Create Supply Request</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($showOrderRadiologyModal)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="$set('showOrderRadiologyModal', false)"></div>
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
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-xl shadow-sm transition-colors">Create Radiology Request</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($showOrderPharmacyModal)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="$set('showOrderPharmacyModal', false)"></div>
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
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">Create Pharmacy Request</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($showOrderProcedureModal)
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" wire:click="$set('showOrderProcedureModal', false)"></div>
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
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors">Create Procedure Request</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    @endif

    @if(!$showTreatmentForm)
    <!-- MICU Patient List -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search admission code or patient&hellip;" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 sm:text-sm shadow-sm">
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    @foreach([['admission_code', 'Code'], ['admission_type', 'Type']] as [$field, $label])
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('{{ $field }}')">
                        <span class="inline-flex items-center gap-1">{{ $label }}<span class="text-gray-300">@if($sortField === $field){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    @endforeach
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Bed</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Attending</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Admitted</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($micuPatients as $adm)
                    <tr class="hover:bg-rose-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $adm->admission_code }}</td>
                        <td class="px-5 py-4 text-gray-600"><span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/50 shadow-sm">{{ $adm->admission_type }}</span></td>
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $adm->patient?->last_name }}, {{ $adm->patient?->first_name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $adm->icuBed?->ward?->name }} / {{ $adm->icuBed?->name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $adm->attendingPhysician?->full_name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $adm->admission_date ? $adm->admission_date->format('M d, Y h:i A') : '' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center">
                                <button wire:click="openTreatment({{ $adm->id }})" class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors" title="Open Treatment Record">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Treatment
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span class="text-lg font-medium text-gray-900">No MICU patients</span>
                                <p class="mt-1 text-sm">No patients are currently admitted to the Medical ICU.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $micuPatients->links() }}</div>
    @endif
</div>
