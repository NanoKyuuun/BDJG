<?php

namespace App\Domains\Payments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'invoice_id' => $this->invoice_id,
            'provider' => $this->provider?->value ?? $this->provider,
            'merchant_order_id' => $this->merchant_order_id,
            'provider_reference' => $this->provider_reference,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'status' => $this->status?->value ?? $this->status,
            'payment_url' => $this->payment_url,
            'va_number' => $this->va_number,
            'qr_string' => $this->qr_string,
            'expires_at' => $this->expires_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
