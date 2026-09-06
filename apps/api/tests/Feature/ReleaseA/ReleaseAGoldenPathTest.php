<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\CRM\Enums\InquiryStatus;
use App\Domains\CRM\Models\Inquiry;
use App\Domains\Payments\Contracts\PaymentGateway;
use App\Domains\Payments\DTOs\PaymentGatewayResult;
use App\Domains\Payments\DTOs\VerifiedPaymentEvent;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Enums\TaskStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Workers\Enums\WorkerProfession;
use App\Domains\Workers\Enums\WorkerStatus;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    // Mock payment gateway initiation & webhook callback verification
    $fakeGateway = Mockery::mock(PaymentGateway::class);
    $fakeGateway->shouldReceive('createTransaction')
        ->andReturn(new PaymentGatewayResult(
            success: true,
            merchantOrderId: 'INV-TEST-001',
            paymentUrl: 'https://sandbox.duitku.com/checkout/test',
            reference: 'DUITKU-REF-999',
            vaNumber: '8888000012345678',
            qrString: null,
            statusCode: '00',
            statusMessage: 'SUCCESS',
            rawResponse: ['statusCode' => '00']
        ));
    $fakeGateway->shouldReceive('verifyCallback')
        ->andReturnUsing(function ($payload) {
            return new VerifiedPaymentEvent(
                isValid: true,
                merchantOrderId: $payload['merchantOrderId'],
                status: PaymentStatus::Paid,
                amount: (int) $payload['amount'],
                providerReference: $payload['reference'] ?? 'DUITKU-REF-999',
                paymentMethod: $payload['paymentCode'] ?? 'VA-BCA',
                rawPayload: $payload
            );
        });

    app()->instance(PaymentGateway::class, $fakeGateway);

    // Create Admin
    $this->admin = User::create([
        'name' => 'Golden Admin',
        'email' => 'golden.admin@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    // Create Worker User
    $this->workerUser = User::create([
        'name' => 'Golden Videographer',
        'email' => 'worker.golden@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->workerUser->syncRoles('WORKER');

    $this->workerProfile = WorkerProfile::create([
        'user_id' => $this->workerUser->id,
        'profession' => WorkerProfession::Videographer,
        'status' => WorkerStatus::Active,
    ]);
});

it('executes full Release A Golden Path from lead ingestion to project completion', function () {
    // 1. Visitor submits public inquiry
    $leadResponse = $this->postJson('/api/v1/inquiries', [
        'client_name' => 'Raden Mas Danang',
        'email' => 'danang@example.com',
        'phone' => '081234567890',
        'preferred_date' => '2026-11-20',
        'estimated_budget' => 35000000,
        'project_brief' => 'Danang & Ayu Royal Wedding Cinematic highlight and full documentary.',
    ]);
    $leadResponse->assertCreated();
    $inquiryId = $leadResponse->json('data.id');
    expect(Inquiry::find($inquiryId)->status)->toBe(InquiryStatus::New);

    // 2. Admin converts inquiry to Client and moves status
    $this->actingAs($this->admin);
    $convertResponse = $this->postJson("/api/v1/admin/inquiries/{$inquiryId}/convert-to-client");
    $convertResponse->assertCreated();
    $clientId = $convertResponse->json('data.id');
    expect(Inquiry::find($inquiryId)->client_id)->toBe($clientId);

    $this->patchJson("/api/v1/admin/inquiries/{$inquiryId}/status", ['status' => 'CONTACTED'])->assertOk();
    $this->patchJson("/api/v1/admin/inquiries/{$inquiryId}/status", ['status' => 'QUALIFIED'])->assertOk();
    $this->patchJson("/api/v1/admin/inquiries/{$inquiryId}/status", ['status' => 'QUOTATION'])->assertOk();
    expect(Inquiry::find($inquiryId)->status)->toBe(InquiryStatus::Quotation);

    // Create a Client User account for portal access
    $clientUser = User::create([
        'name' => 'Raden Mas Danang',
        'email' => 'danang@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $clientUser->syncRoles('CLIENT');
    \DB::table('client_users')->insert([
        'client_id' => $clientId,
        'user_id' => $clientUser->id,
        'is_primary' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 3. Admin creates and sends Quotation
    $quoteResponse = $this->postJson('/api/v1/admin/quotations', [
        'client_id' => $clientId,
        'inquiry_id' => $inquiryId,
        'project_name' => 'Danang & Ayu Royal Wedding Cinematic',
        'dp_type' => 'PERCENTAGE',
        'dp_value' => 50,
        'items' => [
            ['type' => 'PACKAGE', 'name' => 'Signature Wedding Package', 'quantity' => 1, 'unit_price' => 30000000],
            ['type' => 'ADD_ON', 'name' => 'Drone FPV Highlight', 'quantity' => 1, 'unit_price' => 3500000],
        ],
    ]);
    $quoteResponse->assertCreated();
    $quotationId = $quoteResponse->json('data.id');

    // Admin sends quotation
    $this->postJson("/api/v1/admin/quotations/{$quotationId}/send")->assertOk();
    expect(Quotation::find($quotationId)->status)->toBe(QuotationStatus::Sent);

    // 4. Client logs into portal and accepts Quotation
    $this->actingAs($clientUser);
    $acceptResponse = $this->postJson("/api/v1/client/quotations/{$quotationId}/accept");
    $acceptResponse->assertOk();
    expect(Quotation::find($quotationId)->status)->toBe(QuotationStatus::Accepted);

    // 5. Admin generates DP Invoice (automatically in ISSUED status)
    $this->actingAs($this->admin);
    $dpResponse = $this->postJson("/api/v1/admin/quotations/{$quotationId}/generate-dp-invoice");
    $dpResponse->assertCreated();
    $invoiceId = $dpResponse->json('data.id');
    expect(Invoice::find($invoiceId)->status)->toBe(InvoiceStatus::Issued);

    // 6. Client pays invoice via Duitku simulation
    $this->actingAs($clientUser);
    $payResponse = $this->postJson("/api/v1/client/invoices/{$invoiceId}/pay");
    $payResponse->assertCreated();
    $merchantOrderId = $payResponse->json('data.merchant_order_id');

    // Duitku sends verified webhook
    $webhookResponse = $this->postJson('/api/v1/webhooks/duitku', [
        'merchantOrderId' => $merchantOrderId,
        'amount' => 16750000,
        'resultCode' => '00',
        'reference' => 'DUITKU-REF-999',
        'paymentCode' => 'VA-BCA',
        'signature' => 'mocked-valid-signature',
    ]);
    $webhookResponse->assertOk();

    // 7. Auto Project Activation verified
    $invoice = Invoice::find($invoiceId);
    expect($invoice->status)->toBe(InvoiceStatus::Paid);
    expect($invoice->project_id)->not->toBeNull();

    $project = Project::find($invoice->project_id);
    expect($project)->not->toBeNull();
    expect($project->status)->toBe(ProjectStatus::PreProduction);
    expect($project->client_id)->toBe($clientId);

    // 8. Admin assigns worker, task, and schedule
    $this->actingAs($this->admin);
    $assignResponse = $this->postJson("/api/v1/admin/projects/{$project->id}/assignments", [
        'worker_id' => $this->workerProfile->id,
        'assignment_role' => 'Lead Cinematographer',
        'fee_amount' => 5000000,
    ]);
    $assignResponse->assertCreated();

    $taskResponse = $this->postJson('/api/v1/admin/tasks', [
        'project_id' => $project->id,
        'title' => 'Moodboard & Shotlist Preparation',
        'priority' => 'HIGH',
        'assigned_worker_id' => $this->workerProfile->id,
    ]);
    $taskResponse->assertCreated();
    $taskId = $taskResponse->json('data.id');

    $scheduleResponse = $this->postJson('/api/v1/admin/schedules', [
        'project_id' => $project->id,
        'title' => 'Main Wedding Ceremony Shoot',
        'schedule_type' => 'SHOOTING',
        'start_time' => '2026-11-20 07:00:00',
        'end_time' => '2026-11-20 22:00:00',
        'location' => 'Grand Ballroom Hotel Mulia',
    ]);
    $scheduleResponse->assertCreated();

    // 9. Worker logs into portal, views project and completes task
    $this->actingAs($this->workerUser);
    $workerProjResponse = $this->getJson('/api/v1/worker/projects');
    $workerProjResponse->assertOk();
    expect(count($workerProjResponse->json('data')))->toBe(1);

    // Worker advances task status
    $this->patchJson("/api/v1/worker/tasks/{$taskId}/status", ['status' => 'IN_PROGRESS'])->assertOk();
    $this->patchJson("/api/v1/worker/tasks/{$taskId}/status", ['status' => 'REVIEW'])->assertOk();
    $this->patchJson("/api/v1/worker/tasks/{$taskId}/status", ['status' => 'DONE'])->assertOk();
    expect(Task::find($taskId)->status)->toBe(TaskStatus::Done);

    // 10. Admin advances project lifecycle through all phases
    $this->actingAs($this->admin);
    $this->patchJson("/api/v1/admin/projects/{$project->id}/status", ['status' => 'PRODUCTION'])->assertOk();
    $this->patchJson("/api/v1/admin/projects/{$project->id}/status", ['status' => 'POST_PRODUCTION'])->assertOk();
    $this->patchJson("/api/v1/admin/projects/{$project->id}/status", ['status' => 'INTERNAL_REVIEW'])->assertOk();
    $this->patchJson("/api/v1/admin/projects/{$project->id}/status", ['status' => 'CLIENT_REVIEW'])->assertOk();
    $this->patchJson("/api/v1/admin/projects/{$project->id}/status", ['status' => 'FINAL_APPROVAL'])->assertOk();
    $this->patchJson("/api/v1/admin/projects/{$project->id}/status", ['status' => 'FINAL_DELIVERY'])->assertOk();
    $this->patchJson("/api/v1/admin/projects/{$project->id}/status", ['status' => 'COMPLETED'])->assertOk();
    expect($project->fresh()->status)->toBe(ProjectStatus::Completed);

    // 11. Admin verifies real dashboard metrics
    $metricsResponse = $this->getJson('/api/v1/admin/dashboard/metrics');
    $metricsResponse->assertOk();
    expect($metricsResponse->json('summary.quotations_won'))->toBeGreaterThanOrEqual(1);
    expect($metricsResponse->json('finance.cash_received'))->toBeGreaterThanOrEqual(16750000);

    // 12. Audit log verification
    expect(AuditLog::count())->toBeGreaterThanOrEqual(1);
});
