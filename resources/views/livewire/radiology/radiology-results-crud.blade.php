<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Radiology Results</h2>
            <p class="text-sm text-gray-500 mt-1">Review and manage radiology exam results, readings, and attached images.</p>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <button wire:click="$set('filterStatus', '')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $filterStatus === '' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">All</button>
        <button wire:click="$set('filterStatus', 'pending')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $filterStatus === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Pending Results</button>
        <button wire:click="$set('filterStatus', 'completed')" class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $filterStatus === 'completed' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">Completed</button>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"><svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg></div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search exam name, request code, findings..." class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl leading-5 text-gray-900 transition-all hover:bg-white hover:border-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-sm shadow-sm">
        </div>
        <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span class="font-medium">Rows:</span>
            <select wire:model.live="perPage" class="block w-20 pl-3 pr-8 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl shadow-sm hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 sm:text-sm">
                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50/80 backdrop-blur-sm border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('exam_name')">
                        <span class="inline-flex items-center gap-1">Exam Name<span class="text-gray-300">@if($sortField === 'exam_name'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Patient / Request</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Impression</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Images</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer select-none hover:bg-gray-100/50 transition-colors" wire:click="sortBy('result_date')">
                        <span class="inline-flex items-center gap-1">Result Date<span class="text-gray-300">@if($sortField === 'result_date'){{ $sortDirection === 'asc' ? '↑' : '↓' }}@else&uarr;&darr;@endif</span></span>
                    </th>
                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($items as $item)
                    <tr class="hover:bg-indigo-50/30 transition-colors duration-150">
                        <td class="px-5 py-4">
                            <div class="font-medium text-gray-900">{{ $item->exam_name }}</div>
                            <div class="text-xs text-gray-500">{{ $item->modality_name ?? '' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-gray-800">{{ $item->patient_name }}</div>
                            <div class="text-xs text-gray-500 font-mono">{{ $item->request_code }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $item->report_summary ?: '--' }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border {{ $item->status_badge_class }}">{{ $item->status }}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php $imgCount = $item->image_count; @endphp
                            @if($imgCount > 0)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $imgCount }}
                                </span>
                            @else
                                <span class="text-gray-300">--</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right whitespace-nowrap text-sm text-gray-500">{{ $item->result_date ? $item->result_date->format('M d, Y h:i A') : '--' }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                <button wire:click="viewDetail({{ $item->id }})" class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors" title="View Details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                @if($item->status !== 'Cancelled')
                                <button wire:click="openResultModal({{ $item->id }})" class="p-1.5 text-indigo-600 hover:bg-indigo-100 rounded-md transition-colors" title="{{ $item->status === 'Complete' ? 'Edit Results' : 'Input Results' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                @endif
                                @if($item->status === 'Complete')
                                <a href="{{ route('radiology.result.print', $item->id) }}" target="_blank" class="p-1.5 text-gray-500 hover:bg-gray-100 rounded-md transition-colors" title="Print Result">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg><span class="text-lg font-medium text-gray-900">No results found</span><p class="mt-1 text-sm">No radiology results match your current filter.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $detailItem)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl z-10 my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-indigo-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Radiology Result Details</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Exam</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->exam_name }} @if($detailItem->modality_name)({{ $detailItem->modality_name }})@endif</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Request Code</span>
                        <p class="text-sm font-mono font-medium text-gray-900 mt-0.5">{{ $detailItem->request_code }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->patient_name }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Radiologist</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->radiologist?->full_name ?? '--' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Performed By</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->performedBy?->full_name ?? '--' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Result Date</span>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $detailItem->result_date?->format('M d, Y h:i A') ?? '--' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($detailItem->technique)
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Technique</span>
                        <p class="text-sm text-gray-700 mt-1 whitespace-pre-wrap bg-gray-50 rounded-lg p-3 border border-gray-100 min-h-[60px]">{{ $detailItem->technique }}</p>
                    </div>
                    @endif
                    @if($detailItem->impression)
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Impression</span>
                        <p class="text-sm font-medium text-gray-900 mt-1 whitespace-pre-wrap bg-indigo-50 rounded-lg p-3 border border-indigo-100 min-h-[60px]">{{ $detailItem->impression }}</p>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($detailItem->findings)
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Findings</span>
                        <p class="text-sm text-gray-700 mt-1 whitespace-pre-wrap bg-gray-50 rounded-lg p-3 border border-gray-100 min-h-[80px]">{{ $detailItem->findings }}</p>
                    </div>
                    @endif
                    @if($detailItem->result_text)
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Full Report</span>
                        <p class="text-sm text-gray-700 mt-1 whitespace-pre-wrap bg-gray-50 rounded-lg p-3 border border-gray-100 min-h-[80px]">{{ $detailItem->result_text }}</p>
                    </div>
                    @endif
                </div>

                @if($detailItem->doctor_notes || $detailItem->images->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($detailItem->doctor_notes)
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Remarks</span>
                        <p class="text-sm text-gray-600 mt-1 italic bg-gray-50 rounded-lg p-3 border border-gray-100">{{ $detailItem->doctor_notes }}</p>
                    </div>
                    @endif
                    @if($detailItem->images->count() > 0)
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Attached Images ({{ $detailItem->images->count() }})</span>
                        <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($detailItem->images as $img)
                                <a href="{{ Storage::url($img->path) }}" target="_blank" class="group relative block bg-gray-100 rounded-lg overflow-hidden border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all">
                                    @php $isImage = str_starts_with($img->mime_type ?? '', 'image/'); @endphp
                                    @if($isImage)
                                        <img src="{{ Storage::url($img->path) }}" alt="{{ $img->original_name }}" class="w-full h-20 object-cover">
                                    @else
                                        <div class="w-full h-20 flex items-center justify-center text-gray-400">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </div>
                                    @if($img->is_primary)
                                        <span class="absolute top-1 left-1 bg-indigo-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">PRIMARY</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    @if($detailItem->status === 'Complete')
                    <a href="{{ route('radiology.result.print', $detailItem->id) }}" target="_blank" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </a>
                    @endif
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Result Entry Modal --}}
    @if($showResultModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 overflow-y-auto">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-6xl z-10 my-8">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-indigo-50/50">
                <h3 class="text-lg font-semibold text-gray-900">Radiology Result Entry</h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form wire:submit="saveResult" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="radiologist_id" class="block text-sm font-medium text-gray-700 mb-1.5">Reading Radiologist</label>
                        <select wire:model="radiologist_id" id="radiologist_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="">-- Select Radiologist --</option>
                            @foreach($radiologists as $rad)
                                <option value="{{ $rad->id }}">{{ $rad->full_name }} @if($rad->professional_code) [{{ $rad->professional_code }}] @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="performed_by" class="block text-sm font-medium text-gray-700 mb-1.5">Performed By (Technologist)</label>
                        <select wire:model="performed_by" id="performed_by" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            <option value="">-- Select Technologist --</option>
                            @foreach($technologists as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="technique" class="block text-sm font-medium text-gray-700 mb-1.5">Technique</label>
                        <textarea wire:model="technique" id="technique" rows="3" placeholder="e.g. AP and lateral views of the chest..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                    </div>
                    <div>
                        <label for="impression" class="block text-sm font-medium text-gray-700 mb-1.5">Impression / Conclusion</label>
                        <textarea wire:model="impression" id="impression" rows="3" placeholder="Radiologist's impression or diagnosis..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="findings" class="block text-sm font-medium text-gray-700 mb-1.5">Findings</label>
                        <textarea wire:model="findings" id="findings" rows="5" placeholder="Detailed radiological findings..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                    </div>
                    <div>
                        <label for="result_text" class="block text-sm font-medium text-gray-700 mb-1.5">Full Report / Additional Notes</label>
                        <textarea wire:model="result_text" id="result_text" rows="5" placeholder="Complete radiology report text..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="doctor_notes" class="block text-sm font-medium text-gray-700 mb-1.5">Internal Remarks</label>
                        <textarea wire:model="doctor_notes" id="doctor_notes" rows="2" placeholder="Any internal notes or remarks" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Attach Image / DICOM</label>
                        @if(count($existingImages) > 0)
                            <div class="mb-2 flex flex-wrap gap-2">
                                @foreach($existingImages as $img)
                                    <div class="relative group bg-gray-100 rounded-lg overflow-hidden border border-gray-200 w-16 h-16 flex-shrink-0">
                                        @php $isImg = str_starts_with($img['mime_type'] ?? '', 'image/'); @endphp
                                        @if($isImg)
                                            <img src="{{ Storage::url($img['path']) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 text-[10px]">DICOM</div>
                                        @endif
                                        <button type="button" wire:click="removeImage({{ $img['id'] }})" wire:confirm="Remove this image?" class="absolute top-0.5 right-0.5 bg-red-500 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity" title="Remove">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        @if($img['is_primary'])
                                            <span class="absolute bottom-0.5 left-0.5 bg-indigo-600 text-white text-[8px] font-bold px-1 py-0.5 rounded">P</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <input type="file" wire:model="image_upload" accept="image/*,.dcm" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900">
                        <div wire:loading wire:target="image_upload" class="text-xs text-indigo-600 mt-1">Uploading...</div>
                        @error('image_upload') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition-colors">
                        <span wire:loading.remove wire:target="saveResult">Save Result</span>
                        <span wire:loading wire:target="saveResult">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
