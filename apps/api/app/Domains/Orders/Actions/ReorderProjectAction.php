<?php

namespace App\Domains\Orders\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\Package;
use App\Domains\Orders\Enums\BriefCategory;
use App\Domains\Orders\Enums\ServiceOrderReviewType;
use App\Domains\Orders\Enums\ServiceOrderSource;
use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Orders\Models\ServiceOrder;
use App\Domains\Orders\Models\ServiceOrderBrief;
use App\Domains\Orders\Models\ServiceOrderStatusLog;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReorderProjectAction
{
    public function execute(Project $project, User $actor): ServiceOrder
    {
        return DB::transaction(function () use ($project, $actor) {
            $project->loadMissing(['client', 'quotation.currentVersion']);

            if (! $project->client_id) {
                throw new InvalidArgumentException("Cannot reorder project #{$project->project_number}: Missing client record.");
            }

            // Find associated package or service if exists
            $package = null;
            if ($project->service_name_snapshot && $project->package_name_snapshot) {
                $package = Package::where('name', $project->package_name_snapshot)->first();
            }

            $packageSnapshot = null;
            if ($package) {
                $packageSnapshot = [
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'service_id' => $package->service_id,
                    'base_price' => $package->base_price,
                    'currency' => $package->currency,
                    'default_dp_type' => $package->default_dp_type?->value,
                    'default_dp_value' => $package->default_dp_value,
                    'catalog_captured_at' => now()->toISOString(),
                    'reordered_from_project' => $project->project_number,
                ];
            }

            $newOrder = ServiceOrder::create([
                'client_id' => $project->client_id,
                'service_id' => $package?->service_id,
                'package_id' => $package?->id,
                'source' => ServiceOrderSource::Reorder,
                'review_type' => $package ? ServiceOrderReviewType::AutoCheckout : ServiceOrderReviewType::AdminReview,
                'status' => ServiceOrderStatus::Draft,
                'package_snapshot' => $packageSnapshot,
                'location_name' => $project->location,
                'venue_count' => 1,
                'is_outside_base_area' => false,
                'created_by' => $actor->id,
            ]);

            ServiceOrderBrief::create([
                'service_order_id' => $newOrder->id,
                'event_name' => "Reorder: {$project->name}",
                'event_category' => BriefCategory::Wedding,
                'portfolio_consent' => true,
                'additional_notes' => "Re-ordered based on past project #{$project->project_number}. Please confirm updated date and event venue.",
            ]);

            ServiceOrderStatusLog::create([
                'service_order_id' => $newOrder->id,
                'from_status' => null,
                'to_status' => ServiceOrderStatus::Draft,
                'actor_user_id' => $actor->id,
                'reason' => "Re-order created from past project #{$project->project_number}",
            ]);

            AuditLogger::log(
                action: 'SERVICE_ORDER_REORDER_CREATED',
                description: "Re-order #{$newOrder->order_number} created from Project #{$project->project_number}",
                auditable: $newOrder,
                newValues: ['order_number' => $newOrder->order_number, 'source_project' => $project->project_number]
            );

            return $newOrder->load(['brief', 'service', 'package', 'client']);
        });
    }
}
