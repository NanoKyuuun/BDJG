<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ActivateProjectAction
{
    public function execute(Quotation $quotation, ?User $actor = null): Project
    {
        return DB::transaction(function () use ($quotation, $actor) {
            /** @var Quotation $lockedQuotation */
            $lockedQuotation = Quotation::where('id', $quotation->id)->lockForUpdate()->firstOrFail();

            // Ensure quotation status is ACCEPTED
            if ($lockedQuotation->status !== \App\Domains\Commercial\Enums\QuotationStatus::Accepted) {
                $lockedQuotation->status = \App\Domains\Commercial\Enums\QuotationStatus::Accepted;
                $lockedQuotation->accepted_at = $lockedQuotation->accepted_at ?? now();
                $lockedQuotation->save();
            }

            // Check if already activated under lock
            $existing = Project::where('quotation_id', $lockedQuotation->id)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }

            $lockedQuotation->loadMissing(['client', 'inquiry', 'acceptedVersion', 'currentVersion']);
            $version = $lockedQuotation->acceptedVersion
                ?: ($lockedQuotation->currentVersion
                    ?: $lockedQuotation->versions()->latest('version_number')->first());

            if (! $version) {
                throw new \DomainException("Cannot activate project: Accepted quotation version is missing for Quotation #{$lockedQuotation->quotation_number}.");
            }

            $projectName = $version->project_name
                ?: ($lockedQuotation->inquiry?->event_name
                    ?: (($lockedQuotation->client?->display_name ?? 'Client').' Project'));

            $project = Project::create([
                'client_id' => $lockedQuotation->client_id,
                'quotation_id' => $lockedQuotation->id,
                'accepted_quotation_version_id' => $version->id,
                'name' => $projectName,
                'service_name_snapshot' => $version->service_name_snapshot,
                'package_name_snapshot' => $version->package_name_snapshot,
                'contract_value' => $version->grand_total ?? 0,
                'status' => ProjectStatus::PreProduction,
                'shoot_date' => $lockedQuotation->inquiry?->event_date,
                'location' => $lockedQuotation->inquiry?->event_location,
                'brief' => $lockedQuotation->inquiry?->notes_client ?: $version->terms,
                'created_by' => $actor?->id,
                'activated_at' => now(),
            ]);

            // Link existing invoices for this quotation to the newly activated project
            Invoice::where('quotation_id', $lockedQuotation->id)
                ->whereNull('project_id')
                ->update(['project_id' => $project->id]);

            // If a ServiceOrder exists for this quotation, link project and update status
            $serviceOrder = \App\Domains\Orders\Models\ServiceOrder::where('quotation_id', $lockedQuotation->id)->first();
            if ($serviceOrder) {
                $oldStatus = $serviceOrder->status;
                $serviceOrder->update([
                    'project_id' => $project->id,
                    'status' => \App\Domains\Orders\Enums\ServiceOrderStatus::ProjectCreated,
                ]);

                $serviceOrder->statusLogs()->create([
                    'from_status' => $oldStatus,
                    'to_status' => \App\Domains\Orders\Enums\ServiceOrderStatus::ProjectCreated,
                    'actor_id' => $actor?->id,
                    'reason' => "Project #{$project->project_number} automatically activated",
                ]);
            }

            AuditLogger::log(
                action: 'PROJECT_ACTIVATED',
                description: "Project #{$project->project_number} activated from quotation #{$lockedQuotation->quotation_number}",
                auditable: $project,
                newValues: ['project_number' => $project->project_number, 'status' => $project->status->value]
            );

            return $project;
        });
    }
}
