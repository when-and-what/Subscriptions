<?php

use App\Livewire\Settings\CalendarFeed;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get('/settings/calendar-feed')->assertRedirect(route('login'));
});

test('calendar feed settings page is displayed', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/settings/calendar-feed')->assertOk();
});

test('feed token is generated on first visit', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    expect($user->calendar_feed_token)->toBeNull();

    Livewire::test(CalendarFeed::class);

    expect($user->fresh()->calendar_feed_token)->not->toBeNull();
});

test('user can regenerate their calendar feed token', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $oldToken = $user->calendarFeedToken();

    Livewire::test(CalendarFeed::class)->call('regenerateToken');

    $newToken = $user->fresh()->calendar_feed_token;

    expect($newToken)->not->toBeNull();
    expect($newToken)->not->toEqual($oldToken);
});

test('old feed url stops working after regeneration', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $oldUrl = route('calendar.feed', ['token' => $user->calendarFeedToken()]);

    Livewire::test(CalendarFeed::class)->call('regenerateToken');

    $this->get($oldUrl)->assertNotFound();
});
