<?php

use App\Domains\Catalog\Enums\CatalogStatus;
use App\Domains\Catalog\Enums\DpType;
use App\Domains\Catalog\Models\AddOn;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    $this->worker = User::create([
        'name' => 'Worker User',
        'email' => 'worker@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->worker->syncRoles('WORKER');

    $this->client = User::create([
        'name' => 'Client User',
        'email' => 'client@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->client->syncRoles('CLIENT');
});

it('allows guest to view active public services with packages and add-ons', function () {
    $response = $this->getJson('/api/v1/catalog/services');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'public_id',
                    'name',
                    'slug',
                    'status',
                    'packages' => [
                        '*' => ['id', 'name', 'base_price', 'currency', 'default_dp_type'],
                    ],
                ],
            ],
        ]);
});

it('excludes inactive/archived items from public catalog', function () {
    $service = Service::create([
        'name' => 'Secret Inactive Service',
        'slug' => 'secret-inactive',
        'status' => CatalogStatus::Inactive,
    ]);

    Package::create([
        'service_id' => $service->id,
        'name' => 'Inactive Package',
        'base_price' => 1000000,
        'status' => CatalogStatus::Inactive,
    ]);

    $response = $this->getJson('/api/v1/catalog/services');

    $response->assertOk();
    $slugs = collect($response->json('data'))->pluck('slug')->toArray();
    expect($slugs)->not->toContain('secret-inactive');
});

it('allows admin to list and create services', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson('/api/v1/admin/catalog/services', [
        'name' => 'Live Streaming Production',
        'slug' => 'live-streaming',
        'description_internal' => 'Multi-camera broadcast streaming',
        'status' => 'ACTIVE',
        'sort_order' => 4,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Live Streaming Production')
        ->assertJsonPath('data.slug', 'live-streaming');

    $this->assertDatabaseHas('services', [
        'slug' => 'live-streaming',
    ]);
});

it('allows admin to update and delete services', function () {
    $this->actingAs($this->admin);
    $service = Service::where('slug', 'photography')->first();

    $response = $this->putJson("/api/v1/admin/catalog/services/{$service->id}", [
        'name' => 'Photography & Visuals',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Photography & Visuals');

    $delResponse = $this->deleteJson("/api/v1/admin/catalog/services/{$service->id}");
    $delResponse->assertOk();

    $this->assertSoftDeleted('services', ['id' => $service->id]);
});

it('allows admin to create and update packages', function () {
    $this->actingAs($this->admin);
    $service = Service::first();

    $response = $this->postJson('/api/v1/admin/catalog/packages', [
        'service_id' => $service->id,
        'name' => 'Bronze Half-Day',
        'description_internal' => 'Basic half day coverage',
        'base_price' => 3500000,
        'currency' => 'IDR',
        'default_dp_type' => 'PERCENTAGE',
        'default_dp_value' => 50,
        'status' => 'ACTIVE',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Bronze Half-Day')
        ->assertJsonPath('data.base_price', 3500000);

    $packageId = $response->json('data.id');

    $updateResponse = $this->putJson("/api/v1/admin/catalog/packages/{$packageId}", [
        'base_price' => 4000000,
    ]);

    $updateResponse->assertOk()
        ->assertJsonPath('data.base_price', 4000000);
});

it('allows admin to manage add-ons', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson('/api/v1/admin/catalog/add-ons', [
        'name' => 'Teleprompter Operator',
        'price' => 1200000,
        'currency' => 'IDR',
        'status' => 'ACTIVE',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Teleprompter Operator')
        ->assertJsonPath('data.price', 1200000);
});

it('denies unauthenticated request to admin catalog', function () {
    $response = $this->postJson('/api/v1/admin/catalog/services', [
        'name' => 'Hacker Service',
    ]);

    $response->assertUnauthorized();
});

it('denies worker from mutating catalog', function () {
    $this->actingAs($this->worker);

    $response = $this->postJson('/api/v1/admin/catalog/services', [
        'name' => 'Worker Service',
    ]);

    $response->assertForbidden();
});

it('denies client from mutating catalog', function () {
    $this->actingAs($this->client);

    $response = $this->postJson('/api/v1/admin/catalog/services', [
        'name' => 'Client Service',
    ]);

    $response->assertForbidden();
});

it('validates unique slug on service', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson('/api/v1/admin/catalog/services', [
        'name' => 'Photography Duplicate',
        'slug' => 'photography', // already seeded
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['slug']);
});

it('validates package base price and dp type', function () {
    $this->actingAs($this->admin);
    $service = Service::first();

    $response = $this->postJson('/api/v1/admin/catalog/packages', [
        'service_id' => $service->id,
        'name' => 'Invalid Package',
        'base_price' => -500,
        'default_dp_type' => 'INVALID_TYPE',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['base_price', 'default_dp_type']);
});
