<div>
    @if($viewMode === 'list')
        {{-- LIST VIEW --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">PhilHealth Claims</h2>
                <p class="text-sm text-gray-500 mt-1">Manage PhilHealth claims with CF4 clinical data capture for e-Claims submission.</p>
            </div>
            <button wire:click="openCreate"
                    class="mt-4 sm:mt-0 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500 transition-colors duration-200">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Claim
            </button>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1116.65 2a7.5 7.5 0 010 14.65z"/></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search claim code or diagnosis…"
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
                        @foreach([['claim_code', 'Claim #'], ['patient_id', 'Patient'], ['status', 'Status'], ['claim_type', 'Type'], ['total_claimed_amount', 'Claimed'], ['outcome', 'Outcome'], ['created_date', 'Date']] as [$field, $label])
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
                            <td class="px-5 py-4 font-mono font-medium text-gray-900">{{ $item->claim_code }}</td>
                            <td class="px-5 py-4">
                                <div class="text-gray-800 font-medium">{{ $item->patient?->last_name }}, {{ $item->patient?->first_name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->patient?->patient_code }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @php $colors = ['Draft' => 'bg-gray-50 text-gray-600 border-gray-200', 'For Submission' => 'bg-yellow-50 text-yellow-700 border-yellow-200', 'Submitted' => 'bg-blue-50 text-blue-700 border-blue-200', 'Acknowledged' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'Paid' => 'bg-green-50 text-green-700 border-green-200', 'Denied' => 'bg-red-50 text-red-700 border-red-200']; @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold border shadow-sm {{ $colors[$item->status] ?? 'bg-gray-50 text-gray-600' }}">{{ $item->status }}</span>
                                @if($item->status === 'Draft' && !$item->er_admission_id && !$item->admission_id && empty($item->admitting_diagnosis))<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-amber-50 text-amber-700 border border-amber-200" title="Draft from triage — open and pull clinical data">Needs Data</span>@endif
                                @if($item->ecs_tracking_no)<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-purple-50 text-purple-700" title="ECS Uploaded">ECS</span>@endif
                                @if($item->cloud_folder)<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-cyan-50 text-cyan-700" title="Cloud Uploaded">CLOUD</span>@endif
                                @if($item->voucher_number)<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-green-50 text-green-700" title="Paid with Voucher">V</span>@endif
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $item->claim_type }}
                                @if($item->claim_type === 'OECB')
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-700 border border-sky-200">OECB</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-mono text-gray-800">&#x20B1;{{ number_format($item->total_claimed_amount, 2) }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $item->outcome ?? '—' }}</td>
                            <td class="px-5 py-4 text-gray-500 text-xs">{{ $item->created_date?->format('Y-m-d') }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center space-x-2">
                                    @if(in_array($item->status, ['Draft', 'For Submission']))
                                    <button wire:click="openEdit({{ $item->id }})" class="p-1.5 text-light-blue-600 hover:bg-light-blue-100 rounded-md transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="p-1.5 text-red-500 hover:bg-red-100 rounded-md transition-colors" title="Archive"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    @endif
                                    @if(in_array($item->status, ['Submitted', 'Acknowledged', 'Paid']))
                                    <button wire:click="openSubmission({{ $item->id }})" class="p-1.5 text-indigo-600 hover:bg-indigo-100 rounded-md transition-colors" title="Submit/Status"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span class="text-lg font-medium text-gray-900">No claims found</span><p class="mt-1 text-sm">Create a new claim to get started.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>

        {{-- Delete Modal --}}
        @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="close"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm z-10 p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center mb-4"><svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Archive Claim</h3>
                    <p class="text-sm text-gray-500 mb-6">This claim and all its items will be archived.</p>
                    <div class="flex space-x-3 w-full">
                        <button type="button" wire:click="$set('showDeleteModal', false)" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                        <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors">Yes, Archive</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

    @else
        {{-- ENTRY VIEW --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <button wire:click="backToList"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Claims
                </button>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        {{ $entryMode === 'create' ? 'New Claim' : 'Edit Claim' }}
                    </h2>
                    @if($entryClaimId)
                    <span class="text-sm font-mono text-gray-500">Claim #{{ $entryClaimId }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tab Bar --}}
        <div class="flex items-center gap-1 border-b border-gray-200 bg-gray-50/50 -mx-6 px-6 mb-6 overflow-x-auto">
            @php
                $tabs = [
                    'pullup' => ['label' => 'Clinical Pull-Up', 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                    'form' => ['label' => 'Claim Form', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    'items' => ['label' => 'Claim Items', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    'submission' => ['label' => 'Submission', 'icon' => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12'],
                ];
            @endphp
            @foreach($tabs as $key => $info)
                <button wire:click="switchTab('{{ $key }}')"
                        class="flex items-center gap-2 px-4 py-3 text-xs font-semibold border-b-2 transition-colors whitespace-nowrap {{ $activeTab === $key ? 'border-light-blue-500 text-light-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $info['icon'] }}"/></svg>
                    {{ $info['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Tab Content --}}
        <div>
            {{-- Pull-Up Tab --}}
            <div class="{{ $activeTab === 'pullup' ? '' : 'hidden' }}">
                <livewire:philhealth.clinical-data-pull-up />
            </div>

            {{-- Claim Form Tab --}}
            <div class="{{ $activeTab === 'form' ? '' : 'hidden' }}">
                <livewire:philhealth.claim-form />
            </div>

            {{-- Claim Items Tab --}}
            <div class="{{ $activeTab === 'items' ? '' : 'hidden' }}">
                <livewire:philhealth.claim-items />
            </div>

            {{-- Submission Tab --}}
            <div class="{{ $activeTab === 'submission' ? '' : 'hidden' }}">
                <livewire:philhealth.claim-submission />
            </div>
        </div>
    @endif
</div>
