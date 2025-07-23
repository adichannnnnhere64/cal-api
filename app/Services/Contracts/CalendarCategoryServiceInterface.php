<?php

namespace App\Services\Contracts;

use App\Services\Base\Contracts\BaseServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface CalendarCategoryServiceInterface extends BaseServiceInterface
{
    public function getAllCalendarCategories(): Collection;

    public function getFilteredCalendarCategories(?Request $request = null, int $perPage = 15): LengthAwarePaginator;

    public function getCalendarCategoryById(int $id): ?Model;

    public function createCalendarCategory(array $data): Model;

    public function updateCalendarCategory(int $id, array $data): Model;

    public function deleteCalendarCategory(int $id): bool;

    // public function getActiveCalendarCategories(): Collection;
}
