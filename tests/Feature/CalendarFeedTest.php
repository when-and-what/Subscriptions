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

test('feed includes subscriptions with an end date and excludes subscriptions without one', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create(['end_date' => now()->addMonth()]);
    Subscription::factory()->for($service)->create(['end_date' => null]);

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
