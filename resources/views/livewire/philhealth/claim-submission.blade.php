<div>
    @if($showModal)
        <div class="space-y-0">
            {{-- Tabs --}}
            <div class="flex border-b border-gray-200 bg-gray-50/50 -mx-6 px-6 overflow-x-auto">
                @foreach([
                    'submission' => ['label' => 'Submission', 'icon' => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12'],
                    'status' => ['label' => 'Status', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    'rejection' => ['label' => 'Rejection', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z'],
                    'cf5' => ['label' => 'CF5/DRG', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    'pdfs' => ['label' => 'PDF Forms', 'icon' => 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z'],
                ] as $tab => $info)
                <button wire:click="setTab('{{ $tab }}')"
                        class="px-4 py-3 text-xs font-semibold border-b-2 transition-colors whitespace-nowrap {{ $activeTab === $tab ? 'border-indigo-500 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ $info['label'] }}
                </button>
                @endforeach
            </div>

            <div class="mt-4">
                {{-- SUBMISSION TAB --}}
                @if($activeTab === 'submission')
                <div class="space-y-4">
                    {{-- Eligibility --}}
                    <div class="bg-indigo-50/50 rounded-xl p-4 border border-indigo-100">
                        <h4 class="text-sm font-semibold text-indigo-800 mb-3">Check Eligibility (CEWS)</h4>
                        <button type="button" wire:click="checkEligibility" wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="checkEligibility">Check Eligibility</span>
                            <span wire:loading wire:target="checkEligibility" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Checking…</span>
                        </button>
                        @if($eligibilityResult)
                        <div class="mt-3 p-3 rounded-lg {{ $eligibilityResult['eligible'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                            <p class="text-sm font-medium {{ $eligibilityResult['eligible'] ? 'text-green-700' : 'text-red-700' }}">{{ $eligibilityResult['message'] }}</p>
                            @if($eligibilityResult['tracking_no'])<p class="text-xs font-mono text-gray-600 mt-1">Tracking: {{ $eligibilityResult['tracking_no'] }}</p>@endif
                            @if($eligibilityResult['fallback'] ?? false)<p class="text-xs text-amber-600 mt-1">&#x26A0; CEWS unreachable — verify manually.</p>@endif
                        </div>
                        @endif
                    </div>

                    {{-- ECS Upload --}}
                    <div class="bg-purple-50/50 rounded-xl p-4 border border-purple-100">
                        <h4 class="text-sm font-semibold text-purple-800 mb-3">ECS Upload</h4>
                        <button type="button" wire:click="uploadToEcs" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors disabled:opacity-50">
                            <span wire:loading.remove wire:target="uploadToEcs">Upload to ECS</span>
                            <span wire:loading wire:target="uploadToEcs" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Uploading…</span>
                        </button>
                        @if($ecsResultMessage)<p class="mt-2 text-sm {{ str_contains($ecsResultMessage, 'successful') ? 'text-green-700' : 'text-red-700' }}">{{ $ecsResultMessage }}</p>@endif
                    </div>

                    {{-- Cloud Upload --}}
                    <div class="bg-cyan-50/50 rounded-xl p-4 border border-cyan-100">
                        <h4 class="text-sm font-semibold text-cyan-800 mb-3">Cloud Storage Upload</h4>
                        <div class="flex space-x-2">
                            <button type="button" wire:click="uploadToCloud" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-cyan-600 hover:bg-cyan-700 rounded-lg transition-colors disabled:opacity-50">
                                <span wire:loading.remove wire:target="uploadToCloud">Upload to Cloud</span>
                                <span wire:loading wire:target="uploadToCloud" class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>Uploading…</span>
                            </button>
                            <button type="button" wire:click="downloadClaimXml" class="px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-lg transition-colors">
                                Download XML
                            </button>
                        </div>
                        @if($cloudResultMessage)<p class="mt-2 text-sm {{ str_contains($cloudResultMessage, 'successful') ? 'text-green-700' : 'text-red-700' }}">{{ $cloudResultMessage }}</p>@endif
                    </div>
                </div>
                @endif

                {{-- STATUS TAB --}}
                @if($activeTab === 'status')
                <div class="space-y-4">
                    {{-- Acknowledgment --}}
                    <div class="bg-green-50/50 rounded-xl p-4 border border-green-100">
                        <h4 class="text-sm font-semibold text-green-800 mb-3">Acknowledgment</h4>
                        <div class="flex space-x-2">
                            <input wire:model="acknowledgment_code" type="text" placeholder="PH-ACK-2026-xxxxx" class="flex-1 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                            <button type="button" wire:click="acknowledgeClaim" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors disabled:opacity-50">Acknowledge</button>
                        </div>
                        @error('acknowledgment_code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Voucher --}}
                    <div class="bg-emerald-50/50 rounded-xl p-4 border border-emerald-100">
                        <h4 class="text-sm font-semibold text-emerald-800 mb-3">Record Voucher Payment</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <input wire:model="voucher_number" type="text" placeholder="Voucher #" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <input wire:model="voucher_amount" type="number" step="0.01" placeholder="Amount" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <button type="button" wire:click="recordVoucher" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors disabled:opacity-50">Record Payment</button>
                        </div>
                        @error('voucher_number') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        @error('voucher_amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Deny --}}
                    <div class="bg-red-50/50 rounded-xl p-4 border border-red-100">
                        <h4 class="text-sm font-semibold text-red-800 mb-3">Mark Denied</h4>
                        <textarea wire:model="deny_reason" rows="2" placeholder="Reason for denial…" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 mb-3"></textarea>
                        <button type="button" wire:click="markDenied" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors disabled:opacity-50">Confirm Deny</button>
                    </div>

                    {{-- Poll Status --}}
                    <div class="bg-teal-50/50 rounded-xl p-4 border border-teal-100">
                        <h4 class="text-sm font-semibold text-teal-800 mb-3">Status Poll</h4>
                        <div class="flex space-x-2">
                            <button type="button" wire:click="runStatusPoll" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition-colors disabled:opacity-50">Poll Now</button>
                            <button type="button" wire:click="loadPollHistory" class="px-4 py-2 text-sm font-medium text-teal-700 bg-teal-50 border border-teal-200 hover:bg-teal-100 rounded-lg transition-colors">Load History</button>
                        </div>
                        @if($pollResultMessage)<p class="mt-2 text-sm text-gray-700">{{ $pollResultMessage }}</p>@endif
                        @if($pollHistory)
                        <div class="mt-3 space-y-1">
                            @foreach($pollHistory as $ph)
                            <div class="text-xs p-2 bg-white rounded border border-gray-100">
                                <span class="font-mono text-gray-500">{{ $ph['polled_at'] ?? '' }}</span>
                                <span class="ml-2 {{ ($ph['status'] ?? '') === 'Paid' ? 'text-green-700' : 'text-gray-700' }}">{{ $ph['status'] ?? '—' }}</span>
                                @if($ph['remarks'] ?? null)<span class="ml-2 text-gray-500">{{ $ph['remarks'] }}</span>@endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- REJECTION TAB --}}
                @if($activeTab === 'rejection')
                <div class="space-y-4">
                    <div class="bg-amber-50/50 rounded-xl p-4 border border-amber-100">
                        <h4 class="text-sm font-semibold text-amber-800 mb-3">Record Rejection</h4>
                        <div class="space-y-3">
                            <input wire:model="rejection_code" type="text" placeholder="Rejection Code" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            <textarea wire:model="rejection_reason" rows="3" placeholder="Rejection reason from PhilHealth…" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"></textarea>
                            <input wire:model="rejection_date" type="date" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            <button type="button" wire:click="recordRejection" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors disabled:opacity-50">Record Rejection</button>
                        </div>
                    </div>

                    @if($claim && $claim->status === 'Denied')
                    <div class="bg-orange-50/50 rounded-xl p-4 border border-orange-100">
                        <h4 class="text-sm font-semibold text-orange-800 mb-3">Resubmit Claim</h4>
                        <p class="text-sm text-gray-600 mb-3">Reset claim to "For Submission" and mark latest rejection as Resubmitted.</p>
                        <button type="button" wire:click="resubmitClaim"
                                onclick="return confirm('Resubmit this claim?')"
                                class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors">Resubmit</button>
                    </div>
                    @endif
                </div>
                @endif

                {{-- CF5/DRG TAB --}}
                @if($activeTab === 'cf5')
                <div class="space-y-4">
                    <div class="bg-sky-50/50 rounded-xl p-4 border border-sky-100">
                        <h4 class="text-sm font-semibold text-sky-800 mb-3">CF5 — DRG Shadow Billing</h4>
                        <button type="button" wire:click="calculateCf5" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium text-white bg-sky-600 hover:bg-sky-700 rounded-lg transition-colors disabled:opacity-50">Calculate CF5</button>
                        @if($cf5Result)
                        <div class="mt-3 bg-white rounded-lg p-4 text-sm space-y-2 border border-sky-200">
                            <div class="flex justify-between"><span class="text-gray-500">DRG Code:</span><span class="font-bold font-mono">{{ $cf5Result['drg_code'] }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">DRG Weight:</span><span class="font-bold">{{ number_format($cf5Result['drg_weight'], 4) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">DRG Amount:</span><span class="font-bold">&#x20B1;{{ number_format($cf5Result['total_drg_amount'], 2) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Case Rate Amount:</span><span class="font-bold">&#x20B1;{{ number_format($cf5Result['actual_case_rate_amount'], 2) }}</span></div>
                            <div class="border-t border-gray-200 pt-2 flex justify-between {{ $cf5Result['difference_amount'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                <span class="font-semibold">{{ $cf5Result['difference_amount'] >= 0 ? 'GAIN' : 'LOSS' }}:</span>
                                <span class="font-bold">&#x20B1;{{ number_format(abs($cf5Result['difference_amount']), 2) }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- QR Code --}}
                    <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100">
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">eGov QR Code</h4>
                        <button type="button" wire:click="generateQr" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-100 rounded-lg transition-colors">Generate QR</button>
                        @if($qrDataUri)
                        <div class="mt-3 inline-block p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                            <img src="{{ $qrDataUri }}" alt="QR Code" class="w-40 h-40">
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- PDF FORMS TAB --}}
                @if($activeTab === 'pdfs')
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <button type="button" wire:click="previewCf1" class="flex items-center p-4 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100 transition-colors text-left">
                        <svg class="w-8 h-8 text-rose-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div><span class="font-semibold text-sm text-rose-800">CF1</span><br><span class="text-xs text-gray-500">Member Registration</span></div>
                    </button>
                    <button type="button" wire:click="previewCf2" class="flex items-center p-4 bg-indigo-50 border border-indigo-200 rounded-xl hover:bg-indigo-100 transition-colors text-left">
                        <svg class="w-8 h-8 text-indigo-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div><span class="font-semibold text-sm text-indigo-800">CF2</span><br><span class="text-xs text-gray-500">HCI Report</span></div>
                    </button>
                    <button type="button" wire:click="previewCf3" class="flex items-center p-4 bg-emerald-50 border border-emerald-200 rounded-xl hover:bg-emerald-100 transition-colors text-left">
                        <svg class="w-8 h-8 text-emerald-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div><span class="font-semibold text-sm text-emerald-800">CF3</span><br><span class="text-xs text-gray-500">Clinical Record</span></div>
                    </button>
                    <button type="button" wire:click="previewCf4" class="flex items-center p-4 bg-violet-50 border border-violet-200 rounded-xl hover:bg-violet-100 transition-colors text-left">
                        <svg class="w-8 h-8 text-violet-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div><span class="font-semibold text-sm text-violet-800">CF4</span><br><span class="text-xs text-gray-500">Clinical Abstract</span></div>
                    </button>
                    @if($claimId)
                    <a href="{{ route('philhealth.claims.cf5pdf', $claimId) }}" target="_blank" class="flex items-center p-4 bg-sky-50 border border-sky-200 rounded-xl hover:bg-sky-100 transition-colors text-left">
                        <svg class="w-8 h-8 text-sky-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div><span class="font-semibold text-sm text-sky-800">CF5</span><br><span class="text-xs text-gray-500">DRG / Z-Benefit</span></div>
                    </a>
                    <a href="{{ route('philhealth.claims.esoapdf', $claimId) }}" target="_blank" class="flex items-center p-4 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 transition-colors text-left">
                        <svg class="w-8 h-8 text-amber-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div><span class="font-semibold text-sm text-amber-800">eSOA</span><br><span class="text-xs text-gray-500">Statement of Account</span></div>
                    </a>
                    <a href="{{ route('philhealth.claims.eeclpdf', $claimId) }}" target="_blank" class="flex items-center p-4 bg-teal-50 border border-teal-200 rounded-xl hover:bg-teal-100 transition-colors text-left">
                        <svg class="w-8 h-8 text-teal-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div><span class="font-semibold text-sm text-teal-800">EECL</span><br><span class="text-xs text-gray-500">Annex D — OECB Summary</span></div>
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- PDF Preview Modal --}}
        @if($showPreviewModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="closePreview"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl z-10 overflow-hidden flex flex-col" style="height: 85vh;">
                <div class="flex items-center justify-between px-6 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">{{ $previewTitle }}</h3>
                    <div class="flex space-x-2">
                        <a href="{{ $previewDownloadUrl }}" target="_blank" class="px-3 py-1.5 text-xs font-medium text-white bg-light-blue-600 hover:bg-light-blue-700 rounded-lg transition-colors">Download PDF</a>
                        <button wire:click="closePreview" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-6">{!! $previewHtml !!}</div>
            </div>
        </div>
        @endif

    @else
        <div class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <p class="text-lg font-medium text-gray-900">Claim Submission</p>
            <p class="text-sm mt-1">Load a claim to manage submission and status.</p>
        </div>
    @endif
</div>
