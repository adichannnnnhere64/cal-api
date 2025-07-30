<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\V1\BulkSyncRequest;
use App\Http\Requests\Api\V1\CalendarEventStoreRequest;
use App\Http\Requests\Api\V1\CalendarEventUpdateRequest;
use App\Http\Resources\Api\CalendarEvent\CalendarEventResource;
use App\Services\Contracts\CalendarEventServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $events = $this->calendarEventService->getFilteredCalendarEvents(request())->where('user_id', Auth::user()->id);

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
    public function totalAmount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start_date' => 'string|required',
            'end_date' => 'string|required',
        ]);

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $total = $this->calendarEventService->getTotalAmount($startDate, $endDate);

        return $this->successResponse([
            'total_amount' => $total
        ]);
    }

    public function bulkSync(BulkSyncRequest $request): JsonResponse
    {
        $result = $this->calendarEventService->bulkSync($request->validated());

        return response()->json([
        'status'  => 'ok',
            'data'    => $result,
        ])->header('Access-Control-Allow-Origin', '*');

    }

    public function duplicate(Request $request)
    {
        $request->validate([
            'date'       => 'required|date',
            'targetDate' => 'nullable|date',
        ]);

        $sourceDate = Carbon::parse($request->input('date'));
        $targetDate = $request->filled('targetDate') ? Carbon::parse($request->input('targetDate')) : null;

        $duplicates = $this->calendarEventService->duplicate($sourceDate, $targetDate);

        return response()->json(['data' => $duplicates], 200);

    }

    public function importFromCsv(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $imported = $this->calendarEventService->importFromCsv($request->file('file'));

        return response()->json([
            'message' => 'CSV imported successfully.',
            'count'   => $imported->count(),
        ]);
    }
}
