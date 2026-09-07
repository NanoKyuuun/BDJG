<?php

namespace App\Actions\Fortify;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Clients\Enums\ClientStatus;
use App\Domains\Clients\Models\Client;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            // 1. Create User account with CLIENT role
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'status' => UserStatus::Active,
            ]);

            $user->assignRole('CLIENT');

            // 2. Automatically create associated Client domain entity
            $client = Client::create([
                'display_name' => $input['name'],
                'email' => $input['email'],
                'billing_name' => $input['name'],
                'billing_email' => $input['email'],
                'status' => ClientStatus::Active,
            ]);

            // 3. Link User and Client via client_users pivot as primary user
            $user->clients()->attach($client->id, ['is_primary' => true]);

            AuditLogger::log(
                action: 'USER_REGISTERED',
                description: "Client account and profile for {$user->email} successfully registered.",
                auditable: $user,
                newValues: [
                    'user_id' => $user->id,
                    'client_id' => $client->id,
                    'email' => $user->email,
                ]
            );

            return $user;
        });
    }
}
