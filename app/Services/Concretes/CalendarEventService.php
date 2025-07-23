<?php

namespace App\Services\Concretes;

use App\Repositories\CalendarEvent\Contracts\CalendarEventRepositoryInterface;
use App\Services\Base\Concretes\BaseService;
use App\Services\Contracts\CalendarEventServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
    public function getFilteredCalendarEvents(?Request $request = null, int $perPage = 15): LengthAwarePaginator
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

    // /**
    //  * Get active users
    //  */
    // public function getActiveCalendarEvents(): Collection
    // {
    //     // return $this->calendarEventRepository->getActiveCalendarEvents();
    // }

}