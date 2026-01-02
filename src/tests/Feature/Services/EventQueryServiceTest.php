<?php

namespace Tests\Feature\Services;

use App\Models\Event;
use App\Models\EventSlot;
use App\Models\User;
use App\Models\EventApplication;
use App\Services\EventQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private EventQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EventQueryService::class);
    }

    /** @test */
    public function it_prepares_assignment_data_correctly()
    {
        // Create event with slots
        $event = Event::factory()->create([
            'locations' => ['北西', '北東'],
        ]);

        // Create slots with different times and locations
        EventSlot::factory()->create([
            'event_id' => $event->id,
            'start_time' => '13:00:00',
            'end_time' => '13:20:00',
            'location' => '北西',
        ]);

        EventSlot::factory()->create([
            'event_id' => $event->id,
            'start_time' => '13:00:00',
            'end_time' => '13:20:00',
            'location' => '北東',
        ]);

        EventSlot::factory()->create([
            'event_id' => $event->id,
            'start_time' => '13:20:00',
            'end_time' => '13:40:00',
            'location' => '北西',
        ]);

        // Reload event with slots
        $event->load('slots');

        // Execute
        $result = $this->service->prepareAssignmentData($event);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('timeSlots', $result);
        $this->assertArrayHasKey('locations', $result);
        $this->assertArrayHasKey('slotMatrix', $result);

        // Check timeSlots - should have 2 unique time ranges
        $this->assertCount(2, $result['timeSlots']);

        // Check locations
        $this->assertEquals(['北西', '北東'], $result['locations']);

        // Check slotMatrix structure
        $this->assertArrayHasKey('13:00:00-13:20:00', $result['slotMatrix']);
        $this->assertArrayHasKey('13:20:00-13:40:00', $result['slotMatrix']);
        $this->assertArrayHasKey('北西', $result['slotMatrix']['13:00:00-13:20:00']);
        $this->assertArrayHasKey('北東', $result['slotMatrix']['13:00:00-13:20:00']);
    }

    /** @test */
    public function it_prepares_available_users_correctly()
    {
        // Create users
        $user1 = User::factory()->create(['name' => 'User 1']);
        $user2 = User::factory()->create(['name' => 'User 2']);

        // Create event
        $event = Event::factory()->create();

        // Create applications
        $app1 = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user1->id,
        ]);

        $app2 = EventApplication::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user2->id,
        ]);

        // Group applications by user
        $applications = collect([
            $user1->id => collect([$app1]),
            $user2->id => collect([$app2]),
        ]);

        // Execute
        $result = $this->service->prepareAvailableUsers($applications);

        // Assert
        $this->assertCount(2, $result);
        $this->assertEquals($user1->id, $result[0]['id']);
        $this->assertEquals('User 1', $result[0]['name']);
        $this->assertEquals($user2->id, $result[1]['id']);
        $this->assertEquals('User 2', $result[1]['name']);
    }

    /** @test */
    public function it_handles_event_with_no_locations()
    {
        // Create event without locations
        $event = Event::factory()->create([
            'locations' => null,
        ]);

        // Create slot without location
        EventSlot::factory()->create([
            'event_id' => $event->id,
            'start_time' => '13:00:00',
            'end_time' => '13:20:00',
            'location' => null,
        ]);

        // Reload event with slots
        $event->load('slots');

        // Execute
        $result = $this->service->prepareAssignmentData($event);

        // Assert
        $this->assertEquals([], $result['locations']);
        $this->assertArrayHasKey('13:00:00-13:20:00', $result['slotMatrix']);
        $this->assertArrayHasKey('default', $result['slotMatrix']['13:00:00-13:20:00']);
    }

    /** @test */
    public function it_sorts_time_slots_correctly()
    {
        // Create event
        $event = Event::factory()->create();

        // Create slots in random order
        EventSlot::factory()->create([
            'event_id' => $event->id,
            'start_time' => '14:00:00',
            'end_time' => '14:20:00',
        ]);

        EventSlot::factory()->create([
            'event_id' => $event->id,
            'start_time' => '13:00:00',
            'end_time' => '13:20:00',
        ]);

        EventSlot::factory()->create([
            'event_id' => $event->id,
            'start_time' => '13:40:00',
            'end_time' => '14:00:00',
        ]);

        // Reload event with slots
        $event->load('slots');

        // Execute
        $result = $this->service->prepareAssignmentData($event);

        // Assert - time slots should be sorted by start_time
        $timeSlots = $result['timeSlots'];
        $this->assertEquals('13:00:00', $timeSlots[0]->start_time);
        $this->assertEquals('13:40:00', $timeSlots[1]->start_time);
        $this->assertEquals('14:00:00', $timeSlots[2]->start_time);
    }
}
