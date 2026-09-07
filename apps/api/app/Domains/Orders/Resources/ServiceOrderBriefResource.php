<?php

namespace App\Domains\Orders\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrderBriefResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'event_name' => $this->event_name,
            'event_category' => $this->event_category?->value,
            'couple_session_details' => $this->couple_session_details,
            'wedding_details' => $this->wedding_details,
            'custom_requirements' => $this->custom_requirements,
            'selected_add_ons' => $this->selected_add_ons,
            'onsite_pic_name' => $this->onsite_pic_name,
            'onsite_pic_phone' => $this->onsite_pic_phone,
            'portfolio_consent' => $this->portfolio_consent,
            'additional_notes' => $this->additional_notes,
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
