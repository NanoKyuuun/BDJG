<?php

namespace App\Domains\Clients\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'email' => $this->email,
            'token' => $this->token,
            'expires_at' => $this->expires_at?->toISOString(),
            'accepted_at' => $this->accepted_at?->toISOString(),
            'is_valid' => $this->isValid(),
            'client_name' => $this->client?->display_name,
        ];
    }
}
