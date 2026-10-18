<div>
    {{-- ── Page header ── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-emerald-800">Drug Formulary</h2>
            <p class="text-sm text-gray-500 mt-1">Manage medication master list</p>
        </div>
        <button wire:click="openCreate" id="btn-add-drug"
                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-400">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Drug
        </button>
    </div>

    {{-- ── Toolbar (search + per-page) ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" id="search-drug"
                   type="text" placeholder="Search code, name, class, strength…"
                   class="pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 w-full sm:w-80 shadow-sm"/>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" id="per-page-drug"
                    class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    {{-- ── Data Grid ── --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    @foreach([
                        ['drug_code', 'Drug Code'],
                        ['generic_name', 'Generic Name'],
                        ['brand_name', 'Brand Name'],
                        ['drug_strength', 'Strength'],
                        ['dosage_form', 'Dosage Form'],
                        ['therapeutic_class', 'Class'],
                        ['drug_schedule', 'Schedule'],
                    ] as [$field, $label])
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none whitespace-nowrap hover:bg-gray-100/50 transition-colors"
                        wire:click="sortBy('{{ $field }}')">
                        <span class="inline-flex items-center gap-1">
                            {{ $label }}
                            @if($sortField === $field)
                                <svg class="w-3.5 h-3.5 text-emerald-600 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }} transition-transform"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 10l5-5 5 5H5z"/>
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 10l5-5 5 5H5z"/>
                                </svg>
                            @endif
                        </span>
                    </th>
                    @endforeach
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Unit Cost</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($drugs as $drug)
                    <tr class="hover:bg-emerald-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $drug->drug_code }}</td>
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $drug->generic_name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $drug->brand_name ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $drug->drug_strength ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $drug->dosage_form ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $drug->therapeutic_class ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @if($drug->drug_schedule)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold
                                    {{ $drug->drug_schedule === 'Narcotic' ? 'bg-red-50 text-red-700 border border-red-200/50' :
                                       ($drug->drug_schedule === 'Controlled' ? 'bg-amber-50 text-amber-700 border border-amber-200/50' :
                                       ($drug->drug_schedule === 'Prescription' ? 'bg-blue-50 text-blue-700 border border-blue-200/50' :
                                        'bg-green-50 text-green-700 border border-green-200/50')) }}">
                                    {{ $drug->drug_schedule }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center font-mono text-sm font-semibold text-gray-800">
                            {{ number_format($drug->stock_on_hand ?? 0) }}
                        </td>
                        <td class="px-5 py-4 text-center font-mono text-sm text-gray-600">
                            @if(($drug->unit_cost_avg ?? 0) > 0)
                                ${{ number_format($drug->unit_cost_avg, 2) }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($drug->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200/50 shadow-sm">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200 shadow-sm">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <button wire:click="openDetail({{ $drug->id }})"
                                        class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                <button wire:click="openEdit({{ $drug->id }})"
                                        class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-md transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $drug->id }})"
                                        class="p-1.5 text-amber-500 hover:bg-amber-100 rounded-md transition-colors" title="Archive">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8M10 12v4m4-4v4"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-12 text-center text-gray-400">
                            <svg class="mx-auto w-10 h-10 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            No drugs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
    <div class="mt-4">
        {{ $drugs->links() }}
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         CREATE / EDIT MODAL
    ════════════════════════════════════════════════════════════════════════ --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center"
         x-data x-init="$el.querySelector('[data-modal]').focus()">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>

        {{-- Panel (wider for drug fields) --}}
        <div data-modal tabindex="-1"
             class="relative bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 z-10 overflow-y-auto max-h-[90vh]"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50">
                <h3 class="text-lg font-semibold text-emerald-900">
                    {{ $isEditing ? 'Edit Drug' : 'Add New Drug' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Form --}}
            <form wire:submit="save" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Drug Code --}}
                    <div>
                        <label for="drug_code" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Drug Code <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="drug_code" id="drug_code" type="text" maxlength="20"
                               placeholder="DRG-00001"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500
                                      @error('drug_code') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                        @error('drug_code')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Generic Name --}}
                    <div>
                        <label for="generic_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Generic Name <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="generic_name" id="generic_name" type="text" maxlength="255"
                               placeholder="e.g. Paracetamol"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500
                                      @error('generic_name') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                        @error('generic_name')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Brand Name --}}
                    <div>
                        <label for="brand_name" class="block text-sm font-medium text-gray-700 mb-1.5">Brand Name</label>
                        <input wire:model="brand_name" id="brand_name" type="text" maxlength="255"
                               placeholder="e.g. Tylenol"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>

                    {{-- Drug Strength --}}
                    <div>
                        <label for="drug_strength" class="block text-sm font-medium text-gray-700 mb-1.5">Strength</label>
                        <input wire:model="drug_strength" id="drug_strength" type="text" maxlength="100"
                               placeholder="e.g. 500mg"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>

                    {{-- Dosage Form --}}
                    <div>
                        <label for="dosage_form" class="block text-sm font-medium text-gray-700 mb-1.5">Dosage Form</label>
                        <select wire:model="dosage_form" id="dosage_form"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">— Select —</option>
                            @foreach($dosageForms as $form)
                                <option value="{{ $form }}">{{ $form }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Therapeutic Class --}}
                    <div>
                        <label for="therapeutic_class" class="block text-sm font-medium text-gray-700 mb-1.5">Therapeutic Class</label>
                        <input wire:model="therapeutic_class" id="therapeutic_class" type="text" maxlength="100"
                               placeholder="e.g. Analgesic"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>

                    {{-- Drug Schedule --}}
                    <div>
                        <label for="drug_schedule" class="block text-sm font-medium text-gray-700 mb-1.5">Drug Schedule</label>
                        <select wire:model="drug_schedule" id="drug_schedule"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">— Select —</option>
                            @foreach($drugSchedules as $schedule)
                                <option value="{{ $schedule }}">{{ $schedule }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Route (multi-select) --}}
                    <div x-data="{ open: false }" class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Route(s) of Administration</label>
                        <button type="button" @click="open = !open"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-left text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <span class="inline-flex items-center gap-1 flex-wrap">
                                @forelse($route as $r)
                                    <span class="inline-flex items-center px-2 py-0.5 bg-emerald-100 text-emerald-800 text-xs rounded-full">
                                        {{ $r }}
                                    </span>
                                @empty
                                    <span class="text-gray-400">Select route(s)…</span>
                                @endforelse
                            </span>
                            <svg class="w-4 h-4 float-right mt-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                             class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg p-2 max-h-48 overflow-y-auto">
                            <div class="space-y-1">
                                @foreach($routeOptions as $val => $label)
                                    <label class="flex items-center px-2 py-1.5 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox" wire:model="route" value="{{ $val }}"
                                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-2">
                                        <span class="text-sm text-gray-700">{{ $label }} ({{ $val }})</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Active Ingredient --}}
                    <div>
                        <label for="active_ingredient" class="block text-sm font-medium text-gray-700 mb-1.5">Active Ingredient</label>
                        <input wire:model="active_ingredient" id="active_ingredient" type="text" maxlength="255"
                               placeholder="e.g. Acetaminophen"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>

                    {{-- Manufacturer --}}
                    <div>
                        <label for="manufacturer" class="block text-sm font-medium text-gray-700 mb-1.5">Manufacturer</label>
                        <input wire:model="manufacturer" id="manufacturer" type="text" maxlength="255"
                               placeholder="e.g. Pfizer"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>

                {{-- Side Effects --}}
                <div>
                    <label for="side_effects" class="block text-sm font-medium text-gray-700 mb-1.5">Side Effects</label>
                    <textarea wire:model="side_effects" id="side_effects" rows="2"
                              placeholder="List known side effects…"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none"></textarea>
                </div>

                {{-- Contraindications --}}
                <div>
                    <label for="contraindications" class="block text-sm font-medium text-gray-700 mb-1.5">Contraindications</label>
                    <textarea wire:model="contraindications" id="contraindications" rows="2"
                              placeholder="List known contraindications…"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Reorder Level --}}
                    <div>
                        <label for="reorder_level" class="block text-sm font-medium text-gray-700 mb-1.5">Reorder Level</label>
                        <input wire:model="reorder_level" id="reorder_level" type="number" min="0"
                               placeholder="e.g. 100"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500
                                      @error('reorder_level') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                        @error('reorder_level')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Reorder Qty --}}
                    <div>
                        <label for="reorder_qty" class="block text-sm font-medium text-gray-700 mb-1.5">Reorder Quantity</label>
                        <input wire:model="reorder_qty" id="reorder_qty" type="number" min="0"
                               placeholder="e.g. 500"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500
                                      @error('reorder_qty') border-red-300 bg-red-50 ring-red-500/20 focus:border-red-500 focus:ring-red-500/20 @enderror">
                        @error('reorder_qty')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Is Active --}}
                <div class="flex items-center space-x-3">
                    <button type="button" wire:click="$toggle('is_active')" id="toggle-is-active"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-400
                                   {{ $is_active ? 'bg-emerald-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                                     {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                    <span class="text-sm text-gray-700 font-medium">
                        {{ $is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                    <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="btn-save-drug"
                            class="px-5 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-400">
                        <span wire:loading.remove wire:target="save">
                            {{ $isEditing ? 'Update' : 'Save' }}
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            Saving…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         DETAIL MODAL
    ════════════════════════════════════════════════════════════════════════ --}}
    @if($showDetailModal && $detailDrug)
    <div class="fixed inset-0 z-50 flex items-center justify-center"
         x-data x-init="$el.querySelector('[data-detail-modal]').focus()">

        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>

        <div data-detail-modal tabindex="-1"
             class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 z-10 overflow-y-auto max-h-[90vh]"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-emerald-50">
                <h3 class="text-lg font-semibold text-emerald-900">Drug Details</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">
                {{-- Header info --}}
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">{{ $detailDrug['generic_name'] }}</h4>
                        @if($detailDrug['brand_name'])
                            <p class="text-sm text-gray-500">{{ $detailDrug['brand_name'] }}</p>
                        @endif
                    </div>
                    <span class="font-mono text-sm bg-gray-100 px-3 py-1 rounded-md text-gray-700">{{ $detailDrug['drug_code'] }}</span>
                </div>

                {{-- Details grid --}}
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Strength</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailDrug['drug_strength'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Dosage Form</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailDrug['dosage_form'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Therapeutic Class</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailDrug['therapeutic_class'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Schedule</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailDrug['drug_schedule'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Route(s)</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">
                            @if($detailDrug['route'])
                                <span class="inline-flex gap-1 flex-wrap">
                                    @foreach(explode(',', $detailDrug['route']) as $r)
                                        <span class="inline-flex items-center px-2 py-0.5 bg-emerald-100 text-emerald-800 text-xs rounded-full">{{ trim($r) }}</span>
                                    @endforeach
                                </span>
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Active Ingredient</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailDrug['active_ingredient'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Manufacturer</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailDrug['manufacturer'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Stock On Hand</span>
                        <p class="text-sm font-mono font-semibold text-gray-900 mt-0.5">{{ number_format($detailDrug['stock_on_hand']) }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Avg. Unit Cost</span>
                        <p class="text-sm font-mono font-semibold text-gray-900 mt-0.5">
                            @if($detailDrug['unit_cost'] > 0)
                                ${{ number_format($detailDrug['unit_cost'], 2) }}
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Reorder Level</span>
                        <p class="text-sm font-mono font-medium text-gray-900 mt-0.5">{{ $detailDrug['reorder_level'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Reorder Qty</span>
                        <p class="text-sm font-mono font-medium text-gray-900 mt-0.5">{{ $detailDrug['reorder_qty'] ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Status</span>
                        <p class="mt-0.5">
                            @if($detailDrug['is_active'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-green-50 text-green-700 border border-green-200/50">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Side Effects --}}
                @if($detailDrug['side_effects'])
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Side Effects</span>
                    <p class="text-sm text-gray-700 mt-1 bg-red-50 border border-red-100 rounded-lg p-3">{{ $detailDrug['side_effects'] }}</p>
                </div>
                @endif

                {{-- Contraindications --}}
                @if($detailDrug['contraindications'])
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Contraindications</span>
                    <p class="text-sm text-gray-700 mt-1 bg-amber-50 border border-amber-100 rounded-lg p-3">{{ $detailDrug['contraindications'] }}</p>
                </div>
                @endif

                {{-- Close button --}}
                <div class="flex justify-end pt-2 border-t border-gray-100">
                    <button wire:click="closeModal"
                            class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         ARCHIVE (SOFT DELETE) CONFIRM MODAL
    ════════════════════════════════════════════════════════════════════════ --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 z-10 p-6"
             x-data x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8M10 12v4m4-4v4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Archive Drug</h3>
                <p class="text-sm text-gray-500 mb-1">This drug record will be <strong>archived</strong> (history = 0).</p>
                <p class="text-xs text-gray-400 mb-6">No data is permanently deleted. Records can be restored from the database.</p>
                <div class="flex space-x-3 w-full">
                    <button wire:click="closeModal"
                            class="flex-1 px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="delete" id="btn-confirm-delete"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors">
                        <span wire:loading.remove wire:target="delete">Yes, Archive</span>
                        <span wire:loading wire:target="delete">Archiving…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
