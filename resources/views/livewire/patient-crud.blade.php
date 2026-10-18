<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Patient Registration</h2>
            <p class="text-sm text-gray-500 mt-1">Manage patient demographic records.</p>
        </div>
        <button wire:click="openCreate"
                class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Register Patient
        </button>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search code, name, or contact…"
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

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    @foreach([['patient_code', 'Patient Code'], ['last_name', 'Last Name'], ['first_name', 'First Name'], ['date_of_birth', 'Date of Birth'], ['contact_no', 'Contact No']] as [$field, $label])
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('{{ $field }}')">
                        <span class="inline-flex items-center gap-1">{{ $label }}
                            <span class="text-gray-300">@if($sortField === $field){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span>
                        </span>
                    </th>
                    @endforeach
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($items as $item)
                    <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $item->patient_code }}</td>
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $item->last_name }}</td>
                        <td class="px-5 py-4 text-gray-800">{{ $item->first_name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->date_of_birth?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->contact_no ?? '—' }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($item->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200/50 shadow-sm">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200 shadow-sm">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <button wire:click="openEdit({{ $item->id }})" class="p-1.5 text-light-blue-600 hover:bg-light-blue-100 rounded-md transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 12H4"/></svg><span class="text-lg font-medium text-gray-900">No patients found</span><p class="mt-1 text-sm">Try adjusting your search filters.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl z-10 overflow-hidden flex flex-col max-h-[90vh]" x-data="{ activeTab: 1 }">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50/50 flex-shrink-0">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isEditing ? 'Edit Patient' : 'Register New Patient' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            
            <div class="flex border-b border-gray-200 bg-gray-50/50 flex-shrink-0 overflow-x-auto">
                <button type="button" @click="activeTab = 1" :class="{'text-light-blue-600 border-b-2 border-light-blue-600 font-semibold bg-white': activeTab === 1, 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 font-medium': activeTab !== 1}" class="px-5 py-3.5 text-sm transition-colors whitespace-nowrap">1. Identity</button>
                <button type="button" @click="activeTab = 2" :class="{'text-light-blue-600 border-b-2 border-light-blue-600 font-semibold bg-white': activeTab === 2, 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 font-medium': activeTab !== 2}" class="px-5 py-3.5 text-sm transition-colors whitespace-nowrap">2. Demographics</button>
                <button type="button" @click="activeTab = 3" :class="{'text-light-blue-600 border-b-2 border-light-blue-600 font-semibold bg-white': activeTab === 3, 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 font-medium': activeTab !== 3}" class="px-5 py-3.5 text-sm transition-colors whitespace-nowrap">3. Family & IDs</button>
                <button type="button" @click="activeTab = 4" :class="{'text-light-blue-600 border-b-2 border-light-blue-600 font-semibold bg-white': activeTab === 4, 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 font-medium': activeTab !== 4}" class="px-5 py-3.5 text-sm transition-colors whitespace-nowrap">4. Contact & Address</button>
                <button type="button" @click="activeTab = 5" :class="{'text-light-blue-600 border-b-2 border-light-blue-600 font-semibold bg-white': activeTab === 5, 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 font-medium': activeTab !== 5}" class="px-5 py-3.5 text-sm transition-colors whitespace-nowrap">5. Clinical</button>
            </div>

            <form wire:submit="save" class="flex flex-col overflow-hidden">
                <div class="px-6 py-5 overflow-y-auto flex-grow" style="max-height: 50vh;">
                    
                    {{-- Tab 1: Identity --}}
                    <div x-show="activeTab === 1" x-transition.opacity>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="patient_code" class="block text-sm font-medium text-gray-700 mb-1.5">Patient Code <span class="text-red-500">*</span></label>
                                <input wire:model="patient_code" id="patient_code" type="text" maxlength="20" readonly
                                       class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-700 cursor-not-allowed">
                                @error('patient_code') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" wire:model.live="is_philhealth_member" class="form-checkbox h-5 w-5 text-light-blue-600 border-gray-300 rounded focus:ring-light-blue-500">
                                    <span class="text-sm font-medium text-gray-700">PhilHealth Member</span>
                                </label>
                            </div>
                        </div>

                        @if($is_philhealth_member)
                        <div class="mt-4 p-4 bg-light-blue-50/50 rounded-xl border border-light-blue-100">
                            <p class="text-xs text-light-blue-700 font-medium mb-3 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                A draft eClaim will be created automatically. You can complete the PhilHealth PIN and other details from the eClaims module later.
                            </p>
                            <div>
                                <label for="philhealth_member_id" class="block text-sm font-medium text-gray-700 mb-1.5">Link to Member Record <span class="text-gray-400 font-normal">(optional)</span></label>
                                <select wire:model="philhealth_member_id" id="philhealth_member_id"
                                        class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select existing member record (if available) —</option>
                                    @foreach($philhealthMembers as $m)
                                        <option value="{{ $m->id }}">{{ $m->pin }} — {{ $m->last_name }}, {{ $m->first_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                                <input wire:model="last_name" id="last_name" type="text" maxlength="60"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('last_name') border-red-300 bg-red-50 @enderror">
                                @error('last_name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                                <input wire:model="first_name" id="first_name" type="text" maxlength="60"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('first_name') border-red-300 bg-red-50 @enderror">
                                @error('first_name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="middle_name" class="block text-sm font-medium text-gray-700 mb-1.5">Middle Name</label>
                                <input wire:model="middle_name" id="middle_name" type="text" maxlength="60"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                            <div>
                                <label for="suffix" class="block text-sm font-medium text-gray-700 mb-1.5">Suffix</label>
                                <select wire:model="suffix" id="suffix"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">—</option><option value="Jr.">Jr.</option><option value="Sr.">Sr.</option><option value="II">II</option><option value="III">III</option><option value="IV">IV</option><option value="V">V</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-4">
                            <div>
                                <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1.5">Date of Birth</label>
                                <input wire:model="date_of_birth" id="date_of_birth" type="date"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('date_of_birth') border-red-300 bg-red-50 @enderror">
                                @error('date_of_birth') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label for="place_of_birth" class="block text-sm font-medium text-gray-700 mb-1.5">Place of Birth</label>
                                <input wire:model="place_of_birth" id="place_of_birth" type="text" maxlength="255"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                            <div>
                                <label for="maiden_name" class="block text-sm font-medium text-gray-700 mb-1.5">Maiden Name</label>
                                <input wire:model="maiden_name" id="maiden_name" type="text" maxlength="60"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Demographics --}}
                    <div x-show="activeTab === 2" x-transition.opacity style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="gender_id" class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                                <select wire:model="gender_id" id="gender_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($genders as $g)
                                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="civil_status_id" class="block text-sm font-medium text-gray-700 mb-1.5">Civil Status</label>
                                <select wire:model="civil_status_id" id="civil_status_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($civilStatuses as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="religion_id" class="block text-sm font-medium text-gray-700 mb-1.5">Religion</label>
                                <select wire:model="religion_id" id="religion_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($religions as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="occupation_id" class="block text-sm font-medium text-gray-700 mb-1.5">Occupation</label>
                                <select wire:model="occupation_id" id="occupation_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($occupations as $o)
                                        <option value="{{ $o->id }}">{{ $o->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="blood_type_id" class="block text-sm font-medium text-gray-700 mb-1.5">Blood Type</label>
                                <select wire:model="blood_type_id" id="blood_type_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($bloodTypes as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="nationality_id" class="block text-sm font-medium text-gray-700 mb-1.5">Nationality</label>
                                <select wire:model="nationality_id" id="nationality_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($nationalities as $n)
                                        <option value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="preferred_language" class="block text-sm font-medium text-gray-700 mb-1.5">Preferred Language</label>
                                <input wire:model="preferred_language" id="preferred_language" type="text" maxlength="60"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Tab 3: Identifiers & Family --}}
                    <div x-show="activeTab === 3" x-transition.opacity style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="national_id_no" class="block text-sm font-medium text-gray-700 mb-1.5">National ID No.</label>
                                <input wire:model="national_id_no" id="national_id_no" type="text" maxlength="30"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                            <div>
                                <label for="passport_no" class="block text-sm font-medium text-gray-700 mb-1.5">Passport No.</label>
                                <input wire:model="passport_no" id="passport_no" type="text" maxlength="30"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="father_name" class="block text-sm font-medium text-gray-700 mb-1.5">Father's Name</label>
                                <input wire:model="father_name" id="father_name" type="text" maxlength="120"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                            <div>
                                <label for="mother_maiden_name" class="block text-sm font-medium text-gray-700 mb-1.5">Mother's Maiden Name</label>
                                <input wire:model="mother_maiden_name" id="mother_maiden_name" type="text" maxlength="120"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                            <div>
                                <label for="spouse_name" class="block text-sm font-medium text-gray-700 mb-1.5">Spouse's Name</label>
                                <input wire:model="spouse_name" id="spouse_name" type="text" maxlength="120"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Tab 4: Contact & Address --}}
                    <div x-show="activeTab === 4" x-transition.opacity style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="contact_no" class="block text-sm font-medium text-gray-700 mb-1.5">Contact No</label>
                                <input wire:model="contact_no" id="contact_no" type="text" maxlength="30" placeholder="e.g. 09171234567"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                                <input wire:model="email" id="email" type="email" maxlength="100"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="address_street" class="block text-sm font-medium text-gray-700 mb-1.5">Street / Building</label>
                            <input wire:model="address_street" id="address_street" type="text" maxlength="255"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                                <select wire:model.live="country_id" id="country_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($countries as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="region_id" class="block text-sm font-medium text-gray-700 mb-1.5">Region</label>
                                <select wire:model.live="region_id" id="region_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($regions as $r)
                                        <option value="{{ $r['id'] }}">{{ $r['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="province_id" class="block text-sm font-medium text-gray-700 mb-1.5">Province/State</label>
                                <select wire:model.live="province_id" id="province_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($provinces as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="city_id" class="block text-sm font-medium text-gray-700 mb-1.5">City/Municipality</label>
                                <select wire:model.live="city_id" id="city_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($cities as $ct)
                                        <option value="{{ $ct['id'] }}">{{ $ct['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="barangay_id" class="block text-sm font-medium text-gray-700 mb-1.5">Barangay</label>
                                <select wire:model="barangay_id" id="barangay_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select —</option>
                                    @foreach($barangays as $b)
                                        <option value="{{ $b['id'] }}">{{ $b['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="zip_code" class="block text-sm font-medium text-gray-700 mb-1.5">Zip Code</label>
                                <input wire:model="zip_code" id="zip_code" type="text" maxlength="10"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Tab 5: Clinical & Emergency --}}
                    <div x-show="activeTab === 5" x-transition.opacity style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="flex items-center space-x-3 mt-8 cursor-pointer">
                                    <input type="checkbox" wire:model="multiple_birth_indicator" class="form-checkbox h-5 w-5 text-light-blue-600 border-gray-300 rounded focus:ring-light-blue-500">
                                    <span class="text-sm font-medium text-gray-700">Multiple Birth Indicator (Twin, Triplet, etc.)</span>
                                </label>
                            </div>
                            <div>
                                <label for="primary_care_provider_id" class="block text-sm font-medium text-gray-700 mb-1.5">Primary Care Provider</label>
                                <select wire:model="primary_care_provider_id" id="primary_care_provider_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                    <option value="">— Select Provider —</option>
                                    @foreach($pcps as $p)
                                        <option value="{{ $p->id }}">Dr. {{ $p->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 border-b border-gray-100 pb-6">
                            <div>
                                <label class="flex items-center space-x-3 mt-8 cursor-pointer">
                                    <input type="checkbox" wire:model.live="is_deceased" class="form-checkbox h-5 w-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                    <span class="text-sm font-medium text-gray-700">Patient is Deceased</span>
                                </label>
                            </div>
                            <div>
                                <label for="date_of_death" class="block text-sm font-medium text-gray-700 mb-1.5 {{ !$is_deceased ? 'text-gray-400' : '' }}">Date of Death</label>
                                <input wire:model="date_of_death" id="date_of_death" type="date" {{ !$is_deceased ? 'disabled' : '' }}
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            </div>
                        </div>

                        <h5 class="text-sm font-semibold text-gray-700 mb-3">Emergency Contact</h5>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-1.5">Contact Name</label>
                                <input wire:model="emergency_contact_name" id="emergency_contact_name" type="text" maxlength="120"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                            <div>
                                <label for="emergency_contact_no" class="block text-sm font-medium text-gray-700 mb-1.5">Contact No</label>
                                <input wire:model="emergency_contact_no" id="emergency_contact_no" type="text" maxlength="30"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                            <div>
                                <label for="emergency_relationship" class="block text-sm font-medium text-gray-700 mb-1.5">Relationship</label>
                                <input wire:model="emergency_relationship" id="emergency_relationship" type="text" maxlength="60" placeholder="e.g. Spouse, Parent"
                                       class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer / Action Buttons --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center space-x-3">
                        <button type="button" wire:click="$toggle('is_active')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-light-blue-400 {{ $is_active ? 'bg-light-blue-500' : 'bg-gray-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                        <span class="text-sm text-gray-700 font-medium">{{ $is_active ? 'Active Patient' : 'Inactive Patient' }}</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button type="button" @click="if(activeTab > 1) activeTab--" x-show="activeTab > 1" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Previous</button>
                        <button type="button" @click="if(activeTab < 5) activeTab++" x-show="activeTab < 5" class="px-4 py-2 text-sm font-medium text-white bg-gray-800 hover:bg-gray-900 rounded-xl shadow-sm transition-colors">Next</button>
                        
                        <button type="submit" x-show="activeTab === 5" class="px-5 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors" style="display: none;">
                            <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Save Changes' : 'Register Patient' }}</span>
                            <span wire:loading wire:target="save" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving…</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Archive Patient</h3>
                <p class="text-sm text-gray-500 mb-6">This record will be archived. No data is permanently deleted.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Archive</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
