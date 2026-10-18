<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">CSR Dispensing</h2>
            <p class="text-sm text-gray-500 mt-1">Create and manage supply requests from all departments.</p>
        </div>
        <button wire:click="openCreate" class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>New Request</button>
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search request code or person&hellip;" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm shadow-sm"/></div>
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
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient / Requesting Person</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Items</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Amount</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($items as $item)
                    <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $item->request_code }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->department_name }}</td>
                        <td class="px-5 py-4 text-gray-800">{{ $item->patient_name }}</td>
                        <td class="px-5 py-4 text-center font-mono text-gray-700">{{ $item->items->count() }}</td>
                        <td class="px-5 py-4 text-right font-mono text-gray-700">&#8369;{{ number_format($item->total_amount, 2) }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $sClass = match($item->status) {
                                    'Approved' => 'bg-green-50 text-green-700 border-green-200',
                                    'Partial' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'Complete' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    default => 'bg-amber-50 text-amber-700 border-amber-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $sClass }}">{{ $item->status }}</span>
                        </td>
                        <td class="px-5 py-4"><div class="flex items-center justify-center space-x-2">
                            <button wire:click="viewDetail({{ $item->id }})" class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors" title="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                            <button wire:click="openEdit({{ $item->id }})" class="p-1.5 text-light-blue-600 hover:bg-light-blue-100 rounded-md transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                            <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Cancel Request"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            @if($item->status !== 'Complete' && $item->status !== 'Cancelled')
                            <button wire:click="completeAllItems({{ $item->id }})" wire:confirm="Mark all items as Complete?" class="p-1.5 text-green-500 hover:bg-green-100 rounded-md transition-colors" title="Complete All">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <button wire:click="openDispense({{ $item->id }})" class="p-1.5 text-amber-500 hover:bg-amber-100 rounded-md transition-colors" title="Dispense Items">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </button>
                            @endif

                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg><span class="text-lg font-medium text-gray-900">No CSR requests found</span><p class="mt-1 text-sm">Create a new supply request to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-8 pb-8 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 z-10 border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isEditing ? 'Edit' : 'New' }} CSR Request</h3>
                <button wire:click="closeModal" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="save" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Request Code</label>
                        <input type="text" readonly wire:model="request_code" class="block w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 sm:text-sm shadow-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Requesting Department</label>
                        <select wire:model="requesting_department_id" class="block w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-900 sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                            <option value="">— Select Department —</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->dept_name }}</option>
                            @endforeach
                        </select>
                        @error('requesting_department_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Requesting Person</label>
                        <input type="text" wire:model="requesting_person" placeholder="e.g., Nurse Juan" class="block w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-900 sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                        @error('requesting_person') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea wire:model="notes" rows="2" placeholder="Optional notes about this request" class="block w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-900 sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">Request Items</label>
                        <div class="flex items-center space-x-2">
                            <select id="item-selector" class="block w-64 px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-gray-900 sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                <option value="">— Select Supply Item —</option>
                                @foreach($supplyItems as $si)
                                    <option value="{{ $si->id }}" data-cost="{{ $si->unit_cost }}" data-unit="{{ $si->unit?->name ?? '' }}" data-code="{{ $si->code }}">{{ $si->code }} — {{ $si->name }} (&#8369;{{ number_format($si->unit_cost, 2) }})</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="addCsrItem()" class="inline-flex items-center px-3 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors">Add</button>
                        </div>
                    </div>
                    @error('lineItems') <p class="mt-1 text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Item</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-24">Qty</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase w-28">Unit Cost</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase w-28">Total</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-16"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($lineItems as $index => $li)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-4 py-2.5">
                                            <div class="text-gray-800 font-medium">{{ $li['item_name'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $li['item_code'] ?? '' }}{{ $li['supply_unit_name'] ? ' | ' . $li['supply_unit_name'] : '' }}</div>
                                            <input type="hidden" wire:model="lineItems.{{ $index }}.item_id">
                                            <input type="hidden" wire:model="lineItems.{{ $index }}.item_name">
                                            <input type="hidden" wire:model="lineItems.{{ $index }}.item_code">
                                            <input type="hidden" wire:model="lineItems.{{ $index }}.supply_unit_name">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="number" step="0.01" min="0.01" wire:model.live="lineItems.{{ $index }}.quantity" class="block w-full px-2.5 py-1.5 text-center bg-white border border-gray-200 rounded-lg text-gray-900 sm:text-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="number" step="0.01" min="0" wire:model.live="lineItems.{{ $index }}.unit_cost" class="block w-full px-2.5 py-1.5 text-right bg-white border border-gray-200 rounded-lg text-gray-900 sm:text-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-mono text-gray-700">&#8369;{{ number_format((float)($li['total_cost'] ?? 0), 2) }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            <button type="button" wire:click="removeLineItem({{ $index }})" class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No items added yet. Select an item above and click Add.</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50/80 border-t border-gray-200">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total Amount:</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-gray-900">&#8369;{{ number_format((float)$total_amount, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-light-blue-600 border border-transparent rounded-xl shadow-sm hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors">
                        {{ $isEditing ? 'Update' : 'Create' }} Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetailModal && $detailRequest)
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-8 pb-8 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 z-10 border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">CSR Request Details</h3>
                <div class="flex items-center space-x-2">
                    <a href="{{ url('supply/request/' . $detailRequest->id . '/print') }}" target="_blank" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-sky-700 bg-sky-50 border border-sky-200 rounded-lg hover:bg-sky-100 transition-colors" title="Print This Request">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>Print Request
                    </a>
                    <a href="{{ url('supply/request/' . $detailRequest->id . '/encounter-print') }}" target="_blank" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors" title="Print All Supplies for This Encounter">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Print Encounter Supplies
                    </a>
                    <button wire:click="closeModal" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div><label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Request Code</label><p class="mt-1 text-sm font-mono font-medium text-gray-900">{{ $detailRequest->request_code }}</p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Department</label><p class="mt-1 text-sm text-gray-900">{{ $detailRequest->requestingDepartment?->dept_name ?? '—' }}</p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient / Person</label><p class="mt-1 text-sm text-gray-900">{{ $detailRequest->patient_name }}</p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</label><p class="mt-1"><span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ match($detailRequest->status) { 'Approved' => 'bg-green-50 text-green-700 border-green-200', 'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200', default => 'bg-amber-50 text-amber-700 border-amber-200' } }}">{{ $detailRequest->status }}</span></p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Billing Status</label><p class="mt-1"><span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $detailRequest->billing_status === 'Billed' ? 'bg-green-50 text-green-700 border-green-200' : ($detailRequest->billing_status === 'Exempt' ? 'bg-purple-50 text-purple-700 border-purple-200' : ($detailRequest->billing_status === 'Credited' ? 'bg-gray-50 text-gray-600 border-gray-200' : 'bg-amber-50 text-amber-700 border-amber-200')) }}">{{ $detailRequest->billing_status }}</span></p></div>
                    <div><label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Amount</label><p class="mt-1 text-sm font-mono font-bold text-gray-900">&#8369;{{ number_format($detailRequest->total_amount, 2) }}</p></div>
                </div>
                @if($detailRequest->notes)
                <div><label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</label><p class="mt-1 text-sm text-gray-700">{{ $detailRequest->notes }}</p></div>
                @endif
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Request Items</label>
                    <div class="mt-2 overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Item</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-16">Qty</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-20">Status</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase w-24">Unit Cost</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase w-24">Total</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-28">Actions</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($detailRequest->items as $li)
                                @php $is = $li->status; @endphp
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-2.5"><div class="text-gray-800 font-medium">{{ $li->item_name }}</div><div class="text-xs text-gray-400">{{ $li->item_code ?? '' }}{{ $li->supply_unit_name ? ' | ' . $li->supply_unit_name : '' }}</div></td>
                                    <td class="px-4 py-2.5 text-center font-mono text-gray-700">{{ $li->quantity }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="text-xs font-semibold px-1.5 py-0.5 rounded {{ $is === 'Complete' ? 'bg-green-100 text-green-700' : ($is === 'Approved' || $is === 'Partial' ? 'bg-blue-100 text-blue-700' : ($is === 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">{{ $is }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-700">&#8369;{{ number_format($li->unit_cost, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-700">&#8369;{{ number_format($li->total_cost, 2) }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        @if($is !== 'Complete' && $is !== 'Cancelled')
                                        <div class="flex items-center justify-center gap-1">
                                            @if($is === 'Pending')
                                            <button wire:click="updateItemStatus({{ $li->id }}, 'Approved')" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">Approve</button>
                                            <button wire:click="updateItemStatus({{ $li->id }}, 'Complete')" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-green-100 text-green-700 hover:bg-green-200 transition-colors">Done</button>
                                            @endif
                                            @if($is === 'Approved')
                                            <button wire:click="updateItemStatus({{ $li->id }}, 'Partial')" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors">Partial</button>
                                            <button wire:click="updateItemStatus({{ $li->id }}, 'Complete')" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-green-100 text-green-700 hover:bg-green-200 transition-colors">Done</button>
                                            @endif
                                            @if($is === 'Partial')
                                            <button wire:click="updateItemStatus({{ $li->id }}, 'Complete')" class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-green-100 text-green-700 hover:bg-green-200 transition-colors">Done</button>
                                            @endif
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50/80 border-t border-gray-200"><tr>
                                <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total:</td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-gray-900">&#8369;{{ number_format($detailRequest->total_amount, 2) }}</td>
                            </tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete/Cancel Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 z-10 border border-gray-200 p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-amber-100 mb-4">
                <svg class="h-7 w-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Cancel Request</h3>
            <p class="text-sm text-gray-600 mb-6">Are you sure you want to cancel this request? This action cannot be undone.</p>
            <div class="flex items-center justify-center space-x-3">
                <button wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors">Keep</button>
                <button wire:click="delete" class="px-4 py-2.5 text-sm font-medium text-white bg-amber-600 border border-transparent rounded-xl shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">Yes, Cancel Request</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Dispense Items Modal --}}
    @if($showDispenseModal && $dispensingRequest)
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-8 pb-8 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 z-10 border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Dispense Items</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $dispensingRequest->request_code }} — {{ $dispensingRequest->requestingDepartment?->dept_name ?? 'N/A' }} — {{ $dispensingRequest->patient_name }}</p>
                </div>
                <button wire:click="closeModal" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="processDispense" class="p-6 space-y-5">
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Item</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-20">Requested</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-20">Given</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-24">Qty to Give</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase w-24">Unit Cost</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase w-24">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($dispensingRequest->items as $di)
                                @continue($di->status === 'Complete' || $di->status === 'Cancelled')
                                @php
                                    $remaining = max(0, $di->quantity - $di->given_quantity);
                                    $lineTotal = ((float)($dispensingQuantities[$di->id] ?? 0)) * (float)$di->unit_cost;
                                @endphp
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <div class="text-gray-800 font-medium">{{ $di->item_name }}</div>
                                        <div class="text-xs text-gray-400">{{ $di->item_code ?? '' }}{{ $di->supply_unit_name ? ' | ' . $di->supply_unit_name : '' }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-mono text-gray-700">{{ $di->quantity }}</td>
                                    <td class="px-4 py-2.5 text-center font-mono {{ $di->given_quantity > 0 ? 'text-green-600' : 'text-gray-400' }}">{{ $di->given_quantity }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <input type="number" step="0.01" min="0" max="{{ $remaining }}" wire:model.live="dispensingQuantities.{{ $di->id }}"
                                               class="block w-full px-2 py-1.5 text-center bg-white border border-gray-200 rounded-lg text-gray-900 sm:text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 @if($remaining <= 0) opacity-50 cursor-not-allowed @endif"
                                               @if($remaining <= 0) disabled @endif>
                                        @if($remaining > 0)
                                        <p class="text-[10px] text-gray-400 mt-0.5">max {{ $remaining }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-700">&#8369;{{ number_format($di->unit_cost, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-900 font-semibold">&#8369;{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">All items have been fully dispensed.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-amber-50/50 border-t border-gray-200">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right text-sm font-bold text-gray-800">Total to Dispense:</td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-gray-900">
                                    @php
                                        $dispTotal = 0;
                                        foreach ($dispensingQuantities as $dqi => $dq) {
                                            $dItem = $dispensingRequest->items->firstWhere('id', $dqi);
                                            if ($dItem) $dispTotal += max(0, (float)$dq) * (float)$dItem->unit_cost;
                                        }
                                    @endphp
                                    &#8369;{{ number_format($dispTotal, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="processDispense">Confirm Dispense</span>
                        <span wire:loading wire:target="processDispense" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Processing&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @script
    <script>
        window.addCsrItem = function() {
            const sel = document.getElementById('item-selector');
            if (!sel || !sel.value) return;
            const opt = sel.options[sel.selectedIndex];
            const id = opt.value;
            const name = opt.text.split(' — ')[1] || opt.text;
            const cost = opt.dataset.cost || '0';
            const unit = opt.dataset.unit || '';
            const code = opt.dataset.code || '';
            @this.addItem(parseInt(id));
            sel.selectedIndex = 0;
        };
    </script>
    @endscript
</div>
