<?php

use App\Domains\Users\Enums\UserStatus;
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
    $this->seed(ProjectSeeder::class);

    $this->admin = User::create([
        'name' => 'Metrics Admin',
        'email' => 'metrics.admin@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->admin->syncRoles('ADMIN');

    $this->clientUser = User::create([
        'name' => 'Client Metrics',
        'email' => 'client.metrics@example.com',
        'password' => bcrypt('password'),
        'status' => UserStatus::Active,
    ]);
    $this->clientUser->syncRoles('CLIENT');
});

it('allows admin to fetch real studio dashboard metrics', function () {
    $this->actingAs($this->admin);

    $response = $this->getJson('/api/v1/admin/dashboard/metrics');

    $response->assertOk()
        ->assertJsonStructure([
            'summary' => [
                'new_inquiries',
                'quotations_waiting',
                'quotations_won',
                'active_projects',
                'invoices_due',
                'today_shoots',
            ],
            'finance' => [
                'cash_received',
                'outstanding_amount',
                'currency',
            ],
            'recent_projects',
            'upcoming_schedules',
        ]);
});

it('denies client from accessing admin dashboard metrics', function () {
    $this->actingAs($this->clientUser);

    $response = $this->getJson('/api/v1/admin/dashboard/metrics');
    $response->assertForbidden();
});
