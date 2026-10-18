<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">ER Triage Records</h2>
            <p class="text-sm text-gray-500 mt-1">Manage emergency triage data and acuity levels.</p>
        </div>
        <button wire:click="openCreate"
                class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Triage Record
        </button>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search patient or complaint…"
                   class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm shadow-sm"/>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage"
                    class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-md bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/90 backdrop-blur-md border-b border-gray-200 sticky top-0 z-10">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors" wire:click="sortBy('triage_datetime')">Datetime @if($sortField==='triage_datetime') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Patient</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Chief Complaint</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Acuity (ESI)</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($records as $item)
                    <tr class="hover:bg-slate-50 transition-colors duration-200 group">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">{{ $item->triage_datetime->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-900">
                            @if($item->patient)
                                <span class="text-light-blue-600">{{ $item->patient->patient_code }}</span> - {{ $item->patient->last_name }}, {{ $item->patient->first_name }}
                            @else
                                <span class="text-gray-400 italic">Unknown</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600 truncate max-w-xs" title="{{ $item->chief_complaint }}">{{ $item->chief_complaint }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border shadow-sm" style="background-color: {{ strtolower($item->acuityLevel?->color_code ?? '#f3f4f6') }}20; color: {{ strtolower($item->acuityLevel?->color_code ?? '#374151') }}; border-color: {{ strtolower($item->acuityLevel?->color_code ?? '#d1d5db') }}50;">
                                <span class="w-2 h-2 rounded-full mr-1.5" style="background-color: {{ strtolower($item->acuityLevel?->color_code ?? '#6b7280') }}"></span>
                                {{ $item->acuityLevel?->name ?? 'Unassigned' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <button wire:click="openTransfer({{ $item->id }})" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors shadow-sm border border-transparent hover:border-emerald-200 bg-white" title="Transfer to ER"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg></button>
                                <button wire:click="openEdit({{ $item->id }})" class="p-2 text-light-blue-600 hover:bg-light-blue-50 rounded-lg transition-colors shadow-sm border border-transparent hover:border-light-blue-200 bg-white" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                <button wire:click="confirmDelete({{ $item->id }})" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors shadow-sm border border-transparent hover:border-red-200 bg-white" title="Archive"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span class="text-lg font-medium text-gray-900">No triage records found</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $records->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl z-10 flex flex-col max-h-[90vh] transform transition-all" x-data="{ step: 1 }" @go-to-step-2.window="step = 2">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-light-blue-50 to-white">
                <h3 class="text-xl font-bold text-gray-900">{{ $isEditing ? 'Edit Triage Record' : 'New Triage Record' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors bg-white hover:bg-gray-100 rounded-full p-1.5"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            
            <!-- Validation Errors Summary -->
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mx-6 mt-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">There were validation errors with your submission</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Patient Search (outside scroll area to prevent dropdown clipping) -->
            @if(!$isEditing && !$is_new_patient && $showModal)
            <div class="px-6 pt-4 pb-3 border-b border-gray-100 bg-white">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Search Patient <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input wire:model.live.debounce.300ms="patientSearch" type="text" placeholder="Search by name or code..."
                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    @if(strlen($patientSearch) >= 2 && empty($patient_id))
                    <div class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-72 overflow-y-auto">
                        @forelse($patients as $p)
                            <div wire:click="$set('patient_id', {{ $p->id }}); $set('patientSearch', '{{ addslashes($p->patient_code . ' - ' . $p->last_name . ', ' . $p->first_name) }}')"
                                 class="px-4 py-3 cursor-pointer hover:bg-light-blue-50 text-sm border-b border-gray-50 last:border-0">
                                <span class="font-medium text-light-blue-700">{{ $p->patient_code }}</span> - <span class="font-semibold">{{ $p->last_name }}</span>, {{ $p->first_name }}
                            </div>
                        @empty
                            <div class="px-4 py-3 text-sm text-gray-500">No patients found.</div>
                        @endforelse
                    </div>
                    @endif
                </div>
                @if($patient_id)
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-sm font-medium text-light-blue-700">
                            Selected: {{ $patients->firstWhere('id', $patient_id)?->patient_code ?? '' }} - {{ $patients->firstWhere('id', $patient_id)?->full_name ?? '' }}
                        </span>
                        <button type="button" wire:click="$set('patient_id', null); $set('patientSearch', '')" class="text-xs text-red-500 hover:text-red-700 font-medium">Clear</button>
                    </div>
                @endif
                @error('patient_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
            </div>
            @endif

            <div class="flex-1 overflow-y-auto p-6 relative z-10">
                <!-- Progress Indicator -->
                <div class="flex items-center justify-center space-x-4 mb-6">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm transition-colors"
                             :class="step === 1 ? 'bg-light-blue-600 text-white' : 'bg-green-500 text-white'">
                            <span x-show="step === 1">1</span>
                            <svg x-show="step === 2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="ml-2 text-sm font-medium" :class="step === 1 ? 'text-gray-900' : 'text-green-600'">Patient Info</span>
                    </div>
                    <div class="w-12 h-0.5 bg-gray-200"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm transition-colors"
                             :class="step === 2 ? 'bg-light-blue-600 text-white' : 'bg-gray-200 text-gray-500'">
                            2
                        </div>
                        <span class="ml-2 text-sm font-medium" :class="step === 2 ? 'text-gray-900' : 'text-gray-500'">Assessment</span>
                    </div>
                </div>

                <form wire:submit="save" id="triageForm">
                    
                    <!-- STEP 1: PATIENT SELECTION / REGISTRATION -->
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
                        
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-lg font-semibold text-gray-800">Patient Details</h4>
                            @if(!$isEditing)
                            <label class="flex items-center cursor-pointer">
                                <div class="relative">
                                    <input type="checkbox" wire:model.live="is_new_patient" class="sr-only">
                                    <div class="block bg-gray-200 w-10 h-6 rounded-full transition-colors" :class="{'bg-light-blue-500': @entangle('is_new_patient')}"></div>
                                    <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform" :class="{'transform translate-x-4': @entangle('is_new_patient')}"></div>
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-700">New Patient</span>
                            </label>
                            @endif
                        </div>

                        @if($is_new_patient)
                            <!-- Quick Registration Form -->
                            <div class="p-5 bg-light-blue-50/50 rounded-xl border border-light-blue-100 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                                        <input wire:model="patient_first_name" type="text" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm @error('patient_first_name') border-red-300 @enderror">
                                        @error('patient_first_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                        <input wire:model="patient_middle_name" type="text" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                                        <input wire:model="patient_last_name" type="text" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm @error('patient_last_name') border-red-300 @enderror">
                                        @error('patient_last_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth <span class="text-red-500">*</span></label>
                                        <input wire:model="patient_dob" type="date" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm @error('patient_dob') border-red-300 @enderror">
                                        @error('patient_dob') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
                                        <select wire:model="patient_gender_id" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm @error('patient_gender_id') border-red-300 @enderror">
                                            <option value="">-- Select --</option>
                                            @foreach($genders as $g)
                                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('patient_gender_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        @else
                            @if($isEditing)
                                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                                    <p class="text-sm text-gray-700 font-medium flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        {{ ErTriageRecord::find($editingId)->patient->patient_code ?? '' }} - {{ ErTriageRecord::find($editingId)->patient->full_name ?? '' }}
                                    </p>
                                </div>
                            @elseif($patient_id)
                                <div class="p-4 bg-light-blue-50/50 rounded-xl border border-light-blue-100">
                                    <p class="text-sm text-light-blue-800 font-medium flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        {{ $patients->firstWhere('id', $patient_id)?->patient_code ?? '' }} - {{ $patients->firstWhere('id', $patient_id)?->full_name ?? '' }}
                                    </p>
                                </div>
                            @else
                                <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100">
                                    <p class="text-sm text-amber-700 flex items-center">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Search for a patient above, or toggle "New Patient" to register.
                                    </p>
                                </div>
                            @endif
                        @endif

                        @if($patient_id || $is_new_patient)
                        <div class="mt-3 pt-3 border-t border-gray-200 flex items-center space-x-3">
                            <input type="checkbox" wire:model.live="is_philhealth_member" id="is_philhealth_member" class="form-checkbox h-5 w-5 text-light-blue-600 border-gray-300 rounded focus:ring-light-blue-500">
                            <label for="is_philhealth_member" class="text-sm font-medium text-gray-700 cursor-pointer">PhilHealth Member</label>
                        </div>
                        @if($is_philhealth_member)
                        <p class="mt-2 text-xs text-light-blue-700 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            A draft eClaim will be created. PhilHealth PIN can be added later from the eClaims module.
                        </p>
                        @endif
                        @endif

                    </div>

                    <!-- STEP 2: CLINICAL ASSESSMENT -->
                    <div x-show="step === 2" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Triage Datetime <span class="text-red-500">*</span></label>
                                <input wire:model="triage_datetime" type="datetime-local"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('triage_datetime') border-red-300 bg-red-50 @enderror">
                                @error('triage_datetime') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Chief Complaint <span class="text-red-500">*</span></label>
                            <textarea wire:model="chief_complaint" rows="2" placeholder="Why is the patient here?"
                                      class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('chief_complaint') border-red-300 @enderror"></textarea>
                            @error('chief_complaint') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Acuity Level (ESI) <span class="text-red-500">*</span></label>
                                <select wire:model="er_acuity_level_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('er_acuity_level_id') border-red-300 @enderror">
                                    <option value="">-- Select Acuity --</option>
                                    @foreach($acuityLevels as $al)
                                        <option value="{{ $al->id }}">{{ $al->code }} - {{ $al->name }}</option>
                                    @endforeach
                                </select>
                                @error('er_acuity_level_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Mobility Status / Arrival</label>
                                <select wire:model="er_mobility_status_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">-- Select Mobility --</option>
                                    @foreach($mobilityStatuses as $ms)
                                        <option value="{{ $ms->id }}">{{ $ms->name }}</option>
                                    @endforeach
                                </select>
                                @error('er_mobility_status_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Vital Signs Group -->
                        <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100 shadow-sm">
                            <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-light-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                Vital Signs
                            </h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Temp (°C)</label>
                                    <input wire:model="vital_temperature" type="number" step="0.1" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-light-blue-500/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">HR (bpm)</label>
                                    <input wire:model="vital_heart_rate" type="number" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-light-blue-500/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">RR (cpm)</label>
                                    <input wire:model="vital_respiratory_rate" type="number" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-light-blue-500/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">SpO2 (%)</label>
                                    <input wire:model="vital_o2_saturation" type="number" step="0.01" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-light-blue-500/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">BP Systolic</label>
                                    <input wire:model="vital_bp_systolic" type="number" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-light-blue-500/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">BP Diastolic</label>
                                    <input wire:model="vital_bp_diastolic" type="number" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-light-blue-500/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Pain (0-10)</label>
                                    <input wire:model="vital_pain_score" type="number" min="0" max="10" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-light-blue-500/20">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">LOC</label>
                                    <select wire:model="level_of_consciousness" class="w-full px-3 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-light-blue-500/20">
                                        <option value="">-- Select --</option>
                                        @foreach($locOptions as $loc)
                                            <option value="{{ $loc }}">{{ $loc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Allergies</label>
                                <textarea wire:model="allergies" rows="2" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                                <textarea wire:model="notes" rows="2" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-white">
                <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-transparent hover:bg-gray-100 rounded-xl transition-colors">Cancel</button>
                <div class="flex items-center space-x-3">
                    <button x-show="step === 2" @click="step = 1" type="button" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors shadow-sm">
                        Back
                    </button>
                    <button x-show="step === 1" wire:click="nextStep" type="button" class="px-6 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors flex items-center">
                        Next <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <button x-show="step === 2" type="submit" form="triageForm" class="px-6 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-xl shadow-sm transition-colors flex items-center">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update Triage' : 'Save Triage' }}</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6 transform transition-all">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Archive Record</h3>
                <p class="text-sm text-gray-500 mb-6">This triage record will be archived.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Archive</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showTransferModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 p-6 transform transition-all">
            <div class="flex items-center justify-between mb-4 pb-3 border-b">
                <h3 class="text-lg font-bold text-gray-900">Transfer to ER Area</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveTransfer" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ER Bed Assignment</label>
                    <select wire:model="transfer_er_bed_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-light-blue-500">
                        <option value="">-- No Bed Assigned --</option>
                        @foreach($beds as $bed)
                            <option value="{{ $bed->id }}">{{ $bed->name }}</option>
                        @endforeach
                    </select>
                    @error('transfer_er_bed_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attending Physician</label>
                    <select wire:model="transfer_attending_physician_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-light-blue-500">
                        <option value="">-- Unassigned --</option>
                        @foreach($physicians as $dr)
                            <option value="{{ $dr->id }}">Dr. {{ $dr->full_name }}</option>
                        @endforeach
                    </select>
                    @error('transfer_attending_physician_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mode of Arrival</label>
                    <select wire:model="transfer_arrival_mode" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-light-blue-500" required>
                        <option value="Walk-in">Walk-in</option>
                        <option value="Ambulance">Ambulance</option>
                        <option value="Wheelchair">Wheelchair</option>
                        <option value="Police Escort">Police Escort</option>
                        <option value="Other">Other</option>
                    </select>
                    @error('transfer_arrival_mode') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Initial Diagnosis (Optional)</label>
                    <textarea wire:model="transfer_initial_diagnosis" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-light-blue-500"></textarea>
                    @error('transfer_initial_diagnosis') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Complete Transfer</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
