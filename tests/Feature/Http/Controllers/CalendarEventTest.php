<?php

use App\Models\CalendarCategory;
use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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


    $response = authedUser()->postJson('/api/v1/calendar-events',  $calendarEvent->toArray() );
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
        'user_id' => 1
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

it('duplicates all events in a month into target month, adjusting invalid days', function () {
    // Create two events in December
    CalendarEvent::factory()->create([
        'amount'  => 69,
        'date'    => '2024-12-12',
        'user_id' => 1,
    ]);

    CalendarEvent::factory()->create([
        'amount'  => 42,
        'date'    => '2024-12-31',
        'user_id' => 1,
    ]);

    // This should not be duplicated
    CalendarEvent::factory()->create([
        'amount'  => 99,
        'date'    => '2024-11-30',
        'user_id' => 1,
    ]);

    // Case 1: with target date (February 2025)
    authedUser()
        ->postJson('/api/v1/calendar-events/duplicate', [
            'date'       => '2024-12-01',
            'targetDate' => '2025-02-01',
        ])
        ->assertStatus(200);

        // dd(CalendarEvent::all()->pluck('date'));

    $this->assertDatabaseHas('calendar_events', [
        'date'    => '2025-02-12',
        'amount'  => 69,
        'user_id' => 1,
    ]);

    $this->assertDatabaseHas('calendar_events', [
        'date'    => '2025-02-28', // Adjusted from Dec 31
        'amount'  => 42,
        'user_id' => 1,
    ]);

    $this->assertDatabaseMissing('calendar_events', [
        'date'    => '2025-02-30',
        'amount'  => 42,
    ]);

    // Case 2: no targetDate (should default to January)
    CalendarEvent::factory()->create([
        'amount'  => 100,
        'date'    => '2024-12-15',
        'user_id' => 1,
    ]);

    authedUser()
        ->postJson('/api/v1/calendar-events/duplicate', [
            'date' => '2024-12-01',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('calendar_events', [
        'date'   => '2025-01-15',
        'amount' => 100,
    ]);
});


it('imports calendar events from a CSV file', function () {
    Storage::fake('local');

    $csvContent = <<<CSV
name,amount,description,color_scheme,date
Lunch,150.50,Lunch with clients,blue,2025-07-15
Groceries,89.99,Weekly grocery run,green,2025-07-20
Transport,20.00,Taxi fare,red,2025-07-21
CSV;

    $file = UploadedFile::fake()->createWithContent('events.csv', $csvContent);

    $response = authedUser()->postJson('/api/v1/calendar-events/import', [
        'file' => $file,
    ]);

    $response->assertStatus(200);

    expect(CalendarEvent::count())->toBe(3);

    $this->assertDatabaseHas('calendar_events', [
        'name'         => 'Lunch',
        'amount'       => 150.50,
        'color_scheme' => 'blue',
        'date'         => '2025-07-15',
    ]);

    $this->assertDatabaseHas('calendar_events', [
        'name' => 'Groceries',
        'amount' => 89.99,
        'date' => '2025-07-20',
    ]);
});
