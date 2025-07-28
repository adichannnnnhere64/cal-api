<?php

use App\Models\CalendarCategory;
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

it('returns calendar event with categories filter', function () {
    $calenderEvent = CalendarEvent::factory()->create([
        'name' => 'laravel-starter'
    ]);
    $category = CalendarCategory::factory()->create();
    // $category2 = CalendarCategory::factory()->create();

    $calenderEvent->categories()->sync([$category->id]);

    authedUser()->getJson(apiRoute('calendar-events?filter[categories]='. $category->id))
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                0 => [
                    'name' => 'laravel-starter',
                ],
            ],
        ]);
});

it('returns calendar events that match any of the given category IDs', function () {
    $event1 = CalendarEvent::factory()->create(['name' => 'event-1']);
    $event2 = CalendarEvent::factory()->create(['name' => 'event-2']);
    $event3 = CalendarEvent::factory()->create(['name' => 'event-3']);

    $categoryA = CalendarCategory::factory()->create(); // id = 1
    $categoryB = CalendarCategory::factory()->create(); // id = 2
    $categoryC = CalendarCategory::factory()->create(); // id = 3

    // Associate events with categories
    $event1->categories()->sync([$categoryA->id]); // should match
    $event2->categories()->sync([$categoryB->id]); // should match
    $event3->categories()->sync([$categoryC->id]); // should NOT match

    $response = authedUser()->getJson(
        apiRoute('calendar-events?filter[categories]=' . $categoryA->id . ',' . $categoryB->id)
    );

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'event-1'])
        ->assertJsonFragment(['name' => 'event-2'])
        ->assertJsonMissing(['name' => 'event-3']);
});


it('returns empty when filtering with a non-matching category id', function () {
    $event = CalendarEvent::factory()->create();
    $category = CalendarCategory::factory()->create(); // not related to event

    // Do NOT associate the event with the category
    authedUser()->getJson(apiRoute('calendar-events?filter[categories]=' . $category->id))
        ->assertStatus(200)
        ->assertJson([
            'data' => [],
        ]);
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

it('shows a calendar event by total amount', function () {
    $calendarEvent = CalendarEvent::factory()->create([
        'amount' => 69,
        'date'   => '2024-10-12',
    ]);

    authedUser()
        ->getJson("/api/v1/calendar-events/total-amount?start_date=2024-10-10&end_date=2024-11-11")
        ->assertStatus(200)
        ->assertJson([
            'data' => [
                'total_amount' => 69,
            ],
        ]);
});
