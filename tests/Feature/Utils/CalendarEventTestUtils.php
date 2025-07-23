<?php

namespace Tests\Feature\Utils;

use App\Models\CalendarEvent;

class CalendarEventTestUtils
{
    public static function calendarEventResponse(): array
    {
        return [
            'id',
            'name',
            'color_scheme',
        ];
    }

    public static function matchcalendarEventResponse(CalendarEvent|array $calendarEvent): array
    {
        return [
            'id' => $calendarEvent['id'],
            'name' => $calendarEvent['name'],
            'color_scheme' => $calendarEvent['color_scheme'],
        ];
    }
}
