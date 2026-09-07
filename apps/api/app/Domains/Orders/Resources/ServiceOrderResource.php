<?php

namespace App\Domains\Orders\Resources;

use App\Domains\Billing\Resources\ClientInvoiceResource;
use App\Domains\Commercial\Resources\ClientQuotationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'order_number' => $this->order_number,
            'source' => $this->source?->value,
            'review_type' => $this->review_type?->value,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'event_date' => $this->event_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'venue_count' => $this->venue_count,
            'location_name' => $this->location_name,
            'location_address' => $this->location_address,
            'is_outside_base_area' => $this->is_outside_base_area,
            'slot_hold_until' => $this->slot_hold_until?->toISOString(),
            'package_snapshot' => $this->package_snapshot,
            'service' => $this->service ? [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'slug' => $this->service->slug,
            ] : null,
            'package' => $this->package ? [
                'id' => $this->package->id,
                'name' => $this->package->name,
                'price' => $this->package->price ?? $this->package->base_price,
                'base_price' => $this->package->base_price,
                'currency' => $this->package->currency,
            ] : null,
            'client' => $this->client ? [
                'id' => $this->client->id,
                'name' => $this->client->display_name ?? $this->client->name,
                'email' => $this->client->email,
                'phone' => $this->client->phone,
            ] : null,
            'brief' => new ServiceOrderBriefResource($this->whenLoaded('brief')),
            'attachments' => ServiceOrderAttachmentResource::collection($this->whenLoaded('attachments')),
            'messages' => ServiceOrderMessageResource::collection($this->whenLoaded('messages')),
            'quotation' => new ClientQuotationResource($this->whenLoaded('quotation')),
            'invoice' => new ClientInvoiceResource($this->whenLoaded('invoice')),
            'project' => $this->project ? [
                'id' => $this->project->id,
                'project_number' => $this->project->project_number,
                'name' => $this->project->name,
                'status' => $this->project->status?->value,
            ] : null,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
