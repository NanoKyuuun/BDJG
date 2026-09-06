<?php

namespace App\Mail;

use App\Domains\Clients\Models\Client;
use App\Domains\Clients\Models\ClientInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Client $client,
        public ClientInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to BDJG Studio — Activate Your Client Account',
        );
    }

    public function content(): Content
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000'));
        $activationUrl = "{$frontendUrl}/auth/accept-invitation?token={$this->invitation->token}";

        return new Content(
            view: 'emails.client-invitation',
            with: [
                'clientName' => $this->client->display_name,
                'activationUrl' => $activationUrl,
            ],
        );
    }
}
