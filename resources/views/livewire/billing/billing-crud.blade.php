<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Billing &amp; Invoices</h2>
            <p class="text-sm text-gray-500 mt-1">Manage patient invoices, service charges, and payments.</p>
        </div>
        @if($activeTab === 2)
        <button wire:click="openCreateInvoice"
                class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Generate Invoice
        </button>
        @endif
    </div>

    {{-- Summary Cards (Premium Gradients) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl shadow-sm p-4 bg-gradient-to-br from-amber-500 via-amber-600 to-orange-700 text-white">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-white/80 uppercase tracking-wider">Unbilled Services</p>
                    <p class="text-xl font-bold text-white">₱{{ number_format($totalUnbilled, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl shadow-sm p-4 bg-gradient-to-br from-rose-500 via-rose-600 to-red-700 text-white">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-white/80 uppercase tracking-wider">Outstanding Balance</p>
                    <p class="text-xl font-bold text-white">₱{{ number_format($totalOutstanding, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl shadow-sm p-4 bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-700 text-white">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-white/80 uppercase tracking-wider">Total Collected</p>
                    <p class="text-xl font-bold text-white">₱{{ number_format($totalCollected, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200 mb-4">
        <nav class="flex space-x-6">
            <button wire:click="setTab(1)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 1 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Patient Accounts
                </span>
            </button>
            <button wire:click="setTab(2)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 2 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    For Billing
                    @php $unbilledCount = count($this->unbilled_requests); @endphp
                    @if($unbilledCount > 0)
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">{{ $unbilledCount }}</span>
                    @endif
                </span>
            </button>
            <button wire:click="setTab(3)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 3 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Invoices
                </span>
            </button>
            <button wire:click="setTab(4)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 4 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Payments
                </span>
            </button>
            <button wire:click="setTab(5)" class="pb-3 text-sm font-medium border-b-2 transition-colors {{ $activeTab === 5 ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Reports
                </span>
            </button>
        </nav>
    </div>

    @if($activeTab === 1)
    {{-- TAB 1: Patient Accounts (Ledger) --}}
    <div wire:transition.opacity.duration.300ms>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search patient name or code…"
                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
        </div>
    </div>

    @php $accounts = $this->patient_accounts; @endphp

    @if(empty($accounts))
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <p class="mt-3 text-sm font-medium text-gray-500">No patient accounts found.</p>
            <p class="mt-1 text-xs text-gray-400">Patient accounts are created when charges or payments are posted.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Charges</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Payments</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Balance</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Entries</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Activity</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($accounts as $acct)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="text-sm font-bold text-gray-900">{{ $acct['patient_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $acct['patient_code'] }}</div>
                                @if(!empty($acct['is_admitted']) && !empty($acct['days_stayed']))
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-200/60 shadow-xs" title="Admitted on {{ $acct['admission_date'] ?? '' }} in {{ $acct['room_name'] ?? '' }}">
                                        <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        {{ $acct['room_name'] ?? 'Inpatient' }} &bull; <strong class="text-teal-800">{{ $acct['days_stayed'] }}d</strong> to date
                                    </span>
                                </div>
                                @endif
                                @if(!empty($acct['has_unbilled']) && !empty($acct['unbilled_amount']) && $acct['unbilled_amount'] > 0)
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/50">
                                        Unbilled: ₱{{ number_format($acct['unbilled_amount'], 2) }}
                                    </span>
                                </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-semibold text-rose-600">₱{{ number_format($acct['total_charges'], 2) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-semibold text-emerald-600">₱{{ number_format($acct['total_payments'], 2) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-bold {{ $acct['balance'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">₱{{ number_format($acct['balance'], 2) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ $acct['entry_count'] }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-gray-500">{{ $acct['last_entry_date']?->format('M d, Y') ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button wire:click="viewPatientLedger({{ $acct['patient_id'] }})" title="View Ledger"
                                            class="inline-flex items-center px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Ledger
                                    </button>
                                    <button wire:click="openAddPayment({{ $acct['patient_id'] }})" title="Record Payment"
                                            class="inline-flex items-center px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Pay
                                    </button>
                                    <button wire:click="openManualChargeForPatient({{ $acct['patient_id'] }})" title="Add Adjustment"
                                            class="inline-flex items-center px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-semibold rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Charge
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    </div>
    @endif

    @if($activeTab === 2)
    {{-- TAB 2: For Billing (Legacy — unbilled requests view) --}}
    <div wire:transition.opacity.duration.300ms>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search patient name\u2026"
                   class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm shadow-sm"/>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    @php
        $allPatients = $this->unbilled_patients;
        $totalPatientCount = count($allPatients);
        $paginatedPatients = array_slice($allPatients, 0, (int) $perPage);
    @endphp

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/90 backdrop-blur-md sticky top-0 z-10 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Unbilled Items</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Amount</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($paginatedPatients as $pat)
                    <tr class="group hover:bg-emerald-50/40 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-semibold text-sm shadow-sm">
                                    {{ strtoupper(substr($pat['patient_name'], 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $pat['patient_name'] }}</p>
                                    <p class="text-xs text-gray-400 font-mono">ID: {{ $pat['patient_id'] }}</p>
                                    @if(!empty($pat['manual_charges']))
                                    <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200/50">
                                        +{{ count($pat['manual_charges']) }} adjustment(s)
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-50 text-amber-700 text-sm font-semibold border border-amber-200/50">
                                {{ $pat['item_count'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right font-mono font-semibold text-gray-900">₱{{ number_format($pat['total_amount'], 2) }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <button wire:click="openManualChargeForPatient({{ $pat['patient_id'] }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-sky-700 bg-sky-50 border border-sky-200 rounded-lg hover:bg-sky-100 transition-colors"
                                        title="Add adjustment for this patient">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Add Adjustment
                                </button>
                                <button wire:click="viewPatientCharges({{ $pat['patient_id'] }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors"
                                        title="View all unbilled charges for this patient">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View Charges
                                </button>
                                <button wire:click="payPatientCharges({{ $pat['patient_id'] }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-100 border border-emerald-200 rounded-lg hover:bg-emerald-200 transition-colors"
                                        title="Create invoice and record payment for this patient">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Pay
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100 shadow-sm">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <p class="text-base font-semibold text-gray-800">No unbilled patients found</p>
                            <p class="text-sm text-gray-500 mt-1 max-w-sm">All completed service requests have been processed and invoiced successfully.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($totalPatientCount > (int) $perPage)
    <div class="mt-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ min((int) $perPage, $totalPatientCount) }} of {{ $totalPatientCount }} patients</p>
        @if($totalPatientCount > (int) $perPage)
        <button wire:click="$set('perPage', {{ (int) $perPage + 10 }})" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors">
            Load more &darr;
        </button>
        @endif
    </div>
    @endif
    </div>
    @endif

    {{-- TAB 3: Invoices --}}
    @if($activeTab === 3)
    <div wire:transition.opacity.duration.300ms>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search invoice code, patient, or status…"
                   class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm shadow-sm"/>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/90 backdrop-blur-md sticky top-0 z-10 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('invoice_code')">
                        <span class="inline-flex items-center gap-1">Invoice # @if($sortField === 'invoice_code'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span>
                    </th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Paid</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Balance</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($invoices as $inv)
                    <tr class="group hover:bg-emerald-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $inv->invoice_code }}</td>
                        <td class="px-5 py-4 text-gray-800">{{ $inv->patient?->last_name }}, {{ $inv->patient?->first_name }}</td>
                        <td class="px-5 py-4 text-center text-gray-600">{{ $inv->invoice_date?->format('Y-m-d') }}</td>
                        <td class="px-5 py-4 text-right font-mono font-medium text-gray-900">₱{{ number_format($inv->total_amount, 2) }}</td>
                        <td class="px-5 py-4 text-right font-mono text-gray-700">₱{{ number_format($inv->paid_amount, 2) }}</td>
                        <td class="px-5 py-4 text-right font-mono font-semibold {{ $inv->balance > 0 ? 'text-red-600' : 'text-green-600' }}">₱{{ number_format($inv->balance, 2) }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $statusClasses = match($inv->status) {
                                    'Unpaid' => 'bg-red-50 text-red-700 border-red-200/50',
                                    'Partial' => 'bg-amber-50 text-amber-700 border-amber-200/50',
                                    'Paid' => 'bg-green-50 text-green-700 border-green-200/50',
                                    'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    'Credited' => 'bg-purple-50 text-purple-700 border-purple-200/50',
                                    default => 'bg-gray-50 text-gray-700 border-gray-200/50',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border shadow-sm {{ $statusClasses }}">{{ $inv->status }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-1.5">
                                <button wire:click="viewInvoice({{ $inv->id }})" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-md transition-colors" title="View Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <a href="/billing/invoice/{{ $inv->id }}/print" target="_blank" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-md transition-colors" title="Print Invoice HTML">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                <a href="/billing/invoice/{{ $inv->id }}/pdf" target="_blank" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-md transition-colors" title="Download True PDF Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                                @if($inv->status !== 'Paid' && $inv->status !== 'Cancelled')
                                <button wire:click="openAddPayment({{ $inv->id }})" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-md transition-colors" title="Add Payment">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                </button>
                                @endif
                                @if($inv->status !== 'Cancelled')
                                <button wire:click="confirmDeleteInvoice({{ $inv->id }})" wire:confirm="Cancel invoice {{ $inv->invoice_code }}? This will return all service requests to unbilled status." class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Cancel Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100 shadow-sm">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <p class="text-base font-semibold text-gray-800">No invoices found</p>
                            <p class="text-sm text-gray-500 mt-1 max-w-sm">No billing records match your search criteria. Generate a new invoice from the For Billing tab.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $invoices->links() }}</div>
    </div>
    @endif

    {{-- TAB 4: Payments --}}
    @if($activeTab === 4)
    <div wire:transition.opacity.duration.300ms>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search payment code, method, or reference…"
                   class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm shadow-sm"/>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 ... rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/90 backdrop-blur-md sticky top-0 z-10 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment Code</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reference/Source</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Method</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">OR/Ref #</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider w-14"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($payments as $pay)
                    <tr class="group hover:bg-emerald-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $pay->payment_code }}</td>
                        <td class="px-5 py-4 font-mono text-gray-700">{{ $pay->invoice?->invoice_code ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-800">{{ $pay->patient?->last_name }}, {{ $pay->patient?->first_name }}</td>
                        <td class="px-5 py-4 text-center text-gray-600">{{ $pay->payment_date?->format('Y-m-d') }}</td>
                        <td class="px-5 py-4 text-right font-mono font-semibold text-green-700">₱{{ number_format($pay->amount, 2) }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-700 border border-gray-200 shadow-sm">{{ $pay->payment_method }}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-600 text-xs">{{ $pay->reference_no ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('billing.payment.receipt', $pay->id) }}" target="_blank"
                                   class="p-1.5 text-teal-600 hover:bg-teal-100 rounded-md transition-colors" title="Print Receipt">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-16 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 border border-gray-100 shadow-sm">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <p class="text-base font-semibold text-gray-800">No payments recorded</p>
                            <p class="text-sm text-gray-500 mt-1 max-w-sm">There are no payment records matching your search. Add a payment through the Invoices tab.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
    </div>
    @endif

    {{-- TAB 5: Reports --}}
    @if($activeTab === 5)
    <div wire:transition.opacity.duration.300ms class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Aging Receivables --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Aging Receivables
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase">
                            <th class="py-2.5 text-left font-semibold">Bucket</th>
                            <th class="py-2.5 text-center font-semibold">Count</th>
                            <th class="py-2.5 text-right font-semibold">Total Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $agingTotal = 0; @endphp
                        @foreach($agingBuckets as $label => $bucket)
                            @php $agingTotal += $bucket['total']; @endphp
                            <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors">
                                <td class="py-2.5 font-medium text-gray-800">{{ $label }}</td>
                                <td class="py-2.5 text-center text-gray-600">{{ $bucket['count'] }}</td>
                                <td class="py-2.5 text-right font-mono font-semibold {{ $bucket['total'] > 0 ? 'text-red-600' : 'text-gray-600' }}">₱{{ number_format($bucket['total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold text-gray-900 border-t-2 border-gray-300">
                            <td class="py-2.5">Total Outstanding</td>
                            <td class="py-2.5 text-center">{{ collect($agingBuckets)->sum('count') }}</td>
                            <td class="py-2.5 text-right font-mono">₱{{ number_format($agingTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="space-y-4">
            <div class="bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-transparent rounded-xl border border-amber-200/60 p-4">
                <p class="text-xs font-medium text-amber-700 uppercase tracking-wider">Past Due (30+ days)</p>
                <p class="text-2xl font-bold text-amber-900 mt-1">₱{{ number_format(($agingBuckets['31-60 days']['total'] ?? 0) + ($agingBuckets['61-90 days']['total'] ?? 0) + ($agingBuckets['90+ days']['total'] ?? 0), 2) }}</p>
                <p class="text-xs text-amber-600 mt-1">{{ ($agingBuckets['31-60 days']['count'] ?? 0) + ($agingBuckets['61-90 days']['count'] ?? 0) + ($agingBuckets['90+ days']['count'] ?? 0) }} invoices overdue</p>
            </div>
            <div class="bg-gradient-to-br from-emerald-500/10 via-emerald-400/5 to-transparent rounded-xl border border-emerald-200/60 p-4">
                <p class="text-xs font-medium text-emerald-700 uppercase tracking-wider">Current (0-30 days)</p>
                <p class="text-2xl font-bold text-emerald-900 mt-1">₱{{ number_format($agingBuckets['0-30 days']['total'] ?? 0, 2) }}</p>
                <p class="text-xs text-emerald-600 mt-1">{{ $agingBuckets['0-30 days']['count'] ?? 0 }} invoices pending</p>
            </div>
        </div>
    </div>

    {{-- Daily Collections & Monthly Revenue --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Daily Collections (Last 30 Days)
            </h3>
            @if($dailyCollections->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase">
                            <th class="py-2 text-left font-semibold">Date</th>
                            <th class="py-2 text-center font-semibold">Payments</th>
                            <th class="py-2 text-right font-semibold">Total Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyCollections as $dc)
                        <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors">
                            <td class="py-2 text-gray-800">{{ \Carbon\Carbon::parse($dc->payment_date)->format('M d, Y') }}</td>
                            <td class="py-2 text-center text-gray-600">{{ $dc->count }}</td>
                            <td class="py-2 text-right font-mono font-semibold text-emerald-700">₱{{ number_format((float) $dc->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-semibold text-gray-900 border-t-2 border-gray-300">
                            <td class="py-2">Total</td>
                            <td class="py-2 text-center">{{ $dailyCollections->sum('count') }}</td>
                            <td class="py-2 text-right font-mono">₱{{ number_format((float) $dailyCollections->sum('total'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center mb-3 border border-emerald-100 shadow-sm">
                    <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-800">No payment data</p>
                <p class="text-xs text-gray-500 mt-1 max-w-xs">There are no recorded payments for the last 30 days.</p>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Monthly Revenue
            </h3>
            @if($monthlyRevenue->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs text-gray-500 uppercase">
                            <th class="py-2 text-left font-semibold">Period</th>
                            <th class="py-2 text-center font-semibold">Invoices</th>
                            <th class="py-2 text-right font-semibold">Total Billed</th>
                            <th class="py-2 text-right font-semibold">Collected</th>
                            <th class="py-2 text-right font-semibold">Collection Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyRevenue as $mr)
                        @php $rate = $mr->total > 0 ? round(($mr->collected / $mr->total) * 100, 1) : 0; @endphp
                        <tr class="border-b border-gray-100 hover:bg-gray-50/50 transition-colors">
                            <td class="py-2 text-gray-800 font-medium">{{ \Carbon\Carbon::create($mr->yr, $mr->mo, 1)->format('M Y') }}</td>
                            <td class="py-2 text-center text-gray-600">{{ $mr->count }}</td>
                            <td class="py-2 text-right font-mono text-gray-800">₱{{ number_format((float) $mr->total, 2) }}</td>
                            <td class="py-2 text-right font-mono text-emerald-700">₱{{ number_format((float) $mr->collected, 2) }}</td>
                            <td class="py-2 text-right font-mono font-semibold {{ $rate >= 80 ? 'text-emerald-600' : ($rate >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $rate }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-14 h-14 bg-indigo-50 rounded-full flex items-center justify-center mb-3 border border-indigo-100 shadow-sm">
                    <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-800">No invoice data</p>
                <p class="text-xs text-gray-500 mt-1 max-w-xs">There are no invoices available for the requested period.</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    @script
    <script>
    window.printCharges = function(){
        var el=document.getElementById('charges-print-json');
        if(!el)return;
        var items=JSON.parse(el.textContent),total=0,rows='';
        for(var i=0;i<items.length;i++){
            var c=items[i];
            total+=parseFloat(c.total_amount||0);
            var sourceType=(c.source_type||'').replace(/Manual/gi,'Adjustment');
            rows+='<tr><td style="padding:8px 12px;border-bottom:1px solid #e5e7eb">'+sourceType+'</td>'+
                  '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;font-family:monospace">'+c.request_code+'</td>'+
                  '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb">'+c.department+'</td>'+
                  '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;font-family:monospace;text-align:right">\u20B1'+parseFloat(c.total_amount||0).toFixed(2)+'</td>'+
                  '<td style="padding:8px 12px;border-bottom:1px solid #e5e7eb;text-align:center">'+c.date+'</td></tr>';
        }
        var pageRule='@@page{margin:20mm 15mm}';
        var html='<!DOCTYPE html><html><head><meta charset=utf-8><title>Patient Charges</title>'+
            '<style>'+pageRule+'body{font-family:Segoe UI,Arial,sans-serif;color:#111;margin:0;padding:20px}'+
            '.hdr{text-align:center;margin-bottom:24px;border-bottom:2px solid #059669;padding-bottom:16px}'+
            '.hdr h1{font-size:18pt;margin:0;color:#065f46}'+
            '.hdr .nm{font-size:13pt;font-weight:600;margin-top:8px}'+
            '.hdr .dt{font-size:9pt;color:#6b7280;margin-top:4px}'+
            'table{width:100%;border-collapse:collapse;margin-top:12px}'+
            'th{background:#f3f4f6;padding:8px 12px;text-align:left;font-size:9pt;text-transform:uppercase;color:#374151;border-bottom:2px solid #d1d5db}'+
            'td{padding:8px 12px;border-bottom:1px solid #e5e7eb;font-size:10pt}'+
            '.tot td{border-top:2px solid #059669;font-weight:700;font-size:11pt;padding-top:10px}'+
            '.ftr{text-align:center;margin-top:32px;font-size:8pt;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:12px}'+
            '</style></head><body>'+
            '<div class=hdr><h1>STATEMENT OF CHARGES</h1><div class=nm>'+el.dataset.patient+'</div>'+
            '<div class=dt>'+new Date().toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'})+' | '+new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})+'</div></div>'+
            '<table><thead><tr><th>Source</th><th>Request Code</th><th>Department</th><th style=text-align:right>Amount</th><th style=text-align:center>Date</th></tr></thead>'+
            '<tbody>'+rows+'</tbody>'+
            '<tfoot><tr class=tot><td colspan=3 style=text-align:right>Total Unbilled:</td><td style=text-align:right;font-family:monospace>\u20B1'+total.toFixed(2)+'</td><td></td></tr></tfoot></table>'+
            '<div class=ftr>This is a computer-generated document. No signature required.</div>'+
            '<scr'+'ipt>window.onload=function(){window.print();window.close()}<\/scr'+'ipt></body></html>';
        var w=window.open('','_blank');
        if(w){w.document.write(html);w.document.close()}
    };

    Livewire.on('openStatementPdf', (params) => {
        var iframe = document.getElementById('statement-iframe');
        if (!iframe) return;
        // Livewire 3 dispatches named params as an object: {url: '...'}
        // Livewire 3 also sometimes wraps in array: [{url: '...'}]
        var url = '';
        if (Array.isArray(params) && params.length > 0) {
            url = (typeof params[0] === 'object') ? params[0].url : params[0];
        } else if (typeof params === 'object' && params !== null) {
            url = params.url || '';
        } else {
            url = params;
        }
        if (url) iframe.src = url;
    });
    </script>
    @endscript

    {{-- VIEW CHARGES MODAL --}}
    @if($showChargesModal)
    <pre id="charges-print-json" style="display:none" data-patient="{{ $viewingPatientName }}">@json($viewingPatientCharges)</pre>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-md" wire:click="closeChargesModal" wire:transition.opacity.duration.300ms></div>
        <div class="relative bg-white rounded-3xl shadow-[0_25px_60px_-12px_rgba(0,0,0,0.25)] w-full max-w-3xl z-10 overflow-hidden max-h-[92vh] flex flex-col" wire:transition.scale.origin.bottom.duration.300ms>
            {{-- Header --}}
            <div class="relative px-7 pt-6 pb-5 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDM0djItSDI0di0yaDEyem0wLTRWMjhIMjR2Mmgxem0wLTRWMjRIMjR2Mmgxem0tMS00SDE4djJoMnptOSA4aC0ydjJoMnptLTYgMGgtMnYyaDJ6bS02IDBoLTJ2Mmgyek0zMSAxNGgtMnYyaDJ6bS02IDBoLTJ2Mmgyek0yNSA4aC0ydjJoMnptNiAwaC0ydjJoMnoiLz48L2c+PC9nPjwvc3ZnPg==')] opacity-40"></div>
                <div class="relative flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/10 backdrop-blur-sm rounded-lg text-[11px] font-semibold uppercase tracking-widest text-emerald-300">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Patient Ledger
                            </span>
                        </div>
                        <h3 class="text-xl font-bold tracking-tight text-white mt-2">{{ $viewingPatientName }}</h3>
                        <div class="flex items-center gap-4 mt-2.5">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-white/50">Items</span>
                                <span class="text-sm font-bold text-white">{{ count($viewingPatientCharges) }}</span>
                            </div>
                            <div class="w-px h-4 bg-white/20"></div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-white/50">Total</span>
                                <span class="text-sm font-bold text-emerald-300">₱{{ number_format(collect($viewingPatientCharges)->sum('total_amount'), 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <button wire:click="closeChargesModal" class="p-2 -m-2 text-white/40 hover:text-white hover:bg-white/10 rounded-xl transition-all" title="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="flex-1 overflow-y-auto px-7 py-1">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr>
                            <th class="py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Source</th>
                            <th class="py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Code / Room</th>
                            <th class="py-3 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Department</th>
                            <th class="py-3 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Amount</th>
                            <th class="py-3 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Date</th>
                            <th class="py-3 w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($viewingPatientCharges as $idx => $charge)
                        <tr class="group hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wide
                                    {{ match($charge['source_type'] ?? $charge['source_type']) {
                                        'Lab' => 'bg-violet-50 text-violet-700 ring-1 ring-violet-200/60',
                                        'Pharmacy' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/60',
                                        'CSR' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/60',
                                        'Radiology' => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200/60',
                                        'Procedure' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200/60',
                                        'OECB' => 'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200/60',
                                        'Admission' => 'bg-teal-50 text-teal-700 ring-1 ring-teal-200/60',
                                        'Manual' => 'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200/60',
                                        default => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200/60',
                                    } }}">
                                    {{ $charge['source_type'] ?? ($charge['charge_type'] ?? 'Manual') }}
                                </span>
                            </td>
                            <td class="py-3.5">
                                <span class="font-mono font-semibold text-slate-900 text-[13px]">{{ $charge['request_code'] ?? '—' }}</span>
                                @if(!empty($charge['room_name']) && ($charge['source_type'] ?? '') === 'Admission')
                                <div class="mt-0.5 flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-teal-50 text-teal-700 rounded-md text-[10px] font-semibold ring-1 ring-teal-200/50">{{ $charge['room_name'] }}</span>
                                    @if(!empty($charge['days']))
                                    <span class="text-[11px] text-slate-400 font-medium">{{ $charge['days'] }}d @ ₱{{ number_format($charge['rate'], 2) }}/d</span>
                                    @endif
                                </div>
                                @endif
                                @if(($charge['source_type'] ?? '') === 'Manual' && !empty($charge['description']))
                                <div class="mt-0.5">
                                    <span class="text-[11px] text-cyan-600 font-medium">{{ $charge['description'] }}</span>
                                    @if(($charge['quantity'] ?? 1) > 1)
                                    <span class="text-[10px] text-slate-400 ml-1">×{{ $charge['quantity'] }} @ ₱{{ number_format($charge['unit_price'] ?? 0, 2) }}</span>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td class="py-3.5 text-slate-500 text-[13px]">{{ $charge['department'] ?? '—' }}</td>
                            <td class="py-3.5 text-right">
                                <span class="font-mono font-bold text-slate-900 text-[13px]">₱{{ number_format($charge['total_amount'], 2) }}</span>
                            </td>
                            <td class="py-3.5 text-center">
                                <span class="text-[11px] text-slate-400 font-medium tabular-nums">{{ $charge['date'] }}</span>
                            </td>
                            @if(($charge['source_type'] ?? '') === 'Manual')
                            <td class="py-3.5 text-center">
                                <button wire:click="removeManualChargeForPatient({{ $charge['patient_id'] }}, {{ $idx }})" wire:confirm="Remove this adjustment?" class="p-1.5 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg opacity-0 group-hover:opacity-100 transition-all" title="Remove">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </td>
                            @else
                            <td></td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div class="px-7 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between no-print">
                <button onclick="printCharges()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 hover:text-slate-800 rounded-xl text-sm font-semibold shadow-sm hover:shadow transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
                <div class="flex items-center gap-2.5">
                    @if(!empty($viewingPatientCharges[0]['patient_id'] ?? null))
                    <button wire:click="openManualChargeForPatient({{ $viewingPatientCharges[0]['patient_id'] }})"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 hover:border-cyan-300 rounded-xl text-sm font-semibold transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add Adjustment
                    </button>
                    @endif
                    <button wire:click="payPatientCharges({{ $viewingPatientCharges[0]['patient_id'] ?? 0 }})"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                        Create Invoice &amp; Pay
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- PRE-INVOICE MANUAL CHARGE MODAL --}}
    @if($showPatientManualChargeForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click="closePatientManualChargeForm" wire:transition.opacity.duration.300ms></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden" wire:transition.scale.origin.bottom.duration.300ms>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-sky-50/50 backdrop-blur-sm">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Adjustment
                </h3>
                <button wire:click="closePatientManualChargeForm" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="addManualChargeForPatient" class="px-6 py-5 space-y-4">
                @if($manualChargePatientId)
                <p class="text-xs text-gray-500">
                    Adding charge for patient <span class="font-semibold text-gray-700">{{ \App\Models\Patient::find($manualChargePatientId)?->last_name }}, {{ \App\Models\Patient::find($manualChargePatientId)?->first_name }}</span>
                </p>
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Charge Type</label>
                        <select wire:model="manualChargeType" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                            <option value="Miscellaneous">Miscellaneous</option>
                            <option value="Administrative">Administrative Fee</option>
                            <option value="Supply">Supply / Materials</option>
                            <option value="Professional Fee">Professional Fee</option>
                            <option value="Transport">Transport / Escort</option>
                            <option value="Equipment">Equipment Rental</option>
                            <option value="Room Differential">Room / Accommodation Differential</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description <span class="text-red-500">*</span></label>
                        <input wire:model="manualChargeDesc" type="text" placeholder="e.g. Wheelchair rental, Med Cert" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 @error('manualChargeDesc') border-red-300 bg-red-50 @enderror">
                        @error('manualChargeDesc') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity</label>
                        <input wire:model="manualChargeQty" type="number" step="0.01" min="0.01" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit Price (₱)</label>
                        <input wire:model="manualChargePrice" type="number" step="0.01" min="0" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 @error('manualChargePrice') border-red-300 bg-red-50 @enderror">
                        @error('manualChargePrice') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes (optional)</label>
                    <input wire:model="manualChargeNotes" type="text" placeholder="Internal notes..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <p class="text-sm text-gray-700 font-semibold">
                        Total: ₱{{ number_format((float) ($manualChargeQty ?: 0) * (float) ($manualChargePrice ?: 0), 2) }}
                    </p>
                    <div class="flex gap-2">
                        <button type="button" wire:click="closePatientManualChargeForm" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Add Charge
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- CREATE INVOICE MODAL --}}
    @if($showInvoiceModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click="closeInvoiceModal" wire:transition.opacity.duration.300ms></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden" wire:transition.scale.origin.bottom.duration.300ms>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/50 backdrop-blur-sm">
                <h3 class="text-lg font-semibold text-gray-900">Generate Invoice</h3>
                <button wire:click="closeInvoiceModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="createInvoice" class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Invoice Code</label>
                        <input wire:model="invoice_code" type="text" readonly disabled class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm font-mono text-gray-600 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Invoice Date</label>
                        <input wire:model="invoice_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Patient <span class="text-red-500">*</span></label>
                    <div class="relative" wire:ignore.self>
                        <input wire:model.live.debounce.300ms="patient_search" type="text" placeholder="Search by name or patient code..." 
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('patient_id') border-red-300 bg-red-50 @enderror">
                        @if($patients && count($patients) > 0)
                        <div class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            @foreach($patients as $p)
                            <button type="button" wire:click="selectPatient({{ $p->id }})" class="w-full text-left px-4 py-2.5 text-sm hover:bg-emerald-50 transition-colors flex items-center justify-between">
                                <span class="font-medium">{{ $p->last_name }}, {{ $p->first_name }}</span>
                                <span class="text-xs text-gray-400 font-mono">{{ $p->patient_code }}</span>
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @if($patient_id)
                    <p class="mt-1 text-xs text-emerald-600 font-medium">
                        Selected: {{ \App\Models\Patient::find($patient_id)?->last_name }}, {{ \App\Models\Patient::find($patient_id)?->first_name }}
                        <button type="button" wire:click="$set('patient_id', null)" class="ml-2 text-red-500 hover:underline">Clear</button>
                    </p>
                    @endif
                    @error('patient_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea wire:model="invoice_notes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Optional invoice notes..."></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">Selected Items ({{ count($selectedServiceItems) }}{{ ($patientManualCharges[$patient_id] ?? []) ? ' + ' . count($patientManualCharges[$patient_id]) . ' manual' : '' }})</label>
                        <span class="text-xs text-gray-500">Check items from the For Billing tab</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-3 max-h-40 overflow-y-auto">
                        @php $unbilled = $this->unbilled_requests; @endphp
                        @if(count($selectedServiceItems) > 0)
                            @foreach($selectedServiceItems as $selId)
                                @php $req = collect($unbilled)->firstWhere('id', $selId); @endphp
                                @if($req)
                                <div class="flex items-center justify-between py-1.5 text-sm">
                                    <span><span class="font-mono font-medium">{{ $req['source_type'] }}</span> {{ $req['request_code'] }}</span>
                                    <span class="font-mono text-gray-700">₱{{ number_format($req['total_amount'], 2) }}</span>
                                </div>
                                @endif
                            @endforeach
                        @endif
                        @if(!empty($patientManualCharges[$patient_id] ?? []))
                            <div class="border-t border-sky-200 mt-1 pt-1">
                                @foreach($patientManualCharges[$patient_id] as $mcIdx => $mc)
                                <div class="flex items-center justify-between py-1.5 text-sm">
                                    <span class="text-sky-700"><span class="font-mono font-medium">Adjustment</span> {{ $mc['charge_type'] }} &mdash; {{ $mc['description'] }}</span>
                                    <span class="font-mono text-sky-700">₱{{ number_format($mc['total_price'], 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                        @endif
                        @if(count($selectedServiceItems) > 0 || !empty($patientManualCharges[$patient_id] ?? []))
                            @php
                                $serviceTotal = collect($unbilled)->whereIn('id', $selectedServiceItems)->sum('total_amount');
                                $manualTotal = collect($patientManualCharges[$patient_id] ?? [])->sum('total_price');
                            @endphp
                            <div class="border-t border-gray-200 mt-2 pt-2 flex justify-between font-semibold text-sm">
                                <span>Total</span>
                                <span class="font-mono">₱{{ number_format($serviceTotal + $manualTotal, 2) }}</span>
                            </div>
                        @else
                            <p class="text-sm text-gray-400 text-center py-4">No services selected. Go to the For Billing tab and check items or add adjustments.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">Discount</label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <select wire:model.live="discount_type" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="">No Discount</option>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed (₱)</option>
                            </select>
                        </div>
                        <div>
                            <input wire:model.live="discount_value" type="number" step="0.01" min="0" placeholder="0.00"
                                   class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 {{ !$discount_type ? 'opacity-50' : '' }}"
                                   @if(!$discount_type) disabled @endif>
                        </div>
                        <div class="flex items-center justify-end">
                            @if($discount_type && $discount_amount > 0)
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Discount</p>
                                <p class="text-sm font-mono font-semibold text-red-600">-₱{{ number_format($discount_amount, 2) }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @if($discount_type === 'percentage' && $discount_value > 0)
                    <p class="mt-1 text-xs text-gray-500">₱{{ number_format((float) collect($unbilled)->whereIn('id', $selectedServiceItems)->sum('total_amount') + (float) collect($patientManualCharges[$patient_id] ?? [])->sum('total_price'), 2) }} × {{ $discount_value }}% = -₱{{ number_format($discount_amount, 2) }}</p>
                    @endif
                    @php $totalBeforeDiscount = (float) collect($unbilled)->whereIn('id', $selectedServiceItems)->sum('total_amount') + (float) collect($patientManualCharges[$patient_id] ?? [])->sum('total_price'); @endphp
                    @if($discount_amount > 0)
                    <div class="mt-2 flex justify-between text-sm font-semibold border-t border-gray-200 pt-2">
                        <span class="text-gray-700">Net Total</span>
                        <span class="font-mono text-emerald-700">₱{{ number_format(max(0, $totalBeforeDiscount - $discount_amount), 2) }}</span>
                    </div>
                    @endif
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeInvoiceModal" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Create Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- INVOICE DETAIL MODAL --}}
    @if($showInvoiceDetailModal && $detailInvoice)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click="closeInvoiceDetailModal" wire:transition.opacity.duration.300ms></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 overflow-hidden max-h-[90vh] overflow-y-auto" wire:transition.scale.origin.bottom.duration.300ms>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/90 backdrop-blur-md sticky top-0 z-10">
                <h3 class="text-lg font-semibold text-gray-900">Invoice {{ $detailInvoice->invoice_code }}</h3>
                <div class="flex items-center gap-2" x-data="{ printMode: 'detail' }">
                    <div class="inline-flex items-center rounded-lg border border-emerald-200 overflow-hidden text-xs">
                        <button @click="printMode = 'detail'" :class="printMode === 'detail' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700'" class="px-2.5 py-1.5 font-medium transition-colors">Detail</button>
                        <button @click="printMode = 'summary'" :class="printMode === 'summary' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700'" class="px-2.5 py-1.5 font-medium transition-colors border-l border-emerald-200">Summary</button>
                    </div>
                    <a :href="'/billing/invoice/' + {{ $detailInvoice->id }} + '/print?mode=' + printMode" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors"
                       :title="'Print Invoice (' + printMode.charAt(0).toUpperCase() + printMode.slice(1) + ' mode)'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print HTML
                    </a>
                    <a href="/billing/invoice/{{ $detailInvoice->id }}/pdf" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                       title="Download / View True PDF Invoice">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        PDF
                    </a>
                    <button wire:click="closeInvoiceDetailModal" class="text-gray-400 hover:text-gray-600 transition-colors" title="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="px-6 py-5 space-y-6">
                {{-- Invoice Info --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div><p class="text-xs text-gray-500 uppercase">Invoice Code</p><p class="text-sm font-mono font-semibold">{{ $detailInvoice->invoice_code }}</p></div>
                    <div><p class="text-xs text-gray-500 uppercase">Date</p><p class="text-sm">{{ $detailInvoice->invoice_date?->format('Y-m-d') }}</p></div>
                    <div><p class="text-xs text-gray-500 uppercase">Patient</p><p class="text-sm font-medium">{{ $detailInvoice->patient?->last_name }}, {{ $detailInvoice->patient?->first_name }}</p></div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Status</p>
                        @php $sc = match($detailInvoice->status) { 'Unpaid' => 'bg-red-50 text-red-700', 'Partial' => 'bg-amber-50 text-amber-700', 'Paid' => 'bg-green-50 text-green-700', 'Cancelled' => 'bg-gray-50 text-gray-600', default => 'bg-gray-50 text-gray-700' }; @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border shadow-sm {{ $sc }}">{{ $detailInvoice->status }}</span>
                    </div>
                </div>

                {{-- Line Items --}}
                @php
                    // Show Add/Remove Adjustment for everything except Cancelled invoices.
                    // Hospital staff are responsible for managing when charges are appropriate.
                    $canModifyCharges = $detailInvoice->status !== 'Cancelled';
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-semibold text-gray-800">Service Items</h4>
                        @if($canModifyCharges)
                            <button wire:click="openManualChargeForm" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-sky-700 bg-sky-50 border border-sky-200 rounded-lg hover:bg-sky-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Add Adjustment
                        </button>
                        @endif
                    </div>
                    <table class="min-w-full text-sm">
                        <thead><tr class="border-b border-gray-200 text-xs text-gray-500 uppercase"><th class="py-2 text-left">Source</th><th class="py-2 text-left">Description</th><th class="py-2 text-right">Amount</th></tr></thead>
                        <tbody>
                            @foreach($detailInvoice->items as $item)
                            <tr class="border-b border-gray-100">
                                <td class="py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                        {{ match($item->source_type) { 'Lab' => 'bg-purple-50 text-purple-700', 'Pharmacy' => 'bg-emerald-50 text-emerald-700', 'CSR' => 'bg-amber-50 text-amber-700', 'Radiology' => 'bg-indigo-50 text-indigo-700', 'Procedure' => 'bg-rose-50 text-rose-700', 'OECB' => 'bg-sky-50 text-sky-700', 'Manual' => 'bg-sky-50 text-sky-700', default => 'bg-gray-50 text-gray-700' } }}">
                                        {{ $item->source_type }}
                                    </span>
                                </td>
                                <td class="py-2">
                                    <span class="font-medium">{{ $item->description }}</span>
                                    @if($item->source_request_code)<br><span class="text-xs text-gray-400 font-mono">{{ $item->source_request_code }}</span>@endif
                                    @if($item->source_type === 'Admission' && $item->quantity > 0)
                                    <br><span class="text-xs text-teal-600">{{ $item->quantity }} day(s) @ ₱{{ number_format($item->unit_price, 2) }}/day</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right font-mono">₱{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                            @if($detailInvoice->manualCharges && $detailInvoice->manualCharges->count() > 0)
                            <tr class="border-b border-gray-100 bg-sky-50/30">
                                <td colspan="3" class="py-2 px-2">
                                    <span class="text-xs font-semibold text-sky-700 uppercase tracking-wider">Adjustments</span>
                                </td>
                            </tr>
                            @foreach($detailInvoice->manualCharges as $mc)
                            @if($mc->history)
                            <tr class="border-b border-gray-100 bg-sky-50/10 group/manual">
                                <td class="py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200/50">{{ $mc->charge_type }}</span>
                                </td>
                                <td class="py-2">
                                    <span class="font-medium">{{ $mc->description }}</span>
                                    @if($mc->quantity > 1)<br><span class="text-xs text-gray-400">{{ $mc->quantity }} x ₱{{ number_format($mc->unit_price, 2) }}</span>@endif
                                </td>
                                <td class="py-2 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="font-mono">₱{{ number_format($mc->total_price, 2) }}</span>
                                        @if($canModifyCharges)
                                        <button wire:click="removeManualCharge({{ $mc->id }})" wire:confirm="Remove this adjustment?" class="p-0.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Remove">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                            @endif
                        </tbody>
                        @php
                            $showDetailDiscountEdit = $canModifyCharges;
                            $detailDiscountVal = (float) $detail_discount_value;
                            $detailDiscountAmt = (float) $detail_discount_amount;
                        @endphp
                        <tfoot>
                            <tr class="font-semibold"><td colspan="2" class="py-2 text-right">Total:</td><td class="py-2 text-right font-mono">₱{{ number_format($detailInvoice->total_amount, 2) }}</td></tr>
                            @if($showDetailDiscountEdit)
                            <tr class="border-t border-gray-100">
                                <td colspan="3" class="py-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-medium text-gray-600">Discount:</span>
                                        <select wire:model.live="detail_discount_type" class="text-xs px-2 py-1 border border-gray-200 rounded bg-gray-50 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                            <option value="">None</option>
                                            <option value="percentage">%</option>
                                            <option value="fixed">₱</option>
                                        </select>
                                        <input wire:model.live="detail_discount_value" type="number" step="0.01" min="0" placeholder="0"
                                               class="w-20 text-xs px-2 py-1 border border-gray-200 rounded bg-gray-50 focus:outline-none focus:ring-1 focus:ring-emerald-500 {{ !$detail_discount_type ? 'opacity-40' : '' }}"
                                               @if(!$detail_discount_type) disabled @endif>
                                        @if($detail_discount_type && $detailDiscountAmt > 0)
                                        <span class="text-xs font-mono text-red-600">-₱{{ number_format($detailDiscountAmt, 2) }}</span>
                                        <span class="text-xs text-gray-500">Net: ₱{{ number_format(max(0, $detailInvoice->total_amount - $detailDiscountAmt), 2) }}</span>
                                        @endif
                                        <button wire:click="saveInvoiceDiscount" class="text-xs px-2.5 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition-colors {{ !$detail_discount_type ? 'opacity-50 cursor-not-allowed' : '' }}" @if(!$detail_discount_type) disabled @endif>Save</button>
                                        @if($detailInvoice->discount_amount > 0)
                                        <button wire:click="$set('detail_discount_type', ''); $set('detail_discount_value', '0')" class="text-xs px-2 py-1 text-gray-500 hover:text-red-600 transition-colors">Clear</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @elseif($detailInvoice->discount_amount > 0)
                            <tr class="text-red-600"><td colspan="2" class="py-1 text-right">
                                Discount{{ $detailInvoice->discount_type === 'percentage' ? ' (' . rtrim(rtrim(number_format($detailInvoice->discount_value, 2), '0'), '.') . '%)' : '' }}
                            </td><td class="py-1 text-right font-mono">-₱{{ number_format($detailInvoice->discount_amount, 2) }}</td></tr>
                            <tr class="font-semibold text-gray-800"><td colspan="2" class="py-1 text-right">Net Total:</td><td class="py-1 text-right font-mono">₱{{ number_format($detailInvoice->net_amount, 2) }}</td></tr>
                            @endif
                            <tr class="text-green-700"><td colspan="2" class="py-1 text-right">Paid:</td><td class="py-1 text-right font-mono">₱{{ number_format($detailInvoice->paid_amount, 2) }}</td></tr>
                            <tr class="text-red-700 font-semibold"><td colspan="2" class="py-1 text-right">Balance:</td><td class="py-1 text-right font-mono">₱{{ number_format($detailInvoice->balance, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Payments --}}
                @if($detailInvoice->payments->count() > 0)
                <div>
                    <h4 class="text-sm font-semibold text-gray-800 mb-2">Payments</h4>
                    <table class="min-w-full text-sm">
                        <thead><tr class="border-b border-gray-200 text-xs text-gray-500 uppercase"><th class="py-2 text-left">Code</th><th class="py-2 text-center">Date</th><th class="py-2 text-center">Method</th><th class="py-2 text-left">Reference</th><th class="py-2 text-right">Amount</th><th class="py-2 w-10"></th></tr></thead>
                        <tbody>
                            @foreach($detailInvoice->payments as $pay)
                            <tr class="border-b border-gray-100 group/payrow">
                                <td class="py-2 font-mono text-xs">{{ $pay->payment_code }}</td>
                                <td class="py-2 text-center text-xs">{{ $pay->payment_date?->format('Y-m-d') }}</td>
                                <td class="py-2 text-center"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-50 text-gray-700">{{ $pay->payment_method }}</span></td>
                                <td class="py-2 text-xs text-gray-500">{{ $pay->reference_no ?? '—' }}</td>
                                <td class="py-2 text-right font-mono text-green-700">₱{{ number_format($pay->amount, 2) }}</td>
                                <td class="py-2 text-center">
                                    <a href="{{ route('billing.payment.receipt', $pay->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-teal-700 bg-teal-50 border border-teal-200 rounded-md hover:bg-teal-100 transition-colors"
                                       title="Print Receipt">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Receipt
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Adjustment Form --}}
                @if($showManualChargeForm)
                <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-sky-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            New Adjustment
                        </h4>
                        <button wire:click="$set('showManualChargeForm', false)" class="text-sky-400 hover:text-sky-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-sky-700 mb-1">Charge Type</label>
                            <select wire:model="manualChargeType" class="w-full px-3 py-2 bg-white border border-sky-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                                <option value="Miscellaneous">Miscellaneous</option>
                                <option value="Administrative">Administrative Fee</option>
                                <option value="Supply">Supply / Materials</option>
                                <option value="Professional Fee">Professional Fee</option>
                                <option value="Transport">Transport / Escort</option>
                                <option value="Equipment">Equipment Rental</option>
                                <option value="Room Differential">Room / Accommodation Differential</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-sky-700 mb-1">Description <span class="text-red-500">*</span></label>
                            <input wire:model="manualChargeDesc" type="text" placeholder="e.g. Wheelchair rental, Med Cert" class="w-full px-3 py-2 bg-white border border-sky-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 @error('manualChargeDesc') border-red-300 bg-red-50 @enderror">
                            @error('manualChargeDesc') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-sky-700 mb-1">Quantity</label>
                            <input wire:model="manualChargeQty" type="number" step="0.01" min="0.01" class="w-full px-3 py-2 bg-white border border-sky-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-sky-700 mb-1">Unit Price (₱)</label>
                            <input wire:model="manualChargePrice" type="number" step="0.01" min="0" class="w-full px-3 py-2 bg-white border border-sky-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 @error('manualChargePrice') border-red-300 bg-red-50 @enderror">
                            @error('manualChargePrice') <p class="mt-0.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-sky-700 mb-1">Notes (optional)</label>
                        <input wire:model="manualChargeNotes" type="text" placeholder="Internal notes..." class="w-full px-3 py-2 bg-white border border-sky-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <p class="text-sm text-sky-800 font-semibold">
                            Total: ₱{{ number_format((float) ($manualChargeQty ?: 0) * (float) ($manualChargePrice ?: 0), 2) }}
                        </p>
                        <div class="flex gap-2">
                            <button wire:click="$set('showManualChargeForm', false)" type="button" class="px-3 py-1.5 text-xs font-medium border border-sky-200 rounded-lg text-sky-700 hover:bg-sky-100 transition-colors">Cancel</button>
                            <button wire:click="addManualCharge" type="button" class="px-3 py-1.5 text-xs font-medium bg-sky-600 hover:bg-sky-700 text-white rounded-lg transition-colors inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Add Charge
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    @if($detailInvoice->status !== 'Cancelled')
                    <button type="button" wire:click="openAddPayment({{ $detailInvoice->id }})" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors inline-flex items-center gap-2"
                        @if($detailInvoice->status === 'Paid') style="display:none" @endif>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add Payment
                    </button>
                    @endif
                    <button type="button" wire:click="closeInvoiceDetailModal" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- PATIENT LEDGER MODAL --}}
    @if($showLedgerModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-2">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click="closeLedgerModal" wire:transition.opacity.duration.300ms></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-[95vw] z-10 overflow-hidden transform transition-all max-h-[95vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Patient Account Ledger</h3>
                        <p class="text-sm text-gray-500">{{ $viewingPatientName }}</p>
                    </div>
                    <div class="flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm ml-4">
                        <label class="flex items-center px-3 py-1.5 text-xs font-medium cursor-pointer transition-colors {{ $statementMode === 'summary' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            <input type="radio" wire:model.live="statementMode" value="summary" class="sr-only">
                            Summary
                        </label>
                        <label class="flex items-center px-3 py-1.5 text-xs font-medium cursor-pointer transition-colors border-l border-gray-200 {{ $statementMode === 'detailed' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                            <input type="radio" wire:model.live="statementMode" value="detailed" class="sr-only">
                            Detailed
                        </label>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-xs font-medium text-gray-500 uppercase">Outstanding Balance</p>
                        <p class="text-xl font-bold {{ $viewingPatientBalance > 0 ? 'text-amber-600' : 'text-emerald-600' }}">₱{{ number_format($viewingPatientBalance, 2) }}</p>
                    </div>
                    <button wire:click="closeLedgerModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Patient Info Header --}}
            <div class="px-6 py-4 bg-blue-50/60 border-b border-blue-100">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-x-6 gap-y-2">
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Patient Code</p>
                        <p class="text-sm font-medium text-gray-800">{{ $viewingPatientCode }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Date of Birth</p>
                        <p class="text-sm font-medium text-gray-800">{{ $viewingPatientDob }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Gender</p>
                        <p class="text-sm font-medium text-gray-800">{{ $viewingPatientGender }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Contact</p>
                        <p class="text-sm font-medium text-gray-800">{{ $viewingPatientContact }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Address</p>
                        <p class="text-sm font-medium text-gray-800 truncate" title="{{ $viewingPatientAddress }}">{{ $viewingPatientAddress }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Latest Encounter</p>
                        <p class="text-sm font-medium text-gray-800">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($viewingPatientEncounterType === 'ER') bg-red-100 text-red-700
                                @elseif($viewingPatientEncounterType === 'Admission') bg-indigo-100 text-indigo-700
                                @elseif($viewingPatientEncounterType === 'OPD') bg-emerald-100 text-emerald-700
                                @elseif($viewingPatientEncounterType === 'OB') bg-pink-100 text-pink-700
                                @else bg-gray-100 text-gray-500 @endif">
                                {{ $viewingPatientEncounterType }}
                            </span>
                            {{ $viewingPatientEncounterCode }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Encounter Date</p>
                        <p class="text-sm font-medium text-gray-800">{{ $viewingPatientAdmissionDate }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Physician</p>
                        <p class="text-sm font-medium text-gray-800 truncate" title="{{ $viewingPatientPhysician }}">{{ $viewingPatientPhysician }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Room / Bed</p>
                        <p class="text-sm font-medium text-gray-800">{{ $viewingPatientRoom }}</p>
                    </div>
                    @if($viewingPatientDaysStayed !== null)
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Days Stayed</p>
                        <p class="text-sm font-bold text-teal-700">
                            {{ $viewingPatientDaysStayed }} {{ $viewingPatientDaysStayed === 1 ? 'day' : 'days' }}
                            @if($viewingPatientIsAdmitted)
                            <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded ring-1 ring-emerald-200/60 ml-0.5">to date</span>
                            @endif
                        </p>
                    </div>
                    @endif
                    @if($viewingPatientAccruedRoomCharge !== null && $viewingPatientAccruedRoomCharge > 0)
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Accrued Room &amp; Board</p>
                        <p class="text-sm font-bold text-amber-700 font-mono">
                            ₱{{ number_format($viewingPatientAccruedRoomCharge, 2) }}
                            @if($viewingPatientRoomRate)
                            <span class="text-[10px] font-normal text-slate-500 font-sans block">({{ $viewingPatientRoomRate }})</span>
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="overflow-y-auto flex-1 px-6 py-4">
                @if($viewingPatientIsAdmitted && $viewingPatientDaysStayed)
                <div class="mb-4 p-3.5 bg-gradient-to-r from-teal-50 via-emerald-50 to-teal-50/50 border border-teal-200 rounded-xl flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-100 flex items-center justify-center text-teal-700 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-teal-900">Active Inpatient Confinement &bull; Room &amp; Board Auto-Computed</p>
                            <p class="text-[12px] text-teal-700 mt-0.5">
                                Location: <strong>{{ $viewingPatientRoom }}</strong> &bull;
                                Length of stay: <strong class="text-teal-900">{{ $viewingPatientDaysStayed }} {{ $viewingPatientDaysStayed === 1 ? 'day' : 'days' }}</strong> (since {{ $viewingPatientAdmissionDate }}) &bull;
                                Rate: <strong>{{ $viewingPatientRoomRate }}</strong>
                            </p>
                        </div>
                    </div>
                    <div class="text-right pl-4">
                        <span class="text-[10px] uppercase font-bold text-teal-600 block tracking-wider">Accrued to Date</span>
                        <span class="text-base font-extrabold text-teal-900 font-mono">₱{{ number_format($viewingPatientAccruedRoomCharge, 2) }}</span>
                    </div>
                </div>
                @endif
                @if(empty($viewingLedger))
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-400">No ledger entries for this patient.</p>
                    </div>
                @else
                    {{-- Selection action bar --}}
                    @if(count($selectedLedgerEntries) > 0)
                    <div class="mb-3 flex items-center gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-medium text-amber-700">{{ count($selectedLedgerEntries) }} item(s) selected</span>
                        <div class="flex-1"></div>
                        @if($ledgerConfirmAction === '')
                            <button wire:click="$set('ledgerConfirmAction', 'cancel')" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">Cancel Charges</button>
                            <button wire:click="$set('ledgerConfirmAction', 'revoke')" class="px-3 py-1.5 text-xs font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors">Revoke Payments</button>
                            <button wire:click="$set('selectedLedgerEntries', [])" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">Clear</button>
                        @elseif($ledgerConfirmAction === 'cancel')
                            <span class="text-xs font-medium text-red-700">Confirm cancel selected charges?</span>
                            <button wire:click="cancelSelectedEntries" class="px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">Yes, Cancel</button>
                            <button wire:click="$set('ledgerConfirmAction', '')" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">No</button>
                        @elseif($ledgerConfirmAction === 'revoke')
                            <span class="text-xs font-medium text-orange-700">Confirm revoke selected payments?</span>
                            <button wire:click="revokeSelectedPayments" class="px-3 py-1.5 text-xs font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors">Yes, Revoke</button>
                            <button wire:click="$set('ledgerConfirmAction', '')" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">No</button>
                        @endif
                    </div>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/80 sticky top-0">
                            <tr>
                                <th class="px-3 py-2.5 w-10">
                                    <input type="checkbox" wire:change="toggleSelectAllLedger" @if(count($selectedLedgerEntries) === count($viewingLedger) && count($viewingLedger) > 0) checked @endif class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                </th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Source</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Debit</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Credit</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-500 uppercase">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($viewingLedger as $entry)
                            <tr class="hover:bg-gray-50/50 {{ in_array($entry['id'], $selectedLedgerEntries) ? 'bg-amber-50/60' : '' }}">
                                <td class="px-3 py-2.5">
                                    <input type="checkbox" value="{{ $entry['id'] }}" wire:change="toggleLedgerEntry({{ $entry['id'] }})" @if(in_array($entry['id'], $selectedLedgerEntries)) checked @endif class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-600 whitespace-nowrap">{{ $entry['date'] }}</td>
                                <td class="px-4 py-2.5">
                                    @if($entry['entry_type'] === 'charge')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Charge</span>
                                    @elseif($entry['entry_type'] === 'payment')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Payment</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">{{ ucfirst($entry['entry_type']) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-600">{{ $entry['source_type'] }}: {{ $entry['source_code'] }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-700 max-w-xs truncate">{{ $entry['description'] }}</td>
                                <td class="px-4 py-2.5 text-xs text-right font-semibold {{ $entry['debit'] > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                    {{ $entry['debit'] > 0 ? '₱' . number_format($entry['debit'], 2) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-right font-semibold {{ $entry['credit'] > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                                    {{ $entry['credit'] > 0 ? '₱' . number_format($entry['credit'], 2) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-right font-bold text-gray-900">₱{{ number_format($entry['balance'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Final Balance</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-rose-600">₱{{ number_format(collect($viewingLedger)->sum('debit'), 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-emerald-600">₱{{ number_format(collect($viewingLedger)->sum('credit'), 2) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">₱{{ number_format($viewingPatientBalance, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>
            <div class="border-t border-gray-100 px-6 py-4 flex items-center justify-between bg-gray-50/50">
                <button wire:click="closeLedgerModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">
                    Close
                </button>
                <div class="flex items-center gap-2">
                    <button wire:click="printStatement" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print Statement
                    </button>
                    <button wire:click="openAddPayment({{ $viewingPatientId }})" class="px-3 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors">
                        Record Payment
                    </button>
                    <button wire:click="openManualChargeForPatient({{ $viewingPatientId }})" class="px-3 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-xl transition-colors">
                        Add Adjustment
                    </button>
                    <button wire:click="openDiscountModal({{ $viewingPatientId }})" class="px-3 py-2 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-xl transition-colors">
                        Grant Discount
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- PAYMENT MODAL --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" wire:click="closePaymentModal" wire:transition.opacity.duration.300ms></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden" wire:transition.scale.origin.bottom.duration.300ms>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50/50 backdrop-blur-sm">
                <h3 class="text-lg font-semibold text-gray-900">Record Payment</h3>
                <button wire:click="closePaymentModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit="addPayment" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Code</label>
                        <input wire:model="payment_code" type="text" readonly disabled class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm font-mono text-gray-600 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date</label>
                        <input wire:model="payment_date" type="date" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Amount <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><span class="text-gray-500 font-medium">₱</span></div>
                        <input wire:model="payment_amount" type="number" step="0.01" min="0.01" placeholder="0.00"
                               class="w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('payment_amount') border-red-300 bg-red-50 @enderror">
                    </div>
                    @error('payment_amount') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Method</label>
                    <select wire:model="payment_method" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="Cash">Cash</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Check">Check</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="HMO">HMO</option>
                        <option value="Health Insurance">Health Insurance</option>
                        <option value="PhilHealth">PhilHealth</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Budget Source
                        </span>
                    </label>
                    <select wire:model="payment_fund_source_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="">-- Select Fund Source --</option>
                        @foreach($fund_sources as $fs)
                        <option value="{{ $fs->id }}">{{ $fs->name }} ({{ $fs->code }})</option>
                        @endforeach
                    </select>
                </div>
                @if(in_array($payment_method, ['HMO', 'Health Insurance']))
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Insurance Provider
                        </span>
                    </label>
                    <select wire:model="payment_insurance_provider_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="">-- Select Insurance Provider --</option>
                        @foreach($insurance_providers as $ip)
                        <option value="{{ $ip->id }}">{{ $ip->name }} ({{ $ip->code }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Reference No.</label>
                    <input wire:model="payment_reference" type="text" placeholder="Check no, OR no, transaction ID…" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea wire:model="payment_notes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Optional..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closePaymentModal" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" wire:lock class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="addPayment">Record Payment</span>
                        <span wire:loading wire:target="addPayment">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- STATEMENT OF ACCOUNT PDF VIEWER MODAL --}}
    @if($showStatementModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" wire:click="closeStatementModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl z-10 overflow-hidden transform transition-all max-h-[92vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Statement of Account</h3>
                    <p class="text-xs text-gray-500">{{ $statementMode === 'summary' ? 'Summary View' : 'Detailed View' }} — {{ $viewingPatientName }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="(function(){ var f = document.getElementById('statement-iframe'); if (f && f.contentWindow) { try { f.contentWindow.focus(); f.contentWindow.print(); return; } catch(e){} } window.open('{{ $statementUrl }}', '_blank'); })()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </button>
                    <a href="/billing/statement/{{ $viewingPatientId }}/{{ $statementMode }}?format=pdf&download=1" target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download PDF
                    </a>
                    <a href="/billing/statement/{{ $viewingPatientId }}/{{ $statementMode }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl transition-colors shadow-sm"
                       title="Open in new window">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <button wire:click="closeStatementModal" class="ml-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 bg-gray-200">
                <iframe id="statement-iframe"
                        src="{{ $statementUrl }}"
                        class="w-full h-full border-0"
                        style="min-height: 600px;">
                </iframe>
            </div>
        </div>
    </div>
    @endif

    {{-- DISCOUNT MODAL --}}
    @if($showDiscountModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeDiscountModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-cyan-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Grant Discount / Credit Note</h3>
                <button wire:click="closeDiscountModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveDiscountEntry" class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Patient</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $viewingPatientName }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Discount Type</label>
                    <select wire:model="ledger_discount_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount (₱)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Discount Value</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500 font-medium">{{ $ledger_discount_type === 'percentage' ? '%' : '₱' }}</span>
                        <input wire:model="ledger_discount_value" type="number" step="0.01" min="0" placeholder="0.00"
                               class="w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 @error('ledger_discount_value') border-red-300 bg-red-50 @enderror">
                    </div>
                    @error('ledger_discount_value') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Computed Amount</label>
                    <p class="text-lg font-bold text-cyan-700">₱{{ number_format($ledger_discount_computed, 2) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Reason / Notes</label>
                    <textarea wire:model="ledger_discount_notes" rows="2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500" placeholder="Reason for discount..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeDiscountModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" class="px-5 py-2.5 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-xl shadow-sm transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveDiscountEntry">Apply Discount</span>
                        <span wire:loading wire:target="saveDiscountEntry">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
