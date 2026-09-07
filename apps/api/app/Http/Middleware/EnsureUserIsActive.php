<?php

namespace App\Http\Middleware;

use App\Domains\Users\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // If user status is not Active (e.g. SUSPENDED, DISABLED, or INVITED)
            if ($user->status !== UserStatus::Active) {
                // Invalidate sanctum token if token authentication
                if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }

                // Invalidate session if session-based
                if ($request->hasSession()) {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return response()->json([
                    'message' => 'Your account is inactive, suspended, or disabled.',
                ], 403);
            }
        }

        return $next($request);
    }
}
