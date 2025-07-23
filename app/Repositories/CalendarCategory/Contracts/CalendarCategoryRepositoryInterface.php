<?php

namespace App\Repositories\CalendarCategory\Contracts;

use App\Repositories\Base\Contracts\QueryableRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface CalendarCategoryRepositoryInterface extends QueryableRepositoryInterface
{
    /**
     * Return All Users
     */
    public function getCalendarCategories(): Collection;
}
