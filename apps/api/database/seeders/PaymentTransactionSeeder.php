<?php

namespace Database\Seeders;

use App\Domains\Billing\Models\Invoice;
use App\Domains\Payments\Enums\PaymentProvider;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\PaymentStatusHistory;
use App\Domains\Payments\Models\PaymentTransaction;
use Illuminate\Database\Seeder;

class PaymentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $paidInvoice = Invoice::where('invoice_number', 'INV-2026-002')->first();

        if ($paidInvoice) {
            $transaction = PaymentTransaction::firstOrCreate(
                ['merchant_order_id' => 'BDJG-INV-2026-002-178840'],
                [
                    'invoice_id' => $paidInvoice->id,
                    'provider' => PaymentProvider::Duitku,
                    'provider_reference' => 'DUI-REF-20260903001',
                    'amount' => $paidInvoice->amount,
                    'payment_method' => 'VA_BCA',
                    'status' => PaymentStatus::Paid,
                    'payment_url' => 'https://sandbox.duitku.com/pay/mock-va-bca',
                    'va_number' => '8808123456789012',
                    'expires_at' => now()->addDay(),
                    'paid_at' => now()->subDays(2),
                ]
            );

            PaymentStatusHistory::firstOrCreate(
                [
                    'payment_transaction_id' => $transaction->id,
                    'to_status' => PaymentStatus::Paid,
                ],
                [
                    'from_status' => PaymentStatus::Pending,
                    'source' => 'DUITKU_CALLBACK',
                    'payload' => [
                        'merchantCode' => 'D12345',
                        'amount' => $paidInvoice->amount,
                        'merchantOrderId' => $transaction->merchant_order_id,
                        'resultCode' => '00',
                    ],
                ]
            );
        }
    }
}
