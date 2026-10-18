<?php

namespace App\Livewire\Yakap;

use App\Models\Yakap\YakapEmpanelment;
use App\Models\Yakap\YakapFpeRecord;
use App\Models\Yakap\YakapScreening;
use App\Models\Yakap\YakapCapitationPayment;
use Livewire\Component;

class DashboardCrud extends Component
{
    public int $totalEmpaneled = 0;
    public int $fpeCompleted = 0;
    public int $fpePending = 0;
    public int $screeningsCompleted = 0;
    public int $screeningsPending = 0;
    public int $screeningsOverdue = 0;
    public float $totalCapitationPaid = 0;
    public float $totalCapitationPending = 0;
    public int $highRisk = 0;
    public int $moderateRisk = 0;
    public int $lowRisk = 0;

    private function currentTenantId(): int
    {
        return request()->attributes->get('tenant_id') ?? 1;
    }

    public function mount(): void
    {
        $tid = $this->currentTenantId();

        $this->totalEmpaneled = YakapEmpanelment::where('tenant_id', $tid)->where('history', 1)->count();
        $this->fpeCompleted = YakapFpeRecord::where('tenant_id', $tid)->where('status', 'Completed')->where('history', 1)->count();
        $this->fpePending = YakapFpeRecord::where('tenant_id', $tid)->where('status', '!=', 'Completed')->where('history', 1)->count();

        $this->screeningsCompleted = YakapScreening::where('tenant_id', $tid)->where('status', 'Completed')->where('history', 1)->count();
        $this->screeningsPending = YakapScreening::where('tenant_id', $tid)->where('status', 'Pending')->where('history', 1)->count();
        $this->screeningsOverdue = YakapScreening::where('tenant_id', $tid)->where('status', 'Overdue')->where('history', 1)->count();

        $this->totalCapitationPaid = (float) YakapCapitationPayment::where('tenant_id', $tid)->where('status', 'Paid')->where('history', 1)->sum('amount');
        $this->totalCapitationPending = (float) YakapCapitationPayment::where('tenant_id', $tid)->where('status', 'Pending')->where('history', 1)->sum('amount');

        $this->highRisk = YakapEmpanelment::where('tenant_id', $tid)->where('risk_classification', 'High')->where('history', 1)->count();
        $this->moderateRisk = YakapEmpanelment::where('tenant_id', $tid)->where('risk_classification', 'Moderate')->where('history', 1)->count();
        $this->lowRisk = YakapEmpanelment::where('tenant_id', $tid)->where('risk_classification', 'Low')->where('history', 1)->count();
    }

    public function render()
    {
        return view('livewire.yakap.dashboard-crud');
    }
}
