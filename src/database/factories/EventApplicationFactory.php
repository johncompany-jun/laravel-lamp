<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventApplication>
 */
class EventApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => \App\Models\Event::factory(),
            'event_application_slot_id' => \App\Models\EventApplicationSlot::factory(),
            'user_id' => \App\Models\User::factory(),
            'availability' => fake()->randomElement(['available', 'unavailable']),
            'can_help_setup' => fake()->boolean(),
            'can_help_cleanup' => fake()->boolean(),
            'can_transport_by_car' => fake()->boolean(30), // 30% chance
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
