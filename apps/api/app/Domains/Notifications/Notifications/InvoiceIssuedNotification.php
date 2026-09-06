<?php

namespace App\Domains\Notifications\Notifications;

use App\Domains\Billing\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvoiceIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'INVOICE_ISSUED',
            'title' => 'Invoice Issued',
            'message' => "Invoice #{$this->invoice->invoice_number} is ready for payment (Amount: IDR " . number_format($this->invoice->amount, 0, ',', '.') . ")",
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->amount,
            'due_date' => $this->invoice->due_date?->toDateString(),
            'action_url' => "/client/invoices/{$this->invoice->id}",
        ];
    }
}
