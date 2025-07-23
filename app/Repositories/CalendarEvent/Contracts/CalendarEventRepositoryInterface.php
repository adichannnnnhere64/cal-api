<?php

namespace App\Repositories\CalendarEvent\Contracts;

use App\Repositories\Base\Contracts\QueryableRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface CalendarEventRepositoryInterface extends QueryableRepositoryInterface
{
    /**
     * Return All Users
     */
    public function getCalendarEvents(): Collection;
}
