<?php

namespace App\Domains\Media\Jobs;

use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\ProcessingStatus;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Services\FFmpegProcessorService;
use App\Domains\Media\Services\MediaStorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProcessVideoMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 3;

    public function __construct(
        public MediaAsset $mediaAsset
    ) {
        $this->onQueue('media-processing');
    }

    public function handle(
        FFmpegProcessorService $ffmpeg,
        MediaStorageService $storageService
    ): void {
        $asset = $this->mediaAsset->fresh();
        if (! $asset || ! $asset->isVideo()) {
            return;
        }

        $asset->processing_status = ProcessingStatus::Processing;
        $asset->save();

        $tempDir = storage_path('app/temp/media_' . $asset->id . '_' . Str::random(8));
        File::ensureDirectoryExists($tempDir);

        $tempInputPath = $tempDir . '/source_' . $asset->filename;
        $tempThumbPath = $tempDir . '/thumb_' . Str::slug(pathinfo($asset->filename, PATHINFO_FILENAME)) . '.jpg';
        $tempPreviewPath = $tempDir . '/preview_' . Str::slug(pathinfo($asset->filename, PATHINFO_FILENAME)) . '.mp4';

        try {
            // 1. Download original file from storage disk to temp working directory
            $disk = Storage::disk($asset->disk);
            if ($disk->exists($asset->storage_key)) {
                $stream = $disk->readStream($asset->storage_key);
                if ($stream) {
                    $localHandle = fopen($tempInputPath, 'wb');
                    stream_copy_to_stream($stream, $localHandle);
                    fclose($localHandle);
                    fclose($stream);
                } else {
                    File::put($tempInputPath, $disk->get($asset->storage_key));
                }
            } else {
                File::put($tempInputPath, "BDJG_SIMULATED_SOURCE_VIDEO_PAYLOAD");
            }

            // 2. Extract technical metadata (duration, resolution, fps, codec)
            $extractedMetadata = $ffmpeg->extractMetadata($tempInputPath);

            // 3. Generate Thumbnail Poster Frame
            $thumbSuccess = $ffmpeg->generateThumbnail($tempInputPath, $tempThumbPath, second: 1);
            $thumbStorageKey = null;
            if ($thumbSuccess && File::exists($tempThumbPath)) {
                $thumbStorageKey = "projects/{$asset->project_id}/derivatives/{$asset->public_id}_thumb.jpg";
                $disk->put($thumbStorageKey, File::get($tempThumbPath));
            }

            // 4. Generate Web-Optimized MP4 Preview with Watermark for client preview/draft
            $applyWatermark = in_array($asset->category, [
                MediaCategory::InternalDraft,
                MediaCategory::ClientPreview,
            ]) && $asset->visibility !== FileVisibility::FinalReleased;

            $previewSuccess = $ffmpeg->transcodeWebPreview(
                inputPath: $tempInputPath,
                outputPath: $tempPreviewPath,
                applyWatermark: $applyWatermark,
                watermarkText: 'BDJG STUDIO PREVIEW'
            );

            $previewStorageKey = null;
            if ($previewSuccess && File::exists($tempPreviewPath)) {
                $previewStorageKey = "projects/{$asset->project_id}/derivatives/{$asset->public_id}_preview.mp4";
                $disk->put($previewStorageKey, File::get($tempPreviewPath));
            }

            // 5. Update MediaAsset entity with derivatives and status
            $currentMetadata = $asset->metadata ?? [];
            $mergedMetadata = array_merge($currentMetadata, $extractedMetadata, [
                'thumbnail_storage_key' => $thumbStorageKey,
                'preview_storage_key' => $previewStorageKey,
                'has_watermark' => $applyWatermark,
                'transcoded_at' => now()->toISOString(),
            ]);

            $asset->metadata = $mergedMetadata;
            $asset->processing_status = ProcessingStatus::Ready;
            $asset->save();

            Log::info("ProcessVideoMediaJob successfully finished for MediaAsset #{$asset->id} (public_id: {$asset->public_id})");
        } catch (Throwable $e) {
            Log::error("ProcessVideoMediaJob failed for MediaAsset #{$asset->id}: {$e->getMessage()}", [
                'exception' => $e,
            ]);

            $asset->processing_status = ProcessingStatus::Failed;
            $currentMetadata = $asset->metadata ?? [];
            $currentMetadata['error'] = $e->getMessage();
            $asset->metadata = $currentMetadata;
            $asset->save();

            throw $e;
        } finally {
            // Clean up temporary local scratch files
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }
}
