<?php

namespace Database\Seeders;

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clientUser = User::where('email', 'client@bdjg.studio')->first();

        $c1 = Client::firstOrCreate(
            ['email' => 'aruna.karya@example.com'],
            [
                'display_name' => 'PT Aruna Karya',
                'company_or_institution' => 'PT Aruna Karya Indonesia',
                'phone' => '+6281234567890',
                'billing_name' => 'PT Aruna Karya Indonesia',
                'billing_email' => 'finance@arunakarya.co.id',
                'billing_phone' => '+6281234567891',
                'billing_address' => 'Jl. Sudirman No. 45, Jakarta Selatan',
                'status' => ClientStatus::Active,
                'notes_internal' => 'VIP Corporate client. Preferred shooting location in SCBD.',
            ]
        );

        if ($clientUser) {
            $c1->users()->syncWithoutDetaching([$clientUser->id => ['is_primary' => true]]);
        }

        Client::firstOrCreate(
            ['email' => 'dhea.arya@example.com'],
            [
                'display_name' => 'Dhea & Arya Wedding',
                'company_or_institution' => null,
                'phone' => '+6281987654321',
                'billing_name' => 'Dhea Anandita',
                'billing_email' => 'dhea.arya@example.com',
                'billing_phone' => '+6281987654321',
                'billing_address' => 'Jl. Dago Asri No. 12, Bandung',
                'status' => ClientStatus::Active,
                'notes_internal' => 'Cinematic wedding 4K package. Requested drone coverage.',
            ]
        );

        Client::firstOrCreate(
            ['email' => 'contact@glowskin.id'],
            [
                'display_name' => 'Glow Skincare',
                'company_or_institution' => 'CV Glow Nusantara',
                'phone' => '+6281122334455',
                'billing_name' => 'CV Glow Nusantara',
                'billing_email' => 'billing@glowskin.id',
                'billing_phone' => '+6281122334455',
                'billing_address' => 'Ruko Grand Wisata Blok A5, Bekasi',
                'status' => ClientStatus::Active,
                'notes_internal' => 'Quarterly commercial product photoshoot.',
            ]
        );
    }
}
