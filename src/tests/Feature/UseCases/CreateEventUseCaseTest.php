<?php

namespace Tests\Feature\UseCases;

use App\Enums\EventStatus;
use App\Enums\ApplicationSlotDuration;
use App\Enums\AssignmentSlotDuration;
use App\Models\Event;
use App\Models\User;
use App\UseCases\CreateEventUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEventUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private CreateEventUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = app(CreateEventUseCase::class);
    }

    /** @test */
    public function it_creates_event_with_basic_data()
    {
        // Prepare
        $user = User::factory()->create();

        $data = [
            'title' => 'Test Event',
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '16:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::MINUTES_60->value,
            'slot_duration' => AssignmentSlotDuration::MINUTES_20->value,
            'created_by' => $user->id,
            'location' => 'Test Location',
            'notes' => 'Test notes',
        ];

        // Execute
        $event = $this->useCase->execute($data);

        // Assert
        $this->assertInstanceOf(Event::class, $event);
        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'location' => 'Test Location',
            'status' => EventStatus::DRAFT->value,
        ]);

        // Check that slots were generated
        $this->assertGreaterThan(0, $event->applicationSlots()->count());
        $this->assertGreaterThan(0, $event->slots()->count());
    }

    /** @test */
    public function it_creates_event_with_multiple_locations()
    {
        // Prepare
        $user = User::factory()->create();

        $data = [
            'title' => 'Multi-Location Event',
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::MINUTES_60->value,
            'slot_duration' => AssignmentSlotDuration::MINUTES_20->value,
            'created_by' => $user->id,
            'locations' => ['北西', '北東', '南側'],
        ];

        // Execute
        $event = $this->useCase->execute($data);

        // Assert
        $this->assertEquals(['北西', '北東', '南側'], $event->locations);

        // Check that slots were generated for each location
        $this->assertGreaterThan(0, $event->slots()->where('location', '北西')->count());
        $this->assertGreaterThan(0, $event->slots()->where('location', '北東')->count());
        $this->assertGreaterThan(0, $event->slots()->where('location', '南側')->count());
    }

    /** @test */
    public function it_filters_out_empty_locations()
    {
        // Prepare
        $user = User::factory()->create();

        $data = [
            'title' => 'Event With Empty Locations',
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::MINUTES_60->value,
            'slot_duration' => AssignmentSlotDuration::MINUTES_20->value,
            'created_by' => $user->id,
            'locations' => ['北西', '', '北東', null],
        ];

        // Execute
        $event = $this->useCase->execute($data);

        // Assert - empty locations should be filtered out
        $this->assertEquals(['北西', '北東'], $event->locations);
    }

    /** @test */
    public function it_creates_recurring_events()
    {
        // Prepare
        $user = User::factory()->create();

        $startDate = now()->addDays(7);
        $endDate = $startDate->copy()->addWeeks(3);

        $data = [
            'title' => 'Recurring Event',
            'event_date' => $startDate->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::MINUTES_60->value,
            'slot_duration' => AssignmentSlotDuration::MINUTES_20->value,
            'created_by' => $user->id,
            'is_recurring' => true,
            'recurrence_type' => 'weekly',
            'recurrence_end_date' => $endDate->format('Y-m-d'),
        ];

        // Execute
        $event = $this->useCase->execute($data);

        // Assert - should create parent event + child events
        $this->assertTrue($event->is_recurring);
        $this->assertGreaterThan(0, $event->childEvents()->count());

        // Check that child events were created with correct dates
        $childEvents = $event->childEvents;
        foreach ($childEvents as $index => $childEvent) {
            $expectedDate = $startDate->copy()->addWeeks($index + 1);
            $this->assertEquals(
                $expectedDate->format('Y-m-d'),
                $childEvent->event_date->format('Y-m-d')
            );
        }
    }

    /** @test */
    public function it_saves_event_as_template()
    {
        // Prepare
        $user = User::factory()->create();

        $data = [
            'title' => 'Template Event',
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::MINUTES_60->value,
            'slot_duration' => AssignmentSlotDuration::MINUTES_20->value,
            'created_by' => $user->id,
            'is_template' => true,
        ];

        // Execute
        $event = $this->useCase->execute($data);

        // Assert
        $this->assertTrue($event->is_template);
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'is_template' => true,
        ]);
    }

    /** @test */
    public function it_generates_correct_number_of_slots()
    {
        // Prepare
        $user = User::factory()->create();

        // 3 hours (13:00-16:00) with 20-minute slots should generate 9 slots
        $data = [
            'title' => 'Slot Generation Test',
            'event_date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '16:00',
            'status' => EventStatus::DRAFT->value,
            'application_slot_duration' => ApplicationSlotDuration::MINUTES_60->value,
            'slot_duration' => AssignmentSlotDuration::MINUTES_20->value,
            'created_by' => $user->id,
        ];

        // Execute
        $event = $this->useCase->execute($data);

        // Assert - 3 hours = 180 minutes, 180 / 20 = 9 slots
        $this->assertEquals(9, $event->slots()->count());

        // Application slots: 3 hours with 60-minute slots = 3 slots
        $this->assertEquals(3, $event->applicationSlots()->count());
    }
}
