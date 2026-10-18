<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 tracking-tight">YAKAP Dashboard</h2>
        <p class="text-sm text-gray-500 mt-1">Overview of the PhilHealth YAKAP program.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Empaneled</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalEmpaneled }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">FPE Completed</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $fpeCompleted }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            @if($fpePending > 0)
                <p class="text-xs text-amber-600 mt-2">{{ $fpePending }} pending</p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Screenings Done</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $screeningsCompleted }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
            </div>
            @if($screeningsOverdue > 0)
                <p class="text-xs text-red-600 mt-2">{{ $screeningsOverdue }} overdue</p>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Capitation Paid</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">₱{{ number_format($totalCapitationPaid, 2) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            @if($totalCapitationPending > 0)
                <p class="text-xs text-amber-600 mt-2">₱{{ number_format($totalCapitationPending, 2) }} pending</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Risk Classification Breakdown</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">High Risk</span>
                    <span class="text-sm font-semibold text-red-600">{{ $highRisk }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    @php $total = max($highRisk + $moderateRisk + $lowRisk, 1); @endphp
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($highRisk / $total) * 100 }}%"></div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Moderate Risk</span>
                    <span class="text-sm font-semibold text-amber-600">{{ $moderateRisk }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ ($moderateRisk / $total) * 100 }}%"></div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Low Risk</span>
                    <span class="text-sm font-semibold text-green-600">{{ $lowRisk }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($lowRisk / $total) * 100 }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Links</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="/yakap/empanelments" wire:navigate class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Empanelments</p>
                        <p class="text-xs text-gray-500">Manage enrollment</p>
                    </div>
                </a>
                <a href="/yakap/fpe-records" wire:navigate class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">FPE Records</p>
                        <p class="text-xs text-gray-500">First Patient Encounter</p>
                    </div>
                </a>
                <a href="/yakap/screenings" wire:navigate class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Screenings</p>
                        <p class="text-xs text-gray-500">Cancer screenings</p>
                    </div>
                </a>
                <a href="/yakap/capitation" wire:navigate class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Capitation</p>
                        <p class="text-xs text-gray-500">Payment tracking</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
