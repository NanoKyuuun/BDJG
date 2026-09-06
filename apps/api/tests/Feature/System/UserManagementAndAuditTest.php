<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->owner = User::create([
        'name' => 'Owner Admin',
        'email' => 'owner.system@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->owner->syncRoles('OWNER');

    $this->clientUser = User::create([
        'name' => 'Client System',
        'email' => 'client.system@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUser->syncRoles('CLIENT');
});

it('allows owner to list users with roles', function () {
    $this->actingAs($this->owner);

    $response = $this->getJson('/api/v1/admin/system/users');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'status', 'roles'],
            ],
        ]);
});

it('allows owner to create a new admin user and logs audit', function () {
    $this->actingAs($this->owner);

    $response = $this->postJson('/api/v1/admin/system/users', [
        'name' => 'New Studio Admin',
        'email' => 'new.admin@bdjg.studio',
        'password' => 'SecurePass123!',
        'roles' => ['ADMIN'],
        'status' => 'ACTIVE',
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('users', ['email' => 'new.admin@bdjg.studio']);
    $this->assertDatabaseHas('audit_logs', ['action' => 'USER_CREATED']);
});

it('allows owner to suspend a user status and logs audit', function () {
    $this->actingAs($this->owner);

    $response = $this->patchJson("/api/v1/admin/system/users/{$this->clientUser->id}/status", [
        'status' => 'SUSPENDED',
    ]);

    $response->assertOk();
    expect($this->clientUser->fresh()->status)->toBe(UserStatus::Suspended);
    $this->assertDatabaseHas('audit_logs', ['action' => 'USER_STATUS_CHANGED']);
});

it('allows owner to view audit activity stream', function () {
    $this->actingAs($this->owner);

    AuditLog::create([
        'user_id' => $this->owner->id,
        'user_email' => $this->owner->email,
        'user_name' => $this->owner->name,
        'action' => 'SYSTEM_CONFIG_UPDATED',
        'description' => 'Updated system settings',
    ]);

    $response = $this->getJson('/api/v1/admin/system/activity');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'user_email', 'action', 'description', 'created_at'],
            ],
        ]);
});

it('denies non-admin client from managing users or viewing audit logs', function () {
    $this->actingAs($this->clientUser);

    $this->getJson('/api/v1/admin/system/users')->assertForbidden();
    $this->getJson('/api/v1/admin/system/activity')->assertForbidden();
});
