<?php

namespace App\Domains\Notifications\Notifications;

use App\Domains\Payments\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PaymentTransaction $transaction
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'PAYMENT_RECEIVED',
            'title' => 'Payment Verified & Received',
            'message' => "Payment of IDR " . number_format($this->transaction->amount, 0, ',', '.') . " for Invoice #{$this->transaction->invoice?->invoice_number} successfully verified.",
            'transaction_id' => $this->transaction->id,
            'invoice_id' => $this->transaction->invoice_id,
            'amount' => $this->transaction->amount,
            'payment_method' => $this->transaction->payment_method,
            'action_url' => "/admin/finance",
        ];
    }
}
