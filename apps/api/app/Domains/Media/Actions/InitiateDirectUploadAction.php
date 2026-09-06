<?php

namespace App\Domains\Media\Actions;

use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\UploadStatus;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Models\PendingUpload;
use App\Domains\Media\Policies\MediaAssetPolicy;
use App\Domains\Media\Services\MediaStorageService;
use App\Domains\Projects\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class InitiateDirectUploadAction
{
    public function __construct(
        protected MediaStorageService $storageService
    ) {}

    public function execute(
        Project $project,
        User $user,
        string $filename,
        string $mimeType,
        int $sizeBytes,
        MediaCategory $category,
        ?FileVisibility $visibility = null,
        int $expiryMinutes = 120
    ): array {
        $policy = app(MediaAssetPolicy::class);
        if (! $policy->createForProject($user, $project)) {
            throw new AuthorizationException('User is not authorized to upload media for this project.');
        }

        $defaultVisibility = $visibility ?? match ($category) {
            MediaCategory::ClientPreview => FileVisibility::ClientPreview,
            MediaCategory::FinalMaster => FileVisibility::FinalReleased,
            default => FileVisibility::Internal,
        };

        $storageKey = $this->storageService->generateStorageKey($project->id, $category->value, $filename);

        $pendingUpload = PendingUpload::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'filename' => $filename,
            'original_name' => $filename,
            'storage_key' => $storageKey,
            'disk' => 'media',
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
            'category' => $category,
            'visibility' => $defaultVisibility,
            'status' => UploadStatus::Pending,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        $uploadData = $this->storageService->generatePresignedUploadUrl($pendingUpload, $expiryMinutes);

        return [
            'pending_upload' => $pendingUpload,
            'upload_url' => $uploadData['upload_url'],
            'upload_method' => $uploadData['upload_method'],
            'headers' => $uploadData['headers'],
            'expires_at' => $uploadData['expires_at'],
        ];
    }
}
