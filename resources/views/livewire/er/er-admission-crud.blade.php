<div>
    @if($showTreatmentForm && $activeAdmission)
    {{-- ===== TREATMENT FORM VIEW ===== --}}
    <div x-data="{ activeTab: 'clinical' }">
        {{-- Patient Header Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-lg mb-6 overflow-hidden transform transition-all hover:shadow-xl">
            <div class="bg-gradient-to-br from-light-blue-600 via-light-blue-700 to-indigo-800 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-white">ER Treatment Record</h2>
                        <p class="text-light-blue-100 text-sm mt-1">Encounter #{{ $activeAdmission->admission_code }}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($activeAdmission->triageRecord && $activeAdmission->triageRecord->acuityLevel)
                            <span class="px-3 py-1.5 rounded-lg text-sm font-bold text-white shadow" style="background-color: {{ $activeAdmission->triageRecord->acuityLevel->color_code ?? '#6b7280' }}">
                                {{ $activeAdmission->triageRecord->acuityLevel->name }} ({{ $activeAdmission->triageRecord->acuityLevel->code }})
                            </span>
                        @endif
                        <span class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-white/20 text-white">
                            {{ $activeAdmission->status }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 px-6 py-4 bg-gray-50/80 border-b border-gray-100">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeAdmission->patient->last_name }}, {{ $activeAdmission->patient->first_name }} {{ $activeAdmission->patient->middle_name }}</p>
                    @if($activeAdmission->patient->date_of_birth)
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($activeAdmission->patient->date_of_birth)->age }} yrs old
                        @if($activeAdmission->patient->gender) / {{ $activeAdmission->patient->gender->name }} @endif
                        </p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Bed / Zone</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeAdmission->bed?->name ?? 'Unassigned' }}</p>
                    @if($activeAdmission->bed?->zone)
                        <p class="text-xs text-gray-500">{{ $activeAdmission->bed->zone->name }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Attending Physician</p>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeAdmission->attendingPhysician ? 'Dr. ' . $activeAdmission->attendingPhysician->full_name : 'Unassigned' }}</p>
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Arrival / Elapsed</p>
                        @php
                            $elapsedHours = $activeAdmission->admission_datetime ? $activeAdmission->admission_datetime->diffInHours(now()) : 0;
                            $remainingHours = max(0, 24 - $elapsedHours);
                        @endphp
                        @if($is_oecb)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $elapsedHours >= 24 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-teal-100 text-teal-800 border border-teal-300' }}">
                                <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $elapsedHours >= 24 ? '24h Limit (Admit/Transfer)' : ($remainingHours . 'h left in OECB') }}
                            </span>
                        @endif
                    </div>
                    <p class="text-sm font-bold text-gray-900 mt-0.5">{{ $activeAdmission->admission_datetime->format('M d, Y H:i') }}</p>
                    <p class="text-xs text-gray-500">{{ $activeAdmission->admission_datetime->diffForHumans() }} ({{ $elapsedHours }}h)</p>
                </div>
            </div>
            @if($activeAdmission->triageRecord)
            <div class="grid grid-cols-4 md:grid-cols-8 gap-3 px-6 py-3 bg-gray-50/40 border-b border-gray-100 text-center">
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Temp</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeAdmission->triageRecord->vital_temperature ?? '—' }}&deg;</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">HR</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeAdmission->triageRecord->vital_heart_rate ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">RR</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeAdmission->triageRecord->vital_respiratory_rate ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">BP</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeAdmission->triageRecord->vital_bp_systolic ?? '—' }}/{{ $activeAdmission->triageRecord->vital_bp_diastolic ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">SpO2</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeAdmission->triageRecord->vital_o2_saturation ?? '—' }}%</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Pain</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeAdmission->triageRecord->vital_pain_score ?? '—' }}/10</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">LOC</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeAdmission->triageRecord->level_of_consciousness ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-gray-500 uppercase">Arrival</p>
                    <p class="text-sm font-bold text-gray-800">{{ $activeAdmission->arrival_mode ?? '—' }}</p>
                </div>
            </div>
            @endif
            {{-- Initial Diagnosis --}}
            @if($activeAdmission->initial_diagnosis)
            <div class="px-6 py-3 bg-amber-50/60 border-b border-amber-100">
                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Initial Diagnosis</p>
                <p class="text-sm text-amber-900 mt-0.5">{{ $activeAdmission->initial_diagnosis }}</p>
            </div>
            @endif
            {{-- OECB Toggle --}}
            <div class="px-6 py-3 bg-sky-50/60 border-b border-sky-100">
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" wire:model.live="is_oecb" class="w-4 h-4 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <div>
                            <span class="text-sm font-bold text-sky-800">OECB (Outpatient Emergency Care Benefit)</span>
                            <p class="text-xs text-sky-600">PhilHealth Outpatient Emergency Care Benefit &amp; Essential Emergency Care List (EECL Annex D)</p>
                        </div>
                    </label>
                    <div class="flex items-center gap-2">
                        @if($is_oecb)
                        <button type="button" wire:click="autoSyncEeclFromOrders"
                                class="inline-flex items-center px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-300 rounded-lg hover:bg-emerald-100 transition-colors shadow-sm"
                                title="Auto-pull mapped EECL items from laboratory, pharmacy, CSR supplies, and procedures">
                            <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Auto-Sync Clinical Orders
                        </button>
                        @endif
                        @if($is_oecb && count($oecbEntries))
                            <div class="text-right text-xs text-sky-700 bg-sky-100 px-2.5 py-1 rounded-lg font-semibold border border-sky-200">
                                {{ count($oecbEntries) }} item{{ count($oecbEntries) === 1 ? '' : 's' }}
                                &mdash; Total Benefit: &#8369;{{ number_format(collect($oecbEntries)->sum('total_amount'), 2) }}
                            </div>
                        @endif
                    </div>
                </div>
                @if($is_oecb)
                <div class="mt-3 space-y-3">
                    @foreach($oecbEntries as $index => $entry)
                    <div class="flex items-start gap-3 p-3 bg-white rounded-lg border border-sky-200 shadow-sm">
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-sky-700 mb-1">
                                    {{ !empty($entry['eecl_item_id']) ? 'EECL Item / Description' : 'Core Presentation / Package' }} <span class="text-red-500">*</span>
                                </label>
                                @if(!empty($entry['eecl_item_id']))
                                    <div class="flex items-center">
                                        <input type="text" wire:model="oecbEntries.{{ $index }}.core_presentation"
                                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-900" readonly>
                                    </div>
                                @else
                                    <select wire:model.live="oecbEntries.{{ $index }}.core_presentation"
                                            class="w-full px-3 py-2 bg-white border border-sky-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 @error('oecbEntries.{{ $index }}.core_presentation') border-red-300 bg-red-50 @enderror">
                                        <option value="">— Select Core Presentation —</option>
                                        @foreach($corePresentations as $cp)
                                            <option value="{{ $cp }}">{{ $cp }}</option>
                                        @endforeach
                                    </select>
                                    @error('oecbEntries.{{ $index }}.core_presentation') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-sky-700 mb-1">Code &amp; Qty</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="text" wire:model="oecbEntries.{{ $index }}.oecb_package_code" placeholder="Code" readonly
                                           class="w-2/3 px-2 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono text-gray-900">
                                    <input type="number" step="1" min="1" wire:model.live="oecbEntries.{{ $index }}.quantity" placeholder="Qty"
                                           class="w-1/3 px-2 py-2 bg-white border border-gray-200 rounded-lg text-xs text-center font-bold text-gray-900">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-sky-700 mb-1">Total Fee (&#8369;)</label>
                                <input type="number" step="0.01" min="0" wire:model="oecbEntries.{{ $index }}.total_amount" placeholder="0.00" readonly
                                       class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-semibold text-gray-900">
                            </div>
                        </div>
                        <button type="button" wire:click="removeOecbEntry({{ $index }})" class="mt-6 p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Remove entry">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                    @endforeach
                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" wire:click="addOecbEntry" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-sky-700 bg-white border border-sky-300 rounded-lg hover:bg-sky-50 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Manual OECB Package
                        </button>
                        <button type="button" wire:click="autoSyncEeclFromOrders" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-white border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Auto-Sync Clinical Orders
                        </button>
                        @if($treatingId && count($oecbEntries) > 0)
                        <a href="{{ route('er.admission.eeclpdf', $treatingId) }}" target="_blank"
                           class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 border border-teal-700 rounded-lg hover:bg-teal-700 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print EECL (Annex D)
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-6" aria-label="Tabs">
                    <button @click="activeTab = 'clinical'" :class="activeTab === 'clinical' ? 'border-light-blue-500 text-light-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Clinical Notes
                    </button>
                    <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'border-light-blue-500 text-light-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Orders &amp; Interventions
                    </button>
                    <button @click="activeTab = 'disposition'" :class="activeTab === 'disposition' ? 'border-light-blue-500 text-light-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Disposition
                    </button>
                    <button @click="activeTab = 'orschedule'" :class="activeTab === 'orschedule' ? 'border-light-blue-500 text-light-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 inline-flex items-center gap-2 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        OR Schedule
                    </button>
                </nav>
            </div>
        </div>

        <form wire:submit="saveTreatment">
            {{-- Tab: Clinical Notes --}}
            <div x-show="activeTab === 'clinical'" class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-light-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        History of Present Illness
                    </h3>
                    <textarea wire:model="treatment_history_of_present_illness" rows="4" placeholder="Detailed narrative of the present illness, onset, duration, progression, and associated symptoms..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('treatment_history_of_present_illness') border-red-300 bg-red-50 @enderror"></textarea>
                    @error('treatment_history_of_present_illness') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-light-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        Physical Examination Findings
                    </h3>
                    <textarea wire:model="treatment_physical_exam_findings" rows="5" placeholder="Document physical exam findings by system (General, HEENT, Cardiovascular, Respiratory, Abdomen, Extremities, Neurologic, Skin)..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('treatment_physical_exam_findings') border-red-300 bg-red-50 @enderror"></textarea>
                    @error('treatment_physical_exam_findings') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-light-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Assessment &amp; Plan
                    </h3>
                    <textarea wire:model="treatment_assessment" rows="4" placeholder="Working diagnosis, differential diagnoses, and treatment plan..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('treatment_assessment') border-red-300 bg-red-50 @enderror"></textarea>
                    @error('treatment_assessment') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-light-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        Treatment Notes
                    </h3>
                    <textarea wire:model="treatment_treatment_notes" rows="4" placeholder="Additional clinical notes, progress updates, reassessments, and other observations..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('treatment_treatment_notes') border-red-300 bg-red-50 @enderror"></textarea>
                    @error('treatment_treatment_notes') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Tab: Orders & Interventions --}}
            <div x-show="activeTab === 'orders'" class="space-y-5" wire:poll.30s="refreshOrders">
                {{-- Manual Orders Summary --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-light-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Orders Summary (Medications, IV Fluids, Consults, etc.)
                    </h3>
                    <textarea wire:model="treatment_orders_summary" rows="4" placeholder="e.g.&#10;Medications:&#10;- Morphine 4mg IV push for pain&#10;- Ondansetron 4mg IV for nausea&#10;&#10;IV Fluids:&#10;- Normal Saline 1L bolus @ 500mL/hr&#10;&#10;Procedures:&#10;- Laceration repair, left forearm (4-0 Nylon, 5 sutures)&#10;&#10;Consults:&#10;- Cardiology consult called 14:30, Dr. Santos responding..."
                              class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 font-mono leading-relaxed transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('treatment_orders_summary') border-red-300 bg-red-50 @enderror"></textarea>
                    @error('treatment_orders_summary') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
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
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors" title="Change status">&#8942;</button>
                                                    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Approved</button>
                                                        @endif
                                                        @if($is === 'Approved' || $is === 'Partial')
                                                        <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Partial</button>
                                                        <button wire:click="updateCsrItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50 transition-colors">Complete</button>
                                                        @endif
                                                        @if($is === 'Pending')
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
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors" title="Change status">&#8942;</button>
                                                    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Approved</button>
                                                        @endif
                                                        @if($is === 'Approved' || $is === 'Partial')
                                                        <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Partial</button>
                                                        <button wire:click="updateRadiologyItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50 transition-colors">Complete</button>
                                                        @endif
                                                        @if($is === 'Pending')
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
                                        @php $st = $order->status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php $bs = $order->billing_status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span>
                                    </td>
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
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors" title="Change status">&#8942;</button>
                                                    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                        @if($is === 'Pending')
                                                        <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Approved</button>
                                                        @endif
                                                        @if($is === 'Approved' || $is === 'Partial')
                                                        <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Partial</button>
                                                        <button wire:click="updatePharmacyItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50 transition-colors">Complete</button>
                                                        @endif
                                                        @if($is === 'Pending')
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
                                        @php $pr = $order->priority; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $pr === 'STAT' ? 'bg-red-100 text-red-700' : ($pr === 'Urgent' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">{{ $pr }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php $st = $order->status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Pending' ? 'bg-gray-100 text-gray-600' : ($st === 'Approved' || $st === 'Partial' ? 'bg-blue-100 text-blue-700' : ($st === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">{{ $st }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php $bs = $order->billing_status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $bs === 'Billed' ? 'bg-green-100 text-green-700' : ($bs === 'Exempt' ? 'bg-blue-100 text-blue-700' : ($bs === 'Credited' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">{{ $bs ?? 'Pending' }}</span>
                                    </td>
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
                                                    <button @click="open = !open" class="text-gray-400 hover:text-gray-700 px-1 py-0.5 rounded hover:bg-gray-100 transition-colors" title="Change status">&#8942;</button>
                                                    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false" class="absolute left-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[110px]" x-cloak>
                                                        @if($is === 'Pending')
                                                        <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Approved')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Approved</button>
                                                        @endif
                                                        @if($is === 'Approved' || $is === 'Partial')
                                                        <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Partial')" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-blue-50 transition-colors">Partial</button>
                                                        <button wire:click="updateProcedureItemStatus({{ $item->id }}, 'Complete')" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50 transition-colors">Complete</button>
                                                        @endif
                                                        @if($is === 'Pending')
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

            {{-- Tab: OR Schedule --}}
            <div x-show="activeTab === 'orschedule'" class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
                        <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-4 h-4 text-light-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            OR Schedule
                            <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $erOrSchedules->count() }}</span>
                        </h3>
                        <button wire:click="openOrScheduleForm" class="inline-flex items-center px-3 py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Schedule OR
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead><tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                <th class="px-5 py-2.5 text-left">Schedule #</th>
                                <th class="px-5 py-2.5 text-left">OR Room</th>
                                <th class="px-5 py-2.5 text-left">Procedure</th>
                                <th class="px-5 py-2.5 text-left">Surgeon</th>
                                <th class="px-5 py-2.5 text-left">Anesthesiologist</th>
                                <th class="px-5 py-2.5 text-left">Date / Time</th>
                                <th class="px-5 py-2.5 text-center">Status</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($erOrSchedules as $sched)
                                <tr class="hover:bg-light-blue-50/40 transition-colors">
                                    <td class="px-5 py-3 font-mono font-medium text-gray-900">{{ $sched->schedule_code }}</td>
                                    <td class="px-5 py-3">
                                        <span class="bg-light-blue-50 text-light-blue-700 px-2 py-0.5 rounded font-medium text-xs">{{ $sched->orRoom?->name ?? '—' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700 text-xs">{{ $sched->procedure_type ?? $sched->procedure_description ?? '—' }}</td>
                                    <td class="px-5 py-3 text-gray-700 text-xs">{{ $sched->surgeon ? 'Dr. ' . $sched->surgeon->full_name : '—' }}</td>
                                    <td class="px-5 py-3 text-gray-700 text-xs">{{ $sched->anesthesiologist ? 'Dr. ' . $sched->anesthesiologist->full_name : '—' }}</td>
                                    <td class="px-5 py-3 text-gray-700 text-xs">
                                        {{ $sched->scheduled_date?->format('M d, Y') }}
                                        @if($sched->start_time)
                                            <span class="text-gray-400">{{ $sched->start_time }}@if($sched->end_time)-{{ $sched->end_time }}@endif</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @php $st = $sched->status; @endphp
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $st === 'Scheduled' ? 'bg-blue-100 text-blue-700' : ($st === 'In Progress' ? 'bg-amber-100 text-amber-700' : ($st === 'Completed' ? 'bg-green-100 text-green-700' : ($st === 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'))) }}">{{ $st }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400 text-sm">No OR schedules for this patient.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tab: Disposition --}}
            <div x-show="activeTab === 'disposition'" class="space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200 mb-6">
                        <p class="text-sm text-yellow-800">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Setting a disposition other than <strong>"Pending"</strong> will mark the ER encounter as <strong>Completed</strong>, free the assigned bed, and remove from the Active ER Area.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Patient Disposition <span class="text-red-500">*</span></label>
                            <select wire:model="disposition"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 font-medium @error('disposition') border-red-300 bg-red-50 @enderror">
                                <option value="Pending">Pending (Stay in ER)</option>
                                <option value="Admitted to Ward">Admitted to Ward</option>
                                <option value="Admitted to ICU">Admitted to ICU</option>
                                <option value="Discharged Home">Discharged Home</option>
                                <option value="AMA">Against Medical Advice (AMA)</option>
                                <option value="Expired">Expired</option>
                            </select>
                            @error('disposition') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Discharge Instructions</label>
                            <textarea wire:model="discharge_instructions" rows="4" placeholder="Follow-up instructions, medications prescribed, restrictions, return precautions, and follow-up appointment details..."
                                      class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('discharge_instructions') border-red-300 bg-red-50 @enderror"></textarea>
                            @error('discharge_instructions') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
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

            {{-- Save Button (always visible at bottom) --}}
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
                            class="px-6 py-2.5 text-sm font-semibold text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-lg shadow-sm transition-colors inline-flex items-center gap-2">
                        <span wire:loading.remove wire:target="saveTreatment">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Save Treatment Record
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
                <p class="text-sm text-gray-600">Select tests or panels to order for this patient.</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
                        <select wire:model="orderLabPriority"
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                            <option value="Routine">Routine</option>
                            <option value="Urgent">Urgent</option>
                            <option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes (optional)</label>
                        <input type="text" wire:model="orderLabNotes" placeholder="e.g. Fasting, stat..."
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    </div>
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($labTests as $test)
                    <label class="flex items-center px-4 py-2.5 hover:bg-purple-50/40 cursor-pointer transition-colors">
                        <input type="checkbox" wire:model="orderLabSelection" value="{{ $test->id }}"
                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 mr-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $test->code }} — {{ $test->name }}</p>
                        </div>
                        <span class="text-xs font-mono text-gray-500">&#8369;{{ number_format($test->standard_charge ?? 0, 2) }}</span>
                    </label>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No lab tests available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="requestLabOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="requestLabOrder">Place Lab Order</span>
                        <span wire:loading wire:target="requestLabOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
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
                <p class="text-sm text-gray-600">Select supply items to request. Set quantity for each item.</p>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Notes (optional)</label>
                    <input type="text" wire:model="orderCsrNotes" placeholder="e.g. Urgent delivery needed..."
                           class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($supplyItems as $item)
                    <div class="flex items-center px-4 py-2.5 hover:bg-emerald-50/40 transition-colors">
                        <input type="checkbox" wire:model="orderCsrSelection" value="{{ $item->id }}"
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $item->code }} — {{ $item->name }}</p>
                            <p class="text-xs text-gray-400">{{ $item->unit?->name ?? '' }}</p>
                        </div>
                        <input type="number" wire:model="orderCsrQuantities.{{ $item->id }}" min="1" value="1"
                               class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 mx-2"
                               placeholder="Qty">
                        <span class="text-xs font-mono text-gray-500 w-20 text-right">&#8369;{{ number_format($item->unit_cost ?? 0, 2) }}</span>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No supply items available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="requestCsrOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="requestCsrOrder">Place Supply Order</span>
                        <span wire:loading wire:target="requestCsrOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
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
                <p class="text-sm text-gray-600">Select exam types to order.</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
                        <select wire:model="orderRadiologyPriority"
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                            <option value="Routine">Routine</option>
                            <option value="Urgent">Urgent</option>
                            <option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes (optional)</label>
                        <input type="text" wire:model="orderRadiologyNotes" placeholder="e.g. Contrast, positioning..."
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                    </div>
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($examTypes as $exam)
                    <label class="flex items-center px-4 py-2.5 hover:bg-sky-50/40 cursor-pointer transition-colors">
                        <input type="checkbox" wire:model="orderRadiologySelection" value="{{ $exam->id }}"
                               class="rounded border-gray-300 text-sky-600 focus:ring-sky-500 mr-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $exam->code }} — {{ $exam->name }}</p>
                            <p class="text-xs text-gray-400">{{ $exam->modality?->name ?? '' }}</p>
                        </div>
                        <span class="text-xs font-mono text-gray-500">&#8369;{{ number_format($exam->standard_charge ?? 0, 2) }}</span>
                    </label>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No exam types available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="requestRadiologyOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="requestRadiologyOrder">Place Radiology Order</span>
                        <span wire:loading wire:target="requestRadiologyOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
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
                <p class="text-sm text-gray-600">Select drugs to order. Set quantity for each item.</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Priority</label>
                        <select wire:model="orderPharmacyPriority"
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="Routine">Routine</option>
                            <option value="Urgent">Urgent</option>
                            <option value="STAT">STAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes (optional)</label>
                        <input type="text" wire:model="orderPharmacyNotes" placeholder="e.g. IV route, stat..."
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>
                <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($drugs as $drug)
                    <div class="px-4 py-3 hover:bg-emerald-50/30 transition-colors">
                        {{-- Drug select row --}}
                        <div class="flex items-center gap-3 mb-2">
                            <input type="checkbox" wire:model="orderPharmacySelection" value="{{ $drug->id }}"
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $drug->generic_name }} {{ $drug->drug_strength }} @if($drug->brand_name)<span class="text-gray-400">({{ $drug->brand_name }})</span>@endif</p>
                                <p class="text-xs text-gray-400">{{ $drug->drug_code }} | {{ $drug->dosage_form ?? '' }}</p>
                            </div>
                            <input type="number" wire:model="orderPharmacyQuantities.{{ $drug->id }}" min="1" value="1"
                                   class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                                   placeholder="Qty">
                        </div>
                        {{-- CF4 fields: Frequency + Route per drug --}}
                        <div class="grid grid-cols-2 gap-2 pl-7">
                            <div>
                                <label class="block text-xs text-gray-500 mb-0.5">Frequency <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="orderPharmacyFrequencies.{{ $drug->id }}"
                                       placeholder="e.g. BID, TID, q8h, PRN"
                                       class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-0.5">Route <span class="text-red-400">*</span></label>
                                <select wire:model="orderPharmacyRoutes.{{ $drug->id }}"
                                        class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                    <option value="">— Select —</option>
                                    <option value="PO" {{ ($drug->route === 'PO') ? 'selected' : '' }}>PO (Oral)</option>
                                    <option value="IV" {{ ($drug->route === 'IV') ? 'selected' : '' }}>IV (Intravenous)</option>
                                    <option value="IM" {{ ($drug->route === 'IM') ? 'selected' : '' }}>IM (Intramuscular)</option>
                                    <option value="SC" {{ ($drug->route === 'SC') ? 'selected' : '' }}>SC (Subcutaneous)</option>
                                    <option value="SL" {{ ($drug->route === 'SL') ? 'selected' : '' }}>SL (Sublingual)</option>
                                    <option value="TOP" {{ ($drug->route === 'TOP') ? 'selected' : '' }}>TOP (Topical)</option>
                                    <option value="PR" {{ ($drug->route === 'PR') ? 'selected' : '' }}>PR (Rectal)</option>
                                    <option value="INH" {{ ($drug->route === 'INH') ? 'selected' : '' }}>INH (Inhalation)</option>
                                    <option value="NEB" {{ ($drug->route === 'NEB') ? 'selected' : '' }}>NEB (Nebulization)</option>
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
                    <button wire:click="requestPharmacyOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="requestPharmacyOrder">Place Pharmacy Order</span>
                        <span wire:loading wire:target="requestPharmacyOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
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
                <p class="text-sm text-gray-600">Select procedures to order. Set quantity for each item.</p>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Notes (optional)</label>
                    <input type="text" wire:model="orderProcedureNotes" placeholder="e.g. Pre-op clearance needed..."
                           class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                    @forelse($procedures as $procedure)
                    <div class="flex items-center px-4 py-2.5 hover:bg-indigo-50/40 transition-colors">
                        <input type="checkbox" wire:model="orderProcedureSelection" value="{{ $procedure->id }}"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $procedure->name }}</p>
                            <p class="text-xs text-gray-400">{{ $procedure->category?->name ?? '' }}</p>
                        </div>
                        <input type="number" wire:model="orderProcedureQuantities.{{ $procedure->id }}" min="1" value="1"
                               class="w-16 px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-center text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 mx-2"
                               placeholder="Qty">
                        <span class="text-xs font-mono text-gray-500 w-20 text-right">&#8369;{{ number_format($procedure->standard_charge ?? 0, 2) }}</span>
                    </div>
                    @empty
                    <p class="px-4 py-6 text-sm text-gray-400 text-center">No procedures available.</p>
                    @endforelse
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeTreatment" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="requestProcedureOrder" class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="requestProcedureOrder">Place Procedure Order</span>
                        <span wire:loading wire:target="requestProcedureOrder" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Placing…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- OR Schedule Modal --}}
    @if($showOrScheduleModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeOrScheduleModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-light-blue-50/50">
                <h3 class="text-lg font-bold text-gray-900">Schedule OR Procedure</h3>
                <button wire:click="closeOrScheduleModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <p class="text-sm text-gray-600">Schedule an operating room procedure for this patient.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">OR Room <span class="text-red-500">*</span></label>
                        <select wire:model="or_room_id"
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('or_room_id') border-red-300 bg-red-50 @enderror">
                            <option value="">— Select OR Room —</option>
                            @foreach($orRooms as $room)
                                <option value="{{ $room->id }}">{{ $room->code }} — {{ $room->name }}</option>
                            @endforeach
                        </select>
                        @error('or_room_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Procedure Type</label>
                        <input type="text" wire:model="or_procedure_type" placeholder="e.g. Laparoscopic Cholecystectomy"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Scheduled Date <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="or_scheduled_date"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('or_scheduled_date') border-red-300 bg-red-50 @enderror">
                        @error('or_scheduled_date') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Procedure Description</label>
                        <input type="text" wire:model="or_procedure_description" placeholder="Brief description"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Start Time</label>
                        <input type="time" wire:model="or_start_time"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">End Time</label>
                        <input type="time" wire:model="or_end_time"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Surgeon</label>
                        <select wire:model="or_surgeon_id"
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select Surgeon —</option>
                            @foreach($orDoctors as $dr)
                                <option value="{{ $dr->id }}">Dr. {{ $dr->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Anesthesiologist</label>
                        <select wire:model="or_anesthesiologist_id"
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select Anesthesiologist —</option>
                            @foreach($orDoctors as $dr)
                                <option value="{{ $dr->id }}">Dr. {{ $dr->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Pre-Op Notes</label>
                    <textarea wire:model="or_pre_op_notes" rows="2" placeholder="Pre-operative instructions, labs, and preparations..."
                              class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Intra-Op Notes</label>
                    <textarea wire:model="or_intra_op_notes" rows="2" placeholder="Intra-operative notes..."
                              class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Post-Op Notes</label>
                    <textarea wire:model="or_post_op_notes" rows="2" placeholder="Post-operative care instructions..."
                              class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button wire:click="closeOrScheduleModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button wire:click="saveOrSchedule" class="px-5 py-2.5 text-sm font-semibold text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="saveOrSchedule">Create Schedule</span>
                        <span wire:loading wire:target="saveOrSchedule" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @else
    {{-- ===== DATAGRID VIEW ===== --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Emergency Room Area</h2>
        <div class="flex items-center bg-gray-100 rounded-xl p-1 shadow-sm">
            <button wire:click="$set('showCompleted', false)"
                    class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-colors {{ !$showCompleted ? 'bg-white text-light-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Active
            </button>
            <button wire:click="$set('showCompleted', true)"
                    class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-colors {{ $showCompleted ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Completed
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-md bg-white">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50/90 backdrop-blur-md">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search patients by name or encounter #..."
                   class="w-full sm:w-80 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-900 transition-all duration-200 ease-in-out hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 shadow-sm">
            <div class="flex items-center space-x-2 text-sm text-gray-600">
                <span class="font-medium">Rows:</span>
                <select wire:model.live="perPage"
                        class="block w-20 pl-3 pr-8 py-2.5 bg-white border border-gray-200 text-gray-900 transition-all duration-200 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm rounded-xl shadow-sm">
                    <option value="10">10</option><option value="25">25</option><option value="50">50</option>
                </select>
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/90 backdrop-blur-md border-b border-gray-200 text-gray-600 sticky top-0 z-10">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Acuity</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('admission_code')">
                        <span class="inline-flex items-center gap-1">Encounter #<span class="text-gray-300">@if($sortField === 'admission_code'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('admission_datetime')">
                        <span class="inline-flex items-center gap-1">Patient Name<span class="text-gray-300">@if($sortField === 'admission_datetime'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    @if(!$showCompleted)
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Bed / Zone</th>
                    @endif
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Physician</th>
                    @if($showCompleted)
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Disposition</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Disposition Date</th>
                    @else
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider">Arrival Time</th>
                    @endif
                    <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($admissions as $adm)
                    <tr class="hover:bg-slate-50 transition-colors duration-200 group">
                        <td class="px-5 py-4">
                            @if($adm->triageRecord && $adm->triageRecord->acuityLevel)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold text-white shadow-sm" style="background-color: {{ $adm->triageRecord->acuityLevel->color_code ?? '#6b7280' }}">
                                    {{ $adm->triageRecord->acuityLevel->code }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-mono font-bold text-gray-900">
                            {{ $adm->admission_code }}
                            @if($adm->is_oecb)
                                <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-700 border border-sky-200">OECB</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-medium text-gray-900">{{ $adm->patient->last_name }}, {{ $adm->patient->first_name }}</div>
                            <div class="text-xs text-gray-500">
                                @if($adm->patient->date_of_birth)
                                    {{ \Carbon\Carbon::parse($adm->patient->date_of_birth)->age }}y
                                @endif
                                @if($adm->patient->gender) / {{ $adm->patient->gender->name }} @endif
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @if($adm->bed)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/50">
                                    {{ $adm->bed->name }}
                                </span>
                                @if($adm->bed->zone)
                                    <span class="text-xs text-gray-400 ml-1">{{ $adm->bed->zone->name }}</span>
                                @endif
                            @else
                                <span class="text-gray-400 italic text-xs">Unassigned</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-700">{{ $adm->attendingPhysician ? 'Dr. ' . $adm->attendingPhysician->full_name : '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="text-gray-700">{{ $adm->admission_datetime->format('H:i') }}</div>
                            <div class="text-xs text-gray-400">{{ $adm->admission_datetime->format('M d') }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <button wire:click="openTreatment({{ $adm->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors" title="Open Treatment Record">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                    Treatment
                                </button>
                                <button wire:click="openDisposition({{ $adm->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors" title="Set Disposition">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Dispo
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span class="text-lg font-medium text-gray-900">No active patients</span><p class="mt-1 text-sm">Transfer patients from Triage to the ER Area to begin treatment.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-200">{{ $admissions->links() }}</div>
    </div>
    @endif

    {{-- Quick Edit Notes/Bed Modal --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-light-blue-50/50">
                <h3 class="text-lg font-bold text-gray-900">Update ER Encounter</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveEdit" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">ER Bed</label>
                        <select wire:model="er_bed_id"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select Bed —</option>
                            @foreach($beds as $bed)
                                <option value="{{ $bed->id }}">{{ $bed->name }} @if($bed->zone)({{ $bed->zone->name }})@endif</option>
                            @endforeach
                        </select>
                        @error('er_bed_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Attending Physician</label>
                        <select wire:model="attending_physician_id"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select Physician —</option>
                            @foreach($physicians as $dr)
                                <option value="{{ $dr->id }}">Dr. {{ $dr->full_name }}</option>
                            @endforeach
                        </select>
                        @error('attending_physician_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Initial Diagnosis</label>
                    <textarea wire:model="initial_diagnosis" rows="3"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">Save Updates</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Disposition Modal --}}
    @if($showDispositionModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden transform transition-all">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-green-50/50">
                <h3 class="text-lg font-bold text-gray-900">Set Patient Disposition</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveDisposition" class="px-6 py-5 space-y-4">
                <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <p class="text-sm text-amber-800">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Setting a disposition other than "Pending" will mark the ER Encounter as <strong>Completed</strong>, free up the bed, and remove from Active ER Area.
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Disposition <span class="text-red-500">*</span></label>
                    <select wire:model="disposition"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 font-medium @error('disposition') border-red-300 bg-red-50 @enderror">
                        <option value="Pending">Pending (Stay in ER)</option>
                        <option value="Admitted to Ward">Admitted to Ward</option>
                        <option value="Admitted to ICU">Admitted to ICU</option>
                        <option value="Discharged Home">Discharged Home</option>
                        <option value="AMA">Against Medical Advice (AMA)</option>
                        <option value="Expired">Expired</option>
                    </select>
                    @error('disposition') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Discharge / Follow-up Instructions</label>
                    <textarea wire:model="discharge_instructions" rows="4" placeholder="Follow-up care instructions, medications, restrictions, return precautions..."
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-xl shadow-sm transition-colors">Confirm Disposition</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
