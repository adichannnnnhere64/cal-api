<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\CalendarCategoryStoreRequest;
use App\Http\Requests\Api\V1\CalendarCategoryUpdateRequest;
use App\Http\Resources\Api\CalendarCategory\CalendarCategoryResource;
use App\Services\Contracts\CalendarCategoryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CalendarCategoryController extends BaseApiController
{
    /**
     * CalendarCategoryController constructor.
     */
    public function __construct(
        protected readonly CalendarCategoryServiceInterface $calendarEventService
    ) {}

    /**
     * Display a listing of the users with filtering, sorting, and pagination.
     */
    public function index(): JsonResponse
    {
        $events = $this->calendarEventService->getFilteredCalendarCategories(request())->where('user_id', Auth::user()->id);

        return $this->successResponse(CalendarCategoryResource::collection($events));
    }

    /**
     * Display all users.
     */
    public function all(): JsonResponse
    {
        $calendarEvents = $this->calendarEventService->getAllCalendarCategories();

        return $this->successResponse(CalendarCategoryResource::collection($calendarEvents));
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->calendarEventService->getCalendarCategoryById($id);

        return $this->successResponse(new CalendarCategoryResource($user));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(CalendarCategoryStoreRequest $request): JsonResponse
    {
        $user = $this->calendarEventService->createCalendarCategory($request->validated());

        return $this->createdResponse(new CalendarCategoryResource($user));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(CalendarCategoryUpdateRequest $request, int $id): JsonResponse
    {
        $user = $this->calendarEventService->updateCalendarCategory($id, $request->validated());

        return $this->successResponse(new CalendarCategoryResource($user));
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->calendarEventService->deleteCalendarCategory($id);

        return $this->noContentResponse();
    }

    /**
     * Display a listing of active users.
     */
    // public function active(): JsonResponse
    // {
    //     $calendarEvents = $this->calendarEventService->getActiveCalendarCategories();

    //     return $this->successResponse(CalendarCategoryResource::collection($calendarEvents));
    // }
}
