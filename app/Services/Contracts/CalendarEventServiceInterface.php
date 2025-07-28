<?php

namespace App\Services\Contracts;

use App\Services\Base\Contracts\BaseServiceInterface;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

interface CalendarEventServiceInterface extends BaseServiceInterface
{
    public function getAllCalendarEvents(): Collection;

    public function getFilteredCalendarEvents(?Request $request = null, int $perPage = 15): LengthAwarePaginator;

    public function getCalendarEventById(int $id): ?Model;

    public function createCalendarEvent(array $data): Model;

    public function updateCalendarEvent(int $id, array $data): Model;

    public function deleteCalendarEvent(int $id): bool;

    public function getTotalAmount(Carbon $startDate, Carbon $endDate): float;

    public function bulkSync(array $data): array;
}
