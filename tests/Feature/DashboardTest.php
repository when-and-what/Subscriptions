<?php

use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('an upcoming renewal with a price increase shows its price in red', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create([
        'end_date' => now()->addDays(5),
        'new_price' => 2.00,
        'new_price_date' => now()->addDays(3),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('text-red-600');
});

test('an upcoming renewal without an applicable price increase does not show its price in red', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create([
        'end_date' => now()->addDays(5),
    ]);
    Subscription::factory()->for($service)->create([
        'end_date' => now()->addDays(5),
        'new_price' => 2.00,
        'new_price_date' => now()->addDays(20),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertDontSee('text-red-600');
});

test('an upcoming renewal with a price increase shows the new price instead of the current price', function () {
    $user = User::factory()->create();
    $service = Service::factory()->for($user)->create();
    Subscription::factory()->for($service)->create([
        'end_date' => now()->addDays(5),
        'price' => 77.77,
        'new_price' => 88.88,
        'new_price_date' => now()->addDays(3),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSee('$88.88');
    $response->assertDontSee('$77.77');
});
