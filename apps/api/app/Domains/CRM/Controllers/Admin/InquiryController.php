<?php

namespace App\Domains\CRM\Controllers\Admin;

use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Clients\Resources\AdminClientResource;
use App\Domains\CRM\Enums\InquiryStatus;
use App\Domains\CRM\Models\Inquiry;
use App\Domains\CRM\Requests\AssignInquiryRequest;
use App\Domains\CRM\Requests\ChangeInquiryStatusRequest;
use App\Domains\CRM\Requests\StoreInquiryRequest;
use App\Domains\CRM\Requests\UpdateInquiryRequest;
use App\Domains\CRM\Resources\InquiryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class InquiryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Inquiry::class);

        $inquiries = Inquiry::with(['service', 'package', 'client', 'assignedAdmin'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('service_id'), fn ($q) => $q->where('service_id', $request->input('service_id')))
            ->when($request->filled('assigned_admin_id'), fn ($q) => $q->where('assigned_admin_id', $request->input('assigned_admin_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('client_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('company_or_institution', 'like', $term)
                        ->orWhere('project_brief', 'like', $term);
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return InquiryResource::collection($inquiries);
    }

    public function store(StoreInquiryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $addOnIds = $data['add_on_ids'] ?? [];
        unset($data['add_on_ids']);

        $inquiry = Inquiry::create($data);

        if (! empty($addOnIds)) {
            $inquiry->addOns()->sync($addOnIds);
        }

        return (new InquiryResource($inquiry->load(['service', 'package', 'addOns', 'assignedAdmin'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Inquiry $inquiry): InquiryResource
    {
        Gate::authorize('view', $inquiry);

        $inquiry->load(['service', 'package', 'addOns', 'client', 'assignedAdmin']);

        return new InquiryResource($inquiry);
    }

    public function update(UpdateInquiryRequest $request, Inquiry $inquiry): InquiryResource
    {
        $data = $request->validated();
        $addOnIds = $data['add_on_ids'] ?? null;
        unset($data['add_on_ids']);

        $inquiry->update($data);

        if (is_array($addOnIds)) {
            $inquiry->addOns()->sync($addOnIds);
        }

        return new InquiryResource($inquiry->load(['service', 'package', 'addOns', 'client', 'assignedAdmin']));
    }

    public function changeStatus(ChangeInquiryStatusRequest $request, Inquiry $inquiry): InquiryResource|JsonResponse
    {
        $targetStatus = InquiryStatus::from($request->validated('status'));

        if (! $inquiry->status->canTransitionTo($targetStatus)) {
            return response()->json([
                'message' => "Invalid status transition from {$inquiry->status->value} to {$targetStatus->value}.",
                'allowed_transitions' => array_map(fn ($s) => $s->value, $inquiry->status->allowedTransitions()),
            ], 422);
        }

        $inquiry->status = $targetStatus;

        if ($targetStatus === InquiryStatus::Lost) {
            $inquiry->lost_reason = $request->validated('lost_reason');
        }

        if ($request->filled('notes_internal')) {
            $inquiry->notes_internal = $request->validated('notes_internal');
        }

        $inquiry->save();

        return new InquiryResource($inquiry->load(['service', 'package', 'addOns', 'client', 'assignedAdmin']));
    }

    public function assign(AssignInquiryRequest $request, Inquiry $inquiry): InquiryResource
    {
        $inquiry->update([
            'assigned_admin_id' => $request->validated('assigned_admin_id'),
        ]);

        return new InquiryResource($inquiry->load(['service', 'package', 'addOns', 'client', 'assignedAdmin']));
    }

    public function convertToClient(Inquiry $inquiry): JsonResponse
    {
        Gate::authorize('update', $inquiry);

        if ($inquiry->client_id) {
            $client = Client::find($inquiry->client_id);
            if ($client) {
                return (new AdminClientResource($client->load('users')))
                    ->response()
                    ->setStatusCode(200);
            }
        }

        $client = Client::firstOrCreate(
            ['email' => $inquiry->email],
            [
                'display_name' => $inquiry->client_name,
                'company_or_institution' => $inquiry->company_or_institution,
                'phone' => $inquiry->phone,
                'billing_name' => $inquiry->company_or_institution ?: $inquiry->client_name,
                'billing_email' => $inquiry->email,
                'billing_phone' => $inquiry->phone,
                'status' => ClientStatus::Active,
                'notes_internal' => "Converted from inquiry #{$inquiry->public_id}",
            ]
        );

        $inquiry->update(['client_id' => $client->id]);

        return (new AdminClientResource($client->load('users')))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Inquiry $inquiry): JsonResponse
    {
        Gate::authorize('delete', $inquiry);

        $inquiry->delete();

        return response()->json(['message' => 'Inquiry deleted successfully']);
    }
}
