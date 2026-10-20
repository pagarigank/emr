<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'SITHEMR Dashboard' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans antialiased text-gray-900 overflow-hidden">
        
        <div class="h-screen flex flex-col" x-data="{ sidebarOpen: true }">
            
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 z-20 sticky top-0">
                <div class="flex items-center justify-between px-4 h-16">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-light-blue-600 focus:outline-none transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div class="flex items-center">
                            <img src="{{ asset('images/logo.png') }}" alt="SITHEMR Logo" class="h-8 w-auto mr-2">
                            <h1 class="text-light-blue-800 text-xl font-bold tracking-tight hidden sm:block">
                                SITHEMR
                            </h1>
                        </div>
                    </div>

                    <!-- Right Side Dropdown (Login Details) -->
                    <div class="flex items-center" x-data="{ userMenuOpen: false }">
                        <div class="relative">
                            <button @click="userMenuOpen = !userMenuOpen" @click.away="userMenuOpen = false" class="flex items-center space-x-2 text-gray-700 hover:text-light-blue-600 focus:outline-none transition-colors">
                                <div class="w-8 h-8 rounded-full bg-light-blue-100 flex items-center justify-center text-light-blue-700 font-bold text-sm">
                                    {{ strtoupper(substr(session('jwt_user.full_name', 'U'), 0, 1)) }}
                                </div>
                                <div class="hidden md:block text-left">
                                    <span class="block font-medium text-gray-800 text-sm">{{ session('jwt_user.full_name', 'User') }}</span>
                                    <span class="block text-xs {{ session('jwt_user.role') === 'superadmin' ? 'text-purple-500' : 'text-light-blue-500' }} capitalize">{{ session('jwt_user.role', 'user') }}</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': userMenuOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="userMenuOpen" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95 translate-y-[-10px]"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 translate-y-[-10px]"
                                 class="absolute right-0 mt-3 w-48 bg-white rounded-md shadow-lg border border-gray-100 py-1 z-30" style="display: none;">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-900">{{ session('jwt_user.full_name', 'User') }}</p>
                                    <p class="text-xs text-gray-500">{{ session('jwt_user.email', '') }}</p>
                                    @if(session('jwt_user.role') === 'superadmin')
                                        <span class="inline-block mt-1 px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full font-medium">Super Admin</span>
                                    @else
                                        <span class="inline-block mt-1 px-2 py-0.5 bg-light-blue-100 text-light-blue-700 text-xs rounded-full font-medium">{{ ucfirst(str_replace('_', ' ', session('jwt_user.role', 'user'))) }}</span>
                                    @endif
                                </div>
                                <a href="/settings" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-light-blue-50 hover:text-light-blue-700 transition-colors">Profile & Settings</a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <livewire:auth.logout />
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 flex overflow-hidden">
                <!-- Left Sidebar Menu -->
                <nav class="bg-white shadow-xl border-r border-gray-200 flex-shrink-0 transition-all duration-300 ease-in-out z-10"
                     :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full overflow-hidden'"
                     style="width: 16rem;">
                    
                    <div class="h-full overflow-y-auto py-4 space-y-4 scrollbar-thin scrollbar-thumb-gray-200">
                        
                        <!-- ================= MAIN ================= -->
                        <div class="px-3 space-y-1">
                            <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Main</p>
                            
                            <a href="/" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-light-blue-50 hover:text-light-blue-700 transition-colors group {{ request()->is('/') ? 'bg-light-blue-50 text-light-blue-700 font-semibold' : '' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('/') ? 'text-light-blue-600' : 'text-light-blue-400 group-hover:text-light-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                Dashboard
                            </a>

                            @can('doctors.dashboard')
                                <a href="{{ route('doctors.dashboard') }}" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors group {{ request()->routeIs('doctors.dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('doctors.dashboard') ? 'text-emerald-600' : 'text-emerald-400 group-hover:text-emerald-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Doctors Portal
                                </a>
                            @endcan

                            @can('doctors.schedule')
                                <a href="{{ route('doctors.schedule') }}" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors group {{ request()->routeIs('doctors.schedule') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('doctors.schedule') ? 'text-emerald-600' : 'text-emerald-400 group-hover:text-emerald-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Patient Scheduling
                                </a>
                            @endcan

                            @can('patient.view')
                            <a href="/patients" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-light-blue-50 hover:text-light-blue-700 transition-colors group {{ request()->is('patients') ? 'bg-light-blue-50 text-light-blue-700 font-semibold' : '' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('patients') ? 'text-light-blue-600' : 'text-light-blue-400 group-hover:text-light-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                Patients
                            </a>
                            @endcan

                            @can('medical-records.view')
                            <a href="/medical-records" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-violet-50 hover:text-violet-700 transition-colors group {{ request()->is('medical-records*') ? 'bg-violet-50 text-violet-700 font-semibold' : '' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('medical-records*') ? 'text-violet-600' : 'text-violet-400 group-hover:text-violet-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Medical Records
                            </a>
                            @endcan
                        </div>

                        <!-- ================= CLINICAL MODULES ================= -->
                        <div class="px-3 space-y-1">
                            <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-2">Clinical Modules</p>

                            <!-- OPD -->
                            @can('opd.view')
                            <div x-data="{ openOPD: {{ request()->is('opd/*') ? 'true' : 'false' }} }">
                                <button @click="openOPD = !openOPD" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-indigo-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                        <span>OPD</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openOPD}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openOPD" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('opd.view')
                                    <a href="/opd/consultations" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition-colors {{ request()->is('opd/consultations') ? 'hover:text-indigo-700 hover:bg-indigo-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Consultations</span>
                                    </a>
                                    @endcan
                                    @can('admin.masters')
                                    <a href="/opd/visit-types" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition-colors {{ request()->is('opd/visit-types') ? 'hover:text-indigo-700 hover:bg-indigo-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Visit Types</span>
                                    </a>
                                    <a href="/opd/consultation-types" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition-colors {{ request()->is('opd/consultation-types') ? 'hover:text-indigo-700 hover:bg-indigo-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Consultation Types</span>
                                    </a>
                                    <a href="/opd/referral-sources" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition-colors {{ request()->is('opd/referral-sources') ? 'hover:text-indigo-700 hover:bg-indigo-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg><span>Referral Sources</span>
                                    </a>
                                    <a href="/opd/services" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition-colors {{ request()->is('opd/services') ? 'hover:text-indigo-700 hover:bg-indigo-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><span>Services</span>
                                    </a>
                                    <a href="/opd/clinics" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition-colors {{ request()->is('opd/clinics') ? 'hover:text-indigo-700 hover:bg-indigo-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Clinics</span>
                                    </a>
                                    <a href="/opd/complaints" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-indigo-400 hover:text-indigo-700 hover:bg-indigo-50 transition-colors {{ request()->is('opd/complaints') ? 'hover:text-indigo-700 hover:bg-indigo-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><span>Complaints</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- ER -->
                            @can('er.triage')
                            <div x-data="{ openER: {{ request()->is('er/*') ? 'true' : 'false' }} }">
                                <button @click="openER = !openER" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-rose-50 hover:text-rose-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-rose-400 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Emergency Room</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openER}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openER" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('er.triage')
                                    <a href="/er/triage" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('er/triage') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Triage Records</span>
                                    </a>
                                    @endcan
                                    @can('er.manage')
                                    <a href="/er/area" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('er/area') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>ER Area</span>
                                    </a>
                                    @endcan
                                    @can('admin.masters')
                                    <a href="/er/acuity-levels" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('er/acuity-levels') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Acuity Levels</span>
                                    </a>
                                    <a href="/er/mobility-statuses" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('er/mobility-statuses') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg><span>Mobility Statuses</span>
                                    </a>
                                    <a href="/er/zones" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('er/zones') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>ER Zones</span>
                                    </a>
                                    <a href="/er/beds" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('er/beds') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>ER Beds</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- Admission & Wards -->
                            @can('admission.view')
                            <div x-data="{ openAdmWards: {{ (request()->is('admission') || request()->is('ward/*') || request()->is('transfers')) ? 'true' : 'false' }} }">
                                <button @click="openAdmWards = !openAdmWards" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50 hover:text-teal-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-teal-400 group-hover:text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span>Admission & Wards</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openAdmWards}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openAdmWards" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('admission.view')
                                    <a href="/admission" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-teal-400 hover:text-teal-700 hover:bg-teal-50 transition-colors {{ request()->is('admission') ? 'hover:text-teal-700 hover:bg-teal-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Admissions</span>
                                    </a>
                                    @endcan
                                    @can('admin.masters')
                                    <a href="/ward/wards" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-teal-400 hover:text-teal-700 hover:bg-teal-50 transition-colors {{ request()->is('ward/wards') ? 'hover:text-teal-700 hover:bg-teal-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Wards</span>
                                    </a>
                                    <a href="/ward/room-types" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-teal-400 hover:text-teal-700 hover:bg-teal-50 transition-colors {{ request()->is('ward/room-types') ? 'hover:text-teal-700 hover:bg-teal-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Room Types</span>
                                    </a>
                                    <a href="/ward/rooms" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-teal-400 hover:text-teal-700 hover:bg-teal-50 transition-colors {{ request()->is('ward/rooms') ? 'hover:text-teal-700 hover:bg-teal-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Rooms</span>
                                    </a>
                                    @endcan
                                    @can('admission.view')
                                    <a href="/transfers" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-teal-400 hover:text-teal-700 hover:bg-teal-50 transition-colors {{ request()->is('transfers') ? 'hover:text-teal-700 hover:bg-teal-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Hospital Transfers</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- Nursing -->
                            @can('nursing.view')
                            <a href="/nursing" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors group {{ request()->is('nursing') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('nursing') ? 'text-emerald-600' : 'text-emerald-400 group-hover:text-emerald-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                Nursing
                            </a>
                            @endcan

                            <!-- OB -->
                            @can('ob.view')
                            <div x-data="{ openOB: {{ request()->is('ob/*') ? 'true' : 'false' }} }">
                                <button @click="openOB = !openOB" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-pink-50 hover:text-pink-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-pink-400 group-hover:text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                        <span>Obstetrics (OB)</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openOB}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openOB" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('ob.view')
                                    <a href="/ob/consultations" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-pink-400 hover:text-pink-700 hover:bg-pink-50 transition-colors {{ request()->is('ob/consultations') ? 'hover:text-pink-700 hover:bg-pink-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Consultations</span>
                                    </a>
                                    @endcan
                                    @can('admin.masters')
                                    <a href="/ob/visit-types" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-pink-400 hover:text-pink-700 hover:bg-pink-50 transition-colors {{ request()->is('ob/visit-types') ? 'hover:text-pink-700 hover:bg-pink-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Visit Types</span>
                                    </a>
                                    <a href="/ob/fetal-presentations" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-pink-400 hover:text-pink-700 hover:bg-pink-50 transition-colors {{ request()->is('ob/fetal-presentations') ? 'hover:text-pink-700 hover:bg-pink-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Fetal Presentations</span>
                                    </a>
                                    <a href="/ob/contraceptive-methods" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-pink-400 hover:text-pink-700 hover:bg-pink-50 transition-colors {{ request()->is('ob/contraceptive-methods') ? 'hover:text-pink-700 hover:bg-pink-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Contraceptive Methods</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- Delivery -->
                            @can('delivery.view')
                            <div x-data="{ openDelivery: {{ request()->is('delivery/*') ? 'true' : 'false' }} }">
                                <button @click="openDelivery = !openDelivery" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-rose-50 hover:text-rose-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-rose-400 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M3 16h18M5 8h14a2 2 0 012 2v4H3v-4a2 2 0 012-2z"/></svg>
                                        <span>Delivery</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openDelivery}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openDelivery" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('delivery.view')
                                    <a href="/delivery/schedule" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('delivery/schedule') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Schedule</span>
                                    </a>
                                    @endcan
                                    @can('admin.masters')
                                    <a href="/delivery/rooms" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('delivery/rooms') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Rooms</span>
                                    </a>
                                    <a href="/delivery/types" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('delivery/types') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Types</span>
                                    </a>
                                    <a href="/delivery/complications" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-rose-400 hover:text-rose-700 hover:bg-rose-50 transition-colors {{ request()->is('delivery/complications') ? 'hover:text-rose-700 hover:bg-rose-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><span>Complications</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- OR -->
                            @can('or.view')
                            <div x-data="{ openOr: {{ request()->is('or/*') ? 'true' : 'false' }} }">
                                <button @click="openOr = !openOr" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-slate-50 hover:text-slate-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243zm5.656 5.656a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/></svg>
                                        <span>Operating Room</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openOr}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openOr" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('or.view')
                                    <a href="/or/schedule" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('or/schedule') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>OR Schedule</span>
                                    </a>
                                    @endcan
                                    @can('admin.masters')
                                    <a href="/or/rooms" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('or/rooms') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>OR Rooms</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- Renal Care -->
                            @can('renal.view')
                            <div x-data="{ openRenal: {{ request()->is('renal-care/*') ? 'true' : 'false' }} }">
                                <button @click="openRenal = !openRenal" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-cyan-50 hover:text-cyan-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-cyan-400 group-hover:text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2.75a10 10 0 100 20 10 10 0 000-20zM12 4a8 8 0 110 16A8 8 0 0112 4zm0 2a6 6 0 100 12A6 6 0 0012 6zm0 2a4 4 0 110 8 4 4 0 010-8z"/></svg>
                                        <span>Renal Care</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openRenal}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openRenal" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('renal.view')
                                    <a href="/renal-care/dialysis-sessions" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-cyan-400 hover:text-cyan-700 hover:bg-cyan-50 transition-colors {{ request()->is('renal-care/dialysis-sessions') ? 'hover:text-cyan-700 hover:bg-cyan-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Dialysis Sessions</span>
                                    </a>
                                    @endcan
                                    @can('admin.masters')
                                    <a href="/renal-care/dialysis-types" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-cyan-400 hover:text-cyan-700 hover:bg-cyan-50 transition-colors {{ request()->is('renal-care/dialysis-types') ? 'hover:text-cyan-700 hover:bg-cyan-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Dialysis Types</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan
                        </div>

                        <!-- ================= ANCILLARY & LOGISTICS ================= -->
                        <div class="px-3 space-y-1">
                            <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-2">Ancillary & Logistics</p>

                            <!-- Laboratory -->
                            @can('lab.view')
                            <div x-data="{ openLab: {{ request()->is('lab/*') ? 'true' : 'false' }} }">
                                <button @click="openLab = !openLab" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-amber-400 group-hover:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                        <span>Laboratory</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openLab}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openLab" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('lab.dispense')
                                    <a href="/lab/dispensing" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors {{ request()->is('lab/dispensing') ? 'hover:text-amber-700 hover:bg-amber-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Dispensing</span>
                                    </a>
                                    @endcan
                                    @can('lab.result')
                                    <a href="/lab/results" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors {{ request()->is('lab/results') ? 'hover:text-amber-700 hover:bg-amber-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg><span>Results</span>
                                    </a>
                                    @endcan
                                    @can('lab.manage')
                                    <a href="/lab/tests" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors {{ request()->is('lab/tests') ? 'hover:text-amber-700 hover:bg-amber-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Test Catalog</span>
                                    </a>
                                    <a href="/lab/panels" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors {{ request()->is('lab/panels') ? 'hover:text-amber-700 hover:bg-amber-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Panels</span>
                                    </a>
                                    <a href="/lab/reference-ranges" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors {{ request()->is('lab/reference-ranges') ? 'hover:text-amber-700 hover:bg-amber-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Reference Ranges</span>
                                    </a>
                                    <a href="/lab/sections" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors {{ request()->is('lab/sections') ? 'hover:text-amber-700 hover:bg-amber-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Sections</span>
                                    </a>
                                    <a href="/lab/units" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors {{ request()->is('lab/units') ? 'hover:text-amber-700 hover:bg-amber-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Units</span>
                                    </a>
                                    <a href="/lab/specimen-types" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors {{ request()->is('lab/specimen-types') ? 'hover:text-amber-700 hover:bg-amber-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Specimen Types</span>
                                    </a>
                                    <a href="/lab/instruments" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-amber-400 hover:text-amber-700 hover:bg-amber-50 transition-colors {{ request()->is('lab/instruments') ? 'hover:text-amber-700 hover:bg-amber-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Instruments</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- Radiology -->
                            @can('radiology.view')
                            <div x-data="{ openRad: {{ request()->is('radiology/*') ? 'true' : 'false' }} }">
                                <button @click="openRad = !openRad" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-purple-400 group-hover:text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Radiology</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openRad}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openRad" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('radiology.result')
                                    <a href="/radiology/results" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-purple-400 hover:text-purple-700 hover:bg-purple-50 transition-colors {{ request()->is('radiology/results') ? 'hover:text-purple-700 hover:bg-purple-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Results</span>
                                    </a>
                                    @endcan
                                    @can('radiology.dispense')
                                    <a href="/radiology/dispensing" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-purple-400 hover:text-purple-700 hover:bg-purple-50 transition-colors {{ request()->is('radiology/dispensing') ? 'hover:text-purple-700 hover:bg-purple-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Dispensing</span>
                                    </a>
                                    @endcan
                                    @can('radiology.manage')
                                    <a href="/radiology/exam-types" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-purple-400 hover:text-purple-700 hover:bg-purple-50 transition-colors {{ request()->is('radiology/exam-types') ? 'hover:text-purple-700 hover:bg-purple-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Exam Types</span>
                                    </a>
                                    <a href="/radiology/modalities" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-purple-400 hover:text-purple-700 hover:bg-purple-50 transition-colors {{ request()->is('radiology/modalities') ? 'hover:text-purple-700 hover:bg-purple-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Modalities</span>
                                    </a>
                                    <a href="/radiology/body-parts" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-purple-400 hover:text-purple-700 hover:bg-purple-50 transition-colors {{ request()->is('radiology/body-parts') ? 'hover:text-purple-700 hover:bg-purple-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Body Parts</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- Pharmacy -->
                            @can('pharmacy.view')
                            <div x-data="{ openPharmacy: {{ request()->is('pharmacy/*') ? 'true' : 'false' }} }">
                                <button @click="openPharmacy = !openPharmacy" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-emerald-400 group-hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Pharmacy</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openPharmacy}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openPharmacy" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('pharmacy.dispense')
                                    <a href="/pharmacy/dispensing" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('pharmacy/dispensing') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Dispensing</span>
                                    </a>
                                    @endcan
                                    @can('pharmacy.manage')
                                    <a href="/pharmacy/drugs" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('pharmacy/drugs') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Drug Formulary</span>
                                    </a>
                                    <a href="/pharmacy/inventory" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('pharmacy/inventory') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Stock Dashboard</span>
                                    </a>
                                    <a href="/pharmacy/drug-batches" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('pharmacy/drug-batches') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Drug Batches</span>
                                    </a>
                                    <a href="/pharmacy/purchase-orders" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('pharmacy/purchase-orders') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Purchase Orders</span>
                                    </a>
                                    <a href="/pharmacy/stock-adjustments" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('pharmacy/stock-adjustments') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Stock Adjustments</span>
                                    </a>
                                    @endcan
                                    @can('pharmacy.view')
                                    <a href="/pharmacy/patient-medication-record" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('pharmacy/patient-medication-record') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Patient Med Record</span>
                                    </a>
                                    @endcan
                                    @can('reports.view')
                                    <a href="/pharmacy/reports" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('pharmacy/reports') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg><span>Reports</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- Procedures -->
                            @can('procedure.view')
                            <div x-data="{ openProcedures: {{ request()->is('procedures/*') ? 'true' : 'false' }} }">
                                <button @click="openProcedures = !openProcedures" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-sky-50 hover:text-sky-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-sky-400 group-hover:text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Procedures</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openProcedures}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openProcedures" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('procedure.dispense')
                                    <a href="/procedures/dispensing" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-sky-400 hover:text-sky-700 hover:bg-sky-50 transition-colors {{ request()->is('procedures/dispensing') ? 'hover:text-sky-700 hover:bg-sky-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Dispensing</span>
                                    </a>
                                    @endcan
                                    @can('procedure.manage')
                                    <a href="/procedures/procedures" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-sky-400 hover:text-sky-700 hover:bg-sky-50 transition-colors {{ request()->is('procedures/procedures') ? 'hover:text-sky-700 hover:bg-sky-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Procedures</span>
                                    </a>
                                    <a href="/procedures/categories" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-sky-400 hover:text-sky-700 hover:bg-sky-50 transition-colors {{ request()->is('procedures/categories') ? 'hover:text-sky-700 hover:bg-sky-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Categories</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- CSR / Supply -->
                            @can('csr.view')
                            <div x-data="{ openSupply: {{ request()->is('supply/*') ? 'true' : 'false' }} }">
                                <button @click="openSupply = !openSupply" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-orange-400 group-hover:text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        <span>CSR / Supply</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openSupply}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openSupply" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('csr.dispense')
                                    <a href="/supply/dispensing" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-orange-400 hover:text-orange-700 hover:bg-orange-50 transition-colors {{ request()->is('supply/dispensing') ? 'hover:text-orange-700 hover:bg-orange-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Dispensing</span>
                                    </a>
                                    @endcan
                                    @can('csr.manage')
                                    <a href="/supply/items" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-orange-400 hover:text-orange-700 hover:bg-orange-50 transition-colors {{ request()->is('supply/items') ? 'hover:text-orange-700 hover:bg-orange-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Items</span>
                                    </a>
                                    <a href="/supply/inventory" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-orange-400 hover:text-orange-700 hover:bg-orange-50 transition-colors {{ request()->is('supply/inventory') ? 'hover:text-orange-700 hover:bg-orange-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Inventory</span>
                                    </a>
                                    <a href="/supply/stock-adjustments" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-orange-400 hover:text-orange-700 hover:bg-orange-50 transition-colors {{ request()->is('supply/stock-adjustments') ? 'hover:text-orange-700 hover:bg-orange-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Stock Adjustments</span>
                                    </a>
                                    <a href="/supply/categories" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-orange-400 hover:text-orange-700 hover:bg-orange-50 transition-colors {{ request()->is('supply/categories') ? 'hover:text-orange-700 hover:bg-orange-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Categories</span>
                                    </a>
                                    <a href="/supply/suppliers" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-orange-400 hover:text-orange-700 hover:bg-orange-50 transition-colors {{ request()->is('supply/suppliers') ? 'hover:text-orange-700 hover:bg-orange-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Suppliers</span>
                                    </a>
                                    <a href="/supply/stock-locations" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-orange-400 hover:text-orange-700 hover:bg-orange-50 transition-colors {{ request()->is('supply/stock-locations') ? 'hover:text-orange-700 hover:bg-orange-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Stock Locations</span>
                                    </a>
                                    <a href="/supply/units" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-orange-400 hover:text-orange-700 hover:bg-orange-50 transition-colors {{ request()->is('supply/units') ? 'hover:text-orange-700 hover:bg-orange-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Units</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan
                        </div>

                        <!-- ================= FINANCIAL & CLAIMS ================= -->
                        <div class="px-3 space-y-1">
                            <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-2">Financial & Claims</p>

                            <!-- Billing -->
                            @can('billing.view')
                            <div x-data="{ openBilling: {{ request()->is('billing*') ? 'true' : 'false' }} }">
                                <button @click="openBilling = !openBilling" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-emerald-400 group-hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>Billing</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openBilling}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openBilling" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('billing.view')
                                    <a href="/billing" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('billing') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Invoices</span>
                                    </a>
                                    @endcan
                                    @can('billing.manage')
                                    <a href="/billing/fund-sources" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('billing/fund-sources') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><span>Fund Sources</span>
                                        </a>
                                        @endcan
                                        @can('billing.manage')
                                        <a href="/billing/insurance-providers" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('billing/insurance-providers') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><span>Insurance Providers</span>
                                        </a>
                                        @endcan
                                    </div>
                            </div>
                            @endcan

                            <!-- PhilHealth -->
                            @can('philhealth.view')
                            <div x-data="{ openPhilhealth: {{ request()->is('philhealth/*') ? 'true' : 'false' }} }">
                                <button @click="openPhilhealth = !openPhilhealth" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-blue-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span>PhilHealth</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openPhilhealth}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openPhilhealth" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('philhealth.view')
                                    <a href="/philhealth/claims" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/claims*') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>E-Claims</span>
                                    </a>
                                    <a href="/philhealth/members" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/members') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Members</span>
                                    </a>
                                    <a href="/philhealth/hc-professionals" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/hc-professionals') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>HC Professionals</span>
                                    </a>
                                    @endcan
                                    @can('philhealth.manage')
                                    <a href="/philhealth/icd10-codes" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/icd10-codes') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>ICD-10 Codes</span>
                                    </a>
                                    <a href="/philhealth/rvs-codes" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/rvs-codes') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>RVS Codes</span>
                                    </a>
                                    <a href="/philhealth/case-rates" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/case-rates') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Case Rates</span>
                                    </a>
                                    <a href="/philhealth/batches" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/batches*') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Batches</span>
                                    </a>
                                    <a href="/philhealth/eecl-items" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/eecl-items*') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg><span>EECL Items (Annex D)</span>
                                    </a>
                                    <a href="/philhealth/oecb-packages" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/oecb-packages*') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg><span>OECB Packages &amp; Mapping</span>
                                    </a>
                                    @endcan
                                    @can('reports.view')
                                    <a href="/philhealth/reports" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-blue-400 hover:text-blue-700 hover:bg-blue-50 transition-colors {{ request()->is('philhealth/reports*') ? 'hover:text-blue-700 hover:bg-blue-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg><span>Reports</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- YAKAP -->
                            @can('yakap.view')
                            <div x-data="{ openYakap: {{ request()->is('yakap/*') ? 'true' : 'false' }} }">
                                <button @click="openYakap = !openYakap" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-emerald-400 group-hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                                        <span>YAKAP</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openYakap}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openYakap" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    @can('yakap.view')
                                    <a href="/yakap/dashboard" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('yakap/dashboard') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg><span>Dashboard</span>
                                    </a>
                                    @endcan
                                    @can('yakap.manage')
                                    <a href="/yakap/empanelments" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('yakap/empanelments') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Empanelments</span>
                                    </a>
                                    <a href="/yakap/fpe-records" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('yakap/fpe-records') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>FPE Records</span>
                                    </a>
                                    <a href="/yakap/screenings" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('yakap/screenings') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><span>Screenings</span>
                                    </a>
                                    <a href="/yakap/capitation" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-emerald-400 hover:text-emerald-700 hover:bg-emerald-50 transition-colors {{ request()->is('yakap/capitation') ? 'hover:text-emerald-700 hover:bg-emerald-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Capitation</span>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                            @endcan

                            <!-- Konsulta -->
                            @can('konsulta.view')
                            <div x-data="{ openKonsulta: {{ request()->is('konsulta') ? 'true' : 'false' }} }">
                                <a href="/konsulta" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50 hover:text-teal-700 transition-colors group {{ request()->is('konsulta') ? 'bg-teal-50 text-teal-700 font-semibold' : '' }}">
                                    <svg class="w-5 h-5 mr-3 {{ request()->is('konsulta') ? 'text-teal-600' : 'text-teal-400 group-hover:text-teal-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342"/></svg>
                                    <span>Konsulta</span>
                                </a>
                            </div>
                            @endcan

                            <!-- Transactions -->
                            @can('billing.view')
                            <a href="/transactions" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-light-blue-50 hover:text-light-blue-700 transition-colors group {{ request()->is('transactions') ? 'bg-light-blue-50 text-light-blue-700 font-semibold' : '' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('transactions') ? 'text-light-blue-600' : 'text-light-blue-400 group-hover:text-light-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2zm-7-9a3 3 0 100-6 3 3 0 000 6z"/></svg>
                                Transactions
                            </a>
                            @endcan
                            <!-- Reports -->
                            @can('reports.view')
                            <a href="/reports" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-light-blue-50 hover:text-light-blue-700 transition-colors group {{ request()->is('reports') ? 'bg-light-blue-50 text-light-blue-700 font-semibold' : '' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('reports') ? 'text-light-blue-600' : 'text-light-blue-400 group-hover:text-light-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                Reports
                            </a>
                            @endcan
                        </div>

                        <!-- ================= ADMINISTRATION ================= -->
                        @can('admin.masters')
                        <div class="px-3 space-y-1 pb-4">
                            <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-2">Administration</p>
                            
                            <!-- Masters -->
                            <div x-data="{ openMasters: {{ request()->is('masters/*') ? 'true' : 'false' }} }">
                                <button @click="openMasters = !openMasters" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-slate-50 hover:text-slate-700 transition-colors group focus:outline-none">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                                        <span>Masters</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-90': openMasters}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="openMasters" x-collapse style="display: none;" class="pl-10 pr-2 py-1 space-y-1">
                                    <a href="/masters/company" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/company') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Company</span>
                                    </a>
                                    <a href="/masters/department" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/department') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Department</span>
                                    </a>
                                    <a href="/masters/users" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/users') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Users</span>
                                    </a>
                                    <a href="/masters/hospital" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/hospital') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Hospital</span>
                                    </a>
                                    <a href="/masters/permissions" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/permissions') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg><span>Permissions</span>
                                    </a>
                                    <a href="/masters/country" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/country') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Country</span>
                                    </a>
                                    <a href="/masters/region" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/region') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Region</span>
                                    </a>
                                    <a href="/masters/province" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/province') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Province/State</span>
                                    </a>
                                    <a href="/masters/city" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/city') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>City/Municipality</span>
                                    </a>
                                    <a href="/masters/barangay" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/barangay') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Barangay</span>
                                    </a>
                                    <a href="/masters/religion" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/religion') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Religion</span>
                                    </a>
                                    <a href="/masters/gender" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/gender') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Gender</span>
                                    </a>
                                    <a href="/masters/civil-status" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/civil-status') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Civil Status</span>
                                    </a>
                                    <a href="/masters/nationality" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/nationality') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Nationality</span>
                                    </a>
                                    <a href="/masters/occupation" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/occupation') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Occupation</span>
                                    </a>
                                    <a href="/masters/blood-type" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/blood-type') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg><span>Blood Type</span>
                                    </a>
                                    <a href="/masters/relationship" wire:navigate class="flex items-center px-3 py-2 rounded-md text-sm text-slate-400 hover:text-slate-700 hover:bg-slate-50 transition-colors {{ request()->is('masters/relationship') ? 'hover:text-slate-700 hover:bg-slate-50' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span>Relationship</span>
                                    </a>
                                </div>
                            </div>
                            <a href="/admin/roles" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors group {{ request()->is('admin/roles') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/roles') ? 'text-amber-600' : 'text-amber-400 group-hover:text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Roles</span>
                            </a>
                            <a href="/admin/role-permissions" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-light-blue-50 hover:text-light-blue-700 transition-colors group {{ request()->is('admin/role-permissions') ? 'bg-light-blue-50 text-light-blue-700 font-semibold' : '' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/role-permissions') ? 'text-light-blue-600' : 'text-light-blue-400 group-hover:text-light-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Role Permissions</span>
                            </a>
                            <a href="/admin/audit-log" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition-colors group {{ request()->is('admin/audit-log') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                                <svg class="w-5 h-5 mr-3 {{ request()->is('admin/audit-log') ? 'text-amber-600' : 'text-amber-400 group-hover:text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <span>Audit Trail</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </nav>

                <!-- Right Main Content -->
                <main class="flex-1 overflow-y-auto bg-gray-50 p-6 relative">
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100 min-h-full">
                        {{ $slot }}
                    </div>
                </main>
            </div>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 flex-shrink-0 z-10">
                <div class="px-4 py-3">
                    <p class="text-center text-sm text-gray-500">
                        &copy; {{ date('Y') }} SITHEMR. All rights reserved.
                    </p>
                </div>
            </footer>
        </div>

        <!-- Global Toast Notifications -->
        <div x-data="{ toasts: [] }"
             @toast.window="
                let t = { id: Date.now(), type: $event.detail.type || 'success', message: $event.detail.message };
                toasts.push(t);
                setTimeout(() => { toasts = toasts.filter(toast => toast.id !== t.id) }, 4000);
             "
             class="fixed top-4 right-4 z-[60] flex flex-col gap-2 w-full max-w-sm pointer-events-none">
            <template x-for="toast in toasts" :key="toast.id">
                <div x-show="true"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-10 scale-95"
                     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-x-10 scale-95"
                     class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border"
                     :class="toast.type === 'success' ? 'bg-green-50 border-green-100' : 'bg-red-50 border-red-100'">
                    
                    <!-- Icon -->
                    <template x-if="toast.type === 'success'">
                        <svg class="h-5 w-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="h-5 w-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </template>

                    <!-- Message -->
                    <span class="text-sm font-medium" 
                          :class="toast.type === 'success' ? 'text-green-800' : 'text-red-800'"
                          x-text="toast.message"></span>
                    
                    <!-- Close button -->
                    <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="ml-auto shrink-0 p-1 rounded-md hover:bg-black/5 transition-colors"
                            :class="toast.type === 'success' ? 'text-green-600' : 'text-red-600'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>

    </body>
</html>
