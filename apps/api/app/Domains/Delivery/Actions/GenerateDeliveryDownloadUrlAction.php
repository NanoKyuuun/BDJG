<?php

namespace App\Domains\Delivery\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Delivery\Enums\DeliveryPackageStatus;
use App\Domains\Delivery\Models\DeliveryPackage;
use App\Domains\Delivery\Policies\DeliveryPackagePolicy;
use App\Domains\Media\Services\MediaStorageService;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use InvalidArgumentException;

class GenerateDeliveryDownloadUrlAction
{
    public function __construct(
        protected MediaStorageService $storageService
    ) {}

    public function execute(DeliveryPackage $package, User $actor, int $expiryMinutes = 120): array
    {
        $policy = app(DeliveryPackagePolicy::class);
        if (! $policy->download($actor, $package)) {
            throw new AuthorizationException('User is not authorized to download this delivery package.');
        }

        if ($package->isExpired()) {
            throw new InvalidArgumentException('This delivery package has expired.');
        }

        if ($package->status === DeliveryPackageStatus::Revoked) {
            throw new InvalidArgumentException('This delivery package has been revoked.');
        }

        $package->increment('download_count');
        if ($package->status === DeliveryPackageStatus::Ready) {
            $package->status = DeliveryPackageStatus::Downloaded;
            $package->save();
        }

        // Get primary asset or storage key
        $primaryAsset = $package->mediaAssets()->first();
        $downloadUrl = $primaryAsset
            ? $this->storageService->generateSignedViewUrl($primaryAsset, $expiryMinutes)
            : $this->storageService->generateSignedViewUrl((object) [
                'disk' => $package->disk,
                'storage_key' => $package->storage_key,
                'public_id' => $package->public_id,
            ], $expiryMinutes);

        AuditLogger::log(
            action: 'FINAL_DELIVERY_DOWNLOADED',
            description: "Delivery package '{$package->title}' downloaded by {$actor->name} (download count: {$package->download_count})",
            auditable: $package
        );

        return [
            'download_url' => $downloadUrl,
            'package_title' => $package->title,
            'file_count' => $package->file_count,
            'total_size_bytes' => $package->total_size_bytes,
            'download_count' => $package->download_count,
            'expires_at' => now()->addMinutes($expiryMinutes)->toISOString(),
        ];
    }
}
