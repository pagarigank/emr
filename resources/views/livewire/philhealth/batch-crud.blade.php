<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Claim Batches</h2>
            <p class="text-sm text-gray-500 mt-1">Group claims into transmittal batches for e-Claims XML submission.</p>
        </div>
        <button wire:click="openCreate"
                class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors duration-200">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Batch
        </button>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search batch code…"
                   class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm shadow-sm"/>
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage"
                    class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    @foreach([['batch_code', 'Batch #'], ['status', 'Status'], ['total_claims', 'Claims'], ['total_amount', 'Total Amount'], ['submission_date', 'Submitted'], ['created_date', 'Created']] as [$field, $label])
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('{{ $field }}')">
                        <span class="inline-flex items-center gap-1">{{ $label }}
                            <span class="text-gray-300">@if($sortField === $field){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span>
                        </span>
                    </th>
                    @endforeach
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($items as $item)
                    <tr class="hover:bg-light-blue-50/40 transition-colors duration-150">
                        <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $item->batch_code }}</td>
                        <td class="px-5 py-4">
                            @php $colors = ['Draft' => 'bg-gray-50 text-gray-600 border-gray-200', 'Submitted' => 'bg-blue-50 text-blue-700 border-blue-200', 'Acknowledged' => 'bg-green-50 text-green-700 border-green-200']; @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border shadow-sm {{ $colors[$item->status] ?? 'bg-gray-50 text-gray-600' }}">{{ $item->status }}</span>
                            @if($item->ecs_tracking_no)<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-purple-50 text-purple-700" title="ECS Uploaded">ECS</span>@endif
                            @if($item->cloud_folder)<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-cyan-50 text-cyan-700" title="Cloud Uploaded">CLOUD</span>@endif
                        </td>
                        <td class="px-5 py-4 text-gray-800 font-medium">{{ $item->claims_count }}</td>
                        <td class="px-5 py-4 font-mono text-gray-800">&#x20B1;{{ number_format($item->total_amount, 2) }}</td>
                        <td class="px-5 py-4 text-gray-500 text-xs">{{ $item->submission_date?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-500 text-xs">{{ $item->created_date?->format('Y-m-d') }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-1.5">
                                <button wire:click="openDetail({{ $item->id }})" class="p-1.5 text-indigo-500 hover:bg-indigo-100 rounded-md transition-colors" title="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button wire:click="downloadBatchXml({{ $item->id }})" class="p-1.5 text-emerald-500 hover:bg-emerald-100 rounded-md transition-colors" title="Download XML"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></button>
                                @if($item->status === 'Draft')
                                    <button wire:click="confirmSubmit({{ $item->id }})" class="p-1.5 text-blue-500 hover:bg-blue-100 rounded-md transition-colors" title="Submit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg><span class="text-lg font-medium text-gray-900">No batches found</span><p class="mt-1 text-sm">Create a batch to group claims for submission.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>

    {{-- Claim Selection Modal --}}
    @if($showClaimSelectModal)
    <div class="fixed inset-0 z-50 flex items-start justify-center p-4 sm:p-6 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl z-10 my-8 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50/50 sticky top-0 z-10">
                <h3 class="text-lg font-semibold text-gray-900">New Batch: <span class="font-mono">{{ $batch_code }}</span></h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                <p class="text-sm text-gray-600 mb-4">Select claims to include in this batch. Only Draft and For Submission claims are shown.</p>

                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date From</label>
                        <input wire:model.live="date_from" type="date"
                               class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date To</label>
                        <input wire:model.live="date_to" type="date"
                               class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                        <input wire:model.live="search" type="text" placeholder="Claim code or diagnosis…"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500">
                    </div>
                </div>

                <div class="flex items-center justify-between mb-3">
                    <div class="flex space-x-2">
                        <button type="button" wire:click="selectAllClaims" class="text-xs px-3 py-1.5 bg-light-blue-50 text-light-blue-700 rounded-lg hover:bg-light-blue-100 transition-colors">Select All</button>
                        <button type="button" wire:click="deselectAllClaims" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">Deselect All</button>
                    </div>
                    <span class="text-xs text-gray-500">{{ count($selectedClaimIds) }} selected</span>
                </div>

                @if(count($availableClaims) > 0)
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr class="text-xs text-gray-600 uppercase">
                                    <th class="px-3 py-2 w-10"></th>
                                    <th class="px-3 py-2 text-left">Claim #</th>
                                    <th class="px-3 py-2 text-left">Patient</th>
                                    <th class="px-3 py-2 text-left">Diagnosis</th>
                                    <th class="px-3 py-2 text-right">Amount</th>
                                    <th class="px-3 py-2 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($availableClaims as $ac)
                                <tr class="hover:bg-gray-50 transition-colors {{ in_array($ac->id, $selectedClaimIds) ? 'bg-light-blue-50/50' : '' }}">
                                    <td class="px-3 py-2 text-center">
                                        <input type="checkbox" value="{{ $ac->id }}" wire:model="selectedClaimIds"
                                               class="rounded border-gray-300 text-light-blue-600 focus:ring-light-blue-500">
                                    </td>
                                    <td class="px-3 py-2 font-mono font-medium">{{ $ac->claim_code }}</td>
                                    <td class="px-3 py-2">{{ $ac->patient?->last_name }}, {{ $ac->patient?->first_name }}</td>
                                    <td class="px-3 py-2 text-gray-600 max-w-[200px] truncate">{{ $ac->admitting_diagnosis ?? '—' }}</td>
                                    <td class="px-3 py-2 font-mono text-right">&#x20B1;{{ number_format($ac->total_claimed_amount, 2) }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $ac->status === 'For Submission' ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-50 text-gray-600' }}">{{ $ac->status }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm font-medium text-gray-900">No available claims</p>
                        <p class="text-xs mt-1">All unbatched claims matching your filters.</p>
                    </div>
                @endif

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Batch Remarks (optional)</label>
                    <textarea wire:model="remarks" rows="2"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 hover:bg-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="createBatch" @if(count($selectedClaimIds) === 0) disabled @endif
                            class="px-5 py-2.5 text-sm font-medium text-white rounded-xl shadow-sm transition-colors {{ count($selectedClaimIds) > 0 ? 'bg-light-blue-600 hover:bg-light-blue-700' : 'bg-gray-300 cursor-not-allowed' }}">
                        <span wire:loading.remove wire:target="createBatch">Create Batch ({{ count($selectedClaimIds) }} claims)</span>
                        <span wire:loading wire:target="createBatch" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Creating…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetailModal && $detailBatch)
    <div class="fixed inset-0 z-50 flex items-start justify-center p-4 sm:p-6 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 my-8 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-indigo-50/50 sticky top-0 z-10">
                <h3 class="text-lg font-semibold text-gray-900">Batch Details <span class="text-sm font-mono text-gray-500 ml-2">{{ $detailBatch->batch_code }}</span></h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500 font-medium">Batch Code:</span><br><span class="text-gray-900 font-mono">{{ $detailBatch->batch_code }}</span></div>
                    <div><span class="text-gray-500 font-medium">Status:</span><br>
                        @php $colors = ['Draft' => 'bg-gray-50 text-gray-600', 'Submitted' => 'bg-blue-50 text-blue-700', 'Acknowledged' => 'bg-green-50 text-green-700']; @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $colors[$detailBatch->status] ?? 'bg-gray-50' }}">{{ $detailBatch->status }}</span>
                    </div>
                    <div><span class="text-gray-500 font-medium">Total Claims:</span><br><span class="text-gray-900 font-semibold">{{ $detailBatch->total_claims }}</span></div>
                    <div><span class="text-gray-500 font-medium">Total Amount:</span><br><span class="text-gray-900 font-semibold font-mono">&#x20B1;{{ number_format($detailBatch->total_amount, 2) }}</span></div>
                    <div><span class="text-gray-500 font-medium">Submitted:</span><br><span class="text-gray-900">{{ $detailBatch->submission_date?->format('M d, Y H:i') ?? '—' }}</span></div>
                    <div><span class="text-gray-500 font-medium">Acknowledged:</span><br><span class="text-gray-900">{{ $detailBatch->acknowledgement_date?->format('M d, Y H:i') ?? '—' }}</span></div>
                    @if($detailBatch->ecs_tracking_no)
                    <div><span class="text-gray-500 font-medium">ECS Tracking No:</span><br><span class="text-gray-900 font-mono text-xs">{{ $detailBatch->ecs_tracking_no }}</span></div>
                    <div><span class="text-gray-500 font-medium">ECS Uploaded:</span><br><span class="text-gray-900">{{ $detailBatch->ecs_uploaded_at?->format('M d, Y H:i') ?? '—' }}</span></div>
                    @endif
                    @if($detailBatch->cloud_folder)
                    <div><span class="text-gray-500 font-medium">Cloud Folder:</span><br><span class="text-gray-900 font-mono text-xs">{{ $detailBatch->cloud_folder }}</span></div>
                    <div><span class="text-gray-500 font-medium">Cloud Uploaded:</span><br><span class="text-gray-900">{{ $detailBatch->cloud_uploaded_at?->format('M d, Y H:i') ?? '—' }}</span></div>
                    @endif
                    @if($detailBatch->acknowledgment_code)
                    <div class="col-span-2"><span class="text-gray-500 font-medium">Acknowledgment Code:</span><br><span class="text-gray-900 font-mono">{{ $detailBatch->acknowledgment_code }}</span></div>
                    @endif
                    @if($detailBatch->voucher_reference)
                    <div class="col-span-2"><span class="text-gray-500 font-medium">Voucher Reference:</span><br><span class="text-gray-900">{{ $detailBatch->voucher_reference }}</span></div>
                    @endif
                    @if($detailBatch->remarks)
                    <div class="col-span-2"><span class="text-gray-500 font-medium">Remarks:</span><br><span class="text-gray-900">{{ $detailBatch->remarks }}</span></div>
                    @endif
                </div>

                @if($detailBatch->claims->count() > 0)
                <div class="pt-3 border-t border-gray-100">
                    <h5 class="text-sm font-semibold text-gray-700 mb-2">Claims in Batch ({{ $detailBatch->claims->count() }})</h5>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead><tr class="text-gray-500 uppercase"><th class="px-2 py-1 text-left">Claim #</th><th class="px-2 py-1 text-left">Patient</th><th class="px-2 py-1 text-left">Diagnosis</th><th class="px-2 py-1 text-right">Amount</th><th class="px-2 py-1 text-center">Status</th><th class="px-2 py-1"></th></tr></thead>
                            <tbody>
                                @foreach($detailBatch->claims as $bc)
                                <tr class="border-t border-gray-100">
                                    <td class="px-2 py-1.5 font-mono">{{ $bc->claim_code }}</td>
                                    <td class="px-2 py-1.5">{{ $bc->patient?->last_name }}, {{ $bc->patient?->first_name }}</td>
                                    <td class="px-2 py-1.5 text-gray-600 max-w-[180px] truncate">{{ $bc->admitting_diagnosis ?? '—' }}</td>
                                    <td class="px-2 py-1.5 text-right font-mono">&#x20B1;{{ number_format($bc->total_claimed_amount, 2) }}</td>
                                    <td class="px-2 py-1.5 text-center"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold {{ $bc->status === 'Submitted' ? 'bg-blue-50 text-blue-700' : 'bg-yellow-50 text-yellow-700' }}">{{ $bc->status }}</span></td>
                                    <td class="px-2 py-1.5">
                                        <a href="{{ route('philhealth.claims') }}" class="text-light-blue-600 hover:text-light-blue-800" title="View Claim">&#x2197;</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <div class="flex flex-wrap justify-between pt-4 border-t border-gray-100 gap-2">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="downloadBatchXml({{ $detailBatch->id }})" class="px-4 py-2.5 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-xl transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Download XML
                        </button>
                        @if(in_array($detailBatch->status, ['Submitted', 'Acknowledged']))
                            <button type="button" wire:click="openEcsUpload({{ $detailBatch->id }})" class="px-4 py-2.5 text-sm font-medium text-purple-700 bg-purple-50 border border-purple-200 hover:bg-purple-100 rounded-xl transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                Upload to ECS
                            </button>
                            <button type="button" wire:click="openCloudUpload({{ $detailBatch->id }})" class="px-4 py-2.5 text-sm font-medium text-cyan-700 bg-cyan-50 border border-cyan-200 hover:bg-cyan-100 rounded-xl transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                                Upload to Cloud
                            </button>
                            @if($detailBatch->status !== 'Acknowledged')
                                <button type="button" wire:click="openAcknowledgment({{ $detailBatch->id }})" class="px-4 py-2.5 text-sm font-medium text-green-700 bg-green-50 border border-green-200 hover:bg-green-100 rounded-xl transition-colors">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Acknowledge
                                </button>
                            @endif
                            <button type="button" wire:click="openResubmitBatch({{ $detailBatch->id }})" class="px-4 py-2.5 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 rounded-xl transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Resubmit Denied
                            </button>
                        @endif
                    </div>
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ECS Upload Modal --}}
    @if($showEcsUploadModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-purple-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Upload to ECS</h3>
                <p class="text-sm text-gray-500 mb-4">Send this batch XML to the PhilHealth ECS web service (eClaimsUpload).</p>

                @if($ecsResultMessage)
                    <div class="w-full bg-gray-50 rounded-lg px-4 py-3 mb-4 text-sm {{ str_contains($ecsResultMessage, 'successful') ? 'text-green-700' : 'text-red-700' }}">
                        {{ $ecsResultMessage }}
                    </div>
                @endif

                <button type="button" wire:click="uploadBatchToEcs" wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-sm transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="uploadBatchToEcs">Upload Now</span>
                    <span wire:loading wire:target="uploadBatchToEcs" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Uploading…</span>
                </button>
                <button type="button" wire:click="closeModal" class="mt-3 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Cloud Upload Modal --}}
    @if($showCloudUploadModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-cyan-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Upload to PhilHealth Cloud</h3>
                <p class="text-sm text-gray-500 mb-4">Upload this batch XML to the PhilHealth eCCSA Cloud Storage.</p>

                @if($cloudResultMessage)
                    <div class="w-full bg-gray-50 rounded-lg px-4 py-3 mb-4 text-sm {{ str_contains($cloudResultMessage, 'successful') ? 'text-green-700' : 'text-red-700' }}">
                        {{ $cloudResultMessage }}
                    </div>
                @endif

                <button type="button" wire:click="uploadBatchToCloud" wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-xl shadow-sm transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="uploadBatchToCloud">Upload Now</span>
                    <span wire:loading wire:target="uploadBatchToCloud" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Uploading…</span>
                </button>
                <button type="button" wire:click="closeModal" class="mt-3 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Acknowledgment Modal --}}
    @if($showAcknowledgmentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 p-6">
            <div class="text-center">
                <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4"><svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Acknowledge Batch</h3>
                <p class="text-sm text-gray-500 mb-4">Enter the PhilHealth acknowledgment/reference code for this batch.</p>

                <div class="text-left mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Acknowledgment Code <span class="text-red-500">*</span></label>
                    <input wire:model="acknowledgment_code" type="text"
                           class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500"
                           placeholder="e.g., PH-ACK-2026-xxxxx">
                    @error('acknowledgment_code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <button type="button" wire:click="acknowledgeBatch" wire:loading.attr="disabled"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-xl shadow-sm transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="acknowledgeBatch">Confirm Acknowledgment</span>
                    <span wire:loading wire:target="acknowledgeBatch" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Saving…</span>
                </button>
                <button type="button" wire:click="closeModal" class="mt-3 w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Submit Confirmation --}}
    @if($showSubmitModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Submit Batch</h3>
                <p class="text-sm text-gray-500 mb-6">All claims in this batch will be marked as <strong>Submitted</strong>. The batch XML will be ready for download. Continue?</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="submitBatch" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-xl transition-colors">Yes, Submit</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Resubmit Batch Confirmation --}}
    @if($showResubmitBatchModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Resubmit Denied Claims</h3>
                <p class="text-sm text-gray-500 mb-6">All Denied claims in this batch will be reset to <strong>For Submission</strong>. Latest pending rejections will be marked as Resubmitted.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="resubmitBatch" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Resubmit</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Archive Batch</h3>
                <p class="text-sm text-gray-500 mb-6">Claims will be unlinked from this batch and set back to Draft. No data is permanently deleted.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Archive</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
