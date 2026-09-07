<?php

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Catalog\Enums\CatalogStatus;
use App\Domains\Catalog\Models\Package;
use App\Domains\Catalog\Models\Service;
use App\Domains\Clients\Models\Client;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Commercial\Models\QuotationVersion;
use App\Domains\Orders\Enums\BriefCategory;
use App\Domains\Orders\Enums\ServiceOrderReviewType;
use App\Domains\Orders\Enums\ServiceOrderSource;
use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Orders\Models\ServiceOrder;
use App\Domains\Payments\Actions\ProcessPaymentProviderEventAction;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    // Create Admin
    $this->adminUser = User::create([
        'name' => 'Order Admin',
        'email' => 'admin.orders@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->adminUser->syncRoles('ADMIN');

    // Create Client A
    $this->clientUserA = User::create([
        'name' => 'Client A User',
        'email' => 'clienta.orders@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserA->syncRoles('CLIENT');
    $this->clientA = Client::create([
        'name' => 'Client A Studio',
        'email' => 'clienta.orders@example.com',
        'phone' => '08123456789',
        'status' => 'ACTIVE',
    ]);
    $this->clientA->users()->syncWithoutDetaching([$this->clientUserA->id => ['is_primary' => true]]);

    // Create Client B
    $this->clientUserB = User::create([
        'name' => 'Client B User',
        'email' => 'clientb.orders@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUserB->syncRoles('CLIENT');
    $this->clientB = Client::create([
        'name' => 'Client B Studio',
        'email' => 'clientb.orders@example.com',
        'phone' => '08987654321',
        'status' => 'ACTIVE',
    ]);
    $this->clientB->users()->syncWithoutDetaching([$this->clientUserB->id => ['is_primary' => true]]);

    // Create Catalog
    $this->service = Service::create([
        'name' => 'Wedding Documentation',
        'slug' => 'wedding-documentation',
        'category' => 'WEDDING',
        'status' => CatalogStatus::Active,
    ]);

    $this->packageA = Package::create([
        'service_id' => $this->service->id,
        'name' => 'Cinema Wedding Gold',
        'slug' => 'cinema-wedding-gold',
        'price' => 15000000,
        'status' => CatalogStatus::Active,
    ]);
});

it('executes Jalur A auto-checkout and automated project activation', function () {
    // 1. Client creates draft order
    $createRes = $this->actingAs($this->clientUserA)
        ->postJson('/api/v1/client/service-orders', [
            'service_id' => $this->service->id,
            'package_id' => $this->packageA->id,
            'source' => ServiceOrderSource::Catalog->value,
            'event_name' => 'Wedding of Alex & Brenda',
            'brief_category' => BriefCategory::Wedding->value,
        ]);

    $createRes->assertStatus(201);
    $orderId = $createRes->json('data.id');

    $order = ServiceOrder::find($orderId);
    expect($order->status)->toBe(ServiceOrderStatus::Draft);

    // 2. Client fills brief (Single venue, within base area)
    $briefRes = $this->actingAs($this->clientUserA)
        ->patchJson("/api/v1/client/service-orders/{$orderId}/brief", [
            'event_name' => 'Wedding of Alex & Brenda',
            'event_date' => now()->addMonths(2)->toDateString(),
            'venue_name' => 'Grand Ballroom Hotel Majapahit',
            'city' => 'Surabaya',
            'venue_count' => 1,
            'is_outside_base_area' => false,
            'wedding_couple_names' => 'Alex & Brenda',
            'wedding_style_preference' => 'Cinematic Moody & Documentary',
        ]);

    $briefRes->assertStatus(200);

    // 3. Client submits brief -> Jalur A (Auto-Checkout)
    $submitRes = $this->actingAs($this->clientUserA)
        ->postJson("/api/v1/client/service-orders/{$orderId}/submit");

    $submitRes->assertStatus(200);

    $order->refresh();
    expect($order->status)->toBe(ServiceOrderStatus::AwaitingPayment);
    expect($order->review_type)->toBe(ServiceOrderReviewType::AutoCheckout);
    expect($order->quotation_id)->not->toBeNull();
    expect($order->invoice_id)->not->toBeNull();

    $invoice = $order->invoice;
    expect($invoice->status)->toBe(InvoiceStatus::Unpaid);
    expect($invoice->invoice_type)->toBe(InvoiceType::Dp);
    expect($invoice->amount)->toBe(4500000); // 30% of 15,000,000

    // 4. Client simulates payment via Duitku Callback
    $transaction = PaymentTransaction::create([
        'client_id' => $this->clientA->id,
        'invoice_id' => $invoice->id,
        'merchant_order_id' => 'TX-TEST-'.uniqid(),
        'amount' => 4500000,
        'status' => PaymentStatus::Pending,
    ]);

    (new ProcessPaymentProviderEventAction)->execute(
        merchantOrderId: $transaction->merchant_order_id,
        targetStatus: PaymentStatus::Paid,
        reportedAmount: 4500000,
        providerReference: 'DUITKU-REF-12345',
        paymentMethod: 'BCA_VA'
    );

    // 5. Assert Invoice is Paid and Project is automatically created & linked!
    $order->refresh();
    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::Paid);
    expect($order->status)->toBe(ServiceOrderStatus::ProjectCreated);
    expect($order->project_id)->not->toBeNull();

    $project = Project::find($order->project_id);
    expect($project)->not->toBeNull();
    expect($project->client_id)->toBe($this->clientA->id);
    expect($project->name)->toBe('Wedding of Alex & Brenda');
});

it('executes Jalur B admin review workflow', function () {
    // 1. Client creates order with outside base area
    $createRes = $this->actingAs($this->clientUserA)
        ->postJson('/api/v1/client/service-orders', [
            'service_id' => $this->service->id,
            'package_id' => $this->packageA->id,
            'source' => ServiceOrderSource::Catalog->value,
            'event_name' => 'Destination Wedding Bali',
            'brief_category' => BriefCategory::Wedding->value,
        ]);

    $orderId = $createRes->json('data.id');

    // Fill brief with outside base area
    $this->actingAs($this->clientUserA)
        ->patchJson("/api/v1/client/service-orders/{$orderId}/brief", [
            'event_name' => 'Destination Wedding Bali',
            'event_date' => now()->addMonths(3)->toDateString(),
            'venue_name' => 'Ayana Resort Jimbaran',
            'city' => 'Badung, Bali',
            'venue_count' => 2,
            'is_outside_base_area' => true,
        ]);

    // Submit -> Should route to Jalur B (Admin Review)
    $submitRes = $this->actingAs($this->clientUserA)
        ->postJson("/api/v1/client/service-orders/{$orderId}/submit");

    $submitRes->assertStatus(200);

    $order = ServiceOrder::find($orderId);
    expect($order->status)->toBe(ServiceOrderStatus::Submitted);
    expect($order->review_type)->toBe(ServiceOrderReviewType::AdminReview);
    expect($order->invoice_id)->toBeNull();

    // 2. Admin inspects and attaches custom quotation
    $quotation = Quotation::create([
        'client_id' => $this->clientA->id,
        'quotation_number' => 'QUO-TEST-'.uniqid(),
        'status' => QuotationStatus::Sent,
        'valid_until' => now()->addDays(7),
    ]);

    $version = QuotationVersion::create([
        'quotation_id' => $quotation->id,
        'version_number' => 1,
        'project_name' => 'Destination Wedding Bali - Custom Out of Town',
        'subtotal' => 22000000,
        'grand_total' => 22000000,
        'status' => 'SENT',
    ]);

    $attachRes = $this->actingAs($this->adminUser)
        ->postJson("/api/v1/admin/service-orders/{$orderId}/attach-quotation", [
            'quotation_id' => $quotation->id,
            'message' => 'Halo Kak! Kami telah menambahkan akomodasi crew untuk shoot di Bali.',
        ]);

    $attachRes->assertStatus(200);

    $order->refresh();
    expect($order->status)->toBe(ServiceOrderStatus::QuotationSent);
    expect($order->quotation_id)->toBe($quotation->id);

    // 3. Client accepts quotation
    $acceptRes = $this->actingAs($this->clientUserA)
        ->postJson("/api/v1/client/service-orders/{$orderId}/accept-quotation");

    $acceptRes->assertStatus(200);

    $order->refresh();
    expect($order->status)->toBe(ServiceOrderStatus::AwaitingPayment);
    expect($order->invoice_id)->not->toBeNull();
});

it('enforces IDOR isolation between clients', function () {
    // Create order for Client A
    $order = ServiceOrder::create([
        'order_number' => 'ORD-SEC-001',
        'client_id' => $this->clientA->id,
        'created_by_user_id' => $this->clientUserA->id,
        'service_id' => $this->service->id,
        'status' => ServiceOrderStatus::Draft,
        'source' => ServiceOrderSource::Catalog,
    ]);

    // Client B tries to view Client A's order -> 403 Forbidden
    $viewRes = $this->actingAs($this->clientUserB)
        ->getJson("/api/v1/client/service-orders/{$order->id}");

    $viewRes->assertStatus(403);

    // Client B tries to update Client A's order brief -> 403 Forbidden
    $updateRes = $this->actingAs($this->clientUserB)
        ->patchJson("/api/v1/client/service-orders/{$order->id}/brief", [
            'event_name' => 'Malicious Hack Attempt',
        ]);

    $updateRes->assertStatus(403);
});

it('clones project brief on reorder', function () {
    // 1. Create completed past project for Client A
    $pastProject = Project::create([
        'client_id' => $this->clientA->id,
        'name' => 'Annual Corporate Gala 2025',
        'status' => \App\Domains\Projects\Enums\ProjectStatus::Completed,
        'location' => 'Surabaya Convention Center',
        'brief' => 'Corporate documentation multicam & highlight',
    ]);

    // 2. Client requests re-order
    $reorderRes = $this->actingAs($this->clientUserA)
        ->postJson("/api/v1/client/projects/{$pastProject->id}/reorder");

    $reorderRes->assertStatus(201);
    $newOrderId = $reorderRes->json('data.id');

    $newOrder = ServiceOrder::with('brief')->find($newOrderId);
    expect($newOrder)->not->toBeNull();
    expect($newOrder->source)->toBe(ServiceOrderSource::Reorder);
    expect($newOrder->status)->toBe(ServiceOrderStatus::Draft);
    expect($newOrder->brief->event_name)->toContain('Annual Corporate Gala 2025 (Re-order)');
    expect($newOrder->brief->venue_name)->toBe('Surabaya Convention Center');
});
