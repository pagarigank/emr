<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Lab Results</h2>
            <p class="text-sm text-gray-500 mt-1">Enter, review, and validate laboratory test results.</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center space-x-2">
            <button wire:click="$set('filterStatus', 'all')" class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $filterStatus === 'all' ? 'bg-light-blue-100 text-light-blue-800 border border-light-blue-200' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">All</button>
            <button wire:click="$set('filterStatus', 'pending')" class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $filterStatus === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Pending Results</button>
            <button wire:click="$set('filterStatus', 'completed')" class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $filterStatus === 'completed' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Completed</button>
            <button wire:click="$set('filterStatus', 'validated')" class="px-3 py-2 text-xs font-medium rounded-lg transition-colors {{ $filterStatus === 'validated' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Validated</button>
        </div>
        <div class="flex items-center space-x-2">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search test, patient, or request&hellip;" class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm shadow-sm">
            </div>
            <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 transition-all duration-200 hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-light-blue-500/20 focus:border-light-blue-500 sm:text-sm rounded-xl shadow-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('test_name')">
                        <span class="inline-flex items-center gap-1">Test Name<span class="text-gray-300">@if($sortField === 'test_name'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient / Request</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Section</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Result</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Flag</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reference Range</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Validated</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($items as $item)
                    @php $ps = $item->status; @endphp
                    <tr class="hover:bg-light-blue-50/40 transition-colors duration-150 {{ $item->flag === 'H' || $item->flag === 'L' || $item->flag === 'Critical' ? 'bg-red-50/30' : '' }}">
                        <td class="px-5 py-4 font-medium text-gray-900">{{ $item->test_name }}</td>
                        <td class="px-5 py-4">
                            <div class="text-gray-800 font-medium">{{ $item->labRequest?->patient_name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $item->labRequest?->request_code ?? '' }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $item->labTest?->labSection?->name ?? '' }}</td>
                        <td class="px-5 py-4 text-center font-mono text-gray-800">
                            {{ $item->result_value ?? $item->result_categorical ?? ($item->result_text ? \Str::limit($item->result_text, 30) : '--') }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($item->flag)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border {{ $item->flag_badge_class }}">{{ $item->flag }}</span>
                            @else
                                <span class="text-gray-300">--</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-600 text-xs">{{ $item->reference_range_text }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $sClass = match($ps) {
                                    'Completed' => 'bg-green-50 text-green-700 border-green-200',
                                    'Processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'Collected' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'Cancelled' => 'bg-gray-50 text-gray-600 border-gray-200',
                                    default => 'bg-blue-50 text-blue-700 border-blue-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $sClass }}">{{ $ps }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($item->validated_by)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border bg-indigo-50 text-indigo-700 border-indigo-200">Yes</span>
                            @else
                                <span class="text-gray-300">--</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-1">
                                <button wire:click="openDetail({{ $item->id }})" class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                @if($ps !== 'Cancelled')
                                    @if($ps !== 'Completed')
                                        <button wire:click="openResultEntry({{ $item->id }})" class="p-1.5 text-indigo-500 hover:bg-indigo-100 rounded-md transition-colors" title="Enter Results">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    @else
                                        <button wire:click="openResultEntry({{ $item->id }})" class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors" title="Edit Results">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                    @endif
                                    @if($ps === 'Completed' && !$item->validated_by)
                                        <button wire:click="openValidate({{ $item->id }})" class="p-1.5 text-emerald-500 hover:bg-emerald-100 rounded-md transition-colors" title="Validate">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif
                                    @if($ps === 'Completed')
                                        <a href="{{ route('lab.result.print', $item->id) }}" target="_blank" class="p-1.5 text-light-blue-500 hover:bg-light-blue-100 rounded-md transition-colors inline-block" title="Print">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                            <span class="text-lg font-medium text-gray-900">No results found</span>
                            <p class="mt-1 text-sm">Adjust filters or search criteria.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $detailItem)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-light-blue-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Result Details</h3>
                <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->labRequest?->patient_name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500 font-mono">{{ $detailItem->labRequest?->patient?->patient_code ?? '' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Request Code</span>
                        <p class="text-sm font-mono font-medium text-gray-900 mt-0.5">{{ $detailItem->labRequest?->request_code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Test Name</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->test_name }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Section / Unit</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->labTest?->labSection?->name ?? 'N/A' }} / {{ $detailItem->labTest?->labUnit?->name ?? '' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Result Value</span>
                        <p class="text-sm font-mono font-medium text-gray-900 mt-0.5">{{ $detailItem->result_value ?? $detailItem->result_categorical ?? $detailItem->result_text ?? '--' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Flag</span>
                        <p class="mt-0.5">@if($detailItem->flag)<span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold border {{ $detailItem->flag_badge_class }}">{{ $detailItem->flag }}</span>@else<span class="text-sm text-gray-400">--</span>@endif</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference Range</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->reference_range_text }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->status }}</p>
                    </div>
                </div>
                @if($detailItem->result_notes)
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">MedTech Notes</span>
                    <p class="text-sm text-gray-700 mt-0.5 bg-amber-50 rounded-lg p-3 border border-amber-100">{{ $detailItem->result_notes }}</p>
                </div>
                @endif
                <div class="border-t border-gray-100 pt-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Audit Trail</span>
                    <div class="mt-2 space-y-1 text-xs text-gray-600">
                        <div>Performed by: <span class="font-medium">{{ $detailItem->performedBy?->full_name ?? '--' }}</span> @if($detailItem->result_date)on {{ $detailItem->result_date->format('M d, Y h:i A') }}@endif</div>
                        <div>Verified by: <span class="font-medium">{{ $detailItem->verifiedBy?->full_name ?? '--' }}</span> @if($detailItem->verified_date)on {{ $detailItem->verified_date->format('M d, Y h:i A') }}@endif</div>
                        <div>Validated by: <span class="font-medium">{{ $detailItem->validatedBy?->full_name ?? '--' }}</span> @if($detailItem->validated_at)on {{ $detailItem->validated_at->format('M d, Y h:i A') }}@endif</div>
                    </div>
                </div>
                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="button" wire:click="$set('showDetailModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Result Entry Modal --}}
    @if($showResultModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="$set('showResultModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl z-10 my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-indigo-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Input Lab Results</h3>
                <button wire:click="$set('showResultModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveResult" class="px-6 py-5 space-y-4">
                @if($previewRange || $previewFlag || $previewUnit)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 space-y-2">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference Information</div>
                    <div class="flex items-center space-x-4">
                        @if($previewRange)
                        <div>
                            <span class="text-xs text-gray-500">Reference Range:</span>
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
                    <button type="button" wire:click="$set('showResultModal', false)" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
                        Save & Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Validation Modal --}}
    @if($showValidateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="$set('showValidateModal', false)"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
            <div class="flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center mb-4">
                    <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Validate Result</h3>
                <p class="text-sm text-gray-500 mb-6">Confirm that this lab result has been reviewed and validated by a pathologist.</p>
                <div class="flex space-x-3 w-full">
                    <button type="button" wire:click="$set('showValidateModal', false)" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="button" wire:click="validateResult" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors">Yes, Validate</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
