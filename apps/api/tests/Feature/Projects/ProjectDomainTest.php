<?php

use App\Domains\Commercial\Models\Quotation;
use App\Domains\Projects\Actions\ActivateProjectAction;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Tasks\Enums\TaskStatus;
use App\Domains\Tasks\Models\Task;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Workers\Enums\WorkerProfession;
use App\Domains\Workers\Enums\WorkerStatus;
use App\Domains\Workers\Models\ProjectAssignment;
use App\Domains\Workers\Models\WorkerProfile;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\InquirySeeder;
use Database\Seeders\InvoiceSeeder;
use Database\Seeders\PaymentTransactionSeeder;
use Database\Seeders\ProjectSeeder;
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
    $this->seed(PaymentTransactionSeeder::class);

    $this->admin = User::firstOrCreate(
        ['email' => 'project.admin@example.com'],
        [
            'name' => 'Project Admin',
            'password' => bcrypt('password'),
            'status' => UserStatus::Active,
        ]
    );
    $this->admin->syncRoles('ADMIN');

    // Worker 1 (Assigned to PRJ-2026-001)
    $this->workerUser = User::firstOrCreate(
        ['email' => 'worker@bdjg.studio'],
        [
            'name' => 'BDJG Worker',
            'password' => bcrypt('password'),
            'status' => UserStatus::Active,
        ]
    );
    $this->workerUser->syncRoles('WORKER');

    $this->seed(ProjectSeeder::class);

    $this->workerProfile = WorkerProfile::where('user_id', $this->workerUser->id)->first();

    // Worker 2 (Unassigned)
    $this->otherWorkerUser = User::firstOrCreate(
        ['email' => 'other.worker@bdjg.studio'],
        [
            'name' => 'Other Worker',
            'password' => bcrypt('password'),
            'status' => UserStatus::Active,
        ]
    );
    $this->otherWorkerUser->syncRoles('WORKER');
    $this->otherWorkerProfile = WorkerProfile::firstOrCreate(
        ['user_id' => $this->otherWorkerUser->id],
        [
            'profession' => WorkerProfession::Colorist,
            'status' => WorkerStatus::Active,
        ]
    );

    // Client User (Dhea & Arya - owns PRJ-2026-001)
    $this->clientUser = User::firstOrCreate(
        ['email' => 'client@bdjg.studio'],
        [
            'name' => 'BDJG Client',
            'password' => bcrypt('password'),
            'status' => UserStatus::Active,
        ]
    );
    $this->clientUser->syncRoles('CLIENT');
    $clientB = \App\Domains\Clients\Models\Client::where('email', 'dhea.arya@example.com')->first();
    if ($clientB) {
        $clientB->users()->syncWithoutDetaching([$this->clientUser->id => ['is_primary' => true]]);
    }

    // Other Client User (Aruna Karya)
    $this->otherClientUser = User::firstOrCreate(
        ['email' => 'other.client@example.com'],
        [
            'name' => 'Other Client',
            'password' => bcrypt('password'),
            'status' => UserStatus::Active,
        ]
    );
    $this->otherClientUser->syncRoles('CLIENT');
    $clientA = \App\Domains\Clients\Models\Client::where('email', 'aruna.karya@example.com')->first();
    if ($clientA) {
        $clientA->users()->syncWithoutDetaching([$this->otherClientUser->id => ['is_primary' => true]]);
    }
});

it('activates project from accepted quotation with snapshot contract values', function () {
    $quotation = Quotation::where('quotation_number', 'QT-2026-001')->first();

    $project = (new ActivateProjectAction)->execute($quotation, $this->admin);

    expect($project)->not->toBeNull();
    expect($project->project_number)->toMatch('/^PRJ-\d{4}-\d{3}$/');
    expect($project->status)->toBe(ProjectStatus::PreProduction);
    expect($project->contract_value)->toBe(22500000);
});

it('allows admin to list projects with search and filters', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson('/api/v1/admin/projects?search=PRJ-2026-001');

    $response->assertOk()
        ->assertJsonPath('data.0.project_number', 'PRJ-2026-001');
});

