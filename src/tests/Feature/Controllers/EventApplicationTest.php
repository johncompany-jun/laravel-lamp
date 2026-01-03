<?php

namespace Tests\Feature\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\EventApplication;
use App\Models\EventApplicationSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_application_with_all_slots(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['status' => EventStatus::OPEN]);
        $slot1 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);
        $slot2 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        $response = $this->actingAs($user)->post(route('events.apply', $event), [
            'slots' => [
                $slot1->id => [
                    'slot_id' => $slot1->id,
                    'availability' => 'available',
                ],
                $slot2->id => [
                    'slot_id' => $slot2->id,
                    'availability' => 'unavailable',
                ],
            ],
            'can_help_setup' => true,
            'can_help_cleanup' => false,
            'can_transport_by_car' => true,
            'comment' => 'Test comment',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('event_applications', 2);
        $this->assertDatabaseHas('event_applications', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot1->id,
            'availability' => 'available',
            'can_help_setup' => true,
            'can_help_cleanup' => false,
            'can_transport_by_car' => true,
        ]);
    }

    public function test_application_requires_all_slots_to_have_availability(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['status' => EventStatus::OPEN]);
        $slot = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        $response = $this->actingAs($user)->post(route('events.apply', $event), [
            'slots' => [
                $slot->id => [
                    'slot_id' => $slot->id,
                    // Missing 'availability'
                ],
            ],
        ]);

        $response->assertSessionHasErrors(); // Check for any validation errors
        $this->assertDatabaseCount('event_applications', 0);
    }

    public function test_availability_must_be_available_or_unavailable(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['status' => EventStatus::OPEN]);
        $slot = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        $response = $this->actingAs($user)->post(route('events.apply', $event), [
            'slots' => [
                $slot->id => [
                    'slot_id' => $slot->id,
                    'availability' => 'invalid_value',
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('event_applications', 0);
    }

    public function test_can_transport_by_car_is_optional(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['status' => EventStatus::OPEN]);
        $slot = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        $response = $this->actingAs($user)->post(route('events.apply', $event), [
            'slots' => [
                $slot->id => [
                    'slot_id' => $slot->id,
                    'availability' => 'available',
                ],
            ],
            // can_transport_by_car not provided
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('event_applications', [
            'user_id' => $user->id,
            'can_transport_by_car' => false,
        ]);
    }

    public function test_user_can_cancel_all_applications_for_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $slot1 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);
        $slot2 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        // Create applications
        EventApplication::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot1->id,
        ]);

        EventApplication::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot2->id,
        ]);

        $this->assertDatabaseCount('event_applications', 2);

        $response = $this->actingAs($user)->delete(route('applications.cancel', $event));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('event_applications', 0);
    }

    public function test_user_cannot_cancel_another_users_applications(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $event = Event::factory()->create();
        $slot = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        // User1 creates application
        EventApplication::factory()->create([
            'user_id' => $user1->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot->id,
        ]);

        $this->assertDatabaseCount('event_applications', 1);

        // User2 tries to cancel
        $response = $this->actingAs($user2)->delete(route('applications.cancel', $event));

        $response->assertRedirect(route('dashboard'));

        // Application should still exist
        $this->assertDatabaseCount('event_applications', 1);
        $this->assertDatabaseHas('event_applications', [
            'user_id' => $user1->id,
        ]);
    }

    public function test_guest_cannot_submit_application(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::OPEN]);
        $slot = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        $response = $this->post(route('events.apply', $event), [
            'slots' => [
                $slot->id => [
                    'slot_id' => $slot->id,
                    'availability' => 'available',
                ],
            ],
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('event_applications', 0);
    }

    public function test_guest_cannot_cancel_application(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $slot = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        EventApplication::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot->id,
        ]);

        $response = $this->delete(route('applications.cancel', $event));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('event_applications', 1);
    }
}
