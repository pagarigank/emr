<div>
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Appointment Schedule</h1>
            <p class="mt-1 text-sm text-gray-500">Manage patient appointment bookings and your daily schedule.</p>
        </div>
        @if($isAdmin)
            <a href="/book-appointment" target="_blank" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-lg shadow-emerald-200 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Public Booking Page
            </a>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="relative">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search patient or code…"
                           class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
                <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <option value="">All Statuses</option>
                    <option value="Scheduled">Scheduled</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
                <input type="date" wire:model.live="dateFilter" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                @if($isAdmin)
                    <select wire:model.live="doctorFilter" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        <option value="">All Doctors</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}">{{ $doc->full_name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Date &amp; Time</th>
                        <th class="px-4 py-3">Patient</th>
                        <th class="px-4 py-3">Department</th>
                        @if($isAdmin)
                            <th class="px-4 py-3">Doctor</th>
                        @endif
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $apt)
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $apt->appointment_code }}</td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-800">{{ $apt->appointment_date?->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ Carbon\Carbon::parse($apt->start_time)->format('g:i A') }} – {{ Carbon\Carbon::parse($apt->end_time)->format('g:i A') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $apt->patient_name }}</p>
                                @if($apt->patient_phone)
                                    <p class="text-xs text-gray-500">{{ $apt->patient_phone }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $apt->department?->dept_name ?? '—' }}</td>
                            @if($isAdmin)
                                <td class="px-4 py-3 text-gray-600">{{ $apt->doctor?->full_name ?? '—' }}</td>
                            @endif
                            <td class="px-4 py-3">
                                @php
                                    $badge = match($apt->status) {
                                        'Completed' => 'bg-emerald-100 text-emerald-700',
                                        'Cancelled' => 'bg-red-100 text-red-700',
                                        'In Progress' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-sky-100 text-sky-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $apt->status }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button wire:click="openEdit({{ $apt->id }})" title="Edit / Reschedule" class="p-2 rounded-lg text-sky-600 hover:bg-sky-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @if($apt->status === 'Scheduled')
                                        <button wire:click="markInProgress({{ $apt->id }})" title="Start" class="p-2 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif
                                    @if(in_array($apt->status, ['Scheduled', 'In Progress']))
                                        <button wire:click="markDone({{ $apt->id }})" title="Mark Done" class="p-2 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    @endif
                                    @if($apt->status !== 'Cancelled')
                                        <button wire:click="cancelAppointment({{ $apt->id }})" title="Cancel" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="px-4 py-16 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="mt-3 text-sm text-gray-500">No appointments found.</p>
                                <p class="mt-1 text-xs text-gray-400">Bookings made by patients on the public page will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    {{-- Reschedule Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" wire:click.self="closeModal">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Edit Appointment</h3>
                        <p class="text-xs text-gray-500">Reschedule, add notes, or update the status.</p>
                    </div>
                    <button wire:click="closeModal" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="save" class="px-6 py-5 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Appointment Date *</label>
                        <input type="date" wire:model.live="edit_date" min="{{ \Carbon\Carbon::now()->subDay()->toDateString() }}"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('edit_date') border-red-300 @enderror">
                        @error('edit_date') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Time Slot *</label>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                            @foreach($availableSlots as $slot)
                                <button type="button" wire:click="selectSlot('{{ $slot['start'] }}')"
                                        @class([
                                            'py-2.5 rounded-xl border text-sm font-semibold transition-all duration-150',
                                            $edit_start_time === $slot['start']
                                                ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-200'
                                                : 'bg-white border-gray-200 text-gray-700 hover:border-emerald-400 hover:text-emerald-700'
                                        ])>
                                    {{ $slot['label'] }}
                                </button>
                            @endforeach
                        </div>
                        @if(count($availableSlots) === 0)
                            <p class="mt-2 text-xs text-amber-600 font-medium">No free slots for this doctor on the selected date.</p>
                        @endif
                        @error('edit_start_time') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status *</label>
                        <select wire:model="edit_status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="Scheduled">Scheduled</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        @error('edit_status') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                        <textarea wire:model="edit_notes" rows="3" placeholder="Internal notes (visible to staff only)"
                                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                        @error('edit_notes') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-lg shadow-emerald-200 transition-colors">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
