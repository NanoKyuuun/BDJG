<?php

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Notifications\Notifications\InvoiceIssuedNotification;
use App\Domains\Notifications\Notifications\ProjectStatusChangedNotification;
use App\Domains\Notifications\Notifications\QuotationSentNotification;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    // User A (Client)
    $this->clientA = Client::create([
        'display_name' => 'Client Alpha',
        'email' => 'alpha@client.com',
        'status' => ClientStatus::Active,
    ]);
    $this->userA = User::create([
        'name' => 'Alpha User',
        'email' => 'alpha@client.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->userA->syncRoles('CLIENT');

    // User B (Client)
    $this->userB = User::create([
        'name' => 'Beta User',
        'email' => 'beta@client.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->userB->syncRoles('CLIENT');

    $this->project = Project::create([
        'client_id' => $this->clientA->id,
        'name' => 'Alpha Brand Film',
        'status' => ProjectStatus::Production,
    ]);
});

it('lists paginated notifications and tracks unread count', function () {
    // Dispatch 2 notifications to User A
    $this->userA->notify(new ProjectStatusChangedNotification($this->project, 'DRAFT', 'PRODUCTION'));

    $this->actingAs($this->userA);

    $response = $this->getJson('/api/v1/notifications');
    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'type', 'title', 'message', 'data', 'is_read', 'created_at'],
        ],
        'unread_count',
        'total_count',
    ]);
    expect($response->json('unread_count'))->toBe(1);
    expect($response->json('total_count'))->toBe(1);
    expect($response->json('data.0.type'))->toBe('PROJECT_STATUS_CHANGED');
});

it('marks a single notification as read', function () {
    $this->userA->notify(new ProjectStatusChangedNotification($this->project, 'DRAFT', 'PRODUCTION'));
    $notification = $this->userA->notifications()->first();

    $this->actingAs($this->userA);

    $readResponse = $this->patchJson("/api/v1/notifications/{$notification->id}/read");
    $readResponse->assertOk();
    $readResponse->assertJsonPath('unread_count', 0);
    $readResponse->assertJsonPath('notification.is_read', true);

    expect($this->userA->fresh()->unreadNotifications()->count())->toBe(0);
});

it('marks all notifications as read in bulk', function () {
    $this->userA->notify(new ProjectStatusChangedNotification($this->project, 'DRAFT', 'PRODUCTION'));
    $this->userA->notify(new ProjectStatusChangedNotification($this->project, 'PRODUCTION', 'POST_PRODUCTION'));

    expect($this->userA->unreadNotifications()->count())->toBe(2);

    $this->actingAs($this->userA);

    $bulkResponse = $this->postJson('/api/v1/notifications/mark-all-read');
    $bulkResponse->assertOk();
    $bulkResponse->assertJsonPath('unread_count', 0);

    expect($this->userA->fresh()->unreadNotifications()->count())->toBe(0);
});

it('strictly blocks user from viewing or modifying another user notifications', function () {
    $this->userA->notify(new ProjectStatusChangedNotification($this->project, 'DRAFT', 'PRODUCTION'));
    $notificationA = $this->userA->notifications()->first();

    // User B tries to read User A's notification -> 404 (scoped to user's notifications)
    $this->actingAs($this->userB);

    $readResponse = $this->patchJson("/api/v1/notifications/{$notificationA->id}/read");
    $readResponse->assertNotFound();

    $deleteResponse = $this->deleteJson("/api/v1/notifications/{$notificationA->id}");
    $deleteResponse->assertNotFound();
});
