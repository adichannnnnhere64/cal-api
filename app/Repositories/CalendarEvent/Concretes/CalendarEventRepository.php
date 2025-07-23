<?php

namespace App\Repositories\CalendarEvent\Concretes;

use App\Models\CalendarEvent;
use App\Models\User;
use App\Repositories\Base\Concretes\QueryableRepository;
use App\Repositories\CalendarEvent\Contracts\CalendarEventRepositoryInterface as ContractsCalendarEventRepositoryInterface;
use App\Repositories\User\Contracts\CalendarEventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;

class CalendarEventRepository extends QueryableRepository implements ContractsCalendarEventRepositoryInterface
{
    /**
     * Specify Model class name
     */
    protected function model(): string
    {
        return CalendarEvent::class;
    }

    /**
     * Return All Users
     */
    public function getCalendarEvents(): Collection
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
