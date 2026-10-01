<?php

use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionRenewalReminder;
use Illuminate\Support\Facades\Notification;

test('a reminder is sent for an auto-renewing subscription that renews on the last day of the reminder window', function () {
    $this->travelTo('2026-03-01 09:00:00');
    $user = User::factory()->withRenewalNotifications(3)->create();
    $subscription = Subscription::factory()->for(Service::factory()->for($user))->create([
        'end_date' => '2026-03-04',
        'auto_renew' => true,
    ]);
    Notification::fake();

    $this->artisan('app:send-renewal-reminders');

    Notification::assertSentTo(
        $user,
        SubscriptionRenewalReminder::class,
        fn (SubscriptionRenewalReminder $notification): bool => $notification->subscriptions->modelKeys() === [$subscription->id],
    );
    expect($subscription->refresh()->renewal_notified_at->toDateTimeString())->toBe('2026-03-01 09:00:00');
});

test('a reminder that was missed on an earlier day is sent on the next run', function (string $endDate) {
    $this->travelTo('2026-03-01 09:00:00');
    $user = User::factory()->withRenewalNotifications(3)->create();
    Subscription::factory()->for(Service::factory()->for($user))->create([
        'end_date' => $endDate,
        'auto_renew' => true,
    ]);
    Notification::fake();

    $this->artisan('app:send-renewal-reminders');

    Notification::assertSentToTimes($user, SubscriptionRenewalReminder::class, 1);
})->with([
    'renews tomorrow' => '2026-03-02',
    'renews today' => '2026-03-01',
]);

test('running the command again does not send a second reminder for the same subscription', function () {
    $this->travelTo('2026-03-01 09:00:00');
    $user = User::factory()->withRenewalNotifications(3)->create();
    Subscription::factory()->for(Service::factory()->for($user))->create([
        'end_date' => '2026-03-04',
        'auto_renew' => true,
    ]);
    Notification::fake();

    $this->artisan('app:send-renewal-reminders');
    $this->artisan('app:send-renewal-reminders');

    Notification::assertSentToTimes($user, SubscriptionRenewalReminder::class, 1);
});

test('several subscriptions in the reminder window are combined into one reminder', function () {
    $this->travelTo('2026-03-01 09:00:00');
    $user = User::factory()->withRenewalNotifications(3)->create();
    $later = Subscription::factory()->for(Service::factory()->for($user))->create([
        'end_date' => '2026-03-04',
        'auto_renew' => true,
    ]);
    $sooner = Subscription::factory()->for(Service::factory()->for($user))->create([
        'end_date' => '2026-03-02',
        'auto_renew' => true,
    ]);
    Notification::fake();

    $this->artisan('app:send-renewal-reminders');

    Notification::assertSentToTimes($user, SubscriptionRenewalReminder::class, 1);
    Notification::assertSentTo(
        $user,
        SubscriptionRenewalReminder::class,
        fn (SubscriptionRenewalReminder $notification): bool => $notification->subscriptions->modelKeys() === [$sooner->id, $later->id],
    );
});

test('no reminder is sent for a subscription that should not be announced', function (array $attributes) {
    $this->travelTo('2026-03-01 09:00:00');
    $user = User::factory()->withRenewalNotifications(3)->create();
    $subscription = Subscription::factory()->for(Service::factory()->for($user))->create([
        'end_date' => '2026-03-04',
        'auto_renew' => true,
        ...$attributes,
    ]);
    Notification::fake();

    $this->artisan('app:send-renewal-reminders');

    Notification::assertNothingSent();
    expect($subscription->refresh()->renewal_notified_at?->toDateTimeString())
        ->toBe($attributes['renewal_notified_at'] ?? null);
})->with([
    'renews after the reminder window' => [['end_date' => '2026-03-05']],
    'end date already passed' => [['end_date' => '2026-02-28']],
    'auto-renew disabled' => [['auto_renew' => false]],
    'already notified' => [['renewal_notified_at' => '2026-02-27 09:00:00']],
]);

test('no reminder is sent for a subscription that has already been renewed', function () {
    $this->travelTo('2026-03-01 09:00:00');
    $user = User::factory()->withRenewalNotifications(3)->create();
    $service = Service::factory()->for($user)->create();
    $renewal = Subscription::factory()->for($service)->create([
        'end_date' => '2026-04-04',
        'auto_renew' => true,
    ]);
    Subscription::factory()->for($service)->create([
        'end_date' => '2026-03-04',
        'auto_renew' => true,
        'renewed_subscription_id' => $renewal->id,
    ]);
    Notification::fake();

    $this->artisan('app:send-renewal-reminders');

    Notification::assertNothingSent();
});

test('no reminder is sent to a user who should not receive renewal emails', function (Closure $user) {
    $this->travelTo('2026-03-01 09:00:00');
    Subscription::factory()->for(Service::factory()->for($user()))->create([
        'end_date' => '2026-03-02',
        'auto_renew' => true,
    ]);
    Notification::fake();

    $this->artisan('app:send-renewal-reminders');

    Notification::assertNothingSent();
})->with([
    'reminders turned off' => [fn () => User::factory()->create()],
    'unverified email' => [fn () => User::factory()->withRenewalNotifications(3)->unverified()->create()],
]);

test('no reminder is sent for a subscription whose service was deleted', function () {
    $this->travelTo('2026-03-01 09:00:00');
    $user = User::factory()->withRenewalNotifications(3)->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create([
        'end_date' => '2026-03-04',
        'auto_renew' => true,
    ]);
    $service->delete();
    Notification::fake();

    $this->artisan('app:send-renewal-reminders');

    Notification::assertNothingSent();
});

test('a reminder only lists the subscriptions of the user it is sent to', function () {
    $this->travelTo('2026-03-01 09:00:00');
    $user = User::factory()->withRenewalNotifications(3)->create();
    $otherUser = User::factory()->withRenewalNotifications(3)->create();
    $subscription = Subscription::factory()->for(Service::factory()->for($user))->create([
        'end_date' => '2026-03-04',
        'auto_renew' => true,
    ]);
    Notification::fake();

    $this->artisan('app:send-renewal-reminders');

    Notification::assertSentTo(
        $user,
        SubscriptionRenewalReminder::class,
        fn (SubscriptionRenewalReminder $notification): bool => $notification->subscriptions->modelKeys() === [$subscription->id],
    );
    Notification::assertNotSentTo($otherUser, SubscriptionRenewalReminder::class);
});

test('the reminder email lists each service with its renewal date and price and links to the profile settings to unsubscribe', function () {
    $user = User::factory()->create();
    $subscriptions = Subscription::factory()
        ->for(Service::factory()->for($user)->create(['name' => 'Netflix']))
        ->count(2)
        ->sequence(
            ['price' => 9.99, 'new_price' => null, 'new_price_date' => null],
            ['price' => 9.99, 'new_price' => 12.50, 'new_price_date' => '2026-03-01'],
        )
        ->create(['end_date' => '2026-03-04', 'billing_cycle' => 1]);

    $mail = (new SubscriptionRenewalReminder($subscriptions))->toMail($user);

    expect($mail->subject)->toBe('2 subscriptions are renewing soon');
    expect($mail->introLines)->toBe([
        'The following subscriptions will renew automatically:',
        'Netflix renews Mar 4, 2026 — $9.99 / monthly',
        'Netflix renews Mar 4, 2026 — $12.50 / monthly (new price)',
    ]);
    expect($mail->actionUrl)->toBe(route('subscriptions.index'));
    expect($mail->outroLines)->toBe([
        'To stop receiving these emails, [unsubscribe in your profile settings]('.route('profile.edit').').',
    ]);
});
