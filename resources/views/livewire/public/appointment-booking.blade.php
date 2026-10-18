<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-light-blue-50 py-12 px-4">
    <div class="max-w-3xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-600 shadow-lg shadow-emerald-200 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Book an Appointment</h1>
            <p class="mt-2 text-sm text-gray-500">Select your department, doctor, and preferred time slot. No account needed.</p>
        </div>

        @if($booked)
            {{-- ==== SUCCESS =============================== --}}
            <div class="bg-white rounded-2xl shadow-xl border border-emerald-100 p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-4">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Appointment Booked!</h2>
                <p class="mt-2 text-sm text-gray-500">Your appointment reference code is:</p>
                <p class="mt-3 text-3xl font-black tracking-wider text-emerald-600">{{ $booked_code }}</p>
                <p class="mt-4 text-sm text-gray-600">Please save this code. You may need it to check or manage your appointment.</p>
                <button wire:click="bookAnother" class="mt-6 inline-flex items-center px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-lg shadow-emerald-200 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Book Another Appointment
                </button>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Appointment Details</h2>
                            <p class="text-xs text-gray-500">Fill out the details below to schedule a visit</p>
                        </div>
                    </div>
                </div>

                <form wire:submit="book" class="px-8 py-6 space-y-6">
                    {{-- Step 1: Department + Doctor --}}
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Step 1 — Select Doctor</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Department *</label>
                                <select wire:model.live="department_id"
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('department_id') border-red-300 @enderror">
                                    <option value="">Select a department…</option>
                                    @foreach($this->departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->dept_name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Doctor *</label>
                                <select wire:model.live="health_care_professional_id" @if(!$department_id) disabled @endif
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 disabled:opacity-50 @error('health_care_professional_id') border-red-300 @enderror">
                                    <option value="">Select a doctor…</option>
                                    @foreach($this->doctors as $doc)
                                        <option value="{{ $doc->id }}">{{ $doc->full_name }}@if($doc->specialization) — {{ $doc->specialization }}@endif</option>
                                    @endforeach
                                </select>
                                @error('health_care_professional_id') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Date --}}
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Step 2 — Pick a Date</p>
                        <input type="date" wire:model.live="appointment_date"
                               min="{{ \Carbon\Carbon::now()->toDateString() }}"
                               class="w-full md:w-64 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('appointment_date') border-red-300 @enderror">
                        @error('appointment_date') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Step 3: Time slots --}}
                    @if($appointment_date && $health_care_professional_id)
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Step 3 — Choose a Time Slot</p>
                            @if(count($booked_slots) === 0)
                                <div class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 border border-amber-200">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <p class="text-sm text-amber-700">No available slots for this doctor on the selected date. Please try another date.</p>
                                </div>
                            @else
                                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                    @foreach($booked_slots as $slot)
                                        <button type="button" wire:click="selectSlot('{{ $slot['start'] }}')"
                                                @class([
                                                    'py-3 rounded-xl border text-sm font-semibold transition-all duration-150',
                                                    $selected_slot === $slot['start']
                                                        ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-200'
                                                        : 'bg-white border-gray-200 text-gray-700 hover:border-emerald-400 hover:text-emerald-700'
                                                ])>
                                            {{ $slot['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @error('selected_slot') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Step 4: Patient info --}}
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Step 4 — Your Information</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name *</label>
                                <input type="text" wire:model="patient_name" placeholder="e.g. Juan Dela Cruz"
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('patient_name') border-red-300 @enderror">
                                @error('patient_name') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" wire:model="patient_phone" placeholder="e.g. 0917 123 4567"
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                                <input type="email" wire:model="patient_email" placeholder="e.g. juan@email.com"
                                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('patient_email') border-red-300 @enderror">
                                @error('patient_email') <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Reason for Visit</label>
                                <textarea wire:model="reason" rows="3" placeholder="Briefly describe your concern or symptoms (optional)"
                                          class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center px-6 py-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-base font-bold shadow-lg shadow-emerald-200 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Confirm Booking
                        </button>
                    </div>
                </form>
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">© {{ date('Y') }} SITHEMR · Hospital Electronic Medical Records</p>
        @endif
    </div>
</div>
