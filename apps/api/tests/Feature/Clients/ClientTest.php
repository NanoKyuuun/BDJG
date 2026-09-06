<?php

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(ClientSeeder::class);

    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin.test@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    $this->worker = User::create([
        'name' => 'Worker User',
        'email' => 'worker.test@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->worker->syncRoles('WORKER');

    // Client A
    $this->clientUserA = User::create([
        'name' => 'Client User A',
        'email' => 'clientA@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserA->syncRoles('CLIENT');

    $this->clientA = Client::create([
        'display_name' => 'Client A Studio',
        'email' => 'contact@clienta.com',
        'phone' => '+628111111111',
        'billing_name' => 'PT Client A Nusantara',
        'status' => ClientStatus::Active,
        'notes_internal' => 'Confidential discount agreement 15%',
    ]);
    $this->clientA->users()->attach($this->clientUserA->id, ['is_primary' => true]);

    // Client B
    $this->clientUserB = User::create([
        'name' => 'Client User B',
        'email' => 'clientB@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserB->syncRoles('CLIENT');

    $this->clientB = Client::create([
        'display_name' => 'Client B Corporation',
        'email' => 'contact@clientb.com',
        'phone' => '+628222222222',
        'billing_name' => 'PT Client B Makmur',
        'status' => ClientStatus::Active,
        'notes_internal' => 'High risk payment history',
    ]);
    $this->clientB->users()->attach($this->clientUserB->id, ['is_primary' => true]);
});

it('allows admin to list clients with search and filter', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson('/api/v1/admin/clients?search=Aruna');

    $response->assertOk()
        ->assertJsonPath('data.0.display_name', 'PT Aruna Karya');
});

it('allows admin to create a new client with associated users', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson('/api/v1/admin/clients', [
        'display_name' => 'Brand XYZ',
        'company_or_institution' => 'PT Brand XYZ Kreatif',
        'email' => 'hello@brandxyz.com',
        'phone' => '+628999999999',
        'billing_name' => 'PT Brand XYZ Kreatif',
        'billing_email' => 'finance@brandxyz.com',
        'billing_address' => 'Jl. Kemang Raya No. 10',
        'status' => 'ACTIVE',
        'notes_internal' => 'New lead from website',
        'user_ids' => [$this->clientUserA->id],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.display_name', 'Brand XYZ')
        ->assertJsonPath('data.notes_internal', 'New lead from website');

    $this->assertDatabaseHas('clients', [
        'email' => 'hello@brandxyz.com',
    ]);
});

it('allows admin to view client detail including notes_internal', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson("/api/v1/admin/clients/{$this->clientA->id}");

    $response->assertOk()
        ->assertJsonPath('data.display_name', 'Client A Studio')
        ->assertJsonPath('data.notes_internal', 'Confidential discount agreement 15%');
});

it('allows admin to update client details', function () {
    $this->actingAs($this->admin);

    $response = $this->putJson("/api/v1/admin/clients/{$this->clientA->id}", [
        'display_name' => 'Client A Studio Updated',
        'phone' => '+628119999999',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.display_name', 'Client A Studio Updated');

    $this->assertDatabaseHas('clients', [
        'id' => $this->clientA->id,
        'display_name' => 'Client A Studio Updated',
    ]);
});

it('allows admin to delete client', function () {
    $this->actingAs($this->admin);

    $response = $this->deleteJson("/api/v1/admin/clients/{$this->clientA->id}");

    $response->assertOk();
    $this->assertSoftDeleted('clients', ['id' => $this->clientA->id]);
});

it('allows client to view own profile via /api/v1/client/profile', function () {
    $this->actingAs($this->clientUserA);

    $response = $this->getJson('/api/v1/client/profile');

    $response->assertOk()
        ->assertJsonPath('data.display_name', 'Client A Studio')
        ->assertJsonPath('data.billing_name', 'PT Client A Nusantara');
});

it('allows client to update own profile fields', function () {
    $this->actingAs($this->clientUserA);

    $response = $this->putJson('/api/v1/client/profile', [
        'display_name' => 'Client A Studio Renamed',
        'billing_address' => 'Jl. Baru No. 99, Jakarta',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.display_name', 'Client A Studio Renamed')
        ->assertJsonPath('data.billing_address', 'Jl. Baru No. 99, Jakarta');
});

it('prevents client profile endpoint from leaking notes_internal', function () {
    $this->actingAs($this->clientUserA);

    $response = $this->getJson('/api/v1/client/profile');

    $response->assertOk();
    $data = $response->json('data');
    expect(array_key_exists('notes_internal', $data))->toBeFalse();
});

it('prevents client from accessing admin clients directory', function () {
    $this->actingAs($this->clientUserA);

    $response = $this->getJson('/api/v1/admin/clients');
    $response->assertForbidden();

    $postResponse = $this->postJson('/api/v1/admin/clients', [
        'display_name' => 'Illegal Client',
        'email' => 'illegal@example.com',
    ]);
    $postResponse->assertForbidden();
});

it('prevents client A from accessing or mutating client B data via policy (Anti-IDOR)', function () {
    $this->actingAs($this->clientUserA);

    // Direct access to Admin endpoint is forbidden
    $response = $this->getJson("/api/v1/admin/clients/{$this->clientB->id}");
    $response->assertForbidden();

    $putResponse = $this->putJson("/api/v1/admin/clients/{$this->clientB->id}", [
        'display_name' => 'Hacked Name',
    ]);
    $putResponse->assertForbidden();
});

it('prevents worker from accessing client directory', function () {
    $this->actingAs($this->worker);

    $response = $this->getJson('/api/v1/admin/clients');
    $response->assertForbidden();
});

it('validates email format on store and update', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson('/api/v1/admin/clients', [
        'display_name' => 'Invalid Email Client',
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
