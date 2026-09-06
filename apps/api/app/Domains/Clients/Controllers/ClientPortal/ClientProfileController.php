<?php

namespace App\Domains\Clients\Controllers\ClientPortal;

use App\Domains\Clients\Requests\UpdateClientProfileRequest;
use App\Domains\Clients\Resources\ClientProfileResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClientProfileController extends Controller
{
    public function show(Request $request): ClientProfileResource|JsonResponse
    {
        $user = $request->user();
        $client = $user->primaryClient();

        if (! $client) {
            return response()->json(['message' => 'No associated client entity found'], 404);
        }

        Gate::authorize('view', $client);

        return new ClientProfileResource($client);
    }

    public function update(UpdateClientProfileRequest $request): ClientProfileResource|JsonResponse
    {
        $user = $request->user();
        $client = $user->primaryClient();

        if (! $client) {
            return response()->json(['message' => 'No associated client entity found'], 404);
        }

        Gate::authorize('update', $client);

        $client->update($request->validated());

        return new ClientProfileResource($client);
    }
}
