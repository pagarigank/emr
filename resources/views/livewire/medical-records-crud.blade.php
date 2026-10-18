<div class="max-w-7xl mx-auto">

    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Medical Records</h1>
                <p class="text-sm text-gray-500 mt-1">View complete patient encounter history and generate certificates</p>
            </div>
        </div>
    </div>

    @if (!$selectedPatient)
        <!-- Patient Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="max-w-xl">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search Patient</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="patient_search" type="text"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 text-sm"
                        placeholder="Search by patient name or code (min 2 characters)">

                    @if (strlen($patient_search) >= 2 && count($patients) > 0)
                        <div class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-72 overflow-y-auto">
                            @foreach ($patients as $p)
                                <button wire:click="selectPatient({{ $p->id }})" type="button"
                                    class="w-full text-left px-4 py-3 hover:bg-violet-50 border-b border-gray-100 last:border-b-0 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ $p->last_name }}, {{ $p->first_name }}</span>
                                            <span class="text-xs text-gray-500 ml-2">{{ $p->patient_code }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2 text-xs">
                                            @if ($p->is_philhealth_member)
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium">PH</span>
                                            @endif
                                            <span class="text-gray-500">{{ $p->date_of_birth ? Carbon\Carbon::parse($p->date_of_birth)->age . ' y/o' : '' }}</span>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($selectedPatient)
        <!-- Patient Summary Card -->
        <div class="bg-gradient-to-r from-violet-600 to-indigo-700 rounded-xl shadow-sm mb-6 overflow-hidden">
            <div class="p-6 text-white">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold">
                            {{ strtoupper(substr($selectedPatient->first_name, 0, 1)) }}{{ strtoupper(substr($selectedPatient->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">{{ $selectedPatient->last_name }}, {{ $selectedPatient->first_name }} {{ $selectedPatient->middle_name ?? '' }}</h2>
                            <div class="flex items-center space-x-3 mt-1 text-sm text-white/80">
                                <span>{{ $selectedPatient->patient_code }}</span>
                                <span>•</span>
                                <span>{{ $selectedPatient->date_of_birth ? Carbon\Carbon::parse($selectedPatient->date_of_birth)->format('M d, Y') : '—' }}</span>
                                <span>•</span>
                                <span>{{ $selectedPatient->date_of_birth ? Carbon\Carbon::parse($selectedPatient->date_of_birth)->age . ' years old' : '—' }}</span>
                                <span>•</span>
                                <span>{{ $selectedPatient->gender?->name ?? '—' }}</span>
                            </div>
                            <div class="flex items-center space-x-2 mt-2">
                                @if ($selectedPatient->is_philhealth_member)
                                    <span class="px-2.5 py-0.5 bg-white/20 text-white text-xs rounded-full font-medium">PhilHealth Member</span>
                                @else
                                    <span class="px-2.5 py-0.5 bg-white/10 text-white/70 text-xs rounded-full">Non-PhilHealth</span>
                                @endif
                                @if ($selectedPatient->philhealth_pin)
                                    <span class="text-xs text-white/70">PIN: {{ $selectedPatient->philhealth_pin }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <button wire:click="clearPatient" class="px-3 py-1.5 bg-white/10 hover:bg-white/20 rounded-lg text-sm text-white transition-colors">
                        Change Patient
                    </button>
                </div>
            </div>
            <!-- Stats Bar -->
            <div class="bg-black/10 px-6 py-3 flex items-center space-x-8 text-sm text-white/80">
                <span><strong class="text-white">{{ count($encounters ?? []) }}</strong> Total Encounters</span>
                <span><strong class="text-white">{{ collect($encounters ?? [])->where('type', 'OPD')->count() }}</strong> OPD</span>
                <span><strong class="text-white">{{ collect($encounters ?? [])->whereIn('type', ['ER Triage', 'ER Treatment'])->count() }}</strong> ER</span>
                <span><strong class="text-white">{{ collect($encounters ?? [])->where('type', 'Admission')->count() }}</strong> Admissions</span>
                <span><strong class="text-white">{{ collect($encounters ?? [])->where('type', 'OB')->count() }}</strong> OB</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Timeline Filters -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sticky top-24">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Filter</h3>
                    <div class="space-y-1">
                        @foreach ([
                            ['key' => 'all', 'label' => 'All Encounters', 'color' => 'gray'],
                            ['key' => 'OPD', 'label' => 'OPD', 'color' => 'emerald'],
                            ['key' => 'ER Triage', 'label' => 'ER Triage', 'color' => 'red'],
                            ['key' => 'ER Treatment', 'label' => 'ER Treatment', 'color' => 'rose'],
                            ['key' => 'Admission', 'label' => 'Admission', 'color' => 'indigo'],
                            ['key' => 'OB', 'label' => 'OB', 'color' => 'purple'],
                        ] as $f)
                            <button wire:click="filterTimeline('{{ $f['key'] }}')"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm transition-colors
                                    {{ $activeTimelineFilter === $f['key'] ? "bg-{$f['color']}-50 text-{$f['color']}-700 font-medium" : 'text-gray-600 hover:bg-gray-50' }}">
                                <div class="flex items-center">
                                    <span class="w-2 h-2 rounded-full bg-{{ $f['color'] }}-500 mr-2"></span>
                                    {{ $f['label'] }}
                                </div>
                            </button>
                        @endforeach
                    </div>

                    @if ($encounters && count($encounters) > 0)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Timeline</h4>
                            <div class="space-y-0 max-h-96 overflow-y-auto">
                                @foreach ($this->filteredEncounters as $idx => $enc)
                                    <button wire:click="viewEncounterDetail('{{ $enc['type'] }}', {{ $enc['id'] }})"
                                        class="w-full text-left px-2 py-1.5 text-xs rounded transition-colors
                                            {{ $selectedEncounterId === $enc['id'] && $selectedEncounterType === $enc['type'] ? 'bg-violet-50 text-violet-700' : 'text-gray-500 hover:bg-gray-50' }}">
                                        <div class="flex items-center">
                                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $enc['color'] }}-400 mr-2 flex-shrink-0"></span>
                                            <span class="truncate">{{ $enc['code'] }}</span>
                                            <span class="ml-auto text-xs text-gray-400 flex-shrink-0">{{ $enc['date'] }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-4">
                @if ($encounters && count($encounters) > 0)
                    @foreach ($this->filteredEncounters as $enc)
                        <button wire:click="viewEncounterDetail('{{ $enc['type'] }}', {{ $enc['id'] }})"
                            class="w-full text-left bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:border-{{ $enc['color'] }}-300 hover:shadow-md transition-all group">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="w-10 h-10 rounded-lg bg-{{ $enc['color'] }}-100 flex items-center justify-center text-{{ $enc['color'] }}-600 font-bold text-sm flex-shrink-0">
                                        {{ substr($enc['type'], 0, 2) }}
                                    </span>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-semibold text-gray-900">{{ $enc['code'] }}</span>
                                            <span class="px-2 py-0.5 bg-{{ $enc['color'] }}-100 text-{{ $enc['color'] }}-700 text-xs rounded-full font-medium">{{ $enc['type'] }}</span>
                                            @if ($enc['status'] === 'Completed' || $enc['status'] === 'Discharged')
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">{{ $enc['status'] }}</span>
                                            @elseif ($enc['status'] === 'Admitted')
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">{{ $enc['status'] }}</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full">{{ $enc['status'] }}</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 mt-1">{{ $enc['date'] }} • {{ $enc['doctor'] }}</p>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-300 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                            @if ($enc['diagnosis'])
                                <div class="mt-3 pl-13">
                                    <p class="text-sm text-gray-600 line-clamp-1">{{ $enc['diagnosis'] }}</p>
                                </div>
                            @endif
                        </button>
                    @endforeach
                @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Encounters Found</h3>
                        <p class="text-gray-500 text-sm">This patient has no recorded clinical encounters in the system.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Encounter Detail Modal -->
    @if ($showDetailModal && $detailData)
        <div class="fixed inset-0 z-50 flex items-start justify-center pt-10 pb-10 overflow-y-auto"
            x-data x-init="$el.addEventListener('click', function(e) { if(e.target === $el) { $wire.closeDetailModal() } })">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 z-10 max-h-[90vh] flex flex-col">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
                    <div class="flex items-center space-x-3">
                        <span class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center text-violet-600 font-bold text-sm">
                            {{ substr($detailData['title'], 0, 2) }}
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $detailData['title'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $detailData['code'] }} • {{ $detailData['date'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false"
                                class="flex items-center px-3 py-2 bg-violet-50 text-violet-700 hover:bg-violet-100 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Certificates
                                <svg class="w-3 h-3 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-transition
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                                <a href="/medical-records/certificate/medical/{{ $detailData['print_type'] }}/{{ $selectedEncounterId }}/{{ $selectedPatientId }}"
                                    target="_blank" @click="open = false"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-violet-50 hover:text-violet-700 flex items-center transition-colors">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Medical Certificate
                                </a>
                                @if ($detailData['print_type'] === 'admission')
                                <a href="/medical-records/certificate/admission/{{ $detailData['print_type'] }}/{{ $selectedEncounterId }}/{{ $selectedPatientId }}"
                                    target="_blank" @click="open = false"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-violet-50 hover:text-violet-700 flex items-center transition-colors">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    Certificate of Admission
                                </a>
                                <a href="/medical-records/certificate/discharge/{{ $detailData['print_type'] }}/{{ $selectedEncounterId }}/{{ $selectedPatientId }}"
                                    target="_blank" @click="open = false"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-violet-50 hover:text-violet-700 flex items-center transition-colors">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Discharge Summary
                                </a>
                                @endif
                                @if (in_array($detailData['print_type'], ['er', 'er_triage']))
                                <a href="/medical-records/certificate/treatment/{{ $detailData['print_type'] }}/{{ $selectedEncounterId }}/{{ $selectedPatientId }}"
                                    target="_blank" @click="open = false"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-violet-50 hover:text-violet-700 flex items-center transition-colors">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Certificate of Treatment
                                </a>
                                @endif
                                <a href="/medical-records/certificate/abstract/{{ $detailData['print_type'] }}/{{ $selectedEncounterId }}/{{ $selectedPatientId }}"
                                    target="_blank" @click="open = false"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-violet-50 hover:text-violet-700 flex items-center transition-colors">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Clinical Abstract
                                </a>
                            </div>
                        </div>
                        <button wire:click="closeDetailModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 overflow-y-auto flex-1 space-y-6">

                    <!-- Detail Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($detailData['fields'] as $label => $value)
                            <div class="{{ strlen($value) > 100 ? 'md:col-span-2' : '' }}">
                                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $label }}</span>
                                <p class="mt-0.5 text-sm text-gray-900 {{ strlen($value) > 150 ? 'line-clamp-3' : '' }}">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Clinical Notes (ER Treatment) -->
                    @if (isset($detailData['clinical_notes']) && $detailData['clinical_notes'])
                        <div class="border-t border-gray-100 pt-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Clinical Notes</h4>
                            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $detailData['clinical_notes'] }}</div>
                        </div>
                    @endif

                    <!-- Lab Orders -->
                    @if (isset($detailData['labs']) && count($detailData['labs']) > 0)
                        <div class="border-t border-gray-100 pt-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                Laboratory Orders ({{ count($detailData['labs']) }})
                            </h4>
                            <div class="space-y-2">
                                @foreach ($detailData['labs'] as $lab)
                                    <div class="bg-gray-50 rounded-lg p-3 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-900">{{ $lab->request_code }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $lab->status === 'Completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $lab->status }}
                                            </span>
                                        </div>
                                        @if ($lab->items && count($lab->items) > 0)
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach ($lab->items as $item)
                                                    <span class="px-2 py-0.5 bg-white rounded text-xs text-gray-600">{{ $item->labTest?->test_name ?? 'Test #'.$item->id }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Pharmacy Orders -->
                    @if (isset($detailData['pharmacy']) && count($detailData['pharmacy']) > 0)
                        <div class="border-t border-gray-100 pt-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                Pharmacy Orders ({{ count($detailData['pharmacy']) }})
                            </h4>
                            <div class="space-y-2">
                                @foreach ($detailData['pharmacy'] as $rx)
                                    <div class="bg-gray-50 rounded-lg p-3 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-900">{{ $rx->request_code }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $rx->status === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $rx->status }}
                                            </span>
                                        </div>
                                        @if ($rx->items && count($rx->items) > 0)
                                            <div class="mt-1 text-xs text-gray-500">
                                                @foreach ($rx->items as $item)
                                                    <span>{{ $item->drug?->drug_name ?? 'Drug #'.$item->id }}{{ !$loop->last ? ', ' : '' }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Radiology Orders -->
                    @if (isset($detailData['radiology']) && count($detailData['radiology']) > 0)
                        <div class="border-t border-gray-100 pt-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m4-4H8"/></svg>
                                Radiology Orders ({{ count($detailData['radiology']) }})
                            </h4>
                            <div class="space-y-2">
                                @foreach ($detailData['radiology'] as $rad)
                                    <div class="bg-gray-50 rounded-lg p-3 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-900">{{ $rad->request_code }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $rad->status === 'Complete' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $rad->status }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Billing Invoices -->
                    @if (isset($detailData['invoices']) && count($detailData['invoices']) > 0)
                        <div class="border-t border-gray-100 pt-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 10a1 1 0 100 2 1 1 0 000-2zm4 0a1 1 0 100 2 1 1 0 000-2z"/></svg>
                                Billing Invoices ({{ count($detailData['invoices']) }})
                            </h4>
                            <div class="space-y-2">
                                @foreach ($detailData['invoices'] as $inv)
                                    <a href="/billing/invoice/{{ $inv->id }}/print" target="_blank"
                                        class="block bg-gray-50 rounded-lg p-3 text-sm hover:bg-violet-50 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-900">{{ $inv->invoice_code }}</span>
                                            <div class="flex items-center space-x-3 text-xs">
                                                <span class="text-gray-500">₱{{ number_format($inv->total_amount, 2) }}</span>
                                                <span class="px-2 py-0.5 rounded-full font-medium
                                                    {{ $inv->status === 'Paid' ? 'bg-green-100 text-green-700' : ($inv->status === 'Partial' ? 'bg-yellow-100 text-yellow-700' : ($inv->status === 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                                                    {{ $inv->status }}
                                                </span>
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between flex-shrink-0">
                    <span class="text-xs text-gray-400">Encounter #{{ $detailData['code'] }}</span>
                    <button wire:click="closeDetailModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition-colors">Close</button>
                </div>
            </div>
        </div>

        <script>
            function openPrintTab(type, encounterType, encounterId, patientId) {
                const url = '/medical-records/certificate/' + type + '/' + encounterType + '/' + encounterId + '/' + patientId;
                window.open(url, '_blank');
            }
        </script>
    @endif

    <!-- Empty State (no search yet) -->
    @if (!$selectedPatient && strlen($patient_search) === 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-16 text-center">
            <svg class="w-20 h-20 mx-auto text-gray-200 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Patient Medical Records</h3>
            <p class="text-gray-500 max-w-md mx-auto">Search for a patient above to view their complete medical history, including OPD visits, ER encounters, admissions, and more.</p>
            <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl mx-auto">
                @foreach([
                    ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'OPD Visits', 'color' => 'emerald'],
                    ['icon' => 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'ER Encounters', 'color' => 'red'],
                    ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Admissions', 'color' => 'indigo'],
                    ['icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'label' => 'OB Records', 'color' => 'purple'],
                ] as $feat)
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <svg class="w-8 h-8 mx-auto text-{{ $feat['color'] }}-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $feat['icon'] }}"/></svg>
                        <span class="text-xs font-medium text-gray-600">{{ $feat['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
