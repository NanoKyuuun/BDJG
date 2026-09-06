<?php

namespace App\Domains\Media\Services;

use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Models\PendingUpload;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class MediaStorageService
{
    /**
     * Generate storage key pattern: projects/{projectId}/{category}/{year}/{month}/{uniqueId}_{safeFilename}
     */
    public function generateStorageKey(int $projectId, string $category, string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = Str::slug(pathinfo($filename, PATHINFO_FILENAME));
        $uniqueId = Str::random(12);
        $cleanFilename = $extension ? "{$basename}-{$uniqueId}.{$extension}" : "{$basename}-{$uniqueId}";
        $year = date('Y');
        $month = date('m');
        $catFolder = strtolower(str_replace('_', '-', $category));

        return "projects/{$projectId}/{$catFolder}/{$year}/{$month}/{$cleanFilename}";
    }

    /**
     * Generate direct presigned upload URL for S3/MinIO or mock/local route
     */
    public function generatePresignedUploadUrl(PendingUpload $pendingUpload, DateTimeInterface|int $expiresInMinutes = 120): array
    {
        $disk = Storage::disk($pendingUpload->disk ?? 'media');
        $expiresAt = $expiresInMinutes instanceof DateTimeInterface
            ? $expiresInMinutes
            : now()->addMinutes($expiresInMinutes);

        // If driver supports native temporaryUploadUrl (e.g. S3 / R2 / MinIO)
        if (method_exists($disk, 'temporaryUploadUrl')) {
            try {
                $uploadData = $disk->temporaryUploadUrl(
                    $pendingUpload->storage_key,
                    $expiresAt,
                    ['ContentType' => $pendingUpload->mime_type]
                );

                return [
                    'upload_url' => $uploadData['url'] ?? $uploadData,
                    'upload_method' => 'PUT',
                    'headers' => [
                        'Content-Type' => $pendingUpload->mime_type,
                    ],
                    'expires_at' => $expiresAt->toIso8601String(),
                ];
            } catch (\Throwable) {
                // Fall back to local simulated direct upload endpoint
            }
        }

        // For local development / local disk driver: generate signed upload URL through API route
        $localUploadUrl = URL::temporarySignedRoute(
            'media.local-direct-upload',
            $expiresAt,
            ['public_id' => $pendingUpload->public_id]
        );

        return [
            'upload_url' => $localUploadUrl,
            'upload_method' => 'PUT',
            'headers' => [
                'Content-Type' => $pendingUpload->mime_type,
            ],
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * Generate short-lived signed URL for reading/downloading a media asset
     */
    public function generateSignedViewUrl(MediaAsset $mediaAsset, int $expiresInMinutes = 60): string
    {
        $disk = Storage::disk($mediaAsset->disk ?? 'media');
        $expiresAt = now()->addMinutes($expiresInMinutes);

        if (method_exists($disk, 'temporaryUrl')) {
            try {
                return $disk->temporaryUrl($mediaAsset->storage_key, $expiresAt);
            } catch (\Throwable) {
                // Fallback to local signed stream route
            }
        }

        return URL::temporarySignedRoute(
            'media.stream',
            $expiresAt,
            ['mediaAsset' => $mediaAsset->public_id]
        );
    }

    /**
     * Verify if file exists in the storage disk
     */
    public function fileExists(string $storageKey, string $diskName = 'media'): bool
    {
        return Storage::disk($diskName)->exists($storageKey);
    }

    /**
     * Delete file from storage disk
     */
    public function deleteFile(string $storageKey, string $diskName = 'media'): bool
    {
        return Storage::disk($diskName)->delete($storageKey);
    }
}
