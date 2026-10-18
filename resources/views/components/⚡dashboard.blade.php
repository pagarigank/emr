<?php

use Livewire\Component;
use App\Models\Patient;
use App\Models\Er\ErAdmission;
use App\Models\Admission;
use App\Models\Opd\OpdConsultation;
use App\Models\Billing\BillingInvoice;
use App\Models\Billing\BillingPayment;
use App\Models\PhilhealthClaim;
use Carbon\Carbon;

new class extends Component
{
    // Hospital Overview
    public int $totalPatients = 0;
    public int $activeEr = 0;
    public int $activeAdmissions = 0;
    public int $todayOpd = 0;

    // Financial Snapshot
    public float $todayBilled = 0;
    public float $todayCollected = 0;
    public float $totalOutstanding = 0;

    // PhilHealth Claims (Existing)
    public int $totalClaims = 0;
    public int $pendingClaims = 0;
    public int $submittedClaims = 0;
    public int $acknowledgedClaims = 0;
    public int $paidClaims = 0;
    public int $deniedClaims = 0;
    public float $totalClaimedMonth = 0;
    public float $totalPaidMonth = 0;
    public float $approvalRate = 0;
    public float $collectionRate = 0;

    public function mount()
    {
        $today = Carbon::today();
        
        // 1. Hospital Overview
        $this->totalPatients = Patient::count();
        $this->activeEr = ErAdmission::where('disposition', 'Pending')->count();
        $this->activeAdmissions = Admission::whereIn('status', ['Admitted'])->count();
        $this->todayOpd = OpdConsultation::whereDate('created_date', $today)->count();

        // 2. Financial Snapshot
        $this->todayBilled = (float) BillingInvoice::whereDate('created_date', $today)->sum('total_amount');
        $this->todayCollected = (float) BillingPayment::whereDate('payment_date', $today)->sum('amount');
        $this->totalOutstanding = (float) BillingInvoice::whereIn('status', ['Unpaid', 'Partial'])->sum('balance');

        // 3. PhilHealth Claims
        $q = PhilhealthClaim::query();

        $this->totalClaims = (clone $q)->count();
        $this->pendingClaims = (clone $q)->where('status', 'For Submission')->count();
        $this->submittedClaims = (clone $q)->whereIn('status', ['Submitted', 'Acknowledged'])->count();
        $this->acknowledgedClaims = (clone $q)->where('status', 'Acknowledged')->count();
        $this->paidClaims = (clone $q)->where('status', 'Paid')->count();
        $this->deniedClaims = (clone $q)->where('status', 'Denied')->count();

        $thisMonth = now()->startOfMonth();
        $monthly = (clone $q)->where('created_date', '>=', $thisMonth);
        $this->totalClaimedMonth = (float) (clone $monthly)->sum('total_claimed_amount');
        $this->totalPaidMonth = (float) (clone $monthly)->where('status', 'Paid')->sum('voucher_amount');

        $resolved = $this->paidClaims + $this->deniedClaims;
        $this->approvalRate = $resolved > 0 ? round($this->paidClaims / $resolved * 100, 1) : 0;
        $this->collectionRate = $this->totalClaimedMonth > 0
            ? round($this->totalPaidMonth / $this->totalClaimedMonth * 100, 1)
            : 0;
    }
};
?>

