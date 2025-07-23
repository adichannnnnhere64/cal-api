<?php

use App\Models\CalendarCategory;
use Tests\Feature\Utils\CalendarCategoryTestUtils;

it('returns calendar category without filters', function () {

    $cal = CalendarCategory::factory()->create();

    $response = authedUser()->getJson(apiRoute('calendar-categories'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => CalendarCategoryTestUtils::calendarCategoryResponse(),
        ],
    ]);
});

it('creates new calendar category', function () {
    $calendarCategory = CalendarCategory::factory()->make();

    $response = authedUser()->postJson('/api/v1/calendar-categories', $calendarCategory->toArray());
    $createdCalendarCategory = $response->original['data'];

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => CalendarCategoryTestUtils::calendarCategoryResponse(),
        ])
        ->assertJson([
            'data' => CalendarCategoryTestUtils::matchCalendarCategoryResponse($createdCalendarCategory),
        ]);

    $this->assertDatabaseHas('calendar_categories', ['id' => $createdCalendarCategory['id']]);
});

it('updates an existing calendar event', function () {
    $calendarCategory = CalendarCategory::factory()->create()->first();
    $calendarCategory->name = 'cal-event-updated';

    authedUser()
        ->putJson("/api/v1/calendar-categories/$calendarCategory->id", $calendarCategory->toArray())
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => CalendarCategoryTestUtils::calendarCategoryResponse(),
        ])
        ->assertJson([
            'data' => CalendarCategoryTestUtils::matchCalendarCategoryResponse($calendarCategory ?? []),
        ]);

    $this->assertDatabaseHas('calendar_categories', ['name' => $calendarCategory['name']]);
});


it('deletes an existing calendar event', function () {
    $calendarCategory = CalendarCategory::factory()->create()->first();

    authedUser()
        ->deleteJson("/api/v1/calendar-categories/$calendarCategory->id")
        ->assertNoContent();

    $this->assertDatabaseMissing('calendar_categories', ['id' => $calendarCategory['id']]);
});

it('shows a calendar event by id', function () {
    $calendarCategory = CalendarCategory::factory(1)->create()->first();

    authedUser()->getJson("/api/v1/calendar-categories/$calendarCategory->id")
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => CalendarCategoryTestUtils::calendarCategoryResponse(),
        ])
        ->assertJson([
            'data' => CalendarCategoryTestUtils::matchCalendarCategoryResponse($calendarCategory ?? []),
        ]);
});