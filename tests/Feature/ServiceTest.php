<?php

use App\Models\Category;
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

test('a service card shows its category badges', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $category = Category::factory()->for($user)->create(['name' => 'Streaming']);
    $service->categories()->attach($category);

    $response = $this->actingAs($user)->get(route('services.index'));

    $response->assertOk();
    $response->assertSee('Streaming');
});

test('filtering services by category only shows matching services', function () {
    $user = User::factory()->create();
    $streaming = Category::factory()->for($user)->create(['name' => 'Streaming']);
    $software = Category::factory()->for($user)->create(['name' => 'Software']);
    $netflix = Service::factory()->for($user)->create(['name' => 'Netflix']);
    $github = Service::factory()->for($user)->create(['name' => 'GitHub']);
    $netflix->categories()->attach($streaming);
    $github->categories()->attach($software);

    $response = $this->actingAs($user)->get(route('services.index', ['category' => $streaming->id]));

    $response->assertOk();
    $response->assertSee('Netflix');
    $response->assertDontSee('GitHub');
});

test('filtering services by another user\'s category returns no results', function () {
    $user = User::factory()->create();
    Service::factory()->for($user)->create(['name' => 'Paramount Plus']);
    $otherCategory = Category::factory()->create();

    $response = $this->actingAs($user)->get(route('services.index', ['category' => $otherCategory->id]));

    $response->assertOk();
    $response->assertDontSee('Paramount Plus');
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

test('a renewed subscription shows a renewed badge instead of expired', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $renewal = Subscription::factory()->for($service)->create([
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonths(11),
    ]);
    $renewed = Subscription::factory()->for($service)->create([
        'start_date' => now()->subMonths(13),
        'end_date' => now()->subMonth(),
    ]);
    $renewed->renewed_subscription_id = $renewal->id;
    $renewed->save();

    $response = $this->actingAs($user)->get(route('services.show', $service));

    $response->assertOk();
    $response->assertSeeTextInOrder(['Active', 'Renewed']);
    $response->assertDontSeeText('Expired');
});

test('the back link points to the page the user came from', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $this->actingAs($user)->get(route('subscriptions.index'));

    $response = $this->get(route('services.show', $service));

    $response->assertOk();
    $response->assertSee(route('subscriptions.index'), false);
    $response->assertDontSeeText('Back to Services');
});

test('the back link falls back to services index when there is no previous page', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('services.show', $service));

    $response->assertOk();
    $response->assertSee(route('services.index'), false);
    $response->assertSeeText('Back to Services');
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

test('a user can create a service with categories', function () {
    $user = User::factory()->create();
    $categories = Category::factory()->for($user)->count(2)->create();

    $response = $this->actingAs($user)->post(route('services.store'), [
        'name' => 'Netflix',
        'categories' => $categories->pluck('id')->all(),
    ]);

    $response->assertRedirect(route('services.index'));
    $service = Service::where('user_id', $user->id)->where('name', 'Netflix')->firstOrFail();
    expect($service->categories->pluck('id')->sort()->values()->all())
        ->toBe($categories->pluck('id')->sort()->values()->all());
});

test('a user cannot assign another user\'s category to their service', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    $otherCategory = Category::factory()->create();

    $response = $this->actingAs($user)->put(route('services.update', $service), [
        'name' => $service->name,
        'categories' => [$otherCategory->id],
    ]);

    $response->assertSessionHasErrors('categories.0');
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
