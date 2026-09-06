<?php

namespace App\Mail;

use App\Domains\Delivery\Models\DeliveryPackage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FinalDeliveryReadyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public DeliveryPackage $package
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Final Master Package is Ready for Download — BDJG Creative Studio",
        );
    }

    public function content(): Content
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $downloadUrl = "{$frontendUrl}/client/projects/{$this->package->project_id}/deliveries";

        return new Content(
            view: 'emails.final-delivery-ready',
            with: [
                'clientName' => $this->package->project?->client?->display_name ?? 'Valued Client',
                'package' => $this->package,
                'downloadUrl' => $downloadUrl,
            ],
        );
    }
}
