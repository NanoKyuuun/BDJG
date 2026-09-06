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
            $existing = Project::where('quotation_id', $quotation->id)->first();
            if ($existing) {
                return $existing;
            }

            $quotation->loadMissing(['client', 'inquiry', 'acceptedVersion', 'currentVersion']);
            $version = $quotation->acceptedVersion ?? $quotation->currentVersion;

            $projectName = $version?->project_name
                ?: ($quotation->inquiry?->event_name
                    ?: (($quotation->client?->display_name ?? 'Client').' Project'));

            $project = Project::create([
                'client_id' => $quotation->client_id,
                'quotation_id' => $quotation->id,
                'accepted_quotation_version_id' => $version?->id,
                'name' => $projectName,
                'service_name_snapshot' => $version?->service_name_snapshot,
                'package_name_snapshot' => $version?->package_name_snapshot,
                'contract_value' => $version?->grand_total ?? 0,
                'status' => ProjectStatus::PreProduction,
                'shoot_date' => $quotation->inquiry?->event_date,
                'location' => $quotation->inquiry?->event_location,
                'brief' => $quotation->inquiry?->notes_client ?: $version?->terms,
                'created_by' => $actor?->id,
                'activated_at' => now(),
            ]);

            // Link existing invoices for this quotation to the newly activated project
            Invoice::where('quotation_id', $quotation->id)
                ->whereNull('project_id')
                ->update(['project_id' => $project->id]);

            AuditLogger::log(
                action: 'PROJECT_ACTIVATED',
                description: "Project #{$project->project_number} activated from quotation #{$quotation->quotation_number}",
                auditable: $project,
                newValues: ['project_number' => $project->project_number, 'status' => $project->status->value]
            );

            return $project;
        });
    }
}
