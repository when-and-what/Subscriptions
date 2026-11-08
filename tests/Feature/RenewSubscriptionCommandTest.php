<?php

use App\Enums\BillingCycle;
use App\Models\Subscription;

test('an auto-renewing subscription due today gets a renewal created and is linked to it', function () {
    $subscription = Subscription::factory()->create([
        'end_date' => now()->toDateString(),
        'auto_renew' => true,
        'billing_cycle' => BillingCycle::Monthly->value,
        'price' => 9.99,
        'note' => 'some note',
    ]);

    $this->artisan('app:renew-subscription');

    $renewal = Subscription::where('id', '!=', $subscription->id)->first();

    expect($renewal)->not->toBeNull();
    expect($renewal->service_id)->toBe($subscription->service_id);
    expect($renewal->start_date->toDateString())->toBe($subscription->end_date->toDateString());
    expect($renewal->end_date->toDateString())->toBe($subscription->end_date->addMonth()->toDateString());
    expect((float) $renewal->price)->toBe(9.99);
    expect($renewal->note)->toBe('some note');
    expect($renewal->auto_renew)->toBeTrue();

    expect($subscription->refresh()->renewed_subscription_id)->toBe($renewal->id);
});

test('a subscription with auto-renew disabled is not renewed', function () {
    Subscription::factory()->create([
        'end_date' => now()->toDateString(),
        'auto_renew' => false,
    ]);

    $this->artisan('app:renew-subscription');

    expect(Subscription::count())->toBe(1);
});

test('a subscription that was already renewed is not renewed again', function () {
    $existingRenewal = Subscription::factory()->create([
        'end_date' => now()->addYear()->toDateString(),
        'auto_renew' => false,
    ]);
    $subscription = Subscription::factory()->create([
        'end_date' => now()->toDateString(),
        'auto_renew' => true,
    ]);
    $subscription->renewed_subscription_id = $existingRenewal->id;
    $subscription->save();

    $this->artisan('app:renew-subscription');

    expect(Subscription::count())->toBe(2);
    expect($subscription->refresh()->renewed_subscription_id)->toBe($existingRenewal->id);
});

test('an overdue auto-renewing subscription is still renewed, rolling forward from its own end date', function () {
    $endDate = now()->subDays(3);
    $subscription = Subscription::factory()->create([
        'end_date' => $endDate->toDateString(),
        'auto_renew' => true,
        'billing_cycle' => BillingCycle::Quarterly->value,
    ]);

    $this->artisan('app:renew-subscription');

    $renewal = Subscription::where('id', '!=', $subscription->id)->first();

    expect($renewal)->not->toBeNull();
    expect($renewal->start_date->toDateString())->toBe($endDate->toDateString());
    expect($renewal->end_date->toDateString())->toBe($endDate->addMonths(3)->toDateString());
});

test('a renewal is created at the new price when the new price takes effect by the end date', function (int $daysBeforeEndDate) {
    $subscription = Subscription::factory()->create([
        'end_date' => now()->toDateString(),
        'auto_renew' => true,
        'price' => 10.00,
        'new_price' => 12.50,
        'new_price_date' => now()->subDays($daysBeforeEndDate)->toDateString(),
    ]);

    $this->artisan('app:renew-subscription');

    $renewal = Subscription::where('id', '!=', $subscription->id)->first();

    expect($renewal)->not->toBeNull();
    expect($renewal->price)->toBe(12.50);
    expect($renewal->new_price)->toBeNull();
    expect($renewal->new_price_date)->toBeNull();
})->with([
    'effective before the end date' => 3,
    'effective on the end date' => 0,
]);

test('a renewal keeps the current price and carries the pending new price forward when it takes effect after the end date', function () {
    $newPriceDate = now()->addDays(10);
    $subscription = Subscription::factory()->create([
        'end_date' => now()->toDateString(),
        'auto_renew' => true,
        'price' => 10.00,
        'new_price' => 12.50,
        'new_price_date' => $newPriceDate->toDateString(),
    ]);

    $this->artisan('app:renew-subscription');

    $renewal = Subscription::where('id', '!=', $subscription->id)->first();

    expect($renewal)->not->toBeNull();
    expect($renewal->price)->toBe(10.00);
    expect($renewal->new_price)->toBe(12.50);
    expect($renewal->new_price_date->toDateString())->toBe($newPriceDate->toDateString());
});

test('a renewal starts without a renewal reminder marked as sent', function () {
    $subscription = Subscription::factory()->create([
        'end_date' => now()->toDateString(),
        'auto_renew' => true,
        'renewal_notified_at' => now()->subDays(3),
    ]);

    $this->artisan('app:renew-subscription');

    $renewal = Subscription::where('id', '!=', $subscription->id)->first();

    expect($renewal)->not->toBeNull();
    expect($renewal->renewal_notified_at)->toBeNull();
});
