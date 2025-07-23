<?php

namespace Tests\Feature\Utils;

use App\Models\CalendarCategory;

class CalendarCategoryTestUtils
{
    public static function calendarCategoryResponse(): array
    {
        return [
            'id',
            'name',
            'color_scheme',
        ];
    }

    public static function matchCalendarCategoryResponse(CalendarCategory|array $calendarCategory): array
    {
        return [
            'id' => $calendarCategory['id'],
            'name' => $calendarCategory['name'],
            'color_scheme' => $calendarCategory['color_scheme'],
        ];
    }
}
