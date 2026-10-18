<div class="min-h-screen bg-gray-50/30 pb-12">
    {{-- Premium Patient Header --}}
    <div class="relative mb-8 bg-white border border-slate-200/60 shadow-xl shadow-slate-200/30 rounded-3xl overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-800 via-indigo-600 to-indigo-500 opacity-5"></div>
        <div class="absolute top-0 right-0 p-12 opacity-10">
            <svg class="w-32 h-32 text-indigo-900" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
        </div>
        
        <div class="relative p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6">
                <div class="flex items-center space-x-5">
                    <a href="{{ route('doctors.dashboard') }}" class="p-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl transition-all duration-200 hover:-translate-x-1 hover:shadow-md">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    <div>
                        <div class="flex items-center space-x-3 mb-1.5">
                            <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $patient->full_name ?? 'Unknown Patient' }}</h2>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full uppercase tracking-wider shadow-sm">{{ $type }} Encounter</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-slate-600 font-medium">
                            <div class="flex items-center bg-slate-100/80 px-3 py-1.5 rounded-lg">
                                <span class="text-slate-400 mr-1.5">MRN:</span> 
                                <span class="text-slate-800 font-bold font-mono">{{ $patient->patient_code ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center bg-slate-100/80 px-3 py-1.5 rounded-lg">
                                <span class="text-slate-400 mr-1.5">Age:</span> 
                                <span class="text-slate-800">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age . ' yrs' : 'N/A' }}</span>
                            </div>
                            <div class="flex items-center bg-slate-100/80 px-3 py-1.5 rounded-lg">
                                <span class="text-slate-400 mr-1.5">Gender:</span> 
                                <span class="text-slate-800">{{ $patient->gender?->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Navigation Tabs --}}
    <div class="flex justify-center mb-8">
        <nav class="flex p-1.5 bg-white border border-slate-200 rounded-2xl shadow-sm overflow-x-auto w-full max-w-3xl">
            <button wire:click="setTab('notes')" 
                    class="flex-1 whitespace-nowrap py-3 px-6 rounded-xl font-bold text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 
                           {{ $activeTab === 'notes' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                Clinical Notes (SOAP)
            </button>
            <button wire:click="setTab('orders')" 
                    class="flex-1 whitespace-nowrap py-3 px-6 rounded-xl font-bold text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 
                           {{ $activeTab === 'orders' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                Active Orders
            </button>
            <button wire:click="setTab('results')" 
                    class="flex-1 whitespace-nowrap py-3 px-6 rounded-xl font-bold text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 
                           {{ $activeTab === 'results' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                Results
            </button>
            <button wire:click="setTab('records')" 
                    class="flex-1 whitespace-nowrap py-3 px-6 rounded-xl font-bold text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 
                           {{ $activeTab === 'records' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                History
            </button>
        </nav>
    </div>

    {{-- Content --}}
    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden min-h-[600px] mb-8 relative z-10">
        @if($activeTab === 'notes')
            <div class="p-8">
                <form wire:submit="saveNotes">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-12 gap-y-10">
                        
                        {{-- Left Column: Subjective & Objective --}}
                        <div class="space-y-8 relative">
                            <div class="absolute right-0 top-0 bottom-0 w-px bg-gradient-to-b from-transparent via-slate-200 to-transparent hidden lg:block -mr-6"></div>
                            
                            {{-- Subjective / HPI --}}
                            <div class="group">
                                <label for="history_of_present_illness" class="flex items-center text-sm font-bold text-slate-800 mb-2">
                                    <span class="w-6 h-6 rounded bg-indigo-100 text-indigo-700 flex items-center justify-center mr-2 text-xs">S</span>
                                    History of Present Illness (Subjective)
                                </label>
                                <textarea wire:model="history_of_present_illness" id="history_of_present_illness" rows="4" 
                                          class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-900 transition-all duration-300 hover:bg-white hover:border-indigo-300 focus:bg-white focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm" 
                                          placeholder="Patient's symptoms, duration, progression..."></textarea>
                            </div>

                            {{-- Objective / Physical Exam --}}
                            <div class="group">
                                <label for="physical_exam_findings" class="flex items-center text-sm font-bold text-slate-800 mb-2">
                                    <span class="w-6 h-6 rounded bg-emerald-100 text-emerald-700 flex items-center justify-center mr-2 text-xs">O</span>
                                    Physical Exam Findings (Objective)
                                </label>
                                <textarea wire:model="physical_exam_findings" id="physical_exam_findings" rows="4" 
                                          class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-900 transition-all duration-300 hover:bg-white hover:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-sm" 
                                          placeholder="Vital signs, physical exam observations..."></textarea>
                            </div>
                        </div>

                        {{-- Right Column: Assessment & Plan --}}
                        <div class="space-y-8">
                            {{-- Assessment / Diagnosis --}}
                            <div class="group">
                                <label for="assessment" class="flex items-center text-sm font-bold text-slate-800 mb-2">
                                    <span class="w-6 h-6 rounded bg-amber-100 text-amber-700 flex items-center justify-center mr-2 text-xs">A</span>
                                    Assessment & Diagnosis
                                </label>
                                
                                {{-- ICD-10 Search --}}
                                <div class="mb-4 relative z-20">
                                    <div class="relative group-hover:shadow-md transition-shadow rounded-xl">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input wire:model.live.debounce.300ms="searchIcd" type="text" placeholder="Search ICD-10 Code or Disease..." 
                                            class="w-full pl-11 pr-4 py-3 bg-white border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all shadow-sm">
                                    </div>
                                    
                                    @if(count($icdResults) > 0)
                                        <div class="absolute z-50 w-full mt-2 bg-white/95 backdrop-blur-xl rounded-xl shadow-2xl border border-slate-200 max-h-60 overflow-y-auto">
                                            <ul class="py-2">
                                                @foreach($icdResults as $res)
                                                    <li>
                                                        <button type="button" wire:click="selectIcd('{{ $res['code'] }}')" class="w-full text-left px-5 py-3 hover:bg-slate-50 transition-colors flex items-start gap-4 border-b border-slate-50 last:border-0">
                                                            <span class="font-mono text-sm font-bold text-amber-600 min-w-[3.5rem]">{{ $res['code'] }}</span>
                                                            <span class="text-sm text-slate-700 font-medium">{{ $res['description'] }}</span>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                                <input type="hidden" wire:model="icd_10_code">

                                <textarea wire:model="assessment" id="assessment" rows="3" 
                                          class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-900 transition-all duration-300 hover:bg-white hover:border-amber-300 focus:bg-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 shadow-sm" 
                                          placeholder="Primary and secondary diagnoses..."></textarea>
                            </div>

                            {{-- Plan / Treatment Notes --}}
                            <div class="group">
                                <label for="treatment_notes" class="flex items-center text-sm font-bold text-slate-800 mb-2">
                                    <span class="w-6 h-6 rounded bg-rose-100 text-rose-700 flex items-center justify-center mr-2 text-xs">P</span>
                                    Treatment Plan & Notes
                                </label>
                                <textarea wire:model="treatment_notes" id="treatment_notes" rows="4" 
                                          class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-900 transition-all duration-300 hover:bg-white hover:border-rose-300 focus:bg-white focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 shadow-sm" 
                                          placeholder="Course of action, prescriptions, disposition plan..."></textarea>
                            </div>
                        </div>

                        <div class="col-span-1 lg:col-span-2 mt-4 relative z-0">
                            <label for="orders_summary" class="block text-sm font-bold text-slate-800 mb-2">
                                Orders Summary
                            </label>
                            <textarea wire:model="orders_summary" id="orders_summary" rows="2" 
                                      class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm text-slate-900 transition-all duration-300 hover:bg-white hover:border-slate-300 focus:bg-white focus:ring-4 focus:ring-slate-500/20 focus:border-slate-500 shadow-sm" 
                                      placeholder="Briefly summarize ordered labs or meds (for reference)..."></textarea>
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end pt-6 border-t border-slate-100">
                        <button type="submit" class="px-8 py-3.5 bg-slate-900 hover:bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-slate-900/20 transition-all duration-300 hover:-translate-y-1 focus:ring-4 focus:ring-indigo-500/30 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Clinical Notes
                        </button>
                    </div>
                </form>
            </div>
            
        @elseif($activeTab === 'orders')
            <div class="p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                    <div>
                        <h3 class="text-2xl font-extrabold text-slate-900">Active Orders</h3>
                        <p class="text-slate-500 mt-1">Manage pending laboratory, pharmacy, and radiology requests.</p>
                    </div>
                    <button wire:click="openOrderModal('lab')" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-1 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        New Clinical Order
                    </button>
                </div>

                <div class="space-y-10">
                    @if($record->labRequests->count() > 0)
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg mb-4 flex items-center">
                                <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg></span>
                                Laboratory Orders
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($record->labRequests as $req)
                                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-sm font-bold font-mono text-slate-800">{{ $req->request_code }}</span>
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">{{ $req->status }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($record->pharmacyRequests->count() > 0)
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg mb-4 flex items-center">
                                <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg></span>
                                Pharmacy Orders
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($record->pharmacyRequests as $req)
                                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-sm font-bold font-mono text-slate-800">{{ $req->request_code }}</span>
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">{{ $req->status }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($record->radiologyRequests->count() > 0)
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg mb-4 flex items-center">
                                <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-3"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></span>
                                Radiology Orders
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($record->radiologyRequests as $req)
                                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-sm font-bold font-mono text-slate-800">{{ $req->request_code }}</span>
                                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600">{{ $req->status }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($record->labRequests->count() === 0 && $record->pharmacyRequests->count() === 0 && $record->radiologyRequests->count() === 0)
                        <div class="text-center py-24 bg-slate-50/50 rounded-3xl border border-dashed border-slate-300">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-100 mb-4 text-slate-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <p class="text-xl font-bold text-slate-700">No Active Orders</p>
                            <p class="mt-2 text-slate-500">Create a new order using the button above.</p>
                        </div>
                    @endif
                </div>
            </div>

        @elseif($activeTab === 'results')
            <div class="p-8">
                @php
                    $completedLabItems = collect();
                    if ($record && $record->labRequests) {
                        foreach ($record->labRequests as $req) {
                            foreach ($req->items as $item) {
                                if ($item->status === 'Completed') {
                                    $completedLabItems->push($item);
                                }
                            }
                        }
                    }
                    $completedLabItems = $completedLabItems->sortByDesc('verified_date');
                    
                    $completedRadItems = collect();
                    if ($record && $record->radiologyRequests) {
                        foreach ($record->radiologyRequests as $req) {
                            foreach ($req->items as $item) {
                                if ($item->status === 'Complete') {
                                    $completedRadItems->push($item);
                                }
                            }
                        }
                    }
                    $completedRadItems = $completedRadItems->sortByDesc('result_date');
                @endphp

                @if($completedLabItems->count() > 0 || $completedRadItems->count() > 0)
                    @if($completedLabItems->count() > 0)
                        <div class="mb-6 flex justify-between items-end">
                            <h3 class="text-2xl font-extrabold text-slate-900 flex items-center">
                                <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3 shadow-inner"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg></span>
                                Laboratory Results
                            </h3>
                        </div>
                        <div class="overflow-x-auto rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/40">
                            <table class="min-w-full text-sm border-collapse">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Test Name</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Result</th>
                                        <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Flag</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Reference Range</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Remarks</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Date Verified</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($completedLabItems as $item)
                                        @php
                                            $hasAbnormalFlag = in_array($item->flag, ['H', 'L', 'Critical', 'Abnormal']);
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition-colors {{ $hasAbnormalFlag ? 'bg-rose-50/50 hover:bg-rose-50' : '' }}">
                                            <td class="px-6 py-4 text-slate-900 font-bold">{{ $item->test_name }}</td>
                                            <td class="px-6 py-4 text-slate-800 font-mono font-medium text-base">{{ $item->result_value ?? $item->result_categorical ?? ($item->result_text ? \Str::limit($item->result_text, 30) : '--') }}</td>
                                            <td class="px-6 py-4 text-center">
                                                @if($item->flag)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider border {{ $item->flag === 'H' ? 'bg-rose-100 text-rose-800 border-rose-200 shadow-sm shadow-rose-100' : ($item->flag === 'L' ? 'bg-amber-100 text-amber-800 border-amber-200 shadow-sm shadow-amber-100' : ($item->flag === 'N' ? 'bg-emerald-100 text-emerald-800 border-emerald-200 shadow-sm shadow-emerald-100' : ($item->flag === 'Critical' ? 'bg-red-600 text-white border-red-700 shadow-md shadow-red-200 animate-pulse' : ($item->flag === 'Abnormal' ? 'bg-rose-100 text-rose-800 border-rose-200' : 'bg-slate-100 text-slate-600 border-slate-200')))) }}">{{ $item->flag }}</span>
                                                @else
                                                    <span class="text-slate-300">--</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-slate-500 font-medium text-xs">{{ $item->reference_range ?? '--' }}</td>
                                            <td class="px-6 py-4 text-slate-600 text-sm max-w-xs truncate" title="{{ $item->result_text ?? $item->result_notes }}">{{ $item->result_text ?? $item->result_notes ?? '--' }}</td>
                                            <td class="px-6 py-4 text-right text-slate-500 whitespace-nowrap">
                                                <div class="font-medium text-slate-700">{{ $item->verified_date ? $item->verified_date->format('M d, Y h:i A') : '--' }}</div>
                                                <a href="{{ route('lab.result.print', $item->id) }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 inline-flex items-center mt-1 group">
                                                    Print <svg class="w-3 h-3 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if($completedRadItems->count() > 0)
                        <div class="mt-14 mb-6 flex justify-between items-end">
                            <h3 class="text-2xl font-extrabold text-slate-900 flex items-center">
                                <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mr-3 shadow-inner"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></span>
                                Imaging & Radiology Results
                            </h3>
                        </div>
                        <div class="space-y-8">
                            @foreach($completedRadItems as $rad)
                                <div class="bg-white border border-slate-200 rounded-[2rem] shadow-xl shadow-slate-200/40 overflow-hidden group hover:border-purple-200 transition-colors">
                                    <div class="bg-slate-50/80 backdrop-blur-sm border-b border-slate-200 px-8 py-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div>
                                            <h4 class="text-xl font-extrabold text-slate-900">{{ $rad->exam_name }} @if($rad->modality_name)<span class="text-sm font-medium text-slate-500 bg-white px-2 py-0.5 rounded-md border border-slate-200 ml-2 shadow-sm">{{ $rad->modality_name }}</span>@endif</h4>
                                            <div class="text-sm text-slate-500 mt-2 flex items-center">
                                                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                Reported by: <span class="font-bold text-slate-700 ml-1">{{ $rad->radiologist?->full_name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-6">
                                            <div class="text-right">
                                                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Date Finalized</div>
                                                <div class="text-sm font-extrabold text-slate-800 mt-0.5">{{ $rad->result_date ? $rad->result_date->format('M d, Y h:i A') : '--' }}</div>
                                            </div>
                                            <a href="{{ route('radiology.result.print', $rad->id) }}" target="_blank" class="p-3 bg-white border border-slate-200 text-slate-600 hover:text-purple-600 hover:border-purple-300 hover:bg-purple-50 hover:shadow-md rounded-xl transition-all duration-200" title="Print Result">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="p-8 space-y-6">
                                        @if($rad->technique)
                                            <div>
                                                <h5 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Technique</h5>
                                                <p class="text-sm text-slate-700 whitespace-pre-wrap leading-relaxed">{{ $rad->technique }}</p>
                                            </div>
                                        @endif
                                        @if($rad->findings)
                                            <div>
                                                <h5 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Findings</h5>
                                                <div class="whitespace-pre-wrap bg-slate-50 rounded-2xl p-5 border border-slate-100 text-sm text-slate-800 leading-relaxed">{{ $rad->findings }}</div>
                                            </div>
                                        @endif
                                        @if($rad->impression)
                                            <div>
                                                <h5 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Impression</h5>
                                                <div class="whitespace-pre-wrap bg-purple-50/50 rounded-2xl p-5 border border-purple-100 text-sm font-bold text-slate-900 leading-relaxed">{{ $rad->impression }}</div>
                                            </div>
                                        @endif
                                        @if($rad->result_text)
                                            <div>
                                                <h5 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Full Report</h5>
                                                <p class="text-sm text-slate-700 whitespace-pre-wrap leading-relaxed">{{ $rad->result_text }}</p>
                                            </div>
                                        @endif
                                        @if($rad->doctor_notes)
                                            <div class="pt-6 border-t border-slate-100">
                                                <h5 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Remarks / Notes</h5>
                                                <p class="text-sm text-slate-600 italic bg-amber-50 p-4 rounded-xl border border-amber-100">{{ $rad->doctor_notes }}</p>
                                            </div>
                                        @endif
                                        
                                        @php $radImages = $rad->images ?? collect([]); @endphp
                                        @if($radImages->count() > 0)
                                            <div class="pt-6 border-t border-slate-100 mt-6">
                                                <h5 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Attached Images ({{ $radImages->count() }})</h5>
                                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                                    @foreach($radImages as $img)
                                                        <a href="{{ Storage::url($img->path) }}" target="_blank" class="group relative block bg-slate-100 rounded-xl overflow-hidden border-2 border-transparent hover:border-purple-400 hover:shadow-lg hover:shadow-purple-200 transition-all duration-300 transform hover:-translate-y-1">
                                                            @php $isImg = str_starts_with($img->mime_type ?? '', 'image/'); @endphp
                                                            @if($isImg)
                                                                <img src="{{ Storage::url($img->path) }}" alt="{{ $img->original_name }}" class="w-full h-28 object-cover">
                                                            @else
                                                                <div class="w-full h-28 flex items-center justify-center text-slate-400 bg-slate-50"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                                                            @endif
                                                            <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/40 transition-colors duration-300 flex items-center justify-center backdrop-blur-[1px] opacity-0 group-hover:opacity-100">
                                                                <svg class="w-8 h-8 text-white drop-shadow-md scale-50 group-hover:scale-100 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            </div>
                                                            @if($img->is_primary)
                                                                <span class="absolute top-2 left-2 bg-purple-600/90 backdrop-blur-sm text-white text-[9px] font-black px-2 py-1 rounded-md shadow-sm">PRIMARY</span>
                                                            @endif
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="text-center py-24 bg-slate-50/50 rounded-3xl border border-dashed border-slate-300">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-100 mb-4 text-slate-400 shadow-sm">
                            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <p class="text-xl font-extrabold text-slate-700">No Results Available</p>
                        <p class="mt-2 text-slate-500 font-medium max-w-sm mx-auto">Diagnostic results will appear here once processed by the respective departments.</p>
                    </div>
                @endif
            </div>

        @elseif($activeTab === 'records')
            <div class="p-8">
                @if ($viewingPastEncounter && $pastEncounterDetail)
                    {{-- Past Encounter Detail View --}}
                    <div class="animate-in fade-in slide-in-from-bottom-4 duration-300">
                        <button wire:click="closePastEncounter" class="flex items-center text-sm font-bold text-slate-500 hover:text-slate-800 mb-6 transition-colors group bg-white px-4 py-2 border border-slate-200 rounded-xl shadow-sm hover:shadow-md w-fit">
                            <svg class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            Back to Timeline
                        </button>

                        <div class="bg-gradient-to-br from-indigo-800 to-indigo-600 rounded-3xl p-8 mb-8 shadow-xl text-white relative overflow-hidden">
                            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-5"></div>
                            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <span class="px-4 py-1.5 rounded-full text-xs font-black bg-white/20 text-white backdrop-blur-md uppercase tracking-wider border border-white/30">{{ $pastEncounterDetail['type'] }}</span>
                                    <h3 class="text-2xl font-extrabold tracking-tight">{{ $pastEncounterDetail['code'] }}</h3>
                                </div>
                                <div class="text-left md:text-right">
                                    <div class="font-medium text-indigo-200">{{ $pastEncounterDetail['date'] }}</div>
                                    <div class="text-sm font-bold mt-1 text-white flex items-center md:justify-end">
                                        <svg class="w-4 h-4 mr-1.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        {{ $pastEncounterDetail['physician'] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Fields --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 bg-slate-50 p-6 rounded-3xl border border-slate-100">
                            @foreach ($pastEncounterDetail['fields'] as $label => $value)
                                <div class="{{ strlen($value) > 100 ? 'md:col-span-2' : '' }}">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ $label }}</span>
                                    <p class="mt-1 text-sm text-slate-900 font-medium leading-relaxed bg-white p-4 rounded-2xl border border-slate-200/60 shadow-sm">{{ $value }}</p>
                                </div>
                            @endforeach
                        </div>

                        {{-- Past Orders (Similar to active orders redesign) --}}
                        {{-- (Omitting detailed past orders redesign to keep template size manageable, but applying basic classes) --}}
                        @if ($pastEncounterDetail['labs'] && $pastEncounterDetail['labs']->count() > 0)
                            <div class="mb-8">
                                <h4 class="text-base font-extrabold text-slate-900 mb-4 flex items-center">Laboratory Orders</h4>
                                <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-slate-50 border-b border-slate-200">
                                            <tr>
                                                <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-xs">Request</th>
                                                <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-xs">Test</th>
                                                <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-xs">Result</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach ($pastEncounterDetail['labs'] as $lab)
                                                <tr>
                                                    <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $lab['code'] }}</td>
                                                    <td class="px-5 py-3 font-bold text-slate-800">{{ $lab['test'] }}</td>
                                                    <td class="px-5 py-3 font-mono text-sm">{{ $lab['result'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Timeline List --}}
                    <div class="flex justify-between items-center mb-10">
                        <div>
                            <h3 class="text-2xl font-extrabold text-slate-900">Medical Timeline</h3>
                            <p class="text-slate-500 mt-1 font-medium">Chronological history of patient encounters.</p>
                        </div>
                        <a href="/medical-records" target="_blank" class="text-sm font-bold text-indigo-600 bg-indigo-50 px-4 py-2 rounded-xl hover:bg-indigo-100 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Full Records Portal
                        </a>
                    </div>

                    @if(count($this->medical_history) > 0)
                        <div class="relative border-l-2 border-slate-200 ml-4 space-y-8 pl-8">
                            @foreach($this->medical_history as $item)
                                <div class="relative">
                                    {{-- Timeline node --}}
                                    <div class="absolute -left-[2.4rem] top-1.5 w-4 h-4 rounded-full border-4 border-white shadow-sm
                                        {{ $item['color'] === 'rose' ? 'bg-rose-500' : ($item['color'] === 'indigo' ? 'bg-indigo-500' : 'bg-emerald-500') }}">
                                    </div>
                                    
                                    <button wire:click="viewPastEncounter('{{ $item['type_key'] }}', {{ $item['id'] }})"
                                        class="w-full text-left bg-white border border-slate-200 rounded-3xl p-6 hover:border-indigo-300 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 group hover:-translate-y-1 relative overflow-hidden">
                                        <div class="absolute inset-0 bg-gradient-to-r from-transparent to-{{ $item['color'] }}-50/50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        <div class="relative z-10 flex flex-col md:flex-row md:items-start justify-between gap-4">
                                            <div>
                                                <div class="flex items-center space-x-3 mb-2">
                                                    <span class="font-extrabold text-slate-900 text-lg">{{ $item['code'] }}</span>
                                                    <span class="px-3 py-1 bg-{{ $item['color'] }}-100 text-{{ $item['color'] }}-700 text-[10px] uppercase tracking-widest rounded-md font-black shadow-sm border border-{{ $item['color'] }}-200/50">{{ $item['type'] }}</span>
                                                </div>
                                                <p class="text-sm font-medium text-slate-500 flex items-center">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    {{ $item['date'] }} <span class="mx-2 text-slate-300">|</span> 
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                    {{ $item['physician'] }}
                                                </p>
                                                @if ($item['diagnosis'])
                                                    <div class="mt-4 bg-slate-50 p-3 rounded-xl border border-slate-100 group-hover:bg-white transition-colors">
                                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Primary Diagnosis</span>
                                                        <p class="text-sm font-medium text-slate-800 line-clamp-2">{{ $item['diagnosis'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="hidden md:flex items-center justify-center w-10 h-10 rounded-full bg-slate-50 text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors shadow-sm">
                                                <svg class="w-5 h-5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-24 bg-slate-50/50 rounded-3xl border border-dashed border-slate-300">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-100 mb-4 text-slate-400 shadow-sm">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <p class="text-xl font-extrabold text-slate-700">No Past Records Found</p>
                            <p class="mt-2 text-slate-500 font-medium max-w-sm mx-auto">This patient does not have any prior recorded encounters in the system.</p>
                        </div>
                    @endif
                @endif
            </div>
        @endif

    {{-- Glassmorphism Orders Modal --}}
    @if($showOrderModal)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity animate-in fade-in duration-300" wire:click="closeOrderModal"></div>
        <div class="relative bg-white/95 backdrop-blur-2xl rounded-[2rem] shadow-2xl shadow-indigo-900/20 w-full max-w-5xl z-10 my-8 flex flex-col max-h-[90vh] border border-white animate-in zoom-in-95 duration-300">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white rounded-t-[2rem]">
                <h3 class="text-2xl font-extrabold text-slate-900 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </span>
                    New Clinical Order
                </h3>
                <button wire:click="closeOrderModal" class="p-2 bg-slate-100 text-slate-400 hover:bg-slate-200 hover:text-slate-600 rounded-full transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            
            {{-- Modal Body --}}
            <div class="flex-1 overflow-y-auto p-8 bg-slate-50/50">
                {{-- Category Selector --}}
                <div class="flex p-1.5 bg-white border border-slate-200/60 rounded-2xl shadow-sm mb-8">
                    <button wire:click="$set('orderCategory', 'lab')" class="flex-1 py-3 px-6 text-sm font-bold rounded-xl transition-all duration-300 {{ $orderCategory === 'lab' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Laboratory Test</button>
                    <button wire:click="$set('orderCategory', 'rad')" class="flex-1 py-3 px-6 text-sm font-bold rounded-xl transition-all duration-300 {{ $orderCategory === 'rad' ? 'bg-purple-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Radiology / Imaging</button>
                    <button wire:click="$set('orderCategory', 'pharm')" class="flex-1 py-3 px-6 text-sm font-bold rounded-xl transition-all duration-300 {{ $orderCategory === 'pharm' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">Medication / E-Rx</button>
                </div>

                {{-- Search Bar --}}
                <div class="mb-8 relative z-20">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="searchOrderQuery" type="text" placeholder="Search for {{ $orderCategory === 'lab' ? 'Tests' : ($orderCategory === 'rad' ? 'Exams' : 'Medications') }}..." 
                               class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-2xl text-base text-slate-900 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm transition-all font-medium placeholder:font-normal">
                    </div>
                    
                    @if(count($orderSearchResults) > 0)
                        <div class="absolute z-50 w-full mt-2 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-slate-200 max-h-64 overflow-y-auto">
                            <ul class="py-2">
                                @foreach($orderSearchResults as $res)
                                    <li>
                                        <button type="button" wire:click="selectOrderItem({{ $res['id'] }})" class="w-full text-left px-6 py-4 hover:bg-indigo-50/50 transition-colors flex items-center justify-between border-b border-slate-50 last:border-0 group">
                                            <div>
                                                <div class="text-sm font-bold text-slate-800 group-hover:text-indigo-700 transition-colors">
                                                    @if($orderCategory === 'pharm')
                                                        {{ $res['generic_name'] }} @if($res['brand_name'])<span class="text-xs text-slate-400 font-medium ml-1">({{ $res['brand_name'] }})</span>@endif
                                                    @elseif($orderCategory === 'rad')
                                                        {{ $res['exam_name'] }} <span class="text-xs text-slate-400 font-medium ml-1">[{{ $res['code'] }}]</span>
                                                    @else
                                                        {{ $res['test_name'] }} <span class="text-xs text-slate-400 font-medium ml-1">[{{ $res['code'] }}]</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-sm font-mono font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">&#8369;{{ number_format($res[$orderCategory === 'pharm' ? 'unit_price' : 'standard_charge'], 2) }}</div>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{-- Cart Table --}}
                @if(count($selectedOrderItems) > 0)
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left font-bold text-slate-500 uppercase tracking-wider text-xs">Item Name</th>
                                    @if($orderCategory === 'pharm')
                                        <th class="px-6 py-4 text-left font-bold text-slate-500 uppercase tracking-wider text-xs">Sig (Dosage/Freq)</th>
                                    @endif
                                    <th class="px-6 py-4 text-center font-bold text-slate-500 uppercase tracking-wider text-xs">Qty</th>
                                    <th class="px-6 py-4 text-right font-bold text-slate-500 uppercase tracking-wider text-xs">Price</th>
                                    <th class="px-6 py-4 text-right font-bold text-slate-500 uppercase tracking-wider text-xs">Total</th>
                                    <th class="px-6 py-4 w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($selectedOrderItems as $index => $item)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-6 py-4 font-bold text-slate-800">{{ $item['name'] }}</td>
                                        @if($orderCategory === 'pharm')
                                            <td class="px-6 py-3">
                                                <div class="flex flex-wrap gap-2">
                                                    <input type="text" wire:model="selectedOrderItems.{{ $index }}.dosage" placeholder="Dose" class="w-16 px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                                    <input type="text" wire:model="selectedOrderItems.{{ $index }}.frequency" placeholder="Freq" class="w-16 px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                                    <input type="text" wire:model="selectedOrderItems.{{ $index }}.route" placeholder="Route" class="w-20 px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                                    <input type="text" wire:model="selectedOrderItems.{{ $index }}.duration" placeholder="Dur" class="w-16 px-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                                </div>
                                            </td>
                                        @endif
                                        <td class="px-6 py-3 text-center">
                                            <input type="number" wire:model.live="selectedOrderItems.{{ $index }}.quantity" min="1" class="w-20 px-3 py-2 text-center text-sm font-bold border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 bg-slate-50">
                                        </td>
                                        <td class="px-6 py-4 text-right font-mono font-medium text-slate-500">&#8369;{{ number_format($item['cost'], 2) }}</td>
                                        <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">&#8369;{{ number_format($item['cost'] * $item['quantity'], 2) }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <button wire:click="removeOrderItem({{ $index }})" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Priority Level</label>
                            <select wire:model="orderPriority" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                                <option value="Routine">Routine</option>
                                <option value="Urgent">Urgent</option>
                                <option value="STAT">STAT (Immediate)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Clinical Notes (Optional)</label>
                            <input wire:model="orderNotes" type="text" placeholder="Special instructions for the department..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-900 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                        </div>
                    </div>
                @else
                    <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-slate-300">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-3 text-slate-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <p class="text-lg font-bold text-slate-500">Your order list is empty.</p>
                        <p class="text-sm text-slate-400 mt-1">Search and select items above to add them to the order.</p>
                    </div>
                @endif
            </div>

            {{-- Modal Footer --}}
            <div class="px-8 py-5 bg-white border-t border-slate-100 rounded-b-[2rem] flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center">
                    <span class="text-sm font-bold text-slate-500 mr-3 uppercase tracking-wider">Total</span>
                    <span class="font-mono text-2xl font-black text-slate-900 bg-slate-100 px-4 py-1 rounded-xl">&#8369;{{ number_format(collect($selectedOrderItems)->sum(function($i) { return $i['cost'] * $i['quantity']; }), 2) }}</span>
                </div>
                <div class="flex w-full sm:w-auto space-x-4">
                    <button type="button" wire:click="closeOrderModal" class="flex-1 sm:flex-none px-6 py-3.5 text-sm font-bold text-slate-600 bg-white border-2 border-slate-200 hover:bg-slate-50 hover:border-slate-300 rounded-xl transition-all duration-200">Cancel</button>
                    <button type="button" wire:click="submitOrder" @if(count($selectedOrderItems) === 0) disabled @endif class="flex-1 sm:flex-none px-8 py-3.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-xl shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-0.5 flex items-center justify-center">
                        <svg wire:loading wire:target="submitOrder" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Submit Order
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    </div>
</div>
