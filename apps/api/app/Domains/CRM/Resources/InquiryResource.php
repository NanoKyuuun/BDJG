<?php

namespace App\Domains\CRM\Resources;

use App\Domains\Catalog\Resources\AddOnResource;
use App\Domains\Catalog\Resources\PackageResource;
use App\Domains\Catalog\Resources\ServiceResource;
use App\Domains\Clients\Resources\AdminClientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InquiryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'client_name' => $this->client_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_or_institution' => $this->company_or_institution,
            'service_id' => $this->service_id,
            'package_id' => $this->package_id,
            'client_id' => $this->client_id,
            'preferred_date' => $this->preferred_date?->format('Y-m-d'),
            'alternative_date' => $this->alternative_date?->format('Y-m-d'),
            'location' => $this->location,
            'project_brief' => $this->project_brief,
            'reference_links' => $this->reference_links,
            'estimated_budget' => $this->estimated_budget,
            'source' => $this->source?->value ?? $this->source,
            'status' => $this->status?->value ?? $this->status,
            'assigned_admin_id' => $this->assigned_admin_id,
            'lost_reason' => $this->lost_reason,
            'notes_internal' => $this->notes_internal,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'package' => new PackageResource($this->whenLoaded('package')),
            'client' => new AdminClientResource($this->whenLoaded('client')),
            'assigned_admin' => $this->whenLoaded('assignedAdmin', fn () => [
                'id' => $this->assignedAdmin->id,
                'public_id' => $this->assignedAdmin->public_id,
                'name' => $this->assignedAdmin->name,
                'email' => $this->assignedAdmin->email,
            ]),
            'add_ons' => AddOnResource::collection($this->whenLoaded('addOns')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
