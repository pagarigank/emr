<div class="p-6 max-w-7xl mx-auto space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-light-blue-50 text-light-blue-700 border border-light-blue-200">
                    PhilHealth Circular No. 2024-0033 &amp; 2025-0020
                </span>
                <span class="text-xs text-gray-500 font-medium">OECB-FBE Configuration &amp; Mapping</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight mt-1">
                OECB Packages &amp; Hospital Catalog Mapping
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Manage PhilHealth Core Presentation packages and link internal hospital services/meds to official EECL items for 1-click ER auto-billing.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="/philhealth/eecl-items" wire:navigate
               class="inline-flex items-center px-4 py-2 border border-gray-200 text-xs font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-2xs">
                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>EECL Master Items</span>
            </a>
            <a href="/er/area" wire:navigate
               class="inline-flex items-center px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Go to ER Area</span>
            </a>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="flex border-b border-gray-200 overflow-x-auto no-scrollbar scrollbar-none [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden space-x-3" style="scrollbar-width: none; -ms-overflow-style: none;">
        <button wire:click="setTab('packages')" type="button"
            class="flex-shrink-0 -mb-px pb-3 px-4 text-xs sm:text-sm font-semibold border-b-2 flex items-center space-x-2 whitespace-nowrap transition-colors {{ $activeTab === 'packages' ? 'border-light-blue-600 text-light-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}">
            <span>📦</span>
            <span>Core Presentations &amp; Fee Packages (14)</span>
        </button>

        <button wire:click="setTab('mapping')" type="button"
            class="flex-shrink-0 -mb-px pb-3 px-4 text-xs sm:text-sm font-semibold border-b-2 flex items-center space-x-2 whitespace-nowrap transition-colors {{ $activeTab === 'mapping' ? 'border-light-blue-600 text-light-blue-700' : 'border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}">
            <span>⚡</span>
            <span>Hospital &harr; EECL 1-Click Mapping Tool</span>
            <span class="ml-1 px-1.5 py-0.5 bg-teal-100 text-teal-800 rounded text-[10px] font-bold">Auto-Sync</span>
        </button>
    </div>

    {{-- TAB 1: Core Presentations & Fee Packages --}}
    @if($activeTab === 'packages')
        <div class="bg-white rounded-2xl border border-gray-200/90 shadow-xs overflow-hidden space-y-4 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                <div>
                    <h2 class="text-base font-bold text-gray-900 tracking-tight">
                        PhilHealth Outpatient Emergency Care Benefit Packages
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Annex A Package Codes and Fixed Fees assigned to the 14 Emergency Core Presentations.
                    </p>
                </div>
                <div class="relative w-full sm:w-64">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search package or code..."
                        class="w-full pl-8 pr-3 py-1.5 border border-gray-200 rounded-xl text-xs bg-gray-50/50 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-[11px] uppercase font-bold text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Core Emergency Presentation</th>
                            <th class="py-3 px-4">Package Code</th>
                            <th class="py-3 px-4 text-right">Fixed Fee Rate</th>
                            <th class="py-3 px-4">Description / Indication</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse($feeSchedules as $fs)
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-light-blue-800">
                                    {{ $fs->corePresentation?->code ?? '—' }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-gray-900">
                                    {{ $fs->corePresentation?->name ?? '—' }}
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold text-gray-700">
                                    <span class="px-2 py-0.5 bg-gray-100 rounded text-xs border border-gray-200">
                                        {{ $fs->package_code }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-teal-700 text-sm">
                                    ₱{{ number_format((float) $fs->fixed_fee, 2) }}
                                </td>
                                <td class="py-3 px-4 text-gray-500 max-w-xs truncate">
                                    {{ $fs->description ?? 'Standard fixed fee package per Circular 2024-0033' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $fs->is_active ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                        {{ $fs->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <button wire:click="openEditPackage({{ $fs->id }})" type="button"
                                        class="px-2.5 py-1 text-xs font-semibold text-light-blue-700 bg-light-blue-50 hover:bg-light-blue-100 border border-light-blue-200 rounded-lg transition-colors">
                                        Edit Fee
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    No fee schedule packages found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- TAB 2: Hospital Catalog-to-EECL 1-Click Mapping Tool --}}
    @if($activeTab === 'mapping')
        <div class="space-y-4">
            {{-- Catalog Selector Pills & Stats --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-xs space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 tracking-tight flex items-center space-x-2">
                            <span>Hospital Catalog &harr; PhilHealth EECL Mapper</span>
                            <span class="px-2 py-0.5 text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200 rounded-full">Automates Annex D</span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Link your hospital's internal item codes to official EECL items. When doctors order in ER, the system will automatically map and calculate PhilHealth claims without re-entry!
                        </p>
                    </div>
                    <div class="flex items-center space-x-2 self-start lg:self-auto">
                        <button wire:click="autoMatchCatalog" type="button"
                            class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Smart Auto-Match by Name</span>
                        </button>
                    </div>
                </div>

                {{-- Catalog Switcher --}}
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 pt-2 border-t border-gray-100">
                    <button wire:click="$set('mappingCatalog', 'drugs')" type="button"
                        class="p-3 rounded-xl border text-left transition-all {{ $mappingCatalog === 'drugs' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-500/20 shadow-xs' : 'bg-gray-50/70 border-gray-200 hover:bg-white' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900">💊 Medicines</span>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">{{ $mappingStats['drugs_mapped'] }}/{{ $mappingStats['drugs_total'] }}</span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Hospital Drugs</p>
                    </button>

                    <button wire:click="$set('mappingCatalog', 'lab_tests')" type="button"
                        class="p-3 rounded-xl border text-left transition-all {{ $mappingCatalog === 'lab_tests' ? 'bg-indigo-50 border-indigo-300 ring-2 ring-indigo-500/20 shadow-xs' : 'bg-gray-50/70 border-gray-200 hover:bg-white' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900">🔬 Lab Tests</span>
                            <span class="text-[10px] font-bold text-indigo-700 bg-indigo-100 px-1.5 py-0.5 rounded">{{ $mappingStats['labs_mapped'] }}/{{ $mappingStats['labs_total'] }}</span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Diagnostic Services</p>
                    </button>

                    <button wire:click="$set('mappingCatalog', 'radiology')" type="button"
                        class="p-3 rounded-xl border text-left transition-all {{ $mappingCatalog === 'radiology' ? 'bg-purple-50 border-purple-300 ring-2 ring-purple-500/20 shadow-xs' : 'bg-gray-50/70 border-gray-200 hover:bg-white' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900">🩻 Radiology</span>
                            <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-1.5 py-0.5 rounded">{{ $mappingStats['radiology_mapped'] }}/{{ $mappingStats['radiology_total'] }}</span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">X-Rays &amp; Imaging</p>
                    </button>

                    <button wire:click="$set('mappingCatalog', 'supplies')" type="button"
                        class="p-3 rounded-xl border text-left transition-all {{ $mappingCatalog === 'supplies' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-500/20 shadow-xs' : 'bg-gray-50/70 border-gray-200 hover:bg-white' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900">📦 CSR Supplies</span>
                            <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">{{ $mappingStats['supplies_mapped'] }}/{{ $mappingStats['supplies_total'] }}</span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Disposables &amp; Lines</p>
                    </button>

                    <button wire:click="$set('mappingCatalog', 'procedures')" type="button"
                        class="p-3 rounded-xl border text-left transition-all {{ $mappingCatalog === 'procedures' ? 'bg-sky-50 border-sky-300 ring-2 ring-sky-500/20 shadow-xs' : 'bg-gray-50/70 border-gray-200 hover:bg-white' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900">🩺 Procedures</span>
                            <span class="text-[10px] font-bold text-sky-700 bg-sky-100 px-1.5 py-0.5 rounded">{{ $mappingStats['procedures_mapped'] }}/{{ $mappingStats['procedures_total'] }}</span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">ER Interventions</p>
                    </button>
                </div>
            </div>

            {{-- Mapping Table & Controls --}}
            <div class="bg-white rounded-2xl border border-gray-200/90 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-sm">
                        <input type="text" wire:model.live.debounce.300ms="mappingSearch" placeholder="Search item in this catalog..."
                            class="w-full pl-8 pr-3 py-1.5 border border-gray-200 rounded-xl text-xs bg-gray-50/50 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="flex items-center space-x-2">
                        <select wire:model.live="mappingFilter" class="px-3 py-1.5 border border-gray-200 rounded-xl text-xs bg-white text-gray-700 focus:outline-none">
                            <option value="all">All Items</option>
                            <option value="mapped">Mapped Only</option>
                            <option value="unmapped">Unmapped Only</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-[11px] uppercase font-bold text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-4 w-1/3">Hospital Item Name &amp; Details</th>
                                <th class="py-3 px-4 w-1/6">Internal Code</th>
                                <th class="py-3 px-4 w-1/2">Mapped PhilHealth EECL Item (Fixed Fee)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($catalogItems as $item)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-gray-900">
                                            @if($mappingCatalog === 'drugs')
                                                {{ $item->generic_name }}
                                                @if($item->brand_name)
                                                    <span class="text-xs font-normal text-gray-500">({{ $item->brand_name }})</span>
                                                @endif
                                                <span class="block text-[11px] font-normal text-gray-400">{{ $item->drug_strength }} &bull; {{ $item->dosage_form }}</span>
                                            @else
                                                {{ $item->name }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-gray-600">
                                        {{ $item->code ?? $item->drug_code ?? '—' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <select
                                                wire:change="updateItemMapping('{{ $mappingCatalog }}', {{ $item->id }}, $event.target.value)"
                                                class="flex-1 px-3 py-1.5 border rounded-xl text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 {{ $item->eecl_item_id ? 'bg-teal-50/50 border-teal-200 text-teal-900' : 'bg-white border-gray-200 text-gray-600' }}">
                                                <option value="">— Not Mapped (No PhilHealth EECL Link) —</option>
                                                @foreach($availableEeclOptions as $opt)
                                                    <option value="{{ $opt->id }}" {{ $item->eecl_item_id == $opt->id ? 'selected' : '' }}>
                                                        [{{ $opt->item_code }}] {{ $opt->item_name }} &bull; ₱{{ number_format((float) $opt->fixed_fee, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($item->eecl_item_id)
                                                <button wire:click="updateItemMapping('{{ $mappingCatalog }}', {{ $item->id }}, '')" type="button"
                                                    class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors" title="Unlink mapping">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-400">
                                        No items found in this catalog with current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($catalogItems instanceof \Illuminate\Pagination\LengthAwarePaginator && $catalogItems->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $catalogItems->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Fee Schedule Edit Modal --}}
    @if($showPackageModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 text-center">
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs transition-opacity" wire:click="closePackageModal"></div>
                <div class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-gray-200 p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="text-base font-bold text-gray-900">
                            Edit OECB Fee Schedule
                        </h3>
                        <button wire:click="closePackageModal" type="button" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="savePackage" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Core Emergency Presentation</label>
                            <input type="text" value="{{ $presentation_name }}" readonly
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-900">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Package Code <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="package_code"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-mono font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                @error('package_code') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Fixed Fee (₱) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" min="0" wire:model="fixed_fee"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-mono font-bold text-teal-700 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                @error('fixed_fee') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Description</label>
                            <textarea wire:model="description" rows="2"
                                class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="flex items-center cursor-pointer select-none">
                                <input type="checkbox" wire:model="is_active" class="w-4 h-4 rounded border-gray-300 text-light-blue-600 focus:ring-light-blue-500">
                                <span class="ml-2 text-xs font-semibold text-gray-800">Active Package Rate</span>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-2 pt-2 border-t border-gray-100">
                            <button wire:click="closePackageModal" type="button" class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs">
                                Save Package
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
