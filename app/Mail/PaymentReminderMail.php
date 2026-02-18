<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $reminderType;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, $reminderType = 'upcoming')
    {
        $this->payment = $payment;
        $this->reminderType = $reminderType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->reminderType === 'overdue' 
            ? 'Payment Overdue - ' . $this->payment->payment_number
            : 'Payment Due Soon - ' . $this->payment->payment_number;

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
            with: [
                'payment' => $this->payment,
                'reminderType' => $this->reminderType,
                'tenant' => $this->payment->tenant,
                'contract' => $this->payment->contract,
                'rentalSpace' => $this->payment->contract->rentalSpace,
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
