<div>
    @if(!$showConsultationForm)
    {{-- DATAGRID VIEW --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">OPD Consultations</h2>
            <p class="text-sm text-gray-500 mt-1">Manage outpatient clinic encounters, vitals, and treatments.</p>
        </div>
        <button wire:click="openCreate" class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>New Encounter
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search patient or code..." class="block w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl leading-5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm shadow-sm"/>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Consult Code</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Date/Time</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Patient</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Clinic</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Doctor</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">YAKAP</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($consultations as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $item->consultation_code }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ \Carbon\Carbon::parse($item->consultation_datetime)->format('M d, Y h:i A') }}</td>
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $item->patient?->last_name }}, {{ $item->patient?->first_name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->opdClinic?->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->attendingPhysician?->full_name ?? '—' }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $sClass = match($item->status) {
                                    'Completed' => 'bg-green-100 text-green-700',
                                    'In Progress' => 'bg-blue-100 text-blue-700',
                                    'Cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold {{ $sClass }}">{{ $item->status }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($item->is_yakap)
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700">YAKAP</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-1.5">
                                <button wire:click="openVitals({{ $item->id }})" class="px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors border border-emerald-200">Vitals</button>
                                <button wire:click="treatConsultation({{ $item->id }})" class="px-2.5 py-1 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">Consult</button>
                                
                                {{-- Quick True PDF Document Triggers --}}
                                <a href="/opd/consultation/{{ $item->id }}/pdf/prescription" target="_blank" title="Print Prescription (Rx) PDF" class="px-2 py-1 text-xs font-serif font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg border border-teal-200 transition-colors">
                                    ℞
                                </a>
                                <button type="button" wire:click="openMedCertModal({{ $item->id }})" title="Issue Medical Certificate PDF" class="p-1 text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </button>
                                <a href="/opd/consultation/{{ $item->id }}/pdf/summary" target="_blank" title="Consultation Summary Abstract PDF" class="p-1 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400">No OPD consultations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $consultations->links() }}</div>

    {{-- Create Encounter Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showCreateModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 z-10 border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/80 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-gray-900">New OPD Encounter</h3>
                <button wire:click="$set('showCreateModal', false)" class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveEncounter" class="p-6 space-y-4">
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient</label>
                    @if($patient_id && $selected_patient_name)
                        <div class="flex items-center justify-between px-3 py-2 border border-emerald-300 bg-emerald-50 rounded-xl text-sm">
                            <span class="font-medium text-emerald-800">{{ $selected_patient_name }}</span>
                            <button type="button" wire:click="$set('patient_id', null)" class="text-emerald-600 hover:text-emerald-800 focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @else
                        <input wire:model.live.debounce.300ms="patient_search" type="text" placeholder="Search patient name or code..." autocomplete="off" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        @if(strlen($patient_search) > 0 && count($patients) > 0 && !$patient_id)
                            <ul class="absolute z-50 w-full bg-white border border-gray-200 mt-1 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                @foreach($patients as $p)
                                    <li wire:click="selectPatient({{ $p->id }}, '{{ addslashes($p->last_name . ', ' . $p->first_name . ' (' . $p->patient_code . ')') }}')" class="px-4 py-2 hover:bg-emerald-50 cursor-pointer text-sm transition-colors border-b border-gray-50 last:border-0">
                                        <div class="font-medium text-gray-900">{{ $p->last_name }}, {{ $p->first_name }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $p->patient_code }}</div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                    @error('patient_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Clinic</label>
                        <select wire:model="opd_clinic_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            <option value="">— Select Clinic —</option>
                            @foreach($clinics as $c) <option value="{{ $c->id }}">{{ $c->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Attending Physician</label>
                        <select wire:model="attending_physician_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            <option value="">— Select Doctor —</option>
                            @foreach($doctors as $d) <option value="{{ $d->id }}">{{ $d->full_name }}</option> @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Consultation Type</label>
                        <select wire:model="consultation_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            <option value="">— Select Type —</option>
                            @foreach($consultationTypes as $ct) <option value="{{ $ct->id }}">{{ $ct->name }}</option> @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Visit Type</label>
                        <select wire:model="visit_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            <option value="">— Select Visit Type —</option>
                            @foreach($visitTypes as $vt) <option value="{{ $vt->id }}">{{ $vt->name }}</option> @endforeach
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <div class="flex items-center gap-3">
                        <input wire:model.live="is_yakap" type="checkbox" id="is_yakap" class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <label for="is_yakap" class="text-sm font-medium text-gray-700">YAKAP Consultation</label>
                    </div>
                    @if($is_yakap)
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Empanelment</label>
                            <select wire:model="empanelment_id" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                <option value="">— Select Empanelment —</option>
                                @foreach($yakapEmpanelments as $e)
                                    <option value="{{ $e->id }}">{{ $e->empanelment_code }} — {{ $e->patient?->last_name }}, {{ $e->patient?->first_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="px-4 py-2 border rounded-xl text-gray-600">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-xl">Save Encounter</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Vitals Modal --}}
    @if($showVitalsModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showVitalsModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 z-10 border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-rose-50/80 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-gray-900">Nursing Check-in (Vitals & Complaint)</h3>
                <button wire:click="$set('showVitalsModal', false)" class="p-1.5 hover:bg-gray-100 rounded-lg text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveVitals" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chief Complaint</label>
                    <textarea wire:model="chief_complaint" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Why is the patient here?"></textarea>
                </div>
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">BP Systolic</label>
                        <input type="number" wire:model="vital_bp_systolic" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">BP Diastolic</label>
                        <input type="number" wire:model="vital_bp_diastolic" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Heart Rate</label>
                        <input type="number" wire:model="vital_heart_rate" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Temperature (°C)</label>
                        <input type="number" step="0.1" wire:model="vital_temperature" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Respiratory Rate</label>
                        <input type="number" wire:model="vital_respiratory_rate" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">O2 Saturation (%)</label>
                        <input type="number" step="0.1" wire:model="vital_o2_saturation" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Weight (kg)</label>
                        <input type="number" step="0.1" wire:model="vital_weight" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Height (cm)</label>
                        <input type="number" step="0.1" wire:model="vital_height" class="w-full px-3 py-2 border rounded-xl text-sm">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="$set('showVitalsModal', false)" class="px-4 py-2 border rounded-xl text-gray-600">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl">Save Vitals</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @else
    {{-- CONSULTATION FORM VIEW (Doctor View) --}}
    <div class="flex flex-col md:flex-row gap-6">
        <div class="w-full md:w-1/4 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ $consultation->patient?->full_name }}</h3>
                    <button wire:click="closeConsultation" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Back
                    </button>
                </div>
                <div class="space-y-3 text-sm">
                    <div><span class="text-gray-500">Code:</span> <span class="font-mono font-medium text-gray-800">{{ $consultation->consultation_code }}</span></div>
                    <div><span class="text-gray-500">Clinic:</span> <span class="font-medium text-gray-800">{{ $consultation->opdClinic?->name }}</span></div>
                    <div><span class="text-gray-500">Date:</span> <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($consultation->consultation_datetime)->format('M d, Y') }}</span></div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Vitals</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 p-2 rounded-lg"><span class="text-gray-400 block mb-0.5">BP</span><span class="font-medium text-gray-900">{{ $consultation->vital_bp_systolic ?? '-' }}/{{ $consultation->vital_bp_diastolic ?? '-' }}</span></div>
                        <div class="bg-gray-50 p-2 rounded-lg"><span class="text-gray-400 block mb-0.5">HR</span><span class="font-medium text-gray-900">{{ $consultation->vital_heart_rate ?? '-' }} bpm</span></div>
                        <div class="bg-gray-50 p-2 rounded-lg"><span class="text-gray-400 block mb-0.5">Temp</span><span class="font-medium text-gray-900">{{ $consultation->vital_temperature ?? '-' }} °C</span></div>
                        <div class="bg-gray-50 p-2 rounded-lg"><span class="text-gray-400 block mb-0.5">SpO2</span><span class="font-medium text-gray-900">{{ $consultation->vital_o2_saturation ?? '-' }}%</span></div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Chief Complaint</h4>
                    <p class="text-sm text-gray-700 italic">{{ $consultation->chief_complaint ?? 'No complaint recorded.' }}</p>
                </div>

                <div class="mt-6 pt-5 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-light-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Doctor's Orders
                        </h4>
                        <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">OPD</span>
                    </div>
                    <div class="space-y-2">
                        <button type="button" wire:click="openOrderModal('Pharmacy')" class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-emerald-50/70 hover:text-emerald-800 border border-gray-200 hover:border-emerald-200 transition-all shadow-xs flex items-center justify-between group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </span>
                                <span class="truncate">Medications</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <button type="button" wire:click="openOrderModal('Lab')" class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-purple-50/70 hover:text-purple-800 border border-gray-200 hover:border-purple-200 transition-all shadow-xs flex items-center justify-between group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                </span>
                                <span class="truncate">Laboratory Tests</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-purple-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <button type="button" wire:click="openOrderModal('CSR')" class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-amber-50/70 hover:text-amber-800 border border-gray-200 hover:border-amber-200 transition-all shadow-xs flex items-center justify-between group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </span>
                                <span class="truncate">Supplies (CSR)</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-amber-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <button type="button" wire:click="openOrderModal('Radiology')" class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-indigo-50/70 hover:text-indigo-800 border border-gray-200 hover:border-indigo-200 transition-all shadow-xs flex items-center justify-between group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </span>
                                <span class="truncate">Radiology / Imaging</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        <button type="button" wire:click="openOrderModal('Procedure')" class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-rose-50/70 hover:text-rose-800 border border-gray-200 hover:border-rose-200 transition-all shadow-xs flex items-center justify-between group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </span>
                                <span class="truncate">Procedures</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-rose-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- CLINICAL DOCUMENTS & REPORTS (TRUE PDF) --}}
                <div class="mt-6 pt-5 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Reports & Certificates
                        </h4>
                        <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200">True PDF</span>
                    </div>
                    <div class="space-y-2">
                        {{-- 1. Doctor Prescription (Rx) --}}
                        <a href="/opd/consultation/{{ $consultation->id }}/pdf/prescription" target="_blank"
                            class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-teal-50/70 hover:text-teal-800 border border-gray-200 hover:border-teal-300 transition-all shadow-xs flex items-center justify-between group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center shrink-0 font-serif font-bold text-base group-hover:scale-105 transition-transform">
                                    ℞
                                </span>
                                <span class="truncate">Prescription (Rx)</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-teal-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>

                        {{-- 2. Medical Certificate --}}
                        <button type="button" wire:click="openMedCertModal({{ $consultation->id }})"
                            class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-blue-50/70 hover:text-blue-800 border border-gray-200 hover:border-blue-300 transition-all shadow-xs flex items-center justify-between group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </span>
                                <span class="truncate">Medical Certificate</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        {{-- 3. Clinical Abstract / Summary --}}
                        <a href="/opd/consultation/{{ $consultation->id }}/pdf/summary" target="_blank"
                            class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-indigo-50/70 hover:text-indigo-800 border border-gray-200 hover:border-indigo-300 transition-all shadow-xs flex items-center justify-between group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <span class="truncate">Clinical Abstract</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>

                        {{-- 4. Diagnostic Orders Requisition Slip --}}
                        <a href="/opd/consultation/{{ $consultation->id }}/pdf/orders" target="_blank"
                            class="w-full text-left px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-slate-50/70 hover:text-slate-800 border border-gray-200 hover:border-slate-300 transition-all shadow-xs flex items-center justify-between group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </span>
                                <span class="truncate">Order Requisition</span>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-slate-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-3/4 space-y-6">
            @if (session()->has('message'))
                <div class="bg-emerald-50 text-emerald-800 border border-emerald-200 p-4 rounded-xl flex items-center shadow-xs">
                    <svg class="w-5 h-5 mr-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-semibold">{{ session('message') }}</span>
                </div>
            @endif

            <form wire:submit="saveConsultation" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Clinical Documentation (SOAP)
                    </h3>
                    <button type="submit" class="px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">Save Notes</button>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Subjective (HPI)</label>
                        <textarea wire:model="history_of_present_illness" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 text-sm" placeholder="Patient's subjective description of symptoms..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Objective (Physical Exam)</label>
                        <textarea wire:model="physical_exam_findings" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 text-sm" placeholder="Clinical findings and observations..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Assessment (Diagnosis)</label>
                        <textarea wire:model="assessment" rows="2" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 text-sm" placeholder="Clinical diagnosis..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Plan (Treatment)</label>
                        <textarea wire:model="treatment_plan" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 text-sm" placeholder="Plan of care, medications prescribed..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Disposition</label>
                            <select wire:model="disposition" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 text-sm">
                                <option value="">— Select Disposition —</option>
                                <option value="Discharged / Sent Home">Discharged / Sent Home</option>
                                <option value="Admitted">Admitted</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Follow-up Required">Follow-up Required</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Follow-up Date</label>
                            <input type="date" wire:model="next_follow_up_date" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 text-sm">
                        </div>
                    </div>
                </div>
            </form>
            
            {{-- Placed Orders View (Pharmacy, Lab, etc.) --}}
            @php
                $pharmCount = $consultation->pharmacyRequests->count();
                $labCount = $consultation->labRequests->count();
                $csrCount = $consultation->csrRequests->count();
                $radCount = $consultation->radiologyRequests->count();
                $procCount = $consultation->procedureRequests->count();
                $totalOrdersCount = $pharmCount + $labCount + $csrCount + $radCount + $procCount;
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 backdrop-blur-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-light-blue-100 text-light-blue-700 flex items-center justify-center shadow-xs shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 tracking-tight">Active Doctor's Orders</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $totalOrdersCount > 0 ? 'bg-light-blue-100 text-light-blue-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $totalOrdersCount }} {{ \Str::plural('order', $totalOrdersCount) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">Live diagnostic requests, prescribed medications, and medical supplies for this OPD encounter.</p>
                        </div>
                    </div>
                    <div class="flex items-center flex-wrap gap-1.5">
                        <button type="button" wire:click="openOrderModal('Pharmacy')" class="inline-flex items-center px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/60 rounded-lg text-xs font-semibold transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>+ Meds
                        </button>
                        <button type="button" wire:click="openOrderModal('Lab')" class="inline-flex items-center px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200/60 rounded-lg text-xs font-semibold transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mr-1.5"></span>+ Labs
                        </button>
                        <button type="button" wire:click="openOrderModal('CSR')" class="inline-flex items-center px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/60 rounded-lg text-xs font-semibold transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>+ Supplies
                        </button>
                        <button type="button" wire:click="openOrderModal('Radiology')" class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/60 rounded-lg text-xs font-semibold transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5"></span>+ Imaging
                        </button>
                        <button type="button" wire:click="openOrderModal('Procedure')" class="inline-flex items-center px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200/60 rounded-lg text-xs font-semibold transition-colors">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>+ Procedures
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    {{-- 1. Pharmacy / Medications --}}
                    @if($pharmCount > 0)
                        <div class="rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                            <div class="px-5 py-3 border-b border-gray-100 bg-emerald-50/50 flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    Medications (Pharmacy)
                                    <span class="text-xs font-medium text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">{{ $pharmCount }}</span>
                                </h4>
                                <button type="button" wire:click="openOrderModal('Pharmacy')" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 inline-flex items-center">
                                    + Add Medication
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="bg-gray-50/60 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left">Request #</th>
                                            <th class="px-4 py-2.5 text-left">Medication / Sig</th>
                                            <th class="px-4 py-2.5 text-center">Freq &amp; Route</th>
                                            <th class="px-4 py-2.5 text-center">Qty</th>
                                            <th class="px-4 py-2.5 text-center">Priority</th>
                                            <th class="px-4 py-2.5 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 bg-white">
                                        @foreach($consultation->pharmacyRequests as $pr)
                                            @foreach($pr->items as $pri)
                                                @php
                                                    $statusPill = match($pri->status) {
                                                        'Completed', 'Dispensed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        'In Progress', 'Processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    };
                                                    $priPriority = $pr->priority ?? 'Routine';
                                                    $prioBadge = match($priPriority) {
                                                        'STAT' => 'bg-red-50 text-red-700 border-red-200',
                                                        'Urgent' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                                                    };
                                                @endphp
                                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-800">
                                                        {{ $pr->request_code }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="font-semibold text-gray-900">{{ $pri->item_name }}</div>
                                                        @if($pri->brand_name)
                                                            <div class="text-xs text-gray-400 font-medium">{{ $pri->brand_name }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-xs text-gray-600">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 font-medium text-gray-700">
                                                            {{ $pri->frequency ?? '—' }} &bull; {{ $pri->route ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center font-mono font-bold text-gray-800 text-xs">
                                                        {{ $pri->quantity }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $prioBadge }}">
                                                            @if($priPriority === 'STAT')<span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1 animate-pulse"></span>@endif
                                                            {{ $priPriority }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusPill }}">
                                                            {{ $pri->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- 2. Laboratory Tests --}}
                    @if($labCount > 0)
                        <div class="rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                            <div class="px-5 py-3 border-b border-gray-100 bg-purple-50/50 flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                    Laboratory Tests
                                    <span class="text-xs font-medium text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">{{ $labCount }}</span>
                                </h4>
                                <button type="button" wire:click="openOrderModal('Lab')" class="text-xs font-semibold text-purple-700 hover:text-purple-800 inline-flex items-center">
                                    + Add Lab Test
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="bg-gray-50/60 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left">Request #</th>
                                            <th class="px-4 py-2.5 text-left">Test Name</th>
                                            <th class="px-4 py-2.5 text-right">Standard Charge</th>
                                            <th class="px-4 py-2.5 text-center">Priority</th>
                                            <th class="px-4 py-2.5 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 bg-white">
                                        @foreach($consultation->labRequests as $lr)
                                            @foreach($lr->items as $lri)
                                                @php
                                                    $statusPill = match($lri->status) {
                                                        'Completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        'In Progress', 'Processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    };
                                                    $lrPriority = $lr->priority ?? 'Routine';
                                                    $prioBadge = match($lrPriority) {
                                                        'STAT' => 'bg-red-50 text-red-700 border-red-200',
                                                        'Urgent' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                                                    };
                                                @endphp
                                                <tr class="hover:bg-purple-50/30 transition-colors">
                                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-800">
                                                        {{ $lr->request_code }}
                                                    </td>
                                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                                        {{ $lri->test_name }}
                                                    </td>
                                                    <td class="px-4 py-3 text-right font-mono text-gray-700 text-xs">
                                                        &#8369;{{ number_format((float)($lri->charge_amount ?? 0), 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $prioBadge }}">
                                                            @if($lrPriority === 'STAT')<span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1 animate-pulse"></span>@endif
                                                            {{ $lrPriority }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusPill }}">
                                                            {{ $lri->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- 3. Medical Supplies (CSR) --}}
                    @if($csrCount > 0)
                        <div class="rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                            <div class="px-5 py-3 border-b border-gray-100 bg-amber-50/50 flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    Medical Supplies (CSR)
                                    <span class="text-xs font-medium text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">{{ $csrCount }}</span>
                                </h4>
                                <button type="button" wire:click="openOrderModal('CSR')" class="text-xs font-semibold text-amber-700 hover:text-amber-800 inline-flex items-center">
                                    + Add Supply Item
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="bg-gray-50/60 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left">Request #</th>
                                            <th class="px-4 py-2.5 text-left">Item Name</th>
                                            <th class="px-4 py-2.5 text-center">Qty &amp; Unit</th>
                                            <th class="px-4 py-2.5 text-right">Total Cost</th>
                                            <th class="px-4 py-2.5 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 bg-white">
                                        @foreach($consultation->csrRequests as $cr)
                                            @foreach($cr->items as $cri)
                                                @php
                                                    $statusPill = match($cri->status) {
                                                        'Completed', 'Dispensed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        'In Progress', 'Processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    };
                                                @endphp
                                                <tr class="hover:bg-amber-50/30 transition-colors">
                                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-800">
                                                        {{ $cr->request_code }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="font-semibold text-gray-900">{{ $cri->item_name }}</div>
                                                        @if($cri->item_code)
                                                            <div class="text-xs font-mono text-gray-400">{{ $cri->item_code }}</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-xs text-gray-700">
                                                        <span class="font-mono font-bold">{{ $cri->quantity }}</span> {{ $cri->supply_unit_name ?? 'pcs' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-right font-mono text-gray-700 text-xs">
                                                        &#8369;{{ number_format((float)($cri->total_cost ?? 0), 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusPill }}">
                                                            {{ $cri->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- 4. Radiology & Imaging --}}
                    @if($radCount > 0)
                        <div class="rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                            <div class="px-5 py-3 border-b border-gray-100 bg-indigo-50/50 flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                    Radiology &amp; Imaging
                                    <span class="text-xs font-medium text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-full">{{ $radCount }}</span>
                                </h4>
                                <button type="button" wire:click="openOrderModal('Radiology')" class="text-xs font-semibold text-indigo-700 hover:text-indigo-800 inline-flex items-center">
                                    + Add Imaging Exam
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="bg-gray-50/60 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left">Request #</th>
                                            <th class="px-4 py-2.5 text-left">Exam Name</th>
                                            <th class="px-4 py-2.5 text-center">Modality</th>
                                            <th class="px-4 py-2.5 text-right">Standard Charge</th>
                                            <th class="px-4 py-2.5 text-center">Priority</th>
                                            <th class="px-4 py-2.5 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 bg-white">
                                        @foreach($consultation->radiologyRequests as $rr)
                                            @foreach($rr->items as $rri)
                                                @php
                                                    $statusPill = match($rri->status) {
                                                        'Complete', 'Completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        'In Progress', 'Processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    };
                                                    $rrPriority = $rr->priority ?? 'Routine';
                                                    $prioBadge = match($rrPriority) {
                                                        'STAT' => 'bg-red-50 text-red-700 border-red-200',
                                                        'Urgent' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                                                    };
                                                @endphp
                                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-800">
                                                        {{ $rr->request_code }}
                                                    </td>
                                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                                        {{ $rri->exam_name }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-xs font-semibold">
                                                            {{ $rri->modality_name ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-right font-mono text-gray-700 text-xs">
                                                        &#8369;{{ number_format((float)($rri->charge_amount ?? 0), 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $prioBadge }}">
                                                            @if($rrPriority === 'STAT')<span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1 animate-pulse"></span>@endif
                                                            {{ $rrPriority }}
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusPill }}">
                                                            {{ $rri->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- 5. Clinical Procedures --}}
                    @if($procCount > 0)
                        <div class="rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                            <div class="px-5 py-3 border-b border-gray-100 bg-rose-50/50 flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                    Clinical Procedures
                                    <span class="text-xs font-medium text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full">{{ $procCount }}</span>
                                </h4>
                                <button type="button" wire:click="openOrderModal('Procedure')" class="text-xs font-semibold text-rose-700 hover:text-rose-800 inline-flex items-center">
                                    + Add Procedure
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 text-sm">
                                    <thead class="bg-gray-50/60 text-gray-500 text-xs font-semibold uppercase tracking-wider">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left">Request #</th>
                                            <th class="px-4 py-2.5 text-left">Procedure</th>
                                            <th class="px-4 py-2.5 text-center">Category</th>
                                            <th class="px-4 py-2.5 text-center">Qty</th>
                                            <th class="px-4 py-2.5 text-right">Total Charge</th>
                                            <th class="px-4 py-2.5 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 bg-white">
                                        @foreach($consultation->procedureRequests as $pr)
                                            @foreach($pr->items as $pri)
                                                @php
                                                    $statusPill = match($pri->status) {
                                                        'Completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        'In Progress', 'Processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                                        default => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    };
                                                @endphp
                                                <tr class="hover:bg-rose-50/30 transition-colors">
                                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-800">
                                                        {{ $pr->request_code }}
                                                    </td>
                                                    <td class="px-4 py-3 font-semibold text-gray-900">
                                                        {{ $pri->procedure_name }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                                                        {{ $pri->category_name ?? '—' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center font-mono font-bold text-gray-800 text-xs">
                                                        {{ $pri->quantity }}
                                                    </td>
                                                    <td class="px-4 py-3 text-right font-mono text-gray-700 text-xs">
                                                        &#8369;{{ number_format((float)($pri->total_charge ?? 0), 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusPill }}">
                                                            {{ $pri->status }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Empty State --}}
                    @if($totalOrdersCount === 0)
                        <div class="text-center py-14 bg-gray-50/60 rounded-xl border border-dashed border-gray-200">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-light-blue-50 text-light-blue-600 mb-3 shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <h4 class="text-sm font-bold text-gray-800">No Active Doctor's Orders</h4>
                            <p class="text-xs text-gray-500 max-w-md mx-auto mt-1">Prescribe medications or order laboratory tests, CSR supplies, imaging, or procedures using the action buttons above or in the sidebar.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ORDER MODALS (Pharmacy, Lab, CSR, Radiology, Procedure) --}}

    {{-- 1. Pharmacy Order Modal --}}
    @if($showOrderPharmacyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto animate-in fade-in duration-200">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden border border-gray-100 my-8 flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50/80 via-white to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Order Medications (Pharmacy)</h3>
                        <p class="text-xs text-gray-500">Patient: <span class="font-semibold text-gray-700">{{ $consultation->patient?->full_name }}</span> &bull; <span class="font-mono text-gray-600">{{ $consultation->consultation_code }}</span></p>
                    </div>
                </div>
                <button type="button" wire:click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit="placePharmacyOrder" class="flex-1 overflow-y-auto p-6 space-y-4">
                {{-- Controls: Priority & Search --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Priority</label>
                        <select wire:model="orderPriority" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-xs">
                            <option value="Routine">Routine</option>
                            <option value="Urgent">Urgent</option>
                            <option value="STAT">STAT (Immediate)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Search Formulary</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                            </div>
                            <input wire:model.live.debounce.250ms="orderSearch" type="text" placeholder="Search generic name, brand, or code..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-xs">
                        </div>
                    </div>
                </div>

                {{-- Drugs Selection List --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Available Medicines</label>
                        <span class="text-xs text-gray-400">{{ $drugs->count() }} available</span>
                    </div>
                    <div class="max-h-64 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100 bg-white">
                        @forelse($drugs as $drug)
                            @php $isSelected = in_array($drug->id, $orderSelection); @endphp
                            <div class="p-3 transition-colors {{ $isSelected ? 'bg-emerald-50/40 border-l-4 border-l-emerald-500' : 'hover:bg-gray-50/70' }}">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model.live="orderSelection" value="{{ $drug->id }}" id="drug_{{ $drug->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 shrink-0">
                                    <label for="drug_{{ $drug->id }}" class="flex-1 min-w-0 cursor-pointer">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-bold text-gray-900">{{ $drug->generic_name }}</span>
                                            @if($drug->drug_strength)
                                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-700">{{ $drug->drug_strength }}</span>
                                            @endif
                                            @if($drug->dosage_form)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 uppercase">{{ $drug->dosage_form }}</span>
                                            @endif
                                        </div>
                                        @if($drug->brand_name)
                                            <p class="text-xs text-gray-500 font-medium">Brand: {{ $drug->brand_name }}</p>
                                        @endif
                                    </label>
                                    <div class="text-right shrink-0">
                                        <span class="text-xs font-mono font-semibold text-gray-600">&#8369;{{ number_format((float)($drug->unit_cost ?? 0), 2) }}</span>
                                    </div>
                                </div>

                                {{-- Prescription Sig Inputs (Shown when checked) --}}
                                @if($isSelected)
                                    <div class="mt-3 pt-3 border-t border-emerald-100 grid grid-cols-1 sm:grid-cols-3 gap-2.5 pl-7">
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Quantity</label>
                                            <input type="number" wire:model="orderQuantities.{{ $drug->id }}" min="1" value="1" placeholder="Qty" class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-sm text-center font-bold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-xs">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Frequency</label>
                                            <input type="text" wire:model="orderFrequencies.{{ $drug->id }}" placeholder="e.g. BID, TID, q8h, PRN" class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-xs">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Route</label>
                                            <select wire:model="orderRoutes.{{ $drug->id }}" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-xs">
                                                <option value="">— Select Route —</option>
                                                <option value="PO" {{ $drug->route === 'PO' ? 'selected' : '' }}>PO (Oral)</option>
                                                <option value="IV" {{ $drug->route === 'IV' ? 'selected' : '' }}>IV (Intravenous)</option>
                                                <option value="IM" {{ $drug->route === 'IM' ? 'selected' : '' }}>IM (Intramuscular)</option>
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
                        @empty
                            <div class="p-8 text-center text-gray-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <p class="text-sm font-medium">No medications found matching "{{ $orderSearch }}".</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Prescription Notes --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Clinical Instructions &amp; Sig (Optional)</label>
                    <textarea wire:model="orderNotes" rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-xs" placeholder="Add specific prescription notes or instructions for the dispensing pharmacist..."></textarea>
                </div>

                {{-- Modal Footer --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs font-semibold text-gray-500">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ count($orderSelection) }} {{ \Str::plural('medication', count($orderSelection)) }} selected
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" @if(count($orderSelection) === 0) disabled @endif class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                            <svg wire:loading wire:target="placePharmacyOrder" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Submit Prescription
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- 2. Laboratory Order Modal --}}
    @if($showOrderLabModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto animate-in fade-in duration-200">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden border border-gray-100 my-8 flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50/80 via-white to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Order Laboratory Tests</h3>
                        <p class="text-xs text-gray-500">Patient: <span class="font-semibold text-gray-700">{{ $consultation->patient?->full_name }}</span> &bull; <span class="font-mono text-gray-600">{{ $consultation->consultation_code }}</span></p>
                    </div>
                </div>
                <button type="button" wire:click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit="placeLabOrder" class="flex-1 overflow-y-auto p-6 space-y-4">
                {{-- Controls: Priority & Search --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Priority</label>
                        <select wire:model="orderPriority" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 shadow-xs">
                            <option value="Routine">Routine</option>
                            <option value="Urgent">Urgent</option>
                            <option value="STAT">STAT (Immediate)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Search Tests</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                            </div>
                            <input wire:model.live.debounce.250ms="orderSearch" type="text" placeholder="Search test name or code..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 shadow-xs">
                        </div>
                    </div>
                </div>

                {{-- Lab Tests Selection List --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Available Diagnostic Tests</label>
                        <span class="text-xs text-gray-400">{{ $labTests->count() }} tests</span>
                    </div>
                    <div class="max-h-64 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100 bg-white">
                        @forelse($labTests as $test)
                            @php $isSelected = in_array($test->id, $orderSelection); @endphp
                            <label class="flex items-center justify-between p-3.5 cursor-pointer transition-colors {{ $isSelected ? 'bg-purple-50/50' : 'hover:bg-gray-50/70' }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <input type="checkbox" wire:model.live="orderSelection" value="{{ $test->id }}" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 shrink-0">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $test->name }}</p>
                                        @if($test->test_code)
                                            <span class="font-mono text-[11px] text-gray-400">{{ $test->test_code }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs font-mono font-semibold text-gray-700 ml-4 shrink-0">
                                    &#8369;{{ number_format((float)($test->standard_charge ?? 0), 2) }}
                                </span>
                            </label>
                        @empty
                            <div class="p-8 text-center text-gray-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                <p class="text-sm font-medium">No lab tests found matching "{{ $orderSearch }}".</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Clinical Notes --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Clinical Indication / Notes (Optional)</label>
                    <textarea wire:model="orderNotes" rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 shadow-xs" placeholder="Relevant diagnostic indications, history, or special handling instructions..."></textarea>
                </div>

                {{-- Modal Footer --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs font-semibold text-gray-500">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-purple-50 text-purple-800">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            {{ count($orderSelection) }} {{ \Str::plural('test', count($orderSelection)) }} selected
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" @if(count($orderSelection) === 0) disabled @endif class="px-5 py-2 bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                            <svg wire:loading wire:target="placeLabOrder" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Submit Lab Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- 3. CSR Supplies Order Modal --}}
    @if($showOrderCsrModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto animate-in fade-in duration-200">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden border border-gray-100 my-8 flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50/80 via-white to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Order Medical Supplies (CSR)</h3>
                        <p class="text-xs text-gray-500">Patient: <span class="font-semibold text-gray-700">{{ $consultation->patient?->full_name }}</span> &bull; <span class="font-mono text-gray-600">{{ $consultation->consultation_code }}</span></p>
                    </div>
                </div>
                <button type="button" wire:click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit="placeCsrOrder" class="flex-1 overflow-y-auto p-6 space-y-4">
                {{-- Search Bar --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Search CSR Stock</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                        </div>
                        <input wire:model.live.debounce.250ms="orderSearch" type="text" placeholder="Search item name or supply code..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-xs">
                    </div>
                </div>

                {{-- Supplies Selection List --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Available Medical Supplies</label>
                        <span class="text-xs text-gray-400">{{ $csrItems->count() }} items</span>
                    </div>
                    <div class="max-h-64 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100 bg-white">
                        @forelse($csrItems as $item)
                            @php $isSelected = in_array($item->id, $orderSelection); @endphp
                            <div class="p-3 transition-colors {{ $isSelected ? 'bg-amber-50/40 border-l-4 border-l-amber-500' : 'hover:bg-gray-50/70' }}">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model.live="orderSelection" value="{{ $item->id }}" id="csr_{{ $item->id }}" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 shrink-0">
                                    <label for="csr_{{ $item->id }}" class="flex-1 min-w-0 cursor-pointer">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-bold text-gray-900">{{ $item->name }}</span>
                                            @if($item->unit?->name)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">{{ $item->unit->name }}</span>
                                            @endif
                                        </div>
                                        @if($item->code)
                                            <p class="text-xs font-mono text-gray-400">{{ $item->code }}</p>
                                        @endif
                                    </label>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-xs font-mono font-semibold text-gray-600">&#8369;{{ number_format((float)($item->unit_cost ?? 0), 2) }}</span>
                                        @if($isSelected)
                                            <div class="w-20">
                                                <input type="number" wire:model="orderQuantities.{{ $item->id }}" min="1" value="1" placeholder="Qty" class="w-full px-2 py-1 bg-white border border-gray-200 rounded-lg text-sm text-center font-bold focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-xs">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-sm font-medium">No supplies found matching "{{ $orderSearch }}".</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Notes / Destination (Optional)</label>
                    <textarea wire:model="orderNotes" rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 shadow-xs" placeholder="Instructions or clinic destination for CSR staff..."></textarea>
                </div>

                {{-- Modal Footer --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs font-semibold text-gray-500">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-50 text-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            {{ count($orderSelection) }} {{ \Str::plural('item', count($orderSelection)) }} selected
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" @if(count($orderSelection) === 0) disabled @endif class="px-5 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                            <svg wire:loading wire:target="placeCsrOrder" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Submit CSR Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- 4. Radiology Order Modal --}}
    @if($showOrderRadiologyModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto animate-in fade-in duration-200">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden border border-gray-100 my-8 flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 via-white to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Order Radiology &amp; Imaging</h3>
                        <p class="text-xs text-gray-500">Patient: <span class="font-semibold text-gray-700">{{ $consultation->patient?->full_name }}</span> &bull; <span class="font-mono text-gray-600">{{ $consultation->consultation_code }}</span></p>
                    </div>
                </div>
                <button type="button" wire:click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit="placeRadiologyOrder" class="flex-1 overflow-y-auto p-6 space-y-4">
                {{-- Controls: Priority & Search --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Priority</label>
                        <select wire:model="orderPriority" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs">
                            <option value="Routine">Routine</option>
                            <option value="Urgent">Urgent</option>
                            <option value="STAT">STAT (Immediate)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Search Imaging Exams</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                            </div>
                            <input wire:model.live.debounce.250ms="orderSearch" type="text" placeholder="Search exam name, modality, or code..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs">
                        </div>
                    </div>
                </div>

                {{-- Radiology Exams List --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Available Imaging Procedures</label>
                        <span class="text-xs text-gray-400">{{ $radiologyExams->count() }} exams</span>
                    </div>
                    <div class="max-h-64 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100 bg-white">
                        @forelse($radiologyExams as $exam)
                            @php $isSelected = in_array($exam->id, $orderSelection); @endphp
                            <label class="flex items-center justify-between p-3.5 cursor-pointer transition-colors {{ $isSelected ? 'bg-indigo-50/50' : 'hover:bg-gray-50/70' }}">
                                <div class="flex items-center gap-3 min-w-0">
                                    <input type="checkbox" wire:model.live="orderSelection" value="{{ $exam->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 shrink-0">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-semibold text-gray-900">{{ $exam->name }}</p>
                                            @if($exam->modality?->name)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700">{{ $exam->modality->name }}</span>
                                            @endif
                                        </div>
                                        @if($exam->code)
                                            <span class="font-mono text-[11px] text-gray-400">{{ $exam->code }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs font-mono font-semibold text-gray-700 ml-4 shrink-0">
                                    &#8369;{{ number_format((float)($exam->standard_charge ?? 0), 2) }}
                                </span>
                            </label>
                        @empty
                            <div class="p-8 text-center text-gray-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-sm font-medium">No imaging exams found matching "{{ $orderSearch }}".</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Clinical Indications --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Clinical Indication &amp; Body Region (Optional)</label>
                    <textarea wire:model="orderNotes" rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-xs" placeholder="Specify clinical history, specific view, or anatomical site..."></textarea>
                </div>

                {{-- Modal Footer --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs font-semibold text-gray-500">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-800">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            {{ count($orderSelection) }} {{ \Str::plural('exam', count($orderSelection)) }} selected
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" @if(count($orderSelection) === 0) disabled @endif class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                            <svg wire:loading wire:target="placeRadiologyOrder" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Submit Radiology Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- 5. Procedure Order Modal --}}
    @if($showOrderProcedureModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto animate-in fade-in duration-200">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden border border-gray-100 my-8 flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-rose-50/80 via-white to-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Order Clinical Procedures</h3>
                        <p class="text-xs text-gray-500">Patient: <span class="font-semibold text-gray-700">{{ $consultation->patient?->full_name }}</span> &bull; <span class="font-mono text-gray-600">{{ $consultation->consultation_code }}</span></p>
                    </div>
                </div>
                <button type="button" wire:click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form wire:submit="placeProcedureOrder" class="flex-1 overflow-y-auto p-6 space-y-4">
                {{-- Search Bar --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Search Procedures</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                        </div>
                        <input wire:model.live.debounce.250ms="orderSearch" type="text" placeholder="Search procedure name or code..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 shadow-xs">
                    </div>
                </div>

                {{-- Procedures Selection List --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Available Procedures</label>
                        <span class="text-xs text-gray-400">{{ $procedures->count() }} procedures</span>
                    </div>
                    <div class="max-h-64 overflow-y-auto rounded-xl border border-gray-200 divide-y divide-gray-100 bg-white">
                        @forelse($procedures as $proc)
                            @php $isSelected = in_array($proc->id, $orderSelection); @endphp
                            <div class="p-3 transition-colors {{ $isSelected ? 'bg-rose-50/40 border-l-4 border-l-rose-500' : 'hover:bg-gray-50/70' }}">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model.live="orderSelection" value="{{ $proc->id }}" id="proc_{{ $proc->id }}" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500 shrink-0">
                                    <label for="proc_{{ $proc->id }}" class="flex-1 min-w-0 cursor-pointer">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-bold text-gray-900">{{ $proc->name }}</span>
                                            @if($proc->category?->name)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">{{ $proc->category->name }}</span>
                                            @endif
                                        </div>
                                        @if($proc->code)
                                            <p class="text-xs font-mono text-gray-400">{{ $proc->code }}</p>
                                        @endif
                                    </label>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-xs font-mono font-semibold text-gray-600">&#8369;{{ number_format((float)($proc->standard_charge ?? 0), 2) }}</span>
                                        @if($isSelected)
                                            <div class="w-20">
                                                <input type="number" wire:model="orderQuantities.{{ $proc->id }}" min="1" value="1" placeholder="Qty" class="w-full px-2 py-1 bg-white border border-gray-200 rounded-lg text-sm text-center font-bold focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 shadow-xs">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-gray-400">
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <p class="text-sm font-medium">No procedures found matching "{{ $orderSearch }}".</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Clinical Notes (Optional)</label>
                    <textarea wire:model="orderNotes" rows="2" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 shadow-xs" placeholder="Special requirements or scheduling notes..."></textarea>
                </div>

                {{-- Modal Footer --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs font-semibold text-gray-500">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-rose-50 text-rose-800">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            {{ count($orderSelection) }} {{ \Str::plural('procedure', count($orderSelection)) }} selected
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" @if(count($orderSelection) === 0) disabled @endif class="px-5 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                            <svg wire:loading wire:target="placeProcedureOrder" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Submit Procedure Request
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MEDICAL CERTIFICATE ISSUANCE MODAL --}}
    @if($showMedCertModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="closeMedCertModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 z-10 border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-900 to-indigo-950 text-white">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-200 flex items-center justify-center border border-blue-400/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold">Issue Medical Certificate</h3>
                        <p class="text-xs text-blue-200/80">Generate an official, professional True PDF certificate</p>
                    </div>
                </div>
                <button wire:click="closeMedCertModal" class="p-1.5 hover:bg-white/10 rounded-lg text-white/70 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                {{-- Certificate Type --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Certificate Type</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center p-3 border rounded-xl cursor-pointer transition-all {{ $medCertType === 'fit_to_work' ? 'border-blue-600 bg-blue-50/50 ring-1 ring-blue-600' : 'border-gray-200 hover:bg-gray-50' }}">
                            <input type="radio" wire:model.live="medCertType" value="fit_to_work" class="text-blue-600 focus:ring-blue-500 mr-2">
                            <div>
                                <span class="block text-xs font-bold text-gray-900">Fit to Work</span>
                                <span class="block text-[10px] text-gray-500">Medical clearance for duties</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 border rounded-xl cursor-pointer transition-all {{ $medCertType === 'illness' ? 'border-blue-600 bg-blue-50/50 ring-1 ring-blue-600' : 'border-gray-200 hover:bg-gray-50' }}">
                            <input type="radio" wire:model.live="medCertType" value="illness" class="text-blue-600 focus:ring-blue-500 mr-2">
                            <div>
                                <span class="block text-xs font-bold text-gray-900">Sick Leave / Illness</span>
                                <span class="block text-[10px] text-gray-500">Excuse with advised rest</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 border rounded-xl cursor-pointer transition-all {{ $medCertType === 'general' ? 'border-blue-600 bg-blue-50/50 ring-1 ring-blue-600' : 'border-gray-200 hover:bg-gray-50' }}">
                            <input type="radio" wire:model.live="medCertType" value="general" class="text-blue-600 focus:ring-blue-500 mr-2">
                            <div>
                                <span class="block text-xs font-bold text-gray-900">General Certificate</span>
                                <span class="block text-[10px] text-gray-500">Standard consultation cert</span>
                            </div>
                        </label>

                        <label class="flex items-center p-3 border rounded-xl cursor-pointer transition-all {{ $medCertType === 'clearance' ? 'border-blue-600 bg-blue-50/50 ring-1 ring-blue-600' : 'border-gray-200 hover:bg-gray-50' }}">
                            <input type="radio" wire:model.live="medCertType" value="clearance" class="text-blue-600 focus:ring-blue-500 mr-2">
                            <div>
                                <span class="block text-xs font-bold text-gray-900">Clearance</span>
                                <span class="block text-[10px] text-gray-500">Activity / travel clearance</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Rest days (if illness) --}}
                @if($medCertType === 'illness')
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Recommended Rest Period</label>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model="medCertRestDays" min="1" max="60" class="w-24 px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-bold text-center">
                        <span class="text-xs text-gray-600 font-medium">calendar day(s) from today</span>
                    </div>
                </div>
                @endif

                {{-- Clinical Diagnosis --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Clinical Diagnosis / Impression</label>
                    <input type="text" wire:model="medCertDiagnosis" class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-medium" placeholder="Clinical assessment or diagnosis...">
                </div>

                {{-- Specific Remarks --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Physician's Remarks / Instructions (Optional)</label>
                    <textarea wire:model="medCertRemarks" rows="2" class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600" placeholder="e.g. Advised to avoid heavy lifting; follow-up after 1 week..."></textarea>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <button type="button" wire:click="closeMedCertModal" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                    Cancel
                </button>

                <button type="button" onclick="
                    const cid = {{ $medCertConsultationId ?? '0' }};
                    const type = '{{ $medCertType }}';
                    const days = {{ $medCertRestDays ?? 3 }};
                    const diag = encodeURIComponent(@this.get('medCertDiagnosis') || '');
                    const rem = encodeURIComponent(@this.get('medCertRemarks') || '');
                    window.open('/opd/consultation/' + cid + '/pdf/certificate?type=' + type + '&rest_days=' + days + '&diagnosis=' + diag + '&recommendation=' + rem, '_blank');
                    @this.closeMedCertModal();
                " class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Generate Official PDF
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

