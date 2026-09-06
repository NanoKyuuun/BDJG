<?php

namespace App\Domains\Finance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'worker_profile_id' => $this->worker_profile_id,
            'worker_name' => $this->worker?->user?->name,
            'project_id' => $this->project_id,
            'project_name' => $this->project?->name,
            'title' => $this->title,
            'amount' => $this->amount,
            'category' => $this->category?->value ?? $this->category,
            'status' => $this->status?->value ?? $this->status,
            'receipt_storage_key' => $this->receipt_storage_key,
            'approved_at' => $this->approved_at?->toISOString(),
            'approved_by' => $this->approvedBy ? [
                'id' => $this->approvedBy->id,
                'name' => $this->approvedBy->name,
            ] : null,
            'paid_at' => $this->paid_at?->toISOString(),
            'notes' => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
