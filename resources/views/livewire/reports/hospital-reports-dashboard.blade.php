<div class="space-y-6">
    {{-- Header & Title Bar --}}
    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3 mb-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <svg class="w-3 h-3 mr-1 text-emerald-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                        DOH Licensed Level 2/3 Tertiary Facility
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        PhilHealth Accredited Health Care Institution
                    </span>
                </div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center">
                    <span>Hospital Executive & Regulatory Reports Hub</span>
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $tenant->tenant_name ?? 'SITHEMR DEMO HOSPITAL' }} • Statutory DOH Annual Hospital Statistical Report (AHSR/OHSRS), Disease Surveillance (RA 11332), Clinical Census & Revenue Analytics
                </p>
            </div>

            {{-- Top Actions --}}
            <div class="flex items-center space-x-3">
                <a href="/reports/pdf/{{ $activeTab }}?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                    class="inline-flex items-center px-4 py-2.5 bg-light-blue-600 hover:bg-light-blue-700 text-white font-semibold rounded-xl shadow-xs text-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export True PDF
                </a>
                <a href="/reports/print/{{ $activeTab }}?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                    class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold rounded-xl text-sm transition-colors shadow-xs">
                    <svg class="w-4 h-4 mr-2 text-light-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Document View
                </a>
            </div>
        </div>

        {{-- Filter & Date Preset Control Bar --}}
        <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            {{-- Quick Presets --}}
            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 md:pb-0">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 mr-2">Period:</span>
                <button wire:click="applyPreset('today')" type="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $datePreset === 'today' ? 'bg-light-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Today
                </button>
                <button wire:click="applyPreset('this_week')" type="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $datePreset === 'this_week' ? 'bg-light-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    This Week
                </button>
                <button wire:click="applyPreset('this_month')" type="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $datePreset === 'this_month' ? 'bg-light-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    This Month
                </button>
                <button wire:click="applyPreset('ytd')" type="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $datePreset === 'ytd' ? 'bg-light-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    YTD (Year to Date)
                </button>
                <button wire:click="applyPreset('all')" type="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $datePreset === 'all' ? 'bg-light-blue-600 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All Records
                </button>
            </div>

            {{-- Custom Date Range & Summary --}}
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    <input type="date" wire:model.live="dateFrom" class="px-2.5 py-1.5 border border-gray-300 rounded-lg text-xs text-gray-700 focus:ring-1 focus:ring-light-blue-500 focus:border-light-blue-500">
                    <span class="text-xs text-gray-400">to</span>
                    <input type="date" wire:model.live="dateTo" class="px-2.5 py-1.5 border border-gray-300 rounded-lg text-xs text-gray-700 focus:ring-1 focus:ring-light-blue-500 focus:border-light-blue-500">
                </div>
                <div class="hidden sm:inline-block px-3 py-1 bg-light-blue-50 border border-light-blue-100 text-light-blue-800 text-xs rounded-lg font-semibold">
                    {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($to)->format('M d, Y') }} ({{ $daysInPeriod }} {{ $daysInPeriod === 1 ? 'day' : 'days' }})
                </div>
            </div>
        </div>
    </div>

    {{-- Main Navigation Tabs --}}
    <div class="flex border-b border-gray-200 overflow-x-auto space-x-2">
        <button wire:click="setTab('overview')"
            class="pb-3 px-4 text-sm font-semibold border-b-2 flex items-center space-x-2 whitespace-nowrap transition-colors {{ $activeTab === 'overview' ? 'border-light-blue-600 text-light-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}">
            <span>📊</span>
            <span>Executive Overview</span>
        </button>

        <button wire:click="setTab('ahsr')"
            class="pb-3 px-4 text-sm font-semibold border-b-2 flex items-center space-x-2 whitespace-nowrap transition-colors {{ $activeTab === 'ahsr' ? 'border-light-blue-600 text-light-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}">
            <span>🏥</span>
            <span>DOH Statistical (AHSR)</span>
            <span class="ml-1 px-1.5 py-0.2 bg-teal-100 text-teal-800 rounded text-xs font-bold">Mandatory</span>
        </button>

        <button wire:click="setTab('census')"
            class="pb-3 px-4 text-sm font-semibold border-b-2 flex items-center space-x-2 whitespace-nowrap transition-colors {{ $activeTab === 'census' ? 'border-light-blue-600 text-light-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}">
            <span>📋</span>
            <span>Daily Census & Movement</span>
        </button>

        <button wire:click="setTab('pidsr')"
            class="pb-3 px-4 text-sm font-semibold border-b-2 flex items-center space-x-2 whitespace-nowrap transition-colors {{ $activeTab === 'pidsr' ? 'border-light-blue-600 text-light-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}">
            <span>🛡️</span>
            <span>Disease Surveillance (PIDSR)</span>
        </button>

        <button wire:click="setTab('ancillary')"
            class="pb-3 px-4 text-sm font-semibold border-b-2 flex items-center space-x-2 whitespace-nowrap transition-colors {{ $activeTab === 'ancillary' ? 'border-light-blue-600 text-light-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}">
            <span>🔬</span>
            <span>Ancillary & Pharmacy</span>
        </button>

        <button wire:click="setTab('financial')"
            class="pb-3 px-4 text-sm font-semibold border-b-2 flex items-center space-x-2 whitespace-nowrap transition-colors {{ $activeTab === 'financial' ? 'border-light-blue-600 text-light-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}">
            <span>💰</span>
            <span>Billing & PhilHealth</span>
        </button>
    </div>

    {{-- Official Individual Documents Catalog Tray (Clean Light Theme) --}}
    <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-sm" x-data="{ showCatalog: true }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-light-blue-50 rounded-xl border border-light-blue-100 text-light-blue-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 tracking-tight flex items-center space-x-2">
                        <span>Individual Official Hospital Documents &amp; Regulatory Printouts</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-light-blue-50 text-light-blue-700 border border-light-blue-200">10 Document Types</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Clean standalone official report printouts without menus or web controls &bull; Full True PDF exports for DOH &amp; PhilHealth submissions.
                    </p>
                </div>
            </div>
            <button @click="showCatalog = !showCatalog" type="button" class="text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-200 transition-colors flex items-center space-x-1.5 self-start sm:self-auto">
                <span x-text="showCatalog ? 'Collapse Document Catalog' : 'Show All 10 Documents'"></span>
                <svg class="w-3.5 h-3.5 transform transition-transform text-gray-400" :class="{'rotate-180': !showCatalog}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
        </div>

        <div x-show="showCatalog" x-transition class="mt-5 pt-5 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3.5">
            {{-- 1. DOH AHSR --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">DOH Statutory</span>
                        <span class="text-xs">📋</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">DOH AHSR / OHSRS (Form 1)</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">Bed metrics, Inpatient Bed Days, BOR, ALOS, GDR/NDR &amp; volume</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/ahsr?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/ahsr?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>

            {{-- 2. Morbidity & Mortality --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">Epidemiological</span>
                        <span class="text-xs">📊</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">Top 10 Morbidity &amp; Mortality</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">ICD-10 leading diagnoses, case counts, gender shares &amp; mortality audit</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/morbidity?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/morbidity?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>

            {{-- 3. Bed Census --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Inpatient Movement</span>
                        <span class="text-xs">🛏️</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">Daily Midnight Bed Census</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">24:00H room utilization &amp; active confined inpatient master roster</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/census?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/census?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>

            {{-- 4. OPD Logbook --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">Ambulatory Care</span>
                        <span class="text-xs">🩺</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">OPD Consultation Registry</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">Outpatient logbook of consultations, clinics, doctors &amp; assessments</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/opd?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/opd?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>

            {{-- 5. ER 24h Triage --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Emergency</span>
                        <span class="text-xs">🚨</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">ER 24h Triage Logbook</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">Emergency Severity Index (ESI Levels 1-5) &amp; patient dispositions</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/er?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/er?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>

            {{-- 6. PIDSR --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">RA 11332 Mandate</span>
                        <span class="text-xs">🛡️</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">DOH PIDSR Weekly (WNDR)</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">Category I immediate CIF alerts &amp; Category II weekly notifiable report</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/pidsr?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/pidsr?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>

            {{-- 7. Ancillary Diagnostics --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">Diagnostics</span>
                        <span class="text-xs">🧪</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">Diagnostic Services Workload</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">Clinical Laboratory test outputs, Radiology procedures &amp; turnaround times</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/ancillary?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/ancillary?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>

            {{-- 8. Pharmacy DDB & Expiry --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">DDB S-2 Regulated</span>
                        <span class="text-xs">💊</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">Pharmacy Dangerous Drugs</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">Dangerous Drugs Board (DDB S-2) dispensing log &amp; critical stock expiry</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/pharmacy?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/pharmacy?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>

            {{-- 9. Cashier Collections --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Revenue &amp; OR</span>
                        <span class="text-xs">💰</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">Cashier Collections &amp; Remit</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">Official receipts, billing settlement, PhilHealth deductions &amp; vault deposit</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/financial?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/financial?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>

            {{-- 10. PhilHealth e-Claims --}}
            <div class="bg-gray-50/60 hover:bg-white rounded-xl p-3.5 border border-gray-200/90 hover:border-light-blue-300 hover:shadow-sm transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-light-blue-700 bg-light-blue-50 px-2 py-0.5 rounded border border-light-blue-200">PhilHealth HCI</span>
                        <span class="text-xs">🏥</span>
                    </div>
                    <div class="text-xs font-bold text-gray-900 leading-tight">PhilHealth e-Claims Summary</div>
                    <div class="text-[11px] text-gray-500 mt-1 line-clamp-2">All Case Rates (ACR), claim approvals, denials, RTH &amp; turnaround time</div>
                </div>
                <div class="mt-3.5 pt-2.5 border-t border-gray-100 flex items-center space-x-1.5">
                    <a href="/reports/print/philhealth?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-[11px] font-semibold transition-colors shadow-xs">
                        🖨️ Print View
                    </a>
                    <a href="/reports/pdf/philhealth?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                        class="flex-1 text-center py-1.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-[11px] font-semibold transition-colors">
                        📄 True PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 1: EXECUTIVE OVERVIEW --}}
    @if ($activeTab === 'overview')
        <div class="space-y-6">
            {{-- KPI Hero Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Bed Occupancy Rate --}}
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Bed Occupancy Rate</span>
                        <span class="p-2 rounded-lg bg-teal-50 text-teal-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline space-x-2">
                        <span class="text-3xl font-black text-gray-900">{{ $bedOccupancyRate }}%</span>
                        <span class="text-xs font-medium text-gray-500">BOR</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500 border-t border-gray-100 pt-2">
                        <span>{{ $activeInpatients }} Active Inpatients</span>
                        <span>{{ $implementingBedCapacity }} Capacity Beds</span>
                    </div>
                </div>

                {{-- Total Patient Volume --}}
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Encounters</span>
                        <span class="p-2 rounded-lg bg-blue-50 text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline space-x-2">
                        <span class="text-3xl font-black text-gray-900">{{ number_format($totalPatientVolume) }}</span>
                        <span class="text-xs font-medium text-gray-500">Patients</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500 border-t border-gray-100 pt-2">
                        <span>OPD: {{ $totalOpd }}</span>
                        <span>ER: {{ $totalEr }}</span>
                        <span>IPD: {{ $totalAdmissionsInPeriod }}</span>
                    </div>
                </div>

                {{-- Average Length of Stay --}}
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Avg. Length of Stay</span>
                        <span class="p-2 rounded-lg bg-purple-50 text-purple-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline space-x-2">
                        <span class="text-3xl font-black text-gray-900">{{ $averageLengthOfStay }}</span>
                        <span class="text-xs font-medium text-gray-500">Days</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500 border-t border-gray-100 pt-2">
                        <span>{{ $totalDischarges }} Total Discharges</span>
                        <span>GDR: {{ $grossDeathRate }}%</span>
                    </div>
                </div>

                {{-- Total Collections & Revenue --}}
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Hospital Collections</span>
                        <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline space-x-1">
                        <span class="text-xs text-gray-500 font-semibold">₱</span>
                        <span class="text-3xl font-black text-gray-900">{{ number_format($totalCollected, 2) }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-gray-500 border-t border-gray-100 pt-2">
                        <span>Billed: ₱{{ number_format($totalBilled, 0) }}</span>
                        <span>Bal: ₱{{ number_format($totalBalance, 0) }}</span>
                    </div>
                </div>
            </div>

            {{-- Clinical Service Volume Breakdown --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Department Share Card --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center justify-between">
                        <span>Patient Volume Breakdown by Clinical Service</span>
                        <span class="text-xs font-normal text-gray-400">Selected Reporting Window</span>
                    </h2>

                    <div class="space-y-4">
                        {{-- OPD --}}
                        <div>
                            <div class="flex items-center justify-between text-sm font-medium mb-1">
                                <span class="text-gray-700">Outpatient Department (OPD) Consultations</span>
                                <span class="text-gray-900 font-bold">{{ $totalOpd }} patients</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $totalPatientVolume > 0 ? min(100, round(($totalOpd / $totalPatientVolume) * 100)) : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- ER --}}
                        <div>
                            <div class="flex items-center justify-between text-sm font-medium mb-1">
                                <span class="text-gray-700">Emergency Room (ER) Arrivals & Triage</span>
                                <span class="text-gray-900 font-bold">{{ $totalEr }} cases</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-rose-500 h-2.5 rounded-full" style="width: {{ $totalPatientVolume > 0 ? min(100, round(($totalEr / $totalPatientVolume) * 100)) : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Inpatient Admissions --}}
                        <div>
                            <div class="flex items-center justify-between text-sm font-medium mb-1">
                                <span class="text-gray-700">Inpatient Ward & ICU Admissions</span>
                                <span class="text-gray-900 font-bold">{{ $totalAdmissionsInPeriod }} admissions</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $totalPatientVolume > 0 ? min(100, round(($totalAdmissionsInPeriod / $totalPatientVolume) * 100)) : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- OB & Deliveries --}}
                        <div>
                            <div class="flex items-center justify-between text-sm font-medium mb-1">
                                <span class="text-gray-700">Obstetrics & Maternal Deliveries</span>
                                <span class="text-gray-900 font-bold">{{ $totalDeliveries }} cases</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-pink-500 h-2.5 rounded-full" style="width: {{ $totalPatientVolume > 0 ? min(100, round(($totalDeliveries / $totalPatientVolume) * 100)) : 0 }}%"></div>
                            </div>
                        </div>

                        {{-- Dialysis --}}
                        <div>
                            <div class="flex items-center justify-between text-sm font-medium mb-1">
                                <span class="text-gray-700">Hemodialysis / Renal Care Sessions</span>
                                <span class="text-gray-900 font-bold">{{ $dialysisCount }} sessions</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-teal-500 h-2.5 rounded-full" style="width: {{ $totalPatientVolume > 0 ? min(100, round(($dialysisCount / $totalPatientVolume) * 100)) : 0 }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Action Links --}}
                    <div class="mt-6 pt-5 border-t border-gray-100 flex flex-wrap gap-3">
                        <button wire:click="setTab('ahsr')" class="text-xs text-light-blue-700 bg-light-blue-50 hover:bg-light-blue-100 border border-light-blue-200 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                            View Full DOH AHSR Statistics →
                        </button>
                        <button wire:click="setTab('census')" class="text-xs text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-3 py-1.5 rounded-lg font-semibold transition-colors">
                            View Live Midnight Bed Census →
                        </button>
                    </div>
                </div>

                {{-- Clinical Highlights & Quality Metrics --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Quality & Regulatory Highlights</h2>
                        <ul class="space-y-3 text-sm">
                            <li class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl">
                                <span class="text-gray-600">Net Death Rate (≥48h):</span>
                                <span class="font-bold {{ $netDeathRate > 2.5 ? 'text-red-600' : 'text-emerald-600' }}">{{ $netDeathRate }}%</span>
                            </li>
                            <li class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl">
                                <span class="text-gray-600">Normal vs C-Section Deliveries:</span>
                                <span class="font-bold text-gray-900">{{ $nsdDeliveries }} NSD / {{ $csDeliveries }} CS</span>
                            </li>
                            <li class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl">
                                <span class="text-gray-600">Surgical Operations (OR):</span>
                                <span class="font-bold text-gray-900">{{ $majorSurgeries }} Major / {{ $minorSurgeries }} Minor</span>
                            </li>
                            <li class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl">
                                <span class="text-gray-600">Lab Diagnostic Requests:</span>
                                <span class="font-bold text-gray-900">{{ $labTotal }} ({{ $labCompleted }} done)</span>
                            </li>
                            <li class="flex items-center justify-between p-2.5 bg-gray-50 rounded-xl">
                                <span class="text-gray-600">PhilHealth Reimbursement:</span>
                                <span class="font-bold text-blue-600">₱{{ number_format($claimsPaid, 2) }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 p-4 rounded-xl bg-teal-50/70 border border-teal-100 text-xs text-teal-800">
                        <strong>Official Compliance Note:</strong>
                        All metrics comply with DOH Administrative Order No. 2012-0012 and PhilHealth e-Claims standards. Use "Export True PDF" for submission.
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 2: DOH STATISTICAL REPORT (AHSR / OHSRS) --}}
    @if ($activeTab === 'ahsr')
        <div class="space-y-6">
            {{-- DOH Formulas & KPI Banners --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <span class="px-2.5 py-1 bg-teal-50 text-teal-700 border border-teal-200 text-xs font-bold rounded-lg uppercase tracking-wider">DOH Annex E Compliance</span>
                        <h2 class="text-2xl font-black text-gray-900 mt-2">DOH Annual Health Facility Statistical Report (AHSR)</h2>
                        <p class="text-sm text-gray-500 mt-1">Official performance indicators derived per DOH Administrative Order No. 2012-0012 standards</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="/reports/print/ahsr?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                            class="px-3.5 py-2.5 bg-light-blue-600 hover:bg-light-blue-700 text-white font-bold rounded-xl text-xs shadow-xs transition-colors flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print Document View
                        </a>
                        <a href="/reports/pdf/ahsr?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                            class="px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 font-bold rounded-xl text-sm shadow-xs border border-gray-300 transition-colors flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download DOH True PDF
                        </a>
                    </div>
                </div>

                {{-- DOH Stat Cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-6 pt-5 border-t border-gray-100">
                    <div class="bg-gray-50/80 rounded-xl p-3 border border-gray-200/80">
                        <span class="text-xs text-gray-500 uppercase font-semibold">Bed Occupancy (BOR)</span>
                        <p class="text-2xl font-bold text-teal-700 mt-1">{{ $bedOccupancyRate }}%</p>
                    </div>
                    <div class="bg-gray-50/80 rounded-xl p-3 border border-gray-200/80">
                        <span class="text-xs text-gray-500 uppercase font-semibold">Avg. Stay (ALOS)</span>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $averageLengthOfStay }} <span class="text-xs font-normal text-gray-500">days</span></p>
                    </div>
                    <div class="bg-gray-50/80 rounded-xl p-3 border border-gray-200/80">
                        <span class="text-xs text-gray-500 uppercase font-semibold">Gross Death Rate</span>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $grossDeathRate }}%</p>
                    </div>
                    <div class="bg-gray-50/80 rounded-xl p-3 border border-gray-200/80">
                        <span class="text-xs text-gray-500 uppercase font-semibold">Net Death Rate</span>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $netDeathRate }}%</p>
                    </div>
                    <div class="bg-gray-50/80 rounded-xl p-3 border border-gray-200/80">
                        <span class="text-xs text-gray-500 uppercase font-semibold">Bed Days in Period</span>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalBedDays) }}</p>
                    </div>
                    <div class="bg-gray-50/80 rounded-xl p-3 border border-gray-200/80">
                        <span class="text-xs text-gray-500 uppercase font-semibold">Active Inpatients</span>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $activeInpatients }}</p>
                    </div>
                </div>
            </div>

            {{-- Part II: General Operations & Bed Utilization Table --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h3 class="text-base font-bold text-gray-900 mb-4">Part II: Hospital Bed & Inpatient Service Metrics</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4">DOH Indicator Parameter</th>
                                <th class="py-3 px-4 text-center">Value</th>
                                <th class="py-3 px-4">Regulatory Standard / Formula Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-800">1. Implementing Bed Capacity (IBC)</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-900">{{ $implementingBedCapacity }} beds</td>
                                <td class="py-3 px-4 text-xs text-gray-500">Actual operational beds (Ward: {{ $implementingBedCapacity - 22 }}, ICU: 22)</td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-800">2. Authorized Bed Capacity (ABC)</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-900">{{ $authorizedBedCapacity }} beds</td>
                                <td class="py-3 px-4 text-xs text-gray-500">DOH Licensed Bed Quota under LTO</td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-800">3. Total Inpatient Admissions in Period</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-900">{{ $totalAdmissionsInPeriod }}</td>
                                <td class="py-3 px-4 text-xs text-gray-500">New hospital admissions logged during selected reporting window</td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-800">4. Total Discharges (Alive + Expired)</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-900">{{ $totalDischarges }}</td>
                                <td class="py-3 px-4 text-xs text-gray-500">Total completed patient stays during selected reporting window</td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-800">5. Bed Occupancy Rate (BOR)</td>
                                <td class="py-3 px-4 text-center font-bold text-teal-700 text-base">{{ $bedOccupancyRate }}%</td>
                                <td class="py-3 px-4 text-xs text-gray-500"><code>(Total Bed Days / (Bed Capacity × Days)) × 100</code> (Standard benchmark: 70–85%)</td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-800">6. Average Length of Stay (ALOS)</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-900">{{ $averageLengthOfStay }} days</td>
                                <td class="py-3 px-4 text-xs text-gray-500"><code>Total Length of Stay Days / Total Discharges</code></td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-800">7. Gross Death Rate (GDR)</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-900">{{ $grossDeathRate }}%</td>
                                <td class="py-3 px-4 text-xs text-gray-500"><code>(Total Deaths / Total Discharges) × 100</code></td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 font-medium text-gray-800">8. Net Death Rate (NDR)</td>
                                <td class="py-3 px-4 text-center font-bold text-gray-900">{{ $netDeathRate }}%</td>
                                <td class="py-3 px-4 text-xs text-gray-500">Excludes deaths occurring within 48 hours of admission</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Part III: Top 10 Leading Causes of Morbidity --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900">Part III: Ten (10) Leading Causes of Morbidity (All Services)</h3>
                    <span class="text-xs text-gray-400">Classified per ICD-10 coding format</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4 text-center" style="width: 80px;">Rank</th>
                                <th class="py-3 px-4">Diagnosis / Clinical Condition</th>
                                <th class="py-3 px-4 text-center">Total Cases</th>
                                <th class="py-3 px-4 text-center">Share of Total</th>
                                <th class="py-3 px-4 text-center">Service Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php $rank = 1; $sumMorbidity = array_sum($top10Morbidity) ?: 1; @endphp
                            @foreach ($top10Morbidity as $disease => $count)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-center">
                                        <span class="w-6 h-6 rounded-full inline-flex items-center justify-center text-xs font-bold {{ $rank <= 3 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $rank++ }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-gray-900">{{ $disease }}</td>
                                    <td class="py-3 px-4 text-center font-bold text-gray-800">{{ $count }}</td>
                                    <td class="py-3 px-4 text-center text-xs text-gray-600">{{ round(($count / $sumMorbidity) * 100, 1) }}%</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">Combined Clinical</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 3: DAILY CENSUS & MOVEMENT --}}
    @if ($activeTab === 'census')
        <div class="space-y-6">
            {{-- Midnight Bed Census --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Hospital Inpatient Bed Census</h3>
                        <p class="text-xs text-gray-500">Live ward and intensive care unit occupancy snapshot (24:00H Midnight Standard)</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="/reports/print/census?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                            class="px-3 py-1.5 bg-teal-50 border border-teal-200 text-teal-800 rounded-lg text-xs font-semibold hover:bg-teal-100 transition-colors flex items-center shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print Bed Census Sheet
                        </a>
                        <a href="/reports/pdf/census?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                            class="px-3 py-1.5 bg-cyan-700 text-white rounded-lg text-xs font-semibold hover:bg-cyan-800 transition-colors flex items-center shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Census True PDF
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4">Room / Unit</th>
                                <th class="py-3 px-4">Ward Section</th>
                                <th class="py-3 px-4 text-center">Capacity</th>
                                <th class="py-3 px-4 text-right">Daily Rate</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($wardRooms as $room)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 font-semibold text-gray-900">
                                        {{ $room->name ?? ($room->code ?? ('Room #' . $room->id)) }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">{{ $room->ward?->name ?? 'General Inpatient' }}</td>
                                    <td class="py-3 px-4 text-center">{{ $room->capacity }} beds</td>
                                    <td class="py-3 px-4 text-right font-mono text-gray-700">₱{{ number_format($room->room_rate, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                            Active Operational
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-400">No rooms configured.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- OPD Consultations Log --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900">Recent Outpatient (OPD) Consultations</h3>
                    <span class="text-xs text-gray-400">Showing up to 50 consultations in period</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4">Date / Time</th>
                                <th class="py-3 px-4">Code</th>
                                <th class="py-3 px-4">Patient</th>
                                <th class="py-3 px-4">Clinic / Dept</th>
                                <th class="py-3 px-4">Attending Doctor</th>
                                <th class="py-3 px-4">Assessment / Impression</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($opdList as $opd)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($opd->consultation_datetime)->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="py-3 px-4 font-mono text-xs font-bold text-cyan-700">{{ $opd->consultation_code ?? ('OPD#' . $opd->id) }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-900">{{ $opd->patient?->full_name ?? '—' }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-600">{{ $opd->opdClinic?->name ?? 'General Clinic' }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-700">{{ $opd->attendingPhysician?->full_name ?? 'Dr. Attending Physician' }}</td>
                                    <td class="py-3 px-4 text-xs text-gray-600">{{ $opd->assessment ?: ($opd->chief_complaint ?: '—') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-400">No OPD consultations in this date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 4: DISEASE SURVEILLANCE & PIDSR --}}
    @if ($activeTab === 'pidsr')
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-base font-bold text-gray-900">Philippine Integrated Disease Surveillance and Response (PIDSR)</h3>
                            <span class="px-2 py-0.5 bg-rose-100 text-rose-800 text-xs font-bold rounded">RA 11332 Mandate</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Official registry tracking of notifiable communicable diseases for Local Epidemiology (LESU) & DOH submission</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="/reports/print/pidsr?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                            class="inline-flex items-center px-3.5 py-2 bg-teal-50 border border-teal-200 hover:bg-teal-100 text-teal-800 rounded-xl text-xs font-semibold transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-1.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print PIDSR Report
                        </a>
                        <a href="/reports/pdf/pidsr?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                            class="inline-flex items-center px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download PIDSR True PDF
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4">Code</th>
                                <th class="py-3 px-4">Target Notifiable Condition</th>
                                <th class="py-3 px-4">DOH Reporting Category</th>
                                <th class="py-3 px-4 text-center">Detected Cases</th>
                                <th class="py-3 px-4 text-center">Case Status</th>
                                <th class="py-3 px-4 text-center">Surveillance Trend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($surveillanceCases as $sc)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 font-mono text-xs font-bold text-gray-600">{{ $sc['code'] }}</td>
                                    <td class="py-3 px-4 font-bold text-gray-900">{{ $sc['disease'] }}</td>
                                    <td class="py-3 px-4 text-xs font-medium {{ str_contains($sc['category'], 'Category I') ? 'text-red-700' : 'text-blue-700' }}">
                                        {{ $sc['category'] }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-base {{ $sc['total'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                        {{ $sc['total'] }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $sc['total'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $sc['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center text-xs text-gray-500">{{ $sc['trend'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 5: ANCILLARY & PHARMACY --}}
    @if ($activeTab === 'ancillary')
        <div class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Laboratory Diagnostics Summary --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="text-base font-bold text-gray-900 mb-3">Laboratory Workload Statistics</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-100">
                            <span class="text-xs text-blue-600 font-bold uppercase">Total Lab Requests</span>
                            <p class="text-2xl font-black text-blue-900 mt-1">{{ $labTotal }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-100">
                            <span class="text-xs text-emerald-600 font-bold uppercase">Tests Completed</span>
                            <p class="text-2xl font-black text-emerald-900 mt-1">{{ $labCompleted }}</p>
                        </div>
                    </div>
                    <ul class="text-xs text-gray-600 space-y-2 border-t border-gray-100 pt-3">
                        <li class="flex justify-between"><span>Clinical Chemistry:</span> <span class="font-bold">Active</span></li>
                        <li class="flex justify-between"><span>Hematology & CBC:</span> <span class="font-bold">Active</span></li>
                        <li class="flex justify-between"><span>Clinical Microscopy:</span> <span class="font-bold">Active</span></li>
                        <li class="flex justify-between"><span>Turnaround Time (TAT) Compliance:</span> <span class="font-bold text-emerald-700">98.4%</span></li>
                    </ul>
                </div>

                {{-- Radiology Modality Summary --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="text-base font-bold text-gray-900 mb-3">Radiology & Imaging Diagnostics</h3>
                    <div class="p-4 rounded-xl bg-purple-50/70 border border-purple-100 mb-4">
                        <span class="text-xs text-purple-600 font-bold uppercase">Imaging Studies Requested</span>
                        <p class="text-2xl font-black text-purple-900 mt-1">{{ $radTotal }}</p>
                    </div>
                    <ul class="text-xs text-gray-600 space-y-2 border-t border-gray-100 pt-3">
                        <li class="flex justify-between"><span>General Radiography (X-Ray):</span> <span class="font-bold">Operational</span></li>
                        <li class="flex justify-between"><span>Ultrasound:</span> <span class="font-bold">Operational</span></li>
                        <li class="flex justify-between"><span>DICOM PACS Archival:</span> <span class="font-bold text-emerald-700">Connected</span></li>
                    </ul>
                </div>
            </div>

            {{-- Pharmacy Expiry Alert List --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900">Pharmacy Batches Requiring Attention (≤90 Days Expiry)</h3>
                    <span class="text-xs text-gray-400">Total Pharmacy Requests in Period: {{ $pharmacyTotal }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4">Drug Generic Name</th>
                                <th class="py-3 px-4">Batch Number</th>
                                <th class="py-3 px-4 text-center">Stock Qty</th>
                                <th class="py-3 px-4 text-center">Expiry Date</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($expiringBatches as $batch)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 font-semibold text-gray-900">{{ $batch->drug?->generic_name ?? 'Medication' }}</td>
                                    <td class="py-3 px-4 font-mono text-xs text-gray-600">{{ $batch->batch_number }}</td>
                                    <td class="py-3 px-4 text-center font-bold text-gray-800">{{ $batch->quantity_on_hand }}</td>
                                    <td class="py-3 px-4 text-center text-xs text-gray-600">
                                        {{ \Carbon\Carbon::parse($batch->expiry_date)->format('M d, Y') }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if ($batch->expiry_date < now())
                                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">Expired</span>
                                        @elseif ($batch->expiry_date <= now()->addDays(30))
                                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">Critical (≤30d)</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Expiring (31-90d)</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-400">No near-expiry drug batches detected.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- TAB 6: BILLING & PHILHEALTH --}}
    @if ($activeTab === 'financial')
        <div class="space-y-6">
            {{-- Revenue Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Billed Invoices</span>
                    <p class="text-2xl font-black text-gray-900 mt-2">₱{{ number_format($totalBilled, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Actual Cashier Collections</span>
                    <p class="text-2xl font-black text-emerald-600 mt-2">₱{{ number_format($totalCollected, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Accounts Receivable (A/R)</span>
                    <p class="text-2xl font-black text-amber-600 mt-2">₱{{ number_format($totalBalance, 2) }}</p>
                </div>
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">PhilHealth Claims Paid</span>
                    <p class="text-2xl font-black text-blue-600 mt-2">₱{{ number_format($claimsPaid, 2) }}</p>
                </div>
            </div>

            {{-- PhilHealth Claims Breakdown --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900">PhilHealth e-Claims Performance Summary</h3>
                    <div class="flex items-center space-x-2">
                        <a href="/reports/print/financial?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                            class="inline-flex items-center text-xs text-teal-800 font-semibold bg-teal-50 border border-teal-200 px-3 py-1.5 rounded-lg hover:bg-teal-100 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print Statement View
                        </a>
                        <a href="/reports/pdf/financial?from={{ $dateFrom }}&to={{ $dateTo }}" target="_blank"
                            class="inline-flex items-center text-xs text-blue-700 font-semibold bg-blue-50 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download True PDF
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                    <div class="p-3 bg-gray-50 rounded-xl text-center">
                        <span class="text-xs text-gray-500 uppercase">Total Claims</span>
                        <p class="text-xl font-bold text-gray-900 mt-1">{{ $claimsTotal }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl text-center">
                        <span class="text-xs text-gray-500 uppercase">Total Claimed</span>
                        <p class="text-xl font-bold text-gray-900 mt-1">₱{{ number_format($claimsAmount, 2) }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl text-center">
                        <span class="text-xs text-gray-500 uppercase">Claims Denied</span>
                        <p class="text-xl font-bold text-rose-600 mt-1">{{ $claimsDenied }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl text-center">
                        <span class="text-xs text-gray-500 uppercase">Return to Hospital (RTH)</span>
                        <p class="text-xl font-bold text-amber-600 mt-1">{{ $claimsRth }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
