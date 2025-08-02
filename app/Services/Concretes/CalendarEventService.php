<?php

namespace App\Services\Concretes;

use App\Http\Resources\Api\CalendarEvent\CalendarEventResource;
use App\Models\CalendarEvent;
use App\Repositories\CalendarEvent\Contracts\CalendarEventRepositoryInterface;
use App\Services\Base\Concretes\BaseService;
use App\Services\Contracts\CalendarEventServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use League\Csv\Exception;
use League\Csv\Reader;

class CalendarEventService extends BaseService implements CalendarEventServiceInterface
{
    /**
     * CalendarEventService constructor.
     */
    public function __construct(protected CalendarEventRepositoryInterface $calendarEventRepository)
    {
        $this->setRepository($calendarEventRepository);
    }

      /**
     * Get all users
     */
    public function getCalendarEvents(): Collection
    {
        return $this->repository->getFiltered();
    }

    /**
     * Get all users
     */
    public function getAllCalendarEvents(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get filtered users with pagination
     */
    public function getFilteredCalendarEvents(?Request $request = null, int $perPage = 20000): LengthAwarePaginator
    {
        return $this->repository->paginateFiltered($perPage);
    }

    /**
     * Get user by ID
     */
    public function getCalendarEventById(int $id): ?Model
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('CalendarEvent not found');
        }
    }

    /**
     * Create user
     */
    public function createCalendarEvent(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * Update user
     */
    public function updateCalendarEvent(int $id, array $data): Model
    {
        try {
            return $this->repository->update($id, $data);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('CalendarEvent not found');
        }
    }

    /**
     * Delete user
     */
    public function deleteCalendarEvent(int $id): bool
    {
        try {
            $this->repository->delete($id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('CalendarEvent not found');
        }
    }

    public function getTotalAmount(Carbon $startDate, Carbon $endDate): float
    {
        return $this->calendarEventRepository->all()->whereBetween('date', [$startDate, $endDate])->sum('amount');
    }

    // /**
    //  * Get active users
    //  */
    // public function getActiveCalendarEvents(): Collection
    // {
    //     // return $this->calendarEventRepository->getActiveCalendarEvents();
    // }

        /**
     * Bulk sync events coming from the client.
     *
     * Expected payload shape:
     * [
     *   'created' => [ [event data w/o id, 'categories' => [1,2]] ],
     *   'updated' => [ [ 'id' => 1, ...fields, 'categories' => [1,3] ] ],
     *   'deleted' => [1,2,3]
     * ]
     *
     * @return array{created: array<int>, updated: array<int>, deleted: array<int>}
     */
    public function bulkSync(array $payload): array
    {
        $createdIds = [];
        $updatedIds = [];
        $deletedIds = $payload['deleted'] ?? [];

        DB::transaction(function () use ($payload, &$createdIds, &$updatedIds, $deletedIds) {
            // CREATE
            foreach ($payload['created'] ?? [] as $data) {
                $categories = Arr::pull($data, 'categories', []);
                /** @var \App\Models\CalendarEvent $event */
                $event = $this->calendarEventRepository->create($data);
                $this->syncCategories($event, $categories);
                $createdIds[] = $event->id;
            }


                // \Log::info($payload['updated']);
                // \Log::info('ramon buratilya');
            // UPDATE
            foreach ($payload['updated'] ?? [] as $data) {
                /* \Log::info($data); */
                $id = Arr::get($data, 'id');

                if (!$id) {
                    continue;
                }

                $categories = Arr::pull($data, 'categories', []);
                /** @var \App\Models\CalendarEvent $event */
                $event = $this->calendarEventRepository->update($id, $data);
                $this->syncCategories($event, $categories);
                $updatedIds[] = $event->id;
            }

            // DELETE
            if (!empty($deletedIds)) {
                $this->calendarEventRepository->deleteMany($deletedIds);
            }
        });

        return [
            'created' => $createdIds,
            'updated' => $updatedIds,
            'deleted' => $deletedIds,
        ];
    }

    protected function syncCategories(CalendarEvent $event, array $categoryIds): void
    {
        if (method_exists($event, 'categories')) {
            $event->categories()->sync($categoryIds);
        }
    }

    public function duplicate(Carbon $sourceDate, ?Carbon $targetDate = null): \Illuminate\Support\Collection
    {
        $targetDate ??= $sourceDate->copy()->addMonth();

        $events = CalendarEvent::with('categories')
            ->where('user_id', Auth::user()->id)
            ->whereYear('date', $sourceDate->year)
            ->whereMonth('date', $sourceDate->month)
            ->get();

        $duplicates = collect();

        foreach ($events as $event) {
            $originalDay = Carbon::parse($event->date)->day;

            $safeDay = min(
                $originalDay,
                Carbon::create($targetDate->year, $targetDate->month)->endOfMonth()->day
            );

            $newDate = Carbon::create(
                year: $targetDate->year,
                month: $targetDate->month,
                day: $safeDay
            )->format('Y-m-d');

            $duplicate = $event->replicate();
            $duplicate->date = $newDate;
            $duplicate->created_at = now();
            $duplicate->updated_at = now();
            $duplicate->save();

            \Log::info($event->categories);
            \Log::info('adi here');
            $duplicate->categories()->sync($event->categories->pluck('id'));

            \Log::info($duplicate->categories);


            $duplicates->push(CalendarEventResource::make($duplicate));
        }


        return $duplicates;
    }


    public function importFromCsv(UploadedFile $csv): \Illuminate\Support\Collection
    {
        try {
            $reader = Reader::createFromPath($csv->getRealPath(), 'r');
            $reader->setHeaderOffset(0);

            $records = collect();
            foreach ($reader->getRecords() as $record) {
                $record['user_id'] = Auth::user()->id;
                $record['amount'] = is_numeric($record['amount']) ? (float) $record['amount'] : null;
                $record['date'] = Carbon::parse($record['date'])->format('Y-m-d');

                $event = CalendarEvent::create([
                    'name'         => $record['name'],
                    'amount'       => $record['amount'],
                    'description'  => $record['description'],
                    'color_scheme' => $record['color_scheme'],
                    'date'         => $record['date'],
                    'user_id'      => $record['user_id'],
                ]);

                $records->push($event);
            }

            return $records;
        } catch (Exception $e) {
            report($e);
            throw new \RuntimeException('Invalid CSV file.');
        }
    }

}
