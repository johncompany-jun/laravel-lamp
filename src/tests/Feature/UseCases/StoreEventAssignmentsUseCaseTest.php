<?php

namespace Tests\Feature\UseCases;

use App\Models\Event;
use App\Models\EventSlot;
use App\Models\EventAssignment;
use App\Models\User;
use App\UseCases\StoreEventAssignmentsUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreEventAssignmentsUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private StoreEventAssignmentsUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = app(StoreEventAssignmentsUseCase::class);
    }

    /** @test */
    public function it_stores_new_assignments()
    {
        // Create event and slot
        $event = Event::factory()->create();
        $slot = EventSlot::factory()->create(['event_id' => $event->id]);

        // Create users
        $participant = User::factory()->create();
        $leader = User::factory()->create();

        // Prepare assignments data
        $assignments = [
            [
                'slot_id' => $slot->id,
                'user_id' => $participant->id,
                'role' => 'participant',
            ],
            [
                'slot_id' => $slot->id,
                'user_id' => $leader->id,
                'role' => 'leader',
            ],
        ];

        // Execute
        $this->useCase->execute($event, $assignments);

        // Assert
        $this->assertDatabaseHas('event_assignments', [
            'event_id' => $event->id,
            'event_slot_id' => $slot->id,
            'user_id' => $participant->id,
            'role' => 'participant',
        ]);

        $this->assertDatabaseHas('event_assignments', [
            'event_id' => $event->id,
            'event_slot_id' => $slot->id,
            'user_id' => $leader->id,
            'role' => 'leader',
        ]);
    }

    /** @test */
    public function it_replaces_existing_assignments()
    {
        // Create event and slot
        $event = Event::factory()->create();
        $slot = EventSlot::factory()->create(['event_id' => $event->id]);

        // Create users
        $oldUser = User::factory()->create();
        $newUser = User::factory()->create();

        // Create existing assignment
        EventAssignment::factory()->create([
            'event_id' => $event->id,
            'event_slot_id' => $slot->id,
            'user_id' => $oldUser->id,
            'role' => 'participant',
        ]);

        // Prepare new assignments data (replacing old one)
        $assignments = [
            [
                'slot_id' => $slot->id,
                'user_id' => $newUser->id,
                'role' => 'participant',
            ],
        ];

        // Execute
        $this->useCase->execute($event, $assignments);

        // Assert - old assignment should be deleted
        $this->assertDatabaseMissing('event_assignments', [
            'event_id' => $event->id,
            'user_id' => $oldUser->id,
        ]);

        // Assert - new assignment should exist
        $this->assertDatabaseHas('event_assignments', [
            'event_id' => $event->id,
            'event_slot_id' => $slot->id,
            'user_id' => $newUser->id,
            'role' => 'participant',
        ]);
    }

    /** @test */
    public function it_handles_empty_assignments()
    {
        // Create event and slot
        $event = Event::factory()->create();
        $slot = EventSlot::factory()->create(['event_id' => $event->id]);

        // Create existing assignment
        $user = User::factory()->create();
        EventAssignment::factory()->create([
            'event_id' => $event->id,
            'event_slot_id' => $slot->id,
            'user_id' => $user->id,
        ]);

        // Execute with empty assignments
        $this->useCase->execute($event, []);

        // Assert - all assignments should be deleted
        $this->assertDatabaseMissing('event_assignments', [
            'event_id' => $event->id,
        ]);
    }

    /** @test */
    public function it_stores_multiple_participants_for_same_slot()
    {
        // Create event and slot
        $event = Event::factory()->create();
        $slot = EventSlot::factory()->create(['event_id' => $event->id]);

        // Create multiple users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // Prepare assignments with multiple participants
        $assignments = [
            [
                'slot_id' => $slot->id,
                'user_id' => $user1->id,
                'role' => 'participant',
            ],
            [
                'slot_id' => $slot->id,
                'user_id' => $user2->id,
                'role' => 'participant',
            ],
            [
                'slot_id' => $slot->id,
                'user_id' => $user3->id,
                'role' => 'leader',
            ],
        ];

        // Execute
        $this->useCase->execute($event, $assignments);

        // Assert - all assignments should exist
        $this->assertEquals(3, EventAssignment::where('event_id', $event->id)->count());
        $this->assertEquals(2, EventAssignment::where('event_id', $event->id)
            ->where('role', 'participant')
            ->count());
        $this->assertEquals(1, EventAssignment::where('event_id', $event->id)
            ->where('role', 'leader')
            ->count());
    }
}
