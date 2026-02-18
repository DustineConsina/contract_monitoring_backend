<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractExpiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;
    public $daysUntilExpiry;

    /**
     * Create a new message instance.
     */
    public function __construct(Contract $contract, $daysUntilExpiry)
    {
        $this->contract = $contract;
        $this->daysUntilExpiry = $daysUntilExpiry;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contract Expiring Soon - ' . $this->contract->contract_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-expiry',
            with: [
                'contract' => $this->contract,
                'daysUntilExpiry' => $this->daysUntilExpiry,
                'tenant' => $this->contract->tenant,
                'rentalSpace' => $this->contract->rentalSpace,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
