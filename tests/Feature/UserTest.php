<?php

use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;

test('user subscriptions relation returns subscriptions across all of the user\'s services', function () {
    $user = User::factory()->create();

    $serviceOne = Service::factory()->for($user)->create();
    $serviceTwo = Service::factory()->for($user)->create();

    Subscription::factory()->for($serviceOne)->create();
    Subscription::factory()->for($serviceTwo)->create();

    expect($user->subscriptions)->toHaveCount(2);
});
