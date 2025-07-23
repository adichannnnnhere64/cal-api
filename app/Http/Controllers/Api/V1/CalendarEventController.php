<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\CalendarEventStoreRequest;
use App\Http\Requests\Api\V1\CalendarEventUpdateRequest;
use App\Http\Resources\Api\CalendarEvent\CalendarEventResource;
use App\Services\Contracts\CalendarEventServiceInterface;
use Illuminate\Http\JsonResponse;

class CalendarEventController extends BaseApiController
{
    /**
     * CalendarEventController constructor.
     */
    public function __construct(
        protected readonly CalendarEventServiceInterface $calendarEventService
    ) {}

    /**
     * Display a listing of the users with filtering, sorting, and pagination.
     */
    public function index(): JsonResponse
    {
        $events = $this->calendarEventService->getFilteredCalendarEvents(request());

        return $this->successResponse(CalendarEventResource::collection($events));
    }

    /**
     * Display all users.
     */
    public function all(): JsonResponse
    {
        $calendarEvents = $this->calendarEventService->getAllCalendarEvents();

        return $this->successResponse(CalendarEventResource::collection($calendarEvents));
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->calendarEventService->getCalendarEventById($id);

        return $this->successResponse(new CalendarEventResource($user));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(CalendarEventStoreRequest $request): JsonResponse
    {
        $user = $this->calendarEventService->createCalendarEvent($request->validated());

        return $this->createdResponse(new CalendarEventResource($user));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(CalendarEventUpdateRequest $request, int $id): JsonResponse
    {
        $user = $this->calendarEventService->updateCalendarEvent($id, $request->validated());

        return $this->successResponse(new CalendarEventResource($user));
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->calendarEventService->deleteCalendarEvent($id);

        return $this->noContentResponse();
    }

    /**
     * Display a listing of active users.
     */
    // public function active(): JsonResponse
    // {
    //     $calendarEvents = $this->calendarEventService->getActiveCalendarEvents();

    //     return $this->successResponse(CalendarEventResource::collection($calendarEvents));
    // }
}
