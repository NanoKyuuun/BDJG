<?php

namespace Database\Seeders;

use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Billing\Models\InvoiceItem;
use App\Domains\Commercial\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@bdjg.studio')->first();
        $q1 = Quotation::where('quotation_number', 'QT-2026-001')->first();
        $q2 = Quotation::where('quotation_number', 'QT-2026-002')->first();

        if ($q1) {
            $inv1 = Invoice::create([
                'invoice_number' => 'INV-2026-001',
                'client_id' => $q1->client_id,
                'quotation_id' => $q1->id,
                'invoice_type' => InvoiceType::Dp,
                'amount' => 11250000,
                'paid_amount' => 0,
                'status' => InvoiceStatus::Issued,
                'issued_at' => now()->subDay(),
                'due_at' => now()->addDays(6),
                'terms' => 'Pembayaran DP 50% via Duitku Virtual Account / QRIS.',
                'created_by' => $admin?->id,
                'notes_internal' => 'DP invoice sent to Aruna Karya finance team.',
            ]);

            InvoiceItem::create([
                'invoice_id' => $inv1->id,
                'name' => 'Down Payment (DP) 50% - Corporate Profile Video & Aerial 4K',
                'description' => 'Down payment for quotation #QT-2026-001',
                'quantity' => 1,
                'unit_price' => 11250000,
                'line_total' => 11250000,
                'sort_order' => 0,
            ]);
        }

        if ($q2) {
            $inv2 = Invoice::create([
                'invoice_number' => 'INV-2026-002',
                'client_id' => $q2->client_id,
                'quotation_id' => $q2->id,
                'invoice_type' => InvoiceType::Dp,
                'amount' => 9000000,
                'paid_amount' => 9000000,
                'status' => InvoiceStatus::Paid,
                'issued_at' => now()->subDays(3),
                'due_at' => now()->addDays(4),
                'paid_at' => now()->subDays(2),
                'terms' => 'LUNAS (Verified by Duitku Callback).',
                'created_by' => $admin?->id,
                'notes_internal' => 'DP paid via BCA Virtual Account.',
            ]);

            InvoiceItem::create([
                'invoice_id' => $inv2->id,
                'name' => 'Down Payment (DP) 50% - Wedding Cinematic Film 4K',
                'description' => 'Down payment for quotation #QT-2026-002',
                'quantity' => 1,
                'unit_price' => 9000000,
                'line_total' => 9000000,
                'sort_order' => 0,
            ]);
        }
    }
}
