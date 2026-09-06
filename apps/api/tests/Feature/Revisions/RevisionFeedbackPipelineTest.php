<?php

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\ProcessingStatus;
use App\Domains\Media\Models\MediaAsset;
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

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    // Admin
    $this->admin = User::create([
        'name' => 'Studio Admin',
        'email' => 'admin@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    // Client A
    $this->clientA = Client::create([
        'display_name' => 'Client Alpha',
        'email' => 'alpha@client.com',
        'status' => ClientStatus::Active,
    ]);
    $this->clientAUser = User::create([
        'name' => 'Alpha Client User',
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

    // Client B
    $this->clientB = Client::create([
        'display_name' => 'Client Beta',
        'email' => 'beta@client.com',
        'status' => ClientStatus::Active,
    ]);
    $this->clientBUser = User::create([
        'name' => 'Beta Client User',
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

    // Projects
    $this->projectA = Project::create([
        'client_id' => $this->clientA->id,
        'name' => 'Alpha Wedding Cinema',
        'status' => ProjectStatus::Production,
        'created_by' => $this->admin->id,
    ]);

    $this->projectB = Project::create([
        'client_id' => $this->clientB->id,
        'name' => 'Beta Commercial Shoot',
        'status' => ProjectStatus::Production,
        'created_by' => $this->admin->id,
    ]);

    // Media Asset on Project A
    $this->mediaAssetA = MediaAsset::create([
        'project_id' => $this->projectA->id,
        'uploaded_by_user_id' => $this->admin->id,
        'filename' => 'rough_cut_preview_v1.mp4',
        'original_name' => 'Rough Cut Preview v1.mp4',
        'storage_key' => 'projects/1/preview/v1.mp4',
        'disk' => 'media',
        'mime_type' => 'video/mp4',
        'size_bytes' => 120000000,
        'category' => MediaCategory::ClientPreview,
        'visibility' => FileVisibility::ClientPreview,
        'version_number' => 1,
        'processing_status' => ProcessingStatus::Ready,
    ]);

    // Worker 1 (Assigned to Project A)
    $this->worker1User = User::create([
        'name' => 'Editor Specialist',
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

    // Worker 2 (Unassigned)
    $this->worker2User = User::create([
        'name' => 'Unassigned Colorist',
        'email' => 'colorist@bdjg.studio',
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

it('allows client to create a revision round and submit frame-accurate timecode feedback', function () {
    $this->actingAs($this->clientAUser);

    // 1. Client creates Revision Round #1
    $revisionResponse = $this->postJson("/api/v1/client/projects/{$this->projectA->id}/revisions", [
        'media_asset_id' => $this->mediaAssetA->id,
        'title' => 'First Cut Color & Pacing Feedback',
        'notes' => 'Please adjust brightness on ceremony entrance and trim outro.',
    ]);

    $revisionResponse->assertCreated();
    $revisionResponse->assertJsonPath('data.round_number', 1);
    $revisionResponse->assertJsonPath('data.status', 'OPEN');

    $revisionId = $revisionResponse->json('data.id');
    $revision = Revision::find($revisionId);
    expect($revision)->not->toBeNull();

    // 2. Client adds timecode comment at 14.250s
    $commentResponse = $this->postJson("/api/v1/client/revisions/{$revision->id}/comments", [
        'timecode_seconds' => 14.25,
        'frame_number' => 342,
        'coordinates' => ['x' => 0.52, 'y' => 0.38],
        'comment' => 'Lighting looks too warm here, please cool down the skin tones.',
    ]);

    $commentResponse->assertCreated();
    $commentResponse->assertJsonPath('data.timecode_seconds', 14.25);
    $commentResponse->assertJsonPath('data.status', 'OPEN');

    $comment = RevisionComment::find($commentResponse->json('data.id'));
    expect($comment)->not->toBeNull();
    expect($comment->revision_id)->toBe($revision->id);
});

it('allows assigned worker to view and resolve revision feedback comments', function () {
    // Create revision with comment
    $revision = Revision::create([
        'project_id' => $this->projectA->id,
        'media_asset_id' => $this->mediaAssetA->id,
        'round_number' => 1,
        'title' => 'Color Grading Tweaks',
        'requested_by_user_id' => $this->clientAUser->id,
        'status' => RevisionRoundStatus::Open,
    ]);

    $comment = RevisionComment::create([
        'revision_id' => $revision->id,
        'media_asset_id' => $this->mediaAssetA->id,
        'user_id' => $this->clientAUser->id,
        'timecode_seconds' => 35.5,
        'comment' => 'Lower the background audio volume during vows.',
        'status' => CommentStatus::Open,
    ]);

    // Assigned Worker resolves the comment
    $this->actingAs($this->worker1User);

    $resolveResponse = $this->postJson("/api/v1/worker/revision-comments/{$comment->id}/resolve");
    $resolveResponse->assertOk();
    $resolveResponse->assertJsonPath('data.status', 'RESOLVED');

    expect($comment->fresh()->status)->toBe(CommentStatus::Resolved);
    expect($comment->fresh()->resolved_by_user_id)->toBe($this->worker1User->id);

    // All comments resolved -> Revision round status automatically resolves
    expect($revision->fresh()->status)->toBe(RevisionRoundStatus::Resolved);
});

it('strictly denies unassigned worker from accessing or resolving project revisions', function () {
    $revision = Revision::create([
        'project_id' => $this->projectA->id,
        'round_number' => 1,
        'title' => 'Confidential Notes',
        'requested_by_user_id' => $this->clientAUser->id,
        'status' => RevisionRoundStatus::Open,
    ]);

    $comment = RevisionComment::create([
        'revision_id' => $revision->id,
        'user_id' => $this->clientAUser->id,
        'timecode_seconds' => 10.0,
        'comment' => 'Confidential cut notes',
        'status' => CommentStatus::Open,
    ]);

    // Worker 2 is not assigned to Project A
    $this->actingAs($this->worker2User);

    $listResponse = $this->getJson("/api/v1/worker/projects/{$this->projectA->id}/revisions");
    $listResponse->assertForbidden();

    $resolveResponse = $this->postJson("/api/v1/worker/revision-comments/{$comment->id}/resolve");
    $resolveResponse->assertForbidden();
});

it('strictly blocks cross-client access to revisions (Anti-IDOR)', function () {
    $revisionA = Revision::create([
        'project_id' => $this->projectA->id,
        'round_number' => 1,
        'title' => 'Alpha Private Feedback',
        'requested_by_user_id' => $this->clientAUser->id,
        'status' => RevisionRoundStatus::Open,
    ]);

    // Client B attempts to list Project A revisions -> Forbidden
    $this->actingAs($this->clientBUser);

    $listResponse = $this->getJson("/api/v1/client/projects/{$this->projectA->id}/revisions");
    $listResponse->assertForbidden();

    $showResponse = $this->getJson("/api/v1/client/revisions/{$revisionA->id}");
    $showResponse->assertForbidden();
});
