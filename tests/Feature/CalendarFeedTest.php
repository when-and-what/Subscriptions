<?php

use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;

test('guest can access a valid calendar feed url', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create(['end_date' => now()->addMonth()]);

    $response = $this->get(route('calendar.feed', ['token' => $user->calendarFeedToken()]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
});

test('invalid token returns 404', function () {
    $this->get('/calendar/not-a-real-token.ics')->assertNotFound();
});

test('feed excludes subscriptions that ended more than a year ago', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create(['end_date' => now()->addMonth()]);
    Subscription::factory()->for($service)->create([
        'start_date' => now()->subYears(3),
        'end_date' => now()->subYears(2),
    ]);

    $response = $this->get(route('calendar.feed', ['token' => $user->calendarFeedToken()]));

    expect(substr_count($response->getContent(), 'BEGIN:VEVENT'))->toBe(1);
});

test('feed only includes the requesting user\'s subscriptions', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create(['name' => 'My Service']);
    Subscription::factory()->for($service)->create(['end_date' => now()->addMonth()]);

    $otherUser = User::factory()->create();
    $otherService = Service::factory()->for($otherUser)->create(['name' => 'Other Service']);
    Subscription::factory()->for($otherService)->create(['end_date' => now()->addMonth()]);

    $response = $this->get(route('calendar.feed', ['token' => $user->calendarFeedToken()]));

    $response->assertSee('My Service', escape: false);
    $response->assertDontSee('Other Service', escape: false);
});

test('feed event uid is stable per subscription', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $subscription = Subscription::factory()->for($service)->create(['end_date' => now()->addMonth()]);

    $response = $this->get(route('calendar.feed', ['token' => $user->calendarFeedToken()]));

    $response->assertSee("subscription-{$subscription->id}@", escape: false);
});

test('feed summary reflects auto_renew status', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create(['name' => 'Renewing Service']);
    Subscription::factory()->for($service)->create(['end_date' => now()->addMonth(), 'auto_renew' => true]);

    $otherService = Service::factory()->for($user)->create(['name' => 'Expiring Service']);
    Subscription::factory()->for($otherService)->create(['end_date' => now()->addMonth(), 'auto_renew' => false]);

    $response = $this->get(route('calendar.feed', ['token' => $user->calendarFeedToken()]));

    $response->assertSee('Renewing Service renews', escape: false);
    $response->assertSee('Expiring Service expires', escape: false);
});

test('feed excludes soft-deleted subscriptions', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $subscription = Subscription::factory()->for($service)->create([
        'end_date' => now()->addMonth(),
        'note' => 'unique-deleted-note',
    ]);
    $subscription->delete();

    $response = $this->get(route('calendar.feed', ['token' => $user->calendarFeedToken()]));

    $response->assertDontSee('unique-deleted-note', escape: false);
});

test('feed description includes the new price when it takes effect at the next renewal', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create([
        'end_date' => now()->addMonth(),
        'price' => 10,
        'new_price' => 12.5,
        'new_price_date' => now()->addWeek(),
    ]);

    $response = $this->get(route('calendar.feed', ['token' => $user->calendarFeedToken()]));

    $response->assertSee('New price: $12.50', escape: false);
});

test('feed description omits the new price when it takes effect after the next renewal', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create([
        'end_date' => now()->addMonth(),
        'price' => 10,
        'new_price' => 12.5,
        'new_price_date' => now()->addMonths(2),
    ]);

    $response = $this->get(route('calendar.feed', ['token' => $user->calendarFeedToken()]));

    $response->assertDontSee('New price', escape: false);
});

test('feed requests are rate limited per token', function () {
    $token = User::factory()->create()->calendarFeedToken();

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->get(route('calendar.feed', ['token' => $token]))->assertOk();
    }

    $this->get(route('calendar.feed', ['token' => $token]))->assertTooManyRequests();
});

test('rate limiting one feed does not block other feeds from the same ip', function () {
    $throttledToken = User::factory()->create()->calendarFeedToken();
    $otherToken = User::factory()->create()->calendarFeedToken();

    for ($attempt = 0; $attempt < 11; $attempt++) {
        $this->get(route('calendar.feed', ['token' => $throttledToken]));
    }

    $this->get(route('calendar.feed', ['token' => $throttledToken]))->assertTooManyRequests();
    $this->get(route('calendar.feed', ['token' => $otherToken]))->assertOk();
});
