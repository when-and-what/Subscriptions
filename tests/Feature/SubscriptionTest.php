<?php

use App\Enums\BillingCycle;
use App\Models\Category;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $subscription = Subscription::factory()->create();

    $this->get(route('subscriptions.index'))->assertRedirect(route('login'));
    $this->get(route('subscriptions.create'))->assertRedirect(route('login'));
    $this->get(route('subscriptions.edit', $subscription))->assertRedirect(route('login'));
});

test('a user can list subscriptions for their own services', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $subscription = Subscription::factory()->for($service)->create();
    Subscription::factory()->create(); // another user's subscription

    $response = $this->actingAs($user)->get(route('subscriptions.index'));

    $response->assertOk();
    $response->assertSee($service->name);
});

test('a subscription card shows its service\'s category badges', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create(['name' => 'Streaming']);
    $service->categories()->attach($category);
    Subscription::factory()->for($service)->create();

    $response = $this->actingAs($user)->get(route('subscriptions.index'));

    $response->assertOk();
    $response->assertSee('Streaming');
});

test('filtering subscriptions by category only shows subscriptions for matching services', function () {
    $user = User::factory()->create();
    $streaming = Category::factory()->for($user)->create(['name' => 'Streaming']);
    $software = Category::factory()->for($user)->create(['name' => 'Software']);
    $netflix = Service::factory()->for($user)->create(['name' => 'Netflix']);
    $github = Service::factory()->for($user)->create(['name' => 'GitHub']);
    $netflix->categories()->attach($streaming);
    $github->categories()->attach($software);
    Subscription::factory()->for($netflix)->create();
    Subscription::factory()->for($github)->create();

    $response = $this->actingAs($user)->get(route('subscriptions.index', ['category' => $streaming->id]));

    $response->assertOk();
    $response->assertSee('Netflix');
    $response->assertDontSee('GitHub');
});

test('subscription pagination links point to the next page', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->count(20)->create();

    $response = $this->actingAs($user)->get(route('subscriptions.index'));

    $response->assertOk();
    $response->assertSee(route('subscriptions.index').'?page=2', false);
});

test('a user can view the create and edit forms', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $subscription = Subscription::factory()->for($service)->create();

    $this->actingAs($user)->get(route('subscriptions.create'))->assertOk();
    $this->actingAs($user)->get(route('subscriptions.edit', $subscription))->assertOk();
});

test('a user can create a subscription for their own service', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('subscriptions.store'), [
        'service_id' => $service->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addYear()->toDateString(),
        'price' => '9.99',
        'billing_cycle' => 1,
        'auto_renew' => '1',
    ]);

    $response->assertRedirect(route('subscriptions.index'));

    $subscription = Subscription::where('service_id', $service->id)->first();
    expect($subscription)->not->toBeNull();
    expect($subscription->service_id)->toBe($service->id);
    expect($subscription->auto_renew)->toBeTrue();
    expect((float) $subscription->price)->toBe(9.99);
});

test('a user cannot create a subscription for another user\'s service', function () {
    $user = User::factory()->create();
    $otherService = Service::factory()->create();

    $response = $this->actingAs($user)->post(route('subscriptions.store'), [
        'service_id' => $otherService->id,
        'start_date' => now()->toDateString(),
        'billing_cycle' => 1,
        'auto_renew' => '1',
    ]);

    $response->assertInvalid(['service_id']);
});

test('an omitted auto_renew value defaults to false instead of erroring', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('subscriptions.store'), [
        'service_id' => $service->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'billing_cycle' => 1,
    ]);

    $response->assertRedirect(route('subscriptions.index'));
    expect(Subscription::where('service_id', $service->id)->first()->auto_renew)->toBeFalse();
});

test('a checked switch\'s "1" value is accepted as auto_renew=true', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    // <flux:switch ... value="1"> submits "1" when checked, which Laravel's
    // native `boolean` rule accepts directly (no normalization needed).
    $response = $this->actingAs($user)->post(route('subscriptions.store'), [
        'service_id' => $service->id,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'billing_cycle' => 1,
        'auto_renew' => '1',
    ]);

    $response->assertRedirect(route('subscriptions.index'));
    expect(Subscription::where('service_id', $service->id)->first()->auto_renew)->toBeTrue();
});

