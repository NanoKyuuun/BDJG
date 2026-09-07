<?php

namespace App\Domains\Orders\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Billing\Enums\InvoiceStatus;
use App\Domains\Billing\Enums\InvoiceType;
use App\Domains\Billing\Models\Invoice;
use App\Domains\Billing\Models\InvoiceItem;
use App\Domains\Catalog\Enums\DpType;
use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Orders\Models\ServiceOrder;
use App\Domains\Orders\Models\ServiceOrderStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CheckoutStandardOrderAction
{
    public function execute(ServiceOrder $order, User $actor): Invoice
    {
        return DB::transaction(function () use ($order, $actor) {
            $order = ServiceOrder::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($order->invoice_id) {
                return Invoice::findOrFail($order->invoice_id);
            }

            $order->loadMissing(['package', 'service', 'brief', 'client']);
            $package = $order->package;

            if (! $package || $package->base_price <= 0) {
                throw new InvalidArgumentException("Cannot execute standard checkout on order #{$order->order_number}: Missing fixed price package.");
            }

            $packagePrice = (int) $package->base_price;
            $dpAmount = (int) ($packagePrice * 0.5); // Default 50%

            if ($package->default_dp_type === DpType::Fixed && $package->default_dp_value > 0) {
                $dpAmount = min((int) $package->default_dp_value, $packagePrice);
            } elseif ($package->default_dp_type === DpType::Percentage && $package->default_dp_value > 0) {
                $dpAmount = (int) round(($packagePrice * $package->default_dp_value) / 100);
            }

            // Create DP Invoice
            $invoice = Invoice::create([
                'client_id' => $order->client_id,
                'quotation_id' => null,
                'project_id' => null,
                'invoice_type' => InvoiceType::Dp,
                'amount' => $dpAmount,
                'paid_amount' => 0,
                'currency' => $package->currency ?? 'IDR',
                'status' => InvoiceStatus::Issued,
                'issued_at' => now(),
                'due_at' => now()->addDays(2),
                'terms' => 'Down Payment (DP) 50% via Duitku Sandbox / Virtual Account.',
                'notes_internal' => "Auto-generated for Order #{$order->order_number} ({$package->name})",
                'created_by' => $actor->id,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'name' => "Down Payment (DP) - {$package->name}",
                'description' => "DP for Order #{$order->order_number} - Event date: " . ($order->event_date?->format('d M Y') ?? 'TBA'),
                'quantity' => 1,
                'unit_price' => $dpAmount,
                'line_total' => $dpAmount,
                'sort_order' => 0,
            ]);

            $oldStatus = $order->status;
            $order->invoice_id = $invoice->id;
            $order->status = ServiceOrderStatus::AwaitingPayment;
            $order->save();

            ServiceOrderStatusLog::create([
                'service_order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => ServiceOrderStatus::AwaitingPayment,
                'actor_user_id' => $actor->id,
                'reason' => "DP Invoice #{$invoice->invoice_number} generated for standard checkout",
            ]);

            AuditLogger::log(
                action: 'SERVICE_ORDER_CHECKOUT_INVOICE_GENERATED',
                description: "DP Invoice #{$invoice->invoice_number} generated for Service Order #{$order->order_number}",
                auditable: $order,
                newValues: ['invoice_number' => $invoice->invoice_number, 'amount' => $invoice->amount]
            );

            return $invoice;
        });
    }
}
