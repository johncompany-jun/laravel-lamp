<?php

namespace App\UseCases;

use App\Models\Event;
use App\Services\EventSlotGenerator;
use App\Services\RecurringEventService;

class CreateEventUseCase
{
    public function __construct(
        private EventSlotGenerator $slotGenerator,
        private RecurringEventService $recurringEventService
    ) {}

    /**
     * Execute the use case to create an event.
     */
    public function execute(array $data): Event
    {
        // Filter out empty locations
        if (isset($data['locations'])) {
            $data['locations'] = array_values(array_filter($data['locations'], fn($loc) => !empty($loc)));
        }

        // Create the main event
        $event = Event::create($data);

        // Generate application slots based on application_slot_duration
        $this->slotGenerator->generateApplicationSlots($event);

        // Generate time slots based on slot_duration
        $this->slotGenerator->generateTimeSlots($event);

        // If recurring, create recurring events
        if (($data['is_recurring'] ?? false) && $data['recurrence_type'] === 'weekly') {
            $this->recurringEventService->createRecurringEvents($event, $data['recurrence_end_date']);
        }

        return $event;
    }
}
