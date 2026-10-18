<?php

namespace App\Livewire\Philhealth;

use App\Models\PhilhealthClaim;
use App\Models\ClaimRejection;
use App\Models\ClaimStatusPoll;
use App\Services\Philhealth\EclaimsService;
use App\Services\Philhealth\EcsService;
use App\Services\Philhealth\CloudStorageService;
use App\Services\Philhealth\AcknowledgmentService;
use App\Services\Philhealth\ClaimStatusPollingService;
use App\Services\Philhealth\Cf5ShadowBillingService;
use App\Services\Philhealth\QrCodeService;
use Livewire\Component;

class ClaimSubmission extends Component
{
    public ?int $claimId = null;
    public bool $showModal = false;
    public string $activeTab = 'submission';

    public ?array $eligibilityResult = null;
    public string $ecsResultMessage = '';
    public string $cloudResultMessage = '';
    public string $acknowledgmentResultMessage = '';

    public string $acknowledgment_code = '';
    public string $voucher_number = '';
    public string $voucher_amount = '';
    public string $deny_reason = '';

    public string $rejection_code = '';
    public string $rejection_reason = '';
    public string $rejection_date = '';

    public string $pollResultMessage = '';
    public ?array $pollResult = null;
    public ?array $pollHistory = null;

    public string $qrDataUri = '';
    public ?array $cf5Result = null;

    public bool $showPreviewModal = false;
    public string $previewTitle = '';
    public string $previewHtml = '';
    public string $previewDownloadUrl = '';