it('allows admin to change project status following state machine rules', function () {
    $this->actingAs($this->admin);
    $project = Project::where('project_number', 'PRJ-2026-001')->first();

    // PRODUCTION -> POST_PRODUCTION
    $response = $this->patchJson("/api/v1/admin/projects/{$project->id}/status", [
        'status' => 'POST_PRODUCTION',
        'notes' => 'Footage successfully ingested to editing server.',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.status', 'POST_PRODUCTION');

    expect($project->fresh()->status)->toBe(ProjectStatus::PostProduction);
});

it('prevents illegal project state transitions with 422', function () {
    $this->actingAs($this->admin);
    $project = Project::where('project_number', 'PRJ-2026-001')->first();

    // PRODUCTION cannot skip directly to ARCHIVED
    $response = $this->patchJson("/api/v1/admin/projects/{$project->id}/status", [
        'status' => 'ARCHIVED',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('message', 'Cannot transition project from PRODUCTION to ARCHIVED.');
});

it('allows admin to manage worker profiles', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson('/api/v1/admin/workers');

    $response->assertOk()
        ->assertJsonPath('data.0.profession', 'VIDEOGRAPHER');
});

it('allows admin to assign and remove workers from projects', function () {
    $this->actingAs($this->admin);
    $project = Project::where('project_number', 'PRJ-2026-001')->first();

    // Assign colorist
    $assignResponse = $this->postJson("/api/v1/admin/projects/{$project->id}/assignments", [
        'worker_id' => $this->otherWorkerProfile->id,
        'assignment_role' => 'Lead Colorist',
        'fee_amount' => 2000000,
    ]);

    $assignResponse->assertCreated()
        ->assertJsonPath('data.assignment_role', 'Lead Colorist');

    $assignment = ProjectAssignment::where('project_id', $project->id)
        ->where('worker_id', $this->otherWorkerProfile->id)
        ->first();

    // Remove assignment
    $removeResponse = $this->deleteJson("/api/v1/admin/projects/{$project->id}/assignments/{$assignment->id}");
    $removeResponse->assertOk();

    expect($assignment->fresh()->is_active)->toBeFalse();
});

it('allows admin to create tasks and schedules for projects', function () {
    $this->actingAs($this->admin);
    $project = Project::where('project_number', 'PRJ-2026-001')->first();

    // Create task
    $taskResponse = $this->postJson('/api/v1/admin/tasks', [
        'project_id' => $project->id,
        'title' => 'Audio Mastering 5.1 Surround',
        'priority' => 'HIGH',
    ]);
    $taskResponse->assertCreated()
        ->assertJsonPath('data.title', 'Audio Mastering 5.1 Surround');

    // Create schedule
    $schedResponse = $this->postJson('/api/v1/admin/schedules', [
        'project_id' => $project->id,
        'title' => 'Director Pre-Production Meeting',
        'schedule_type' => 'MEETING',
        'start_time' => now()->addDays(2)->toISOString(),
    ]);
    $schedResponse->assertCreated()
        ->assertJsonPath('data.schedule_type', 'MEETING');
});

it('allows assigned worker to list assigned projects and update task status via worker portal', function () {
    $this->actingAs($this->workerUser);

    // List projects
    $projectsResponse = $this->getJson('/api/v1/worker/projects');
    $projectsResponse->assertOk()
        ->assertJsonPath('data.0.project_number', 'PRJ-2026-001');

    // Update task status
    $task = Task::where('assigned_worker_id', $this->workerProfile->id)
        ->where('status', TaskStatus::InProgress)
        ->first();

    $taskUpdateResponse = $this->patchJson("/api/v1/worker/tasks/{$task->id}/status", [
        'status' => 'REVIEW',
    ]);

    $taskUpdateResponse->assertOk()
        ->assertJsonPath('data.status', 'REVIEW');
});

it('prevents worker from accessing unassigned projects (Assigned Scope Invariant)', function () {
    $this->actingAs($this->otherWorkerUser);
    $project = Project::where('project_number', 'PRJ-2026-001')->first();

    $response = $this->getJson("/api/v1/worker/projects/{$project->id}");
    $response->assertForbidden();
});

it('prevents worker portal from leaking contract value or internal notes (Anti-leakage)', function () {
    $this->actingAs($this->workerUser);
    $project = Project::where('project_number', 'PRJ-2026-001')->first();

    $response = $this->getJson("/api/v1/worker/projects/{$project->id}");
    $response->assertOk();

    $data = $response->json('data');
    expect(array_key_exists('contract_value', $data))->toBeFalse();
    expect(array_key_exists('notes_internal', $data))->toBeFalse();
});

it('allows client to view own project and schedules via client portal', function () {
    $this->actingAs($this->clientUser);
    $project = Project::where('project_number', 'PRJ-2026-001')->first();

    $response = $this->getJson("/api/v1/client/projects/{$project->id}");

    $response->assertOk()
        ->assertJsonPath('data.project_number', 'PRJ-2026-001')
        ->assertJsonPath('data.name', $project->name);
});

it('prevents client A from viewing client B project (Anti-IDOR)', function () {
    $this->actingAs($this->otherClientUser);
    $project = Project::where('project_number', 'PRJ-2026-001')->first();

    $response = $this->getJson("/api/v1/client/projects/{$project->id}");
    $response->assertForbidden();
});
