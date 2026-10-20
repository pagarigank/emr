<div>
    @if($showModal)
        <form wire:submit="save" class="space-y-6">
            {{-- Section A: Patient & Admission --}}
            <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-semibold text-gray-700 flex items-center"><span class="w-6 h-6 rounded-full bg-light-blue-100 text-light-blue-700 text-xs font-bold flex items-center justify-center mr-2">A</span> Patient &amp; Admission Details</h4>
                    <button type="button" wire:click="openPullUp" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-sky-700 bg-sky-50 border border-sky-200 rounded-lg hover:bg-sky-100 transition-colors shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        ⚡ Pull Clinical Data
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Patient <span class="text-red-500">*</span></label>
                        <div class="relative" x-data="{ open: false }" wire:key="patient-search-{{ $editingId }}">
                            <input wire:model.live.debounce.250ms="patient_search"
                                   @focus="open = true" @click.away="setTimeout(() => open = false, 250)" type="text"
                                   placeholder="Search patient name or code…"
                                   class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('patient_id') border-red-300 bg-red-50 @enderror">
                            @error('patient_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror

                            @if($patients && $patients->isNotEmpty())
                            <div x-show="open" class="absolute z-30 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl max-h-56 overflow-y-auto divide-y divide-gray-100">
                                @foreach($patients as $p)
                                <div wire:click="selectPatient({{ $p->id }})" @click="open = false"
                                     class="px-4 py-2.5 hover:bg-light-blue-50 cursor-pointer text-sm flex items-center justify-between transition-colors">
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $p->last_name }}, {{ $p->first_name }}</span>
                                        <span class="text-gray-500 ml-2 font-mono text-xs">({{ $p->patient_code }})</span>
                                    </div>
                                    <span class="text-[10px] font-semibold text-light-blue-700 bg-light-blue-50 px-2 py-0.5 rounded border border-light-blue-200">Select</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">PhilHealth Member</label>
                        <select wire:model="philhealth_member_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select Member —</option>
                            @foreach($philhealthMembers as $m)
                                <option value="{{ $m->id }}">{{ $m->pin }} — {{ $m->last_name }}, {{ $m->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date Admitted</label>
                        <input wire:model="date_admitted" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Time Admitted</label>
                        <input wire:model="time_admitted" type="time" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date Discharged</label>
                        <input wire:model="date_discharged" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Time Discharged</label>
                        <input wire:model="time_discharged" type="time" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Admitting Diagnosis</label>
                        <textarea wire:model="admitting_diagnosis" rows="2" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Discharge Diagnosis</label>
                        <textarea wire:model="discharge_diagnosis" rows="2" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Primary ICD-10 Code</label>
                        <select wire:model.live="icd10_code_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select —</option>
                            @foreach($icd10Codes as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Case Rate</label>
                        <select wire:model="case_rate_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select —</option>
                            @forelse($filteredCaseRates as $cr)
                                <option value="{{ $cr['id'] }}">{{ $cr['rvs_code'] }} — {{ $cr['description'] }} (&#x20B1;{{ number_format($cr['total_rate'], 2) }})</option>
                            @empty
                                @foreach($allCaseRates as $cr)
                                    <option value="{{ $cr->id }}">{{ $cr->rvs_code }} — {{ $cr->description }} (&#x20B1;{{ number_format($cr->total_rate, 2) }})</option>
                                @endforeach
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Secondary ICD-10</label>
                        <select wire:model="secondary_icd10_code_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— None —</option>
                            @foreach($icd10Codes as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tertiary ICD-10</label>
                        <select wire:model="tertiary_icd10_code_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— None —</option>
                            @foreach($icd10Codes as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Secondary Case Rate</label>
                        <select wire:model="second_case_rate_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— None —</option>
                            @foreach($allCaseRates as $cr)
                                <option value="{{ $cr->id }}">{{ $cr->rvs_code }} — {{ $cr->description }}</option>
                            @endforeach
                        </select>
                    </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Attending Physician (CF2/CF4)</label>
                        <select wire:model="attending_physician_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select Attending Physician —</option>
                            @if($doctors)
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->last_name }}, {{ $doc->first_name }} {{ $doc->middle_name ? substr($doc->middle_name, 0, 1) . '.' : '' }} ({{ $doc->license_number ?? 'HCP-' . $doc->id }})</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Referring Physician (Optional)</label>
                        <select wire:model="referring_physician_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select Referring Physician —</option>
                            @if($doctors)
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->last_name }}, {{ $doc->first_name }} {{ $doc->middle_name ? substr($doc->middle_name, 0, 1) . '.' : '' }} ({{ $doc->license_number ?? 'HCP-' . $doc->id }})</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section B: CF4 Clinical Data --}}
            <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center"><span class="w-6 h-6 rounded-full bg-light-blue-100 text-light-blue-700 text-xs font-bold flex items-center justify-center mr-2">B</span> CF4 — Clinical Data</h4>
                <div class="space-y-4">
                    {{-- Vital Signs at Admission --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Admission Vital Signs</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white p-3 rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Blood Pressure (BP)</label>
                                <input wire:model="blood_pressure" type="text" placeholder="120/80" class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-xs text-gray-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-light-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Heart Rate (bpm)</label>
                                <input wire:model="heart_rate" type="text" placeholder="75" class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-xs text-gray-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-light-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Respiratory Rate (cpm)</label>
                                <input wire:model="respiratory_rate" type="text" placeholder="18" class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-xs text-gray-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-light-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Temperature (°C)</label>
                                <input wire:model="temperature" type="text" placeholder="36.8" class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded text-xs text-gray-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-light-blue-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Chief Complaint</label>
                        <input wire:model="chief_complaint" type="text" placeholder="Primary reason for admission…" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">History of Present Illness</label>
                        <textarea wire:model="history_of_present_illness" rows="3" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500" placeholder="Chronological events of present illness…"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Pertinent Past Medical History</label>
                        <textarea wire:model="pertinent_past_medical_history" rows="2" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500" placeholder="Past medical conditions, surgeries, allergies…"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Physical Examination Findings</label>
                        <textarea wire:model="physical_exam_findings" rows="3" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500" placeholder="Vital signs, HEENT, chest/lungs, CVS, abdomen…"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Course in the Ward</label>
                        <textarea wire:model="course_in_ward" rows="4" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500" placeholder="Progress during confinement, treatments, procedures…"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Lab/Imaging Results Summary (CF3)</label>
                        <textarea wire:model="lab_imaging_summary" rows="3" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500" placeholder="Significant laboratory and radiology findings…"></textarea>
                    </div>
                </div>
            </div>

            {{-- Section B2: CF3 OB / Delivery Data --}}
            <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center"><span class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 text-xs font-bold flex items-center justify-center mr-2">B2</span> CF3 — OB / Maternity Data</h4>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">LMP</label><input wire:model="lmp_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">EDC</label><input wire:model="edc_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Gravida</label><input wire:model="gravida" type="number" min="0" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Para</label><input wire:model="para" type="number" min="0" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Date</label><input wire:model="delivery_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Type</label>
                        <select wire:model="delivery_type_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select —</option>
                            @foreach($deliveryTypes as $dt)
                                <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Newborn Outcome</label>
                        <select wire:model="newborn_outcome" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select —</option>
                            <option value="Live Birth">Live Birth</option>
                            <option value="Stillbirth">Stillbirth</option>
                            <option value="Early Neonatal Death">Early Neonatal Death</option>
                            <option value="Late Neonatal Death">Late Neonatal Death</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Section E: Z-Benefits --}}
            <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center"><span class="w-6 h-6 rounded-full bg-violet-100 text-violet-700 text-xs font-bold flex items-center justify-center mr-2">E</span> Z-Benefits (CF5)</h4>
                @if(count($zBenefits) > 0)
                <div class="overflow-x-auto mb-3">
                    <table class="min-w-full text-sm">
                        <thead><tr class="text-xs text-gray-500 uppercase"><th class="px-2 py-1.5 text-left">Z-Code</th><th class="px-2 py-1.5 text-left">Pre-Auth</th><th class="px-2 py-1.5 text-right">Tranche #</th><th class="px-2 py-1.5 text-right">Amount</th><th class="px-2 py-1.5"></th></tr></thead>
                        <tbody>
                            @foreach($zBenefits as $i => $zb)
                            <tr class="border-t border-gray-200">
                                <td class="px-2 py-1.5"><input wire:model="zBenefits.{{ $i }}.z_benefit_code" type="text" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-violet-500/20" placeholder="Z001"></td>
                                <td class="px-2 py-1.5"><input wire:model="zBenefits.{{ $i }}.pre_authorization_code" type="text" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-violet-500/20" placeholder="PRE-12345"></td>
                                <td class="px-2 py-1.5"><input wire:model="zBenefits.{{ $i }}.tranche_number" type="number" min="1" class="w-20 px-2 py-1.5 bg-white border border-gray-200 rounded text-xs text-right focus:outline-none focus:ring-1 focus:ring-violet-500/20"></td>
                                <td class="px-2 py-1.5"><input wire:model="zBenefits.{{ $i }}.tranche_amount" type="number" step="0.01" class="w-32 px-2 py-1.5 bg-white border border-gray-200 rounded text-xs text-right font-mono focus:outline-none focus:ring-1 focus:ring-violet-500/20"></td>
                                <td class="px-2 py-1.5"><button type="button" wire:click="removeZBenefit({{ $i }})" class="p-1 text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                <button type="button" wire:click="addZBenefit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-violet-700 bg-violet-50 border border-violet-200 rounded-lg hover:bg-violet-100 transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Add Z-Benefit
                </button>
            </div>

            {{-- Section D: Outcome & Status --}}
            <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center"><span class="w-6 h-6 rounded-full bg-light-blue-100 text-light-blue-700 text-xs font-bold flex items-center justify-center mr-2">D</span> Outcome & Status</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                        <select wire:model="status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="Draft">Draft</option>
                            <option value="For Submission">For Submission</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Claim Type</label>
                        <select wire:model="claim_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="ACR">All Case Rate (ACR)</option>
                            <option value="DRG">DRG</option>
                            <option value="Z-Benefit">Z-Benefit</option>
                            <option value="OECB">Outpatient Emergency Care Benefit (OECB)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Outcome (VI of CF4)</label>
                        <select wire:model="outcome" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select —</option>
                            <option value="Improved">Improved</option>
                            <option value="Recovered">Recovered</option>
                            <option value="HAMA">HAMA</option>
                            <option value="Expired">Expired</option>
                            <option value="Absconded">Absconded</option>
                            <option value="Transferred">Transferred</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4"><label class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label><textarea wire:model="remarks" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea></div>
                <div class="mt-4"><label class="block text-sm font-medium text-gray-700 mb-1.5">HCI Signatory Name (CF2)</label><input wire:model="hci_signatory_name" type="text" placeholder="Authorized HCI representative" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-gray-100">
                <button type="button" wire:click="openPullUp"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-sky-700 bg-sky-50 border border-sky-200 rounded-xl hover:bg-sky-100 transition-colors shadow-xs w-full sm:w-auto justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Pull Encounters (CF4)
                </button>
                <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                    <button type="submit" class="px-5 py-2.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Save Claim' : 'Save as Draft' }}</span>
                        <span wire:loading wire:target="save" class="flex items-center"><svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-gray-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving…</span>
                    </button>
                    <button type="button" wire:click="saveAndProceed"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="saveAndProceed">Save &amp; Proceed to Items &rarr;</span>
                        <span wire:loading wire:target="saveAndProceed" class="flex items-center"><svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving…</span>
                    </button>
                </div>
            </div>
        </form>
    @else
        <div class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-lg font-medium text-gray-900">Claim Form</p>
            <p class="text-sm mt-1">Select a tab to begin editing this claim.</p>
        </div>
    @endif
</div>
