<?php

namespace App\Console\Commands;

use App\Services\Philhealth\ClaimStatusPollingService;
use Illuminate\Console\Command;

class PollPhilhealthClaims extends Command
{
    protected $signature = 'philhealth:poll-claims
                            {--batch= : Poll a specific batch by ID}
                            {--claim= : Poll a specific claim by ID}';

    protected $description = 'Poll PhilHealth for status updates on submitted claims';

    public function handle(ClaimStatusPollingService $pollingService): int
    {
        $batchId = $this->option('batch');
        $claimId = $this->option('claim');

        if ($claimId) {
            $this->info("Polling claim ID {$claimId}...");
            $result = $pollingService->pollClaim((int) $claimId);
            $this->displayResult($result);
            return Command::SUCCESS;
        }

        if ($batchId) {
            $this->info("Polling batch ID {$batchId}...");
            $result = $pollingService->pollBatch((int) $batchId);
            $this->info("Batch poll complete: {$result['total']} claims, {$result['changed']} changed.");
            return Command::SUCCESS;
        }

        $this->info('Polling all submitted claims...');
        $result = $pollingService->pollAllSubmitted();
        $this->info("Poll complete: {$result['total']} claims polled, {$result['changed']} status changes detected.");

        return Command::SUCCESS;
    }

    protected function displayResult(array $result): void
    {
        if ($result['success']) {
            $msg = $result['message'] ?? 'Poll complete.';
            if ($result['changed'] ?? false) {
                $this->info("✓ {$msg}");
            } else {
                $this->warn("{$msg}");
            }
        } else {
            $this->error('✗ ' . ($result['message'] ?? 'Poll failed.'));
        }
    }
}
