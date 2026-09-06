<?php

namespace App\Mail;

use App\Domains\Commercial\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationSentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quotation $quotation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Quotation #{$this->quotation->quotation_number} — BDJG Creative Studio",
        );
    }

    public function content(): Content
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $viewUrl = "{$frontendUrl}/client/quotations/{$this->quotation->id}";

        return new Content(
            view: 'emails.quotation-sent',
            with: [
                'clientName' => $this->quotation->client?->display_name ?? 'Valued Client',
                'quotation' => $this->quotation,
                'viewUrl' => $viewUrl,
            ],
        );
    }
}
