<div class="space-y-6">
    {{-- Header & Title --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dialysis Machines &amp; Stations</h1>
            <p class="text-sm text-gray-500 mt-1">Manage hemodialysis stations, machine models, infection isolation categories, and sanitization status</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('renal-care.dialysis-sessions') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Dialysis Sessions
            </a>
            <button wire:click="openCreate" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Station / Machine
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Stations</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Licensed Units</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Available</p>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ $stats['available'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Ready for Shift</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">In-Session</p>
            <p class="text-2xl font-bold text-blue-700 mt-1">{{ $stats['occupied'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Currently Dialyzing</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Disinfecting</p>
            <p class="text-2xl font-bold text-purple-700 mt-1">{{ $stats['disinfecting'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Rinse / Chemical</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Maintenance</p>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ $stats['maintenance'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Calibration / Offline</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-rose-200 shadow-sm bg-rose-50/20">
            <p class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Isolation Bays</p>
            <p class="text-2xl font-bold text-rose-800 mt-1">{{ $stats['isolation'] }}</p>
            <p class="text-xs text-rose-600 mt-0.5">Hep B &amp; C Dedicated</p>
        </div>
    </div>

    {{-- Filters & Search Toolbar --}}
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
        <div class="relative flex-1">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by station code, name, serial number, brand, or model..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="statusFilter" class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                <option value="">All Statuses</option>
                <option value="Available">Available</option>
                <option value="Occupied">Occupied (In-Session)</option>
                <option value="Disinfecting">Disinfecting</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Offline">Offline</option>
            </select>
            <select wire:model.live="categoryFilter" class="px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
                <option value="">All Categories</option>
                <option value="Regular">Regular (Negative)</option>
                <option value="HepB_Isolation">Hep B Isolation</option>
                <option value="HepC_Isolation">Hep C Isolation</option>
                <option value="ICU_Mobile">ICU Mobile</option>
            </select>
        </div>
    </div>

    {{-- Stations Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                        <th class="px-6 py-3 text-left cursor-pointer select-none" wire:click="sortBy('station_code')">
                            Station Code
                            @if($sortField === 'station_code') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                        </th>
                        <th class="px-6 py-3 text-left cursor-pointer select-none" wire:click="sortBy('station_name')">
                            Station Name &amp; Location
                        </th>
                        <th class="px-6 py-3 text-left">Machine Details</th>
                        <th class="px-6 py-3 text-center">Infection Category</th>
                        <th class="px-6 py-3 text-center">Operational Status</th>
                        <th class="px-6 py-3 text-center">Active</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $m)
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-6 py-4 font-mono font-bold text-emerald-700 whitespace-nowrap">
                            {{ $m->station_code }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $m->station_name }}</div>
                            @if($m->notes)
                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $m->notes }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-800">{{ $m->brand ?? '—' }} {{ $m->model_name ? "({$m->model_name})" : '' }}</div>
                            <div class="text-xs text-gray-400 font-mono">SN: {{ $m->serial_number ?: 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if($m->station_category === 'Regular')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Regular (HBsAg/HCV-)
                                </span>
                            @elseif($m->station_category === 'HepB_Isolation')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300">
                                    ⚠️ Hep B Dedicated
                                </span>
                            @elseif($m->station_category === 'HepC_Isolation')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 border border-orange-300">
                                    ⚠️ Hep C Dedicated
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    ICU Mobile Bedside
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @php
                                $statusClasses = match($m->status) {
                                    'Available' => 'bg-green-100 text-green-800 border-green-200',
                                    'Occupied' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'Disinfecting' => 'bg-purple-100 text-purple-800 border-purple-200',
                                    'Maintenance' => 'bg-amber-100 text-amber-800 border-amber-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200'
                                };
                            @endphp
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button @click="open = !open" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $statusClasses }} hover:opacity-80 transition">
                                    <span>{{ $m->status }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" @click.outside="open = false" x-cloak class="origin-top-right absolute right-0 mt-1.5 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20 py-1">
                                    <button wire:click="updateStatus({{ $m->id }}, 'Available')" @click="open = false" class="block w-full text-left px-3 py-1.5 text-xs text-green-700 hover:bg-green-50">Set: Available</button>
                                    <button wire:click="updateStatus({{ $m->id }}, 'Occupied')" @click="open = false" class="block w-full text-left px-3 py-1.5 text-xs text-blue-700 hover:bg-blue-50">Set: In-Session</button>
                                    <button wire:click="updateStatus({{ $m->id }}, 'Disinfecting')" @click="open = false" class="block w-full text-left px-3 py-1.5 text-xs text-purple-700 hover:bg-purple-50">Set: Disinfecting</button>
                                    <button wire:click="updateStatus({{ $m->id }}, 'Maintenance')" @click="open = false" class="block w-full text-left px-3 py-1.5 text-xs text-amber-700 hover:bg-amber-50">Set: Maintenance</button>
                                    <button wire:click="updateStatus({{ $m->id }}, 'Offline')" @click="open = false" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">Set: Offline</button>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if($m->is_active)
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Active</span>
                            @else
                                <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $m->id }})" class="p-1.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Edit Station">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $m->id }})" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Archive Station">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400 text-sm">
                            No dialysis machines or stations found matching the filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-6 py-3 border-t border-gray-100">
            {{ $items->links() }}
        </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-gray-100 overflow-hidden transform transition-all">
            <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between">
                <h3 class="text-lg font-bold">{{ $isEditing ? 'Edit Dialysis Station / Machine' : 'New Dialysis Station / Machine' }}</h3>
                <button wire:click="closeModal" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
            </div>
            <form wire:submit="save" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Station Code *</label>
                        <input type="text" wire:model="station_code" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        @error('station_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Category *</label>
                        <select wire:model="station_category" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="Regular">Regular (Negative)</option>
                            <option value="HepB_Isolation">Hep B Isolation</option>
                            <option value="HepC_Isolation">Hep C Isolation</option>
                            <option value="ICU_Mobile">ICU Mobile</option>
                        </select>
                        @error('station_category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Station Name / Location *</label>
                    <input type="text" wire:model="station_name" placeholder="e.g. Station 01 - Main Hall Bay A" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    @error('station_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Brand</label>
                        <input type="text" wire:model="brand" placeholder="e.g. Fresenius" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Model</label>
                        <input type="text" wire:model="model_name" placeholder="e.g. 4008S" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                        <select wire:model="status" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:outline-none">
                            <option value="Available">Available</option>
                            <option value="Occupied">Occupied</option>
                            <option value="Disinfecting">Disinfecting</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Offline">Offline</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Serial Number</label>
                    <input type="text" wire:model="serial_number" placeholder="e.g. FN-4008S-2024-001" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm font-mono focus:bg-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Clinical &amp; Technical Notes</label>
                    <textarea wire:model="notes" rows="3" placeholder="Water line supply specs, disinfection cycle notes, isolation guidelines..." class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:bg-white focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="is_active" wire:model="is_active" class="w-4 h-4 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                    <label for="is_active" class="text-sm text-gray-700 font-medium">Station is Active</label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm">Save Station</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Delete / Archive Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Archive Dialysis Station?</h3>
            <p class="text-sm text-gray-500">This station will be marked inactive and archived. Existing historical session logs will be preserved.</p>
            <div class="flex items-center justify-center gap-3 pt-2">
                <button wire:click="closeModal" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">Cancel</button>
                <button wire:click="delete" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg shadow-sm">Yes, Archive</button>
            </div>
        </div>
    </div>
    @endif
</div>
