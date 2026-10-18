<div class="p-6 space-y-6">
    <h2 class="text-2xl font-bold text-emerald-800">Patient Medication Record</h2>

    @if (!$selectedPatientId)
        <div class="bg-white rounded-lg shadow-sm border border-emerald-200 p-8">
            <div class="text-center max-w-md mx-auto">
                <svg class="w-16 h-16 text-emerald-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <h3 class="text-lg font-medium text-emerald-700 mb-2">Search for a Patient</h3>
                <p class="text-sm text-emerald-500 mb-6">Enter a patient name, code, or identifier to view their complete medication history.</p>
            </div>

            <div class="max-w-xl mx-auto" x-data="{ open: false }">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.200ms="patient_search"
                        placeholder="Search by name or patient code..."
                        class="w-full pl-10 pr-4 py-3 border-2 border-emerald-300 rounded-xl text-sm text-emerald-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        autocomplete="off"
                        @focus="open = true"
                        @click.away="if ($wire.patient_search.length < 2) open = false"
                    />
                </div>

                @if (count($patientResults) > 0)
                    <div class="mt-2 bg-white border-2 border-emerald-200 rounded-xl shadow-lg overflow-hidden">
                        @foreach ($patientResults as $patient)
                            <button
                                type="button"
                                wire:click="selectPatient({{ $patient['id'] }})"
                                class="w-full text-left px-4 py-3 text-sm text-emerald-800 hover:bg-emerald-50 border-b border-emerald-100 last:border-0 transition-colors"
                            >
                                <div class="font-medium">{{ $patient['last_name'] }}, {{ $patient['first_name'] }} {{ $patient['middle_name'] ?? '' }}</div>
                                <div class="text-xs text-emerald-500 font-mono">{{ $patient['patient_code'] }}</div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-emerald-200 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-full">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-emerald-800">{{ $selectedPatientName }}</h3>
                        <p class="text-xs text-emerald-500 font-mono">Patient ID: {{ $selectedPatientId }}</p>
                    </div>
                </div>
                <button
                    wire:click="clearPatient"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Change Patient
                </button>
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($requests as $request)
                <div x-data="{ open: false }" class="bg-white rounded-lg shadow-sm border border-emerald-200 overflow-hidden">
                    <div
                        class="px-4 py-3 bg-emerald-50 flex items-center justify-between cursor-pointer hover:bg-emerald-100 transition-colors"
                        @click="open = !open"
                    >
                        <div class="flex items-center gap-4">
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">{{ $request->request_code }}</p>
                                <p class="text-xs text-emerald-500">{{ $request->created_date ? $request->created_date->format('M d, Y h:i A') : '—' }}</p>
                            </div>
                            <div class="text-xs text-emerald-600">
                                <div>{{ $request->requestingDepartment?->name ?? '—' }}</div>
                                <div class="text-emerald-400">Dr. {{ $request->requestingDoctor?->last_name ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $request->status === 'Complete' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $request->status === 'Pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $request->status === 'Cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $request->status === 'Dispensed' ? 'bg-blue-100 text-blue-700' : '' }}">
                                {{ $request->status ?? 'Draft' }}
                            </span>
                            <svg class="w-4 h-4 text-emerald-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="open" x-cloak>
                        @if ($request->items && $request->items->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-emerald-100">
                                    <thead class="bg-emerald-50/50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-emerald-600 uppercase tracking-wider">Drug</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-emerald-600 uppercase tracking-wider">Dosage</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-emerald-600 uppercase tracking-wider">Frequency</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-emerald-600 uppercase tracking-wider">Route</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-emerald-600 uppercase tracking-wider">Duration</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-emerald-600 uppercase tracking-wider">Qty Given</th>
                                            <th class="px-4 py-2 text-center text-xs font-medium text-emerald-600 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-emerald-50">
                                        @foreach ($request->items as $item)
                                            <tr class="hover:bg-emerald-50/50">
                                                <td class="px-4 py-2 text-sm text-emerald-800">
                                                    <div class="font-medium">{{ $item->drug?->generic_name ?? '—' }}</div>
                                                    <div class="text-xs text-emerald-400">{{ $item->drug?->drug_code ?? '' }}</div>
                                                </td>
                                                <td class="px-4 py-2 text-sm text-center text-emerald-700">{{ $item->dosage ?? '—' }}</td>
                                                <td class="px-4 py-2 text-sm text-center text-emerald-700">{{ $item->frequency ?? '—' }}</td>
                                                <td class="px-4 py-2 text-sm text-center text-emerald-700">{{ $item->route ?? '—' }}</td>
                                                <td class="px-4 py-2 text-sm text-center text-emerald-700">{{ $item->duration ?? '—' }} {{ $item->duration_unit ?? '' }}</td>
                                                <td class="px-4 py-2 text-sm text-right font-semibold text-emerald-800">{{ number_format($item->given_quantity ?? 0) }}</td>
                                                <td class="px-4 py-2 text-sm text-center">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $item->is_given ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                                        {{ $item->is_given ? 'Given' : 'Pending' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-4 py-3 text-sm text-emerald-500 italic text-center">No medication items found for this request.</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow-sm border border-emerald-200 p-8 text-center">
                    <svg class="w-12 h-12 text-emerald-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-emerald-600 font-medium">No medication history found</p>
                    <p class="text-sm text-emerald-400 mt-1">This patient has no completed pharmacy requests on record.</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
