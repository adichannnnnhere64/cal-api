<?php

namespace App\Services\Concretes;

use App\Repositories\CalendarCategory\Contracts\CalendarCategoryRepositoryInterface;
use App\Services\Base\Concretes\BaseService;
use App\Services\Contracts\CalendarCategoryServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CalendarCategoryService extends BaseService implements CalendarCategoryServiceInterface
{
    /**
     * CalendarCategoryService constructor.
     */
    public function __construct(protected CalendarCategoryRepositoryInterface $calendarEventRepository)
    {
        $this->setRepository($calendarEventRepository);
    }

      /**
     * Get all users
     */
    public function getCalendarCategories(): Collection
    {
        return $this->repository->getFiltered();
    }

    /**
     * Get all users
     */
    public function getAllCalendarCategories(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get filtered users with pagination
     */
    public function getFilteredCalendarCategories(?Request $request = null, int $perPage = 20000): LengthAwarePaginator
    {
        return $this->repository->paginateFiltered($perPage);
    }

    /**
     * Get user by ID
     */
    public function getCalendarCategoryById(int $id): ?Model
    {
        try {
            return $this->repository->findOrFail($id);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('CalendarCategory not found');
        }
    }

    /**
     * Create user
     */
    public function createCalendarCategory(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * Update user
     */
    public function updateCalendarCategory(int $id, array $data): Model
    {
        try {
            return $this->repository->update($id, $data);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('CalendarCategory not found');
        }
    }

    /**
     * Delete user
     */
    public function deleteCalendarCategory(int $id): bool
    {
        try {
            $this->repository->delete($id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('CalendarCategory not found');
        }
    }

    // /**
    //  * Get active users
    //  */
    // public function getActiveCalendarCategories(): Collection
    // {
    //     // return $this->calendarEventRepository->getActiveCalendarCategories();
    // }

}