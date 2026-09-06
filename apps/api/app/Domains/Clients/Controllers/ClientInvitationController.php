<?php

namespace App\Domains\Clients\Controllers;

use App\Domains\Clients\Models\Client;
use App\Domains\Clients\Models\ClientInvitation;
use App\Domains\Clients\Requests\AcceptClientInvitationRequest;
use App\Domains\Clients\Requests\InviteClientUserRequest;
use App\Domains\Clients\Resources\ClientInvitationResource;
use App\Domains\Users\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class ClientInvitationController extends Controller
{
    public function invite(InviteClientUserRequest $request, Client $client): JsonResponse
    {
        Gate::authorize('update', $client);

        $invitation = ClientInvitation::create([
            'client_id' => $client->id,
            'email' => $request->validated('email'),
            'expires_at' => now()->addDays(7),
            'created_by' => $request->user()?->id,
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to($invitation->email)
                ->send(new \App\Mail\ClientInvitationMail($client, $invitation));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to send client invitation email: " . $e->getMessage());
        }

        return (new ClientInvitationResource($invitation->load('client')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(string $token): ClientInvitationResource|JsonResponse
    {
        $invitation = ClientInvitation::where('token', $token)->first();

        if (! $invitation || ! $invitation->isValid()) {
            return response()->json(['message' => 'Invitation token is invalid or expired.'], 404);
        }

        return new ClientInvitationResource($invitation->load('client'));
    }

    public function accept(AcceptClientInvitationRequest $request, string $token): JsonResponse
    {
        $invitation = ClientInvitation::where('token', $token)->first();

        if (! $invitation || ! $invitation->isValid()) {
            return response()->json(['message' => 'Invitation token is invalid or expired.'], 422);
        }

        $user = DB::transaction(function () use ($invitation, $request) {
            $user = User::firstOrCreate(
                ['email' => $invitation->email],
                [
                    'name' => $request->validated('name'),
                    'password' => Hash::make($request->validated('password')),
                    'status' => UserStatus::Active,
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole('CLIENT')) {
                $user->assignRole('CLIENT');
            }

            $invitation->client->users()->syncWithoutDetaching([
                $user->id => ['is_primary' => true],
            ]);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        return response()->json([
            'message' => 'Account successfully activated.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }
}
