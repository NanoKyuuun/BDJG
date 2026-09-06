<?php

use App\Domains\Catalog\Enums\DpType;
use App\Domains\Clients\Models\Client;
use App\Domains\Commercial\Enums\QuotationItemType;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\CRM\Enums\InquiryStatus;
use App\Domains\CRM\Models\Inquiry;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\InquirySeeder;
use Database\Seeders\QuotationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(ClientSeeder::class);
    $this->seed(InquirySeeder::class);
    $this->seed(QuotationSeeder::class);

    $this->admin = User::create([
        'name' => 'Commercial Admin',
        'email' => 'sales.admin@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    $this->worker = User::create([
        'name' => 'Worker User',
        'email' => 'worker.sales@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->worker->syncRoles('WORKER');

    // Client A setup
    $this->clientA = Client::where('email', 'aruna.karya@example.com')->first();
    $this->clientUserA = User::create([
        'name' => 'Client User A',
        'email' => 'clientA.quotation@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserA->syncRoles('CLIENT');
    $this->clientA->users()->syncWithoutDetaching([$this->clientUserA->id => ['is_primary' => true]]);

    // Client B setup
    $this->clientB = Client::where('email', 'dhea.arya@example.com')->first();
    $this->clientUserB = User::create([
        'name' => 'Client User B',
        'email' => 'clientb.quotation@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserB->syncRoles('CLIENT');
    $this->clientB->users()->syncWithoutDetaching([$this->clientUserB->id => ['is_primary' => true]]);
});

it('allows admin to list quotations with filters and search', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson('/api/v1/admin/quotations?search=QT-2026-001');

    $response->assertOk()
        ->assertJsonPath('data.0.quotation_number', 'QT-2026-001');
});

it('allows admin to create a new quotation with Version 1 and items and calculates financial math correctly', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson('/api/v1/admin/quotations', [
        'client_id' => $this->clientA->id,
        'project_name' => 'Music Video Cinematic 4K',
        'service_name_snapshot' => 'Videography & Film Production',
        'package_name_snapshot' => 'Commercial Video Production Standard',
        'dp_type' => 'PERCENTAGE',
        'dp_value' => 50.00,
        'discount' => 1000000,
        'tax' => 0,
        'notes_internal' => 'Internal margin calculated at 42%',
        'items' => [
            [
                'type' => 'PACKAGE',
                'name' => 'Commercial Video Production Standard',
                'description' => '2 Shooting days 4K',
                'quantity' => 1,
                'unit_price' => 20000000,
            ],
            [
                'type' => 'ADD_ON',
                'name' => 'Lighting Rig Gaffer Package',
                'quantity' => 1,
                'unit_price' => 3000000,
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.current_version.subtotal', 23000000)
        ->assertJsonPath('data.current_version.discount', 1000000)
        ->assertJsonPath('data.current_version.grand_total', 22000000)
        ->assertJsonPath('data.current_version.dp_amount', 11000000)
        ->assertJsonPath('data.current_version.remaining_amount', 11000000)
        ->assertJsonPath('data.status', 'DRAFT');
});

it('allows admin to view quotation detail with all versions', function () {
    $this->actingAs($this->admin);
    $quotation = Quotation::first();

    $response = $this->getJson("/api/v1/admin/quotations/{$quotation->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $quotation->id)
        ->assertJsonPath('data.quotation_number', $quotation->quotation_number);
});

it('allows admin to create a revision (Version 2) when revision is requested', function () {
    $this->actingAs($this->admin);
    $quotation = Quotation::where('quotation_number', 'QT-2026-001')->first();

    $response = $this->postJson("/api/v1/admin/quotations/{$quotation->id}/versions", [
        'project_name' => 'Corporate Profile Video V2 (Revised)',
        'dp_type' => 'PERCENTAGE',
        'dp_value' => 50.00,
        'discount' => 3000000,
        'revision_notes' => 'Reduced discount as per owner approval.',
        'items' => [
            [
                'type' => 'PACKAGE',
                'name' => 'Commercial Video Production Standard',
                'quantity' => 1,
                'unit_price' => 20000000,
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.current_version.version_number', 2)
        ->assertJsonPath('data.current_version.grand_total', 17000000)
        ->assertJsonPath('data.status', 'DRAFT');

    expect($quotation->fresh()->versions()->count())->toBe(2);
});

it('allows admin to send quotation to client', function () {
    $this->actingAs($this->admin);
    $quotation = Quotation::where('status', QuotationStatus::Draft)->first()
        ?? Quotation::first();
    $quotation->update(['status' => QuotationStatus::Draft]);

    $response = $this->postJson("/api/v1/admin/quotations/{$quotation->id}/send", [
        'expires_at' => now()->addDays(20)->toDateString(),
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'SENT');

    expect($quotation->fresh()->sent_at)->not->toBeNull();
});

it('allows client to view own quotations in client portal and marks viewed_at', function () {
    $this->actingAs($this->clientUserA);
    $quotation = Quotation::where('client_id', $this->clientA->id)
        ->where('status', QuotationStatus::Sent)
        ->first();

    // List own quotations
    $listResponse = $this->getJson('/api/v1/client/quotations');
    $listResponse->assertOk()
        ->assertJsonPath('data.0.quotation_number', $quotation->quotation_number);

    // View detail (triggers VIEWED status)
    $detailResponse = $this->getJson("/api/v1/client/quotations/{$quotation->id}");
    $detailResponse->assertOk()
        ->assertJsonPath('data.status', 'VIEWED');

    expect($quotation->fresh()->viewed_at)->not->toBeNull();
});

it('allows client to accept quotation and sets accepted_version_id and updates linked inquiry to WON', function () {
    $this->actingAs($this->clientUserA);
    $quotation = Quotation::where('client_id', $this->clientA->id)->first();
    $quotation->update(['status' => QuotationStatus::Sent]);

    $inquiry = Inquiry::create([
        'client_name' => $this->clientA->display_name,
        'email' => $this->clientA->email,
        'project_brief' => 'Corporate video brief',
        'status' => InquiryStatus::Quotation,
    ]);
    $quotation->update(['inquiry_id' => $inquiry->id]);

    $response = $this->postJson("/api/v1/client/quotations/{$quotation->id}/accept");

    $response->assertOk()
        ->assertJsonPath('data.status', 'ACCEPTED');

    $freshQ = $quotation->fresh();
    expect($freshQ->accepted_version_id)->toBe($freshQ->current_version_id);
    expect($inquiry->fresh()->status)->toBe(InquiryStatus::Won);
});

it('allows client to request revision with notes', function () {
    $this->actingAs($this->clientUserA);
    $quotation = Quotation::where('client_id', $this->clientA->id)->first();
    $quotation->update(['status' => QuotationStatus::Sent]);

    $response = $this->postJson("/api/v1/client/quotations/{$quotation->id}/request-revision", [
        'notes' => 'Please remove drone add-on and recalculate total.',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'REVISION_REQUESTED')
        ->assertJsonPath('data.revision_request_notes', 'Please remove drone add-on and recalculate total.');
});

it('allows client to decline quotation with reason and marks linked inquiry to LOST', function () {
    $this->actingAs($this->clientUserA);
    $quotation = Quotation::where('client_id', $this->clientA->id)->first();
    $quotation->update(['status' => QuotationStatus::Sent]);

    $inquiry = Inquiry::create([
        'client_name' => $this->clientA->display_name,
        'email' => $this->clientA->email,
        'project_brief' => 'Brief',
        'status' => InquiryStatus::Quotation,
    ]);
    $quotation->update(['inquiry_id' => $inquiry->id]);

    $response = $this->postJson("/api/v1/client/quotations/{$quotation->id}/decline", [
        'reason' => 'Project postponed to next fiscal year.',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'DECLINED')
        ->assertJsonPath('data.decline_reason', 'Project postponed to next fiscal year.');

    expect($inquiry->fresh()->status)->toBe(InquiryStatus::Lost);
});

it('prevents client portal response from leaking notes_internal', function () {
    $this->actingAs($this->clientUserA);
    $quotation = Quotation::where('client_id', $this->clientA->id)->first();
    $quotation->update(['status' => QuotationStatus::Sent, 'notes_internal' => 'Top secret margin data']);

    $response = $this->getJson("/api/v1/client/quotations/{$quotation->id}");

    $response->assertOk();
    $data = $response->json('data');
    expect(array_key_exists('notes_internal', $data))->toBeFalse();
});

it('prevents client A from viewing or acting on client B quotation (Anti-IDOR)', function () {
    $this->actingAs($this->clientUserA);
    $quotationB = Quotation::where('client_id', $this->clientB->id)->first();

    $response = $this->getJson("/api/v1/client/quotations/{$quotationB->id}");
    $response->assertForbidden();

    $acceptResponse = $this->postJson("/api/v1/client/quotations/{$quotationB->id}/accept");
    $acceptResponse->assertForbidden();
});

it('denies worker from accessing quotations', function () {
    $this->actingAs($this->worker);

    $response = $this->getJson('/api/v1/admin/quotations');
    $response->assertForbidden();

    $clientPortalResponse = $this->getJson('/api/v1/client/quotations');
    $clientPortalResponse->assertOk()
        ->assertJsonCount(0, 'data');
});
