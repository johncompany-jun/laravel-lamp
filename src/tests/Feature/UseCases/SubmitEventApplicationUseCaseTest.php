<?php

namespace Tests\Feature\UseCases;

use App\Models\Event;
use App\Models\EventApplicationSlot;
use App\Models\User;
use App\Services\EventApplicationService;
use App\UseCases\SubmitEventApplicationUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitEventApplicationUseCaseTest extends TestCase
{
    use RefreshDatabase;

    private SubmitEventApplicationUseCase $useCase;
    private EventApplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(EventApplicationService::class);
        $this->useCase = new SubmitEventApplicationUseCase($this->service);
    }

    public function test_can_submit_application_with_all_required_fields(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $slot1 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);
        $slot2 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        $slots = [
            ['slot_id' => $slot1->id, 'availability' => 'available'],
            ['slot_id' => $slot2->id, 'availability' => 'unavailable'],
        ];

        $this->useCase->execute(
            $user,
            $event,
            $slots,
            canHelpSetup: true,
            canHelpCleanup: false,
            canTransportByCar: true,
            comment: 'Test comment'
        );

        $this->assertDatabaseHas('event_applications', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot1->id,
            'availability' => 'available',
            'can_help_setup' => true,
            'can_help_cleanup' => false,
            'can_transport_by_car' => true,
            'comment' => 'Test comment',
        ]);

        $this->assertDatabaseHas('event_applications', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot2->id,
            'availability' => 'unavailable',
        ]);
    }

    public function test_can_submit_application_without_optional_fields(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $slot = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        $slots = [
            ['slot_id' => $slot->id, 'availability' => 'available'],
        ];

        $this->useCase->execute($user, $event, $slots);

        $this->assertDatabaseHas('event_applications', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'event_application_slot_id' => $slot->id,
            'availability' => 'available',
            'can_help_setup' => false,
            'can_help_cleanup' => false,
            'can_transport_by_car' => false,
            'comment' => null,
        ]);
    }

    public function test_replaces_existing_applications_when_resubmitting(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $slot1 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);
        $slot2 = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        // First submission
        $this->useCase->execute(
            $user,
            $event,
            [['slot_id' => $slot1->id, 'availability' => 'available']],
            canHelpSetup: true
        );

        $this->assertDatabaseCount('event_applications', 1);

        // Second submission - should replace
        $this->useCase->execute(
            $user,
            $event,
            [
                ['slot_id' => $slot1->id, 'availability' => 'unavailable'],
                ['slot_id' => $slot2->id, 'availability' => 'available'],
            ],
            canHelpSetup: false,
            canHelpCleanup: true
        );

        $this->assertDatabaseCount('event_applications', 2);

        $this->assertDatabaseHas('event_applications', [
            'user_id' => $user->id,
            'event_application_slot_id' => $slot1->id,
            'availability' => 'unavailable',
            'can_help_setup' => false,
            'can_help_cleanup' => true,
        ]);
    }

    public function test_throws_exception_for_invalid_availability(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = User::factory()->create();
        $event = Event::factory()->create();
        $slot = EventApplicationSlot::factory()->create(['event_id' => $event->id]);

        $slots = [
            ['slot_id' => $slot->id, 'availability' => null],
        ];

        $this->useCase->execute($user, $event, $slots);
    }

    public function test_throws_exception_for_empty_slots_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->useCase->execute($user, $event, []);
    }
}