    private function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }

    public function mount(?int $claimId = null): void
    {
    }

    #[\Livewire\Attributes\On('open-claim-submission')]
    public function openSubmission(int $claimId): void
    {
        $this->claimId = $claimId;
        $this->activeTab = 'submission';
        $this->resetMessages();
        $this->showModal = true;
    }

    private function resetMessages(): void
    {
        $this->eligibilityResult = null;
        $this->ecsResultMessage = '';
        $this->cloudResultMessage = '';
        $this->acknowledgmentResultMessage = '';
        $this->pollResultMessage = '';
        $this->pollResult = null;
        $this->pollHistory = null;
        $this->cf5Result = null;
        $this->qrDataUri = '';
        $this->acknowledgment_code = '';
        $this->voucher_number = '';
        $this->voucher_amount = '';
        $this->deny_reason = '';
        $this->rejection_code = '';
        $this->rejection_reason = '';
        $this->rejection_date = '';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function checkEligibility(): void
    {
        $claim = PhilhealthClaim::with(['philhealthMember', 'patient', 'caseRate'])->find($this->claimId);
        if (!$claim) { $this->eligibilityResult = ['eligible' => false, 'message' => 'Claim not found.', 'tracking_no' => null, 'fallback' => false]; return; }
        if (!$claim->philhealth_member_id) { $this->eligibilityResult = ['eligible' => false, 'message' => 'No PhilHealth member linked.', 'tracking_no' => null, 'fallback' => false]; return; }
        try {
            $this->eligibilityResult = app(EclaimsService::class)->checkEligibility($claim);
        } catch (\Throwable $e) {
            $this->eligibilityResult = ['eligible' => false, 'message' => 'Error: ' . $e->getMessage(), 'tracking_no' => null, 'fallback' => false];
        }
    }

    public function uploadToEcs(): void
    {
        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) return;
        try {
            $result = app(EcsService::class)->uploadClaim($claim);
            if ($result['success']) {
                $claim->update(['ecs_tracking_no' => $result['tracking_no'], 'ecs_uploaded_at' => now(), 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
                $this->ecsResultMessage = 'ECS upload successful. Tracking: ' . $result['tracking_no'];
                $this->dispatch('toast', type: 'success', message: 'Claim uploaded to ECS.');
            } else {
                $this->ecsResultMessage = 'ECS upload failed: ' . $result['message'];
            }
        } catch (\Throwable $e) {
            $this->ecsResultMessage = 'ECS error: ' . $e->getMessage();
        }
    }

    public function uploadToCloud(): void
    {
        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) return;
        try {
            $cloud = app(CloudStorageService::class);
            $xml = app(\App\Services\Philhealth\XmlBuilder::class)->buildClaimXml($claim);
            $folder = $claim->claim_code;
            $cloud->createFolder($folder);
            $result = $cloud->uploadFile($folder, $claim->claim_code . '.xml', $xml);
            if ($result['success']) {
                $claim->update(['cloud_folder' => $folder, 'cloud_file' => $claim->claim_code . '.xml', 'cloud_uploaded_at' => now(), 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
                $this->cloudResultMessage = "Cloud upload successful. Folder: {$folder}";
                $this->dispatch('toast', type: 'success', message: 'Claim uploaded to Cloud.');
            } else {
                $this->cloudResultMessage = 'Cloud upload failed: ' . $result['message'];
            }
        } catch (\Throwable $e) {
            $this->cloudResultMessage = 'Cloud error: ' . $e->getMessage();
        }
    }

    public function acknowledgeClaim(): void
    {
        $this->validate(['acknowledgment_code' => ['required', 'string', 'max:50']]);
        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) return;
        try {
            app(AcknowledgmentService::class)->acknowledgeClaim($claim, $this->acknowledgment_code);
            $this->dispatch('toast', type: 'success', message: 'Claim acknowledged.');
            $this->dispatch('claim-status-changed');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function recordVoucher(): void
    {
        $this->validate([
            'voucher_number' => ['required', 'string', 'max:50'],
            'voucher_amount' => ['required', 'numeric', 'min:0'],
        ]);
        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) return;
        try {
            app(AcknowledgmentService::class)->recordVoucher($claim, $this->voucher_number, (float) $this->voucher_amount);
            $this->dispatch('toast', type: 'success', message: 'Voucher recorded. Claim marked as Paid.');
            $this->dispatch('claim-status-changed');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function markDenied(): void
    {
        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) return;
        try {
            app(AcknowledgmentService::class)->markDenied($claim, $this->deny_reason);
            $this->dispatch('toast', type: 'success', message: 'Claim marked as Denied.');
            $this->dispatch('claim-status-changed');
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    public function recordRejection(): void
    {
        $this->validate([
            'rejection_reason' => ['required', 'string'],
            'rejection_date' => ['required', 'date'],
        ]);
        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) return;

        ClaimRejection::create([
            'tenant_id' => $claim->tenant_id,
            'philhealth_claim_id' => $claim->id,
            'rejection_code' => $this->rejection_code ?: null,
            'rejection_reason' => $this->rejection_reason,
            'rejection_date' => $this->rejection_date,
            'resubmission_status' => 'Pending',
            'history' => 1,
            'created_by' => $this->currentUserId(),
            'created_date' => now(),
        ]);

        $claim->update([
            'status' => 'Denied',
            'remarks' => $claim->remarks ? $claim->remarks . "\nRejected: " . $this->rejection_reason : 'Rejected: ' . $this->rejection_reason,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Rejection recorded. Claim marked Denied.');
        $this->dispatch('claim-status-changed');
    }

    public function resubmitClaim(): void
    {
        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) return;

        $latestRejection = ClaimRejection::where('philhealth_claim_id', $this->claimId)
            ->where('resubmission_status', 'Pending')->latest('id')->first();

        if ($latestRejection) {
            $latestRejection->update(['resubmission_status' => 'Resubmitted', 'resubmitted_at' => now(), 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);
        }

        $claim->update(['status' => 'For Submission', 'acknowledgment_code' => null, 'acknowledgment_date' => null, 'updated_by' => $this->currentUserId(), 'updated_date' => now()]);

        $this->dispatch('toast', type: 'success', message: 'Claim resubmitted.');
        $this->dispatch('claim-status-changed');
    }

    public function runStatusPoll(): void
    {
        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) return;
        try {
            $result = app(ClaimStatusPollingService::class)->pollClaim($claim->id);
            $this->pollResult = $result;
            $this->pollResultMessage = $result['message'] ?? 'Poll complete.';
            $this->dispatch('toast', type: $result['success'] ? 'success' : 'info', message: $this->pollResultMessage);
        } catch (\Throwable $e) {
            $this->pollResultMessage = 'Poll error: ' . $e->getMessage();
        }
    }

    public function loadPollHistory(): void
    {
        $this->pollHistory = ClaimStatusPoll::where('philhealth_claim_id', $this->claimId)
            ->orderBy('polled_at', 'desc')->get()->toArray();
    }

    public function calculateCf5(): void
    {
        $claim = PhilhealthClaim::with('caseRate')->find($this->claimId);
        if (!$claim) return;
        try {
            $this->cf5Result = app(Cf5ShadowBillingService::class)->calculate($claim)->toArray();
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'CF5 error: ' . $e->getMessage());
        }
    }

    public function generateQr(): void
    {
        $claim = PhilhealthClaim::find($this->claimId);
        if (!$claim) return;
        $this->qrDataUri = app(QrCodeService::class)->generateDataUri($claim);
    }

    public function downloadClaimXml()
    {
        $claim = PhilhealthClaim::findOrFail($this->claimId);
        $xml = app(\App\Services\Philhealth\XmlBuilder::class)->buildClaimXml($claim);
        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, $claim->claim_code . '.xml', ['Content-Type' => 'application/xml']);
    }

    public function previewCf1(): void { $this->loadPreview('CF1 — Member Registration', 'cf1pdf', 'philhealth.claims.cf1pdf'); }
    public function previewCf2(): void { $this->loadPreview('CF2 — HCI Report', 'cf2pdf', 'philhealth.claims.cf2pdf'); }
    public function previewCf3(): void { $this->loadPreview('CF3 — Clinical Record', 'cf3pdf', 'philhealth.claims.cf3pdf'); }
    public function previewCf4(): void { $this->loadPreview('CF4 — Clinical Abstract', 'cf4pdf', 'philhealth.claims.cf4pdf'); }
    public function previewEecl(): void { $this->loadPreview('EECL — Annex D Summary', 'eeclpdf', 'philhealth.claims.eeclpdf'); }

    private function loadPreview(string $title, string $view, string $route): void
    {
        $claim = PhilhealthClaim::with(['patient', 'philhealthMember', 'attendingPhysician', 'referringPhysician', 'icd10Code', 'secondaryIcd10Code', 'tertiaryIcd10Code', 'caseRate', 'deliveryType', 'items', 'claimBatch', 'erAdmission.triageRecord.acuityLevel', 'erAdmission.oecbEntries'])->find($this->claimId);
        if ($claim) {
            $this->previewTitle = $title;
            $this->previewHtml = view("pages.philhealth.{$view}", compact('claim'))->render();
            $this->previewDownloadUrl = route($route, $this->claimId);
            $this->showPreviewModal = true;
        }
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewHtml = '';
        $this->previewTitle = '';
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $claim = $this->claimId ? PhilhealthClaim::find($this->claimId) : null;
        return view('livewire.philhealth.claim-submission', compact('claim'));
    }
}
