<?php

namespace App\Domains\Schedules\Controllers\Admin;

use App\Domains\Schedules\Models\Schedule;
use App\Domains\Schedules\Requests\StoreScheduleRequest;
use App\Domains\Schedules\Resources\ScheduleResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ScheduleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Schedule::class);

        $schedules = Schedule::with('project')
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->input('project_id')))
            ->when($request->filled('schedule_type'), fn ($q) => $q->where('schedule_type', $request->input('schedule_type')))
            ->orderBy('start_time', 'asc')
            ->paginate($request->integer('per_page', 25));

        return ScheduleResource::collection($schedules);
    }

    public function store(StoreScheduleRequest $request): JsonResponse
    {
        $schedule = Schedule::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return (new ScheduleResource($schedule->load('project')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Schedule $schedule): ScheduleResource
    {
        Gate::authorize('view', $schedule);

        return new ScheduleResource($schedule->load('project'));
    }

    public function destroy(Schedule $schedule): JsonResponse
    {
        Gate::authorize('delete', $schedule);

        $schedule->delete();

        return response()->json(['message' => 'Schedule deleted successfully.']);
    }
}
