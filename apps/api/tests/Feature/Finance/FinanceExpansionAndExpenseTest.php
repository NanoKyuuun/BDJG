<?php

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Finance\Enums\ExpenseCategory;
use App\Domains\Finance\Enums\ExpenseStatus;
use App\Domains\Finance\Models\WorkerExpense;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Workers\Enums\WorkerProfession;
use App\Domains\Workers\Enums\WorkerStatus;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(CatalogSeeder::class);

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
        'display_name' => 'Bintang Media Group',
        'email' => 'finance@bintang.com',
        'status' => ClientStatus::Active,
    ]);
    $this->clientUser = User::create([
        'name' => 'Bintang Client User',
        'email' => 'finance@bintang.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUser->syncRoles('CLIENT');
    DB::table('client_users')->insert([
        'client_id' => $this->client->id,
        'user_id' => $this->clientUser->id,
        'is_primary' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Project
    $this->project = Project::create([
        'client_id' => $this->client->id,
        'name' => 'Bintang Commercial Shoot',
        'status' => ProjectStatus::Production,
        'created_by' => $this->admin->id,
    ]);

    // Worker 1 (Assigned)
    $this->workerUser1 = User::create([
        'name' => 'Camera Operator 1',
        'email' => 'cam1@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->workerUser1->syncRoles('WORKER');
    $this->workerProfile1 = WorkerProfile::create([
        'user_id' => $this->workerUser1->id,
        'profession' => WorkerProfession::Videographer,
        'status' => WorkerStatus::Active,
    ]);
    $this->project->assignments()->create([
        'worker_id' => $this->workerProfile1->id,
        'assignment_role' => 'Principal Cinematographer',
        'is_active' => true,
        'assigned_at' => now(),
    ]);

    // Worker 2 (Unassigned)
    $this->workerUser2 = User::create([
        'name' => 'Editor 2',
        'email' => 'editor2@bdjg.studio',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->workerUser2->syncRoles('WORKER');
    $this->workerProfile2 = WorkerProfile::create([
        'user_id' => $this->workerUser2->id,
        'profession' => WorkerProfession::Editor,
        'status' => WorkerStatus::Active,
    ]);
});

it('allows assigned worker to submit an expense claim and admin to approve it', function () {
    // 1. Worker 1 submits expense
    $this->actingAs($this->workerUser1);

    $submitResponse = $this->postJson('/api/v1/worker/expenses', [
        'project_id' => $this->project->id,
        'title' => 'Toll & Fuel Reimbursement for Location Shoot',
        'amount' => 450000,
        'category' => 'TRAVEL',
        'receipt_storage_key' => "projects/{$this->project->id}/expenses/receipt_toll.jpg",
        'notes' => 'Toll Jagorawi PP and Pertamax 30L',
    ]);
    $submitResponse->assertCreated();
    $submitResponse->assertJsonPath('data.title', 'Toll & Fuel Reimbursement for Location Shoot');
    $submitResponse->assertJsonPath('data.status', 'SUBMITTED');
    $submitResponse->assertJsonPath('data.amount', 450000);

    $expenseId = $submitResponse->json('data.id');
    $expense = WorkerExpense::find($expenseId);
    expect($expense)->not->toBeNull();

    // 2. Admin approves expense
    $this->actingAs($this->admin);

    $approveResponse = $this->patchJson("/api/v1/admin/finance/expenses/{$expense->id}/approve");
    $approveResponse->assertOk();
    $approveResponse->assertJsonPath('data.status', 'APPROVED');
    expect($expense->fresh()->status)->toBe(ExpenseStatus::Approved);
    expect($expense->fresh()->approved_by_user_id)->toBe($this->admin->id);
});

it('strictly blocks unassigned worker from submitting expenses to the project', function () {
    $this->actingAs($this->workerUser2);

    $submitResponse = $this->postJson('/api/v1/worker/expenses', [
        'project_id' => $this->project->id,
        'title' => 'Unauthorized Lens Rental',
        'amount' => 1200000,
        'category' => 'EQUIPMENT_RENTAL',
    ]);
    $submitResponse->assertForbidden();
});

it('calculates project profit and gross margin correctly for admin', function () {
    // Project Invoices: 1 Paid (10,000,000)
    Invoice::create([
        'client_id' => $this->client->id,
        'project_id' => $this->project->id,
        'invoice_type' => InvoiceType::Dp,
        'amount' => 10000000,
        'paid_amount' => 10000000,
        'status' => InvoiceStatus::Paid,
        'paid_at' => now(),
    ]);

    // Approved Expense: 1,500,000
    WorkerExpense::create([
        'worker_profile_id' => $this->workerProfile1->id,
        'project_id' => $this->project->id,
        'title' => 'Gimbal & Drone Rental',
        'amount' => 1500000,
        'category' => ExpenseCategory::EquipmentRental,
        'status' => ExpenseStatus::Approved,
        'approved_by_user_id' => $this->admin->id,
        'approved_at' => now(),
    ]);

    // Admin requests profit calculation
    $this->actingAs($this->admin);

    $profitResponse = $this->getJson("/api/v1/admin/finance/projects/{$this->project->id}/profit");
    $profitResponse->assertOk();
    $profitResponse->assertJsonPath('data.total_paid_revenue', 10000000);
    $profitResponse->assertJsonPath('data.total_approved_expenses', 1500000);
    $profitResponse->assertJsonPath('data.gross_profit', 8500000);
    $profitResponse->assertJsonPath('data.margin_percentage', 85);
});

it('strictly protects profit calculations and worker costs from client and worker leak (Negative Auth)', function () {
    // Worker attempts to view project profit -> 403
    $this->actingAs($this->workerUser1);
    $workerResponse = $this->getJson("/api/v1/admin/finance/projects/{$this->project->id}/profit");
    $workerResponse->assertForbidden();

    // Client attempts to view project profit -> 403
    $this->actingAs($this->clientUser);
    $clientResponse = $this->getJson("/api/v1/admin/finance/projects/{$this->project->id}/profit");
    $clientResponse->assertForbidden();
});
