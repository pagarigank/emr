<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">PhilHealth Konsulta</h2>
            <p class="text-sm text-gray-500 mt-1">Primary care package — patient enrollment, consultations, GAMOT prescriptions, and lab referrals.</p>
        </div>
    </div>

    {{-- Toast via dispatch --}}
    @if (session()->has('message'))
        <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('message') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-teal-600 uppercase tracking-wider">Enrolled</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_enrolled'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">FPE Done</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['fpe_completed'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Consultations</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_consults'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Prescriptions</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_rx'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-green-600 uppercase tracking-wider">GAMOT</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['gamot_rx'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <p class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Pending Labs</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['pending_labs'] }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-5 border-b border-gray-200">
        <button wire:click="switchTab(1)" class="px-5 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab === 1 ? 'text-teal-700 border-teal-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">Enrollments</button>
        <button wire:click="switchTab(2)" class="px-5 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab === 2 ? 'text-teal-700 border-teal-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">Consultations</button>
        <button wire:click="switchTab(3)" class="px-5 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab === 3 ? 'text-teal-700 border-teal-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">Prescriptions</button>
        <button wire:click="switchTab(4)" class="px-5 py-3 text-sm font-medium transition-colors border-b-2 {{ $activeTab === 4 ? 'text-teal-700 border-teal-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">Lab Referrals</button>
    </div>

    {{-- Search + CTA --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..." class="block w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl leading-5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 sm:text-sm shadow-sm"/>
        </div>
        @if($activeTab === 1)
            <button wire:click="openCreateEnrollment" class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>New Enrollment
            </button>
        @elseif($activeTab === 2)
            <button wire:click="openCreateConsultation" class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>New Consultation
            </button>
        @endif
    </div>

    {{-- ============================== TAB 1: ENROLLMENTS ============================== --}}
    @if($activeTab === 1)
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer" wire:click="sortBy('enrollment_code')">Code</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer" wire:click="sortBy('enrollment_date')">Patient</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Enrolled</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Provider</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Risk</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">FPE</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($enrollments as $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $item->enrollment_code }}</td>
                    <td class="px-5 py-4 text-gray-800 font-medium">{{ $item->patient?->last_name }}, {{ $item->patient?->first_name }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $item->enrollment_date?->format('Y-m-d') }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $item->primaryCareProvider?->full_name ?? '—' }}</td>
                    <td class="px-5 py-4 text-center">
                        @php $rc = match($item->risk_classification) { 'High' => 'bg-red-50 text-red-700', 'Moderate' => 'bg-amber-50 text-amber-700', 'Low' => 'bg-green-50 text-green-700', default => 'bg-gray-50 text-gray-500' }; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rc }}">{{ $item->risk_classification ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        @if($item->fpe_completed)
                            <span class="text-green-600 font-medium text-xs">Done</span>
                        @else
                            <span class="text-amber-600 font-medium text-xs">Pending</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        @php $sc = match($item->status) { 'Active' => 'bg-emerald-50 text-emerald-700', 'Transferred' => 'bg-blue-50 text-blue-700', 'Expired' => 'bg-gray-50 text-gray-600', 'Cancelled' => 'bg-red-50 text-red-700', default => 'bg-gray-50 text-gray-700' }; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sc }}">{{ $item->status }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <button wire:click="openCreateConsultation({{ $item->id }})" class="p-1.5 text-indigo-500 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg transition-colors" title="New Consultation">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </button>
                            <button wire:click="editEnrollment({{ $item->id }})" class="p-1.5 text-amber-500 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="archive({{ $item->id }}, 'enrollment')" wire:confirm="Archive this enrollment?" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Archive">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">No enrollments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $enrollments->links(data: ['scrollTo' => false]) }}</div>
    @endif

    {{-- ============================== TAB 2: CONSULTATIONS ============================== --}}
    @if($activeTab === 2)
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Code</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Patient</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Provider</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Complaint</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">FPE</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($consultations as $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $item->consultation_code }}</td>
                    <td class="px-5 py-4 text-gray-800 font-medium">{{ $item->patient?->last_name }}, {{ $item->patient?->first_name }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $item->consultation_date?->format('Y-m-d h:i A') }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $item->provider?->full_name ?? '—' }}</td>
                    <td class="px-5 py-4 text-gray-600 max-w-[200px] truncate">{{ $item->chief_complaint ?? '—' }}</td>
                    <td class="px-5 py-4 text-center">
                        @if($item->is_fpe)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700">FPE</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <button wire:click="viewConsultation({{ $item->id }})" class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            <button wire:click="archive({{ $item->id }}, 'consultation')" wire:confirm="Archive this consultation?" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Archive">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No consultations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $consultations->links(data: ['scrollTo' => false]) }}</div>
    @endif

    {{-- ============================== TAB 3: PRESCRIPTIONS ============================== --}}
    @if($activeTab === 3)
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Medicine</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Patient</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Dosage</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Frequency</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Qty</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">GAMOT</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($prescriptions as $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 font-medium text-gray-900">{{ $item->medicine_name }}</td>
                    <td class="px-5 py-4 text-gray-700">{{ $item->patient?->last_name }}, {{ $item->patient?->first_name }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $item->dosage ?? '—' }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $item->frequency ?? '—' }}</td>
                    <td class="px-5 py-4 text-center text-gray-700">{{ $item->quantity }}</td>
                    <td class="px-5 py-4 text-center">
                        @if($item->is_gamot_covered)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700">Yes</span>
                        @else
                            <span class="text-gray-400 text-xs">No</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-center">
                        @php $rsc = match($item->status) { 'Prescribed' => 'bg-blue-50 text-blue-700', 'Dispensed' => 'bg-emerald-50 text-emerald-700', 'Cancelled' => 'bg-red-50 text-red-700', default => 'bg-gray-50 text-gray-700' }; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rsc }}">{{ $item->status }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <button wire:click="archive({{ $item->id }}, 'prescription')" wire:confirm="Archive this prescription?" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Archive">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">No prescriptions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $prescriptions->links(data: ['scrollTo' => false]) }}</div>
    @endif

    {{-- ============================== TAB 4: LAB REFERRALS ============================== --}}
    @if($activeTab === 4)
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Test Type</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Patient</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Referred</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Performed</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Result</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($labReferrals as $item)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 font-medium text-gray-900">{{ $item->test_type }}</td>
                    <td class="px-5 py-4 text-gray-700">{{ $item->patient?->last_name }}, {{ $item->patient?->first_name }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $item->referral_date?->format('Y-m-d') }}</td>
                    <td class="px-5 py-4 text-gray-600">{{ $item->performed_date?->format('Y-m-d') ?? '—' }}</td>
                    <td class="px-5 py-4 text-gray-600 max-w-[200px] truncate">{{ $item->result ?? '—' }}</td>
                    <td class="px-5 py-4 text-center">
                        @php $lsc = match($item->status) { 'Pending' => 'bg-amber-50 text-amber-700', 'Completed' => 'bg-emerald-50 text-emerald-700', 'Cancelled' => 'bg-red-50 text-red-700', default => 'bg-gray-50 text-gray-700' }; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $lsc }}">{{ $item->status }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <button wire:click="archive({{ $item->id }}, 'lab')" wire:confirm="Archive this lab referral?" class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Archive">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">No lab referrals found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $labReferrals->links(data: ['scrollTo' => false]) }}</div>
    @endif

    {{-- ============================== ENROLLMENT MODAL ============================== --}}
    @if($showEnrollmentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click.self="$set('showEnrollmentModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-teal-50/90 sticky top-0 z-10">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isEditing ? 'Edit' : 'New' }} Konsulta Enrollment</h3>
                <button wire:click="$set('showEnrollmentModal', false)" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient</label>
                    <input wire:model.live.debounce.300ms="patient_search" type="text" placeholder="Search patient..." class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    @if($patient_search && !$patient_id)
                        <div class="mt-1 border border-gray-200 rounded-xl overflow-hidden shadow-sm max-h-48 overflow-y-auto">
                            @php $patients = \App\Models\Patient::where('history', 1)->where(function($q) use($patient_search) { $q->where('last_name','like',"%{$patient_search}%")->orWhere('first_name','like',"%{$patient_search}%")->orWhere('patient_code','like',"%{$patient_search}%"); })->limit(10)->get(); @endphp
                            @foreach($patients as $p)
                                <button wire:click="selectPatient({{ $p->id }})" type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-teal-50 transition-colors">{{ $p->last_name }}, {{ $p->first_name }} ({{ $p->patient_code ?? '—' }})</button>
                            @endforeach
                        </div>
                    @endif
                    @error('patient_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Enrollment Date</label>
                        <input wire:model="enrollment_date" type="date" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input wire:model="expiry_date" type="date" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Primary Care Provider</label>
                        <select wire:model="primary_care_provider_id" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="">— Select —</option>
                            @foreach($providers as $p)
                                <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Risk Classification</label>
                        <select wire:model="risk_classification" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="">— Select —</option>
                            <option value="Low">Low</option>
                            <option value="Moderate">Moderate</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model="status" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="Active">Active</option>
                        <option value="Transferred">Transferred</option>
                        <option value="Expired">Expired</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button wire:click="$set('showEnrollmentModal', false)" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button wire:click="saveEnrollment" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">{{ $isEditing ? 'Update' : 'Enroll' }}</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================== CONSULTATION MODAL ============================== --}}
    @if($showConsultationModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click.self="$set('showConsultationModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-indigo-50/90 sticky top-0 z-10">
                <h3 class="text-lg font-semibold text-gray-900">New Konsulta Consultation</h3>
                <button wire:click="$set('showConsultationModal', false)" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Consultation Date</label>
                        <input wire:model="consultDate" type="datetime-local" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                        <select wire:model="consultProviderId" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="">— Select —</option>
                            @foreach($providers as $p)
                                <option value="{{ $p->id }}">{{ $p->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient</label>
                    <input wire:model.live.debounce.300ms="consultPatientSearch" type="text" placeholder="Search patient..." class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    @if($consultPatientSearch && !$consultPatientId)
                        <div class="mt-1 border border-gray-200 rounded-xl overflow-hidden shadow-sm max-h-48 overflow-y-auto">
                            @php $consultPatients = \App\Models\Patient::where('history', 1)->where(function($q) use($consultPatientSearch) { $q->where('last_name','like',"%{$consultPatientSearch}%")->orWhere('first_name','like',"%{$consultPatientSearch}%")->orWhere('patient_code','like',"%{$consultPatientSearch}%"); })->limit(10)->get(); @endphp
                            @foreach($consultPatients as $p)
                                <button wire:click="selectConsultPatient({{ $p->id }})" type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-indigo-50 transition-colors">{{ $p->last_name }}, {{ $p->first_name }} ({{ $p->patient_code ?? '—' }})</button>
                            @endforeach
                        </div>
                    @endif
                    @error('consultPatientId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chief Complaint</label>
                    <textarea wire:model="chiefComplaint" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">BP</label>
                        <input wire:model="bp" type="text" placeholder="120/80" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Temp (°C)</label>
                        <input wire:model="temperature" type="number" step="0.1" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">HR</label>
                        <input wire:model="heartRate" type="number" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RR</label>
                        <input wire:model="respiratoryRate" type="number" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input wire:model="weight" type="number" step="0.1" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                        <input wire:model="height" type="number" step="0.1" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Diagnosis</label>
                    <input wire:model="diagnosis" type="text" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ICD-10 Code</label>
                    <select wire:model="consultIcd10Id" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="">— Select —</option>
                        @foreach($icd10Codes as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->description }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assessment</label>
                    <textarea wire:model="assessment" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Management Plan</label>
                    <textarea wire:model="managementPlan" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="consultNotes" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input wire:model="isFpe" type="checkbox" id="isFpe" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="isFpe" class="text-sm text-gray-700">This is the First Physical Exam (FPE)</label>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button wire:click="$set('showConsultationModal', false)" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button wire:click="saveConsultation" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">Save Consultation</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================== CONSULTATION VIEW MODAL ============================== --}}
    @if($showConsultationView && $viewingConsultation)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click="closeViewConsultation"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-indigo-50/90 sticky top-0 z-10">
                <h3 class="text-lg font-semibold text-gray-900">Consultation {{ $viewingConsultation->consultation_code }}</h3>
                <button wire:click="closeViewConsultation" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-5">
                {{-- Patient Info --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div><p class="text-xs text-gray-500 uppercase">Patient</p><p class="text-sm font-medium">{{ $viewingConsultation->patient?->last_name }}, {{ $viewingConsultation->patient?->first_name }}</p></div>
                    <div><p class="text-xs text-gray-500 uppercase">Date</p><p class="text-sm">{{ $viewingConsultation->consultation_date?->format('Y-m-d h:i A') }}</p></div>
                    <div><p class="text-xs text-gray-500 uppercase">Provider</p><p class="text-sm">{{ $viewingConsultation->provider?->full_name ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-500 uppercase">FPE</p><p class="text-sm">{{ $viewingConsultation->is_fpe ? 'Yes' : 'No' }}</p></div>
                </div>

                {{-- Vitals --}}
                @if($viewingConsultation->bp || $viewingConsultation->temperature)
                <div class="bg-gray-50 rounded-xl p-4">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Vital Signs</h4>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 text-center">
                        @if($viewingConsultation->bp)<div><p class="text-xs text-gray-400">BP</p><p class="text-sm font-medium">{{ $viewingConsultation->bp }}</p></div>@endif
                        @if($viewingConsultation->temperature)<div><p class="text-xs text-gray-400">Temp</p><p class="text-sm font-medium">{{ $viewingConsultation->temperature }}°C</p></div>@endif
                        @if($viewingConsultation->heart_rate)<div><p class="text-xs text-gray-400">HR</p><p class="text-sm font-medium">{{ $viewingConsultation->heart_rate }}</p></div>@endif
                        @if($viewingConsultation->respiratory_rate)<div><p class="text-xs text-gray-400">RR</p><p class="text-sm font-medium">{{ $viewingConsultation->respiratory_rate }}</p></div>@endif
                        @if($viewingConsultation->weight)<div><p class="text-xs text-gray-400">Weight</p><p class="text-sm font-medium">{{ $viewingConsultation->weight }} kg</p></div>@endif
                        @if($viewingConsultation->height)<div><p class="text-xs text-gray-400">Height</p><p class="text-sm font-medium">{{ $viewingConsultation->height }} cm</p></div>@endif
                    </div>
                </div>
                @endif

                {{-- Clinical Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><p class="text-xs text-gray-500 uppercase mb-1">Chief Complaint</p><p class="text-sm">{{ $viewingConsultation->chief_complaint ?? '—' }}</p></div>
                    <div><p class="text-xs text-gray-500 uppercase mb-1">Diagnosis</p><p class="text-sm">{{ $viewingConsultation->diagnosis ?? '—' }} @if($viewingConsultation->icd10Code)<span class="text-xs text-gray-400 ml-1">({{ $viewingConsultation->icd10Code->code }})</span>@endif</p></div>
                </div>
                <div><p class="text-xs text-gray-500 uppercase mb-1">Assessment</p><p class="text-sm whitespace-pre-wrap">{{ $viewingConsultation->assessment ?? '—' }}</p></div>
                <div><p class="text-xs text-gray-500 uppercase mb-1">Management Plan</p><p class="text-sm whitespace-pre-wrap">{{ $viewingConsultation->management_plan ?? '—' }}</p></div>

                {{-- Prescriptions --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Prescriptions</h4>
                        <button wire:click="openCreateRx({{ $viewingConsultation->id }}, {{ $viewingConsultation->enrollment_id ?? 'null' }}, {{ $viewingConsultation->patient_id }})" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">+ Add Rx</button>
                    </div>
                    @forelse($viewingConsultation->prescriptions as $rx)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            <div><span class="text-sm font-medium">{{ $rx->medicine_name }}</span> @if($rx->is_gamot_covered)<span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-50 text-green-700 ml-1">GAMOT</span>@endif</div>
                            <div class="text-xs text-gray-500">{{ $rx->dosage ?? '' }} {{ $rx->frequency ?? '' }} {{ $rx->duration ?? '' }} ({{ $rx->quantity }})</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No prescriptions.</p>
                    @endforelse
                </div>

                {{-- Lab Referrals --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Lab Referrals</h4>
                        <button wire:click="openCreateLab({{ $viewingConsultation->id }}, {{ $viewingConsultation->enrollment_id ?? 'null' }}, {{ $viewingConsultation->patient_id }})" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">+ Refer Lab</button>
                    </div>
                    @forelse($viewingConsultation->labReferrals as $lab)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                            <div><span class="text-sm font-medium">{{ $lab->test_type }}</span></div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">{{ $lab->referral_date?->format('Y-m-d') }}</span>
                                @php $lbsc = match($lab->status) { 'Pending' => 'bg-amber-50 text-amber-700', 'Completed' => 'bg-emerald-50 text-emerald-700', default => 'bg-gray-50 text-gray-700' }; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $lbsc }}">{{ $lab->status }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No lab referrals.</p>
                    @endforelse
                </div>
            </div>
            <div class="flex justify-end px-6 py-4 border-t border-gray-100">
                <button wire:click="closeViewConsultation" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Close</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================== PRESCRIPTION MODAL ============================== --}}
    @if($showRxModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click.self="$set('showRxModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-amber-50/90 sticky top-0 z-10">
                <h3 class="text-lg font-semibold text-gray-900">New Prescription</h3>
                <button wire:click="$set('showRxModal', false)" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Medicine Name</label>
                    <input wire:model="medicineName" type="text" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    @error('medicineName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosage</label>
                        <input wire:model="dosage" type="text" placeholder="500mg" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                        <input wire:model="frequency" type="text" placeholder="1 tab BID" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                        <input wire:model="duration" type="text" placeholder="7 days" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input wire:model="rxQuantity" type="number" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                        @error('rxQuantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="rxStatus" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            <option value="Prescribed">Prescribed</option>
                            <option value="Dispensed">Dispensed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input wire:model="isGamotCovered" type="checkbox" id="isGamotCovered" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                    <label for="isGamotCovered" class="text-sm text-gray-700">Covered under GAMOT benefit</label>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button wire:click="$set('showRxModal', false)" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button wire:click="saveRx" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">Save Prescription</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================== LAB REFERRAL MODAL ============================== --}}
    @if($showLabModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click.self="$set('showLabModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-sky-50/90 sticky top-0 z-10">
                <h3 class="text-lg font-semibold text-gray-900">New Lab Referral</h3>
                <button wire:click="$set('showLabModal', false)" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Test Type</label>
                    <input wire:model="testType" type="text" placeholder="e.g. CBC, Urinalysis, FBS" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                    @error('testType') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Referral Date</label>
                    <input wire:model="labReferralDate" type="date" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model="labStatus" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="labNotes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button wire:click="$set('showLabModal', false)" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button wire:click="saveLab" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors">Submit Referral</button>
            </div>
        </div>
    </div>
    @endif
</div>
