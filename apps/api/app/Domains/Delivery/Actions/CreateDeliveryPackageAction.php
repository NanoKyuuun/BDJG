<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Delivery\Enums\DeliveryPackageStatus;
use App\Domains\Delivery\Models\DeliveryPackage;
use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateDeliveryPackageAction
{
    public function execute(
        Project $project,
        User $actor,
        array $mediaAssetIds,
        string $title,
        ?string $notes = null,
        ?int $expiryDays = 30
    ): DeliveryPackage {
        if (empty($mediaAssetIds)) {
            throw new InvalidArgumentException('At least one media asset must be selected for delivery packaging.');
        }

        return DB::transaction(function () use ($project, $actor, $mediaAssetIds, $title, $notes, $expiryDays) {
            $assets = MediaAsset::where('project_id', $project->id)
                ->whereIn('id', $mediaAssetIds)
                ->get();

            if ($assets->count() !== count($mediaAssetIds)) {
                throw new InvalidArgumentException('One or more selected media assets do not belong to this project.');
            }

            $totalSizeBytes = $assets->sum('size_bytes');
            $fileCount = $assets->count();

            // Set delivery storage key to the primary master or bundle
            $primaryAsset = $assets->first();
            $storageKey = $primaryAsset ? $primaryAsset->storage_key : "projects/{$project->id}/delivery/bundle.zip";

            $package = DeliveryPackage::create([
                'project_id' => $project->id,
                'title' => $title,
                'status' => DeliveryPackageStatus::Ready,
                'storage_key' => $storageKey,
                'disk' => 'media',
                'total_size_bytes' => $totalSizeBytes,
                'file_count' => $fileCount,
                'download_count' => 0,
                'expires_at' => $expiryDays ? now()->addDays($expiryDays) : now()->addDays(30),
                'released_by_user_id' => $actor->id,
                'released_at' => now(),
                'notes' => $notes,
            ]);

            // Attach items and update media asset visibility to FINAL_RELEASED
            foreach ($assets as $asset) {
                $package->items()->create(['media_asset_id' => $asset->id]);

                $asset->visibility = FileVisibility::FinalReleased;
                $asset->released_by_user_id = $actor->id;
                $asset->released_at = now();
                $asset->save();
            }

            AuditLogger::log(
                action: 'DELIVERY_PACKAGE_CREATED',
                description: "Final delivery package '{$title}' ({$fileCount} files, " . round($totalSizeBytes / 1048576, 2) . "MB) created for Project #{$project->id}",
                auditable: $package,
                newValues: [
                    'title' => $title,
                    'file_count' => $fileCount,
                    'total_size_bytes' => $totalSizeBytes,
                ]
            );

            if ($project->client && $project->client->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($project->client->email)
                        ->send(new \App\Mail\FinalDeliveryReadyMail($package->load('project.client')));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to send final delivery email: " . $e->getMessage());
                }
            }

            return $package;
        });
    }
}
