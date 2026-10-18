<?php

namespace App\Mail;

use App\Models\PhilhealthClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public PhilhealthClaim $claim;
    public string $changeType;
    public array $details;

    public function __construct(PhilhealthClaim $claim, string $changeType, array $details = [])
    {
        $this->claim = $claim;
        $this->changeType = $changeType;
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->changeType) {
            'acknowledged' => 'Claim Acknowledged — ' . $this->claim->claim_code,
            'paid' => 'Payment Received — ' . $this->claim->claim_code,
            'denied' => 'Claim Denied — ' . $this->claim->claim_code,
            'resubmitted' => 'Claim Resubmitted — ' . $this->claim->claim_code,
            default => 'Claim Status Update — ' . $this->claim->claim_code,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.claim-status-notification',
        );
    }
}