<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-gray-200/60">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Command Center</h1>
            <p class="mt-1 text-sm text-gray-500">Live overview of hospital operations and financials.</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center bg-white border border-gray-200 shadow-sm rounded-full px-4 py-2">
            <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-2"></div>
            <span class="text-xs font-semibold text-gray-600 tracking-wide uppercase">{{ now()->format('F d, Y h:i A') }}</span>
        </div>
    </div>

    <!-- Section 1: Hospital Overview -->
    <div>
        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Live Patient Census</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            
            <!-- Total Patients -->
            <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-6 shadow-lg shadow-blue-500/30 text-white relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300">
                <div class="absolute -right-6 -top-6 text-white/10 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wider mb-1">Total Registered</p>
                    <p class="text-4xl font-extrabold tracking-tight">{{ number_format($totalPatients) }}</p>
                    <p class="mt-2 text-xs text-blue-200 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        All-time patient database
                    </p>
                </div>
            </div>

            <!-- Active ER -->
            <div class="bg-gradient-to-br from-rose-500 to-red-600 rounded-2xl p-6 shadow-lg shadow-rose-500/30 text-white relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300">
                <div class="absolute -right-6 -top-6 text-white/10 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-rose-100 text-sm font-medium uppercase tracking-wider mb-1">Active ER</p>
                    <p class="text-4xl font-extrabold tracking-tight">{{ number_format($activeEr) }}</p>
                    <p class="mt-2 text-xs text-rose-200 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Pending disposition
                    </p>
                </div>
            </div>

            <!-- Active Admissions -->
            <div class="bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl p-6 shadow-lg shadow-violet-500/30 text-white relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300">
                <div class="absolute -right-6 -top-6 text-white/10 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-violet-100 text-sm font-medium uppercase tracking-wider mb-1">Active Admissions</p>
                    <p class="text-4xl font-extrabold tracking-tight">{{ number_format($activeAdmissions) }}</p>
                    <p class="mt-2 text-xs text-violet-200 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Currently in Wards/ICU
                    </p>
                </div>
            </div>

            <!-- Today's OPD -->
            <div class="bg-gradient-to-br from-cyan-500 to-sky-600 rounded-2xl p-6 shadow-lg shadow-cyan-500/30 text-white relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300">
                <div class="absolute -right-6 -top-6 text-white/10 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-cyan-100 text-sm font-medium uppercase tracking-wider mb-1">Today's OPD</p>
                    <p class="text-4xl font-extrabold tracking-tight">{{ number_format($todayOpd) }}</p>
                    <p class="mt-2 text-xs text-cyan-200 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Consultations created today
                    </p>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Section 2: Financial Snapshot -->
    <div class="mt-8">
        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Financial Snapshot</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <!-- Today's Billed -->
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Billed Today</h3>
                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-gray-900 tracking-tight">₱{{ number_format($todayBilled, 2) }}</p>
                <div class="mt-3 w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-full w-full"></div>
                </div>
            </div>

            <!-- Today's Collected -->
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Collected Today</h3>
                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-gray-900 tracking-tight">₱{{ number_format($todayCollected, 2) }}</p>
                <div class="mt-3 w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-full w-full"></div>
                </div>
            </div>

            <!-- Total Outstanding -->
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Outstanding</h3>
                    <div class="bg-amber-100 text-amber-600 p-2 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-3xl font-extrabold text-amber-600 tracking-tight">₱{{ number_format($totalOutstanding, 2) }}</p>
                <div class="mt-3 w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-amber-400 h-full w-full"></div>
                </div>
            </div>

        </div>
    </div>

    <!-- Section 3: PhilHealth E-Claims -->
    <div class="mt-8">
        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">PhilHealth E-Claims Tracker</h2>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">E-Claims Monthly Performance</h3>
                        <p class="text-sm text-gray-500">Tracking metrics for {{ now()->format('F Y') }}</p>
                    </div>
                </div>
                <div class="hidden sm:flex space-x-6 text-right">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Approval Rate</p>
                        <p class="text-xl font-bold text-emerald-600">{{ $approvalRate }}%</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Collection Rate</p>
                        <p class="text-xl font-bold text-blue-600">{{ $collectionRate }}%</p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-6 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                <div class="p-5 text-center hover:bg-gray-50 transition-colors">
                    <p class="text-sm font-medium text-gray-500 mb-1">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalClaims) }}</p>
                </div>
                <div class="p-5 text-center hover:bg-amber-50/50 transition-colors">
                    <p class="text-sm font-medium text-amber-600 mb-1">For Submission</p>
                    <p class="text-2xl font-bold text-amber-600">{{ number_format($pendingClaims) }}</p>
                </div>
                <div class="p-5 text-center hover:bg-blue-50/50 transition-colors">
                    <p class="text-sm font-medium text-blue-600 mb-1">Submitted/Ack'd</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($submittedClaims) }}</p>
                </div>
                <div class="p-5 text-center hover:bg-emerald-50/50 transition-colors">
                    <p class="text-sm font-medium text-emerald-600 mb-1">Paid</p>
                    <p class="text-2xl font-bold text-emerald-600">{{ number_format($paidClaims) }}</p>
                </div>
                <div class="p-5 text-center hover:bg-red-50/50 transition-colors">
                    <p class="text-sm font-medium text-red-600 mb-1">Denied</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($deniedClaims) }}</p>
                </div>
                <div class="p-5 text-center hover:bg-indigo-50/50 transition-colors bg-gray-50/50">
                    <p class="text-sm font-medium text-indigo-600 mb-1">This Month</p>
                    <p class="text-2xl font-bold text-indigo-700">₱{{ number_format($totalClaimedMonth, 0) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
