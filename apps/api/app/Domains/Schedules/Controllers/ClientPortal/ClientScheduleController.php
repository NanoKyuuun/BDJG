<?php

namespace App\Domains\Schedules\Controllers\ClientPortal;

use App\Domains\Schedules\Models\Schedule;
use App\Domains\Schedules\Resources\ScheduleResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientScheduleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $clientIds = $user->clients()->pluck('clients.id')->all();

        $schedules = Schedule::with(['project'])
            ->whereHas('project', function ($q) use ($clientIds) {
                $q->whereIn('client_id', $clientIds);
            })
            ->orderBy('start_time', 'asc')
            ->get();

        return ScheduleResource::collection($schedules);
    }
}
