<?php

use App\Models\CalendarEvent;
use App\Models\User;
use Tests\Feature\Utils\CalendarEventTestUtils;

it('returns calendar event without filters', function () {

    $cal = CalendarEvent::factory()->create();

    $response = authedUser()->getJson(apiRoute('calendar-events'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => CalendarEventTestUtils::calendarEventResponse(),
        ],
    ]);
});

it('creates new calendar event', function () {
    $calendarEvent = CalendarEvent::factory()->make();

    $response = authedUser()->postJson('/api/v1/calendar-events', $calendarEvent->toArray());
    $createdCalendarEvent = $response->original['data'];

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => CalendarEventTestUtils::calendarEventResponse(),
        ])
        ->assertJson([
            'data' => CalendarEventTestUtils::matchCalendarEventResponse($createdCalendarEvent),
        ]);

    $this->assertDatabaseHas('calendar_events', ['id' => $createdCalendarEvent['id']]);
});

it('updates an existing calendar event', function () {
    $calendarEvent = CalendarEvent::factory()->create()->first();
    $calendarEvent->name = 'cal-event-updated';

    authedUser()
        ->putJson("/api/v1/calendar-events/$calendarEvent->id", $calendarEvent->toArray())
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => CalendarEventTestUtils::calendarEventResponse(),
        ])
        ->assertJson([
            'data' => CalendarEventTestUtils::matchCalendarEventResponse($calendarEvent ?? []),
        ]);

    $this->assertDatabaseHas('calendar_events', ['name' => $calendarEvent['name']]);
});


it('deletes an existing calendar event', function () {
    $calendarEvent = CalendarEvent::factory()->create()->first();

    authedUser()
        ->deleteJson("/api/v1/calendar-events/$calendarEvent->id")
        ->assertNoContent();

    $this->assertDatabaseMissing('calendar_events', ['id' => $calendarEvent['id']]);
});

it('shows a calendar event by id', function () {
    $calendarEvent = CalendarEvent::factory(1)->create()->first();

    authedUser()->getJson("/api/v1/calendar-events/$calendarEvent->id")
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => CalendarEventTestUtils::calendarEventResponse(),
        ])
        ->assertJson([
            'data' => CalendarEventTestUtils::matchCalendarEventResponse($calendarEvent ?? []),
        ]);
});