<?php

namespace Database\Seeders;

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CatalogSeeder::class,
        ]);

        User::firstOrCreate(
            ['email' => 'owner@gmail.com'],
            [
                'name' => 'BDJG Owner',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        )->syncRoles('OWNER');

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'BDJG Admin',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        )->syncRoles('ADMIN');

        User::firstOrCreate(
            ['email' => 'worker@gmail.com'],
            [
                'name' => 'BDJG Worker',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        )->syncRoles('WORKER');

        // CLIENT seed user — auto-create Client record + link (mirrors CreateNewUser Fortify action)
        $clientUser = User::firstOrCreate(
            ['email' => 'client@gmail.com'],
            [
                'name' => 'BDJG Client',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );
        $clientUser->syncRoles('CLIENT');

        // Ensure Client domain record exists and is linked
        $clientRecord = Client::firstOrCreate(
            ['email' => 'client@gmail.com'],
            [
                'display_name' => 'BDJG Client',
                'billing_name' => 'BDJG Client',
                'billing_email' => 'client@gmail.com',
                'status' => ClientStatus::Active,
            ]
        );
        $clientUser->clients()->syncWithoutDetaching([$clientRecord->id => ['is_primary' => true]]);

        $this->call([
            ClientSeeder::class,
            InquirySeeder::class,
            QuotationSeeder::class,
            InvoiceSeeder::class,
            PaymentTransactionSeeder::class,
            ProjectSeeder::class,
            MediaAssetSeeder::class,
        ]);
    }
}
