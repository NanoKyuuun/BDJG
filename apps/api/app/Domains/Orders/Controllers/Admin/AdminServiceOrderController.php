<?php

namespace App\Domains\Orders\Controllers\Admin;

use App\Domains\Commercial\Models\Quotation;
use App\Domains\Orders\Actions\ReviewServiceOrderAction;
use App\Domains\Orders\Enums\ServiceOrderStatus;
use App\Domains\Orders\Models\ServiceOrder;
use App\Domains\Orders\Models\ServiceOrderStatusLog;
use App\Domains\Orders\Requests\SendOrderMessageRequest;
use App\Domains\Orders\Resources\ServiceOrderMessageResource;
use App\Domains\Orders\Resources\ServiceOrderResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminServiceOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = ServiceOrder::with(['brief', 'service', 'package', 'client', 'quotation.currentVersion', 'invoice', 'project'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('review_type'), fn ($q) => $q->where('review_type', $request->input('review_type')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($cq) => $cq->where('display_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('brief', fn ($bq) => $bq->where('event_name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return ServiceOrderResource::collection($orders);
    }

    public function show(ServiceOrder $serviceOrder): JsonResponse
    {
        $serviceOrder->loadMissing([
            'brief',
            'service',
            'package',
            'client',
            'attachments.uploader',
            'messages.sender.roles',
            'quotation.acceptedVersion.items',
            'quotation.currentVersion.items',
            'invoice.items',
            'project',
        ]);

        return (new ServiceOrderResource($serviceOrder))->response();
    }

    public function requestInformation(
        Request $request,
        ServiceOrder $serviceOrder,
        ReviewServiceOrderAction $action
    ): JsonResponse {
        $request->validate(['notes' => ['required', 'string', 'max:2000']]);

        $order = $action->requestInformation($serviceOrder, $request->input('notes'), $request->user());

        return (new ServiceOrderResource($order))->response();
    }

    public function attachQuotation(
        Request $request,
        ServiceOrder $serviceOrder,
        ReviewServiceOrderAction $action
    ): JsonResponse {
        $request->validate(['quotation_id' => ['required', 'integer', 'exists:quotations,id']]);

        $quotation = Quotation::findOrFail($request->input('quotation_id'));
        $order = $action->attachQuotation($serviceOrder, $quotation, $request->user());

        return (new ServiceOrderResource($order))->response();
    }

    public function confirmAvailability(
        Request $request,
        ServiceOrder $serviceOrder
    ): JsonResponse {
        $request->validate(['hold_hours' => ['nullable', 'integer', 'min:1', 'max:168']]);

        $holdHours = $request->integer('hold_hours', 48);
        $serviceOrder->slot_hold_until = now()->addHours($holdHours);
        $serviceOrder->save();

        ServiceOrderStatusLog::create([
            'service_order_id' => $serviceOrder->id,
            'from_status' => $serviceOrder->status,
            'to_status' => $serviceOrder->status,
            'actor_user_id' => $request->user()->id,
            'reason' => "Crew & date availability confirmed. Slot held for {$holdHours} hours.",
        ]);

        return (new ServiceOrderResource($serviceOrder->fresh(['brief', 'service', 'package', 'client'])))->response();
    }

    public function sendMessage(
        SendOrderMessageRequest $request,
        ServiceOrder $serviceOrder
    ): JsonResponse {
        $message = $serviceOrder->messages()->create([
            'sender_user_id' => $request->user()->id,
            'message' => $request->validated('message'),
            'attachments' => $request->validated('attachments'),
            'is_internal_note' => $request->boolean('is_internal_note'),
        ]);

        return (new ServiceOrderMessageResource($message->load('sender.roles')))->response()->setStatusCode(201);
    }

    public function cancel(
        Request $request,
        ServiceOrder $serviceOrder
    ): JsonResponse {
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $oldStatus = $serviceOrder->status;
        $serviceOrder->status = ServiceOrderStatus::Cancelled;
        $serviceOrder->cancelled_at = now();
        $serviceOrder->save();

        ServiceOrderStatusLog::create([
            'service_order_id' => $serviceOrder->id,
            'from_status' => $oldStatus,
            'to_status' => ServiceOrderStatus::Cancelled,
            'actor_user_id' => $request->user()->id,
            'reason' => $request->input('reason'),
        ]);

        return (new ServiceOrderResource($serviceOrder->fresh(['brief', 'service', 'package', 'client'])))->response();
    }
}
