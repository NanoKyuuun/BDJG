<?php

namespace App\Domains\Users\Controllers\Admin;

use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Users\Enums\UserStatus;
use App\Domains\Users\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        if (! $user->hasRole('OWNER') && ! $user->hasRole('ADMIN')) {
            abort(403, 'Unauthorized.');
        }

        $query = User::with('roles')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('role')) {
            $query->role($request->query('role'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return UserResource::collection($query->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->hasRole('OWNER') && ! $actor->hasRole('ADMIN')) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,name'],
            'status' => ['nullable', new Enum(UserStatus::class)],
        ]);

        // Security check: Only an OWNER can grant the OWNER role
        if (in_array('OWNER', $validated['roles']) && ! $actor->hasRole('OWNER')) {
            abort(403, 'Only an Owner can assign the Owner role.');
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $actor) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => $validated['status'] ?? UserStatus::Active,
            ]);

            $user->syncRoles($validated['roles']);

            AuditLogger::log(
                action: 'USER_CREATED',
                description: "User account {$user->email} created by {$actor->name}.",
                auditable: $user,
                newValues: [
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $validated['roles'],
                    'status' => $user->status->value,
                ]
            );

            return response()->json([
                'message' => 'User created successfully.',
                'data' => new UserResource($user),
            ], 201);
        });
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->hasRole('OWNER') && ! $actor->hasRole('ADMIN')) {
            abort(403, 'Unauthorized.');
        }

        $user->load('roles');

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->hasRole('OWNER') && ! $actor->hasRole('ADMIN')) {
            abort(403, 'Unauthorized.');
        }

        $user->load('roles');

        // Security check: Non-Owner cannot modify an Owner account
        if ($user->hasRole('OWNER') && ! $actor->hasRole('OWNER')) {
            abort(403, 'Only an Owner can modify an Owner account.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,name'],
            'status' => ['sometimes', new Enum(UserStatus::class)],
        ]);

        // Security check: Only an OWNER can grant the OWNER role
        if (isset($validated['roles']) && in_array('OWNER', $validated['roles']) && ! $actor->hasRole('OWNER')) {
            abort(403, 'Only an Owner can assign the Owner role.');
        }

        // Security check: Cannot remove OWNER role if user is the last remaining OWNER
        if (isset($validated['roles']) && $user->hasRole('OWNER') && ! in_array('OWNER', $validated['roles'])) {
            $ownerCount = User::role('OWNER')->where('status', UserStatus::Active)->count();
            if ($ownerCount <= 1) {
                return response()->json([
                    'message' => 'Cannot remove Owner role from the last remaining active Owner.',
                ], 422);
            }
        }

        // Security check: Cannot deactivate self
        if (isset($validated['status']) && $validated['status'] !== UserStatus::Active && $actor->id === $user->id) {
            return response()->json([
                'message' => 'You cannot deactivate or suspend your own account.',
            ], 422);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($user, $validated, $actor) {
            $oldRoles = $user->roles->pluck('name')->all();
            $oldStatus = $user->status->value;

            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }
            if (! empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            if (isset($validated['status'])) {
                $user->status = $validated['status'];
            }
            $user->save();

            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            AuditLogger::log(
                action: 'USER_UPDATED',
                description: "User account {$user->email} updated by {$actor->name}.",
                auditable: $user,
                oldValues: ['roles' => $oldRoles, 'status' => $oldStatus],
                newValues: ['roles' => $validated['roles'] ?? $oldRoles, 'status' => $user->status->value]
            );

            return response()->json([
                'message' => 'User updated successfully.',
                'data' => new UserResource($user->fresh('roles')),
            ]);
        });
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->hasRole('OWNER') && ! $actor->hasRole('ADMIN')) {
            abort(403, 'Unauthorized.');
        }

        $user->load('roles');

        // Security check: Non-Owner cannot modify an Owner account
        if ($user->hasRole('OWNER') && ! $actor->hasRole('OWNER')) {
            abort(403, 'Only an Owner can change the status of an Owner account.');
        }

        // Security check: Cannot deactivate self
        if ($actor->id === $user->id) {
            return response()->json([
                'message' => 'You cannot change your own account status.',
            ], 422);
        }

        $validated = $request->validate([
            'status' => ['required', new Enum(UserStatus::class)],
        ]);

        // Security check: Cannot deactivate the last remaining active OWNER
        if ($user->hasRole('OWNER') && $validated['status'] !== UserStatus::Active) {
            $activeOwnerCount = User::role('OWNER')->where('status', UserStatus::Active)->count();
            if ($activeOwnerCount <= 1) {
                return response()->json([
                    'message' => 'Cannot deactivate the last remaining active Owner.',
                ], 422);
            }
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($user, $validated, $actor) {
            $oldStatus = $user->status->value;
            $user->status = $validated['status'];
            $user->save();

            AuditLogger::log(
                action: 'USER_STATUS_CHANGED',
                description: "User {$user->email} status changed from {$oldStatus} to {$user->status->value} by {$actor->name}.",
                auditable: $user,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $user->status->value]
            );

            return response()->json([
                'message' => 'User status updated successfully.',
                'data' => new UserResource($user->fresh('roles')),
            ]);
        });
    }

    public function roles(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->hasRole('OWNER') && ! $actor->hasRole('ADMIN')) {
            abort(403, 'Unauthorized.');
        }

        $roles = Role::withCount('permissions')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions_count,
            ];
        });

        return response()->json([
            'data' => $roles,
        ]);
    }
}
