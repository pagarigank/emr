<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Hospital Transfers</h2>
            <p class="text-sm text-gray-500 mt-1">Track patient transfers to other healthcare facilities.</p>
        </div>
        <button wire:click="openCreate" class="inline-flex items-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Transfer
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by patient name or code..." class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th wire:click="sortBy('transfer_code')" class="px-5 py-3 text-left font-semibold text-gray-600 uppercase cursor-pointer hover:text-gray-900">Transfer #</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase">Patient</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 uppercase">Destination</th>
                        <th wire:click="sortBy('transfer_datetime')" class="px-5 py-3 text-left font-semibold text-gray-600 uppercase cursor-pointer hover:text-gray-900">Date</th>
                        <th class="px-5 py-3 text-center font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-5 py-3 text-center font-semibold text-gray-600 uppercase w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($records as $r)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $r->transfer_code }}</td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900">{{ $r->patient->full_name ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $r->patient->patient_code ?? '' }}</div>
                            </td>
                            <td class="px-5 py-4 text-gray-800">{{ $r->destinationHospital->name ?? 'N/A' }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-gray-600">{{ $r->transfer_datetime ? $r->transfer_datetime->format('M d, Y H:i') : '—' }}</td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $statusColors = ['Pending' => 'bg-amber-100 text-amber-800', 'In Transit' => 'bg-blue-100 text-blue-800', 'Completed' => 'bg-emerald-100 text-emerald-800', 'Cancelled' => 'bg-red-100 text-red-800'];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$r->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $r->status }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <button wire:click="openEdit({{ $r->id }})" class="p-1.5 text-amber-600 hover:bg-amber-100 rounded-md transition-colors" title="View/Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button wire:click="confirmDelete({{ $r->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16h.01M8 12h.01M8 8h.01M12 16h.01M12 12h.01M12 8h.01M16 16h.01M16 12h.01M16 8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-lg font-medium text-gray-900">No transfers found</span><p class="mt-1 text-sm">Create a new transfer record to get started.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t">{{ $records->links() }}</div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 my-8 flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-amber-50/50 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h.01M8 12h.01M8 8h.01M12 16h.01M12 12h.01M12 8h.01M16 16h.01M16 12h.01M16 8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $isEditing ? 'Edit' : 'New' }} Hospital Transfer
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="save" class="flex-1 overflow-y-auto p-6 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Patient <span class="text-red-500">*</span></label>
                        <input wire:model.live.debounce.300ms="patient_search" type="text" placeholder="Search by name or code..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm @error('patient_id') border-red-300 bg-red-50 @enderror">
                        @if(strlen($patient_search) >= 2 && empty($patient_id))
                            <div class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200 max-h-40 overflow-y-auto">
                                <ul class="py-1">
                                    @foreach($patients as $p)
                                        <li>
                                            <button type="button" wire:click="selectPatient({{ $p->id }})" class="w-full text-left px-4 py-2.5 text-sm hover:bg-amber-50 transition-colors">{{ $p->last_name }}, {{ $p->first_name }} ({{ $p->patient_code }})</button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if($patient_id && $patient_search)
                            <div class="mt-1 flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Selected: {{ $patient_search }}
                                <button type="button" wire:click="$set('patient_id', null); $set('patient_search', '')" class="ml-auto text-red-500 hover:text-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </div>
                        @endif
                        @error('patient_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Destination Hospital <span class="text-red-500">*</span></label>
                        <select wire:model="destination_hospital_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm @error('destination_hospital_id') border-red-300 bg-red-50 @enderror">
                            <option value="">Select Hospital</option>
                            @foreach($hospitals as $h)
                                <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->code }})</option>
                            @endforeach
                        </select>
                        @error('destination_hospital_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Source Encounter Type</label>
                        <select wire:model="source_type" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                            @foreach($sourceTypes as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Source Encounter ID</label>
                        <input wire:model="source_id" type="number" placeholder="Encounter ID (optional)" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transfer Reason</label>
                    <textarea wire:model="transfer_reason" rows="2" placeholder="Why is the patient being transferred?" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Transfer Diagnosis</label>
                    <textarea wire:model="transfer_diagnosis" rows="2" placeholder="Primary diagnosis at time of transfer" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transferred By</label>
                        <select wire:model="transferred_by" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                            <option value="">Select Doctor</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transport Mode</label>
                        <select wire:model="transport_mode" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                            <option value="">Select Mode</option>
                            <option value="Ambulance">Ambulance</option>
                            <option value="Private Vehicle">Private Vehicle</option>
                            <option value="Walk-in">Walk-in</option>
                            <option value="Air Ambulance">Air Ambulance</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                            <option value="Pending">Pending</option>
                            <option value="In Transit">In Transit</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Accompanying Personnel</label>
                    <input wire:model="accompanying_personnel" placeholder="e.g. Nurse Rodriguez, EMT Cruz" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Condition on Transfer</label>
                    <textarea wire:model="condition_on_transfer" rows="2" placeholder="Patient's clinical condition at time of transfer (vitals, level of consciousness, etc.)" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="notes" rows="2" placeholder="Additional notes" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-sm transition-colors">{{ $isEditing ? 'Update' : 'Create Transfer' }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 p-6">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Archive Transfer Record?</h3>
                <p class="text-sm text-gray-500">This will hide this transfer record. Are you sure?</p>
                <div class="flex justify-center gap-3 mt-6">
                    <button wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                    <button wire:click="delete" class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm">Archive</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
