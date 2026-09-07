<?php

namespace App\Domains\Orders\Actions;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Commercial\Actions\CreateQuotationAction;
use App\Domains\Commercial\Models\Quotation;
use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Orders\Models\ServiceOrder;
use App\Domains\Orders\Models\ServiceOrderStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReviewServiceOrderAction
{
    public function requestInformation(ServiceOrder $order, string $notes, User $admin): ServiceOrder
    {
        return DB::transaction(function () use ($order, $notes, $admin) {
            $oldStatus = $order->status;
            $order->status = ServiceOrderStatus::NeedsInformation;
            $order->save();

            // Create message from admin
            $order->messages()->create([
                'sender_user_id' => $admin->id,
                'message' => $notes,
                'is_internal_note' => false,
            ]);

            ServiceOrderStatusLog::create([
                'service_order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => ServiceOrderStatus::NeedsInformation,
                'actor_user_id' => $admin->id,
                'reason' => $notes,
            ]);

            AuditLogger::log(
                action: 'SERVICE_ORDER_REQUESTED_INFO',
                description: "Admin {$admin->name} requested additional information on Order #{$order->order_number}",
                auditable: $order,
                newValues: ['status' => ServiceOrderStatus::NeedsInformation->value, 'notes' => $notes]
            );

            return $order->fresh(['brief', 'service', 'package', 'client', 'messages']);
        });
    }

    public function attachQuotation(ServiceOrder $order, Quotation $quotation, User $admin): ServiceOrder
    {
        return DB::transaction(function () use ($order, $quotation, $admin) {
            $oldStatus = $order->status;
            $order->quotation_id = $quotation->id;
            $order->status = ServiceOrderStatus::QuotationReady;
            $order->reviewed_at = now();
            $order->save();

            ServiceOrderStatusLog::create([
                'service_order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => ServiceOrderStatus::QuotationReady,
                'actor_user_id' => $admin->id,
                'reason' => "Quotation #{$quotation->quotation_number} attached and ready for client acceptance",
            ]);

            AuditLogger::log(
                action: 'SERVICE_ORDER_QUOTATION_ATTACHED',
                description: "Quotation #{$quotation->quotation_number} attached to Order #{$order->order_number}",
                auditable: $order,
                newValues: ['quotation_number' => $quotation->quotation_number, 'status' => ServiceOrderStatus::QuotationReady->value]
            );

            return $order->fresh(['brief', 'service', 'package', 'client', 'quotation.acceptedVersion', 'quotation.currentVersion']);
        });
    }
}
