<?php

namespace App\Domains\Media\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Media\Enums\ProcessingStatus;
use App\Domains\Media\Enums\UploadStatus;
use App\Domains\Media\Jobs\ProcessVideoMediaJob;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Models\PendingUpload;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinalizeDirectUploadAction
{
    public function execute(string $pendingUploadPublicId, User $actor, ?array $metadata = null): MediaAsset
    {
        return DB::transaction(function () use ($pendingUploadPublicId, $actor, $metadata) {
            $pendingUpload = PendingUpload::where('public_id', $pendingUploadPublicId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pendingUpload->status !== UploadStatus::Pending) {
                throw new InvalidArgumentException("Pending upload has already been processed with status {$pendingUpload->status->value}.");
            }

            if ($pendingUpload->isExpired()) {
                $pendingUpload->status = UploadStatus::Expired;
                $pendingUpload->save();
                throw new InvalidArgumentException('Pending upload session has expired. Please initiate a new upload.');
            }

            // Calculate version number for this category in the project
            $latestVersion = MediaAsset::where('project_id', $pendingUpload->project_id)
                ->where('category', $pendingUpload->category)
                ->max('version_number') ?? 0;

            $versionNumber = $latestVersion + 1;
            $isVideo = str_starts_with($pendingUpload->mime_type, 'video/');

            $mediaAsset = MediaAsset::create([
                'project_id' => $pendingUpload->project_id,
                'uploaded_by_user_id' => $actor->id,
                'pending_upload_id' => $pendingUpload->id,
                'filename' => $pendingUpload->filename,
                'original_name' => $pendingUpload->original_name,
                'storage_key' => $pendingUpload->storage_key,
                'disk' => $pendingUpload->disk,
                'mime_type' => $pendingUpload->mime_type,
                'size_bytes' => $pendingUpload->size_bytes,
                'category' => $pendingUpload->category,
                'visibility' => $pendingUpload->visibility,
                'version_number' => $versionNumber,
                'processing_status' => $isVideo ? ProcessingStatus::Pending : ProcessingStatus::Ready,
                'metadata' => $metadata,
            ]);

            $pendingUpload->status = UploadStatus::Completed;
            $pendingUpload->save();

            AuditLogger::log(
                action: 'MEDIA_ASSET_UPLOADED',
                description: "Media asset '{$mediaAsset->original_name}' (v{$mediaAsset->version_number}) finalized for Project #{$mediaAsset->project_id}",
                auditable: $mediaAsset,
                newValues: [
                    'filename' => $mediaAsset->filename,
                    'size_bytes' => $mediaAsset->size_bytes,
                    'category' => $mediaAsset->category->value,
                    'visibility' => $mediaAsset->visibility->value,
                ]
            );

            if ($isVideo) {
                DB::afterCommit(function () use ($mediaAsset) {
                    ProcessVideoMediaJob::dispatch($mediaAsset);
                });
            }

            return $mediaAsset;
        });
    }
}
