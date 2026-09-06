<?php

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Clients\Models\Client;
use App\Domains\Clients\Models\ClientInvitation;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\InquirySeeder;
use Database\Seeders\InvoiceSeeder;
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
    $this->seed(InvoiceSeeder::class);

    $this->admin = User::create([
        'name' => 'Finance Admin',
        'email' => 'finance.admin@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    $this->worker = User::create([
        'name' => 'Worker User',
        'email' => 'worker.finance@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->worker->syncRoles('WORKER');

    // Client A setup
    $this->clientA = Client::where('email', 'aruna.karya@example.com')->first();
    $this->clientUserA = User::create([
        'name' => 'Client User A',
        'email' => 'clientA.finance@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserA->syncRoles('CLIENT');
    $this->clientA->users()->syncWithoutDetaching([$this->clientUserA->id => ['is_primary' => true]]);

    // Client B setup
    $this->clientB = Client::where('email', 'dhea.arya@example.com')->first();
    $this->clientUserB = User::create([
        'name' => 'Client User B',
        'email' => 'clientB.finance@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserB->syncRoles('CLIENT');
    $this->clientB->users()->syncWithoutDetaching([$this->clientUserB->id => ['is_primary' => true]]);
});

it('allows admin to list invoices with filters and search', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson('/api/v1/admin/invoices?search=INV-2026-001');

    $response->assertOk()
        ->assertJsonPath('data.0.invoice_number', 'INV-2026-001');
});

it('allows admin to create a manual invoice with items', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson('/api/v1/admin/invoices', [
        'client_id' => $this->clientA->id,
        'invoice_type' => 'ADDITIONAL',
        'due_at' => now()->addDays(10)->toDateString(),
        'terms' => 'Net 10 Days.',
        'notes_internal' => 'Extra 2 shooting hours requested on set.',
        'items' => [
            [
                'name' => 'Extra Shooting Hours (2 hrs)',
                'description' => 'Overtime for crew and gear',
                'quantity' => 2,
                'unit_price' => 1500000,
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.amount', 3000000)
        ->assertJsonPath('data.invoice_type', 'ADDITIONAL')
        ->assertJsonPath('data.status', 'DRAFT');
});

it('allows admin to view invoice details', function () {
    $this->actingAs($this->admin);
    $invoice = Invoice::first();

    $response = $this->getJson("/api/v1/admin/invoices/{$invoice->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $invoice->id)
        ->assertJsonPath('data.invoice_number', $invoice->invoice_number);
});

it('allows admin to issue an invoice and sets issued_at and due_at', function () {
    $this->actingAs($this->admin);
    $invoice = Invoice::create([
        'client_id' => $this->clientA->id,
        'invoice_type' => InvoiceType::Additional,
        'amount' => 5000000,
        'status' => InvoiceStatus::Draft,
    ]);

    $response = $this->postJson("/api/v1/admin/invoices/{$invoice->id}/issue", [
        'due_at' => now()->addDays(5)->toDateString(),
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'ISSUED');

    expect($invoice->fresh()->issued_at)->not->toBeNull();
});

it('allows admin to void an unpaid invoice', function () {
    $this->actingAs($this->admin);
    $invoice = Invoice::where('invoice_number', 'INV-2026-001')->first();

    $response = $this->postJson("/api/v1/admin/invoices/{$invoice->id}/void");

    $response->assertOk()
        ->assertJsonPath('data.status', 'VOID');

    expect($invoice->fresh()->voided_at)->not->toBeNull();
});

it('prevents voiding a PAID invoice', function () {
    $this->actingAs($this->admin);
    $paidInvoice = Invoice::where('status', InvoiceStatus::Paid)->first();

    $response = $this->postJson("/api/v1/admin/invoices/{$paidInvoice->id}/void");

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Cannot void an invoice that is already marked as PAID.');
});

it('allows admin to generate DP invoice from an accepted quotation', function () {
    $this->actingAs($this->admin);
    $quotation = Quotation::where('status', QuotationStatus::Accepted)->first();

    $response = $this->postJson("/api/v1/admin/quotations/{$quotation->id}/generate-dp-invoice");

    $response->assertOk()
        ->assertJsonPath('data.invoice_type', 'DP');
});

it('allows client to list own invoices via client portal', function () {
    $this->actingAs($this->clientUserA);
    $invoice = Invoice::where('client_id', $this->clientA->id)->first();

    $response = $this->getJson('/api/v1/client/invoices');

    $response->assertOk()
        ->assertJsonPath('data.0.invoice_number', $invoice->invoice_number);
});

it('allows client to view own invoice detail', function () {
    $this->actingAs($this->clientUserA);
    $invoice = Invoice::where('client_id', $this->clientA->id)->first();

    $response = $this->getJson("/api/v1/client/invoices/{$invoice->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $invoice->id)
        ->assertJsonPath('data.amount', $invoice->amount);
});

it('prevents client portal from leaking notes_internal (Anti-leakage)', function () {
    $this->actingAs($this->clientUserA);
    $invoice = Invoice::where('client_id', $this->clientA->id)->first();

    $response = $this->getJson("/api/v1/client/invoices/{$invoice->id}");

    $response->assertOk();
    $data = $response->json('data');
    expect(array_key_exists('notes_internal', $data))->toBeFalse();
});

it('prevents client A from viewing client B invoice (Anti-IDOR)', function () {
    $this->actingAs($this->clientUserA);
    $invoiceB = Invoice::where('client_id', $this->clientB->id)->first();

    $response = $this->getJson("/api/v1/client/invoices/{$invoiceB->id}");
    $response->assertForbidden();
});

it('allows admin to invite client user and user can activate account via token', function () {
    $this->actingAs($this->admin);

    // 1. Admin sends invitation
    $inviteResponse = $this->postJson("/api/v1/admin/clients/{$this->clientA->id}/invite", [
        'email' => 'new.pic@arunakarya.co.id',
    ]);

    $inviteResponse->assertCreated();
    $token = $inviteResponse->json('data.token');
    expect($token)->not->toBeEmpty();

    // 2. Public checks invitation validity
    $checkResponse = $this->getJson("/api/v1/auth/invitations/{$token}");
    $checkResponse->assertOk()
        ->assertJsonPath('data.is_valid', true)
        ->assertJsonPath('data.client_name', $this->clientA->display_name);

    // 3. Public accepts invitation and sets password
    $acceptResponse = $this->postJson("/api/v1/auth/invitations/{$token}/accept", [
        'name' => 'Dimas Wicaksono',
        'password' => 'secretPassword123!',
        'password_confirmation' => 'secretPassword123!',
    ]);

    $acceptResponse->assertOk()
        ->assertJsonPath('message', 'Account successfully activated.');

    $user = User::where('email', 'new.pic@arunakarya.co.id')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('CLIENT'))->toBeTrue();
    expect($this->clientA->hasUser($user))->toBeTrue();
});

it('denies worker from accessing invoices', function () {
    $this->actingAs($this->worker);

    $response = $this->getJson('/api/v1/admin/invoices');
    $response->assertForbidden();

    $clientPortalResponse = $this->getJson('/api/v1/client/invoices');
    $clientPortalResponse->assertOk()
        ->assertJsonCount(0, 'data');
});
