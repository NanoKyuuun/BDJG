<?php

namespace App\Domains\Notifications\Notifications;

use App\Domains\Commercial\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class QuotationSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Quotation $quotation
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'QUOTATION_SENT',
            'title' => 'New Quotation Received',
            'message' => "Quotation #{$this->quotation->quotation_number} has been sent for your review (Total: IDR " . number_format($this->quotation->total_amount, 0, ',', '.') . ")",
            'quotation_id' => $this->quotation->id,
            'quotation_number' => $this->quotation->quotation_number,
            'total_amount' => $this->quotation->total_amount,
            'action_url' => "/client/quotations/{$this->quotation->id}",
        ];
    }
}
