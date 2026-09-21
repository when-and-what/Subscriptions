<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'service_id' => Service::factory(),
            'start_date' => $startDate,
            'end_date' => fake()->boolean(70) ? fake()->dateTimeBetween($startDate, '+1 year') : null,
            'price' => fake()->randomFloat(2, 3, 60),
            'billing_cycle' => fake()->randomElement(BillingCycle::cases())->value,
            'auto_renew' => fake()->boolean(),
        ];
    }
}
