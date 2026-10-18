<div>
    @if($showDetailView && $activeAdmission)
    {{-- ═══════════════════ DETAIL / TREATMENT VIEW ═══════════════════ --}}
    <div>
        {{-- Back button --}}
        <button wire:click="backToList" class="mb-4 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Patient List
        </button>

        {{-- Patient Header Card (Premium Gradient) --}}
        <div class="bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 rounded-2xl shadow-md p-6 mb-6 text-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">{{ $activeAdmission->patient?->last_name }}, {{ $activeAdmission->patient?->first_name }}</h2>
                        <div class="flex items-center gap-3 mt-1 text-sm text-white/80">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                {{ $activeAdmission->admission_code }}
                            </span>
                            <span>{{ $activeAdmission->patient?->patient_code }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <div class="text-right">
                        <p class="text-white/70 text-xs">Admitted</p>
                        <p class="font-semibold">{{ $activeAdmission->admission_date?->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-white/70 text-xs">Room / Ward</p>
                        <p class="font-semibold">{{ $activeAdmission->wardRoom?->name ?? $activeAdmission->icuBed?->name ?? '—' }}<br><span class="text-xs text-white/70">{{ $activeAdmission->wardRoom?->ward?->name ?? $activeAdmission->icuBed?->ward?->name ?? '' }}</span></p>
                    </div>
                    <button wire:click="openTransferModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-medium transition-colors" title="Transfer to another room">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Transfer Room
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 pt-4 border-t border-white/20">
                <div><p class="text-xs text-white/70">Admission Type</p><p class="text-sm font-medium">{{ $activeAdmission->admission_type ?? '—' }}</p></div>
                <div><p class="text-xs text-white/70">Source</p><p class="text-sm font-medium">{{ $activeAdmission->admission_source ?? '—' }}</p></div>
                <div><p class="text-xs text-white/70">Attending Physician</p><p class="text-sm font-medium">{{ $activeAdmission->attendingPhysician?->full_name ?? '—' }}</p></div>
                <div><p class="text-xs text-white/70">Admitting Diagnosis</p><p class="text-sm font-medium">{{ $activeAdmission->admitting_diagnosis ?? '—' }}</p></div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 mb-5">
            <nav class="flex space-x-6">
                <button wire:click="setDetailTab(1)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $detailTab === 1 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Vitals
                    </span>
                </button>
                <button wire:click="setDetailTab(2)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $detailTab === 2 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Care Notes
                    </span>
                </button>
                <button wire:click="setDetailTab(3)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $detailTab === 3 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Orders
                    </span>
                </button>
                <button wire:click="setDetailTab(4)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $detailTab === 4 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        MAR
                    </span>
                </button>
                <button wire:click="setDetailTab(5)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $detailTab === 5 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        Discharge
                    </span>
                </button>
                <button wire:click="setDetailTab(6)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $detailTab === 6 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        OR Schedule
                    </span>
                </button>
            </nav>
        </div>

        {{-- Tab 1: Vitals --}}
        @if($detailTab === 1)
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">Vital Signs History</h3>
            <button wire:click="openAddVital" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Record Vitals
            </button>
        </div>

        @if($latestVitals->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50/90 backdrop-blur-md sticky top-0 z-10 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date/Time</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Temp (°C)</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">HR (bpm)</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">RR (/min)</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">BP (mmHg)</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">SpO₂ (%)</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Pain</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">LOC</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase">BS (mg/dL)</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-20">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($latestVitals as $v)
                    <tr class="group hover:bg-emerald-50/40 transition-colors">
                        <td class="px-3 py-2.5 text-xs text-gray-600 whitespace-nowrap">{{ $v->recorded_at?->format('M d, h:i A') }}</td>
                        <td class="px-3 py-2.5 text-center font-mono {{ $v->vital_temperature && $v->vital_temperature > 37.5 ? 'text-red-600 font-semibold' : '' }}">{{ $v->vital_temperature ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-center font-mono {{ $v->vital_heart_rate && ($v->vital_heart_rate > 100 || $v->vital_heart_rate < 60) ? 'text-red-600 font-semibold' : '' }}">{{ $v->vital_heart_rate ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-center font-mono {{ $v->vital_respiratory_rate && ($v->vital_respiratory_rate > 20 || $v->vital_respiratory_rate < 12) ? 'text-red-600 font-semibold' : '' }}">{{ $v->vital_respiratory_rate ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-center font-mono">{{ $v->vital_bp_systolic && $v->vital_bp_diastolic ? $v->vital_bp_systolic.'/'.$v->vital_bp_diastolic : '—' }}</td>
                        <td class="px-3 py-2.5 text-center font-mono {{ $v->vital_o2_saturation && $v->vital_o2_saturation < 95 ? 'text-red-600 font-semibold' : '' }}">{{ $v->vital_o2_saturation ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-center font-mono {{ $v->vital_pain_score && $v->vital_pain_score > 4 ? 'text-red-600 font-semibold' : '' }}">{{ $v->vital_pain_score ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-center text-xs">{{ $v->level_of_consciousness ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-center font-mono {{ $v->blood_sugar && ($v->blood_sugar > 180 || $v->blood_sugar < 70) ? 'text-red-600 font-semibold' : '' }}">{{ $v->blood_sugar ?? '—' }}</td>
                        <td class="px-3 py-2.5">
                            <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="editVital({{ $v->id }})" class="p-1 text-emerald-600 hover:bg-emerald-100 rounded transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteVital({{ $v->id }})" wire:confirm="Remove this vital record?" class="p-1 text-red-500 hover:bg-red-100 rounded transition-colors" title="Delete">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center py-14 text-gray-400 bg-white rounded-xl border border-gray-200 shadow-sm">
            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <p class="text-sm font-medium">No vital signs recorded yet.</p>
            <p class="text-xs mt-1">Click "Record Vitals" to add the first measurement.</p>
        </div>
        @endif
        @endif

        {{-- Tab 2: Care Notes --}}
        @if($detailTab === 2)
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">Nursing Care Notes (SOAP)</h3>
            <button wire:click="openAddCareNote" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Care Note
            </button>
        </div>

        @if($careNotes->count() > 0)
        <div class="space-y-3">
            @foreach($careNotes as $note)
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border
                            {{ $note->shift === 'AM' ? 'bg-amber-50 text-amber-700 border-amber-200' : ($note->shift === 'PM' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-50 text-slate-700 border-slate-200') }}">
                            {{ $note->shift }} Shift
                        </span>
                        <span class="text-sm text-gray-500">{{ $note->care_date?->format('M d, Y') }}</span>
                        <span class="text-xs text-gray-400">by User #{{ $note->nurse_id }}</span>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="editCareNote({{ $note->id }})" class="p-1 text-emerald-600 hover:bg-emerald-100 rounded transition-colors" title="Edit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button wire:click="deleteCareNote({{ $note->id }})" wire:confirm="Remove this care note?" class="p-1 text-red-500 hover:bg-red-100 rounded transition-colors" title="Delete">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    @if($note->subjective)
                    <div><span class="text-xs font-semibold text-gray-500 uppercase">S (Subjective)</span><p class="mt-0.5 text-gray-800">{{ $note->subjective }}</p></div>
                    @endif
                    @if($note->objective)
                    <div><span class="text-xs font-semibold text-gray-500 uppercase">O (Objective)</span><p class="mt-0.5 text-gray-800">{{ $note->objective }}</p></div>
                    @endif
                    @if($note->assessment)
                    <div><span class="text-xs font-semibold text-gray-500 uppercase">A (Assessment)</span><p class="mt-0.5 text-gray-800">{{ $note->assessment }}</p></div>
                    @endif
                    @if($note->plan)
                    <div><span class="text-xs font-semibold text-gray-500 uppercase">P (Plan)</span><p class="mt-0.5 text-gray-800">{{ $note->plan }}</p></div>
                    @endif
                </div>
                @if($note->notes)
                <div class="mt-2 pt-2 border-t border-gray-100 text-xs text-gray-500"><span class="font-medium">Additional Notes:</span> {{ $note->notes }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center py-14 text-gray-400 bg-white rounded-xl border border-gray-200 shadow-sm">
            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm font-medium">No care notes recorded yet.</p>
            <p class="text-xs mt-1">Click "Add Care Note" to create the first SOAP entry.</p>
        </div>
        @endif
        @endif

        {{-- Tab 3: Orders --}}
        @if($detailTab === 3)
        <div wire:poll.30s="refreshOrders" class="space-y-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">Orders &amp; Interventions</h3>
            <button wire:click="openOrderModal('Lab')" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Place Order
            </button>
        </div>

        @php
            $erAdmission = $activeAdmission->erAdmission;
            $erLab = $erAdmission?->labRequests ?? collect();
            $erPharm = $erAdmission?->pharmacyRequests ?? collect();
            $erCsr = $erAdmission?->csrRequests ?? collect();
            $erRad = $erAdmission?->radiologyRequests ?? collect();
            $erProc = $erAdmission?->procedureRequests ?? collect();
            $allLab = collect($erLab)->merge($admissionLabRequests);
            $allPharm = collect($erPharm)->merge($admissionPharmacyRequests);
            $allCsr = collect($erCsr)->merge($admissionCsrRequests);
            $allRad = collect($erRad)->merge($admissionRadiologyRequests);
            $allProc = collect($erProc)->merge($admissionProcedureRequests);
            $totalOrders = $allLab->count() + $allPharm->count() + $allCsr->count() + $allRad->count() + $allProc->count();
        @endphp

        @if($totalOrders > 0)

        {{-- ── Lab Orders ───────────────────────────────────────────────────── --}}
        @if($allLab->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> Laboratory Requests
                    <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $allLab->count() }}</span>
                </h4>
                <button wire:click="openOrderModal('Lab')" class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
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
                        @forelse($allLab as $order)
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
                                            <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors" title="Change status">&#8942;</button>
                                            <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                @if($is === 'Pending')
                                                <button wire:click="updateLabItemStatus({{ $item->id }}, 'Collected')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Collected</button>
                                                @endif
                                                @if($is === 'Pending' || $is === 'Collected')
                                                <button wire:click="updateLabItemStatus({{ $item->id }}, 'Processing')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Processing</button>
                                                @endif
                                                @if($is === 'Processing' || $is === 'Collected')
                                                <button wire:click="updateLabItemStatus({{ $item->id }}, 'Completed')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50 transition-colors">Completed</button>
                                                @endif
                                                <button wire:click="updateLabItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 transition-colors">Cancel</button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ ($order->priority ?? '') === 'STAT' ? 'bg-red-100 text-red-700' : (($order->priority ?? '') === 'Urgent' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">{{ $order->priority ?? 'Routine' }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $st = $order->status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Processing' || $st === 'Collected' ? 'bg-blue-100 text-blue-700' : ($st === 'Completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $bs = $order->billing_status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format((float) ($order->total_charge ?? 0), 2) }}</td>
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
        @endif

        {{-- ── Pharmacy Orders ───────────────────────────────────────────────── --}}
        @if($allPharm->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Pharmacy Requests
                    <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $allPharm->count() }}</span>
                </h4>
                <button wire:click="openOrderModal('Pharmacy')" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
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
                        @forelse($allPharm as $order)
                        <tr class="hover:bg-emerald-50/40 transition-colors">
                            <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                            <td class="px-5 py-3">
                                <div class="space-y-1">
                                @foreach($order->items as $item)
                                    <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                        <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">{{ $item->item_name }} (x{{ $item->quantity }})</span>
                                        @php $is = $item->status; @endphp
                                        <span class="font-semibold px-1.5 py-0.5 rounded whitespace-nowrap {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                        @if($is !== 'Complete' && $is !== 'Cancelled')
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors" title="Change status">&#8942;</button>
                                            <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                @if($is === 'Pending')
                                                <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Approved</button>
                                                @endif
                                                @if($is === 'Pending' || $is === 'Approved')
                                                <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Partial</button>
                                                @endif
                                                @if($is === 'Approved' || $is === 'Partial')
                                                <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50 transition-colors">Complete</button>
                                                @endif
                                                <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 transition-colors">Cancel</button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ ($order->priority ?? '') === 'STAT' ? 'bg-red-100 text-red-700' : (($order->priority ?? '') === 'Urgent' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">{{ $order->priority ?? 'Routine' }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $st = $order->status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $bs = $order->billing_status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format((float) ($order->total_amount ?? 0), 2) }}</td>
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
        @endif

        {{-- ── CSR / Supply Orders ───────────────────────────────────────────── --}}
        @if($allCsr->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> CSR / Supply Requests
                    <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $allCsr->count() }}</span>
                </h4>
                <button wire:click="openOrderModal('CSR')" class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
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
                        @forelse($allCsr as $order)
                        <tr class="hover:bg-amber-50/40 transition-colors">
                            <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                            <td class="px-5 py-3">
                                <div class="space-y-1">
                                @foreach($order->items as $item)
                                    <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                        <span class="bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">{{ $item->item_name }} (x{{ $item->quantity }})</span>
                                        @php $is = $item->status; @endphp
                                        <span class="font-semibold px-1.5 py-0.5 rounded whitespace-nowrap {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                        @if($is !== 'Complete' && $is !== 'Cancelled')
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors" title="Change status">&#8942;</button>
                                            <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                @if($is === 'Pending')
                                                <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Approved</button>
                                                @endif
                                                @if($is === 'Pending' || $is === 'Approved')
                                                <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Partial</button>
                                                @endif
                                                @if($is === 'Approved' || $is === 'Partial')
                                                <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50 transition-colors">Complete</button>
                                                @endif
                                                <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 transition-colors">Cancel</button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $st = $order->status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $bs = $order->billing_status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format((float) ($order->total_amount ?? 0), 2) }}</td>
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
                        <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400 text-sm">No CSR orders placed yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ── Radiology Orders ──────────────────────────────────────────────── --}}
        @if($allRad->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Radiology Requests
                    <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $allRad->count() }}</span>
                </h4>
                <button wire:click="openOrderModal('Radiology')" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Request Exam
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                        <th class="px-5 py-2.5 text-left">Request #</th>
                        <th class="px-5 py-2.5 text-left">Exams</th>
                        <th class="px-5 py-2.5 text-center">Priority</th>
                        <th class="px-5 py-2.5 text-center">Status</th>
                        <th class="px-5 py-2.5 text-center">Billing</th>
                        <th class="px-5 py-2.5 text-right">Total</th>
                        <th class="px-5 py-2.5 text-left">Date</th>
                        <th class="px-5 py-2.5 text-center">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($allRad as $order)
                        <tr class="hover:bg-indigo-50/40 transition-colors">
                            <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                            <td class="px-5 py-3">
                                <div class="space-y-1">
                                @foreach($order->items as $item)
                                    <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                        <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">{{ $item->exam_name }}</span>
                                        <span class="text-gray-400 text-[10px]">{{ $item->modality_name ?? '' }}</span>
                                        @php $is = $item->status; @endphp
                                        <span class="font-semibold px-1.5 py-0.5 rounded whitespace-nowrap {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                        @if($is !== 'Complete' && $is !== 'Cancelled')
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors" title="Change status">&#8942;</button>
                                            <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                @if($is === 'Pending')
                                                <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Approved</button>
                                                @endif
                                                @if($is === 'Pending' || $is === 'Approved')
                                                <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Partial</button>
                                                @endif
                                                @if($is === 'Approved' || $is === 'Partial')
                                                <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50 transition-colors">Complete</button>
                                                @endif
                                                <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 transition-colors">Cancel</button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ ($order->priority ?? '') === 'STAT' ? 'bg-red-100 text-red-700' : (($order->priority ?? '') === 'Urgent' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">{{ $order->priority ?? 'Routine' }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $st = $order->status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $bs = $order->billing_status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format((float) ($order->total_amount ?? 0), 2) }}</td>
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
                        <tr><td colspan="8" class="px-5 py-8 text-center text-gray-400 text-sm">No radiology orders placed yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ── Procedure Orders ──────────────────────────────────────────────── --}}
        @if($allProc->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Procedure Requests
                    <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $allProc->count() }}</span>
                </h4>
                <button wire:click="openOrderModal('Procedure')" class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
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
                        @forelse($allProc as $order)
                        <tr class="hover:bg-rose-50/40 transition-colors">
                            <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $order->request_code }}</td>
                            <td class="px-5 py-3">
                                <div class="space-y-1">
                                @foreach($order->items as $item)
                                    <div class="flex items-center gap-1.5 text-xs flex-wrap">
                                        <span class="bg-rose-50 text-rose-700 px-2 py-0.5 rounded font-medium whitespace-nowrap">{{ $item->procedure_name }}</span>
                                        <span class="text-gray-400 text-[10px]">{{ $item->category_name ?? '' }}</span>
                                        @php $is = $item->status; @endphp
                                        <span class="font-semibold px-1.5 py-0.5 rounded whitespace-nowrap {{ $is === 'Pending' ? 'bg-gray-100 text-gray-600' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $is }}</span>
                                        @if($is !== 'Complete' && $is !== 'Cancelled')
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors" title="Change status">&#8942;</button>
                                            <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                @if($is === 'Pending')
                                                <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Approved</button>
                                                @endif
                                                @if($is === 'Pending' || $is === 'Approved')
                                                <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Partial</button>
                                                @endif
                                                @if($is === 'Approved' || $is === 'Partial')
                                                <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50 transition-colors">Complete</button>
                                                @endif
                                                <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Cancelled')" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50 transition-colors">Cancel</button>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $st = $order->status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @php $bs = $order->billing_status; @endphp
                                <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-gray-700">&#8369;{{ number_format((float) ($order->total_amount ?? 0), 2) }}</td>
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
        @endif

        @else
        <div class="flex flex-col items-center py-14 text-gray-400 bg-white rounded-xl border border-gray-200 shadow-sm">
            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-sm font-medium">No orders yet.</p>
            <p class="text-xs mt-1">Click "Place Order" to create the first order for this patient.</p>
        </div>
        @endif
        </div>
        @endif

        {{-- Tab 4: MAR (Medication Administration Record) --}}
        @if($detailTab === 4)
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">Medication Administration Record</h3>
            <button wire:click="openAddMar" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Log Administration
            </button>
        </div>

        @if($marRecords->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50/90 backdrop-blur-md sticky top-0 z-10 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date/Time</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Drug</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Dosage</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Dose Given</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Route</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Given By</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-20">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($marRecords as $m)
                    <tr class="group hover:bg-emerald-50/40 transition-colors">
                        <td class="px-4 py-2.5 text-xs text-gray-600 whitespace-nowrap">{{ $m->administered_at?->format('M d, h:i A') }}</td>
                        <td class="px-4 py-2.5 text-sm font-medium text-gray-800">{{ $m->drug_name }}</td>
                        <td class="px-4 py-2.5 text-center text-xs font-mono text-gray-600">{{ $m->dosage ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-center text-xs font-mono text-gray-600">{{ $m->dose_given ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700">{{ $m->route ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-center text-xs text-gray-500">{{ $m->administeredBy?->name ?? 'User #'.$m->administered_by }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="editMar({{ $m->id }})" class="p-1 text-emerald-600 hover:bg-emerald-100 rounded transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="deleteMar({{ $m->id }})" wire:confirm="Remove this MAR entry?" class="p-1 text-red-500 hover:bg-red-100 rounded transition-colors" title="Delete">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center py-14 text-gray-400 bg-white rounded-xl border border-gray-200 shadow-sm">
            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm font-medium">No medication administrations recorded.</p>
            <p class="text-xs mt-1">Click "Log Administration" to record the first medication given.</p>
        </div>
        @endif
        @endif

        {{-- Tab 5: Discharge --}}
        @if($detailTab === 5)
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Discharge Patient</h3>
                <p class="text-sm text-gray-500 mb-5">Review patient information before discharging.</p>

                <div class="grid grid-cols-2 gap-4 mb-5 p-4 bg-gray-50 rounded-lg">
                    <div><span class="text-xs text-gray-500 uppercase">Patient</span><p class="text-sm font-medium">{{ $activeAdmission->patient?->last_name }}, {{ $activeAdmission->patient?->first_name }}</p></div>
                    <div><span class="text-xs text-gray-500 uppercase">Admission Code</span><p class="text-sm font-mono font-medium">{{ $activeAdmission->admission_code }}</p></div>
                    <div><span class="text-xs text-gray-500 uppercase">Admission Date</span><p class="text-sm">{{ $activeAdmission->admission_date?->format('M d, Y') }}</p></div>
                    <div><span class="text-xs text-gray-500 uppercase">Ward / Room</span><p class="text-sm">{{ $activeAdmission->wardRoom?->name ?? '—' }}</p></div>
                    <div><span class="text-xs text-gray-500 uppercase">Attending Physician</span><p class="text-sm">{{ $activeAdmission->attendingPhysician?->full_name ?? '—' }}</p></div>
                    <div><span class="text-xs text-gray-500 uppercase">Admitting Diagnosis</span><p class="text-sm">{{ $activeAdmission->admitting_diagnosis ?? '—' }}</p></div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discharge Date <span class="text-red-500">*</span></label>
                        <input wire:model="discharge_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discharge Summary / Notes</label>
                        <textarea wire:model="discharge_notes" rows="4" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Optional discharge summary, instructions, or follow-up notes..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button wire:click="confirmDischarge" wire:confirm="Discharge {{ $activeAdmission->patient?->first_name }} {{ $activeAdmission->patient?->last_name }}? This will free the ward room and mark the admission as discharged." class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Confirm Discharge
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Tab 6: OR Schedule --}}
        @if($detailTab === 6)
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800">OR Schedule</h3>
            <button wire:click="openOrScheduleForm" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Schedule OR
            </button>
        </div>

        @if($admissionOrSchedules->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50/90 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Schedule #</th>
                        <th class="px-4 py-3 text-left font-semibold">OR Room</th>
                        <th class="px-4 py-3 text-left font-semibold">Procedure</th>
                        <th class="px-4 py-3 text-left font-semibold">Surgeon</th>
                        <th class="px-4 py-3 text-left font-semibold">Date/Time</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($admissionOrSchedules as $sched)
                    <tr class="group hover:bg-emerald-50/40 transition-colors">
                        <td class="px-4 py-2.5 font-mono text-xs text-gray-600">{{ $sched->schedule_code }}</td>
                        <td class="px-4 py-2.5 text-sm">{{ $sched->orRoom?->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-sm">{{ $sched->procedure_type ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-sm">{{ $sched->surgeon?->full_name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-xs">
                            {{ $sched->scheduled_date?->format('M d, Y') }}
                            @if($sched->start_time)
                            <br><span class="text-gray-400">{{ $sched->start_time }}{{ $sched->end_time ? ' - '.$sched->end_time : '' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                {{ match($sched->status) {
                                    'Scheduled' => 'bg-blue-50 text-blue-700',
                                    'In Progress' => 'bg-amber-50 text-amber-700',
                                    'Completed' => 'bg-green-50 text-green-700',
                                    'Cancelled' => 'bg-red-50 text-red-700',
                                    default => 'bg-gray-100 text-gray-700',
                                } }}">
                                {{ $sched->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center py-14 text-gray-400 bg-white rounded-xl border border-gray-200 shadow-sm">
            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-sm font-medium">No OR schedules for this admission.</p>
            <p class="text-xs mt-1">Click "Schedule OR" to create a new OR schedule for this patient.</p>
        </div>
        @endif
        @endif
    </div>

    @else
    {{-- ═══════════════════ LIST VIEW ═══════════════════ --}}
    <div>
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">In-Patient Nursing</h2>
                <p class="text-sm text-gray-500 mt-1">Manage admitted patients, vitals, care notes, and discharges.</p>
            </div>
        </div>

        {{-- Summary Cards --}}
        @php
            $tid = request()->attributes->get('tenant_id') ?? 1;
            $admittedCount = \App\Models\Admission::where('tenant_id', $tid)->where('status', 'Admitted')->count();
            $dischargedToday = \App\Models\Admission::where('tenant_id', $tid)
                ->where('status', 'Discharged')
                ->whereDate('discharge_date', now()->toDateString())
                ->count();
            $totalInpatients = \App\Models\Admission::where('tenant_id', $tid)
                ->whereIn('status', ['Admitted'])
                ->count();

            // Dashboard: due for vitals (no vitals in last 8 hours)
            $dueVitals = \App\Models\Admission::where('tenant_id', $tid)->where('status', 'Admitted')
                ->whereDoesntHave('nursingVitals', function ($q) {
                    $q->where('recorded_at', '>=', now()->subHours(8));
                })
                ->with('patient', 'wardRoom.ward')
                ->limit(10)->get();

            // Dashboard: recent vitals (last 10)
            $recentVitals = \App\Models\Nursing\NursingVital::whereHas('admission', function ($q) use ($tid) {
                    $q->where('tenant_id', $tid)->where('status', 'Admitted');
                })
                ->with('admission.patient', 'admission.wardRoom.ward')
                ->orderBy('recorded_at', 'desc')
                ->limit(10)->get();

            // Dashboard: pending orders count
            $pendingOrders = \App\Models\LabRequest::where('admission_id', '>', 0)
                ->whereIn('status', ['Pending'])->whereHas('admission', fn($q) => $q->where('tenant_id', $tid)->where('status', 'Admitted'))
                ->count()
                + \App\Models\Pharmacy\PharmacyRequest::whereIn('status', ['Pending'])->whereHas('admission', fn($q) => $q->where('tenant_id', $tid)->where('status', 'Admitted'))
                ->count()
                + \App\Models\CsrRequest::whereIn('status', ['Pending'])->whereHas('admission', fn($q) => $q->where('tenant_id', $tid)->where('status', 'Admitted'))
                ->count();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="rounded-xl shadow-sm p-4 bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-700 text-white">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-white/80 uppercase tracking-wider">Admitted</p>
                        <p class="text-xl font-bold text-white">{{ $admittedCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl shadow-sm p-4 bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 text-white">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-white/80 uppercase tracking-wider">Occupied Beds</p>
                        <p class="text-xl font-bold text-white">{{ $totalInpatients }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl shadow-sm p-4 bg-gradient-to-br from-rose-500 via-rose-600 to-pink-700 text-white">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-white/80 uppercase tracking-wider">Due for Vitals</p>
                        <p class="text-xl font-bold text-white">{{ $dueVitals->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl shadow-sm p-4 bg-gradient-to-br from-amber-500 via-amber-600 to-orange-700 text-white">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-white/80 uppercase tracking-wider">Pending Orders</p>
                        <p class="text-xl font-bold text-white">{{ $pendingOrders }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dashboard: Due for Vitals Alert --}}
        @if($dueVitals->count() > 0)
        <div class="mb-5 bg-white rounded-xl border border-rose-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-rose-50 border-b border-rose-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-rose-800 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Patients Due for Vitals (no vitals in last 8 hours)
                </h3>
                <span class="text-xs font-medium text-rose-600 bg-rose-100 px-2 py-0.5 rounded-full">{{ $dueVitals->count() }} patients</span>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($dueVitals as $adm)
                <div class="flex items-center justify-between px-4 py-2.5 hover:bg-rose-50/30 transition-colors cursor-pointer" wire:click="viewAdmission({{ $adm->id }})">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr($adm->patient?->last_name ?? '?', 0, 1)) }}
                        </span>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $adm->patient?->last_name }}, {{ $adm->patient?->first_name }}</p>
                            <p class="text-xs text-gray-400">{{ $adm->wardRoom?->name ?? '—' }} · {{ $adm->admission_code }}</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Dashboard: Recent Vitals --}}
        @if($recentVitals->count() > 0)
        <div class="mb-5 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    Recent Vitals Recorded
                </h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($recentVitals as $v)
                <div class="flex items-center justify-between px-4 py-2 hover:bg-gray-50/30 transition-colors cursor-pointer" wire:click="viewAdmission({{ $v->admission_id }})">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-mono text-gray-600 w-16">{{ $v->recorded_at?->format('h:i A') }}</span>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $v->admission?->patient?->last_name }}, {{ $v->admission?->patient?->first_name }}</p>
                            <p class="text-xs text-gray-400">
                                Temp: {{ $v->vital_temperature ?? '—' }}°C · HR: {{ $v->vital_heart_rate ?? '—' }} · BP: {{ $v->vital_bp_systolic ?? '—' }}/{{ $v->vital_bp_diastolic ?? '—' }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ $v->admission?->wardRoom?->name ?? '' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Search & Filter --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by patient name, admission code, or diagnosis…"
                       class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm shadow-sm"/>
            </div>
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.live="showCompleted" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm text-gray-600">Show discharged</span>
                </label>
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <span class="font-medium">Rows:</span>
                    <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm rounded-xl shadow-sm">
                        <option value="10">10</option><option value="25">25</option><option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Admissions Table --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50/90 backdrop-blur-md sticky top-0 z-10 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('admission_code')">
                            <span class="inline-flex items-center gap-1">Admission # @if($sortField === 'admission_code'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span>
                        </th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ward / Room</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Physician</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Admission Date</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($admissions as $adm)
                    <tr class="group hover:bg-emerald-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $adm->admission_code }}</td>
                        <td class="px-5 py-4">
                            <span class="font-medium text-gray-800">{{ $adm->patient?->last_name }}, {{ $adm->patient?->first_name }}</span>
                            <br><span class="text-xs text-gray-400 font-mono">{{ $adm->patient?->patient_code }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-gray-800">{{ $adm->wardRoom?->name ?? '—' }}</span>
                            @if($adm->wardRoom?->ward)
                            <br><span class="text-xs text-gray-400">{{ $adm->wardRoom->ward->name }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-700">{{ $adm->attendingPhysician?->full_name ?? '—' }}</td>
                        <td class="px-5 py-4 text-center text-gray-600 text-xs">{{ $adm->admission_date?->format('M d, Y h:i A') }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border shadow-sm
                                {{ $adm->status === 'Admitted' ? 'bg-green-50 text-green-700 border-green-200/50' : 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                {{ $adm->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <button wire:click="viewAdmission({{ $adm->id }})" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-md transition-colors" title="Manage Patient">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <p class="text-sm font-medium">{{ $showCompleted ? 'No discharged patients found.' : 'No admitted patients found.' }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $showCompleted ? 'Patients discharged will appear here.' : 'Patients admitted to wards will appear here.' }}</p>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $admissions->links() }}</div>
    </div>
    @endif

    {{-- ═══════════════════ MODALS ═══════════════════ --}}

    {{-- Vitals Modal --}}
    @if($showVitalModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeVitalModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/50 backdrop-blur-sm">
                <h3 class="text-lg font-semibold text-gray-900">{{ $editingVitalId ? 'Edit' : 'Record' }} Vital Signs</h3>
                <button wire:click="closeVitalModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="saveVital" class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Temperature (°C)</label>
                        <input wire:model="vital_temperature" type="number" step="0.1" placeholder="36.5" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Heart Rate (bpm)</label>
                        <input wire:model="vital_heart_rate" type="number" placeholder="72" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Respiratory Rate (/min)</label>
                        <input wire:model="vital_respiratory_rate" type="number" placeholder="16" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">BP Systolic (mmHg)</label>
                        <input wire:model="vital_bp_systolic" type="number" placeholder="120" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">BP Diastolic (mmHg)</label>
                        <input wire:model="vital_bp_diastolic" type="number" placeholder="80" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">O₂ Saturation (%)</label>
                        <input wire:model="vital_o2_saturation" type="number" step="0.1" placeholder="98" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Pain Score (0-10)</label>
                        <input wire:model="vital_pain_score" type="number" min="0" max="10" placeholder="0" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Blood Sugar (mg/dL)</label>
                        <input wire:model="blood_sugar" type="number" step="0.1" placeholder="100" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Level of Consciousness</label>
                        <select wire:model="level_of_consciousness" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">— Select —</option>
                            <option value="Alert">Alert</option>
                            <option value="Verbal">Verbal</option>
                            <option value="Pain">Pain</option>
                            <option value="Unresponsive">Unresponsive</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="vital_notes" rows="2" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Optional notes..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeVitalModal" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">{{ $editingVitalId ? 'Update' : 'Save' }} Vitals</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Care Note Modal --}}
    @if($showCareNoteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeCareNoteModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/50 backdrop-blur-sm">
                <h3 class="text-lg font-semibold text-gray-900">{{ $editingCareNoteId ? 'Edit' : 'Add' }} Care Note (SOAP)</h3>
                <button wire:click="closeCareNoteModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="saveCareNote" class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Care Date <span class="text-red-500">*</span></label>
                        <input wire:model="care_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shift <span class="text-red-500">*</span></label>
                        <select wire:model="shift" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="AM">AM Shift</option>
                            <option value="PM">PM Shift</option>
                            <option value="Night">Night Shift</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><span class="text-xs font-bold uppercase text-gray-500">S — Subjective</span></label>
                    <textarea wire:model="subjective" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Patient-reported symptoms, complaints, concerns..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><span class="text-xs font-bold uppercase text-gray-500">O — Objective</span></label>
                    <textarea wire:model="objective" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Observed signs, vital measurements, physical findings..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><span class="text-xs font-bold uppercase text-gray-500">A — Assessment</span></label>
                    <textarea wire:model="assessment" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Nursing assessment, interpretation of findings..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><span class="text-xs font-bold uppercase text-gray-500">P — Plan</span></label>
                    <textarea wire:model="plan" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Nursing interventions, plan of care, follow-up..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                    <textarea wire:model="care_notes" rows="1" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Optional..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeCareNoteModal" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">{{ $editingCareNoteId ? 'Update' : 'Save' }} Care Note</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MAR Modal --}}
    @if($showMarModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeMarModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/50 backdrop-blur-sm">
                <h3 class="text-lg font-semibold text-gray-900">{{ $editingMarId ? 'Edit' : 'Log' }} Medication Administration</h3>
                <button wire:click="closeMarModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="saveMar" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Drug Name <span class="text-red-500">*</span></label>
                        <input wire:model="mar_drug_name" type="text" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="e.g. Amoxicillin 500mg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prescribed Dosage</label>
                        <input wire:model="mar_dosage" type="text" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="e.g. 500mg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dose Given</label>
                        <input wire:model="mar_dose_given" type="text" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="e.g. 1 tab">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Route</label>
                        <select wire:model="mar_route" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="PO">PO (Oral)</option>
                            <option value="IV">IV (Intravenous)</option>
                            <option value="IM">IM (Intramuscular)</option>
                            <option value="SC">SC (Subcutaneous)</option>
                            <option value="SL">SL (Sublingual)</option>
                            <option value="PR">PR (Rectal)</option>
                            <option value="TOP">TOP (Topical)</option>
                            <option value="INH">INH (Inhalation)</option>
                            <option value="NG">NG (Nasogastric)</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Administered At <span class="text-red-500">*</span></label>
                        <input wire:model="mar_administered_at" type="datetime-local" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="mar_notes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Optional notes..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeMarModal" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">{{ $editingMarId ? 'Update' : 'Save' }} Administration</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Discharge Modal --}}
    @if($showDischargeModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeDischargeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/50 backdrop-blur-sm">
                <h3 class="text-lg font-semibold text-gray-900">Confirm Discharge</h3>
                <button wire:click="closeDischargeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="confirmDischarge" class="px-6 py-5 space-y-4">
                <p class="text-sm text-gray-600">Discharge <strong>{{ $activeAdmission?->patient?->last_name }}, {{ $activeAdmission?->patient?->first_name }}</strong> ({{ $activeAdmission?->admission_code }})?</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discharge Date <span class="text-red-500">*</span></label>
                    <input wire:model="discharge_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discharge Notes</label>
                    <textarea wire:model="discharge_notes" rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Optional..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeDischargeModal" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">Confirm Discharge</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Order Modal — Batch Queue --}}
    @if($showOrderModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="$set('showOrderModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/50 backdrop-blur-sm">
                <h3 class="text-xl font-bold text-gray-900">
                    Place Orders
                    @if(!empty($batchOrders))
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-semibold bg-emerald-100 text-emerald-800">{{ count($batchOrders) }} queued</span>
                    @endif
                </h3>
                <button type="button" wire:click="$set('showOrderModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-6 py-5 space-y-4 max-h-[85vh] overflow-y-auto">

                {{-- Order Type Tabs --}}
                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-2">Order Type</label>
                    <div class="flex flex-wrap gap-2">
                        @php $orderTypes = ['Lab', 'Pharmacy', 'CSR', 'Radiology', 'Procedure']; @endphp
                        @php
                            $typeColors = [
                                'Lab' => ['active' => 'border-purple-500 bg-purple-50 text-purple-700', 'inactive' => 'border-gray-200 bg-white text-gray-600 hover:border-purple-300'],
                                'Pharmacy' => ['active' => 'border-emerald-500 bg-emerald-50 text-emerald-700', 'inactive' => 'border-gray-200 bg-white text-gray-600 hover:border-emerald-300'],
                                'CSR' => ['active' => 'border-amber-500 bg-amber-50 text-amber-700', 'inactive' => 'border-gray-200 bg-white text-gray-600 hover:border-amber-300'],
                                'Radiology' => ['active' => 'border-indigo-500 bg-indigo-50 text-indigo-700', 'inactive' => 'border-gray-200 bg-white text-gray-600 hover:border-indigo-300'],
                                'Procedure' => ['active' => 'border-rose-500 bg-rose-50 text-rose-700', 'inactive' => 'border-gray-200 bg-white text-gray-600 hover:border-rose-300'],
                            ];
                        @endphp
                        @foreach($orderTypes as $type)
                        @php
                            $active = $orderType === $type;
                            $colors = $typeColors[$type];
                        @endphp
                        <button type="button" wire:click="$set('orderType', '{{ $type }}')"
                            class="px-4 py-2.5 rounded-lg text-base font-semibold border-2 transition-all {{ $active ? $colors['active'] : $colors['inactive'] }}">
                            {{ $type }}
                        </button>
                        @endforeach
                    </div>
                </div>

                @if($orderType)
                {{-- Item Selection --}}
                @php
                    $items = match($orderType) {
                        'Lab' => \App\Models\LabTest::where('is_active', true)->orderBy('name')->get(),
                        'Pharmacy' => \App\Models\Pharmacy\Drug::where('is_active', true)->orderBy('generic_name')->get(),
                        'CSR' => \App\Models\Supply\Item::where('is_active', true)->orderBy('name')->get(),
                        'Radiology' => \App\Models\Radiology\RadiologyExamType::where('is_active', true)->orderBy('name')->get(),
                        'Procedure' => \App\Models\Procedures\Procedure::where('is_active', true)->orderBy('name')->get(),
                        default => collect(),
                    };
                @endphp

                @if(in_array($orderType, ['Lab', 'Pharmacy', 'Radiology']))
                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-1">Priority</label>
                    <select wire:model.live="orderPriority" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="Routine">Routine</option>
                        <option value="Urgent">Urgent</option>
                        <option value="STAT">STAT</option>
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-2">Select Items</label>
                    @if($items->count() > 0)
                    <div class="max-h-56 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100 bg-white">
                        @foreach($items as $item)
                        <div class="{{ $orderType === 'Pharmacy' ? 'px-4 py-3' : '' }} hover:bg-emerald-50/40 {{ $orderType !== 'Pharmacy' ? 'cursor-pointer transition-colors' : 'transition-colors' }}">
                            <label class="flex items-center {{ $orderType === 'Pharmacy' ? 'mb-2' : 'px-4 py-2.5' }} cursor-pointer">
                                <input type="checkbox" wire:model.live="orderSelection" value="{{ $item->id }}"
                                       class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-base font-semibold text-gray-900">
                                        {{ $item->code ?? $item->drug_code ?? $item->name ?? $item->generic_name }}
                                        @if($item instanceof \App\Models\Pharmacy\Drug)
                                        — {{ $item->generic_name }} {{ $item->drug_strength ? '('.$item->drug_strength.')' : '' }}
                                        @endif
                                    </p>
                                    @if($item instanceof \App\Models\Supply\Item)
                                    <p class="text-sm text-gray-500">{{ $item->unit?->name ?? '' }}</p>
                                    @endif
                                    @if($item instanceof \App\Models\Radiology\RadiologyExamType)
                                    <p class="text-sm text-gray-500">{{ $item->modality?->name ?? '' }}</p>
                                    @endif
                                    @if($item instanceof \App\Models\Procedures\Procedure)
                                    <p class="text-sm text-gray-500">{{ $item->category?->name ?? '' }}</p>
                                    @endif
                                </div>
                                @if(in_array($orderType, ['CSR', 'Pharmacy', 'Procedure']))
                                 <input type="number" wire:model.live="orderQuantities.{{ $item->id }}" min="1" value="1"
                                       class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-base text-center focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 mr-3"
                                       placeholder="Qty" onclick="event.stopPropagation()">
                                @endif
                                <span class="text-sm font-mono font-semibold text-gray-600 w-20 text-right">
                                    &#8369;{{ number_format((float) ($item->standard_charge ?? $item->unit_cost ?? 0), 2) }}
                                </span>
                            </label>
                            @if($orderType === 'Pharmacy')
                            <div class="grid grid-cols-2 gap-2 pl-7">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-0.5">Frequency <span class="text-red-500">*</span></label>
                                     <input type="text" wire:model.live="orderFrequencies.{{ $item->id }}"
                                           placeholder="e.g. BID, TID, q8h, PRN"
                                           class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-0.5">Route <span class="text-red-500">*</span></label>
                                     <select wire:model.live="orderRoutes.{{ $item->id }}"
                                            class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                        <option value="">— Select —</option>
                                        <option value="PO">PO (Oral)</option>
                                        <option value="IV">IV (Intravenous)</option>
                                        <option value="IM">IM (Intramuscular)</option>
                                        <option value="SC">SC (Subcutaneous)</option>
                                        <option value="SL">SL (Sublingual)</option>
                                        <option value="TOP">TOP (Topical)</option>
                                        <option value="PR">PR (Rectal)</option>
                                        <option value="INH">INH (Inhalation)</option>
                                        <option value="NEB">NEB (Nebulization)</option>
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-base text-gray-500 py-4 text-center">No items available for {{ $orderType }}.</p>
                    @endif
                    @error('orderSelection') <span class="text-sm font-medium text-red-500 mt-1">{{ $message }}</span> @enderror
                    @error('orderFrequencies.*') <span class="text-sm font-medium text-red-500 mt-1">{{ $message }}</span> @enderror
                    @error('orderRoutes.*') <span class="text-sm font-medium text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-base font-semibold text-gray-800 mb-1">Notes</label>
                    <textarea wire:model.live="orderNotes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Optional notes for the order..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button" wire:click="queueOrder" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-base font-semibold shadow-sm transition-colors flex items-center gap-2" @if(!count($orderSelection)) disabled @endif>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add {{ $orderType }} to Order
                    </button>
                </div>
                @endif

                {{-- Batch Queue Summary --}}
                @if(!empty($batchOrders))
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-base font-bold text-gray-800">Queued Orders ({{ count($batchOrders) }})</h4>
                        <button type="button" wire:click="clearBatch" class="text-sm text-red-500 hover:text-red-700 font-semibold">Clear All</button>
                    </div>
                    <div class="space-y-2">
                        @foreach($batchOrders as $idx => $batch)
                        @php
                            $batchColors = [
                                'Lab' => 'bg-purple-50 border-purple-200 text-purple-700',
                                'Pharmacy' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
                                'CSR' => 'bg-amber-50 border-amber-200 text-amber-700',
                                'Radiology' => 'bg-indigo-50 border-indigo-200 text-indigo-700',
                                'Procedure' => 'bg-rose-50 border-rose-200 text-rose-700',
                            ];
                        @endphp
                        <div class="flex items-center justify-between px-3 py-2 rounded-lg border {{ $batchColors[$batch['type']] }}">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold">{{ $batch['type'] }}</span>
                                <span class="text-sm opacity-75">{{ $batch['item_count'] }} item(s)</span>
                                @if($batch['priority'] !== 'Routine')
                                <span class="text-sm font-semibold px-1.5 py-0.5 rounded bg-white/60">{{ $batch['priority'] }}</span>
                                @endif
                                @if($batch['notes'])
                                <span class="text-sm opacity-60 truncate max-w-[200px]" title="{{ $batch['notes'] }}">— {{ $batch['notes'] }}</span>
                                @endif
                            </div>
                            <button type="button" wire:click="removeBatchOrder({{ $idx }})" class="text-sm opacity-60 hover:opacity-100 font-semibold px-2 py-1 rounded hover:bg-white/50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Footer --}}
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="$set('showOrderModal', false)" class="px-4 py-2.5 border border-gray-300 rounded-xl text-base font-semibold text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="button" wire:click="placeOrder" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-base font-semibold shadow-sm transition-colors flex items-center gap-2" @if(empty($batchOrders)) disabled @endif>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Place {{ count($batchOrders) }} Order(s)
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ OR Schedule Modal ═══ --}}
    @if($showOrScheduleModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-600 to-teal-700 rounded-t-2xl">
                <h3 class="text-lg font-bold text-white">Schedule OR Procedure</h3>
                <button wire:click="closeOrScheduleModal" class="p-1 text-white/80 hover:text-white hover:bg-white/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="saveOrSchedule" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">OR Room <span class="text-red-500">*</span></label>
                        <select wire:model="or_room_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">— Select OR Room —</option>
                            @foreach($orRooms as $room)
                            <option value="{{ $room->id }}">{{ $room->code }} — {{ $room->name }}</option>
                            @endforeach
                        </select>
                        @error('or_room_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date <span class="text-red-500">*</span></label>
                        <input wire:model="or_scheduled_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        @error('or_scheduled_date') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                            <input wire:model="or_start_time" type="time" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <input wire:model="or_end_time" type="time" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Surgeon</label>
                        <select wire:model="or_surgeon_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">— Select Surgeon —</option>
                            @foreach($orDoctors as $doc)
                            <option value="{{ $doc->id }}">{{ $doc->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Anesthesiologist</label>
                        <select wire:model="or_anesthesiologist_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">— Select Anesthesiologist —</option>
                            @foreach($orDoctors as $doc)
                            <option value="{{ $doc->id }}">{{ $doc->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Procedure Type</label>
                        <input wire:model="or_procedure_type" type="text" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="e.g. Cholecystectomy">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Procedure Description</label>
                        <textarea wire:model="or_procedure_description" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Optional description..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeOrScheduleModal" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ═══ Transfer Room Modal ═══ --}}
    @if($showTransferModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-600 to-teal-700 rounded-t-2xl">
                <h3 class="text-lg font-bold text-white">Transfer Room</h3>
                <button wire:click="closeTransferModal" class="p-1 text-white/80 hover:text-white hover:bg-white/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="saveTransfer" class="p-6 space-y-4">
                {{-- Current Room (read-only) --}}
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Current Room / Bed</p>
                    <p class="text-sm font-medium text-gray-800">
                        {{ $activeAdmission->wardRoom?->name ?? $activeAdmission->icuBed?->name ?? '—' }}
                        <span class="text-gray-400">({{ $activeAdmission->wardRoom?->ward?->name ?? $activeAdmission->icuBed?->ward?->name ?? '' }})</span>
                    </p>
                </div>

                {{-- Target Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transfer To <span class="text-red-500">*</span></label>
                    <select wire:model="transferTargetType" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="ward_room">Ward Room</option>
                        <option value="icu_bed">ICU Bed</option>
                    </select>
                    @error('transferTargetType') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Target Selection --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $transferTargetType === 'ward_room' ? 'Available Ward Rooms' : 'Available ICU Beds' }}
                        <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="transferTargetId" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="">— Select —</option>
                        @if($transferTargetType === 'ward_room')
                            @foreach($availableWardRooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }} — {{ $room->ward?->name ?? '' }} ({{ $room->roomType?->name ?? '' }})</option>
                            @endforeach
                        @else
                            @foreach($availableIcuBeds as $bed)
                            <option value="{{ $bed->id }}">{{ $bed->name }} — {{ $bed->ward?->name ?? '' }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('transferTargetId') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                    <textarea wire:model="transferReason" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="e.g. Clinical need, bed availability..."></textarea>
                    @error('transferReason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeTransferModal" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Confirm Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
