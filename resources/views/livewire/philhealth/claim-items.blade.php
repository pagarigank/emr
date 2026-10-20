<div>
    @if(!$claimId)
        <div class="rounded-2xl border border-sky-100 bg-sky-50/50 p-8 text-center max-w-lg mx-auto my-6">
            <div class="w-14 h-14 mx-auto rounded-full bg-sky-100 text-sky-600 flex items-center justify-center mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900">Claim Form Not Yet Saved</h3>
            <p class="text-xs text-gray-600 mt-2 leading-relaxed">
                Items and eSOA billing particulars require an existing claim record. Please fill out and save the <strong>Claim Form</strong> first.
            </p>
            <div class="mt-4">
                <button type="button" wire:click="goToClaimForm"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Go to Claim Form
                </button>
            </div>
        </div>
    @elseif($showModal)
        <div class="space-y-4">
            {{-- Context header bar --}}
            <div class="flex items-center justify-between bg-sky-50/70 border border-sky-100 rounded-xl px-4 py-2.5 text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-sky-900">Itemized eSOA Particulars</span>
                    <span class="text-sky-600">|</span>
                    <span class="font-mono text-sky-700">Claim #{{ $claimId }}</span>
                    <span class="px-2 py-0.5 rounded-full bg-white text-sky-800 font-semibold border border-sky-200">{{ count($items) }} item(s)</span>
                </div>
                <div class="text-xs text-gray-500">
                    Auto-calculates Actual &amp; Claimed amounts as you type.
                </div>
            </div>

            @if(count($items) > 0)
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50/80 border-b border-gray-200">
                        <tr class="text-xs font-semibold text-gray-600 uppercase">
                            <th class="px-3 py-2.5 text-left w-36">Type</th>
                            <th class="px-3 py-2.5 text-left w-28">Code (RVS/Drug)</th>
                            <th class="px-3 py-2.5 text-left">Item Name / Particulars</th>
                            <th class="px-3 py-2.5 text-right w-20">Qty</th>
                            <th class="px-3 py-2.5 text-right w-28">Unit Price</th>
                            <th class="px-3 py-2.5 text-right w-28">Actual (&#x20B1;)</th>
                            <th class="px-3 py-2.5 text-right w-28">Claimed (&#x20B1;)</th>
                            <th class="px-2 py-2.5 w-10 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $i => $item)
                        <tr class="hover:bg-sky-50/30 transition-colors">
                            <td class="px-2 py-2">
                                <select wire:model.live="items.{{ $i }}.item_type"
                                        class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                                    <option value="Drug/Medicine">Drug/Medicine</option>
                                    <option value="Laboratory">Laboratory</option>
                                    <option value="Radiology">Radiology</option>
                                    <option value="Procedure">Procedure</option>
                                    <option value="Supply">Supply</option>
                                    <option value="RoomBoard">Room &amp; Board</option>
                                    <option value="ProfessionalFee">Prof. Fee</option>
                                    <option value="Others">Others</option>
                                </select>
                            </td>
                            <td class="px-2 py-2">
                                <input wire:model.live.debounce.300ms="items.{{ $i }}.item_code" type="text"
                                       class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs font-mono focus:outline-none focus:ring-1 focus:ring-light-blue-500/20" placeholder="RVS/Code">
                            </td>
                            <td class="px-2 py-2">
                                <input wire:model.live.debounce.300ms="items.{{ $i }}.item_name" type="text"
                                       class="w-full min-w-[140px] px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-light-blue-500/20" placeholder="Item name / Description">
                            </td>
                            <td class="px-2 py-2">
                                <input wire:model.live.debounce.300ms="items.{{ $i }}.quantity" type="number" step="any" min="0"
                                       class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs text-right font-medium focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                            </td>
                            <td class="px-2 py-2">
                                <input wire:model.live.debounce.300ms="items.{{ $i }}.unit_price" type="number" step="0.01" min="0"
                                       class="w-full px-2 py-1.5 bg-white border border-gray-200 rounded-lg text-xs text-right font-mono focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                            </td>
                            <td class="px-2 py-2">
                                <input wire:model.live.debounce.300ms="items.{{ $i }}.actual_amount" type="number" step="0.01" min="0"
                                       class="w-full px-2 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-right font-mono text-gray-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                            </td>
                            <td class="px-2 py-2">
                                <input wire:model.live.debounce.300ms="items.{{ $i }}.claimed_amount" type="number" step="0.01" min="0"
                                       class="w-full px-2 py-1.5 bg-sky-50/50 border border-sky-200 rounded-lg text-xs text-right font-mono font-semibold text-light-blue-700 focus:bg-white focus:outline-none focus:ring-1 focus:ring-light-blue-500/20">
                            </td>
                            <td class="px-2 py-2 text-center">
                                <button type="button" wire:click="removeItem({{ $i }})" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Remove Item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50/80 border-t-2 border-gray-300">
                        <tr class="font-bold text-xs">
                            <td colspan="5" class="px-4 py-3 text-right uppercase tracking-wider text-gray-600">Totals:</td>
                            <td class="px-3 py-3 text-right font-mono text-sm text-gray-900">&#x20B1;{{ number_format($totalActual, 2) }}</td>
                            <td class="px-3 py-3 text-right font-mono text-sm text-light-blue-700">&#x20B1;{{ number_format($totalClaimed, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="rounded-xl border border-dashed border-gray-200 bg-white p-8 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-sm font-medium text-gray-700">No claim items added yet</p>
                <p class="text-xs text-gray-400 mt-1">Add items manually below, or switch to the <strong>Clinical Pull-Up</strong> tab to import items directly from billing.</p>
            </div>
            @endif

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                <div class="flex items-center space-x-2 w-full sm:w-auto">
                    <button type="button" wire:click="addItem" class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-light-blue-700 bg-light-blue-50 border border-light-blue-200 rounded-xl hover:bg-light-blue-100 transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Item
                    </button>
                    <button type="button" wire:click="$dispatch('switch-claim-tab', { tab: 'pullup' })" class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import from Billing
                    </button>
                </div>
                <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                    <button type="button" wire:click="save" class="px-5 py-2.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="save">Save Items</span>
                        <span wire:loading wire:target="save" class="flex items-center"><svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-gray-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving…</span>
                    </button>
                    <button type="button" wire:click="saveAndProceed" class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="saveAndProceed">Save &amp; Proceed to CF5 DRG &rarr;</span>
                        <span wire:loading wire:target="saveAndProceed" class="flex items-center"><svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Processing…</span>
                    </button>
                </div>
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
