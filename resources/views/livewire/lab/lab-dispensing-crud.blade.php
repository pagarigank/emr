<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Lab Dispensing</h2>
            <p class="text-sm text-gray-500 mt-1">Create and manage laboratory test requests from all departments.</p>
        </div>
        <button wire:click="openCreate" class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>New Lab Request</button>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search request code or patient&hellip;" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm shadow-sm"/></div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option></select>
        </div>
    </div>
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('request_code')">
                        <span class="inline-flex items-center gap-1">Request Code<span class="text-gray-300">@if($sortField === 'request_code'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Priority</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Tests</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Charge</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($items as $item)
                    <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $item->request_code }}</td>
                        <td class="px-5 py-4">
                            <div class="text-gray-800 font-medium">{{ $item->patient_name }}</div>
                            @if($item->patient) <div class="text-xs text-gray-400 font-mono">{{ $item->patient->patient_code }}</div> @endif
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->department_name }}</td>
                        <td class="px-5 py-4 text-center">
                            @php $pClass = $item->priority === 'STAT' ? 'bg-red-50 text-red-700 border-red-200' : ($item->priority === 'Urgent' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-blue-50 text-blue-700 border-blue-200'); @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $pClass }}">{{ $item->priority }}</span>
                        </td>
                        <td class="px-5 py-4 text-center font-mono text-gray-700">{{ $item->items->count() }}</td>
                        <td class="px-5 py-4 text-right font-mono text-gray-700">&#8369;{{ number_format($item->total_charge, 2) }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $sClass = match($item->status) {
                                    'Completed' => 'bg-green-50 text-green-700 border-green-200',
                                    'Processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'Collected' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    default => 'bg-blue-50 text-blue-700 border-blue-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $sClass }}">{{ $item->status }}</span>
                        </td>
                        <td class="px-5 py-4"><div class="flex items-center justify-center space-x-2">
                            <button wire:click="viewDetail({{ $item->id }})" class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors" title="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                            <button wire:click="openEdit({{ $item->id }})" class="p-1.5 text-light-blue-600 hover:bg-light-blue-100 rounded-md transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                            <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Cancel Request"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            @if($item->status !== 'Completed' && $item->status !== 'Cancelled')
                            <button wire:click="completeAllItems({{ $item->id }})" wire:confirm="Mark all items as Completed?" class="p-1.5 text-green-500 hover:bg-green-100 rounded-md transition-colors" title="Complete All">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            @endif

                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg><span class="text-lg font-medium text-gray-900">No lab requests found</span><p class="mt-1 text-sm">Create a new lab request to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50/50">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isEditing ? 'Edit Lab Request' : 'New Lab Request' }}</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="save" class="px-6 py-5 space-y-5">
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Request Code</label>
                        <input wire:model="request_code" type="text" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-700 cursor-not-allowed">
                    </div>
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1.5">Priority <span class="text-red-500">*</span></label>
                        <select wire:model="priority" id="priority" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('priority') border-red-300 bg-red-50 @enderror">
                            <option value="Routine">Routine</option>
                            <option value="Urgent">Urgent</option>
                            <option value="STAT">STAT</option>
                        </select>
                        @error('priority') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="requesting_department_id" class="block text-sm font-medium text-gray-700 mb-1.5">Requesting Dept</label>
                        <select wire:model="requesting_department_id" id="requesting_department_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('requesting_department_id') border-red-300 bg-red-50 @enderror">
                            <option value="">-- Select --</option>
                            @foreach($departments as $d)<option value="{{ $d->id }}">[{{ $d->dept_code }}] {{ $d->dept_name }}</option>@endforeach
                        </select>
                        @error('requesting_department_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="relative">
                        <label for="patient_id" class="block text-sm font-medium text-gray-700 mb-1.5">Patient <span class="text-red-500">*</span></label>
                        @if($patient_id && $selected_patient_name)
                            <div class="flex items-center justify-between px-4 py-2.5 bg-light-blue-50 border border-light-blue-200 rounded-lg text-sm transition-all">
                                <span class="font-medium text-light-blue-800">{{ $selected_patient_name }}</span>
                                <button type="button" wire:click="$set('patient_id', null)" class="text-light-blue-500 hover:text-light-blue-700 focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @else
                            <input wire:model.live.debounce.300ms="patient_search" id="patient_search" type="text" placeholder="Search patient name or code&hellip;" autocomplete="off" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('patient_id') border-red-300 bg-red-50 @enderror">
                            @if(strlen($patient_search) > 0 && count($patients) > 0 && !$patient_id)
                                <ul class="absolute z-50 w-full bg-white border border-gray-200 mt-1 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                    @foreach($patients as $p)
                                        <li wire:click="selectPatient({{ $p->id }}, '{{ addslashes($p->last_name . ', ' . $p->first_name . ' (' . $p->patient_code . ')') }}')" class="px-4 py-2 hover:bg-light-blue-50 cursor-pointer text-sm transition-colors border-b border-gray-50 last:border-0">
                                            <div class="font-medium text-gray-900">{{ $p->last_name }}, {{ $p->first_name }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $p->patient_code }}</div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                        @error('patient_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="requesting_doctor_id" class="block text-sm font-medium text-gray-700 mb-1.5">Requesting Doctor</label>
                        <select wire:model="requesting_doctor_id" id="requesting_doctor_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('requesting_doctor_id') border-red-300 bg-red-50 @enderror">
                            <option value="">-- Select --</option>
                            @foreach($doctors as $doc)<option value="{{ $doc->id }}">[{{ $doc->professional_code }}] {{ $doc->full_name }}</option>@endforeach
                        </select>
                        @error('requesting_doctor_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Test Items <span class="text-red-500">*</span></label>
                    <div class="flex space-x-2 mb-3">
                        <select id="test_selector" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900">
                            <option value="">-- Add Individual Test --</option>
                            @foreach($labTests as $t)
                                <option value="{{ $t->id }}">[{{ $t->code }}] {{ $t->name }} ({{ $t->labSection?->name ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="addSelectedTest()" class="px-4 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-lg transition-colors whitespace-nowrap">Add Test</button>
                    </div>
                    <div class="flex space-x-2 mb-3">
                        <select id="panel_selector" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900">
                            <option value="">-- Add Panel (Profile) --</option>
                            @foreach($labPanels as $pl)
                                <option value="{{ $pl->id }}">[{{ $pl->code }}] {{ $pl->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="addSelectedPanel()" class="px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors whitespace-nowrap">Add Panel</button>
                    </div>
                    @error('lineItems') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Item</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Type</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Charge</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-16"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($lineItems as $index => $li)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-gray-800">{{ $li['test_name'] }}</td>
                                        <td class="px-4 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $li['item_type'] === 'Panel' ? 'bg-indigo-50 text-indigo-700' : 'bg-blue-50 text-blue-700' }}">{{ $li['item_type'] }}</span></td>
                                        <td class="px-4 py-2 text-right">
                                            <input wire:model="lineItems.{{ $index }}.charge_amount" type="number" step="0.01" min="0" class="w-28 px-2 py-1 text-right bg-gray-50 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-light-blue-500">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button type="button" wire:click="removeLineItem({{ $index }})" class="p-1 text-red-400 hover:text-red-600 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No test items added yet. Use the dropdowns above to add tests or panels.</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Total Charge:</td>
                                    <td class="px-4 py-2 text-right text-sm font-bold text-gray-900">&#8369;{{ number_format((float) $total_charge, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div>
                    <label for="request_notes" class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea wire:model="request_notes" id="request_notes" rows="2" placeholder="Optional clinical notes or special instructions" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 @error('request_notes') border-red-300 bg-red-50 @enderror"></textarea>
                    @error('request_notes') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Update' : 'Submit Request' }}</span>
                        <span wire:loading wire:target="save" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @endif

    <script>
        window.addSelectedTest = function() {
            const sel = document.getElementById('test_selector');
            if (!sel) return;
            const id = sel.value;
            if (id) { @this.call('addTest', parseInt(id)); sel.value = ''; }
        };
        window.addSelectedPanel = function() {
            const sel = document.getElementById('panel_selector');
            if (!sel) return;
            const id = sel.value;
            if (id) { @this.call('addPanel', parseInt(id)); sel.value = ''; }
        };
    </script>

    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Cancel Lab Request</h3>
                <p class="text-sm text-gray-500 mb-6">This lab request and all its items will be cancelled. No data is permanently deleted.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
                    <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showDetailModal && $detailRequest)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Lab Request Details</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Request Code</span>
                        <p class="text-sm font-mono font-medium text-gray-900 mt-0.5">{{ $detailRequest->request_code }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailRequest->status }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailRequest->patient_name }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailRequest->priority }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Requesting Department</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailRequest->requestingDepartment?->dept_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Billing Status</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailRequest->billing_status }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Requesting Doctor</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailRequest->requestingDoctor?->full_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Charge</span>
                        <p class="text-sm font-mono font-medium text-gray-900 mt-0.5">&#8369;{{ number_format($detailRequest->total_charge, 2) }}</p>
                    </div>
                </div>
                @if($detailRequest->request_notes)
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</span>
                    <p class="text-sm text-gray-700 mt-0.5">{{ $detailRequest->request_notes }}</p>
                </div>
                @endif
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Test Items</span>
                    <div class="mt-2 overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Test Name</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase">Type</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase">Result</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase">Flag</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Charge</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-28">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($detailRequest->items as $ri)
                                    @php $is = $ri->status; @endphp
                                    <tr class="hover:bg-gray-50 {{ $ri->flag === 'H' || $ri->flag === 'L' || $ri->flag === 'Critical' ? 'bg-red-50/30' : '' }}">
                                        <td class="px-4 py-2 text-gray-800">{{ $ri->test_name }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $ri->item_type === 'Panel' ? 'bg-indigo-50 text-indigo-700' : 'bg-blue-50 text-blue-700' }}">{{ $ri->item_type }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-center font-mono text-xs">
                                            @if($is === 'Completed')
                                                {{ $ri->result_value ?? $ri->result_categorical ?? ($ri->result_text ? \Str::limit($ri->result_text, 20) : '--') }}
                                            @else
                                                <span class="text-gray-300">--</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            @if($ri->flag)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold border {{ $ri->flag === 'H' ? 'bg-red-100 text-red-800 border-red-200' : ($ri->flag === 'L' ? 'bg-amber-100 text-amber-800 border-amber-200' : ($ri->flag === 'N' ? 'bg-green-100 text-green-800 border-green-200' : ($ri->flag === 'Abnormal' ? 'bg-red-100 text-red-800 border-red-200' : ($ri->flag === 'Critical' ? 'bg-red-200 text-red-900 border-red-300' : 'bg-gray-100 text-gray-600 border-gray-200')))) }}">{{ $ri->flag }}</span>
                                            @else
                                                <span class="text-gray-300">--</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="text-xs font-semibold px-1.5 py-0.5 rounded {{ $is === 'Completed' ? 'bg-green-100 text-green-700' : ($is === 'Processing' || $is === 'Collected' ? 'bg-blue-100 text-blue-700' : ($is === 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">{{ $is }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-right font-mono">&#8369;{{ number_format($ri->charge_amount, 2) }}</td>
                                        <td class="px-4 py-2 text-center">
                                            @if($is !== 'Completed' && $is !== 'Cancelled')
                                            <div class="flex items-center justify-center gap-1">
                                                @if($is === 'Pending')
                                                <button wire:click="updateItemStatus({{ $ri->id }}, 'Collected')" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition-colors">Collect</button>
                                                <button wire:click="updateItemStatus({{ $ri->id }}, 'Completed')" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-green-100 text-green-700 hover:bg-green-200 transition-colors">Done</button>
                                                @endif
                                                @if($is === 'Collected')
                                                <button wire:click="updateItemStatus({{ $ri->id }}, 'Processing')" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">Process</button>
                                                <button wire:click="updateItemStatus({{ $ri->id }}, 'Completed')" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-green-100 text-green-700 hover:bg-green-200 transition-colors">Done</button>
                                                @endif
                                                @if($is === 'Processing')
                                                <button wire:click="openResultModal({{ $ri->id }})" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors">Input Results</button>
                                                @endif
                                                @if($is === 'Completed')
                                                <button wire:click="openResultModal({{ $ri->id }})" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">Edit Results</button>
                                                @endif
                                            </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showResultModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl z-10 my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-indigo-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Input Lab Results</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveResult" class="px-6 py-5 space-y-4">
                @if($previewRange || $previewFlag || $previewUnit)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 space-y-2">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference Information</div>
                    <div class="flex items-center space-x-4">
                        @if($previewRange)
                        <div>
                            <span class="text-xs text-gray-500">Range:</span>
                            <span class="text-sm font-semibold text-gray-800 ml-1">{{ $previewRange }}</span>
                        </div>
                        @endif
                        @if($previewUnit)
                        <div>
                            <span class="text-xs text-gray-500">Unit:</span>
                            <span class="text-sm font-semibold text-gray-800 ml-1">{{ $previewUnit }}</span>
                        </div>
                        @endif
                        @if($previewFlag)
                        <div>
                            <span class="text-xs text-gray-500">Flag:</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border ml-1 {{ $previewFlag === 'H' ? 'bg-red-100 text-red-800 border-red-200' : ($previewFlag === 'L' ? 'bg-amber-100 text-amber-800 border-amber-200' : ($previewFlag === 'N' ? 'bg-green-100 text-green-800 border-green-200' : ($previewFlag === 'Abnormal' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-green-100 text-green-800 border-green-200'))) }}">{{ $previewFlag }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="result_value" class="block text-sm font-medium text-gray-700 mb-1.5">Numeric Value</label>
                        <input wire:model.live="result_value" id="result_value" type="text" placeholder="e.g. 14.5" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="result_categorical" class="block text-sm font-medium text-gray-700 mb-1.5">Category / Status</label>
                        <input wire:model.live="result_categorical" id="result_categorical" type="text" placeholder="e.g. Normal, High, Positive" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label for="result_text" class="block text-sm font-medium text-gray-700 mb-1.5">Text Result (Long Form)</label>
                    <textarea wire:model="result_text" id="result_text" rows="3" placeholder="Detailed textual results or observations" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                </div>
                <div>
                    <label for="result_notes" class="block text-sm font-medium text-gray-700 mb-1.5">Clinical Notes / Comments</label>
                    <textarea wire:model="result_notes" id="result_notes" rows="2" placeholder="Any internal notes or remarks by MedTech" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 transition-all focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
                        Save & Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
