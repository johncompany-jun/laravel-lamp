<?php

namespace Tests\Feature\UseCases;

use App\Models\Event;
use App\Models\EventApplication;
use App\Models\EventApplicationSlot;
use App\Models\User;
use App\Services\EventApplicationService;
use App\UseCases\CancelEventApplicationUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelEventApplicationUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private CancelEventApplicationUseCase $useCase;
    private EventApplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EventApplicationService::class);
        $this->useCase = new CancelEventApplicationUseCase($this->service);
    }

    public function test_can_cancel_all_user_applications_for_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $slot1 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);
        $slot2 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        // Create multiple applications for the same event
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

        // Cancel all applications for this event
        $this->useCase->execute($event, $user);

        $this->assertDatabaseCount('event_applications', 0);
    }

    public function test_only_cancels_applications_for_specified_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $event = Event::factory()->create();
        $slot = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        // Create applications for both users
        EventApplication::factory()->create([
            'user_id' => $user1->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot->id,
        ]);

        EventApplication::factory()->create([
            'user_id' => $user2->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot->id,
        ]);

        $this->assertDatabaseCount('event_applications', 2);

        // Cancel only user1's applications
        $this->useCase->execute($event, $user1);

        $this->assertDatabaseCount('event_applications', 1);
        $this->assertDatabaseHas('event_applications', [
            'user_id' => $user2->id,
        ]);
        $this->assertDatabaseMissing('event_applications', [
            'user_id' => $user1->id,
        ]);
    }

    public function test_only_cancels_applications_for_specified_event(): void
    {
        $user = User::factory()->create();
        $event1 = Event::factory()->create();
        $event2 = Event::factory()->create();
        $slot1 = EventApplicationSlot::factory()->create(['event_id' => $event1->id]);
        $slot2 = EventApplicationSlot::factory()->create(['event_id' => $event2->id]);

        // Create applications for both events
        EventApplication::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event1->id,
            'event_application_slot_id' => $slot1->id,
        ]);

        EventApplication::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event2->id,
            'event_application_slot_id' => $slot2->id,
        ]);

        $this->assertDatabaseCount('event_applications', 2);

        // Cancel only event1's applications
        $this->useCase->execute($event1, $user);

        $this->assertDatabaseCount('event_applications', 1);
        $this->assertDatabaseHas('event_applications', [
            'event_id' => $event2->id,
        ]);
        $this->assertDatabaseMissing('event_applications', [
            'event_id' => $event1->id,
        ]);
    }

    public function test_does_nothing_if_no_applications_exist(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->assertDatabaseCount('event_applications', 0);

        // Should not throw exception
        $this->useCase->execute($event, $user);

        $this->assertDatabaseCount('event_applications', 0);
    }
}
