<?php

namespace Database\Seeders;

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

        $owner = User::firstOrCreate(
            ['email' => 'owner@gmail.com'],
            [
                'name' => 'BDJG Owner',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );
        $owner->syncRoles('OWNER');

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

        User::firstOrCreate(
            ['email' => 'client@gmail.com'],
            [
                'name' => 'BDJG Client',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        )->syncRoles('CLIENT');

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
