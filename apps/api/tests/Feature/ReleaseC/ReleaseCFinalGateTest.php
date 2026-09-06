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
use App\Domains\Finance\Enums\ExpenseCategory;
use App\Domains\Finance\Enums\ExpenseStatus;
use App\Domains\Finance\Models\WorkerExpense;
use App\Domains\Media\Enums\FileVisibility;
use App\Domains\Media\Enums\MediaCategory;
use App\Domains\Media\Enums\ProcessingStatus;
use App\Domains\Media\Models\MediaAsset;
use App\Domains\Notifications\Notifications\ProjectStatusChangedNotification;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Workers\Enums\WorkerProfession;
use App\Domains\Workers\Enums\WorkerStatus;
use App\Domains\Workers\Models\WorkerProfile;
use App\Mail\ClientInvitationMail;
use App\Mail\InvoiceIssuedMail;
use App\Mail\QuotationSentMail;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('Release C Final Gate: Multi-Channel Notifications, Expense Claims, and Finance Leak Isolation', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

    Mail::fake();
    Storage::fake('media');

    // 1. Setup Studio Admin
    $admin = User::create([
        'name' => 'Studio Executive Producer',
        'email' => 'producer@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $admin->syncRoles('ADMIN');

    // 2. Setup Client & Client User
    $client = Client::create([
        'display_name' => 'Nusantara Heritage Corp',
        'email' => 'finance@nusantara.co.id',
        'status' => ClientStatus::Active,
    ]);
    $clientUser = User::create([
        'name' => 'Nusantara Director',
        'email' => 'director@nusantara.co.id',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $clientUser->syncRoles('CLIENT');
    DB::table('client_users')->insert([
        'client_id' => $client->id,
        'user_id' => $clientUser->id,
        'is_primary' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 3. Setup Assigned Worker
    $workerUser = User::create([
        'name' => 'Field Cinematographer',
        'email' => 'cinematographer@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $workerUser->syncRoles('WORKER');
    $workerProfile = WorkerProfile::create([
        'user_id' => $workerUser->id,
        'profession' => WorkerProfession::Videographer,
        'status' => WorkerStatus::Active,
    ]);

    // 4. Create Active Project
    $project = Project::create([
        'client_id' => $client->id,
        'name' => 'Nusantara Heritage Documentary',
        'status' => ProjectStatus::Production,
        'created_by' => $admin->id,
    ]);
    $project->assignments()->create([
        'worker_id' => $workerProfile->id,
        'assignment_role' => 'Director of Photography',
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    // =========================================================================
    // GATE CHECK 1: In-App Notifications Engine & Read State Lifecycle
    // =========================================================================
    $clientUser->notify(new ProjectStatusChangedNotification($project, 'DRAFT', 'PRODUCTION'));
    expect($clientUser->unreadNotifications()->count())->toBe(1);

    $this->actingAs($clientUser);
    $notifList = $this->getJson('/api/v1/notifications');
    $notifList->assertOk();
    $notifId = $notifList->json('data.0.id');

    $readResp = $this->patchJson("/api/v1/notifications/{$notifId}/read");
    $readResp->assertOk();
    expect($clientUser->fresh()->unreadNotifications()->count())->toBe(0);

    // =========================================================================
    // GATE CHECK 2: Email Dispatch Integration
    // =========================================================================
    $this->actingAs($admin);
    $this->postJson("/api/v1/admin/clients/{$client->id}/invite", [
        'email' => 'finance@nusantara.co.id',
    ])->assertCreated();

    Mail::assertQueued(ClientInvitationMail::class, function ($mail) use ($client) {
        return $mail->hasTo('finance@nusantara.co.id') && $mail->client->id === $client->id;
    });

    // =========================================================================
    // GATE CHECK 3: Worker Expense Submission & Admin Approval
    // =========================================================================
    $this->actingAs($workerUser);
    $expenseResp = $this->postJson('/api/v1/worker/expenses', [
        'project_id' => $project->id,
        'title' => 'Underwater Drone Battery Pack Rental',
        'amount' => 750000,
        'category' => 'EQUIPMENT_RENTAL',
        'notes' => 'Day 2 coral reef documentary shooting',
    ]);
    $expenseResp->assertCreated();
    $expenseId = $expenseResp->json('data.id');
    $expense = WorkerExpense::find($expenseId);

    $this->actingAs($admin);
    $approveResp = $this->patchJson("/api/v1/admin/finance/expenses/{$expense->id}/approve");
    $approveResp->assertOk();
    expect($expense->fresh()->status)->toBe(ExpenseStatus::Approved);

    // =========================================================================
    // GATE CHECK 4: Studio Financial Calculations & Reconciliation
    // =========================================================================
    Invoice::create([
        'client_id' => $client->id,
        'project_id' => $project->id,
        'invoice_type' => InvoiceType::Dp,
        'amount' => 25000000,
        'paid_amount' => 25000000,
        'status' => InvoiceStatus::Paid,
        'paid_at' => now(),
    ]);

    $profitResp = $this->getJson("/api/v1/admin/finance/projects/{$project->id}/profit");
    $profitResp->assertOk();
    $profitResp->assertJsonPath('data.total_paid_revenue', 25000000);
    $profitResp->assertJsonPath('data.total_approved_expenses', 750000);
    $profitResp->assertJsonPath('data.gross_profit', 24250000);
    $profitResp->assertJsonPath('data.margin_percentage', 97);

    // =========================================================================
    // GATE CHECK 5: Strict Anti-Leak Authorization Isolation
    // =========================================================================
    // Worker MUST NOT see financial profit
    $this->actingAs($workerUser);
    $this->getJson("/api/v1/admin/finance/projects/{$project->id}/profit")->assertForbidden();
    $this->getJson("/api/v1/admin/finance/summary")->assertForbidden();

    // Client MUST NOT see financial profit or worker expenses
    $this->actingAs($clientUser);
    $this->getJson("/api/v1/admin/finance/projects/{$project->id}/profit")->assertForbidden();
    $this->getJson("/api/v1/admin/finance/expenses")->assertForbidden();
});
