<?php

namespace App\UseCases;

use App\Models\Event;
use App\Services\EventSlotGenerator;

class UpdateEventUseCase
{
    public function __construct(
        private EventSlotGenerator $slotGenerator
    ) {}

    /**
     * Execute the use case to update an event.
     */
    public function execute(Event $event, array $data): Event
    {
        // Filter out empty locations
        if (isset($data['locations'])) {
            $data['locations'] = array_values(array_filter($data['locations'], fn($loc) => !empty($loc)));
        }

        // Check if regeneration is needed before updating
        $needsApplicationSlotRegeneration = $this->slotGenerator->needsApplicationSlotRegeneration($event, $data);
        $needsTimeSlotRegeneration = $this->slotGenerator->needsTimeSlotRegeneration($event, $data);

        // Update the event
        $event->update($data);

        // Regenerate application slots if needed and safe
        if ($needsApplicationSlotRegeneration) {
            $this->slotGenerator->regenerateApplicationSlotsIfSafe($event);
        }

        // Regenerate time slots if needed and safe
        if ($needsTimeSlotRegeneration) {
            $this->slotGenerator->regenerateTimeSlotsIfSafe($event);
        }

        return $event;
    }
}
