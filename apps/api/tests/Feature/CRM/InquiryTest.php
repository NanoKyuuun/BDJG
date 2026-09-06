<?php

use App\Domains\Catalog\Models\Service;
use App\Domains\Clients\Models\Client;
use App\Domains\CRM\Enums\InquiryStatus;
use App\Domains\CRM\Models\Inquiry;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\InquirySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(ClientSeeder::class);
    $this->seed(InquirySeeder::class);

    $this->admin = User::create([
        'name' => 'CRM Admin',
        'email' => 'crm.admin@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    $this->worker = User::create([
        'name' => 'Worker User',
        'email' => 'worker.crm@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->worker->syncRoles('WORKER');

    $this->clientUser = User::create([
        'name' => 'Client User',
        'email' => 'client.crm@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUser->syncRoles('CLIENT');
});

it('allows guest to submit public lead form', function () {
    $service = Service::first();

    $response = $this->postJson('/api/v1/inquiries', [
        'client_name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'phone' => '+628123456789',
        'company_or_institution' => 'Acme Corp',
        'service_id' => $service->id,
        'preferred_date' => now()->addWeeks(2)->toDateString(),
        'project_brief' => 'Brand video shoot for Q4 product launch.',
        'estimated_budget' => 20000000,
        'source' => 'WEBSITE',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.client_name', 'John Doe')
        ->assertJsonPath('data.status', 'NEW');

    $this->assertDatabaseHas('inquiries', [
        'email' => 'johndoe@example.com',
        'status' => 'NEW',
    ]);
});

it('allows admin to list inquiries with status and search filters', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson('/api/v1/admin/inquiries?status=NEW');

    $response->assertOk()
        ->assertJsonPath('data.0.status', 'NEW');

    $searchResponse = $this->getJson('/api/v1/admin/inquiries?search=Telkom');
    $searchResponse->assertOk()
        ->assertJsonPath('data.0.company_or_institution', 'PT Telkom Digital');
});

it('allows admin to create a manual inquiry', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson('/api/v1/admin/inquiries', [
        'client_name' => 'Jane Smith',
        'email' => 'janesmith@example.com',
        'phone' => '+628111222333',
        'project_brief' => 'Manual lead entered by admin from walk-in consultation.',
        'status' => 'CONTACTED',
        'assigned_admin_id' => $this->admin->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.client_name', 'Jane Smith')
        ->assertJsonPath('data.status', 'CONTACTED');
});

it('allows admin to view inquiry details with relations', function () {
    $this->actingAs($this->admin);
    $inquiry = Inquiry::first();

    $response = $this->getJson("/api/v1/admin/inquiries/{$inquiry->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $inquiry->id)
        ->assertJsonPath('data.email', $inquiry->email);
});

it('allows admin to update inquiry details', function () {
    $this->actingAs($this->admin);
    $inquiry = Inquiry::first();

    $response = $this->putJson("/api/v1/admin/inquiries/{$inquiry->id}", [
        'location' => 'Bandung Creative Hub',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.location', 'Bandung Creative Hub');
});

it('enforces canonical state transitions', function () {
    $this->actingAs($this->admin);
    $inquiry = Inquiry::where('status', InquiryStatus::New)->first();

    // 1. Transition NEW -> CONTACTED (Valid)
    $res1 = $this->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", [
        'status' => 'CONTACTED',
    ]);
    $res1->assertOk()
        ->assertJsonPath('data.status', 'CONTACTED');

    // 2. Transition CONTACTED -> QUALIFIED (Valid)
    $res2 = $this->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", [
        'status' => 'QUALIFIED',
    ]);
    $res2->assertOk()
        ->assertJsonPath('data.status', 'QUALIFIED');

    // 3. Transition QUALIFIED -> QUOTATION (Valid)
    $res3 = $this->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", [
        'status' => 'QUOTATION',
    ]);
    $res3->assertOk()
        ->assertJsonPath('data.status', 'QUOTATION');

    // 4. Transition QUOTATION -> WON (Valid)
    $res4 = $this->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", [
        'status' => 'WON',
    ]);
    $res4->assertOk()
        ->assertJsonPath('data.status', 'WON');
});

it('rejects invalid status transitions', function () {
    $this->actingAs($this->admin);
    $inquiry = Inquiry::where('status', InquiryStatus::New)->first();

    // NEW cannot jump directly to WON
    $response = $this->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", [
        'status' => 'WON',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Invalid status transition from NEW to WON.');
});

it('requires lost_reason when status is changed to LOST', function () {
    $this->actingAs($this->admin);
    $inquiry = Inquiry::where('status', InquiryStatus::New)->first();

    $failResponse = $this->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", [
        'status' => 'LOST',
    ]);
    $failResponse->assertStatus(422)
        ->assertJsonValidationErrors(['lost_reason']);

    $successResponse = $this->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/status", [
        'status' => 'LOST',
        'lost_reason' => 'Client chose another studio because of schedule conflict.',
    ]);
    $successResponse->assertOk()
        ->assertJsonPath('data.status', 'LOST')
        ->assertJsonPath('data.lost_reason', 'Client chose another studio because of schedule conflict.');
});

it('allows admin to assign inquiry to an admin PIC', function () {
    $this->actingAs($this->admin);
    $inquiry = Inquiry::first();

    $response = $this->patchJson("/api/v1/admin/inquiries/{$inquiry->id}/assign", [
        'assigned_admin_id' => $this->admin->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.assigned_admin_id', $this->admin->id);
});

it('allows admin to convert inquiry into a new or existing Client', function () {
    $this->actingAs($this->admin);

    $inquiry = Inquiry::create([
        'client_name' => 'Brand Megantara',
        'company_or_institution' => 'PT Megantara Visual',
        'email' => 'contact@megantara.co.id',
        'phone' => '+6287711223344',
        'project_brief' => 'Music video production.',
        'status' => InquiryStatus::Qualified,
    ]);

    $response = $this->postJson("/api/v1/admin/inquiries/{$inquiry->id}/convert-to-client");

    $response->assertCreated()
        ->assertJsonPath('data.display_name', 'Brand Megantara')
        ->assertJsonPath('data.email', 'contact@megantara.co.id');

    $this->assertDatabaseHas('clients', [
        'email' => 'contact@megantara.co.id',
    ]);

    expect($inquiry->fresh()->client_id)->not->toBeNull();
});

it('allows admin to soft delete inquiry', function () {
    $this->actingAs($this->admin);
    $inquiry = Inquiry::first();

    $response = $this->deleteJson("/api/v1/admin/inquiries/{$inquiry->id}");

    $response->assertOk();
    $this->assertSoftDeleted('inquiries', ['id' => $inquiry->id]);
});

it('denies worker and client from accessing admin CRM endpoints', function () {
    $this->actingAs($this->worker);
    $response = $this->getJson('/api/v1/admin/inquiries');
    $response->assertForbidden();

    $this->actingAs($this->clientUser);
    $clientResponse = $this->getJson('/api/v1/admin/inquiries');
    $clientResponse->assertForbidden();
});
