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
            'colorScheme',
        ];
    }

    public static function matchcalendarEventResponse(CalendarEvent|array $calendarEvent): array
    {
        return [
            'id' => $calendarEvent['id'],
            'name' => $calendarEvent['name'],
            'colorScheme' => @$calendarEvent['color_scheme'] ?? @$calendarEvent['colorScheme'],
        ];
    }
}
