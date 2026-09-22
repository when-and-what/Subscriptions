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
