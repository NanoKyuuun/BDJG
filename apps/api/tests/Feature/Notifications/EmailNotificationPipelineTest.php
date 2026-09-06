<?php

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Commercial\Models\QuotationVersion;
use App\Domains\Delivery\Actions\CreateDeliveryPackageAction;
use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\ProcessingStatus;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\DTOs\VerifiedPaymentEvent;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Enums\UserStatus;
use App\Mail\ClientInvitationMail;
use App\Mail\FinalDeliveryReadyMail;
use App\Mail\InvoiceIssuedMail;
use App\Mail\PaymentReceiptMail;
use App\Mail\QuotationSentMail;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    Mail::fake();
    Storage::fake('media');

    // Admin
    $this->admin = User::create([
        'name' => 'Studio Admin',
        'email' => 'admin@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    // Client
    $this->client = Client::create([
        'display_name' => 'Garuda Visual Works',
        'email' => 'billing@garudaworks.com',
        'status' => ClientStatus::Active,
    ]);
});

it('dispatches ClientInvitationMail when inviting a client user', function () {
    $this->actingAs($this->admin);

    $response = $this->postJson("/api/v1/admin/clients/{$this->client->id}/invite", [
        'email' => 'director@garudaworks.com',
    ]);
    $response->assertCreated();

    Mail::assertQueued(ClientInvitationMail::class, function ($mail) {
        return $mail->hasTo('director@garudaworks.com') &&
            $mail->client->id === $this->client->id;
    });
});

it('dispatches QuotationSentMail when sending quotation to client', function () {
    $this->actingAs($this->admin);

    $quotation = Quotation::create([
        'client_id' => $this->client->id,
        'status' => QuotationStatus::Draft,
        'created_by' => $this->admin->id,
    ]);

    $version = QuotationVersion::create([
        'quotation_id' => $quotation->id,
        'version_number' => 1,
        'project_name' => 'Commercial Cinema 2026',
        'subtotal' => 10000000,
        'grand_total' => 10000000,
        'dp_type' => 'PERCENTAGE',
        'dp_value' => 50,
        'dp_amount' => 5000000,
        'remaining_amount' => 5000000,
        'terms' => 'DP 50%',
    ]);
    $quotation->update(['current_version_id' => $version->id]);

    $response = $this->postJson("/api/v1/admin/quotations/{$quotation->id}/send");
    $response->assertOk();

    Mail::assertQueued(QuotationSentMail::class, function ($mail) {
        return $mail->hasTo('billing@garudaworks.com');
    });
});

it('dispatches InvoiceIssuedMail when issuing an invoice', function () {
    $this->actingAs($this->admin);

    $invoice = Invoice::create([
        'client_id' => $this->client->id,
        'invoice_type' => InvoiceType::Dp,
        'amount' => 5000000,
        'status' => InvoiceStatus::Draft,
        'created_by' => $this->admin->id,
    ]);

    $response = $this->postJson("/api/v1/admin/invoices/{$invoice->id}/issue");
    $response->assertOk();

    Mail::assertQueued(InvoiceIssuedMail::class, function ($mail) {
        return $mail->hasTo('billing@garudaworks.com');
    });
});

it('dispatches PaymentReceiptMail when payment webhook confirms payment', function () {
    $invoice = Invoice::create([
        'client_id' => $this->client->id,
        'invoice_type' => InvoiceType::Dp,
        'amount' => 5000000,
        'status' => InvoiceStatus::Issued,
        'created_by' => $this->admin->id,
    ]);

    $transaction = PaymentTransaction::create([
        'client_id' => $this->client->id,
        'invoice_id' => $invoice->id,
        'merchant_order_id' => 'INV-PAY-99999',
        'amount' => 5000000,
        'status' => PaymentStatus::Pending,
        'created_by' => $this->admin->id,
    ]);

    // Mock Gateway
    $gateway = Mockery::mock(PaymentGateway::class);
    $gateway->shouldReceive('verifyCallback')
        ->once()
        ->andReturn(new VerifiedPaymentEvent(
            isValid: true,
            merchantOrderId: 'INV-PAY-99999',
            status: PaymentStatus::Paid,
            amount: 5000000,
            providerReference: 'DUITKU-REF-12345',
            paymentMethod: 'BCA_VA',
            rawPayload: ['status' => '00']
        ));
    $this->app->instance(PaymentGateway::class, $gateway);

    $response = $this->postJson('/api/v1/webhooks/duitku', [
        'merchantOrderId' => 'INV-PAY-99999',
        'resultCode' => '00',
    ]);
    $response->assertOk();

    Mail::assertQueued(PaymentReceiptMail::class, function ($mail) {
        return $mail->hasTo('billing@garudaworks.com');
    });
});

it('dispatches FinalDeliveryReadyMail when creating a delivery package', function () {
    $project = Project::create([
        'client_id' => $this->client->id,
        'name' => 'Garuda Master Film',
        'status' => ProjectStatus::PostProduction,
    ]);

    $masterAsset = MediaAsset::create([
        'project_id' => $project->id,
        'uploaded_by_user_id' => $this->admin->id,
        'filename' => 'garuda_master.mp4',
        'original_name' => 'Garuda Master 4K.mp4',
        'storage_key' => "projects/{$project->id}/final/garuda_master.mp4",
        'disk' => 'media',
        'mime_type' => 'video/mp4',
        'size_bytes' => 800000000,
        'category' => MediaCategory::FinalMaster,
        'visibility' => FileVisibility::Internal,
        'version_number' => 1,
        'processing_status' => ProcessingStatus::Ready,
    ]);

    $action = app(CreateDeliveryPackageAction::class);
    $action->execute(
        project: $project,
        actor: $this->admin,
        mediaAssetIds: [$masterAsset->id],
        title: 'Official Garuda 4K Deliverables',
        notes: 'Final Master handover package.'
    );

    Mail::assertQueued(FinalDeliveryReadyMail::class, function ($mail) {
        return $mail->hasTo('billing@garudaworks.com');
    });
});
