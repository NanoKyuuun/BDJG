<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $user = Auth::user();

        return (new UserResource($user))
            ->response()
            ->setStatusCode(200);
    }
}
