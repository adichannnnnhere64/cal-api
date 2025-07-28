<?php

namespace App\Services\Concretes;

use App\Models\CalendarEvent;
use App\Repositories\CalendarEvent\Contracts\CalendarEventRepositoryInterface;
use App\Services\Base\Concretes\BaseService;
use App\Services\Contracts\CalendarEventServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
                \Log::info($data);
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

}