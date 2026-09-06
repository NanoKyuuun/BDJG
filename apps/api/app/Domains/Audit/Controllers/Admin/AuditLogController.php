<?php

namespace App\Domains\Audit\Controllers\Admin;

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Audit\Resources\AuditLogResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        if (! $user->hasRole('OWNER') && ! $user->hasRole('ADMIN')) {
            abort(403, 'Unauthorized.');
        }

        $query = AuditLog::with('user')->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_email', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        return AuditLogResource::collection($query->paginate(25));
    }
}
