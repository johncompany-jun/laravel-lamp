<?php

namespace App\Services;

use App\Models\Event;
use Carbon\Carbon;

class RecurringEventService
{
    public function __construct(
        private EventSlotGenerator $slotGenerator
    ) {}

    /**
     * Create recurring events based on the parent event.
     */
    public function createRecurringEvents(Event $parentEvent, string $endDate): void
    {
        $currentDate = Carbon::parse($parentEvent->event_date)->addWeek();
        $endDate = Carbon::parse($endDate);

        while ($currentDate->lte($endDate)) {
            $this->createRecurringInstance($parentEvent, $currentDate);
            $currentDate->addWeek();
        }
    }

    /**
     * Create a single recurring instance.
     */
    private function createRecurringInstance(Event $parentEvent, Carbon $date): Event
    {
        $recurringEvent = $parentEvent->replicate();
        $recurringEvent->event_date = $date->format('Y-m-d');
        $recurringEvent->parent_event_id = $parentEvent->id;
        $recurringEvent->is_recurring = false;
        $recurringEvent->recurrence_type = null;
        $recurringEvent->recurrence_end_date = null;
        $recurringEvent->save();

        // Generate application slots for recurring event
        $this->slotGenerator->generateApplicationSlots($recurringEvent);

        // Generate time slots for recurring event
        $this->slotGenerator->generateTimeSlots($recurringEvent);

        return $recurringEvent;
    }
}
