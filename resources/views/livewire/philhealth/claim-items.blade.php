<div>
    @if($showModal)
        <div class="space-y-4">
            @if(count($items) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase border-b border-gray-200">
                            <th class="px-2 py-2 text-left w-32">Type</th>
                            <th class="px-2 py-2 text-left w-24">Code</th>
                            <th class="px-2 py-2 text-left">Name / Description</th>
                            <th class="px-2 py-2 text-right w-16">Qty</th>
                            <th class="px-2 py-2 text-right w-24">Unit Price</th>
                            <th class="px-2 py-2 text-right w-24">Actual</th>
                            <th class="px-2 py-2 text-right w-24">Claimed</th>
                            <th class="px-2 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $item)
                        <tr class="border-t border-gray-100 hover:bg-gray-50/50">
                            <td class="px-2 py-1.5">
                                <select wire:model="items.{{ $i }}.item_type"
                                        class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                                    <option value="Drug/Medicine">Drug/Medicine</option>
                                    <option value="Laboratory">Laboratory</option>
                                    <option value="Radiology">Radiology</option>
                                    <option value="Procedure">Procedure</option>
                                    <option value="Supply">Supply</option>
                                    <option value="RoomBoard">Room & Board</option>
                                    <option value="ProfessionalFee">Prof. Fee</option>
                                    <option value="Others">Others</option>
                                </select>
                            </td>
                            <td class="px-2 py-1.5">
                                <input wire:model="items.{{ $i }}.item_code" type="text" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-light-blue-500/20" placeholder="RVS">
                            </td>
                            <td class="px-2 py-1.5">
                                <input wire:model="items.{{ $i }}.item_name" type="text" class="w-full min-w-[120px] px-2 py-1.5 bg-white border border-gray-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-light-blue-500/20" placeholder="Item name">
                            </td>
                            <td class="px-2 py-1.5">
                                <input wire:model="items.{{ $i }}.quantity" type="number" step="any" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded text-xs text-right focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                            </td>
                            <td class="px-2 py-1.5">
                                <input wire:model="items.{{ $i }}.unit_price" type="number" step="0.01" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded text-xs text-right focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                            </td>
                            <td class="px-2 py-1.5">
                                <input wire:model="items.{{ $i }}.actual_amount" type="number" step="0.01" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded text-xs text-right font-mono focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                            </td>
                            <td class="px-2 py-1.5">
                                <input wire:model="items.{{ $i }}.claimed_amount" type="number" step="0.01" class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded text-xs text-right font-mono focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                            </td>
                            <td class="px-2 py-1.5">
                                <button type="button" wire:click="removeItem({{ $i }})" class="p-1 text-red-400 hover:text-red-600 transition-colors" title="Remove"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 font-semibold">
                            <td colspan="5" class="px-2 py-2 text-right text-gray-700">Totals:</td>
                            <td class="px-2 py-2 text-right font-mono text-gray-800">&#x20B1;{{ number_format($totalActual, 2) }}</td>
                            <td class="px-2 py-2 text-right font-mono text-light-blue-700">&#x20B1;{{ number_format($totalClaimed, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-8 text-gray-400">
                <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-sm">No items yet. Add items or import from billing.</p>
            </div>
            @endif

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <button type="button" wire:click="addItem" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-light-blue-700 bg-light-blue-50 border border-light-blue-200 rounded-lg hover:bg-light-blue-100 transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Item
                    </button>
                </div>
                <button type="button" wire:click="save" class="px-5 py-2.5 text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">
                    <span wire:loading.remove wire:target="save">Save Items</span>
                    <span wire:loading wire:target="save" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving…</span>
                </button>
            </div>
        </div>
    @else
        <div class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-lg font-medium text-gray-900">No claim loaded</p>
            <p class="text-sm mt-1">Save the claim first, then return to this tab to add items.</p>
        </div>
    @endif
</div>
