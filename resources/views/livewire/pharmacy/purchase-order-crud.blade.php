<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Purchase Order Management</h2>
            <p class="text-sm text-gray-500 mt-1">Create and manage drug procurement from suppliers.</p>
        </div>
        <button wire:click="openCreate" class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>New Purchase Order</button>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search PO code, supplier&hellip;" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm shadow-sm">
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('po_code')">
                        <span class="inline-flex items-center gap-1">PO Code<span class="text-gray-300">@if($sortField === 'po_code'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Supplier</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('order_date')">
                        <span class="inline-flex items-center gap-1">Order Date<span class="text-gray-300">@if($sortField === 'order_date'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Expected</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Items</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('total_amount')">
                        <span class="inline-flex items-center gap-1">Total Amount<span class="text-gray-300">@if($sortField === 'total_amount'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('created_date')">
                        <span class="inline-flex items-center gap-1">Created<span class="text-gray-300">@if($sortField === 'created_date'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($orders as $o)
                    <tr class="hover:bg-emerald-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $o->po_code }}</td>
                        <td class="px-5 py-4 text-gray-800">{{ $o->supplier?->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-center text-gray-700">{{ $o->order_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-5 py-4 text-center text-gray-700">{{ $o->expected_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-5 py-4 text-center font-mono text-gray-700">{{ $o->items->count() }}</td>
                        <td class="px-5 py-4 text-right font-mono text-gray-700">&#8369;{{ number_format($o->total_amount, 2) }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $sClass = match($o->status) {
                                    'Draft' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    'Submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Approved' => 'bg-green-50 text-green-700 border-green-200',
                                    'Partial' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Received' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $sClass }}">{{ $o->status }}</span>
                        </td>
                        <td class="px-5 py-4 text-center text-gray-500 text-xs">{{ $o->created_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-1">
                                <button wire:click="viewDetail({{ $o->id }})" class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                @if($o->status === 'Draft')
                                    <button wire:click="openEdit({{ $o->id }})" class="p-1.5 text-emerald-600 hover:bg-emerald-100 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="submitForApproval({{ $o->id }})" wire:confirm="Submit this purchase order for approval?" class="p-1.5 text-blue-500 hover:bg-blue-100 rounded-md transition-colors" title="Submit for Approval">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $o->id }})" class="p-1.5 text-red-400 hover:bg-red-100 rounded-md transition-colors" title="Cancel">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @elseif($o->status === 'Submitted')
                                    <button wire:click="approve({{ $o->id }})" wire:confirm="Approve this purchase order?" class="p-1.5 text-green-500 hover:bg-green-100 rounded-md transition-colors" title="Approve">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $o->id }})" class="p-1.5 text-red-400 hover:bg-red-100 rounded-md transition-colors" title="Cancel">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @elseif($o->status === 'Approved' || $o->status === 'Partial')
                                    <button wire:click="openReceive({{ $o->id }})" class="p-1.5 text-emerald-500 hover:bg-emerald-100 rounded-md transition-colors" title="Receive Items">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                                    </button>
                                    @if($o->status === 'Approved')
                                        <button wire:click="confirmDelete({{ $o->id }})" class="p-1.5 text-red-400 hover:bg-red-100 rounded-md transition-colors" title="Cancel">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg><span class="text-lg font-medium text-gray-900">No purchase orders found</span><p class="mt-1 text-sm">Create a new purchase order to get started.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-8 pb-8 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 z-10 border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isEditing ? 'Edit' : 'New' }} Purchase Order</h3>
                <button wire:click="closeModal" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="save" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">PO Code</label>
                        <input type="text" readonly wire:model="po_code" class="block w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 sm:text-sm shadow-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Supplier</label>
                        <select wire:model="supplier_id" class="block w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-900 sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">— Select Supplier —</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">[{{ $s->code }}] {{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Order Date</label>
                        <input type="date" wire:model="order_date" class="block w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-900 sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        @error('order_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Expected Date</label>
                        <input type="date" wire:model="expected_date" class="block w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-900 sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        @error('expected_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <textarea wire:model="notes" rows="2" placeholder="Optional notes about this purchase order" class="block w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-900 sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">Line Items</label>
                        <div class="flex items-center space-x-2">
                            <div class="relative">
                                <input wire:model.live.debounce.300ms="drugSearch" type="text" placeholder="Search drug&hellip;" class="block w-72 px-3.5 py-2 bg-white border border-gray-200 rounded-xl text-gray-900 sm:text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                @if($showDrugDropdown && count($drugSearchResults) > 0)
                                    <div class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-20 max-h-48 overflow-y-auto">
                                        @foreach($drugSearchResults as $dr)
                                            <button type="button" wire:click="selectDrug({{ $dr['id'] }})" class="block w-full text-left px-3.5 py-2 text-sm text-gray-700 hover:bg-emerald-50 transition-colors border-b border-gray-100 last:border-b-0">
                                                <span class="font-medium">{{ $dr['drug_code'] }}</span> — {{ $dr['generic_name'] ?: $dr['drug_name'] }}
                                                @if($dr['drug_strength']) <span class="text-gray-400">({{ $dr['drug_strength'] }})</span> @endif
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <button type="button" wire:click="addItem" class="inline-flex items-center px-3 py-2 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">Add</button>
                        </div>
                    </div>
                    @error('lineItems') <p class="mt-1 text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Drug</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-24">Qty Ordered</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase w-28">Unit Cost</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase w-28">Total Cost</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-16"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($lineItems as $index => $li)
                                    <tr class="hover:bg-gray-50/50 align-top">
                                        <td class="px-4 py-2.5">
                                            <div class="text-gray-800 font-medium">{{ $li['drug_name'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $li['drug_code'] ?? '' }}</div>
                                            <input type="hidden" wire:model="lineItems.{{ $index }}.drug_id">
                                            <input type="hidden" wire:model="lineItems.{{ $index }}.drug_name">
                                            <input type="hidden" wire:model="lineItems.{{ $index }}.drug_code">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="number" step="0.01" min="0.01" wire:model.live="lineItems.{{ $index }}.quantity_ordered" class="block w-full px-2 py-1.5 text-center bg-white border border-gray-200 rounded-lg text-gray-900 sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                            @error('lineItems.{{ $index }}.quantity_ordered') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="number" step="0.01" min="0" wire:model.live="lineItems.{{ $index }}.unit_cost" class="block w-full px-2 py-1.5 text-right bg-white border border-gray-200 rounded-lg text-gray-900 sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                            @error('lineItems.{{ $index }}.unit_cost') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-mono font-semibold text-gray-900">&#8369;{{ number_format((float)($li['quantity_ordered'] ?? 0) * (float)($li['unit_cost'] ?? 0), 2) }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            <button type="button" wire:click="removeItem({{ $index }})" class="p-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No items added yet. Search for a drug above and click Add.</td></tr>
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
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-xl shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                        {{ $isEditing ? 'Update' : 'Create' }} Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Receive (GRN) Modal --}}
    @if($showReceiveModal && $receiveOrder)
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-8 pb-8 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 z-10 border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Receive Purchase Order</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $receiveOrder->po_code }} — {{ $receiveOrder->supplier?->name ?? 'N/A' }}</p>
                </div>
                <button wire:click="closeModal" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="processReceive" class="p-6 space-y-5">
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600 uppercase">Drug</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-20">Ordered</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-20">Received</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-24">Qty to Receive</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-28">Batch No.</th>
                                <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 uppercase w-28">Expiry Date</th>
                                <th class="px-4 py-2.5 text-right text-xs font-semibold text-gray-600 uppercase w-24">Unit Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($receiveItems as $itemId => $ri)
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <div class="text-gray-800 font-medium">{{ $ri['drug_name'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $ri['drug_code'] ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-mono text-gray-700">{{ $ri['quantity_ordered'] }}</td>
                                    <td class="px-4 py-2.5 text-center font-mono text-gray-500">{{ $ri['quantity_received'] }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <input type="number" step="0.01" min="0.01" max="{{ $ri['remaining'] }}" wire:model.live="receiveItems.{{ $itemId }}.qty_received" class="block w-full px-2 py-1.5 text-center bg-white border border-gray-200 rounded-lg text-gray-900 sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                        <p class="text-[10px] text-gray-400 mt-0.5">max {{ $ri['remaining'] }}</p>
                                        @error('receiveItems.{{ $itemId }}.qty_received') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <input type="text" wire:model="receiveItems.{{ $itemId }}.batch_no" placeholder="e.g. BATCH001" class="block w-full px-2 py-1.5 text-center bg-white border border-gray-200 rounded-lg text-gray-900 sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                        @error('receiveItems.{{ $itemId }}.batch_no') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <input type="date" wire:model="receiveItems.{{ $itemId }}.expiry_date" class="block w-full px-2 py-1.5 text-center bg-white border border-gray-200 rounded-lg text-gray-900 sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                        @error('receiveItems.{{ $itemId }}.expiry_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono text-gray-700">&#8369;{{ number_format($ri['unit_cost'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">All items have been fully received.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-600 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="processReceive">Process Receive</span>
                        <span wire:loading wire:target="processReceive" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Processing&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetailModal && $detailOrder)
    <div class="fixed inset-0 z-50 flex items-start justify-center pt-8 pb-8 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-4 z-10 border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Purchase Order Details</h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $detailOrder->po_code }} — {{ $detailOrder->supplier?->name ?? 'N/A' }}</p>
                </div>
                <button wire:click="closeModal" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors text-gray-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">PO Code</span><p class="text-sm font-mono font-medium text-gray-900 mt-0.5">{{ $detailOrder->po_code }}</p></div>
                    <div><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</span>
                        <p class="mt-0.5">
                            @php
                                $dsClass = match($detailOrder->status) {
                                    'Draft' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    'Submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Approved' => 'bg-green-50 text-green-700 border-green-200',
                                    'Partial' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Received' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Cancelled' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $dsClass }}">{{ $detailOrder->status }}</span>
                        </p>
                    </div>
                    <div><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</span><p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailOrder->supplier?->name ?? 'N/A' }}</p></div>
                    <div><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier Code</span><p class="text-sm font-mono text-gray-700 mt-0.5">{{ $detailOrder->supplier?->code ?? 'N/A' }}</p></div>
                    <div><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Order Date</span><p class="text-sm text-gray-900 mt-0.5">{{ $detailOrder->order_date?->format('M d, Y') ?? 'N/A' }}</p></div>
                    <div><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Expected Date</span><p class="text-sm text-gray-900 mt-0.5">{{ $detailOrder->expected_date?->format('M d, Y') ?? 'N/A' }}</p></div>
                    <div><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Amount</span><p class="text-sm font-mono font-bold text-gray-900 mt-0.5">&#8369;{{ number_format($detailOrder->total_amount, 2) }}</p></div>
                    <div><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</span><p class="text-sm text-gray-700 mt-0.5">{{ $detailOrder->created_date?->format('M d, Y h:i A') ?? 'N/A' }}</p></div>
                </div>
                @if($detailOrder->notes)
                <div><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</span><p class="text-sm text-gray-700 mt-0.5">{{ $detailOrder->notes }}</p></div>
                @endif
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Line Items</span>
                    <div class="mt-2 overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Drug</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-20">Qty Ordered</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-20">Qty Received</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-28">Batch No.</th>
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase w-24">Expiry</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24">Unit Cost</th>
                                    <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase w-24">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($detailOrder->items as $oi)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-gray-800">{{ $oi->drug?->generic_name ?: ($oi->drug?->drug_name ?? '—') }}</td>
                                        <td class="px-4 py-2 text-center font-mono text-gray-700">{{ $oi->quantity_ordered }}</td>
                                        <td class="px-4 py-2 text-center font-mono {{ $oi->quantity_received > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">{{ $oi->quantity_received ?: '—' }}</td>
                                        <td class="px-4 py-2 text-center font-mono text-gray-600">{{ $oi->batch_no ?? '—' }}</td>
                                        <td class="px-4 py-2 text-center text-gray-600">{{ $oi->expiry_date?->format('M d, Y') ?? '—' }}</td>
                                        <td class="px-4 py-2 text-right font-mono text-gray-700">&#8369;{{ number_format($oi->unit_cost, 2) }}</td>
                                        <td class="px-4 py-2 text-right font-mono text-gray-700">&#8369;{{ number_format($oi->total_cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50/80 border-t border-gray-200">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right text-sm font-bold text-gray-800">Total:</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-gray-900">&#8369;{{ number_format($detailOrder->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
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

    {{-- Cancel Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Cancel Purchase Order</h3>
                <p class="text-sm text-gray-500 mb-6">This action will cancel the purchase order. No data is permanently deleted.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
                    <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