test('a user can update their own subscription', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $subscription = Subscription::factory()->for($service)->create();

    $response = $this->actingAs($user)->put(route('subscriptions.update', $subscription), [
        'service_id' => $service->id,
        'start_date' => $subscription->start_date->toDateString(),
        'end_date' => $subscription->start_date->addYear()->toDateString(),
        'billing_cycle' => 12,
        'auto_renew' => '0',
    ]);

    $response->assertRedirect(route('subscriptions.index'));
    expect($subscription->refresh()->billing_cycle)->toBe(BillingCycle::Yearly);
    expect($subscription->auto_renew)->toBeFalse();
});

test('a user can create a subscription with a new price and effective date', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('subscriptions.store'), [
        'service_id' => $service->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-02-01',
        'price' => '10.00',
        'billing_cycle' => 1,
        'new_price' => '12.50',
        'new_price_date' => '2026-01-15',
    ]);

    $response->assertRedirect(route('subscriptions.index'));

    $subscription = Subscription::where('service_id', $service->id)->first();
    expect($subscription)->not->toBeNull();
    expect($subscription->new_price)->toBe(12.50);
    expect($subscription->new_price_date->toDateString())->toBe('2026-01-15');
});

test('a user can update the new price and effective date of their own subscription', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $subscription = Subscription::factory()->for($service)->create();

    $response = $this->actingAs($user)->put(route('subscriptions.update', $subscription), [
        'service_id' => $service->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-02-01',
        'new_price' => '12.50',
        'new_price_date' => '2026-01-15',
    ]);

    $response->assertRedirect(route('subscriptions.index'));
    expect($subscription->refresh()->new_price)->toBe(12.50);
    expect($subscription->new_price_date->toDateString())->toBe('2026-01-15');
});

test('a user can clear the new price and effective date of their own subscription', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $subscription = Subscription::factory()->for($service)->create([
        'new_price' => 12.50,
        'new_price_date' => '2026-01-15',
    ]);

    $response = $this->actingAs($user)->put(route('subscriptions.update', $subscription), [
        'service_id' => $service->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-02-01',
        'new_price' => '',
        'new_price_date' => '',
    ]);

    $response->assertRedirect(route('subscriptions.index'));
    expect($subscription->refresh()->new_price)->toBeNull();
    expect($subscription->new_price_date)->toBeNull();
});

test('a new price without an effective date is rejected', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('subscriptions.store'), [
        'service_id' => $service->id,
        'new_price' => '12.50',
        'new_price_date' => '',
    ]);

    $response->assertInvalid(['new_price_date' => 'required when new price is present']);
    expect(Subscription::count())->toBe(0);
});

test('an effective date without a new price is rejected', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('subscriptions.store'), [
        'service_id' => $service->id,
        'new_price' => '',
        'new_price_date' => '2026-01-15',
    ]);

    $response->assertInvalid(['new_price' => 'required when new price date is present']);
    expect(Subscription::count())->toBe(0);
});

test('an effective date that is not after the start date is rejected', function (string $newPriceDate) {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('subscriptions.store'), [
        'service_id' => $service->id,
        'start_date' => '2026-01-15',
        'new_price' => '12.50',
        'new_price_date' => $newPriceDate,
    ]);

    $response->assertInvalid(['new_price_date' => 'must be a date after start date']);
    expect(Subscription::count())->toBe(0);
})->with([
    'before the start date' => '2026-01-14',
    'on the start date' => '2026-01-15',
]);

test('an invalid new price or effective date is rejected', function (string $field, string $value) {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('subscriptions.store'), [
        'service_id' => $service->id,
        'new_price' => '12.50',
        'new_price_date' => '2026-01-15',
        $field => $value,
    ]);

    $response->assertInvalid([$field]);
    expect(Subscription::count())->toBe(0);
})->with([
    'new price with more than two decimals' => ['new_price', '12.505'],
    'non-numeric new price' => ['new_price', 'abc'],
    'malformed effective date' => ['new_price_date', 'not-a-date'],
]);

test('a user can delete their own subscription', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $subscription = Subscription::factory()->for($service)->create();

    $response = $this->actingAs($user)->delete(route('subscriptions.destroy', $subscription));

    $response->assertRedirect(route('subscriptions.index'));
    expect(Subscription::find($subscription->id))->toBeNull();
});

test('a user cannot edit another user\'s subscription', function () {
    $intruder = User::factory()->create();
    $subscription = Subscription::factory()->create();

    $this->actingAs($intruder)->get(route('subscriptions.edit', $subscription))->assertForbidden();
    $this->actingAs($intruder)->delete(route('subscriptions.destroy', $subscription))->assertForbidden();
});
