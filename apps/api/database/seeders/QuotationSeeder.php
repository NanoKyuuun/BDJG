<?php

namespace Database\Seeders;

use App\Domains\Catalog\Enums\DpType;
use App\Domains\Clients\Models\Client;
use App\Domains\Commercial\Enums\QuotationItemType;
use App\Domains\Commercial\Enums\QuotationStatus;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Commercial\Models\QuotationItem;
use App\Domains\Commercial\Models\QuotationVersion;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@bdjg.studio')->first();
        $client1 = Client::where('email', 'aruna.karya@example.com')->first();
        $client2 = Client::where('email', 'dhea.arya@example.com')->first();

        if ($client1) {
            $q1 = Quotation::create([
                'quotation_number' => 'QT-2026-001',
                'client_id' => $client1->id,
                'status' => QuotationStatus::Sent,
                'sent_at' => now()->subDays(2),
                'expires_at' => now()->addDays(12),
                'created_by' => $admin?->id,
                'notes_internal' => 'Special discount applied for long-term contract.',
            ]);

            $v1 = QuotationVersion::create([
                'quotation_id' => $q1->id,
                'version_number' => 1,
                'project_name' => 'Corporate Profile Video & Aerial 4K',
                'service_name_snapshot' => 'Videography & Film Production',
                'package_name_snapshot' => 'Commercial Video Production Standard',
                'subtotal' => 25000000,
                'discount' => 2500000,
                'tax' => 0,
                'grand_total' => 22500000,
                'dp_type' => DpType::Percentage,
                'dp_value' => 50.00,
                'dp_amount' => 11250000,
                'remaining_amount' => 11250000,
                'terms' => 'DP 50% saat penandatanganan penawaran, sisa 50% sebelum rilis final media.',
                'created_by' => $admin?->id,
            ]);

            QuotationItem::create([
                'quotation_version_id' => $v1->id,
                'type' => QuotationItemType::Package,
                'name' => 'Commercial Video Production Standard',
                'description' => '2 shooting days, 2 cameras 4K, lighting set, sound engineer.',
                'quantity' => 1,
                'unit_price' => 20000000,
                'line_total' => 20000000,
                'sort_order' => 0,
            ]);

            QuotationItem::create([
                'quotation_version_id' => $v1->id,
                'type' => QuotationItemType::AddOn,
                'name' => 'Drone Aerial 4K Cinematography',
                'description' => 'Licensed drone pilot, 2 batteries session.',
                'quantity' => 1,
                'unit_price' => 5000000,
                'line_total' => 5000000,
                'sort_order' => 1,
            ]);

            $q1->current_version_id = $v1->id;
            $q1->save();
        }

        if ($client2) {
            $q2 = Quotation::create([
                'quotation_number' => 'QT-2026-002',
                'client_id' => $client2->id,
                'status' => QuotationStatus::Accepted,
                'sent_at' => now()->subDays(5),
                'viewed_at' => now()->subDays(4),
                'accepted_at' => now()->subDays(3),
                'expires_at' => now()->addDays(9),
                'created_by' => $admin?->id,
                'notes_internal' => 'Accepted by Dhea Anandita via client portal.',
            ]);

            $v2_1 = QuotationVersion::create([
                'quotation_id' => $q2->id,
                'version_number' => 1,
                'project_name' => 'Wedding Cinematic Film 4K',
                'service_name_snapshot' => 'Videography & Film Production',
                'package_name_snapshot' => 'Wedding Cinematic Film 4K',
                'subtotal' => 18000000,
                'discount' => 0,
                'tax' => 0,
                'grand_total' => 18000000,
                'dp_type' => DpType::Percentage,
                'dp_value' => 50.00,
                'dp_amount' => 9000000,
                'remaining_amount' => 9000000,
                'terms' => 'DP 50% via Duitku Virtual Account / QRIS, sisa 50% sebelum penyerahan final master.',
                'created_by' => $admin?->id,
            ]);

            QuotationItem::create([
                'quotation_version_id' => $v2_1->id,
                'type' => QuotationItemType::Package,
                'name' => 'Wedding Cinematic Film 4K',
                'quantity' => 1,
                'unit_price' => 18000000,
                'line_total' => 18000000,
                'sort_order' => 0,
            ]);

            $q2->current_version_id = $v2_1->id;
            $q2->accepted_version_id = $v2_1->id;
            $q2->save();
        }
    }
}
