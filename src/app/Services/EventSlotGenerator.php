<?php

namespace App\Services;

use App\Models\Event;
use Carbon\Carbon;

class EventSlotGenerator
{
    /**
     * Generate application slots for an event based on application_slot_duration.
     */
    public function generateApplicationSlots(Event $event): void
    {
        $startTime = Carbon::parse($event->start_time);
        $endTime = Carbon::parse($event->end_time);
        $duration = $event->application_slot_duration;

        $currentTime = $startTime->copy();
        $slots = [];

        while ($currentTime->lt($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            if ($slotEnd->lte($endTime)) {
                $slots[] = [
                    'event_id' => $event->id,
                    'start_time' => $currentTime->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $currentTime->addMinutes($duration);
        }

        if (!empty($slots)) {
            $event->applicationSlots()->insert($slots);
        }
    }

    /**
     * Generate time slots for an event based on slot duration.
     */
    public function generateTimeSlots(Event $event): void
    {
        $startTime = Carbon::parse($event->start_time);
        $endTime = Carbon::parse($event->end_time);
        $duration = $event->slot_duration;
        $locations = $event->locations ?? [];

        $currentTime = $startTime->copy();
        $slots = [];

        while ($currentTime->lt($endTime)) {
            $slotEnd = $currentTime->copy()->addMinutes($duration);

            if ($slotEnd->lte($endTime)) {
                $slotData = [
                    'event_id' => $event->id,
                    'start_time' => $currentTime->format('H:i:s'),
                    'end_time' => $slotEnd->format('H:i:s'),
                    'capacity' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (!empty($locations)) {
                    foreach ($locations as $location) {
                        $slots[] = array_merge($slotData, ['location' => $location]);
                    }
                } else {
                    $slots[] = $slotData;
                }
            }

            $currentTime->addMinutes($duration);
        }

        if (!empty($slots)) {
            $event->slots()->insert($slots);
        }
    }

    /**
     * Check if application slots need regeneration.
     */
    public function needsApplicationSlotRegeneration(Event $event, array $newData): bool
    {
        return $event->start_time !== $newData['start_time']
            || $event->end_time !== $newData['end_time']
            || $event->application_slot_duration !== $newData['application_slot_duration'];
    }

    /**
     * Check if time slots need regeneration.
     */
    public function needsTimeSlotRegeneration(Event $event, array $newData): bool
    {
        $locationsChanged = json_encode($event->locations ?? []) !== json_encode($newData['locations'] ?? []);

        return $event->start_time !== $newData['start_time']
            || $event->end_time !== $newData['end_time']
            || $event->slot_duration !== $newData['slot_duration']
            || $locationsChanged;
    }

    /**
     * Regenerate application slots if safe to do so.
     */
    public function regenerateApplicationSlotsIfSafe(Event $event): bool
    {
        $hasApplications = $event->applicationSlots()->whereHas('applications')->exists();

        if (!$hasApplications) {
            $event->applicationSlots()->delete();
            $this->generateApplicationSlots($event);
            return true;
        }

        return false;
    }

    /**
     * Regenerate time slots if safe to do so.
     */
    public function regenerateTimeSlotsIfSafe(Event $event): bool
    {
        $hasAssignments = $event->slots()->whereHas('assignments')->exists();

        if (!$hasAssignments) {
            $event->slots()->delete();
            $this->generateTimeSlots($event);
            return true;
        }

        return false;
    }
}
