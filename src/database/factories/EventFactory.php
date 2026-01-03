<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'event_date' => fake()->dateTimeBetween('now', '+3 months'),
            'start_time' => '13:00:00',
            'end_time' => '16:00:00',
            'location' => fake()->city(),
            'locations' => json_encode(['エリアA', 'エリアB']),
            'status' => \App\Enums\EventStatus::OPEN,
            'slot_duration' => \App\Enums\AssignmentSlotDuration::TWENTY_MINUTES,
            'application_slot_duration' => \App\Enums\ApplicationSlotDuration::ONE_HOUR,
            'is_template' => false,
            'is_recurring' => false,
            'recurrence_type' => null,
            'recurrence_end_date' => null,
            'parent_event_id' => null,
            'created_by' => \App\Models\User::factory(),
            'notes' => null,
        ];
    }
}
