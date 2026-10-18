<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">YAKAP Screenings</h2>
            <p class="text-sm text-gray-500 mt-1">Track the 6 YAKAP cancer screenings for empaneled patients.</p>
        </div>
        <button wire:click="openCreate" class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>New Screening
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-xl flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search patient or screening..." class="block w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl leading-5 text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm shadow-sm"/>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Patient</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Empanelment</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer" wire:click="sortBy('screening_type')">Type</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer" wire:click="sortBy('screening_name')">Screening</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase cursor-pointer" wire:click="sortBy('screening_date')">Date</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase">Result</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($screenings as $s)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $s->patient?->last_name }}, {{ $s->patient?->first_name }}</td>
                        <td class="px-5 py-4 font-mono text-gray-600">{{ $s->empanelment?->empanelment_code ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-700">{{ $s->screening_type }}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-600 max-w-xs truncate">{{ $s->screening_name }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $s->screening_date ? \Carbon\Carbon::parse($s->screening_date)->format('M d, Y') : '—' }}</td>
                        <td class="px-5 py-4 text-gray-600 max-w-xs truncate">{{ $s->result ?? '—' }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $sc = match($s->status) {
                                    'Completed' => 'bg-green-100 text-green-700',
                                    'Pending' => 'bg-amber-100 text-amber-700',
                                    'Overdue' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold {{ $sc }}">{{ $s->status }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="edit({{ $s->id }})" class="text-blue-600 hover:text-blue-800"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button wire:click="confirmDelete({{ $s->id }})" class="text-red-600 hover:text-red-800"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-8 text-center text-gray-500">No screening records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $screenings->links() }}</div>

    {{-- CREATE/EDIT MODAL --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" wire:click.self="$set('showModal', false)">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto m-4">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">{{ $isEditing ? 'Edit' : 'New' }} Screening Record</h3>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Empanelment <span class="text-red-500">*</span></label>
                        @if(!$isEditing)
                            <input wire:model.live="empanelment_search" type="text" placeholder="Search empanelment..." class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            @if($empanelment_search && $empanelments->isNotEmpty())
                                <ul class="mt-1 border border-gray-200 rounded-xl divide-y divide-gray-100 max-h-40 overflow-y-auto">
                                    @foreach($empanelments as $e)
                                        <li wire:click="$set('empanelment_id', {{ $e->id }}); $set('empanelment_search', '{{ $e->empanelment_code }}')" class="px-3 py-2 text-sm cursor-pointer hover:bg-emerald-50">{{ $e->empanelment_code }} — {{ $e->patient?->last_name }}, {{ $e->patient?->first_name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        @else
                            <p class="text-sm text-gray-600">{{ $screenings->find($editingId)?->empanelment?->empanelment_code }}</p>
                        @endif
                        @error('empanelment_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Screening Type <span class="text-red-500">*</span></label>
                        <select wire:model.live="screening_type" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select type</option>
                            @foreach($screeningTypes as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                        @error('screening_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Screening Name <span class="text-red-500">*</span></label>
                    <input wire:model="screening_name" type="text" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    @error('screening_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Screening Date</label>
                        <input wire:model="screening_date" type="date" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Next Screening Due</label>
                        <input wire:model="next_screening_due" type="date" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Result</label>
                        <select wire:model="result" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">Select result</option>
                            <option value="Normal">Normal</option>
                            <option value="Abnormal">Abnormal</option>
                            <option value="Positive">Positive</option>
                            <option value="Negative">Negative</option>
                            <option value="Inconclusive">Inconclusive</option>
                            <option value="Not Done">Not Done</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="status" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                            <option value="Overdue">Overdue</option>
                            <option value="Skipped">Skipped</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Result Details</label>
                    <textarea wire:model="result_details" rows="2" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility Name</label>
                        <input wire:model="facility_name" type="text" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Performed By</label>
                        <select wire:model="performed_by" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
                            <option value="">Select doctor</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <input wire:model="notes" type="text" class="block w-full px-3 py-2 border border-gray-200 rounded-xl text-sm">
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Cancel</button>
                <button wire:click="save" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700">Save</button>
            </div>
        </div>
    </div>
    @endif

    {{-- DELETE MODAL --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" wire:click.self="$set('showDeleteModal', false)">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md m-4 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Archive Screening Record</h3>
            <p class="text-sm text-gray-600 mb-6">Are you sure you want to archive this screening record?</p>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50">Cancel</button>
                <button wire:click="delete" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700">Archive</button>
            </div>
        </div>
    </div>
    @endif
</div>
