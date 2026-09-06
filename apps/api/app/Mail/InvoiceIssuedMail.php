<?php

namespace App\Mail;

use App\Domains\Billing\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceIssuedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invoice #{$this->invoice->invoice_number} Ready for Payment — BDJG Creative Studio",
        );
    }

    public function content(): Content
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $paymentUrl = "{$frontendUrl}/client/invoices/{$this->invoice->id}";

        return new Content(
            view: 'emails.invoice-issued',
            with: [
                'clientName' => $this->invoice->client?->display_name ?? 'Valued Client',
                'invoice' => $this->invoice,
                'paymentUrl' => $paymentUrl,
            ],
        );
    }
}
