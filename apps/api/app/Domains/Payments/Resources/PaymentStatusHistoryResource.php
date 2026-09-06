<?php

namespace App\Domains\Payments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_transaction_id' => $this->payment_transaction_id,
            'from_status' => $this->from_status?->value ?? $this->from_status,
            'to_status' => $this->to_status?->value ?? $this->to_status,
            'source' => $this->source,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
