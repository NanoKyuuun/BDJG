<?php

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Delivery\Enums\DeliveryPackageStatus;
use App\Domains\Delivery\Models\DeliveryPackage;
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
use App\Domains\Revisions\Enums\CommentStatus;
use App\Domains\Revisions\Enums\RevisionRoundStatus;
use App\Domains\Revisions\Models\Revision;
use App\Domains\Revisions\Models\RevisionComment;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Workers\Enums\WorkerProfession;
use App\Domains\Workers\Enums\WorkerStatus;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('Release B Golden Path: Full End-to-End Media Workflow (Upload -> Transcode -> Preview -> Revisions -> Final Delivery)', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    Storage::fake('media');

    // 1. Setup Users & Roles
    $admin = User::create([
        'name' => 'Studio Admin',
        'email' => 'admin@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $admin->syncRoles('ADMIN');

    $client = Client::create([
        'display_name' => 'Cakrawala Studios',
        'email' => 'contact@cakrawala.id',
        'status' => ClientStatus::Active,
    ]);

    $clientUser = User::create([
        'name' => 'Cakrawala Director',
        'email' => 'contact@cakrawala.id',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $clientUser->syncRoles('CLIENT');
    DB::table('client_users')->insert([
        'client_id' => $client->id,
        'user_id' => $clientUser->id,
        'is_primary' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $project = Project::create([
        'client_id' => $client->id,
        'name' => 'Cakrawala Brand Film 2026',
        'status' => ProjectStatus::Production,
        'created_by' => $admin->id,
    ]);

    $workerUser = User::create([
        'name' => 'Lead Video Editor',
        'email' => 'editor@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $workerUser->syncRoles('WORKER');
    $workerProfile = WorkerProfile::create([
        'user_id' => $workerUser->id,
        'profession' => WorkerProfession::Editor,
        'status' => WorkerStatus::Active,
    ]);
    $project->assignments()->create([
        'worker_id' => $workerProfile->id,
        'assignment_role' => 'Lead Video Editor',
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    // =========================================================================
    // STEP 1: Worker initiates direct upload intent for 4K video draft
    // =========================================================================
    $this->actingAs($workerUser);
    $intentResponse = $this->postJson('/api/v1/worker/media/upload-intent', [
        'project_id' => $project->id,
        'filename' => 'brand_film_draft_v1.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 450000000,
        'category' => 'INTERNAL_DRAFT',
    ]);
    $intentResponse->assertCreated();
    $pendingPublicId = $intentResponse->json('data.public_id');
    $pending = PendingUpload::where('public_id', $pendingPublicId)->first();
    expect($pending)->not->toBeNull();

    // =========================================================================
    // STEP 2: Worker finalizes direct upload
    // =========================================================================
    Storage::disk('media')->put($pending->storage_key, '4K_SOURCE_VIDEO_PAYLOAD');

    $finalizeResponse = $this->postJson('/api/v1/worker/media/finalize-upload', [
        'pending_upload_public_id' => $pendingPublicId,
        'metadata' => ['fps' => 24, 'resolution' => '3840x2160'],
    ]);
    $finalizeResponse->assertCreated();
    $mediaAssetId = $finalizeResponse->json('data.id');
    $mediaAsset = MediaAsset::find($mediaAssetId);
    expect($mediaAsset)->not->toBeNull();
    expect(in_array($mediaAsset->processing_status, [ProcessingStatus::Pending, ProcessingStatus::Ready]))->toBeTrue();

    // =========================================================================
    // STEP 3: Asynchronous FFmpeg transcode & thumbnail pipeline executes
    // =========================================================================
    $ffmpeg = app(FFmpegProcessorService::class);
    $storageService = app(MediaStorageService::class);
    $job = new ProcessVideoMediaJob($mediaAsset);
    $job->handle($ffmpeg, $storageService);

    $mediaAsset->refresh();
    expect($mediaAsset->processing_status)->toBe(ProcessingStatus::Ready);
    expect($mediaAsset->metadata['thumbnail_storage_key'])->not->toBeNull();
    expect($mediaAsset->metadata['preview_storage_key'])->not->toBeNull();

    // =========================================================================
    // STEP 4: Admin releases media asset to client as CLIENT_PREVIEW
    // =========================================================================
    $this->actingAs($admin);
    $releaseResponse = $this->patchJson("/api/v1/admin/media/{$mediaAsset->id}/release", [
        'visibility' => 'CLIENT_PREVIEW',
    ]);
    $releaseResponse->assertOk();
    expect($mediaAsset->fresh()->visibility)->toBe(FileVisibility::ClientPreview);

    // =========================================================================
    // STEP 5: Client accesses project media & views signed stream URL
    // =========================================================================
    $this->actingAs($clientUser);
    $clientMediaList = $this->getJson("/api/v1/client/projects/{$project->id}/media");
    $clientMediaList->assertOk();
    expect(count($clientMediaList->json('data')))->toBe(1);

    $signedUrlResponse = $this->getJson("/api/v1/client/media/{$mediaAsset->id}/signed-url");
    $signedUrlResponse->assertOk();
    expect($signedUrlResponse->json('url'))->not->toBeNull();

    // =========================================================================
    // STEP 6: Client opens Revision Round #1 with frame-accurate timecode feedback
    // =========================================================================
    $revisionResponse = $this->postJson("/api/v1/client/projects/{$project->id}/revisions", [
        'media_asset_id' => $mediaAsset->id,
        'title' => 'First Cut Visual & Audio Feedback',
        'notes' => 'Great start! Couple of timing tweaks needed.',
    ]);
    $revisionResponse->assertCreated();
    $revisionId = $revisionResponse->json('data.id');
    $revision = Revision::find($revisionId);

    $commentResponse = $this->postJson("/api/v1/client/revisions/{$revision->id}/comments", [
        'timecode_seconds' => 12.5,
        'frame_number' => 300,
        'coordinates' => ['x' => 0.45, 'y' => 0.60],
        'comment' => 'Add subtle zoom on product reveal at 12.5s',
    ]);
    $commentResponse->assertCreated();
    $commentId = $commentResponse->json('data.id');
    $comment = RevisionComment::find($commentId);
    expect($comment->status)->toBe(CommentStatus::Open);

    // =========================================================================
    // STEP 7: Worker resolves feedback comment
    // =========================================================================
    $this->actingAs($workerUser);
    $resolveResponse = $this->postJson("/api/v1/worker/revision-comments/{$comment->id}/resolve");
    $resolveResponse->assertOk();
    expect($comment->fresh()->status)->toBe(CommentStatus::Resolved);
    expect($revision->fresh()->status)->toBe(RevisionRoundStatus::Resolved);

    // =========================================================================
    // STEP 8: Admin packages final master deliverables for client handover
    // =========================================================================
    $this->actingAs($admin);
    $masterMedia = MediaAsset::create([
        'project_id' => $project->id,
        'uploaded_by_user_id' => $admin->id,
        'filename' => 'brand_film_final_master_4k.mp4',
        'original_name' => 'Brand Film 2026 4K Master.mp4',
        'storage_key' => "projects/{$project->id}/final/master_4k.mp4",
        'disk' => 'media',
        'mime_type' => 'video/mp4',
        'size_bytes' => 950000000,
        'category' => MediaCategory::FinalMaster,
        'visibility' => FileVisibility::Internal,
        'version_number' => 1,
        'processing_status' => ProcessingStatus::Ready,
    ]);
    Storage::disk('media')->put($masterMedia->storage_key, 'FINAL_4K_MASTER_PAYLOAD');

    $packageResponse = $this->postJson("/api/v1/admin/projects/{$project->id}/deliveries", [
        'media_asset_ids' => [$masterMedia->id],
        'title' => 'Official Brand Film 2026 Master Package',
        'notes' => 'Master 4K deliverable.',
        'expiry_days' => 30,
    ]);
    $packageResponse->assertCreated();
    $packageId = $packageResponse->json('data.id');
    $package = DeliveryPackage::find($packageId);
    expect($package->status)->toBe(DeliveryPackageStatus::Ready);
    expect($masterMedia->fresh()->visibility)->toBe(FileVisibility::FinalReleased);

    // =========================================================================
    // STEP 9: Client downloads final deliverables via high-speed signed link
    // =========================================================================
    $this->actingAs($clientUser);
    $downloadResponse = $this->getJson("/api/v1/client/deliveries/{$package->id}/download-url");
    $downloadResponse->assertOk();
    expect($downloadResponse->json('download_url'))->not->toBeNull();
    expect($package->fresh()->status)->toBe(DeliveryPackageStatus::Downloaded);
    expect($package->fresh()->download_count)->toBe(1);
});
