<?php

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Delivery\Enums\DeliveryPackageStatus;
use App\Domains\Delivery\Models\DeliveryPackage;
use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\ProcessingStatus;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    Storage::fake('media');

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

    // Project A & B
    $this->projectA = Project::create([
        'client_id' => $this->clientA->id,
        'name' => 'Alpha Master Production',
        'status' => ProjectStatus::PostProduction,
        'created_by' => $this->admin->id,
    ]);

    $this->projectB = Project::create([
        'client_id' => $this->clientB->id,
        'name' => 'Beta Project',
        'status' => ProjectStatus::PostProduction,
        'created_by' => $this->admin->id,
    ]);

    // Final Master Media Assets on Project A
    $this->masterVideo = MediaAsset::create([
        'project_id' => $this->projectA->id,
        'uploaded_by_user_id' => $this->admin->id,
        'filename' => 'wedding_master_4k.mp4',
        'original_name' => 'Wedding Master 4K Final.mp4',
        'storage_key' => "projects/{$this->projectA->id}/final/wedding_master_4k.mp4",
        'disk' => 'media',
        'mime_type' => 'video/mp4',
        'size_bytes' => 850000000,
        'category' => MediaCategory::FinalMaster,
        'visibility' => FileVisibility::Internal,
        'version_number' => 1,
        'processing_status' => ProcessingStatus::Ready,
    ]);
    Storage::disk('media')->put($this->masterVideo->storage_key, 'MASTER_VIDEO_4K_BINARY');

    $this->masterPhotos = MediaAsset::create([
        'project_id' => $this->projectA->id,
        'uploaded_by_user_id' => $this->admin->id,
        'filename' => 'photo_selection_edited.zip',
        'original_name' => 'High Resolution Edited Photos.zip',
        'storage_key' => "projects/{$this->projectA->id}/final/photo_selection_edited.zip",
        'disk' => 'media',
        'mime_type' => 'application/zip',
        'size_bytes' => 450000000,
        'category' => MediaCategory::PhotoSelection,
        'visibility' => FileVisibility::Internal,
        'version_number' => 1,
        'processing_status' => ProcessingStatus::Ready,
    ]);
    Storage::disk('media')->put($this->masterPhotos->storage_key, 'PHOTO_SELECTION_ZIP_BINARY');
});

it('allows admin to package final deliverables and client to download package', function () {
    // 1. Admin creates final delivery package
    $this->actingAs($this->admin);

    $createResponse = $this->postJson("/api/v1/admin/projects/{$this->projectA->id}/deliveries", [
        'media_asset_ids' => [$this->masterVideo->id, $this->masterPhotos->id],
        'title' => 'Official Wedding 4K Master & High-Res Photos',
        'notes' => 'Complete high-resolution collection. Download link valid for 30 days.',
        'expiry_days' => 30,
    ]);

    $createResponse->assertCreated();
    $createResponse->assertJsonPath('data.title', 'Official Wedding 4K Master & High-Res Photos');
    $createResponse->assertJsonPath('data.status', 'READY');
    $createResponse->assertJsonPath('data.file_count', 2);
    $createResponse->assertJsonPath('data.total_size_bytes', 1300000000);

    $packageId = $createResponse->json('data.id');
    $package = DeliveryPackage::find($packageId);
    expect($package)->not->toBeNull();

    // Verify media assets visibility updated to FINAL_RELEASED
    expect($this->masterVideo->fresh()->visibility)->toBe(FileVisibility::FinalReleased);
    expect($this->masterPhotos->fresh()->visibility)->toBe(FileVisibility::FinalReleased);

    // 2. Client A views available deliveries
    $this->actingAs($this->clientAUser);

    $listResponse = $this->getJson("/api/v1/client/projects/{$this->projectA->id}/deliveries");
    $listResponse->assertOk();
    expect(count($listResponse->json('data')))->toBe(1);

    // 3. Client A requests high-speed signed download URL
    $downloadResponse = $this->getJson("/api/v1/client/deliveries/{$package->id}/download-url");
    $downloadResponse->assertOk();
    $downloadResponse->assertJsonStructure([
        'download_url',
        'package_title',
        'file_count',
        'total_size_bytes',
        'download_count',
        'expires_at',
    ]);
    expect($downloadResponse->json('download_count'))->toBe(1);
    expect($package->fresh()->status)->toBe(DeliveryPackageStatus::Downloaded);
});

it('strictly blocks cross-client access to final delivery downloads (Anti-IDOR)', function () {
    // Admin creates package on Project A
    $packageA = DeliveryPackage::create([
        'project_id' => $this->projectA->id,
        'title' => 'Alpha Private Master Handover',
        'status' => DeliveryPackageStatus::Ready,
        'storage_key' => $this->masterVideo->storage_key,
        'total_size_bytes' => 850000000,
        'file_count' => 1,
        'download_count' => 0,
        'expires_at' => now()->addDays(30),
    ]);
    $packageA->items()->create(['media_asset_id' => $this->masterVideo->id]);

    // Client B attempts to list Project A deliveries -> 403
    $this->actingAs($this->clientBUser);

    $listResponse = $this->getJson("/api/v1/client/projects/{$this->projectA->id}/deliveries");
    $listResponse->assertForbidden();

    // Client B attempts to download package A -> 403
    $downloadResponse = $this->getJson("/api/v1/client/deliveries/{$packageA->id}/download-url");
    $downloadResponse->assertForbidden();
});
