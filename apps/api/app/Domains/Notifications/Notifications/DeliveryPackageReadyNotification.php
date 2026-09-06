<?php

namespace App\Domains\Notifications\Notifications;

use App\Domains\Delivery\Models\DeliveryPackage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DeliveryPackageReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DeliveryPackage $package
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'DELIVERY_PACKAGE_READY',
            'title' => 'Final Deliverables Package Ready',
            'message' => "Your final deliverables package '{$this->package->title}' is ready for download ({$this->package->file_count} files, " . round($this->package->total_size_bytes / 1048576, 2) . "MB).",
            'delivery_package_id' => $this->package->id,
            'package_title' => $this->package->title,
            'file_count' => $this->package->file_count,
            'action_url' => "/client/projects/{$this->package->project_id}/deliveries",
        ];
    }
}
