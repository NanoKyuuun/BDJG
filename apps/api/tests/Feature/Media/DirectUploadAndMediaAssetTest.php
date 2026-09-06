<?php

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\UploadStatus;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Media\Models\PendingUpload;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Workers\Enums\WorkerProfession;
use App\Domains\Workers\Enums\WorkerStatus;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    // Create Admin
    $this->admin = User::create([
        'name' => 'Studio Admin',
        'email' => 'admin@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    // Create Client A & Account
    $this->clientA = Client::create([
        'display_name' => 'Client Alpha',
        'email' => 'alpha@client.com',
        'status' => ClientStatus::Active,
    ]);
    $this->clientAUser = User::create([
        'name' => 'Alpha User',
        'email' => 'alpha@client.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientAUser->syncRoles('CLIENT');
    DB::table('client_users')->insert([
        'client_id' => $this->clientA->id,
        'user_id' => $this->clientAUser->id,
        'is_primary' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create Client B & Account
    $this->clientB = Client::create([
        'display_name' => 'Client Beta',
        'email' => 'beta@client.com',
        'status' => ClientStatus::Active,
    ]);
    $this->clientBUser = User::create([
        'name' => 'Beta User',
        'email' => 'beta@client.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientBUser->syncRoles('CLIENT');
    DB::table('client_users')->insert([
        'client_id' => $this->clientB->id,
        'user_id' => $this->clientBUser->id,
        'is_primary' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Create Project A & Project B
    $this->projectA = Project::create([
        'client_id' => $this->clientA->id,
        'name' => 'Project Alpha Wedding',
        'status' => ProjectStatus::Production,
        'created_by' => $this->admin->id,
    ]);

    $this->projectB = Project::create([
        'client_id' => $this->clientB->id,
        'name' => 'Project Beta Commercial',
        'status' => ProjectStatus::Production,
        'created_by' => $this->admin->id,
    ]);

    // Create Worker 1 (Assigned to Project A)
    $this->worker1User = User::create([
        'name' => 'Editor Worker',
        'email' => 'editor@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->worker1User->syncRoles('WORKER');
    $this->worker1Profile = WorkerProfile::create([
        'user_id' => $this->worker1User->id,
        'profession' => WorkerProfession::Editor,
        'status' => WorkerStatus::Active,
    ]);

    $this->projectA->assignments()->create([
        'worker_id' => $this->worker1Profile->id,
        'assignment_role' => 'Lead Video Editor',
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    // Create Worker 2 (Unassigned)
    $this->worker2User = User::create([
        'name' => 'Unassigned Worker',
        'email' => 'unassigned@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->worker2User->syncRoles('WORKER');
    $this->worker2Profile = WorkerProfile::create([
        'user_id' => $this->worker2User->id,
        'profession' => WorkerProfession::Colorist,
        'status' => WorkerStatus::Active,
    ]);
});

it('allows admin and assigned worker to initiate direct upload intent and finalize media asset', function () {
    // 1. Worker initiates upload intent
    $this->actingAs($this->worker1User);
    $intentResponse = $this->postJson('/api/v1/worker/media/upload-intent', [
        'project_id' => $this->projectA->id,
        'filename' => 'rough_cut_v1.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 104857600, // 100 MB
        'category' => 'INTERNAL_DRAFT',
    ]);

    $intentResponse->assertCreated();
    $intentResponse->assertJsonStructure([
        'data' => [
            'public_id',
            'filename',
            'storage_key',
            'upload_url',
            'upload_method',
            'headers',
            'expires_at',
        ],
    ]);

    $pendingUploadPublicId = $intentResponse->json('data.public_id');
    $pending = PendingUpload::where('public_id', $pendingUploadPublicId)->first();
    expect($pending)->not->toBeNull();
    expect($pending->status)->toBe(UploadStatus::Pending);

    // 2. Finalize upload
    $finalizeResponse = $this->postJson('/api/v1/worker/media/finalize-upload', [
        'pending_upload_public_id' => $pendingUploadPublicId,
        'metadata' => ['duration_seconds' => 180, 'resolution' => '3840x2160'],
    ]);

    $finalizeResponse->assertCreated();
    $finalizeResponse->assertJsonPath('data.filename', 'rough_cut_v1.mp4');
    $finalizeResponse->assertJsonPath('data.category', 'INTERNAL_DRAFT');
    $finalizeResponse->assertJsonPath('data.visibility', 'INTERNAL');
    $finalizeResponse->assertJsonPath('data.version_number', 1);

    expect($pending->fresh()->status)->toBe(UploadStatus::Completed);
    $asset = MediaAsset::where('pending_upload_id', $pending->id)->first();
    expect($asset)->not->toBeNull();
    expect($asset->is_video)->toBeTrue();
});

it('denies unassigned worker from uploading media to project', function () {
    $this->actingAs($this->worker2User);
    $response = $this->postJson('/api/v1/worker/media/upload-intent', [
        'project_id' => $this->projectA->id,
        'filename' => 'unauthorized_footage.mov',
        'mime_type' => 'video/quicktime',
        'size_bytes' => 50000000,
        'category' => 'RAW_FOOTAGE',
    ]);

    // Returns 422 or 403 authorization denial
    expect(in_array($response->status(), [403, 422]))->toBeTrue();
});

it('denies client from seeing internal media drafts', function () {
    // Create an internal draft
    $internalAsset = MediaAsset::create([
        'project_id' => $this->projectA->id,
        'uploaded_by_user_id' => $this->admin->id,
        'filename' => 'internal_notes_cut.mp4',
        'original_name' => 'internal_notes_cut.mp4',
        'storage_key' => 'projects/1/internal/draft.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 50000000,
        'category' => MediaCategory::InternalDraft,
        'visibility' => FileVisibility::Internal,
        'version_number' => 1,
    ]);

    // Client A requests project media list
    $this->actingAs($this->clientAUser);
    $response = $this->getJson("/api/v1/client/projects/{$this->projectA->id}/media");
    $response->assertOk();
    // Internal draft should NOT be returned to Client
    expect($response->json('data'))->toBeEmpty();

    // Client A requests signed URL directly for internal asset -> forbidden
    $urlResponse = $this->getJson("/api/v1/client/media/{$internalAsset->id}/signed-url");
    $urlResponse->assertForbidden();
});

it('allows client to access released preview and final master media', function () {
    // Create an internal asset and release it
    $previewAsset = MediaAsset::create([
        'project_id' => $this->projectA->id,
        'uploaded_by_user_id' => $this->admin->id,
        'filename' => 'client_preview_cut_v1.mp4',
        'original_name' => 'client_preview_cut_v1.mp4',
        'storage_key' => 'projects/1/preview/v1.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 80000000,
        'category' => MediaCategory::ClientPreview,
        'visibility' => FileVisibility::Internal,
        'version_number' => 1,
    ]);

    // Admin releases asset to client
    $this->actingAs($this->admin);
    $releaseResponse = $this->patchJson("/api/v1/admin/media/{$previewAsset->id}/release", [
        'visibility' => 'CLIENT_PREVIEW',
    ]);
    $releaseResponse->assertOk();
    expect($previewAsset->fresh()->visibility)->toBe(FileVisibility::ClientPreview);

    // Client A can now see the released preview
    $this->actingAs($this->clientAUser);
    $listResponse = $this->getJson("/api/v1/client/projects/{$this->projectA->id}/media");
    $listResponse->assertOk();
    expect(count($listResponse->json('data')))->toBe(1);

    // Client A can generate signed URL
    $signedUrlResponse = $this->getJson("/api/v1/client/media/{$previewAsset->id}/signed-url");
    $signedUrlResponse->assertOk();
    expect($signedUrlResponse->json('url'))->not->toBeNull();
});

it('strictly blocks cross-client access to media assets (Anti-IDOR)', function () {
    // Create a client-shared asset on Project A (Client Alpha)
    $assetA = MediaAsset::create([
        'project_id' => $this->projectA->id,
        'uploaded_by_user_id' => $this->admin->id,
        'filename' => 'alpha_wedding_teaser.mp4',
        'original_name' => 'alpha_wedding_teaser.mp4',
        'storage_key' => 'projects/1/preview/alpha.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => 50000000,
        'category' => MediaCategory::ClientPreview,
        'visibility' => FileVisibility::ClientPreview,
        'version_number' => 1,
    ]);

    // Client B attempts to list Project A media -> 403 Forbidden
    $this->actingAs($this->clientBUser);
    $response = $this->getJson("/api/v1/client/projects/{$this->projectA->id}/media");
    $response->assertForbidden();

    // Client B attempts to get signed URL for Asset A -> 403 Forbidden
    $signedResponse = $this->getJson("/api/v1/client/media/{$assetA->id}/signed-url");
    $signedResponse->assertForbidden();
});
