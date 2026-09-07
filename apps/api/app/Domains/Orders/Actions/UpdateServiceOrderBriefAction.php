<?php

namespace App\Domains\Orders\Actions;

use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Orders\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateServiceOrderBriefAction
{
    public function execute(ServiceOrder $order, array $data, User $actor): ServiceOrder
    {
        return DB::transaction(function () use ($order, $data, $actor) {
            // Cannot edit brief once payment is done or project created
            if (in_array($order->status, [ServiceOrderStatus::Paid, ServiceOrderStatus::ProjectCreated, ServiceOrderStatus::Cancelled])) {
                throw new InvalidArgumentException("Cannot update brief for an order with status {$order->status->value}.");
            }

            // Update main order event attributes
            $orderUpdates = [];
            if (array_key_exists('event_date', $data)) {
                $orderUpdates['event_date'] = $data['event_date'];
            }
            if (array_key_exists('start_time', $data)) {
                $orderUpdates['start_time'] = $data['start_time'];
            }
            if (array_key_exists('end_time', $data)) {
                $orderUpdates['end_time'] = $data['end_time'];
            }
            if (array_key_exists('venue_count', $data)) {
                $orderUpdates['venue_count'] = (int) $data['venue_count'];
            }
            if (array_key_exists('location_name', $data)) {
                $orderUpdates['location_name'] = $data['location_name'];
            }
            if (array_key_exists('location_address', $data)) {
                $orderUpdates['location_address'] = $data['location_address'];
            }
            if (array_key_exists('is_outside_base_area', $data)) {
                $orderUpdates['is_outside_base_area'] = (bool) $data['is_outside_base_area'];
            }

            if (! empty($orderUpdates)) {
                $order->update($orderUpdates);
            }

            // Update brief record
            $brief = $order->brief ?: $order->brief()->create([]);
            $briefUpdates = [];

            if (array_key_exists('event_name', $data)) {
                $briefUpdates['event_name'] = $data['event_name'];
            }
            if (array_key_exists('event_category', $data)) {
                $briefUpdates['event_category'] = $data['event_category'];
            }
            if (array_key_exists('couple_session_details', $data)) {
                $briefUpdates['couple_session_details'] = $data['couple_session_details'];
            }
            if (array_key_exists('wedding_details', $data)) {
                $briefUpdates['wedding_details'] = $data['wedding_details'];
            }
            if (array_key_exists('custom_requirements', $data)) {
                $briefUpdates['custom_requirements'] = $data['custom_requirements'];
            }
            if (array_key_exists('selected_add_ons', $data)) {
                $briefUpdates['selected_add_ons'] = $data['selected_add_ons'];
            }
            if (array_key_exists('onsite_pic_name', $data)) {
                $briefUpdates['onsite_pic_name'] = $data['onsite_pic_name'];
            }
            if (array_key_exists('onsite_pic_phone', $data)) {
                $briefUpdates['onsite_pic_phone'] = $data['onsite_pic_phone'];
            }
            if (array_key_exists('portfolio_consent', $data)) {
                $briefUpdates['portfolio_consent'] = (bool) $data['portfolio_consent'];
            }
            if (array_key_exists('additional_notes', $data)) {
                $briefUpdates['additional_notes'] = $data['additional_notes'];
            }

            if (! empty($briefUpdates)) {
                $brief->update($briefUpdates);
            }

            return $order->fresh(['brief', 'service', 'package', 'client', 'attachments', 'messages']);
        });
    }
}
