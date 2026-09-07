<?php

namespace App\Domains\Orders\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Domains\Clients\Models\Client;
use App\Domains\Orders\Enums\BriefCategory;
use App\Domains\Orders\Enums\ServiceOrderReviewType;
use App\Domains\Orders\Enums\ServiceOrderSource;
use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Orders\Models\ServiceOrder;
use App\Domains\Orders\Models\ServiceOrderBrief;
use App\Domains\Orders\Models\ServiceOrderStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateServiceOrderAction
{
    public function execute(
        Client $client,
        User $actor,
        ?int $packageId = null,
        ?int $serviceId = null,
        ServiceOrderSource $source = ServiceOrderSource::Catalog,
        ?string $eventName = null,
        BriefCategory $briefCategory = BriefCategory::Wedding
    ): ServiceOrder {
        return DB::transaction(function () use (
            $client,
            $actor,
            $packageId,
            $serviceId,
            $source,
            $eventName,
            $briefCategory
        ) {
            $package = null;
            $packageSnapshot = null;

            if ($packageId) {
                $package = Package::with('service')->findOrFail($packageId);
                $serviceId = $serviceId ?: $package->service_id;

                $packageSnapshot = [
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'service_id' => $package->service_id,
                    'service_name' => $package->service?->name,
                    'base_price' => $package->base_price,
                    'currency' => $package->currency,
                    'default_dp_type' => $package->default_dp_type?->value,
                    'default_dp_value' => $package->default_dp_value,
                    'description' => $package->description_internal,
                    'catalog_captured_at' => now()->toISOString(),
                ];
            } elseif ($serviceId) {
                $service = Service::findOrFail($serviceId);
                $packageSnapshot = [
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'is_custom' => true,
                    'catalog_captured_at' => now()->toISOString(),
                ];
            }

            $order = ServiceOrder::create([
                'client_id' => $client->id,
                'service_id' => $serviceId,
                'package_id' => $packageId,
                'source' => $source,
                'review_type' => ServiceOrderReviewType::AutoCheckout,
                'status' => ServiceOrderStatus::Draft,
                'package_snapshot' => $packageSnapshot,
                'venue_count' => 1,
                'is_outside_base_area' => false,
                'created_by' => $actor->id,
            ]);

            ServiceOrderBrief::create([
                'service_order_id' => $order->id,
                'event_name' => $eventName ?: ($package?->name ? "{$package->name} - {$client->display_name}" : "Brief - {$client->display_name}"),
                'event_category' => $briefCategory,
                'portfolio_consent' => true,
            ]);

            ServiceOrderStatusLog::create([
                'service_order_id' => $order->id,
                'from_status' => null,
                'to_status' => ServiceOrderStatus::Draft,
                'actor_user_id' => $actor->id,
                'reason' => 'Draft order initialized',
            ]);

            AuditLogger::log(
                action: 'SERVICE_ORDER_INITIALIZED',
                description: "Service Order #{$order->order_number} initialized as DRAFT by {$actor->name}",
                auditable: $order,
                newValues: ['order_number' => $order->order_number, 'status' => $order->status->value]
            );

            return $order->load(['brief', 'service', 'package', 'client']);
        });
    }
}
