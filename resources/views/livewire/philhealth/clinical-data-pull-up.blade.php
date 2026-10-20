<div>
    {{-- Patient Context / Selector Bar --}}
    <div class="mb-5 p-4 rounded-xl border border-sky-100 bg-sky-50/50">
        @if($patientId)
            @php
                $patient = \App\Models\Patient::find($patientId);
            @endphp
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-light-blue-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                        {{ strtoupper(substr($patient?->first_name ?? 'P', 0, 1)) }}{{ strtoupper(substr($patient?->last_name ?? 'T', 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-sm">
                            {{ $patient?->last_name }}, {{ $patient?->first_name }}
                            @if($patient?->middle_name) {{ substr($patient->middle_name, 0, 1) }}. @endif
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-500 font-mono mt-0.5">
                            <span>Code: {{ $patient?->patient_code }}</span>
                            @if($patient?->gender) <span>• {{ $patient->gender }}</span> @endif
                            @if($patient?->birth_date) <span>• {{ $patient->birth_date->format('M d, Y') }}</span> @endif
                            @if($patient?->philhealth_member_id) <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded font-bold text-[10px]">PhilHealth Member</span> @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="$set('patientId', null)" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Change Patient
                    </button>
                    <button type="button" wire:click="$dispatch('switch-claim-tab', { tab: 'form' })" class="px-3 py-1.5 text-xs font-semibold text-light-blue-700 bg-white border border-light-blue-200 rounded-lg hover:bg-light-blue-50 transition-colors">
                        Go to Claim Form &rarr;
                    </button>
                </div>
            </div>
        @else
            <div>
                <label class="block text-xs font-bold text-sky-900 uppercase tracking-wider mb-1.5">
                    Select Patient to Auto-Extract Clinical Data
                </label>
                <div class="relative">
                    <input wire:model.live.debounce.250ms="patientSearch" type="text"
                           placeholder="Type patient last name, first name, or code (e.g. Santos, Dela Cruz, PAT-001)..."
                           class="w-full pl-9 pr-4 py-2 text-xs bg-white border border-sky-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    <svg class="w-4 h-4 text-sky-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                </div>

                @if(!empty($searchedPatients) && count($searchedPatients) > 0)
                <div class="mt-2 bg-white rounded-lg border border-gray-200 shadow-md divide-y divide-gray-100 max-h-48 overflow-y-auto">
                    @foreach($searchedPatients as $p)
                    <div class="p-2.5 flex items-center justify-between hover:bg-light-blue-50/50 transition-colors cursor-pointer"
                         wire:click="selectPatient({{ $p->id }})">
                        <div>
                            <span class="text-xs font-semibold text-gray-900">{{ $p->last_name }}, {{ $p->first_name }}</span>
                            <span class="text-xs font-mono text-gray-500 ml-2">({{ $p->patient_code }})</span>
                        </div>
                        <span class="text-[10px] font-semibold text-light-blue-700 bg-light-blue-50 px-2 py-0.5 rounded border border-light-blue-200">
                            Select Patient
                        </span>
                    </div>
                    @endforeach
                </div>
                @elseif(strlen(trim($patientSearch)) >= 2)
                <div class="mt-2 p-3 text-center text-xs text-gray-500 bg-white rounded-lg border border-gray-200">
                    No matching patients found.
                </div>
                @endif
            </div>
        @endif
    </div>

    @if(!$patientId)
        <div class="text-center py-10 text-gray-400 bg-white rounded-xl border border-gray-200">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <p class="text-base font-bold text-gray-800">Search or Select a Patient First</p>
            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">Once a patient is selected, all past Emergency Room encounters, In-Patient Ward stays, and OPD Consultations will appear here for 1-click extraction.</p>
        </div>
    @else
        <div class="space-y-6">
            {{-- ER Admissions --}}
            @if($erAdmissions && $erAdmissions->isNotEmpty())
            <div class="bg-white rounded-xl border border-sky-200 p-4 shadow-sm">
                <h4 class="text-xs font-bold text-sky-900 uppercase tracking-wider mb-3 flex items-center">
                    <span class="w-5 h-5 rounded-full bg-sky-100 text-sky-700 text-[10px] font-bold flex items-center justify-center mr-2">ER</span>
                    Emergency Room Encounters ({{ $erAdmissions->count() }})
                </h4>
                <div class="space-y-2.5">
                    @foreach($erAdmissions as $er)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 bg-sky-50/50 border border-sky-100 rounded-xl hover:bg-sky-100/50 transition-colors gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-mono font-bold text-sky-800">{{ $er->admission_code }}</span>
                                <span class="text-gray-400">•</span>
                                <span class="text-gray-700">{{ $er->admission_datetime?->format('M d, Y H:i') }}</span>
                                @if($er->is_oecb)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-200 text-sky-800">OECB</span>
                                @endif
                                @if($er->disposition)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-white border border-gray-200 text-gray-600">{{ $er->disposition }}</span>
                                @endif
                            </div>
                            @if($er->initial_diagnosis)
                            <p class="text-xs text-gray-700 font-medium mt-1 truncate">Diagnosis: {{ $er->initial_diagnosis }}</p>
                            @endif
                            @if($er->attendingPhysician)
                            <p class="text-[11px] text-gray-500 mt-0.5">Doctor: Dr. {{ $er->attendingPhysician->first_name }} {{ $er->attendingPhysician->last_name }}</p>
                            @endif
                            @if($er->oecbEntries->isNotEmpty())
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach($er->oecbEntries->take(3) as $entry)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-white border border-sky-200 text-sky-700">{{ $entry->core_presentation ?? 'Entry' }} (&#x20B1;{{ number_format($entry->oecb_fixed_fee, 0) }})</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" wire:click="pullFromEr({{ $er->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow-sm transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Pull Data
                            </button>
                            <button type="button" wire:click="importFromBilling(erAdmissionId: {{ $er->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Import Billing
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Ward Admissions --}}
            @if($admissions && $admissions->isNotEmpty())
            <div class="bg-white rounded-xl border border-emerald-200 p-4 shadow-sm">
                <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider mb-3 flex items-center">
                    <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold flex items-center justify-center mr-2">W</span>
                    In-Patient Ward Admissions ({{ $admissions->count() }})
                </h4>
                <div class="space-y-2.5">
                    @foreach($admissions as $adm)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 bg-emerald-50/50 border border-emerald-100 rounded-xl hover:bg-emerald-100/50 transition-colors gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-mono font-bold text-emerald-800">{{ $adm->admission_code ?? $adm->admission_no ?? '#' . $adm->id }}</span>
                                <span class="text-gray-400">•</span>
                                <span class="text-gray-700">{{ $adm->admission_date?->format('M d, Y') }}</span>
                                @if($adm->discharge_date)
                                <span class="text-xs text-gray-500">→ {{ $adm->discharge_date->format('M d, Y') }}</span>
                                @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-800">Admitted</span>
                                @endif
                                @if($adm->disposition)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-white border border-gray-200 text-gray-600">{{ $adm->disposition }}</span>
                                @endif
                            </div>
                            @if($adm->admitting_diagnosis)
                            <p class="text-xs text-gray-700 font-medium mt-1 truncate">Diagnosis: {{ $adm->admitting_diagnosis }}</p>
                            @endif
                            @if($adm->attendingPhysician)
                            <p class="text-[11px] text-gray-500 mt-0.5">Doctor: Dr. {{ $adm->attendingPhysician->first_name }} {{ $adm->attendingPhysician->last_name }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" wire:click="pullFromWard({{ $adm->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Pull Data
                            </button>
                            <button type="button" wire:click="importFromBilling(admissionId: {{ $adm->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-white border border-emerald-300 hover:bg-emerald-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Import Billing
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- OPD Consultations --}}
            @if($opdConsultations && $opdConsultations->isNotEmpty())
            <div class="bg-white rounded-xl border border-violet-200 p-4 shadow-sm">
                <h4 class="text-xs font-bold text-violet-900 uppercase tracking-wider mb-3 flex items-center">
                    <span class="w-5 h-5 rounded-full bg-violet-100 text-violet-700 text-[10px] font-bold flex items-center justify-center mr-2">OPD</span>
                    Outpatient Consultations ({{ $opdConsultations->count() }})
                </h4>
                <div class="space-y-2.5">
                    @foreach($opdConsultations as $opd)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 bg-violet-50/50 border border-violet-100 rounded-xl hover:bg-violet-100/50 transition-colors gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="font-mono font-bold text-violet-800">#{{ $opd->id }}</span>
                                <span class="text-gray-400">•</span>
                                <span class="text-gray-700">{{ $opd->consultation_datetime?->format('M d, Y H:i') }}</span>
                            </div>
                            @if($opd->chief_complaint)
                            <p class="text-xs text-gray-700 font-medium mt-1 truncate">Complaint: {{ $opd->chief_complaint }}</p>
                            @endif
                            @if($opd->assessment)
                            <p class="text-xs text-gray-600 truncate mt-0.5">Assessment: {{ $opd->assessment }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" wire:click="pullFromOpd({{ $opd->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-violet-600 hover:bg-violet-700 rounded-lg shadow-sm transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Pull Data
                            </button>
                            <button type="button" wire:click="importFromBilling(opdConsultationId: {{ $opd->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-white border border-emerald-300 hover:bg-emerald-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                Import Billing
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Empty State --}}
            @if(
                (!$erAdmissions || $erAdmissions->isEmpty()) &&
                (!$admissions || $admissions->isEmpty()) &&
                (!$opdConsultations || $opdConsultations->isEmpty())
            )
            <div class="text-center py-10 text-gray-400 bg-white rounded-xl border border-gray-200">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-base font-bold text-gray-800">No Encounters Found for this Patient</p>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">This patient has no recorded ER visits, Ward admissions, or OPD consultations. You can enter diagnosis and vitals manually on the Claim Form.</p>
                <button type="button" wire:click="$dispatch('switch-claim-tab', { tab: 'form' })"
                        class="mt-4 px-4 py-2 text-xs font-semibold text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">
                    Proceed to Claim Form &rarr;
                </button>
            </div>
            @endif
        </div>
    @endif
</div>
