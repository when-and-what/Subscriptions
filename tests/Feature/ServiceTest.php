<?php

use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $service = Service::factory()->create();

    $this->get(route('services.index'))->assertRedirect(route('login'));
    $this->get(route('services.create'))->assertRedirect(route('login'));
    $this->get(route('services.edit', $service))->assertRedirect(route('login'));
});

test('a user can list their own services', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Service::factory()->create(); // another user's service

    $response = $this->actingAs($user)->get(route('services.index'));

    $response->assertOk();
    $response->assertSee($service->name);
});

test('service pagination links point to the next page', function () {
    $user = User::factory()->create();
    Service::factory()->for($user)->count(20)->create();

    $response = $this->actingAs($user)->get(route('services.index'));

    $response->assertOk();
    $response->assertSee(route('services.index').'?page=2', false);
});

test('a service with an active subscription shows an active badge', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create(['end_date' => now()->addMonth()]);

    $response = $this->actingAs($user)->get(route('services.index'));

    $response->assertOk();
    $response->assertSeeText('Active');
    $response->assertDontSeeText('Expired');
});

test('a service with an expired subscription shows an expired badge', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create(['end_date' => now()->subMonth()]);

    $response = $this->actingAs($user)->get(route('services.index'));

    $response->assertOk();
    $response->assertSeeText('Expired');
    $response->assertDontSeeText('Active');
});

test('a service without a subscription shows a no subscription badge', function () {
    $user = User::factory()->create();
    Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('services.index'));

    $response->assertOk();
    $response->assertSeeText('No subscription');
});

test('a user can view their own service with its subscription history', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $past = Subscription::factory()->for($service)->create([
        'start_date' => now()->subYear(),
        'end_date' => now()->subMonths(6),
    ]);
    $current = Subscription::factory()->for($service)->create([
        'start_date' => now()->subMonths(6),
        'end_date' => now()->addMonth(),
    ]);

    $response = $this->actingAs($user)->get(route('services.show', $service));

    $response->assertOk();
    $response->assertSeeTextInOrder([$current->start_date->format('M j, Y'), $past->start_date->format('M j, Y')]);
});

test('a user cannot view another user\'s service', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $service = Service::factory()->for($owner)->create();

    $this->actingAs($intruder)->get(route('services.show', $service))->assertForbidden();
});

test('a user can view the create and edit forms', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $this->actingAs($user)->get(route('services.create'))->assertOk();
    $this->actingAs($user)->get(route('services.edit', $service))->assertOk();
});

test('a user can create a service', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('services.store'), [
        'name' => 'Netflix',
        'url' => 'https://netflix.com',
    ]);

    $response->assertRedirect(route('services.index'));
    expect(Service::where('user_id', $user->id)->where('name', 'Netflix')->exists())->toBeTrue();
});

test('a user can update their own service', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->put(route('services.update', $service), [
        'name' => 'Updated Name',
        'url' => $service->url,
    ]);

    $response->assertRedirect(route('services.index'));
    expect($service->refresh()->name)->toBe('Updated Name');
});

test('a user can delete their own service', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('services.destroy', $service));

    $response->assertRedirect(route('services.index'));
    expect(Service::find($service->id))->toBeNull();
});

test('a user cannot edit another user\'s service', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $service = Service::factory()->for($owner)->create();

    $this->actingAs($intruder)->get(route('services.edit', $service))->assertForbidden();
    $this->actingAs($intruder)->put(route('services.update', $service), ['name' => 'Hacked'])->assertForbidden();
    $this->actingAs($intruder)->delete(route('services.destroy', $service))->assertForbidden();
});
