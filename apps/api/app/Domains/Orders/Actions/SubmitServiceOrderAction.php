<?php

namespace App\Domains\Orders\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Orders\Enums\ServiceOrderReviewType;
use App\Domains\Orders\Enums\ServiceOrderSource;
use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Orders\Models\ServiceOrder;
use App\Domains\Orders\Models\ServiceOrderStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubmitServiceOrderAction
{
    public function __construct(
        protected CheckoutStandardOrderAction $checkoutStandard
    ) {}

    public function execute(ServiceOrder $order, User $actor): ServiceOrder
    {
        return DB::transaction(function () use ($order, $actor) {
            $order = ServiceOrder::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($order->status, [ServiceOrderStatus::Draft, ServiceOrderStatus::NeedsInformation])) {
                throw new InvalidArgumentException("Order #{$order->order_number} cannot be submitted from status {$order->status->value}.");
            }

            // Load required associations
            $order->loadMissing(['package', 'service', 'brief']);

            // Evaluate Decision Engine
            $requiresAdminReview = false;
            $reviewReason = [];

            // 1. Custom order or no fixed package selected
            if ($order->source === ServiceOrderSource::Custom || ! $order->package_id) {
                $requiresAdminReview = true;
                $reviewReason[] = 'Custom scope and budget inquiry.';
            }

            // 2. Specific packages that require review (Loyalty, Cute, Sweet)
            if ($order->package) {
                $packageName = strtolower($order->package->name);
                if (
                    str_contains($packageName, 'loyalty') ||
                    str_contains($packageName, 'cute') ||
                    str_contains($packageName, 'sweet') ||
                    $order->package->base_price <= 0
                ) {
                    $requiresAdminReview = true;
                    $reviewReason[] = 'Package requires admin quote / scope confirmation.';
                }
            }

            // 3. Location / Venue criteria
            if ($order->is_outside_base_area) {
                $requiresAdminReview = true;
                $reviewReason[] = 'Location is outside standard BDJG base area (travel & lodging required).';
            }

            if ($order->venue_count > 1) {
                $requiresAdminReview = true;
                $reviewReason[] = 'Multi-venue coverage requires logistics assessment.';
            }

            $oldStatus = $order->status;

            if ($requiresAdminReview) {
                // Route to Jalur B / Jalur C (ADMIN_REVIEW)
                $order->review_type = ServiceOrderReviewType::AdminReview;
                $order->status = ServiceOrderStatus::Submitted;
                $order->submitted_at = now();
                $order->save();

                ServiceOrderStatusLog::create([
                    'service_order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => ServiceOrderStatus::Submitted,
                    'actor_user_id' => $actor->id,
                    'reason' => implode('; ', $reviewReason),
                ]);

                AuditLogger::log(
                    action: 'SERVICE_ORDER_SUBMITTED_REVIEW',
                    description: "Order #{$order->order_number} submitted for Admin Review",
                    auditable: $order,
                    newValues: ['status' => ServiceOrderStatus::Submitted->value, 'reasons' => $reviewReason]
                );

                return $order->fresh(['brief', 'service', 'package', 'client']);
            }

            // Jalur A: Standard Auto-Checkout
            $order->review_type = ServiceOrderReviewType::AutoCheckout;
            $order->submitted_at = now();
            // Hold slot for 48 hours for standard checkout
            $order->slot_hold_until = now()->addHours(48);
            $order->save();

            // Generate DP Invoice directly
            $this->checkoutStandard->execute($order, $actor);

            return $order->fresh(['brief', 'service', 'package', 'client', 'invoice']);
        });
    }
}
