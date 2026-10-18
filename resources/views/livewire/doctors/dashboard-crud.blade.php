<div class="min-h-screen bg-gray-50/50 pb-12">
    {{-- Page Header --}}
    <div class="bg-white border-b border-gray-200 shadow-sm mb-8 px-6 sm:px-12 pt-8 pb-6">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Doctors Portal</h2>
                <p class="text-sm text-gray-500 mt-1">Manage your active patients and clinical workflows.</p>
            </div>
            
            {{-- Toolbar / Search --}}
            @if($doctorId || $isAdmin)
            <div class="relative w-full md:w-96 group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-light-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search"
                       type="text" placeholder="Search patients across departments..."
                       class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm leading-5 text-gray-900 placeholder-gray-400 transition-all focus:bg-white focus:border-light-blue-500 focus:ring-2 focus:ring-light-blue-500/20 shadow-sm"/>
            </div>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-4 relative z-20">
        @if(!$doctorId && !$isAdmin)
            <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-6 rounded-2xl shadow-xl flex items-start">
                <svg class="w-8 h-8 mr-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h3 class="text-lg font-bold">Unlinked Profile</h3>
                    <p class="font-medium mt-1">Your account is not currently linked to a Healthcare Professional profile. Please contact an administrator to complete your setup.</p>
                </div>
            </div>
        @else
            {{-- Tabs --}}
            <div class="flex justify-center mb-8">
                <div class="flex p-1.5 bg-white border border-gray-200 rounded-xl shadow-sm overflow-x-auto">
                    <button wire:click="setTab('opd')"
                            class="px-8 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 whitespace-nowrap outline-none
                                   {{ $activeTab === 'opd' ? 'bg-light-blue-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }}">
                        Outpatient (OPD)
                    </button>
                    <button wire:click="setTab('er')"
                            class="px-8 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 whitespace-nowrap outline-none
                                   {{ $activeTab === 'er' ? 'bg-rose-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }}">
                        Emergency (ER)
                    </button>
                    <button wire:click="setTab('ward')"
                            class="px-8 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200 whitespace-nowrap outline-none
                                   {{ $activeTab === 'ward' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-100' }}">
                        Ward Admissions
                    </button>
                </div>
            </div>

            {{-- Content Area --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                @if($activeTab === 'opd')
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border-collapse">
                            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Date</th>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Patient Details</th>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Status</th>
                                    <th class="px-6 py-3.5 text-center font-semibold text-gray-600 uppercase tracking-wider text-xs">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($opdPatients as $patient)
                                    <tr class="group hover:bg-light-blue-50/40 transition-all duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $patient->consultation_datetime ? \Carbon\Carbon::parse($patient->consultation_datetime)->format('M d, Y') : '' }}</div>
                                            <div class="text-xs text-gray-400 mt-1">{{ $patient->consultation_datetime ? \Carbon\Carbon::parse($patient->consultation_datetime)->format('h:i A') : '' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 text-base group-hover:text-light-blue-700 transition-colors">{{ $patient->patient->full_name ?? 'N/A' }}</div>
                                            <div class="text-xs font-mono text-gray-500 mt-1">MRN: {{ $patient->patient->patient_code ?? '--' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-light-blue-100 text-light-blue-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-light-blue-500 mr-1.5 animate-pulse"></span>
                                                {{ $patient->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <a href="{{ route('doctors.patient', ['type' => 'opd', 'id' => $patient->id]) }}" 
                                               class="inline-flex items-center justify-center px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition-all duration-200">
                                                Open Chart 
                                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-16 text-center">
                                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                            </div>
                                            <p class="text-gray-500 text-lg font-medium">No active OPD patients found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($opdPatients->hasPages())
                        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">{{ $opdPatients->links() }}</div>
                    @endif
                
                @elseif($activeTab === 'er')
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border-collapse">
                            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Admission</th>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Patient Details</th>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Acuity</th>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Status</th>
                                    <th class="px-6 py-3.5 text-center font-semibold text-gray-600 uppercase tracking-wider text-xs">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($erPatients as $patient)
                                    <tr class="group hover:bg-light-blue-50/40 transition-all duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $patient->admission_datetime ? \Carbon\Carbon::parse($patient->admission_datetime)->format('M d, Y') : '' }}</div>
                                            <div class="text-xs font-semibold text-rose-500 mt-1">{{ $patient->admission_datetime ? \Carbon\Carbon::parse($patient->admission_datetime)->format('h:i A') : '' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 text-base group-hover:text-light-blue-700 transition-colors">{{ $patient->patient->full_name ?? 'N/A' }}</div>
                                            <div class="text-xs font-mono text-gray-500 mt-1">MRN: {{ $patient->patient->patient_code ?? '--' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php $acuity = $patient->triageRecord->acuityLevel->name ?? 'None'; @endphp
                                            <span class="inline-flex items-center font-semibold text-gray-700">
                                                @if(str_contains(strtolower($acuity), 'resuscitation') || str_contains(strtolower($acuity), 'emergency'))
                                                    <svg class="w-4 h-4 text-red-500 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                @endif
                                                {{ $acuity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5 animate-pulse"></span>
                                                {{ $patient->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <a href="{{ route('doctors.patient', ['type' => 'er', 'id' => $patient->id]) }}" 
                                               class="inline-flex items-center justify-center px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition-all duration-200">
                                                Open Chart 
                                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                            </div>
                                            <p class="text-gray-500 text-lg font-medium">No active ER patients found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($erPatients->hasPages())
                        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">{{ $erPatients->links() }}</div>
                    @endif

                @elseif($activeTab === 'ward')
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border-collapse">
                            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Admission</th>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Patient Details</th>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Room/Bed</th>
                                    <th class="px-6 py-3.5 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Status</th>
                                    <th class="px-6 py-3.5 text-center font-semibold text-gray-600 uppercase tracking-wider text-xs">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($wardPatients as $patient)
                                    <tr class="group hover:bg-light-blue-50/40 transition-all duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $patient->admission_date ? \Carbon\Carbon::parse($patient->admission_date)->format('M d, Y') : '' }}</div>
                                            <div class="text-xs text-gray-400 mt-1">{{ $patient->admission_date ? \Carbon\Carbon::parse($patient->admission_date)->format('h:i A') : '' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900 text-base group-hover:text-light-blue-700 transition-colors">{{ $patient->patient->full_name ?? 'N/A' }}</div>
                                            <div class="text-xs font-mono text-gray-500 mt-1">MRN: {{ $patient->patient->patient_code ?? '--' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center text-gray-700 font-medium">
                                                <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                {{ $patient->wardRoom->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                                {{ $patient->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <a href="{{ route('doctors.patient', ['type' => 'ward', 'id' => $patient->id]) }}" 
                                               class="inline-flex items-center justify-center px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white text-sm font-medium rounded-xl shadow-sm transition-all duration-200">
                                                Open Chart 
                                                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-16 text-center">
                                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                            </div>
                                            <p class="text-gray-500 text-lg font-medium">No active Ward admissions found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($wardPatients->hasPages())
                        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50">{{ $wardPatients->links() }}</div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</div>
