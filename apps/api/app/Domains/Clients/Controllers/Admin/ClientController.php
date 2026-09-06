<?php

namespace App\Domains\Clients\Controllers\Admin;

use App\Domains\Clients\Models\Client;
use App\Domains\Clients\Requests\StoreClientRequest;
use App\Domains\Clients\Requests\UpdateClientRequest;
use App\Domains\Clients\Resources\AdminClientResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ClientController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Client::class);

        $clients = Client::with('users')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->input('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('display_name', 'like', $term)
                        ->orWhere('company_or_institution', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return AdminClientResource::collection($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userIds = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $client = Client::create($data);

        if (! empty($userIds)) {
            $client->users()->sync($userIds);
        }

        return (new AdminClientResource($client->load('users')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Client $client): AdminClientResource
    {
        Gate::authorize('view', $client);

        return new AdminClientResource($client->load('users'));
    }

    public function update(UpdateClientRequest $request, Client $client): AdminClientResource
    {
        Gate::authorize('update', $client);

        $data = $request->validated();
        $userIds = $data['user_ids'] ?? null;
        unset($data['user_ids']);

        $client->update($data);

        if (is_array($userIds)) {
            $client->users()->sync($userIds);
        }

        return new AdminClientResource($client->load('users'));
    }

    public function destroy(Client $client): JsonResponse
    {
        Gate::authorize('delete', $client);

        $client->delete();

        return response()->json(['message' => 'Client deleted successfully']);
    }
}
