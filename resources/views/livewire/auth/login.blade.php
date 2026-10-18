<div class="min-h-screen flex">

    {{-- ═══════════════════════════════════════════════════
         LEFT PANEL — Branding
    ═══════════════════════════════════════════════════ --}}
    <div class="hidden lg:flex lg:w-1/2 relative flex-col overflow-hidden"
         style="background: linear-gradient(145deg, #0369a1 0%, #0284c7 35%, #0ea5e9 70%, #38bdf8 100%);">

        {{-- Animated background orbs --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            {{-- Large white orb top-left --}}
            <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full"
                 style="background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%); animation: pulse-slow 6s ease-in-out infinite;"></div>
            {{-- Secondary orb right --}}
            <div class="absolute top-1/3 -right-24 w-80 h-80 rounded-full"
                 style="background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%); animation: pulse-slow 8s ease-in-out infinite 2s;"></div>
            {{-- Bottom orb --}}
            <div class="absolute -bottom-28 left-1/4 w-80 h-80 rounded-full"
                 style="background: radial-gradient(circle, rgba(3,105,161,0.5) 0%, transparent 70%); animation: pulse-slow 7s ease-in-out infinite 1s;"></div>

            {{-- Grid lines overlay --}}
            <svg class="absolute inset-0 w-full h-full" style="opacity:0.08;" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="44" height="44" patternUnits="userSpaceOnUse">
                        <path d="M 44 0 L 0 0 0 44" fill="none" stroke="white" stroke-width="0.6"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>

            {{-- Floating particles --}}
            <div class="absolute top-1/4 left-1/3 w-2 h-2 rounded-full bg-white opacity-40"
                 style="animation: float 4s ease-in-out infinite;"></div>
            <div class="absolute top-1/2 left-3/4 w-1.5 h-1.5 rounded-full bg-white opacity-30"
                 style="animation: float 5s ease-in-out infinite 1s;"></div>
            <div class="absolute top-3/4 left-1/4 w-2 h-2 rounded-full bg-white opacity-25"
                 style="animation: float 6s ease-in-out infinite 0.5s;"></div>
            <div class="absolute top-1/6 left-2/3 w-1.5 h-1.5 rounded-full bg-white opacity-35"
                 style="animation: float 4.5s ease-in-out infinite 2s;"></div>

            {{-- Large decorative ring --}}
            <div class="absolute -bottom-20 -right-20 w-80 h-80 rounded-full"
                 style="border: 2px solid rgba(255,255,255,0.12);"></div>
            <div class="absolute -bottom-10 -right-10 w-60 h-60 rounded-full"
                 style="border: 2px solid rgba(255,255,255,0.08);"></div>
        </div>

        {{-- Content --}}
        <div class="relative z-10 flex flex-col h-full px-14 py-16">

            {{-- Logo + Brand --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3);">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 2h4v6h6v4h-6v6h-4v-6H4v-4h6z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg tracking-wide" style="text-shadow: 0 1px 4px rgba(0,0,0,0.2);">SITHEMR</span>
            </div>

            {{-- Hero text --}}
            <div class="flex-1 flex flex-col justify-center">
                <div class="mb-8">
                    {{-- Badge --}}
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full mb-6 text-xs font-semibold tracking-widest uppercase"
                         style="background: rgba(255,255,255,0.18); backdrop-filter: blur(6px); border: 1px solid rgba(255,255,255,0.3); color: white;">
                        <div class="w-1.5 h-1.5 rounded-full bg-white" style="animation: ping-slow 2s ease-in-out infinite;"></div>
                        Healthcare Management Platform
                    </div>

                    <h1 class="text-5xl font-extrabold leading-tight mb-4 text-white"
                        style="text-shadow: 0 2px 12px rgba(3,105,161,0.4);">
                        Smart Clinical<br>
                        <span style="opacity: 0.85;">Management</span>
                    </h1>
                    <p class="text-base leading-relaxed max-w-sm" style="color: rgba(255,255,255,0.80);">
                        A unified electronic medical records platform built for Philippine hospitals — streamlining patient care, PhilHealth compliance, and clinical workflows.
                    </p>
                </div>

                {{-- Feature list --}}
                <div class="flex flex-col gap-3.5">
                    @foreach([
                        ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',           'text' => 'PhilHealth eClaims & OECB Ready'],
                        ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 8v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'text' => 'Full In-Patient & ER Workflow'],
                        ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'text' => 'Real-Time Billing & Analytics'],
                    ] as $feat)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feat['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium" style="color: rgba(255,255,255,0.85);">{{ $feat['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Bottom stats --}}
            <div class="flex gap-10 pt-8" style="border-top: 1px solid rgba(255,255,255,0.2);">
                @foreach([
                    ['num' => '15+',  'label' => 'Clinical Modules'],
                    ['num' => 'CF1–5','label' => 'PhilHealth Forms'],
                    ['num' => 'OECB', 'label' => 'Annex A&B Ready'],
                ] as $stat)
                <div>
                    <div class="text-2xl font-extrabold text-white">{{ $stat['num'] }}</div>
                    <div class="text-xs mt-0.5" style="color: rgba(255,255,255,0.65);">{{ $stat['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         RIGHT PANEL — Login Form (Light Blue)
    ═══════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-h-screen"
         style="background: linear-gradient(160deg, #f0f9ff 0%, #e0f2fe 50%, #f0f9ff 100%);">

        {{-- Mobile logo --}}
        <div class="flex lg:hidden items-center gap-3 px-8 pt-8">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                 style="background: linear-gradient(135deg, #0ea5e9, #0369a1);">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 2h4v6h6v4h-6v6h-4v-6H4v-4h6z"/>
                </svg>
            </div>
            <span class="font-bold text-sky-800">SITHEMR</span>
        </div>

        {{-- Centered form --}}
        <div class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">

                {{-- Card --}}
                <div class="rounded-2xl px-10 py-10"
                     style="background: rgba(255,255,255,0.85); backdrop-filter: blur(16px); border: 1px solid rgba(186,230,253,0.7); box-shadow: 0 8px 40px rgba(14,165,233,0.12), 0 2px 8px rgba(3,105,161,0.06);">

                    {{-- Form header --}}
                    <div class="mb-8 text-center">
                        {{-- Logo icon --}}
                        <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                             style="background: linear-gradient(135deg, #0ea5e9, #0369a1); box-shadow: 0 4px 20px rgba(14,165,233,0.35);">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 2h4v6h6v4h-6v6h-4v-6H4v-4h6z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold" style="color: #0c4a6e;">Welcome back</h2>
                        <p class="text-sm mt-1" style="color: #64748b;">Sign in to access your clinical dashboard</p>
                    </div>

                    {{-- Flash error from session --}}
                    @if(session('error'))
                        <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl text-sm"
                             style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- Livewire error --}}
                    @if($error)
                        <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl text-sm"
                             style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $error }}</span>
                        </div>
                    @endif

                    {{-- Form --}}
                    <form wire:submit="login" class="space-y-5">

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-widest mb-2"
                                   style="color: #0369a1;">Email Address</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="#7dd3fc" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <input wire:model="email"
                                       id="email"
                                       type="email"
                                       autocomplete="email"
                                       placeholder="you@sithemr.com"
                                       class="w-full pl-10 pr-4 py-3 text-sm rounded-xl transition-all duration-200 focus:outline-none"
                                       style="background: #f0f9ff; border: 1.5px solid {{ $errors->has('email') ? '#fca5a5' : '#bae6fd' }}; color: #0c4a6e;"
                                       onfocus="this.style.border='1.5px solid #0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14,165,233,0.15)';"
                                       onblur="this.style.border='1.5px solid #bae6fd'; this.style.boxShadow='none';">
                            </div>
                            @error('email')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div x-data="{ show: false }">
                            <label for="password" class="block text-xs font-semibold uppercase tracking-widest mb-2"
                                   style="color: #0369a1;">Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="#7dd3fc" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                <input wire:model="password"
                                       id="password"
                                       :type="show ? 'text' : 'password'"
                                       autocomplete="current-password"
                                       placeholder="••••••••••"
                                       class="w-full pl-10 pr-11 py-3 text-sm rounded-xl transition-all duration-200 focus:outline-none"
                                       style="background: #f0f9ff; border: 1.5px solid {{ $errors->has('password') ? '#fca5a5' : '#bae6fd' }}; color: #0c4a6e;"
                                       onfocus="this.style.border='1.5px solid #0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14,165,233,0.15)';"
                                       onblur="this.style.border='1.5px solid #bae6fd'; this.style.boxShadow='none';">
                                <button type="button"
                                        @click="show = !show"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 transition-colors duration-150"
                                        style="color: #7dd3fc;"
                                        @mouseenter="$el.style.color='#0ea5e9'"
                                        @mouseleave="$el.style.color='#7dd3fc'">
                                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="pt-2">
                            <button type="submit"
                                    id="btn-login"
                                    class="w-full py-3.5 px-4 font-semibold text-sm rounded-xl text-white relative overflow-hidden focus:outline-none transition-all duration-200"
                                    style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%); box-shadow: 0 4px 20px rgba(14,165,233,0.4);"
                                    onmouseenter="this.style.boxShadow='0 6px 28px rgba(14,165,233,0.55)'; this.style.transform='translateY(-1px)';"
                                    onmouseleave="this.style.boxShadow='0 4px 20px rgba(14,165,233,0.4)'; this.style.transform='translateY(0)';">

                                {{-- Shimmer --}}
                                <span class="absolute inset-0 -translate-x-full"
                                      style="background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent); animation: shimmer 2.5s ease-in-out infinite;"></span>

                                <span wire:loading.remove wire:target="login" class="relative flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                    </svg>
                                    Sign In to Dashboard
                                </span>
                                <span wire:loading wire:target="login" class="relative flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Authenticating…
                                </span>
                            </button>
                        </div>

                    </form>

                    {{-- Patient booking link --}}
                    <div class="mt-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex-1 h-px" style="background: #bae6fd;"></div>
                            <span class="text-xs font-medium" style="color: #94a3b8;">OR</span>
                            <div class="flex-1 h-px" style="background: #bae6fd;"></div>
                        </div>
                        <a href="/book-appointment"
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 font-semibold text-sm rounded-xl transition-all duration-200"
                           style="background: #ffffff; border: 1.5px solid #bae6fd; color: #0284c7;"
                           onmouseenter="this.style.border='1.5px solid #0ea5e9'; this.style.boxShadow='0 0 0 3px rgba(14,165,233,0.15)';"
                           onmouseleave="this.style.border='1.5px solid #bae6fd'; this.style.boxShadow='none';">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Book an Appointment
                        </a>
                    </div>

                    {{-- Security note --}}
                    <div class="mt-6 flex items-center justify-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="#7dd3fc" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="text-xs" style="color: #94a3b8;">256-bit encrypted · Secure session · Multi-tenant isolated</span>
                    </div>

                </div>{{-- /card --}}

                {{-- Footer --}}
                <p class="text-center text-xs mt-8" style="color: #94a3b8;">
                    &copy; {{ date('Y') }} SITHEMR &nbsp;·&nbsp; Hospital Electronic Medical Records
                </p>

            </div>
        </div>
    </div>

</div>

{{-- Animations --}}
<style>
    @keyframes pulse-slow {
        0%, 100% { opacity: 0.18; transform: scale(1); }
        50%       { opacity: 0.32; transform: scale(1.1); }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-14px); }
    }
    @keyframes ping-slow {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: 0.4; transform: scale(1.6); }
    }
    @keyframes shimmer {
        0%   { transform: translateX(-100%); }
        60%  { transform: translateX(100%); }
        100% { transform: translateX(100%); }
    }
    #email::placeholder, #password::placeholder {
        color: #93c5fd;
    }
</style>
