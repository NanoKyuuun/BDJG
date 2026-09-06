<?php

use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('seeds the four canonical roles', function () {
    expect(Role::count())->toBe(4);
    expect(Role::where('name', 'OWNER')->exists())->toBeTrue();
    expect(Role::where('name', 'ADMIN')->exists())->toBeTrue();
    expect(Role::where('name', 'WORKER')->exists())->toBeTrue();
    expect(Role::where('name', 'CLIENT')->exists())->toBeTrue();
});

it('does not duplicate roles on re-run', function () {
    $this->seed(RoleSeeder::class);
    expect(Role::count())->toBe(4);
});

it('seeds permissions using resource.action convention', function () {
    $permissions = Permission::pluck('name')->toArray();

    // ponytail: spot-check, not exhaustive list
    expect($permissions)->toContain('clients.view');
    expect($permissions)->toContain('clients.create');
    expect($permissions)->toContain('inquiries.view');
    expect($permissions)->toContain('quotations.view');
    expect($permissions)->toContain('invoices.view');
    expect($permissions)->toContain('projects.view');
    expect($permissions)->toContain('tasks.view');
    expect($permissions)->toContain('audit.view');
});

it('does not duplicate permissions on re-run', function () {
    $countBefore = Permission::count();
    $this->seed(RoleSeeder::class);
    expect(Permission::count())->toBe($countBefore);
});

it('assigns all permissions to OWNER role', function () {
    $ownerRole = Role::where('name', 'OWNER')->first();
    $totalPermissions = Permission::count();
    expect($ownerRole->permissions->count())->toBe($totalPermissions);
});

it('assigns all permissions to ADMIN role', function () {
    $adminRole = Role::where('name', 'ADMIN')->first();
    $totalPermissions = Permission::count();
    expect($adminRole->permissions->count())->toBe($totalPermissions);
});

it('assigns limited permissions to WORKER role', function () {
    $workerRole = Role::where('name', 'WORKER')->first();
    $workerPerms = $workerRole->permissions->pluck('name')->toArray();

    expect($workerPerms)->toContain('projects.view');
    expect($workerPerms)->toContain('tasks.view');
    expect($workerPerms)->toContain('tasks.update');
    expect($workerPerms)->not->toContain('clients.create');
    expect($workerPerms)->not->toContain('invoices.create');
    expect($workerPerms)->not->toContain('payments.view');
});

it('assigns limited permissions to CLIENT role', function () {
    $clientRole = Role::where('name', 'CLIENT')->first();
    $clientPerms = $clientRole->permissions->pluck('name')->toArray();

    expect($clientPerms)->toContain('clients.view');
    expect($clientPerms)->toContain('quotations.view');
    expect($clientPerms)->toContain('invoices.view');
    expect($clientPerms)->not->toContain('clients.create');
    expect($clientPerms)->not->toContain('inquiries.create');
    expect($clientPerms)->not->toContain('projects.create');
});

it('grants OWNER super-admin via Gate::before', function () {
    $owner = User::factory()->create([
        'status' => UserStatus::Active,
    ])->syncRoles('OWNER');

    expect($owner->can('anything'))->toBeTrue();
    expect($owner->can('clients.create'))->toBeTrue();
    expect($owner->can('payments.refund'))->toBeTrue();
});

it('checks permission on user with role', function () {
    $admin = User::factory()->create([
        'status' => UserStatus::Active,
    ])->syncRoles('ADMIN');

    expect($admin->can('clients.create'))->toBeTrue();
    expect($admin->can('clients.view'))->toBeTrue();
});

it('denies permission on user without role', function () {
    $user = User::factory()->create([
        'status' => UserStatus::Active,
    ]);

    expect($user->can('clients.create'))->toBeFalse();
    expect($user->can('inquiries.view'))->toBeFalse();
});

it('denies permission for wrong role', function () {
    $worker = User::factory()->create([
        'status' => UserStatus::Active,
    ])->syncRoles('WORKER');

    expect($worker->can('clients.create'))->toBeFalse();
    expect($worker->can('invoices.create'))->toBeFalse();
    expect($worker->can('payments.refund'))->toBeFalse();
});

it('allows worker to view assigned resources', function () {
    $worker = User::factory()->create([
        'status' => UserStatus::Active,
    ])->syncRoles('WORKER');

    expect($worker->can('projects.view'))->toBeTrue();
    expect($worker->can('tasks.view'))->toBeTrue();
    expect($worker->can('tasks.update'))->toBeTrue();
    expect($worker->can('schedules.view'))->toBeTrue();
});

it('allows client to view own resources', function () {
    $client = User::factory()->create([
        'status' => UserStatus::Active,
    ])->syncRoles('CLIENT');

    expect($client->can('clients.view'))->toBeTrue();
    expect($client->can('clients.update'))->toBeTrue();
    expect($client->can('quotations.view'))->toBeTrue();
    expect($client->can('invoices.view'))->toBeTrue();
    expect($client->can('projects.view'))->toBeTrue();
});

it('uses web guard for all roles', function () {
    $roles = Role::all();
    foreach ($roles as $role) {
        expect($role->guard_name)->toBe('web');
    }
});
