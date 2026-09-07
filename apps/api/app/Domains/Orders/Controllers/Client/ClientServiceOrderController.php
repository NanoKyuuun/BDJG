<?php

namespace App\Domains\Orders\Controllers\Client;

use App\Domains\Commercial\Actions\AcceptQuotationAction;
use App\Domains\Commercial\Actions\DeclineQuotationAction;
use App\Domains\Commercial\Actions\RequestQuotationRevisionAction;
use App\Domains\Orders\Actions\CreateServiceOrderAction;
use App\Domains\Orders\Actions\ReorderProjectAction;
use App\Domains\Orders\Actions\SubmitServiceOrderAction;
use App\Domains\Orders\Actions\UpdateServiceOrderBriefAction;
use App\Domains\Orders\Enums\OrderAttachmentType;
use App\Domains\Orders\Enums\ServiceOrderSource;
use App\Domains\Orders\Models\ServiceOrder;
use App\Domains\Orders\Requests\CreateServiceOrderRequest;
use App\Domains\Orders\Requests\SendOrderMessageRequest;
use App\Domains\Orders\Requests\UpdateServiceOrderBriefRequest;
use App\Domains\Orders\Resources\ServiceOrderAttachmentResource;
use App\Domains\Orders\Resources\ServiceOrderMessageResource;
use App\Domains\Orders\Resources\ServiceOrderResource;
use App\Domains\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientServiceOrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $client = $user->clients()->first();

        if (! $client) {
            abort(403, 'No client account associated with your user.');
        }

        $orders = ServiceOrder::where('client_id', $client->id)
            ->with(['brief', 'service', 'package', 'quotation.acceptedVersion', 'quotation.currentVersion', 'invoice', 'project'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return ServiceOrderResource::collection($orders);
    }

    public function store(CreateServiceOrderRequest $request, CreateServiceOrderAction $action): JsonResponse
    {
        $user = $request->user();
        $client = $user->clients()->first();

        if (! $client) {
            abort(403, 'No client account associated with your user.');
        }

        $source = $request->filled('source')
            ? ServiceOrderSource::from($request->input('source'))
            : ServiceOrderSource::Catalog;

        $briefCategory = $request->filled('brief_category')
            ? \App\Domains\Orders\Enums\BriefCategory::from($request->input('brief_category'))
            : \App\Domains\Orders\Enums\BriefCategory::Wedding;

        $order = $action->execute(
            client: $client,
            actor: $user,
            packageId: $request->input('package_id'),
            serviceId: $request->input('service_id'),
            source: $source,
            eventName: $request->input('event_name'),
            briefCategory: $briefCategory
        );

        return (new ServiceOrderResource($order))->response()->setStatusCode(201);
    }

    public function show(ServiceOrder $serviceOrder): JsonResponse
    {
        Gate::authorize('view', $serviceOrder);

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

    public function updateBrief(
        UpdateServiceOrderBriefRequest $request,
        ServiceOrder $serviceOrder,
        UpdateServiceOrderBriefAction $action
    ): JsonResponse {
        Gate::authorize('update', $serviceOrder);

        $order = $action->execute($serviceOrder, $request->validated(), $request->user());

        return (new ServiceOrderResource($order))->response();
    }

    public function submit(
        Request $request,
        ServiceOrder $serviceOrder,
        SubmitServiceOrderAction $action
    ): JsonResponse {
        Gate::authorize('update', $serviceOrder);

        $order = $action->execute($serviceOrder, $request->user());

        return (new ServiceOrderResource($order))->response();
    }

    public function uploadAttachment(Request $request, ServiceOrder $serviceOrder): JsonResponse
    {
        Gate::authorize('update', $serviceOrder);

        $request->validate([
            'file' => ['required', 'file', 'max:20480'], // 20 MB max
            'attachment_type' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . Str::random(8) . '.' . $extension;
        $storageKey = "orders/{$serviceOrder->id}/attachments/{$safeName}";

        Storage::disk('media')->put($storageKey, file_get_contents($file->getRealPath()));

        $attachment = $serviceOrder->attachments()->create([
            'uploaded_by_user_id' => $request->user()->id,
            'filename' => $safeName,
            'original_name' => $file->getClientOriginalName(),
            'storage_key' => $storageKey,
            'disk' => 'media',
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size_bytes' => $file->getSize(),
            'attachment_type' => $request->filled('attachment_type')
                ? OrderAttachmentType::tryFrom($request->input('attachment_type')) ?? OrderAttachmentType::Moodboard
                : OrderAttachmentType::Moodboard,
        ]);

        return (new ServiceOrderAttachmentResource($attachment->load('uploader')))->response()->setStatusCode(201);
    }

    public function sendMessage(
        SendOrderMessageRequest $request,
        ServiceOrder $serviceOrder
    ): JsonResponse {
        Gate::authorize('view', $serviceOrder);

        $message = $serviceOrder->messages()->create([
            'sender_user_id' => $request->user()->id,
            'message' => $request->validated('message'),
            'attachments' => $request->validated('attachments'),
            'is_internal_note' => false,
        ]);

        return (new ServiceOrderMessageResource($message->load('sender.roles')))->response()->setStatusCode(201);
    }

    public function acceptQuotation(
        Request $request,
        ServiceOrder $serviceOrder,
        AcceptQuotationAction $action
    ): JsonResponse {
        Gate::authorize('update', $serviceOrder);

        if (! $serviceOrder->quotation_id || ! $serviceOrder->quotation) {
            abort(404, 'No quotation linked to this order.');
        }

        $action->execute($serviceOrder->quotation, $request->user());

        $serviceOrder->status = \App\Domains\Orders\Enums\ServiceOrderStatus::AwaitingPayment;
        $serviceOrder->save();

        return (new ServiceOrderResource($serviceOrder->fresh(['brief', 'service', 'package', 'client', 'quotation', 'invoice'])))->response();
    }

    public function requestQuotationRevision(
        Request $request,
        ServiceOrder $serviceOrder,
        RequestQuotationRevisionAction $action
    ): JsonResponse {
        Gate::authorize('update', $serviceOrder);

        $request->validate(['notes' => ['required', 'string', 'max:2000']]);

        if (! $serviceOrder->quotation_id || ! $serviceOrder->quotation) {
            abort(404, 'No quotation linked to this order.');
        }

        $action->execute($serviceOrder->quotation, $request->input('notes'), $request->user());

        $serviceOrder->status = \App\Domains\Orders\Enums\ServiceOrderStatus::RevisionRequested;
        $serviceOrder->save();

        return (new ServiceOrderResource($serviceOrder->fresh(['brief', 'service', 'package', 'client', 'quotation'])))->response();
    }

    public function declineQuotation(
        Request $request,
        ServiceOrder $serviceOrder,
        DeclineQuotationAction $action
    ): JsonResponse {
        Gate::authorize('update', $serviceOrder);

        $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        if (! $serviceOrder->quotation_id || ! $serviceOrder->quotation) {
            abort(404, 'No quotation linked to this order.');
        }

        $action->execute($serviceOrder->quotation, $request->input('reason'), $request->user());

        $serviceOrder->status = \App\Domains\Orders\Enums\ServiceOrderStatus::Declined;
        $serviceOrder->save();

        return (new ServiceOrderResource($serviceOrder->fresh(['brief', 'service', 'package', 'client', 'quotation'])))->response();
    }

    public function reorder(
        Request $request,
        Project $project,
        ReorderProjectAction $action
    ): JsonResponse {
        Gate::authorize('view', $project);

        $order = $action->execute($project, $request->user());

        return (new ServiceOrderResource($order))->response()->setStatusCode(201);
    }
}
