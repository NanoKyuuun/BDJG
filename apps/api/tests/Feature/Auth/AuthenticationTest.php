<?php

use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('allows a valid user to login', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'status' => UserStatus::Active,
    ]);

    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertOk();
    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'status' => UserStatus::Active,
    ]);

    $response = $this->postJson('/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $this->assertGuest();
});

it('prevents suspended user from authenticating', function () {
    User::create([
        'name' => 'Suspended User',
        'email' => 'suspended@example.com',
        'password' => Hash::make('password'),
        'status' => UserStatus::Suspended,
    ]);

    $response = $this->postJson('/login', [
        'email' => 'suspended@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422);
    $this->assertGuest();
});

it('prevents disabled user from authenticating', function () {
    User::create([
        'name' => 'Disabled User',
        'email' => 'disabled@example.com',
        'password' => Hash::make('password'),
        'status' => UserStatus::Disabled,
    ]);

    $response = $this->postJson('/login', [
        'email' => 'disabled@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422);
    $this->assertGuest();
});

it('allows a user to logout', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'status' => UserStatus::Active,
    ]);

    $this->actingAs($user);

    $response = $this->postJson('/logout');

    $response->assertSuccessful();
    $this->assertGuest();
});

it('rejects unauthenticated api request', function () {
    $response = $this->getJson('/api/v1/me');

    $response->assertUnauthorized();
});

it('returns current user from /api/v1/me', function () {
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'status' => UserStatus::Active,
    ]);

    $this->actingAs($user);

    $response = $this->getJson('/api/v1/me');

    $response->assertOk()
        ->assertJsonPath('data.email', 'test@example.com')
        ->assertJsonPath('data.name', 'Test User');
});

it('returns health check', function () {
    $response = $this->getJson('/api/health');

    $response->assertOk()
        ->assertJson(['status' => 'ok']);
});
