<?php

namespace App\Services\Philhealth;

use App\Mail\ClaimStatusNotification;
use App\Models\ClaimBatch;
use App\Models\PhilhealthClaim;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AcknowledgmentService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('philhealth');
    }

    public function acknowledgeClaim(PhilhealthClaim $claim, string $acknowledgmentCode): PhilhealthClaim
    {
        $claim->update([
            'status' => 'Acknowledged',
            'acknowledgment_code' => $acknowledgmentCode,
            'acknowledgment_date' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->sendNotification($claim->fresh(), 'acknowledged', [
            'acknowledgment_code' => $acknowledgmentCode,
        ]);

        return $claim->fresh();
    }

    public function acknowledgeBatch(ClaimBatch $batch, string $acknowledgmentCode): ClaimBatch
    {
        $batch->update([
            'status' => 'Acknowledged',
            'acknowledgment_code' => $acknowledgmentCode,
            'acknowledgement_date' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        PhilhealthClaim::where('claim_batch_id', $batch->id)->update([
            'status' => 'Acknowledged',
            'acknowledgment_code' => $acknowledgmentCode,
            'acknowledgment_date' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        return $batch->fresh();
    }

    public function recordVoucher(PhilhealthClaim $claim, string $voucherNumber, float $voucherAmount): PhilhealthClaim
    {
        $claim->update([
            'status' => 'Paid',
            'voucher_number' => $voucherNumber,
            'voucher_amount' => $voucherAmount,
            'voucher_date' => now(),
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->sendNotification($claim->fresh(), 'paid', [
            'voucher_number' => $voucherNumber,
            'voucher_amount' => $voucherAmount,
        ]);

        return $claim->fresh();
    }

    public function markDenied(PhilhealthClaim $claim, string $reason = ''): PhilhealthClaim
    {
        $claim->update([
            'status' => 'Denied',
            'remarks' => $reason ? ($claim->remarks ? $claim->remarks . "\nDenied: " . $reason : 'Denied: ' . $reason) : $claim->remarks,
            'updated_by' => $this->currentUserId(),
            'updated_date' => now(),
        ]);

        $this->sendNotification($claim->fresh(), 'denied', [
            'denial_reason' => $reason ?: 'No reason provided.',
        ]);

        return $claim->fresh();
    }

    protected function sendNotification(PhilhealthClaim $claim, string $changeType, array $details = []): void
    {
        try {
            $email = $this->config['hospital']['email'] ?? config('mail.from.address');
            if ($email) {
                Mail::to($email)->send(new ClaimStatusNotification($claim, $changeType, $details));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send claim status notification: ' . $e->getMessage());
        }
    }

    protected function currentUserId(): int
    {
        return request()->attributes->get('jwt_user')?->id ?? 1;
    }
}
