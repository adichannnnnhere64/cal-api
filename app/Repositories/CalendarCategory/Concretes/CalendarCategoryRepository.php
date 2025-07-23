<?php

namespace App\Repositories\CalendarCategory\Concretes;

use App\Models\CalendarCategory;
use App\Models\CalendarEvent;
use App\Models\User;
use App\Repositories\Base\Concretes\QueryableRepository;
use App\Repositories\CalendarCategory\Contracts\CalendarCategoryRepositoryInterface;
use App\Repositories\User\Contracts\CalendarEventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;

class CalendarCategoryRepository extends QueryableRepository implements CalendarCategoryRepositoryInterface
{
    /**
     * Specify Model class name
     */
    protected function model(): string
    {
        return CalendarCategory::class;
    }

    /**
     * Return All Users
     */
    public function getCalendarCategories(): Collection
    {
        return $this->getFiltered();
    }



    /**
     * Get allowed filters for this repository.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            'name',
            'email',
        ];
    }

    /**
     * Get allowed sorts for this repository.
     */
    public function getAllowedSorts(): array
    {
        return ['id', 'name'];
    }

    /**
     * Get allowed includes for this repository.
     */
    public function getAllowedIncludes(): array
    {
        // Add any relationships you want to allow including
        // For example: 'posts', 'comments', etc.
        return [];
    }

    /**
     * Get allowed fields for this repository.
     */
    public function getAllowedFields(): array
    {
        return ['id', 'name', 'email'];
    }
}
