<?php

namespace App\Mail;

use App\Domains\Payments\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public PaymentTransaction $transaction
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Payment Verified — Receipt #{$this->transaction->transaction_reference} — BDJG Studio",
        );
    }

    public function content(): Content
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $portalUrl = "{$frontendUrl}/client/dashboard";

        return new Content(
            view: 'emails.payment-receipt',
            with: [
                'clientName' => $this->transaction->client?->display_name ?? 'Valued Client',
                'transaction' => $this->transaction,
                'portalUrl' => $portalUrl,
            ],
        );
    }
}
