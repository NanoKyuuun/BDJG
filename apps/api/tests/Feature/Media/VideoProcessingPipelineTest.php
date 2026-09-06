<?php

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Media\Actions\FinalizeDirectUploadAction;
use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\ProcessingStatus;
use App\Domains\Media\Enums\UploadStatus;
use App\Domains\Media\Jobs\ProcessVideoMediaJob;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Models\PendingUpload;
use App\Domains\Media\Services\FFmpegProcessorService;
use App\Domains\Media\Services\MediaStorageService;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    Storage::fake('media');

    $this->admin = User::create([
        'name' => 'Studio Admin',
        'email' => 'admin@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    $this->client = Client::create([
        'display_name' => 'Acme Corp',
        'email' => 'acme@corp.com',
        'status' => ClientStatus::Active,
    ]);

    $this->project = Project::create([
        'client_id' => $this->client->id,
        'name' => 'Commercial Video 2026',
        'status' => ProjectStatus::Production,
        'created_by' => $this->admin->id,
    ]);
});

it('dispatches ProcessVideoMediaJob when a video direct upload is finalized', function () {
    Queue::fake();

    $pending = PendingUpload::create([
        'project_id' => $this->project->id,
        'user_id' => $this->admin->id,
        'filename' => 'interview_footage.mp4',
        'original_name' => 'interview_footage.mp4',
        'storage_key' => "projects/{$this->project->id}/raw/interview_footage.mp4",
        'disk' => 'media',
        'mime_type' => 'video/mp4',
        'size_bytes' => 80000000,
        'category' => MediaCategory::RawFootage,
        'visibility' => FileVisibility::Internal,
        'status' => UploadStatus::Pending,
        'expires_at' => now()->addHour(),
    ]);

    $action = app(FinalizeDirectUploadAction::class);
    $asset = $action->execute($pending->public_id, $this->admin);

    expect($asset)->not->toBeNull();
    expect($asset->processing_status)->toBe(ProcessingStatus::Pending);

    Queue::assertPushed(ProcessVideoMediaJob::class, function ($job) use ($asset) {
        return $job->mediaAsset->id === $asset->id;
    });
});

it('executes ProcessVideoMediaJob to generate derivatives and update asset status to READY', function () {
    $storageKey = "projects/{$this->project->id}/draft/wedding_teaser.mp4";
    Storage::disk('media')->put($storageKey, 'DUMMY_MP4_BINARY_PAYLOAD_FOR_TEST');

    $asset = MediaAsset::create([
        'project_id' => $this->project->id,
        'uploaded_by_user_id' => $this->admin->id,
        'filename' => 'wedding_teaser.mp4',
        'original_name' => 'Wedding Teaser 4K Cut.mp4',
        'storage_key' => $storageKey,
        'disk' => 'media',
        'mime_type' => 'video/mp4',
        'size_bytes' => 150000000,
        'category' => MediaCategory::ClientPreview,
        'visibility' => FileVisibility::ClientPreview,
        'version_number' => 1,
        'processing_status' => ProcessingStatus::Pending,
    ]);

    $ffmpeg = app(FFmpegProcessorService::class);
    $storageService = app(MediaStorageService::class);

    $job = new ProcessVideoMediaJob($asset);
    $job->handle($ffmpeg, $storageService);

    $refreshed = $asset->fresh();
    expect($refreshed->processing_status)->toBe(ProcessingStatus::Ready);
    expect($refreshed->metadata)->toHaveKeys([
        'duration_seconds',
        'resolution',
        'thumbnail_storage_key',
        'preview_storage_key',
        'has_watermark',
    ]);
    expect($refreshed->metadata['has_watermark'])->toBeTrue();

    // Verify derivative files were written to storage disk
    expect(Storage::disk('media')->exists($refreshed->metadata['thumbnail_storage_key']))->toBeTrue();
    expect(Storage::disk('media')->exists($refreshed->metadata['preview_storage_key']))->toBeTrue();
});
