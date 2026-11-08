<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('renewal reminder days can be saved', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('renewal_notification_days', 7)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->renewal_notification_days)->toBe(7);
});

test('renewal reminders are turned off when the days field is cleared', function () {
    $user = User::factory()->withRenewalNotifications(7)->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->assertSet('renewal_notification_days', 7)
        ->set('renewal_notification_days', '')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->renewal_notification_days)->toBeNull();
});

test('renewal reminder days outside the allowed range are rejected', function (int $days) {
    $user = User::factory()->withRenewalNotifications(7)->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('renewal_notification_days', $days)
        ->call('updateProfileInformation');

    $response->assertHasErrors(['renewal_notification_days']);

    expect($user->refresh()->renewal_notification_days)->toBe(7);
})->with([
    'below the minimum' => 0,
    'above the maximum' => 91,
]);

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
