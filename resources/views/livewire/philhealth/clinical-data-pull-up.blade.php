<div>
    @if($showModal)
        <div class="space-y-6">
            {{-- ER Admissions --}}
            @if($erAdmissions && $erAdmissions->isNotEmpty())
            <div>
                <h4 class="text-sm font-bold text-sky-800 mb-3 flex items-center">
                    <span class="w-6 h-6 rounded-full bg-sky-200 text-sky-700 text-xs font-bold flex items-center justify-center mr-2">ER</span>
                    Emergency Room Encounters ({{ $erAdmissions->count() }})
                </h4>
                <div class="space-y-2">
                    @foreach($erAdmissions as $er)
                    <div class="flex items-center justify-between p-3 bg-sky-50/70 border border-sky-200 rounded-xl hover:bg-sky-100/70 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-mono font-medium text-sky-700">{{ $er->admission_code }}</span>
                                <span class="text-gray-500">—</span>
                                <span class="text-gray-700">{{ $er->admission_datetime?->format('M d, Y H:i') }}</span>
                                @if($er->is_oecb)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-200 text-sky-800">OECB</span>
                                @endif
                            </div>
                            @if($er->initial_diagnosis)
                            <p class="text-xs text-gray-500 mt-1 truncate">{{ $er->initial_diagnosis }}</p>
                            @endif
                            @if($er->oecbEntries->isNotEmpty())
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($er->oecbEntries->take(3) as $entry)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-white border border-sky-200 text-sky-700">{{ $entry->core_presentation ?? 'Entry' }} (&#x20B1;{{ number_format($entry->oecb_fixed_fee, 0) }})</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 ml-4 shrink-0">
                            <button type="button" wire:click="pullFromEr({{ $er->id }})"
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-lg transition-colors">
                                Pull Data
                            </button>
                            <button type="button" wire:click="importFromBilling(erAdmissionId: {{ $er->id }})"
                                    class="px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-lg transition-colors">
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
            <div>
                <h4 class="text-sm font-bold text-emerald-800 mb-3 flex items-center">
                    <span class="w-6 h-6 rounded-full bg-emerald-200 text-emerald-700 text-xs font-bold flex items-center justify-center mr-2">W</span>
                    In-Patient Ward Admissions ({{ $admissions->count() }})
                </h4>
                <div class="space-y-2">
                    @foreach($admissions as $adm)
                    <div class="flex items-center justify-between p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl hover:bg-emerald-100/70 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-mono font-medium text-emerald-700">{{ $adm->admission_no }}</span>
                                <span class="text-gray-500">—</span>
                                <span class="text-gray-700">{{ $adm->admission_date?->format('M d, Y') }}</span>
                                @if($adm->discharge_date)
                                <span class="text-xs text-gray-500">→ {{ $adm->discharge_date->format('M d, Y') }}</span>
                                @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-700">Admitted</span>
                                @endif
                            </div>
                            @if($adm->admitting_diagnosis)
                            <p class="text-xs text-gray-500 mt-1 truncate">{{ $adm->admitting_diagnosis }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 ml-4 shrink-0">
                            <button type="button" wire:click="pullFromWard({{ $adm->id }})"
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                                Pull Data
                            </button>
                            <button type="button" wire:click="importFromBilling(admissionId: {{ $adm->id }})"
                                    class="px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-lg transition-colors">
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
            <div>
                <h4 class="text-sm font-bold text-violet-800 mb-3 flex items-center">
                    <span class="w-6 h-6 rounded-full bg-violet-200 text-violet-700 text-xs font-bold flex items-center justify-center mr-2">OPD</span>
                    Outpatient Consultations ({{ $opdConsultations->count() }})
                </h4>
                <div class="space-y-2">
                    @foreach($opdConsultations as $opd)
                    <div class="flex items-center justify-between p-3 bg-violet-50/70 border border-violet-200 rounded-xl hover:bg-violet-100/70 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-mono font-medium text-violet-700">#{{ $opd->id }}</span>
                                <span class="text-gray-500">—</span>
                                <span class="text-gray-700">{{ $opd->consultation_datetime?->format('M d, Y H:i') }}</span>
                            </div>
                            @if($opd->chief_complaint)
                            <p class="text-xs text-gray-500 mt-1 truncate">{{ $opd->chief_complaint }}</p>
                            @endif
                            @if($opd->assessment)
                            <p class="text-xs text-gray-500 truncate">{{ $opd->assessment }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 ml-4 shrink-0">
                            <button type="button" wire:click="pullFromOpd({{ $opd->id }})"
                                    class="px-3 py-1.5 text-xs font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition-colors">
                                Pull Data
                            </button>
                            <button type="button" wire:click="importFromBilling(opdConsultationId: {{ $opd->id }})"
                                    class="px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-lg transition-colors">
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
            <div class="text-center py-12 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-lg font-medium text-gray-900">No encounters found</p>
                <p class="text-sm mt-1">No ER, Ward, or OPD records exist for this patient.</p>
            </div>
            @endif
        </div>
    @else
        <div class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <p class="text-lg font-medium text-gray-900">Select a patient first</p>
            <p class="text-sm mt-1">Patient selection is required to pull clinical encounter data.</p>
        </div>
    @endif
</div>
