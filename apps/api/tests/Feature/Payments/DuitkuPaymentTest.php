<?php

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Clients\Models\Client;
use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\DTOs\PaymentGatewayResult;
use App\Domains\Payments\DTOs\PaymentGatewayStatus;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Users\Enums\UserStatus;
use App\Integrations\Duitku\DuitkuSignature;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\InquirySeeder;
use Database\Seeders\InvoiceSeeder;
use Database\Seeders\PaymentTransactionSeeder;
use Database\Seeders\QuotationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->seed(ClientSeeder::class);
    $this->seed(InquirySeeder::class);
    $this->seed(QuotationSeeder::class);
    $this->seed(InvoiceSeeder::class);
    $this->seed(PaymentTransactionSeeder::class);

    $this->admin = User::create([
        'name' => 'Payment Admin',
        'email' => 'payment.admin@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    $this->worker = User::create([
        'name' => 'Payment Worker',
        'email' => 'payment.worker@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->worker->syncRoles('WORKER');

    // Client A
    $this->clientA = Client::where('email', 'aruna.karya@example.com')->first();
    $this->clientUserA = User::create([
        'name' => 'Client User A',
        'email' => 'clientA.pay@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserA->syncRoles('CLIENT');
    $this->clientA->users()->syncWithoutDetaching([$this->clientUserA->id => ['is_primary' => true]]);

    // Client B
    $this->clientB = Client::where('email', 'dhea.arya@example.com')->first();
    $this->clientUserB = User::create([
        'name' => 'Client User B',
        'email' => 'clientB.pay@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserB->syncRoles('CLIENT');
    $this->clientB->users()->syncWithoutDetaching([$this->clientUserB->id => ['is_primary' => true]]);
});

it('allows client to initiate payment and creates PaymentTransaction with payment URL', function () {
    Http::fake([
        'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry' => Http::response([
            'statusCode' => '00',
            'statusMessage' => 'SUCCESS',
            'paymentUrl' => 'https://sandbox.duitku.com/pay/mock-va-bca-url',
            'reference' => 'DUI-REF-TEST-001',
            'vaNumber' => '8808123456789012',
        ], 200),
    ]);

    $this->actingAs($this->clientUserA);
    $invoice = Invoice::where('client_id', $this->clientA->id)
        ->where('status', InvoiceStatus::Issued)
        ->first();

    $response = $this->postJson("/api/v1/client/invoices/{$invoice->id}/pay", [
        'payment_method' => 'VA_BCA',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.payment_url', 'https://sandbox.duitku.com/pay/mock-va-bca-url')
        ->assertJsonPath('data.va_number', '8808123456789012')
        ->assertJsonPath('data.status', 'PENDING');

    $this->assertDatabaseHas('payment_transactions', [
        'invoice_id' => $invoice->id,
        'status' => 'PENDING',
        'payment_method' => 'VA_BCA',
    ]);
});

it('returns existing pending transaction if still valid', function () {
    $this->actingAs($this->clientUserA);
    $invoice = Invoice::where('client_id', $this->clientA->id)
        ->where('status', InvoiceStatus::Issued)
        ->first();

    $existing = PaymentTransaction::create([
        'invoice_id' => $invoice->id,
        'merchant_order_id' => 'EXISTING-ORDER-123',
        'amount' => $invoice->amount,
        'status' => PaymentStatus::Pending,
        'payment_url' => 'https://sandbox.duitku.com/pay/existing-url',
        'expires_at' => now()->addHour(),
    ]);

    $response = $this->postJson("/api/v1/client/invoices/{$invoice->id}/pay");

    $response->assertOk()
        ->assertJsonPath('data.merchant_order_id', 'EXISTING-ORDER-123')
        ->assertJsonPath('data.payment_url', 'https://sandbox.duitku.com/pay/existing-url');
});

it('processes valid Duitku webhook callback and marks transaction and invoice as PAID', function () {
    $invoice = Invoice::where('client_id', $this->clientA->id)
        ->where('status', InvoiceStatus::Issued)
        ->first();

    $transaction = PaymentTransaction::create([
        'invoice_id' => $invoice->id,
        'merchant_order_id' => 'ORDER-TEST-WEBHOOK-99',
        'amount' => $invoice->amount,
        'status' => PaymentStatus::Pending,
    ]);

    $merchantCode = config('services.duitku.merchant_code', 'D12345');
    $apiKey = config('services.duitku.api_key', 'sandbox_api_key_12345');
    $signature = DuitkuSignature::generateCallbackSignatureHmac($merchantCode, $invoice->amount, $transaction->merchant_order_id, $apiKey);

    $response = $this->postJson('/api/v1/webhooks/duitku', [
        'merchantCode' => $merchantCode,
        'amount' => $invoice->amount,
        'merchantOrderId' => $transaction->merchant_order_id,
        'signature' => $signature,
        'resultCode' => '00',
        'reference' => 'DUI-CALLBACK-REF-123',
        'paymentCode' => 'VA_BCA',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Callback processed successfully');

    expect($transaction->fresh()->status)->toBe(PaymentStatus::Paid);
    expect($transaction->fresh()->paid_at)->not->toBeNull();
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
    expect($invoice->fresh()->paid_amount)->toBe($invoice->amount);
});

it('handles duplicate callback idempotently without double processing', function () {
    $invoice = Invoice::where('invoice_number', 'INV-2026-002')->first();
    $transaction = PaymentTransaction::where('invoice_id', $invoice->id)->first();

    $merchantCode = config('services.duitku.merchant_code', 'D12345');
    $apiKey = config('services.duitku.api_key', 'sandbox_api_key_12345');
    $signature = DuitkuSignature::generateCallbackSignatureHmac($merchantCode, $transaction->amount, $transaction->merchant_order_id, $apiKey);

    $response = $this->postJson('/api/v1/webhooks/duitku', [
        'merchantCode' => $merchantCode,
        'amount' => $transaction->amount,
        'merchantOrderId' => $transaction->merchant_order_id,
        'signature' => $signature,
        'resultCode' => '00',
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Idempotent: Status already updated');
});

it('rejects callback with invalid signature', function () {
    $response = $this->postJson('/api/v1/webhooks/duitku', [
        'merchantCode' => 'D12345',
        'amount' => 1000000,
        'merchantOrderId' => 'SOME-ORDER',
        'signature' => 'invalid_signature_hash',
        'resultCode' => '00',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('message', 'Invalid callback signature.');
});

it('rejects callback with mismatched merchant code', function () {
    $response = $this->postJson('/api/v1/webhooks/duitku', [
        'merchantCode' => 'WRONG_MERCHANT',
        'amount' => 1000000,
        'merchantOrderId' => 'SOME-ORDER',
        'signature' => 'some_signature',
        'resultCode' => '00',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('message', 'Merchant code mismatch.');
});

it('allows admin to list payment transactions with filters', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson('/api/v1/admin/payments?status=PAID');

    $response->assertOk()
        ->assertJsonPath('data.0.status', 'PAID');
});

it('allows admin to perform manual status check against gateway', function () {
    Http::fake([
        'https://sandbox.duitku.com/webapi/api/merchant/transactionStatus' => Http::response([
            'statusCode' => '00',
            'statusMessage' => 'SUCCESS',
            'amount' => 11250000,
            'reference' => 'DUI-CHECK-REF-999',
        ], 200),
    ]);

    $this->actingAs($this->admin);
    $invoice = Invoice::where('invoice_number', 'INV-2026-001')->first();
    $transaction = PaymentTransaction::create([
        'invoice_id' => $invoice->id,
        'merchant_order_id' => 'CHECK-ORDER-STATUS-123',
        'amount' => $invoice->amount,
        'status' => PaymentStatus::Pending,
    ]);

    $response = $this->postJson("/api/v1/admin/payments/{$transaction->id}/check-status");

    $response->assertOk()
        ->assertJsonPath('data.status', 'PAID');

    expect($transaction->fresh()->status)->toBe(PaymentStatus::Paid);
    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
});

it('prevents client A from initiating payment or checking status of client B invoice (Anti-IDOR)', function () {
    $this->actingAs($this->clientUserA);
    $invoiceB = Invoice::where('client_id', $this->clientB->id)->first();

    $response = $this->postJson("/api/v1/client/invoices/{$invoiceB->id}/pay");
    $response->assertForbidden();
});

it('denies worker from accessing payment records', function () {
    $this->actingAs($this->worker);

    $response = $this->getJson('/api/v1/admin/payments');
    $response->assertForbidden();
});
